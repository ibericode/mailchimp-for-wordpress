<?php

use PHPUnit\Framework\TestCase;

class MailchimpSubscriberTest extends TestCase
{
    public function test_default_properties()
    {
        $subscriber = new MC4WP_MailChimp_Subscriber();

        self::assertSame('', $subscriber->email_address);
        self::assertSame([], $subscriber->interests);
        self::assertSame([], $subscriber->merge_fields);
        self::assertSame('pending', $subscriber->status);
        self::assertSame('html', $subscriber->email_type);
        self::assertNull($subscriber->ip_signup);
        self::assertNull($subscriber->language);
        self::assertNull($subscriber->vip);
        self::assertSame([], $subscriber->tags);
        self::assertSame([], $subscriber->marketing_permissions);
    }

    public function test_to_array_default_values()
    {
        $subscriber = new MC4WP_MailChimp_Subscriber();
        $array      = $subscriber->to_array();

        self::assertIsArray($array);
        self::assertArrayHasKey('email_address', $array);
        self::assertArrayHasKey('interests', $array);
        self::assertArrayHasKey('merge_fields', $array);
        self::assertArrayHasKey('status', $array);
        self::assertArrayHasKey('email_type', $array);
        self::assertArrayHasKey('tags', $array);

        // Null properties must be excluded.
        self::assertArrayNotHasKey('ip_signup', $array);
        self::assertArrayNotHasKey('language', $array);
        self::assertArrayNotHasKey('vip', $array);

        // Empty marketing_permissions property must be excluded.
        self::assertArrayNotHasKey('marketing_permissions', $array);
    }

    public function test_to_array_with_custom_values()
    {
        $subscriber                = new MC4WP_MailChimp_Subscriber();
        $subscriber->email_address = 'john.doe@example.com';
        $subscriber->status        = 'subscribed';
        $subscriber->email_type    = 'text';
        $subscriber->interests     = [ 'interest_1' => true, 'interest_2' => false ];
        $subscriber->merge_fields  = [ 'FNAME' => 'John', 'LNAME' => 'Doe' ];
        $subscriber->tags          = [ 'Lead', 'Customer' ];

        $array = $subscriber->to_array();

        self::assertSame('john.doe@example.com', $array['email_address']);
        self::assertSame('subscribed', $array['status']);
        self::assertSame('text', $array['email_type']);
        self::assertSame([ 'interest_1' => true, 'interest_2' => false ], $array['interests']);
        self::assertSame([ 'FNAME' => 'John', 'LNAME' => 'Doe' ], $array['merge_fields']);
        self::assertSame([ 'Lead', 'Customer' ], $array['tags']);
    }

    public function test_to_array_includes_marketing_permissions_when_not_empty()
    {
        $subscriber                            = new MC4WP_MailChimp_Subscriber();
        $permissions                           = [
            [
                'marketing_permission_id' => 'permission_123',
                'enabled'                 => true,
            ],
        ];
        $subscriber->marketing_permissions     = $permissions;

        $array = $subscriber->to_array();

        self::assertArrayHasKey('marketing_permissions', $array);
        self::assertSame($permissions, $array['marketing_permissions']);
    }

    public function test_to_array_with_nullable_fields_populated()
    {
        $subscriber            = new MC4WP_MailChimp_Subscriber();
        $subscriber->ip_signup = '192.168.1.100';
        $subscriber->language  = 'es';
        $subscriber->vip       = true;

        $array = $subscriber->to_array();

        self::assertArrayHasKey('ip_signup', $array);
        self::assertSame('192.168.1.100', $array['ip_signup']);
        self::assertArrayHasKey('language', $array);
        self::assertSame('es', $array['language']);
        self::assertArrayHasKey('vip', $array);
        self::assertTrue($array['vip']);
    }

    public function test_to_array_filters_out_explicit_null_values()
    {
        $subscriber                = new MC4WP_MailChimp_Subscriber();
        $subscriber->email_address = 'jane@example.com';
        $subscriber->status        = null;
        $subscriber->email_type    = null;
        $subscriber->interests     = null;
        $subscriber->tags          = null;

        $array = $subscriber->to_array();

        self::assertArrayHasKey('email_address', $array);
        self::assertArrayNotHasKey('status', $array);
        self::assertArrayNotHasKey('email_type', $array);
        self::assertArrayNotHasKey('interests', $array);
        self::assertArrayNotHasKey('tags', $array);
    }

    public function test_to_array_preserves_falsy_non_null_values()
    {
        $subscriber      = new MC4WP_MailChimp_Subscriber();
        $subscriber->vip = false;

        $array = $subscriber->to_array();

        self::assertArrayHasKey('vip', $array);
        self::assertFalse($array['vip']);
    }
}
