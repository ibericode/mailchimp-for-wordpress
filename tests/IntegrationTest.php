<?php

use PHPUnit\Framework\TestCase;

/**
 * Class IntegrationTest
 *
 * @ignore
 */
class IntegrationTest extends TestCase
{
    /**
     * @covers MC4WP_Integration::__construct
     */
    public function test_constructor()
    {
        $slug = 'my-integration';

        $instance = $this->getMockForAbstractClass('MC4WP_Integration', [
            $slug,
            [],
        ]);

        self::assertEquals($slug, $instance->slug);
    }

    /**
     * @covers MC4WP_Integration::checkbox_was_checked
     */
    public function test_checkbox_was_checked()
    {
        $slug = 'my-integration';

        /** @var MC4WP_Integration $instance */
        $instance = $this->getMockForAbstractClass('MC4WP_Integration', [
            $slug,
            [],
        ]);
        self::assertFalse($instance->checkbox_was_checked());

        // copy of request data is stored in constructor so we should create a new instance to replicate
        $_POST[ '_mc4wp_subscribe_' . $slug ] = 1;
        self::assertTrue($instance->checkbox_was_checked());
    }

    /**
     * @covers MC4WP_Integration::subscribe
     */
    public function test_subscribe_queues_runtime_options()
    {
        global $mock_scheduled_events;
        $mock_scheduled_events = [];

        $instance = new class ('my-integration', [
            'double_optin'      => 0,
            'update_existing'   => 1,
            'replace_interests' => 1,
            'lists'             => [ 'abc123' ],
        ]) extends MC4WP_Sample_Integration {
            public function subscribe_public(array $data, $related_object_id = 0)
            {
                return $this->subscribe($data, $related_object_id);
            }

            protected function get_log()
            {
                return new class () {
                    public function warning($message)
                    {
                    }

                    public function error($message)
                    {
                    }

                    public function info($message)
                    {
                    }
                };
            }
        };

        self::assertTrue($instance->subscribe_public([ 'EMAIL' => 'test@example.com' ], 42));
        self::assertCount(1, $mock_scheduled_events);
        self::assertEquals('mc4wp_integration_subscribe', $mock_scheduled_events[0]['hook']);

        $args = $mock_scheduled_events[0]['args'][0];

        self::assertEquals('my-integration', $args['integration_slug']);
        self::assertEquals(42, $args['related_object_id']);
        self::assertEquals([ 'abc123' ], $args['list_ids']);
        self::assertSame(0, $args['options']['double_optin']);
        self::assertSame(1, $args['options']['update_existing']);
        self::assertSame(1, $args['options']['replace_interests']);
    }
}
