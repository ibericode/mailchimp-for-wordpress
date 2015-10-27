<?php

class MC4WP_MailChimp_Merge_Var {

	public $name;

	public $field_type;

	public $tag;

	public $required = false;

	public $choices = array();

	public $public = true;

	public $default = '';

	/**
	 * @param      $name
	 * @param      $field_type
	 * @param      $tag
	 * @param bool $required
	 * @param array $choices
	 */
	public function __construct( $name, $field_type, $tag, $required = false, $choices = array() ) {
		$this->name = $name;
		$this->field_type = $field_type;
		$this->tag = $tag;

		// todo remove $req property
		$this->required = $this->req = $required;
		$this->choices = $choices;
	}

	/**
	 * @param $data
	 *
	 * @return MC4WP_MailChimp_Merge_Var
	 */
	public static function from_data( $data ) {

		$instance = new self( $data->name, $data->field_type, $data->tag, $data->req );

		$optional = array(
			'choices',
			'public',
			'default'
		);

		foreach( $optional as $key ) {
			if( isset( $data->$key ) ) {
				$instance->$key = $data->$key;
			}
		}

		return $instance;
	}

	/**
	 * @return array
	 *
	 * @todo move this to Field class? Or client-side?
	 */
	public function get_fields() {

		// this merge var needs no form fields if it's not public
		if( ! $this->public ) {
			return array();
		}

		$fields = array();

		switch( $this->field_type ) {
			case 'address':
				$fields[] = new MC4WP_Form_Field( __( 'Address', 'mailchimp-for-wp' ), $this->tag . '[addr1]', 'text', $this->required );
				$fields[] = new MC4WP_Form_Field( __( 'City', 'mailchimp-for-wp' ), $this->tag . '[city]', 'text', $this->required );
				$fields[] = new MC4WP_Form_Field( __( 'State', 'mailchimp-for-wp' ), $this->tag . '[state]', 'text', $this->required );
				$fields[] = new MC4WP_Form_Field( __( 'ZIP', 'mailchimp-for-wp' ), $this->tag . '[zip]', 'text', $this->required );

				$countries = MC4WP_Tools::get_countries();
				$choices = array();
				foreach( $countries as $value => $label ) {
					$choices[] = new MC4wP_Form_Field_Choice( $value, $label );
				}
				$fields[] = new MC4WP_Form_Field( __( 'Country', 'mailchimp-for-wp' ), $this->tag . '[country]', 'select', $this->required, 'US', $choices );
				break;

			case 'phone':
				$fields[] = new MC4WP_Form_Field( $this->name, $this->tag, 'tel', $this->required, $this->default, $this->choices );
				break;

			case 'radio':
				$choices = array();
				foreach( $this->choices as $choice ) {
					$choices[] = new MC4WP_Form_Field_Choice( $choice );
				}

				$fields[] = new MC4WP_Form_Field( $this->name, $this->tag, 'radio', $this->required, $this->default, $choices );

				break;

			case 'dropdown':
				$choices = array();
				foreach( $this->choices as $choice ) {
					$choices[] = new MC4wP_Form_Field_Choice( $choice );
				}

				$fields[] = new MC4WP_Form_Field( $this->name, $this->tag, 'select', $this->required, $this->default, $choices );
				break;

			default:
				$fields[] = new MC4WP_Form_Field( $this->name, $this->tag, $this->field_type, $this->required, $this->default, $this->choices );
				break;

		}

		return $fields;
	}
}