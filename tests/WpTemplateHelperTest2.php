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
        $this->assertEquals($expected, $this->helper->_withLineBreaks($lines));

        // Custom separator
        $expected = 'Line 1|Line 2|Line 3';
        $this->assertEquals($expected, $this->helper->_withLineBreaks($lines, '|'));
    }
}

class WpTemplateHelperImageTest extends TestCase
{
    protected WpTemplateHelper $helper;

    public function setUp(): void
    {
        WP_Mock::setUp();

        $this->helper = new WpTemplateHelper([
            'advanced_image' => [
                'base_img' => [
                    'url' => 'https://example.com/image.jpg',
                    'alt' => 'Base image'
                ],
                'sources' => [
                    [
                        'img_id' => 123,
                        'media_query' => '(min-width: 768px)'
                    ],
                    [
                        'img_id' => 456,
                        'media_query' => '(min-width: 1024px)'
                    ]
                ],
                'sizing' => 'width-full-height-auto',
                'custom_width' => '800px',
                'custom_height' => '600px',
                'object_fit' => 'cover',
                'display' => 'block'
            ]
        ]);
    }

    public function tearDown(): void
    {
        WP_Mock::tearDown();
    }

    public function testAdvancedImageRendering()
    {
        $this->setupImageMocks();

        $output = $this->helper->_advancedImg('advanced_image');

        // Check for container
        $this->assertStringContainsString('class="lx-img', $output);
        $this->assertStringContainsString('style="--width: 800px; --height: 600px"', $output);

        // Check for picture element
        $this->assertStringContainsString('<picture>', $output);

        // Check for source elements
        $this->assertStringContainsString('<source media="(min-width: 768px)"', $output);
        $this->assertStringContainsString('<source media="(min-width: 1024px)"', $output);

        // Check for base image
        $this->assertStringContainsString('src="https://example.com/image.jpg"', $output);
        $this->assertStringContainsString('alt="Base image"', $output);
    }

    public function testResponsiveImageRendering()
    {
        $this->setupImageMocks();

        $output = $this->helper->_responsiveImg('advanced_image');

        // Check for picture element structure
        $this->assertStringContainsString('<picture>', $output);
        $this->assertStringContainsString('</picture>', $output);

        // Check for source elements
        $this->assertStringContainsString('<source', $output);
        $this->assertStringContainsString('media="(min-width: 768px)"', $output);
        $this->assertStringContainsString('media="(min-width: 1024px)"', $output);
    }

    protected function setupImageMocks()
    {
        // Mock wp_get_attachment_image
        WP_Mock::userFunction('wp_get_attachment_image', [
            'times' => '0+',
            'return' => function($id, $size = 'full', $icon = false, $attr = '') {
                return "<img src=\"attachment-$id.jpg\" class=\"attachment-$size\">";
            },
        ]);

        // Mock wp_get_attachment_image_src
        WP_Mock::userFunction('wp_get_attachment_image_src', [
            'times' => '0+',
            'return' => function($id, $size = 'full') {
                return ["attachment-$id.jpg", 800, 600, true];
            },
        ]);

        // Mock wp_get_attachment_metadata
        WP_Mock::userFunction('wp_get_attachment_metadata', [
            'times' => '0+',
            'return' => function($id) {
                return [
                    'width' => 800,
                    'height' => 600,
                    'file' => "image-$id.jpg",
                    'sizes' => [
                        'thumbnail' => ['file' => "thumb-$id.jpg", 'width' => 150, 'height' => 150],
                        'medium' => ['file' => "medium-$id.jpg", 'width' => 300, 'height' => 225],
                    ]
                ];
            },
        ]);

        // Mock wp_calculate_image_sizes
        WP_Mock::userFunction('wp_calculate_image_sizes', [
            'times' => '0+',
            'return' => '(max-width: 800px) 100vw, 800px',
        ]);

        // Mock wp_calculate_image_srcset
        WP_Mock::userFunction('wp_calculate_image_srcset', [
            'times' => '0+',
            'return' => 'image-800.jpg 800w, image-400.jpg 400w',
        ]);

        // Mock image_hwstring
        WP_Mock::userFunction('image_hwstring', [
            'times' => '0+',
            'return' => 'width="800" height="600"',
        ]);

        // Mock esc_attr for various attribute escaping
        WP_Mock::userFunction('esc_attr', [
            'times' => '0+',
            'return_arg' => 0,
        ]);

        // Mock esc_url for URL escaping
        WP_Mock::userFunction('esc_url', [
            'times' => '0+',
            'return_arg' => 0,
        ]);
    }
}
