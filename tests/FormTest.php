<?php

class FormTest extends PHPUnit_Framework_TestCase {

	protected $form;

	public function __construct() {
		$this->form = new MC4WP_Form( 123 );
	}

	/**
	 * @covers MC4WP_Form::__construct
	 */
	public function test_constructor() {
		$id = 12;
		mock_get_post( array( 'ID' => $id ) );
		$form = new MC4WP_Form( $id );
		$this->assertEquals( $id, $form->ID );
		$this->assertEquals( $id, $form->post->ID );
	}

	/**
	 * @covers MC4WP_Form::has_field_types
	 */
	public function test_has_field_type() {
		mock_get_post(
			array(
				'post_content' => '<input type="email" name="EMAIL" />'
			)
		);

		$form = new MC4WP_Form( 1 );
		$this->assertTrue( $form->has_field_type( 'email' ) );
		$this->assertFalse( $form->has_field_type( 'date' ) );


		mock_get_post(
			array(
				'post_content' => '<input type="email" name="EMAIL" /><input type="date" name="EMAIL" /><input type="url" name="EMAIL" />'
			)
		);
		$form = new MC4WP_Form( 1 );
		$this->assertTrue( $form->has_field_type( 'email' ) );
		$this->assertTrue( $form->has_field_type( 'date' ) );
		$this->assertTrue( $form->has_field_type( 'url' ) );
		$this->assertFalse( $form->has_field_type( 'number' ) );
	}

	/**
	 * @covers MC4WP_Form::get_field_types
	 */
	public function test_get_field_types() {
		$types = array( 'number', 'email', 'date', 'url' );
		mock_get_post(
			array(
				'post_content' => '<input type="number"><input type="email" name="EMAIL" /><input type="date" name="EMAIL" /><input type="url" name="EMAIL" />'
			)
		);
		$form = new MC4WP_Form(15);
		$this->assertEquals( $form->get_field_types(), $types );

		mock_get_post(
			array(
				'post_content' => '',
			)
		);
		$form = new MC4WP_Form(1);
		$this->assertEmpty( $form->get_field_types() );
	}


}