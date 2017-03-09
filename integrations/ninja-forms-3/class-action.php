<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class MC4WP_Ninja_Forms_Action
 */
final class MC4WP_Ninja_Forms_Action extends NF_Abstracts_ActionNewsletter
{
    /**
     * @var string
     */
    protected $_name  = 'mc4wp';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->_nicename = __( 'MailChimp', 'mailchimp-for-wp' );

        // TODO: Add settings for $update_existing and $replace_interests here.
       // $this->_settings = array_merge( $this->_settings, $settings );
    }

    /*
    * PUBLIC METHODS
    */

    public function save( $action_settings )
    {

    }

    public function process( $action_settings, $form_id, $data )
    {
        if( empty( $action_settings['newsletter_list'] ) || empty( $action_settings['EMAIL'] ) ) {
            return;
        }

        // TODO: Check if checkbox is checked.

        $list_id = $action_settings['newsletter_list'];
        $email_address = $action_settings['EMAIL'];
        $mailchimp = new MC4WP_MailChimp();
        $list = $mailchimp->get_list( $list_id );

        $merge_fields = array();
        foreach( $list->merge_fields as $merge_field ) {
            if( ! empty( $action_settings[ $merge_field->tag ] ) ) {
                $merge_fields[ $merge_field->tag ] = $action_settings[ $merge_field->tag ];
            }
        }

        $args = array(
            'email_address' => $email_address,
            'merge_fields' => $merge_fields,
            'status' => 'pending', // TODO: Add setting for $double_optin
        );

        // TODO: Handle errors here..
        $subscriber = $mailchimp->list_subscribe( $list_id, $email_address, $args );
    }

    protected function get_lists()
    {
        $mailchimp = new MC4WP_MailChimp();

        /** @var MC4WP_MailChimp_List[] $lists */
        $lists = $mailchimp->get_cached_lists();
        $return = array();

        foreach( $lists as $list ) {

            $list_fields = array();
            foreach( $list->merge_fields as $merge_field ) {
                $list_fields[] = array(
                    'value' => $merge_field->tag,
                    'label' => $merge_field->name,
                );
            }

//            TODO: Add support for groups once base class supports this.
//            $list_groups = array();
//            foreach( $list->interest_categories as $category ) {
//
//            }

            $return[] = array(
                'value' => $list->id,
                'label' => $list->name,
                'fields' => $list_fields,
            );
        }

        return $return;
    }
}
