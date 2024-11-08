<?php

namespace Lxbdr\WpTemplateHelper\Tests;

use Lxbdr\WpTemplateHelper\WpTemplateHelper;
use WP_Mock;
use WP_Mock\Tools\TestCase;

class WpEscapingJsXmlTest extends TestCase
{
    protected WpTemplateHelper $helper;

    public function setUp(): void
    {
        WP_Mock::setUp();

        $this->helper = new WpTemplateHelper([
            'js_string' => "var message = 'Hello'; alert(message);",
            'js_special' => "function test() { return 'quotes\" and \n newlines'; }",
            'js_nested' => [
                'value' => "document.write('hello & goodbye');"
            ],
            'xml_content' => '<person name="John & Jane" age="30"/>',
            'xml_special' => '< > & " \'',
            'xml_nested' => [
                'value' => '<data value="Special & chars"/>'
            ],
            'multiline' => "Line 1\nLine 2\rLine 3\r\nLine 4",
            'empty_value' => '',
            'null_value' => null
        ]);
    }

    public function tearDown(): void
    {
        WP_Mock::tearDown();
    }

    /**
     * Test JavaScript escaping with simple strings
     */
    public function testBasicJsEscaping()
    {
        $input = "var message = 'Hello'; alert(message);";
        $expected = "var message = \'Hello\'; alert(message);";

        WP_Mock::userFunction('esc_js', [
            'args' => [$input],
            'times' => 1,
            'return' => $expected
        ]);

        $this->assertEquals($expected, $this->helper->_js('js_string'));
    }

    /**
     * Test JavaScript escaping with special characters
     */
    public function testJsEscapingWithSpecialChars()
    {
        $input = "function test() { return 'quotes\" and \n newlines'; }";
        $expected = "function test() { return \'quotes\\\" and \\n newlines\'; }";

        WP_Mock::userFunction('esc_js', [
            'args' => [$input],
            'times' => 1,
            'return' => $expected
        ]);

        $this->assertEquals($expected, $this->helper->_js('js_special'));
    }

    /**
     * Test JavaScript escaping with nested values
     */
    public function testJsEscapingWithNestedValue()
    {
        $input = "document.write('hello & goodbye');";
        $expected = "document.write(\'hello & goodbye\');";

        WP_Mock::userFunction('esc_js', [
            'args' => [$input],
            'times' => 1,
            'return' => $expected
        ]);

        $this->assertEquals($expected, $this->helper->_js('js_nested.value'));
    }

    /**
     * Test XML escaping with simple content
     */
    public function testBasicXmlEscaping()
    {
        $input = '<person name="John & Jane" age="30"/>';
        $expected = '&lt;person name=&quot;John &amp; Jane&quot; age=&quot;30&quot;/&gt;';

        WP_Mock::userFunction('esc_xml', [
            'args' => [$input],
            'times' => 1,
            'return' => $expected
        ]);

        $this->assertEquals($expected, $this->helper->_xml('xml_content'));
    }

    /**
     * Test XML escaping with special characters
     */
    public function testXmlEscapingWithSpecialChars()
    {
        $input = '< > & " \'';
        $expected = '&lt; &gt; &amp; &quot; &apos;';

        WP_Mock::userFunction('esc_xml', [
            'args' => [$input],
            'times' => 1,
            'return' => $expected
        ]);

        $this->assertEquals($expected, $this->helper->_xml('xml_special'));
    }

    /**
     * Test XML escaping with nested values
     */
    public function testXmlEscapingWithNestedValue()
    {
        $input = '<data value="Special & chars"/>';
        $expected = '&lt;data value=&quot;Special &amp; chars&quot;/&gt;';

        WP_Mock::userFunction('esc_xml', [
            'args' => [$input],
            'times' => 1,
            'return' => $expected
        ]);

        $this->assertEquals($expected, $this->helper->_xml('xml_nested.value'));
    }

    /**
     * Test JavaScript escaping with multiline content
     */
    public function testJsEscapingWithMultilineContent()
    {
        $input = "Line 1\nLine 2\rLine 3\r\nLine 4";
        $expected = "Line 1\\nLine 2\\rLine 3\\r\\nLine 4";

        WP_Mock::userFunction('esc_js', [
            'args' => [$input],
            'times' => 1,
            'return' => $expected
        ]);

        $this->assertEquals($expected, $this->helper->_js('multiline'));
    }

    /**
     * Test handling of empty values
     */
    public function testEmptyValueHandling()
    {
        WP_Mock::userFunction('esc_js', [
            'args' => [''],
            'times' => 1,
            'return' => ''
        ]);

        WP_Mock::userFunction('esc_xml', [
            'args' => [''],
            'times' => 1,
            'return' => ''
        ]);

        $this->assertEquals('', $this->helper->_js('empty_value'));
        $this->assertEquals('', $this->helper->_xml('empty_value'));
    }

    /**
     * Test handling of null values
     */
    public function testNullValueHandling()
    {
        WP_Mock::userFunction('esc_js', [
            'args' => [''],
            'times' => 1,
            'return' => ''
        ]);

        WP_Mock::userFunction('esc_xml', [
            'args' => [''],
            'times' => 1,
            'return' => ''
        ]);

        $this->assertEquals('', $this->helper->_js('null_value'));
        $this->assertEquals('', $this->helper->_xml('null_value'));
    }

    /**
     * Test output buffering for direct echo methods
     */
    public function testEchoMethods()
    {
        WP_Mock::userFunction('esc_js', [
            'args' => ["var message = 'Hello'; alert(message);"],
            'times' => 1,
            'return' => "var message = \'Hello\'; alert(message);"
        ]);

        ob_start();
        $this->helper->js('js_string');
        $output = ob_get_clean();
        $this->assertEquals("var message = \'Hello\'; alert(message);", $output);

        WP_Mock::userFunction('esc_xml', [
            'args' => ['<person name="John & Jane" age="30"/>'],
            'times' => 1,
            'return' => '&lt;person name=&quot;John &amp; Jane&quot; age=&quot;30&quot;/&gt;'
        ]);

        ob_start();
        $this->helper->xml('xml_content');
        $output = ob_get_clean();
        $this->assertEquals('&lt;person name=&quot;John &amp; Jane&quot; age=&quot;30&quot;/&gt;', $output);
    }

    /**
     * Test handling of non-existent keys
     */
    public function testNonExistentKeys()
    {
        WP_Mock::userFunction('esc_js', [
            'args' => [''],
            'times' => 1,
            'return' => ''
        ]);

        WP_Mock::userFunction('esc_xml', [
            'args' => [''],
            'times' => 1,
            'return' => ''
        ]);

        $this->assertEquals('', $this->helper->_js('non.existent.key'));
        $this->assertEquals('', $this->helper->_xml('non.existent.key'));
    }
}
