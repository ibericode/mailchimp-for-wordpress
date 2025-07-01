<?php

use PHPUnit\Framework\TestCase;

/**
 * Class FormTest
 * @ignore
 */
class MC4WP_Form_Tags_Test extends TestCase
{
    public function test_replace_in_html(): void
    {
        $t = new MC4WP_Form_Tags();
        $t->register();

        $p = new WP_Post();
        $p->ID = 1;
        global $post;
        $post = $p;
        $f = new MC4WP_Form(1, $p, []);
        $e = new MC4WP_Form_Element($f, 1, []);

        self::assertEquals('<script>alert(1);</script>', $t->replace_in_form_content('<script>alert(1);</script>', $f, $e));
        self::assertEquals('Post ID: 1', $t->replace_in_form_content('Post ID: {post property=\'ID\'}', $f, $e));

        $_GET['foo'] = 'bar';
        self::assertEquals('URL Parameter: bar', $t->replace_in_form_content('URL Parameter: {data key="foo"}', $f, $e));

        $_GET['foo'] = '<script>alert(1);</script>';
        self::assertEquals('URL Parameter: &lt;script&gt;alert(1);&lt;/script&gt;', $t->replace_in_form_content('URL Parameter: {data key="foo"}', $f, $e));
    }
}
