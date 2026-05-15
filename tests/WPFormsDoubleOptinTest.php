<?php

use PHPUnit\Framework\TestCase;

/**
 * Class WPFormsDoubleOptinTest
 *
 * Tests for the WPForms integration double opt-in feature.
 *
 * @covers MC4WP_WPForms_Integration
 */
class WPFormsDoubleOptinTest extends TestCase
{
    /**
     * Helper to create a testable WPForms integration instance.
     *
     * @param array $options Options to pass to constructor.
     * @return MC4WP_WPForms_Integration
     */
    private function create_integration(array $options = [])
    {
        return new MC4WP_WPForms_Integration('wpforms', $options);
    }

    /**
     * Helper to build mock form data.
     *
     * @param int    $checkbox_field_id  The mailchimp checkbox field ID.
     * @param string $double_optin_value The double opt-in value ('1' or '0'), or null to omit.
     * @return array
     */
    private function build_form_data($checkbox_field_id = 2, $double_optin_value = null)
    {
        $field_config = [
            'mailchimp_list' => 'abc123',
        ];

        if (null !== $double_optin_value) {
            $field_config['mailchimp_double_optin'] = $double_optin_value;
        }

        return [
            'id'     => 1,
            'fields' => [
                $checkbox_field_id => $field_config,
            ],
        ];
    }

    /**
     * Helper to build mock processed fields array.
     *
     * @param int    $checkbox_field_id The mailchimp checkbox field ID.
     * @param string $email             The email value.
     * @return array
     */
    private function build_fields($checkbox_field_id = 2, $email = 'test@example.com')
    {
        return [
            1 => [
                'type'  => 'email',
                'value' => $email,
            ],
            $checkbox_field_id => [
                'type'      => 'mailchimp',
                'value'     => 'Yes',
                'value_raw' => '1',
            ],
        ];
    }

    /**
     * Test that double_optin defaults to 1 when not set in field config.
     */
    public function test_default_double_optin()
    {
        $integration = $this->create_integration();
        $form_data   = $this->build_form_data(2, null);
        $fields      = $this->build_fields(2);

        // subscribe_from_wpforms should set double_optin to '1' by default
        // We can verify this by checking options are correctly set
        // Since subscribe() will fail without a real API, we use reflection
        // to check the options set during the method call

        $options_during_subscribe = null;

        $mock = $this->getMockBuilder(MC4WP_WPForms_Integration::class)
            ->setConstructorArgs(['wpforms', []])
            ->onlyMethods(['subscribe'])
            ->getMock();

        $mock->expects($this->once())
            ->method('subscribe')
            ->willReturnCallback(function ($data, $form_id) use ($mock, &$options_during_subscribe) {
                $options_during_subscribe = $mock->options;
                return true;
            });

        $mock->subscribe_from_wpforms(2, $fields, $form_data);

        self::assertNotNull($options_during_subscribe);
        self::assertEquals('1', $options_during_subscribe['double_optin']);
    }

    /**
     * Test that double_optin is set to '0' when configured for single opt-in.
     */
    public function test_single_optin()
    {
        $fields    = $this->build_fields(2);
        $form_data = $this->build_form_data(2, '0');

        $options_during_subscribe = null;

        $mock = $this->getMockBuilder(MC4WP_WPForms_Integration::class)
            ->setConstructorArgs(['wpforms', []])
            ->onlyMethods(['subscribe'])
            ->getMock();

        $mock->expects($this->once())
            ->method('subscribe')
            ->willReturnCallback(function ($data, $form_id) use ($mock, &$options_during_subscribe) {
                $options_during_subscribe = $mock->options;
                return true;
            });

        $mock->subscribe_from_wpforms(2, $fields, $form_data);

        self::assertNotNull($options_during_subscribe);
        self::assertEquals('0', $options_during_subscribe['double_optin']);
    }

    /**
     * Test that original options are restored after subscribe_from_wpforms.
     */
    public function test_options_restored_after_subscribe()
    {
        $original_options = [
            'double_optin' => 1,
            'lists'        => ['original_list'],
            'enabled'      => 1,
        ];

        $mock = $this->getMockBuilder(MC4WP_WPForms_Integration::class)
            ->setConstructorArgs(['wpforms', $original_options])
            ->onlyMethods(['subscribe'])
            ->getMock();

        $mock->method('subscribe')->willReturn(true);

        $fields    = $this->build_fields(2);
        $form_data = $this->build_form_data(2, '0');

        // Store options before call
        $options_before = $mock->options;

        $mock->subscribe_from_wpforms(2, $fields, $form_data);

        // Options should be restored to original values
        self::assertEquals($options_before['double_optin'], $mock->options['double_optin']);
        self::assertEquals($options_before['lists'], $mock->options['lists']);
    }

    /**
     * Test that listen_to_wpforms triggers subscription for checked mailchimp fields.
     */
    public function test_listen_to_wpforms_triggers_subscription()
    {
        $fields = $this->build_fields(2);
        $form_data = $this->build_form_data(2, '0');

        $mock = $this->getMockBuilder(MC4WP_WPForms_Integration::class)
            ->setConstructorArgs(['wpforms', []])
            ->onlyMethods(['subscribe'])
            ->getMock();

        $mock->expects($this->once())
            ->method('subscribe')
            ->willReturn(true);

        // Simulate WPForms process hook
        $mock->listen_to_wpforms($fields, [], $form_data);
    }

    /**
     * Test that listen_to_wpforms does NOT trigger subscription when checkbox is unchecked.
     */
    public function test_listen_to_wpforms_skips_unchecked()
    {
        $fields = [
            1 => [
                'type'  => 'email',
                'value' => 'test@example.com',
            ],
            2 => [
                'type'      => 'mailchimp',
                'value'     => 'No',
                'value_raw' => '',  // unchecked
            ],
        ];
        $form_data = $this->build_form_data(2, '1');

        $mock = $this->getMockBuilder(MC4WP_WPForms_Integration::class)
            ->setConstructorArgs(['wpforms', []])
            ->onlyMethods(['subscribe'])
            ->getMock();

        $mock->expects($this->never())
            ->method('subscribe');

        $mock->listen_to_wpforms($fields, [], $form_data);
    }
}
