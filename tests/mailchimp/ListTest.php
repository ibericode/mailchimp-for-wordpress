<?php

class ListTest extends PHPUnit_Framework_TestCase {

	/**
	 * @covers MC4WP_MailChimp_List::__construct
	 */
	public function test_constructor() {
		$id = 'abcdefg';
		$name = 'My MailChimp List';
		$web_id = '500';
		$list = new MC4WP_MailChimp_List( $id, $name, $web_id );

		$this->assertAttributeEquals( $id, 'id', $list );
		$this->assertAttributeEquals( $web_id, 'web_id', $list );
		$this->assertAttributeEquals( $name, 'name', $list );
	}

	/**
	 * @covers MC4WP_MailChimp_List::get_field_name_by_tag
	 */
	public function test_get_field_name_by_tag() {
		$id = 'abcdefg';
		$name = 'My MailChimp List';
		$web_id = '500';
		$list = new MC4WP_MailChimp_List( $id, $name, $web_id );

		$this->assertEmpty( $list->get_field_name_by_tag( 'tag' ) );

		// we should always know email field name
		$this->assertStringStartsWith( 'Email', $list->get_field_name_by_tag( 'email' ) );

		$field_name = 'Field Name';
		$field_tag = 'tag';
		$list->merge_vars[] = new MC4WP_MailChimp_Merge_Var( $field_name, 'email', $field_tag );
		$this->assertEquals( $list->get_field_name_by_tag( $field_tag ), $field_name );
	}

	/**
	 * @covers MC4WP_MailChimp_List::get_grouping
	 */
	public function test_get_grouping() {
		$id = 'abcdefg';
		$name = 'My MailChimp List';
		$web_id = '500';
		$list = new MC4WP_MailChimp_List( $id, $name, $web_id );

		$this->assertNull( $list->get_grouping( 'grouping-id' ) );

		$grouping = new MC4WP_MailChimp_Grouping( 'sample-id', 'Grouping Name', 'dropdown' );
		$list->groupings[]  = $grouping;
		$this->assertEquals( $list->get_grouping( $grouping->id ), $grouping );

	}

	/**
	 * @covers MC4WP_MailChimp_List::get_grouping_name
	 */
	public function test_get_grouping_name() {
		$id = 'abcdefg';
		$name = 'My MailChimp List';
		$web_id = '500';
		$list = new MC4WP_MailChimp_List( $id, $name, $web_id );

		$this->assertEmpty( $list->get_grouping_name( 'sample-id' ) );

		$grouping_name = 'Grouping Name';
		$list->groupings[]  = new MC4WP_MailChimp_Grouping( 'sample-id', $grouping_name, 'dropdown' );
		$this->assertEquals( $list->get_grouping_name( 'sample-id' ), $grouping_name );
	}

}