<?php

/**
 * Represents an Interest Category in Mailchimp.
 *
 * @access public
 * @since 4.0
 */
class MC4WP_MailChimp_Interest_Category
{

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $field_type = '';

    /**
     * @var string[]
     */
    public $interests = array();

    /**
     * @param int $id
     * @param string $name
     * @param string $field_type
     * @param array $interests
     */
    public function __construct($id, $name, $field_type, $interests = array())
    {
        $this->id = $id;
        $this->name = $name;
        $this->field_type = $field_type;
        $this->interests = $interests;
    }

    /**
     * @param string $name
     *
     * @return array|string[]
     */
    public function __get($name)
    {
        // for backwards compatibility with v3.x, channel these properties to their new names
        if ($name === 'groups') {
            return $this->interests;
        }
    }

    /**
     * @param object $data
     *
     * @return MC4WP_MailChimp_Interest_Category
     */
    public static function from_data($data)
    {
        $instance = new self($data->id, $data->title, $data->type);
        return $instance;
    }

    /**
     * @since 4.0
     *
     * @param string $interest_id
     * @return string
     *
     * @throws Exception
     */
    public function get_interest_name_by_id($interest_id)
    {
        foreach ($this->interests as $id => $name) {
            if ($id != $interest_id) {
                continue;
            }

            return $name;
        }

        throw new Exception(sprintf('No interest with ID %s', $interest_id));
    }
}
