<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

// Mock WP functions
if (! function_exists('add_filter')) {
    function add_filter()
    {
    }
}

if (! function_exists('add_action')) {
    function add_action()
    {
    }
}

if (! function_exists('__')) {
    function __($text, $domain = 'default')
    {
        return $text;
    }
}

if (! function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default')
    {
        return $text;
    }
}

// Mock Parent Class
if (! class_exists('NF_Abstracts_Input')) {
    abstract class NF_Abstracts_Input
    {
        protected $_settings = []; // Needed for child constructor access

        public function __construct()
        {
            // Mock constructor
        }

        public function validate($field, $data)
        {
            return [];
        }
    }
}

require_once __DIR__ . '/../integrations/ninja-forms/class-field.php';

class NinjaFormsValidationTest extends \PHPUnit\Framework\TestCase
{
    public function testRequiredValidationFailsOnEmpty()
    {
        $field = new MC4WP_Ninja_Forms_Field();

        $field_data = [
            'required' => 1,
            'value' => 0,
        ];
        $form_data = [];

        $errors = $field->validate($field_data, $form_data);

        $this->assertIsArray($errors);
        $this->assertArrayHasKey('slug', $errors, 'Error slug should be present');
        $this->assertEquals('required-error', $errors['slug']);
    }

    public function testRequiredValidationPassesOnChecked()
    {
        $field = new MC4WP_Ninja_Forms_Field();

        $field_data = [
            'required' => 1,
            'value' => 1,
        ];
        $form_data = [];

        $errors = $field->validate($field_data, $form_data);

        $this->assertEmpty($errors, 'Validation should pass for required checked field');
    }

    public function testOptionalValidationPassesOnUnchecked()
    {
        $field = new MC4WP_Ninja_Forms_Field();

        $field_data = [
            'required' => 0,
            'value' => 0,
        ];
        $form_data = [];

        $errors = $field->validate($field_data, $form_data);

        $this->assertEmpty($errors, 'Validation should pass for optional unchecked field');
    }
}
