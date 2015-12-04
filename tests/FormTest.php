<?php

/**
 * Class FormTest
 * @ignore
 */
class FormTest extends PHPUnit_Framework_TestCase {

	/**
	 * Unmock posts after every test
	 */
	public function tearDown() {
		unmock_post();
	}

	/**
	 * @covers MC4WP_Form::get_instance
	 */
	public function test_get_instance() {

		// we should get an exception when getting non-existing form
		self::setExpectedException( 'Exception' );
		new MC4WP_Form( 500 );
		self::setExpectedException(null);
	}

	/**
	 * @covers MC4WP_Form::__construct
	 */
	public function test_constructor() {
		$id = 12;
		mock_get_post( array( 'ID' => $id ) );
		$form = new MC4WP_Form( $id );
		self::assertEquals( $id, $form->ID );
		self::assertEquals( $id, $form->post->ID );

		// settings & messages should be loaded
		self::assertNotEmpty( $form->settings );
		self::assertNotEmpty( $form->messages );

		// default form action should be "subscribe
		self::assertEquals( 'subscribe', $form->config['action'] );

		// lists should default to lists from settings
		self::assertEquals( array(), $form->config['lists'] );
	}

	/**
	 * @covers MC4WP_Form::has_field_type
	 */
	public function test_has_field_type() {
		mock_get_post(
			array(
				'post_content' => '<input type="email" name="EMAIL" />'
			)
		);

		$form = new MC4WP_Form( 1 );
		self::assertTrue( $form->has_field_type( 'email' ) );
		self::assertFalse( $form->has_field_type( 'date' ) );


		mock_get_post(
			array(
				'post_content' => '<input type="email" name="EMAIL" /><input type="date" name="EMAIL" /><input type="url" name="EMAIL" />'
			)
		);
		$form = new MC4WP_Form( 1 );
		self::assertTrue( $form->has_field_type( 'email' ) );
		self::assertTrue( $form->has_field_type( 'date' ) );
		self::assertTrue( $form->has_field_type( 'url' ) );
		self::assertFalse( $form->has_field_type( 'number' ) );
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
		self::assertEquals( $form->get_field_types(), $types );

		mock_get_post(
			array(
				'post_content' => '',
			)
		);
		$form = new MC4WP_Form(1);
		self::assertEmpty( $form->get_field_types() );
	}

	/**
	 * @covers MC4WP_Form::is_valid
	 */
	public function test_is_valid() {
		mock_get_post( array( 'ID' => 1 ) );
		$form = new MC4WP_Form(1);
		self::assertTrue( $form->validate() );

		// empty data should not validate
		$request = new MC4WP_Request();
		$form = new MC4WP_Form(1);
		$form->handle_request( $request );
		$valid = $form->validate();
		self::assertFalse( $valid );

		// errors array should have been filled
		self::assertNotEmpty( $form->errors );


//		// with lists and mocked nonce, form should be valid
		// @todo fix this test
//		define( 'WP_CACHE', true );
//		$valid_data = array(
//			'email' => 'johngreene@hotmail.com',
//			'_mc4wp_lists' => array( 'list-id' ),
//			'_mc4wp_timestamp' => time() - 100
//		);
//
//		$request = new MC4WP_Request( array(), $valid_data );
//		$form = new MC4WP_Form(1);
//		$form->handle_request( $request );
//		self::assertTrue( $form->is_valid() );

		// todo: required fields
	}

	/**
	 * @covers MC4WP_Form::has_errors
	 */
	public function test_has_errors() {
		mock_get_post( array( 'ID' => 1 ) );
		$form = new MC4WP_Form(1);
		$form->errors = array( 'required_field_missing' );
		self::assertTrue( $form->has_errors() );

		$form->errors = array();
		self::assertFalse( $form->has_errors() );
	}

