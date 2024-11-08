<?php

namespace Lxbdr\WpTemplateHelper\Tests;

use Lxbdr\WpTemplateHelper\WpTemplateHelper;
use WP_Mock;
use WP_Mock\Tools\TestCase;

class WpTemplateHelperTest2 extends TestCase
{
    protected WpTemplateHelper $helper;
    protected array $testData;

    public function setUp(): void
    {
        WP_Mock::setUp();

        $this->testData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'nested' => [
                'key' => 'value',
                'deeper' => [
                    'item' => 'nested-value'
                ]
            ],
            'image' => [
                'url' => 'https://example.com/image.jpg',
                'alt' => 'Test Image'
            ],
            'html_content' => '<p>This is <strong>formatted</strong> content</p>'
        ];

        $this->helper = new WpTemplateHelper($this->testData);
    }

    public function tearDown(): void
    {
        WP_Mock::tearDown();
    }

    public function testConstructorAndGetData()
    {
        $this->assertEquals($this->testData, $this->helper->getData());
    }

    public function testFromObject()
    {
        $obj = (object)$this->testData;
        $helper = WpTemplateHelper::fromObject($obj);
        $this->assertEquals($this->testData, $helper->getData());
    }

    public function testNestedDataAccess()
    {
        $this->assertEquals('value', $this->helper->get('nested.key'));
        $this->assertEquals('nested-value', $this->helper->get('nested.deeper.item'));
        $this->assertEquals('', $this->helper->get('non.existent.key'));
    }

    public function testIssetNested()
    {
        $helper = new WpTemplateHelper([
            'user' => [
                'profile' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'settings' => [
                        'newsletter' => true,
                        'theme' => 'dark'
                    ],
                    'nullValue' => null,
                    'emptyString' => '',
                    'zero' => 0,
                    'false' => false
                ]
            ],
            'empty_array' => [],
            'top_level' => 'value'
        ]);

        // Test existing nested keys
        $this->assertTrue($helper->has('user.profile.name'));
        $this->assertTrue($helper->has('user.profile.settings.newsletter'));
        $this->assertTrue($helper->has('user.profile.settings.theme'));

        // Test top level keys
        $this->assertTrue($helper->has('top_level'));
        $this->assertTrue($helper->has('empty_array'));

        // Test non-existent keys
        $this->assertFalse($helper->has('user.profile.nonexistent'));
        $this->assertFalse($helper->has('nonexistent.key'));
        $this->assertFalse($helper->has('user.profile.settings.nonexistent'));

        // Test edge cases
        $this->assertTrue($helper->has('user.profile.nullValue')); // Should return true for null values
        $this->assertTrue($helper->has('user.profile.emptyString')); // Should return true for empty string
        $this->assertTrue($helper->has('user.profile.zero')); // Should return true for zero
        $this->assertTrue($helper->has('user.profile.false')); // Should return true for false

        // Test partial paths
        $this->assertTrue($helper->has('user.profile'));
        $this->assertTrue($helper->has('user.profile.settings'));

        // Test empty key
        $this->assertFalse($helper->has(''));

        // Test custom separator
        $helper = new WpTemplateHelper([
            'deeply' => [
                'nested' => [
                    'key' => 'value'
                ]
            ]
        ]);

        $this->assertTrue($helper->has('deeply/nested/key', '/'));
        $this->assertFalse($helper->has('deeply/wrong/key', '/'));
    }

    public function testHasMethod()
    {
        $this->assertTrue($this->helper->has('name'));
        $this->assertTrue($this->helper->has('nested.key'));
        $this->assertFalse($this->helper->has('non.existent'));
    }

    public function testEmptyAndNotEmpty()
    {
        $helper = new WpTemplateHelper([
            'empty_string' => '',
            'zero' => 0,
            'null' => null,
            'valid' => 'content'
        ]);

        $this->assertTrue($helper->empty('empty_string'));
        $this->assertTrue($helper->empty('zero'));
        $this->assertTrue($helper->empty('null'));
        $this->assertFalse($helper->empty('valid'));

        $this->assertFalse($helper->notEmpty('empty_string'));
        $this->assertTrue($helper->notEmpty('valid'));
    }

    public function testEscaping()
    {
        $testHtml = '<p>Test & "quotes"</p>';
        $testUrl = 'https://example.com?param=value&other=123';

        $helper = new WpTemplateHelper([
            'html' => $testHtml,
            'url' => $testUrl,
        ]);

        WP_Mock::userFunction('esc_attr', [
            'args' => [$testHtml],
            'times' => 1,
            'return' => htmlspecialchars($testHtml, ENT_QUOTES, 'UTF-8'),
        ]);

        WP_Mock::userFunction('esc_url', [
            'args' => [$testUrl],
            'times' => 1,
            'return' => $testUrl,
        ]);

        $this->assertEquals(
            htmlspecialchars($testHtml, ENT_QUOTES, 'UTF-8'),
            $helper->_attr('html')
        );

        $this->assertEquals(
            $testUrl,
            $helper->_url('url')
        );
    }

    public function testClsxMethod()
    {
        $cases = [
            // Simple strings
            [['btn', 'btn-primary'], 'btn btn-primary'],

            // Arrays with conditions
            [[
                'btn' => true,
                'active' => false,
                'disabled' => true
            ], 'btn disabled'],

            // Mixed input
            [
                [
                    'btn',
                    ['primary' => true, 'secondary' => false],
                    'active'
                ],
                'btn primary active'
            ]
        ];

        foreach ($cases as [$input, $expected]) {
            $this->assertEquals($expected, $this->helper->_clsx($input));
        }
    }

    public function testAttributesMethod()
    {
        $attributes = [
            'class' => 'btn btn-primary',
            'id' => 'submit-btn',
            'data-action' => 'submit'
        ];

        WP_Mock::userFunction('esc_attr', [
            'times' => 3,
            'return_arg' => 0,
        ]);

        $expected = 'class="btn btn-primary" id="submit-btn" data-action="submit"';
        $this->assertEquals($expected, $this->helper->_attributes($attributes));
    }

    public function testMaybeAnchorTag()
    {
        $url = 'https://example.com';
        $attributes = ['class' => 'link'];

        WP_Mock::userFunction('esc_url', [
            'args' => [$url],
            'times' => 1,
            'return' => $url,
        ]);

        // Test with valid link
        $tag = $this->helper->maybeAnchorTag($url, $attributes);
        ob_start();
        $tag->open();
        $content = ob_get_clean();
        $this->assertStringContainsString('<a href="https://example.com"', $content);
        $this->assertStringContainsString('class="link"', $content);

        // Test with empty link
        $tag = $this->helper->maybeAnchorTag('', ['class' => 'no-link'], 'span');
        ob_start();
        $tag->open();
        $content = ob_get_clean();
        $this->assertStringContainsString('<span', $content);
        $this->assertStringContainsString('class="no-link"', $content);
    }

    public function testWithLineBreaks()
    {
        $lines = ['Line 1', '', 'Line 2', 'Line 3'];
        $expected = 'Line 1<br/>Line 2<br/>Line 3';
        $this->assertEquals($expected, WpTemplateHelper::_withLineBreaks($lines));

        // Custom separator
        $expected = 'Line 1|Line 2|Line 3';
        $this->assertEquals($expected, WpTemplateHelper::_withLineBreaks($lines, '|'));
    }
}

