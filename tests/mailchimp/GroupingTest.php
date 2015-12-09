<?php

class GroupingTest extends PHPUnit_Framework_TestCase {

	/**
	 * @covers MC4WP_MailChimp_Grouping::__construct
	 */
	public function test_constructor() {

		$name = 'My Grouping';
		$field_type = 'dropdown';
		$id = 'my-grouping-id';

		$instance = new MC4WP_MailChimp_Grouping( $id, $name, $field_type );
		self::assertAttributeEquals( $name, 'name', $instance );
		self::assertAttributeEquals( $field_type, 'field_type', $instance );
		self::assertAttributeEquals( $id, 'id', $instance );
	}

	/**
	 * @covers MC4WP_MailChimp_Grouping::from_data
	 */
	public function test_from_data() {
		$data = (object) (array(
			'id' => 5381,
			'name' => 'Dropdown Group',
			'form_field' => 'dropdown',
			'display_order' => '0',
			'groups' =>
				array (
					(object) (array(
						'id' => 29109,
						'bit' => '1',
						'name' => 'First Choice',
						'display_order' => '1',
						'subscribers' => NULL,
					)),
					(object)(array(
						'id' => 29113,
						'bit' => '2',
						'name' => 'Second Choice',
						'display_order' => '2',
						'subscribers' => NULL,
					)),
					(object)(array(
						'id' => 29117,
						'bit' => '4',
						'name' => 'Third Choice',
						'display_order' => '3',
						'subscribers' => NULL,
					)),
				),
			)
		);

		$grouping = MC4WP_MailChimp_Grouping::from_data( $data );
		self::assertEquals( $grouping->name, $data->name );
		self::assertEquals( $grouping->field_type, $data->form_field );
		self::assertEquals( $grouping->id, $data->id );
		self::assertCount( count( $data->groups ), $grouping->groups );

		$sample_group = array_pop( $data->groups );
		self::assertArrayHasKey( $sample_group->id, $grouping->groups );
		self::assertContains( $sample_group->name, $grouping->groups );
	}

}