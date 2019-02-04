<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

// Debug class used for testing, no remote requests..

/**
 * Class ApiDebug
 * @ignore
 */
class ApiDebug extends MC4WP_API
{

    /**
     * Default response
     *
     * @var bool
     */
    private $response = false;

    /**
     * Overwrite `call` method to just store test response right away.
     */
    public function call($method, array $data = array())
    {
        if (isset($this->response->error)) {
            $this->error_message = $this->response->error;
        }

        if (isset($this->response->code)) {
            $this->error_code = (int) $this->response->code;
        }

        return $this->response;
    }

    /**
     * Test helper to set the API response in an easy way.
     *
     * @param  $response
     */
    public function set_response($response)
    {
        $this->response = $response;
    }
}

class ApiTest extends TestCase
{

    /**
     * @var MC4WP_API
     */
    private $api;

    /**
     * Before each test
     */
    public function setUp()
    {
        $this->api = new ApiDebug('api_key');
    }

    /**
     * Tests if API url is correctly set from given API key
     */
    public function test_api_url()
    {
        $instance = new MC4WP_API('aaaaaaaaaaaaaaaaaaaa-us1');
        $property = Assert::readAttribute($instance, 'api_url');

        self::assertEquals('https://us1.api.mailchimp.com/2.0/', $property);
    }

    /**
     * @covers MC4WP_Api::is_connected
     */
    public function test_is_connected()
    {

        // no api key, false
        $api = new ApiDebug('');
        self::assertFalse($api->is_connected());

        // correct response, true
        $api = new ApiDebug('apikey');
        $api->set_response((object) array( 'msg' => 'Everything\'s Chimpy!' ));
        self::assertTrue($api->is_connected());

        // failed request, false
        $api = new ApiDebug('apikey');
        $api->set_response(false);
        self::assertFalse($api->is_connected());
    }

    /**
     * @covers MC4WP_API::subscribe
     */
    public function test_subscribe()
    {
        // test request error
        $this->api->set_response(false);
        self::assertFalse($this->api->subscribe('sample_list_id', 'sample_email'));

        // test "already_subscribed" API error
        $this->api->set_response((object) array( 'error' => 'error message', 'code' => '214' ));
        self::assertFalse($this->api->subscribe('sample_list_id', 'sample_email'));

        // test general API errors
        $this->api->set_response((object) array( 'error' => 'error message', 'code' => '-99' ));
        self::assertFalse($this->api->subscribe('sample_list_id', 'sample_email'));

        // test success
        $this->api->set_response((object) array( 'email' => 'sample_email', 'euid' => 'sample_euid', 'leid' => 'sample_leid' ));
        self::assertTrue($this->api->subscribe('sample_list_id', 'sample_email'));
    }

    /**
     * @covers MC4WP_API::get_lists
     */
    public function test_get_lists()
    {
        // test error
        $this->api->set_response(false);
        self::assertFalse($this->api->get_lists());

        // test api error
        $this->api->set_response((object) array( 'error' => 'Error message', 'code' => -99 ));
        self::assertFalse($this->api->get_lists());

        // test success
        $lists = array( 'sample_list' );
        $this->api->set_response((object) array( 'data' => $lists ));
        self::assertEquals($this->api->get_lists(), $lists);
    }

    /**
     * @covers MC4WP_API::get_list_groupings
     */
    public function test_get_list_groupings()
    {
        // test error
        $this->api->set_response(false);
        self::assertFalse($this->api->get_list_groupings('list_id'));

        // test api error
        $this->api->set_response((object) array( 'error' => 'Error message', 'code' => -99 ));
        self::assertFalse($this->api->get_list_groupings('list_id'));

        // test success
        $groups = array( (object) array( 'id' => 1, 'name' => 'Group Name' ) );
        $this->api->set_response($groups);
        self::assertEquals($this->api->get_list_groupings('list_id'), $groups);
    }

    /**
     * @covers MC4WP_API::get_lists_with_merge_vars
     */
    public function test_get_lists_with_merge_vars()
    {
        // test error
        $this->api->set_response(false);
        self::assertFalse($this->api->get_lists_with_merge_vars(array( 1, 2 )));

        // test api error
        $this->api->set_response((object) array( 'error' => 'Error message', 'code' => -99 ));
        self::assertFalse($this->api->get_lists_with_merge_vars(array( 1, 2 )));

        // test success
        $lists = array( 'sample_list' );
        $this->api->set_response((object) array( 'data' => $lists ));
        self::assertEquals($this->api->get_lists_with_merge_vars(array( 1, 2 )), $lists);
    }

    /**
     * @covers MC4WP_API::has_error
     */
    public function test_has_error()
    {
        // no error by default
        self::assertFalse($this->api->has_error());

        // error should be stored after failed API request
        $this->api->set_response((object) array( 'error' => 'error message', 'code' => -99 ));
        $this->api->subscribe('sample_list_id', 'sample_email');
        self::assertTrue($this->api->has_error());
    }

    /**
     * @covers MC4WP_API::get_error_message
     */
    public function test_get_error_message()
    {
        // no error by default
        self::assertEmpty($this->api->get_error_message());

        // error should be stored after failed API request
        $this->api->set_response((object) array( 'error' => 'error message', 'code' => -99 ));
        $this->api->subscribe('sample_list_id', 'sample_email');
        self::assertEquals($this->api->get_error_message(), 'error message');
    }

    public function test_get_error_code()
    {
        $this->api->set_response((object) array( 'error' => 'error message', 'code' => "-99" ));
        $this->api->subscribe('sample_list_id', 'sample_email');

        self::assertEquals($this->api->get_error_code(), -99);
    }

    /**
     * @covers MC4WP_API::get_last_response
     */
    public function test_get_last_response()
    {
        // no response by default
        self::assertNull($this->api->get_last_response());
    }
}
