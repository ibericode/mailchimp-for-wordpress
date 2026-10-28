<?php

use PHPUnit\Framework\TestCase;

/**
 * Class FormTest
 *
 * @ignore
 */
class MC4WP_Form_Tags_Test extends TestCase
{
    public function test_replace_in_html(): void
    {
        $t = new MC4WP_Form_Tags();
        $t->register();

        $p     = new WP_Post();
        $p->ID = 1;
        global $post;
        $post = $p;
        $f    = new MC4WP_Form(1, $p, []);
        $e    = new MC4WP_Form_Element($f, 1, []);

        self::assertEquals('<script>alert(1);</script>', $t->replace_in_form_content('<script>alert(1);</script>', $f, $e));
        self::assertEquals('Post ID: 1', $t->replace_in_form_content('Post ID: {post property=\'ID\'}', $f, $e));

        $_GET['foo'] = 'bar';
        self::assertEquals('URL Parameter: bar', $t->replace_in_form_content('URL Parameter: {data key="foo"}', $f, $e));

        $_GET['foo'] = '<script>alert(1);</script>';
        self::assertEquals('URL Parameter: &lt;script&gt;alert(1);&lt;/script&gt;', $t->replace_in_form_content('URL Parameter: {data key="foo"}', $f, $e));
    }

    public function test_replace_in_html_attributes(): void
    {
        $t = new MC4WP_Form_Tags();
        $t->register();
        $f       = new MC4WP_Form(1, new WP_Post(), []);
        $e       = new MC4WP_Form_Element($f, 1, []);
        $replace = function ($html, $x, $y = '') use ($t, $f, $e) {
            $_GET['x'] = $x;
            $_GET['y'] = $y;
            return $t->replace_in_form_content($html, $f, $e);
        };

        // square brackets are encoded, so that a value can not form a shortcode
        self::assertEquals('<p>&#91;caption&#93;x&#91;/caption&#93;</p>', $replace('<p>{data key="x"}</p>', '[caption]x[/caption]'));
        self::assertEquals('<input value="&#91;x&#93;">', $replace('<input value="{data key=x}">', '[x]'));
        self::assertEquals('<input value=&#91;x&#93;>', $replace('<input value={data key=x}>', '[x]'));
        self::assertEquals('<a href="/?a=&#91;x&#93;">Continue</a>', $replace('<a href="/?a={data key=x}">Continue</a>', '[x]'));

        // text: colons are kept
        self::assertEquals('Subject: Re: hello', $replace('Subject: {data key="x"}', 'Re: hello'));
        self::assertEquals('<textarea>&lt;b&gt;</textarea>', $replace('<textarea>{data key="x"}</textarea>', '<b>'));

        // URL attribute: protocol is checked
        self::assertEquals('<a href="">Continue</a>', $replace('<a href="{data key=\'x\'}">Continue</a>', 'javascript:alert(1)'));
        self::assertEquals('<a href=\'\'>Continue</a>', $replace('<a href=\'{data key="x"}\'>Continue</a>', 'javascript:alert(1)'));
        self::assertEquals('<a href=" ">Continue</a>', $replace('<a href=" {data key=\'x\'}">Continue</a>', 'javascript:alert(1)'));
        self::assertEquals('<a href=>Continue</a>', $replace('<a href={data key=x}>Continue</a>', 'javascript:alert(1)'));
        self::assertEquals('<a/href="">Continue</a>', $replace('<a/href="{data key=\'x\'}">Continue</a>', 'javascript:alert(1)'));
        self::assertEquals('<a title="a > b" HREF="">Continue</a>', $replace('<a title="a > b" HREF="{data key=\'x\'}">Continue</a>', 'javascript:alert(1)'));
        self::assertEquals('<form action="">', $replace('<form action="{data key=\'x\'}">', 'javascript:alert(1)'));
        self::assertEquals('<a href="">Continue</a>', $replace('<a href="{data key=\'x\'}">Continue</a>', "\x01java\tscript:alert(1)"));
        self::assertEquals('<a href="">Continue</a>', $replace('<a href="{data key=\'x\'}">Continue</a>', '&#106;avascript:alert(1)'));
        self::assertEquals('<a href="https://example.com/?a=1&amp;b=2">Continue</a>', $replace('<a href="{data key=\'x\'}">Continue</a>', 'https://example.com/?a=1&b=2'));
        self::assertEquals('<a href="thanks.html">Continue</a>', $replace('<a href="{data key=\'x\'}">Continue</a>', 'thanks.html'));
        self::assertEquals('<a href=about&#32;me.html>Continue</a>', $replace('<a href={data key=x}>Continue</a>', 'about me.html'));
        self::assertEquals('<a href="/thanks/?ref=javascript:alert(1)">Continue</a>', $replace('<a href="/thanks/?ref={data key=\'x\'}">Continue</a>', 'javascript:alert(1)'));

        // URL attribute: protocol of the complete URL is checked, and all values are removed if it is not allowed
        self::assertEquals('<a href="about.html">Continue</a>', $replace('<a href="{data key=\'x\'}.html">Continue</a>', 'about'));
        self::assertEquals('<a href="about.html?a=b:c">Continue</a>', $replace('<a href="{data key=\'x\'}.html?a=b:c">Continue</a>', 'about'));
        self::assertEquals('<a href=".html">Continue</a>', $replace('<a href="{data key=\'x\'}.html">Continue</a>', 'javascript:alert(1)//'));
        self::assertEquals('<a href="://example.com">Continue</a>', $replace('<a href="{data key=\'x\'}://example.com">Continue</a>', 'javascript'));
        self::assertEquals('<a href="">Continue</a>', $replace('<a href="{data key=\'y\'}{data key=\'x\'}">Continue</a>', ':alert(1)', 'javascript'));
        self::assertEquals('<a href="java:alert(1)">Continue</a>', $replace('<a href="java{data key=\'x\'}:alert(1)">Continue</a>', 'script'));
        self::assertEquals('<a href="&#58;alert(1)">Continue</a>', $replace('<a href="{data key=\'x\'}&#58;alert(1)">Continue</a>', 'javascript'));
        self::assertEquals('<a href="&colon;alert(1)">Continue</a>', $replace('<a href="{data key=\'x\'}&colon;alert(1)">Continue</a>', 'javascript'));
        self::assertEquals('<a href="javascript:track(\'\')">Continue</a>', $replace('<a href="javascript:track(\'{data key=x}\')">Continue</a>', '\');alert(1);//'));

        // URL attribute in a tag that is still open at the end of the HTML
        self::assertEquals('<p>Hi</p><a href="', $replace('<p>Hi</p><a href="{data key=\'x\'}', 'javascript:alert(1)'));

        // event handler attribute: value is removed
        self::assertEquals('<a onclick="track(\'\')">Continue</a>', $replace('<a onclick="track(\'{data key=x}\')">Continue</a>', 'x\');alert(1);//'));

        // unquoted attribute: whitespace is escaped
        self::assertEquals('<input name=foo value=x&#32;onfocus=alert(1)&#32;autofocus>', $replace('<input name=foo value={data key=x}>', 'x onfocus=alert(1) autofocus'));

        // inside tag, but not in attribute value: value is removed
        self::assertEquals('<input >', $replace('<input {data key=x}>', 'onfocus=alert(1) autofocus'));
        self::assertEquals('<input value="a" >', $replace('<input value="a" {data key=x}>', 'onfocus=alert(1) autofocus'));

        // quoted attribute: quotes and whitespace are escaped
        self::assertEquals('<input value="&quot;&#32;onfocus=&quot;alert(1)">', $replace('<input value="{data key=x}">', '" onfocus="alert(1)'));
    }

