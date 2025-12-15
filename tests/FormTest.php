<?php

use PHPUnit\Framework\TestCase;

/**
 * Class FormTest
 * @ignore
 */
class FormTest extends TestCase
{
    /**
     * Unmock posts after every test
     */
    public function tearDown(): void
    {
        unmock_post();
    }

    /**
     * @covers MC4WP_Form::get_instance
     */
    public function test_get_instance()
    {

        // we should get an exception when getting non-existing form
        self::expectException('Exception');
        MC4WP_Form::get_instance(500);
        self::expectException(null);
    }

    /**
     * @covers MC4WP_Form::__construct
     */
    public function test_constructor()
    {
        $id = 12;
        mock_get_post([ 'ID' => $id ]);
        $post = get_post($id);
        $form = new MC4WP_Form($id, $post, []);
        self::assertEquals($id, $form->ID);

        // settings & messages should be loaded
        self::assertNotEmpty($form->settings);

        // default form action should be "subscribe
        self::assertEquals('subscribe', $form->config['action']);

        // lists should default to lists from settings
        self::assertEquals([], $form->config['lists']);
    }

    /**
     * @covers MC4WP_Form::has_field_type
     */
    public function test_has_field_type()
    {
        mock_get_post(
            [
                'post_content' => '<input type="email" name="EMAIL" />'
            ]
        );

        $post = get_post(1);
        $form = new MC4WP_Form(1, $post);
        self::assertTrue($form->has_field_type('email'));
        self::assertFalse($form->has_field_type('date'));


        mock_get_post(
            [
                'post_content' => '<input type="email" name="EMAIL" /><input type="date" name="EMAIL" /><input type="url" name="EMAIL" />'
            ]
        );
        $post = get_post(1);
        $form = new MC4WP_Form(1, $post);
        self::assertTrue($form->has_field_type('email'));
        self::assertTrue($form->has_field_type('date'));
        self::assertTrue($form->has_field_type('url'));
        self::assertFalse($form->has_field_type('number'));
    }

    /**
     * @covers MC4WP_Form::get_field_types
     */
    public function test_get_field_types()
    {
        $types = [ 'number', 'email', 'date', 'url' ];
        mock_get_post(
            [
                'post_content' => '<input type="number"><input type="email" name="EMAIL" /><input type="date" name="EMAIL" /><input type="url" name="EMAIL" />'
            ]
        );
        $post = get_post(15);
        $form = new MC4WP_Form(15, $post);
        self::assertEquals($form->get_field_types(), $types);

        mock_get_post(
            [
                'post_content' => '',
            ]
        );
        $post = get_post(1);
        $form = new MC4WP_Form(1, $post);
        self::assertEmpty($form->get_field_types());
    }

    /**
     * @covers MC4WP_Form::is_valid
     */
    public function test_is_valid()
    {
        mock_get_post([ 'ID' => 1 ]);
        $post = get_post(1);
        $form = new MC4WP_Form(1, $post);
        self::assertTrue($form->validate());

        // empty data should not validate
        $data = [];
        $form = new MC4WP_Form(1, $post);
        $form->handle_request($data);
        $valid = $form->validate();
        self::assertFalse($valid);

        // errors array should have been filled
        self::assertNotEmpty($form->errors);
    }

    /**
     * @covers MC4WP_Form::has_errors
     */
    public function test_has_errors()
    {
        mock_get_post([ 'ID' => 1 ]);
        $post = get_post(1);
        $form = new MC4WP_Form(1, $post);
        $form->errors = [ 'required_field_missing' ];
        self::assertTrue($form->has_errors());

        $form->errors = [];
        self::assertFalse($form->has_errors());
    }

    /**
     * @covers MC4WP_Form::handle_request
     */
    public function test_handle_request()
    {
        mock_get_post([ 'ID' => 15 ]);
        $post = get_post(15);
        $form = new MC4WP_Form(15, $post);
        $data = [
            'EMAIL' => 'value'
        ];

        $form->handle_request($data);

        // form should show as submitted
        self::assertTrue($form->is_submitted);

        // data should have been filled
        $form_data = $form->get_data();
        self::assertNotEmpty($form_data);
        self::assertEquals($form_data, $data);


        // data should have been uppercased
        $form = new MC4WP_Form(15, $post);
        $data = [
            'email' => 'value'
        ];
        $data_uppercased = array_change_key_case($data, CASE_UPPER);
        $form->handle_request($data);
        $form_data = $form->get_data();
        self::assertEquals($form_data, $data_uppercased);
    }