	/**
	 * @covers MC4WP_Form::handle_request
	 */
	public function test_handle_request() {
		mock_get_post( array( 'ID' => 15 ) );
		$form = new MC4WP_Form(15);
		$data = array(
			'EMAIL' => 'value'
		);
		$request = new MC4WP_Request( array(), $data );
		$form->handle_request( $request );

		// form should show as submitted
		self::assertTrue( $form->is_submitted );

		// data should have been filled
		self::assertNotEmpty( $form->data );
		self::assertEquals( $form->data, $data );


		// data should have been uppercased
		$form = new MC4WP_Form(15);
		$data = array(
			'email' => 'value'
		);
		$data_uppercased = array_change_key_case( $data, CASE_UPPER );
		$request = new MC4WP_Request( array(), $data );
		$form->handle_request( $request );
		self::assertEquals( $form->data, $data_uppercased );
	}

	/**
	 * @covers MC4WP_Form::get_required_fields
	 */
	public function test_get_required_fields() {
		mock_get_post( array( 'ID' => 15 ) );
		$form = new MC4WP_Form(15);
		$form->settings['required_fields'] = 'EMAIL';
		self::assertEquals( $form->get_required_fields(), array() );

		// use array_diff here as order of elements is not important
		$form->settings['required_fields'] = 'EMAIL,FNAME';
		self::assertEmpty( array_diff( $form->get_required_fields(), array( 'FNAME' ) ) );

		$form->settings['required_fields'] = 'website,EMAIL,FNAME';
		self::assertEmpty( array_diff( $form->get_required_fields(), array( 'WEBSITE', 'FNAME' ) ) );
	}

	/**
	 * @covers MC4WP_Form::get_stylesheet
	 */
	public function test_get_stylesheet() {
		mock_get_post( array( 'ID' => 15 ) );
		$form = new MC4WP_Form(15);
		$form->settings['css'] = false;
		self::assertEmpty( $form->get_stylesheet() );

		$form->settings['css'] = 'some-stylesheet';
		self::assertEquals( $form->get_stylesheet(), 'some-stylesheet' );

		// themes are bundled
		$form->settings['css'] = 'theme-something';
		self::assertEquals( $form->get_stylesheet(), 'themes' );
	}

	/**
	 * @covers MC4WP_Form::add_error
	 * @covers MC4WP_Form::has_errors
	 */
	public function test_errors() {
		mock_get_post( array( 'ID' => 15 ) );
		$form = new MC4WP_Form(15);

		self::assertFalse( $form->has_errors() );

		$form->add_error( 'some_error' );
		self::assertTrue( $form->has_errors() );

		$form->add_error( 'some_other_error' );
		self::assertCount( 2, $form->errors );
	}

	/**
	 * @covers MC4WP_Form::get_message
	 */
	public function test_get_message() {
		mock_get_post( array( 'ID' => 15 ) );
		$form = new MC4WP_Form(15);

		$errorMessage = new MC4WP_Form_Message( 'Error text', 'error' );
		$successMessage = new MC4WP_Form_Message( 'Success text', 'success' );

		$form->messages = array(
			'error' => $errorMessage,
			'success' => $successMessage
		);

		self::assertInstanceOf( 'MC4WP_Form_Message', $form->get_message( 'error' ) );
		self::assertEquals( $errorMessage, $form->get_message( 'error' ) );
		self::assertEquals( $errorMessage, $form->get_message( 'unexisting_error' ) );
		self::assertEquals( $successMessage, $form->get_message( 'success' ) );
	}

	/**
	 * @covers MC4WP_Form::set_config
	 */
	public function test_set_config() {
		mock_get_post( array( 'ID' => 15 ) );
		$form = new MC4WP_Form(15);

		$list_id = 'some-list-id';
		$form->set_config( array(
			'lists' => $list_id,
			'action' => 'unsubscribe',
		));
		self::assertTrue( is_array( $form->config['lists'] ) );
		self::assertTrue( in_array( $list_id, $form->config['lists'] ) );
		self::assertEquals( 'unsubscribe', $form->config['action'] );
		self::assertFalse( in_array( 'unexisting-list-id', $form->config['lists'] ) );

		// test passing comma-separated string
		$form->set_config( array(
			'lists' => 'list-id,another-list-id',
		));
		self::assertTrue( is_array( $form->config['lists'] ) );
		self::assertTrue( in_array( 'list-id', $form->config['lists'] ) );
		self::assertTrue( in_array( 'another-list-id', $form->config['lists'] ) );
		self::assertFalse( in_array( 'unexisting-list-id', $form->config['lists'] ) );


	}


}