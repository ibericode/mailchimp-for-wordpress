<?php

/**
* Class MC4WP_Field_Map
*
* @access private
* @since 4.0
* @ignore
*/
class MC4WP_List_Data_Mapper
{
    /**
    * @var array
    */
    private $data = [];

    /**
    * @var array
    */
    private $list_ids = [];

    /**
    * @var MC4WP_Field_Formatter
    */
    private $formatter;

    /**
     * @var MC4WP_MailChimp
     */
    private $mailchimp;

    /**
    * @param array $data
    * @param array $list_ids
    */
    public function __construct(array $data, array $list_ids)
    {
        $this->data = array_change_key_case($data, CASE_UPPER);
        if (! isset($this->data['EMAIL'])) {
            throw new InvalidArgumentException('Data needs at least an EMAIL key.');
        }

        $this->list_ids  = $list_ids;
        $this->formatter = new MC4WP_Field_Formatter();
        $this->mailchimp = new MC4WP_MailChimp();
    }

    /**
    * @return MC4WP_MailChimp_Subscriber[]
    */
    public function map()
    {
        $map = [];

        foreach ($this->list_ids as $list_id) {
            $map[ "$list_id" ] = $this->map_list($list_id);
        }

        return $map;
    }

    /**
    * @param string $list_id
    * @return MC4WP_MailChimp_Subscriber
    * @throws Exception
    */
    protected function map_list($list_id)
    {
        $subscriber                = new MC4WP_MailChimp_Subscriber();
        $subscriber->email_address = $this->data['EMAIL'];

        // find merge fields
        $merge_fields = $this->mailchimp->get_list_merge_fields($list_id);
        foreach ($merge_fields as $merge_field) {
            // skip EMAIL field as that is handled separately (see above)
            if ($merge_field->tag === 'EMAIL') {
                continue;
            }

            // use empty() here to skip empty field values
            if (empty($this->data[ $merge_field->tag ])) {
                continue;
            }

            // format field value
            $value = $this->data[ $merge_field->tag ];
            $value = $this->format_merge_field_value($merge_field, $value);

            // add to map
            $subscriber->merge_fields[ $merge_field->tag ] = $value;
        }

        // find interest categories
        if (! empty($this->data['INTERESTS'])) {
            $interest_categories = $this->mailchimp->get_list_interest_categories($list_id);
            foreach ($interest_categories as $interest_category) {
                foreach ($interest_category->interests as $interest_id => $interest_name) {
                    // straight lookup by ID as key with value copy.
                    if (isset($this->data['INTERESTS'][ $interest_id ])) {
                        $subscriber->interests[ $interest_id ] = $this->formatter->boolean($this->data['INTERESTS'][ $interest_id ]);
                    }

                    // straight lookup by ID as top-level value
                    if (in_array($interest_id, $this->data['INTERESTS'], false)) {
                        $subscriber->interests[ $interest_id ] = true;
                    }

                    // look in array with category ID as key.
                    if (isset($this->data['INTERESTS'][ $interest_category->id ])) {
                        $value  = $this->data['INTERESTS'][ $interest_category->id ];
                        $values = is_array($value) ? $value : array_map('trim', explode('|', $value));

                        // find by category ID + interest ID
                        if (in_array($interest_id, $values, false)) {
                            $subscriber->interests[ $interest_id ] = true;
                        }

                        // find by category ID + interest name
                        if (in_array($interest_name, $values, true)) {
                            $subscriber->interests[ $interest_id ] = true;
                        }
                    }
                }
            }
        }

        // add GDPR marketing permissions
        if (! empty($this->data['MARKETING_PERMISSIONS'])) {
            $values                = $this->data['MARKETING_PERMISSIONS'];
            $values                = is_array($values) ? $values : explode(',', $values);
            $values                = array_map('trim', $values);
            $marketing_permissions = $this->mailchimp->get_list_marketing_permissions($list_id);
            foreach ($marketing_permissions as $mp) {
                if (in_array($mp->marketing_permission_id, $values, true) || in_array($mp->text, $values, true)) {
                    $subscriber->marketing_permissions[] = (object) [
                        'marketing_permission_id' => $mp->marketing_permission_id,
                        'enabled'                 => true,
                    ];
                }
            }
        }

        // find language
        /* @see http://kb.mailchimp.com/lists/managing-subscribers/view-and-edit-subscriber-languages?utm_source=mc-api&utm_medium=docs&utm_campaign=apidocs&_ga=1.211519638.2083589671.1469697070 */
        if (! empty($this->data['MC_LANGUAGE'])) {
            $subscriber->language = $this->formatter->language($this->data['MC_LANGUAGE']);
        }

        return $subscriber;
    }


    /**
     * @param object $merge_field
     * @param string $value
     *
     * @return mixed
    */
    private function format_merge_field_value($merge_field, $value)
    {
        $field_type = strtolower($merge_field->type);

        if (method_exists($this->formatter, $field_type)) {
            $value = call_user_func([ $this->formatter, $field_type ], $value, $merge_field->options);
        }

        /**
        * Filters the value of a field after it is formatted.
        *
        * Use this to format a field value according to the field type (in Mailchimp).
        *
        * @since 3.0
        * @param string $value The value
        * @param string $field_type The type of the field (in Mailchimp)
        */
        $value = apply_filters('mc4wp_format_field_value', $value, $field_type);

        return $value;
    }
}