    /**
     * @covers MC4WP_Form::get_required_fields
     */
    public function test_get_required_fields()
    {
        mock_get_post([ 'ID' => 15 ]);
        $post = get_post(15);
        $form = new MC4WP_Form(15, $post);
        $form->settings['required_fields'] = 'EMAIL';
        self::assertEquals($form->get_required_fields(), []);

        // use array_diff here as order of elements is not important
        $form->settings['required_fields'] = 'EMAIL,FNAME';
        self::assertEmpty(array_diff($form->get_required_fields(), [ 'FNAME' ]));

        $form->settings['required_fields'] = 'website,EMAIL,FNAME';
        self::assertEmpty(array_diff($form->get_required_fields(), [ 'WEBSITE', 'FNAME' ]));
    }

    /**
     * @covers MC4WP_Form::get_stylesheet
     */
    public function test_get_stylesheet()
    {
        mock_get_post([ 'ID' => 15 ]);
        $post = get_post(15);
        $form = new MC4WP_Form(15, $post);
        $form->settings['css'] = false;
        self::assertEmpty($form->get_stylesheet());

        $form->settings['css'] = 'some-stylesheet';
        self::assertEquals($form->get_stylesheet(), 'some-stylesheet');

        // themes are bundled
        $form->settings['css'] = 'theme-something';
        self::assertEquals($form->get_stylesheet(), 'themes');
    }

    /**
     * @covers MC4WP_Form::add_error
     * @covers MC4WP_Form::has_errors
     */
    public function test_errors()
    {
        mock_get_post([ 'ID' => 15 ]);
        $post = get_post(15);
        $form = new MC4WP_Form(15, $post);

        self::assertFalse($form->has_errors());

        $form->add_error('some_error');
        self::assertTrue($form->has_errors());

        $form->add_error('some_other_error');
        self::assertCount(2, $form->errors);
    }

    /**
     * @covers MC4WP_Form::set_config
     */
    public function test_set_config()
    {
        mock_get_post([ 'ID' => 15 ]);
        $post = get_post(15);
        $form = new MC4WP_Form(15, $post);

        $list_id = 'some-list-id';
        $form->set_config([
            'lists' => $list_id,
            'action' => 'unsubscribe',
        ]);
        self::assertTrue(is_array($form->config['lists']));
        self::assertTrue(in_array($list_id, $form->config['lists']));
        self::assertEquals('unsubscribe', $form->config['action']);
        self::assertFalse(in_array('unexisting-list-id', $form->config['lists']));

        // test passing comma-separated string
        $form->set_config([
            'lists' => 'list-id,another-list-id',
        ]);
        self::assertTrue(is_array($form->config['lists']));
        self::assertTrue(in_array('list-id', $form->config['lists']));
        self::assertTrue(in_array('another-list-id', $form->config['lists']));
        self::assertFalse(in_array('unexisting-list-id', $form->config['lists']));
    }

    public function test_add_notice()
    {
        mock_get_post([ 'ID' => 1 ]);
        $post = get_post(1);
        $form = new MC4WP_Form(1, $post);

        $form->add_notice('Text', 'notice');
        self::assertCount(1, $form->notices);
    }

    public function test_get_subscriber_tags()
    {
        mock_get_post([ 'ID' => 1 ]);
        $post = get_post(1);
        $form = new MC4WP_Form(1, $post);
        $form->settings = [
            'subscriber_tags' => 'foo,bar',
            'remove_subscriber_tags' => 'old,,tag'
        ];

        $this->assertEquals($form->get_subscriber_tags(), [
            ['name' => 'foo', 'status' => 'active'],
            ['name' => 'bar', 'status' => 'active'],
            ['name' => 'old', 'status' => 'inactive'],
            ['name' => 'tag', 'status' => 'inactive']
        ]);
    }
}
