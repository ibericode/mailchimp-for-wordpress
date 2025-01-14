<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Class IntegrationTest
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
            []
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
            []
        ]);
        self::assertFalse($instance->checkbox_was_checked());

        // copy of request data is stored in constructor so we should create a new instance to replicate
        $_POST[ '_mc4wp_subscribe_' . $slug ] = 1;
        self::assertTrue($instance->checkbox_was_checked());
    }
}
