<?php

class MergeVarTest extends PHPUnit_Framework_TestCase {

	/**
	 * @covers MC4WP_MailChimp_Merge_Var::__construct
	 */
	public function test_constructor() {

		$field_name = 'Email Address';
		$field_type = 'email';
		$field_tag = 'MMERGE1';

		$instance = new MC4WP_MailChimp_Merge_Var( $field_name, $field_type, $field_tag );
		self::assertAttributeEquals( $field_name, 'name', $instance );
		self::assertAttributeEquals( $field_type, 'field_type', $instance );
		self::assertAttributeEquals( $field_tag, 'tag', $instance );
	}

	/**
	 * @covers MC4WP_MailChimp_List::from_data
	 */
	public function test_from_data() {
		$data = (object) (array(
			'name' => 'Email Address',
			'req' => true,
			'field_type' => 'email',
			'public' => true,
			'show' => true,
			'order' => '1',
			'default' => '',
			'helptext' => '',
			'size' => '25',
			'tag' => 'EMAIL',
			'id' => 0,
		));

		$merge_var = MC4WP_MailChimp_Merge_Var::from_data( $data );
		self::assertEquals( $merge_var->name, $data->name );
		self::assertEquals( $merge_var->required, $data->req );
		self::assertEquals( $merge_var->field_type, $data->field_type );
		self::assertEquals( $merge_var->tag, $data->tag );
		self::assertEquals( $merge_var->public, $data->public );
		self::assertEquals( $merge_var->default, $data->default );
		self::assertEmpty( $merge_var->choices );

		$data = (object) (array(
			'name' => 'Website',
			'req' => false,
			'field_type' => 'url',
			'public' => false,
			'show' => true,
			'order' => '4',
			'default' => 'http://dvk.co/',
			'helptext' => '',
			'size' => '25',
			'tag' => 'WEBSITE',
			'id' => 3,
		));
		$merge_var = MC4WP_MailChimp_Merge_Var::from_data( $data );
		self::assertEquals( $merge_var->public, $data->public );
		self::assertEquals( $merge_var->default, $data->default );
	}

}