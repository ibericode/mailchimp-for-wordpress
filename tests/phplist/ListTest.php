<?php
use PHPUnit\Framework\TestCase;

class ListTest extends TestCase {

	/**
	 * @covers PL4WP_PhpList_List::__construct
	 */
	public function test_constructor() {
		$id = 'abcdefg';
		$name = 'My PhpList List';
		$web_id = '500';
		$list = new PL4WP_PhpList_List( $id, $name, $web_id );

		self::assertAttributeEquals( $id, 'id', $list );
		self::assertAttributeEquals( $web_id, 'web_id', $list );
		self::assertAttributeEquals( $name, 'name', $list );
	}

	/**
	 * @covers PL4WP_PhpList_List::get_field_name_by_tag
	 */
	public function test_get_field_name_by_tag()
    {
        $id = 'abcdefg';
        $name = 'My PhpList List';
        $web_id = '500';
        $list = new PL4WP_PhpList_List($id, $name, $web_id);
        $list->merge_fields[] = new PL4WP_PhpList_Merge_Field('Email', 'email', 'EMAIL');

        // we should always know email field name
        self::assertStringStartsWith('Email', $list->get_field_name_by_tag('email'));
    }

}