    public function test_replace_in_html_without_html_api(): void
    {
        $t = new MC4WP_Form_Tags();
        $t->register();

        // WordPress < 6.2 has no HTML API, which makes MC4WP_Dynamic_Content_Tags::replace_in_html() use this instead
        $replace = Closure::bind(
            function ($html, $x) {
                $_GET['x'] = $x;
                return $this->replace($html, [$this, 'escape_context_free']);
            },
            $t,
            MC4WP_Dynamic_Content_Tags::class
        );

        self::assertEquals('Subject: Re:&#32;hello', $replace('Subject: {data key="x"}', 'Re: hello'));
        self::assertEquals('&lt;script&gt;', $replace('{data key="x"}', '<script>'));
        self::assertEquals('<a href="">Continue</a>', $replace('<a href="{data key=\'x\'}">Continue</a>', " java\tscript:alert(1)"));
        self::assertEquals('<a href="&amp;#106;avascript:alert(1)">Continue</a>', $replace('<a href="{data key=\'x\'}">Continue</a>', '&#106;avascript:alert(1)'));
        self::assertEquals('<input value=x&#32;onfocus=alert(1)>', $replace('<input value={data key=x}>', 'x onfocus=alert(1)'));
        self::assertEquals('<input value="&quot;&#32;onfocus=&quot;alert(1)">', $replace('<input value="{data key=x}">', '" onfocus="alert(1)'));
    }
}
