<?php
use PHPUnit\Framework\TestCase;

class ListTest extends TestCase
{

    /**
     * @covers MC4WP_MailChimp_List::__construct
     */
    public function test_constructor()
    {
        $id = 'abcdefg';
        $name = 'My Mailchimp List';
        $web_id = '500';
        $list = new MC4WP_MailChimp_List($id, $name, $web_id);

        self::assertEquals($list->id, $id);
        self::assertEquals($list->web_id, $web_id);
        self::assertEquals($list->name, $name);
    }

    /**
     * @covers MC4WP_MailChimp_List::get_field_name_by_tag
     */
    public function test_get_field_name_by_tag()
    {
        $id = 'abcdefg';
        $name = 'My Mailchimp List';
        $web_id = '500';
        $list = new MC4WP_MailChimp_List($id, $name, $web_id);
        $list->merge_fields[] = new MC4WP_MailChimp_Merge_Field('Email', 'email', 'EMAIL');

        // we should always know email field name
        self::assertInstanceOf(MC4WP_MailChimp_Merge_Field::class, $list->get_field_by_tag('email'));
    }
}
