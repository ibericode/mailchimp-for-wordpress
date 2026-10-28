<?php

use PHPUnit\Framework\TestCase;

/**
 * Class APITest
 *
 * @ignore
 */
class APITest extends TestCase
{
    /**
     * @covers MC4WP_API_V3::update_list_member
     */
    public function test_resource_path_arguments_are_url_encoded()
    {
        $client = new class ('') extends MC4WP_API_V3_Client {
            public $resources = [];

            public function get($resource, array $args = [])
            {
                $this->resources[] = $resource;
                return (object) [];
            }

            public function patch($resource, array $data)
            {
                $this->resources[] = $resource;
                return (object) [];
            }
        };

        $api = new class ('', $client) extends MC4WP_API_V3 {
            public function __construct($api_key, $client)
            {
                $this->client = $client;
            }
        };

        $victim_hash = md5('victim@example.com');
        $hash        = md5('attacker@example.com');
        $api->update_list_member("abc/members/{$victim_hash}#", 'attacker@example.com', []);
        $api->get_list_member('abc?x=1', 'attacker@example.com');
        $api->get_list('abc123');

        self::assertEquals([
            "/lists/abc%2Fmembers%2F{$victim_hash}%23/members/{$hash}",
            "/lists/abc%3Fx%3D1/members/{$hash}",
            '/lists/abc123',
        ], $client->resources);
    }
}
