<?php

namespace Lxbdr\WpTemplateHelper\Tests;

use WP_Mock;
use PHPUnit\Framework\TestCase;
use Lxbdr\WpTemplateHelper\WpTemplateHelper;

class WpTemplateHelperImageTest extends TestCase
{
    protected WpTemplateHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();
        WP_Mock::setUp();

        // Reset helper for each test with fresh test data
        $this->helper = new WpTemplateHelper([
            'simple_image' => 123,
            'url_image' => 'http://example.com/image.jpg',
            'array_image' => [
                'url' => 'http://example.com/image.jpg',
                'alt' => 'Test Alt Text'
            ],
            'responsive_image' => [
                'base_img' => [
                    'url' => 'http://example.com/mobile.jpg',
                    'alt' => 'Mobile Image'
                ],
                'sources' => [
                    [
                        'img_id' => 456,
                        'media_query' => '(min-width: 768px)'
                    ]
                ]
            ],
            'advanced_image' => [
                'sizing' => 'width-full-height-full',
                'custom_width' => '500px',
                'custom_height' => '300px',
                'focal_x' => '30',
                'focal_y' => '70',
                'object_fit' => 'cover',
                'display' => 'block',
                'base_img' => [
                    'url' => 'http://example.com/image.jpg',
                    'alt' => 'Advanced Image'
                ],
                'sources' => []
            ]
        ]);
    }

    protected function tearDown(): void
    {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function testInstanceImgMethodWithNumericId()
    {
        WP_Mock::userFunction('wp_get_attachment_image', [
            'args' => [123, 'full', false, ''],
            'times' => 1,
            'return' => '<img src="test.jpg" alt="Test">'
        ]);

        $result = $this->helper->_img('simple_image');
        $this->assertEquals('<img src="test.jpg" alt="Test">', $result);
    }

    public function testInstanceImgMethodWithUrl()
    {
        WP_Mock::userFunction('esc_url', [
            'args' => ['http://example.com/image.jpg'],
            'return' => 'http://example.com/image.jpg'
        ]);

        WP_Mock::userFunction('esc_attr', [
            'args' => [''],
            'return' => ''
        ]);

        $result = $this->helper->_img('url_image');
        $this->assertStringContainsString('http://example.com/image.jpg', $result);
        $this->assertStringContainsString('alt=""', $result);
    }

    public function testInstanceImgMethodWithArrayData()
    {
        WP_Mock::userFunction('esc_url', [
            'args' => ['http://example.com/image.jpg'],
            'return' => 'http://example.com/image.jpg'
        ]);

        WP_Mock::userFunction('esc_attr', [
            'args' => ['Test Alt Text'],
            'return' => 'Test Alt Text'
        ]);

        $result = $this->helper->_img('array_image');
        $this->assertStringContainsString('http://example.com/image.jpg', $result);
        $this->assertStringContainsString('Test Alt Text', $result);
    }

    public function testInstanceResponsiveImg()
    {
        $this->setupWordPressMocks();

        $result = $this->helper->_responsiveImg('responsive_image');

        $this->assertStringContainsString('<picture>', $result);
        $this->assertStringContainsString('<source', $result);
        $this->assertStringContainsString('(min-width: 768px)', $result);
        $this->assertStringContainsString('http://example.com/mobile.jpg', $result);
    }

    public function testInstanceAdvancedImg()
    {
        // Mock WordPress escaping functions
        WP_Mock::userFunction('esc_attr', [
            'return_arg' => true
        ]);

        WP_Mock::userFunction('esc_url', [
            'return_arg' => true
        ]);

        $result = $this->helper->_advancedImg('advanced_image');

        // Test container classes
        $this->assertStringContainsString('lx-img--full-width', $result);
        $this->assertStringContainsString('lx-img--full-height', $result);
        $this->assertStringContainsString('lx-img--cover', $result);
        $this->assertStringContainsString('lx-img--block', $result);

        // Test custom styles
        $this->assertStringContainsString('--width: 500px', $result);
        $this->assertStringContainsString('--height: 300px', $result);
        $this->assertStringContainsString('--focal-x: 30%', $result);
        $this->assertStringContainsString('--focal-y: 70%', $result);

        // Test image content
        $this->assertStringContainsString('http://example.com/image.jpg', $result);
        $this->assertStringContainsString('Advanced Image', $result);
    }

    public function testImgOutputMethod()
    {
        WP_Mock::userFunction('wp_get_attachment_image', [
            'return' => '<img src="test.jpg" alt="Test">'
        ]);

        ob_start();
        $this->helper->img('simple_image');
        $output = ob_get_clean();

        $this->assertEquals('<img src="test.jpg" alt="Test">', $output);
    }

    public function testAdvancedImgOutputMethod()
    {
        WP_Mock::userFunction('esc_attr', [
            'return_arg' => true
        ]);

        WP_Mock::userFunction('esc_url', [
            'return_arg' => true
        ]);

        ob_start();
        $this->helper->advancedImg('advanced_image');
        $output = ob_get_clean();

        $this->assertStringContainsString('lx-img', $output);
        $this->assertStringContainsString('http://example.com/image.jpg', $output);
    }

    public function testResponsiveImgOutputMethod()
    {
        $this->setupWordPressMocks();

        ob_start();
        $this->helper->responsiveImg('responsive_image');
        $output = ob_get_clean();

        $this->assertStringContainsString('<picture>', $output);
        $this->assertStringContainsString('http://example.com/mobile.jpg', $output);
    }

    public function testImgWithCustomAttributes()
    {
        WP_Mock::userFunction('esc_url', [
            'return_arg' => true
        ]);

        WP_Mock::userFunction('esc_attr', [
            'return_arg' => true
        ]);

        $result = $this->helper->_img('url_image', 'full', [
            'class' => 'custom-class',
            'data-test' => 'test-value'
        ]);

        $this->assertStringContainsString('class="custom-class"', $result);
        $this->assertStringContainsString('data-test="test-value"', $result);
    }

    public function testAdvancedImgCssGeneration()
    {
        $css = WpTemplateHelper::getAdvancedImgCss();

        $this->assertStringContainsString('.lx-img {', $css);
        $this->assertStringContainsString('--width: auto;', $css);
        $this->assertStringContainsString('--height: auto;', $css);
        $this->assertStringContainsString('.lx-img--full-width', $css);
        $this->assertStringContainsString('.lx-img--constrained', $css);
    }

    private function setupWordPressMocks(): void
    {
        WP_Mock::userFunction('wp_get_attachment_image_src', [
            'args' => [456, 'full'],
            'return' => ['http://example.com/desktop.jpg', 1200, 800]
        ]);

        WP_Mock::userFunction('wp_get_attachment_metadata', [
            'args' => [456],
            'return' => [
                'width' => 1200,
                'height' => 800,
                'file' => 'desktop.jpg',
                'sizes' => []
            ]
        ]);

        WP_Mock::userFunction('image_hwstring', [
            'return' => 'width="1200" height="800"'
        ]);

        WP_Mock::userFunction('wp_calculate_image_sizes', [
            'return' => '100vw'
        ]);

        WP_Mock::userFunction('wp_calculate_image_srcset', [
            'return' => 'http://example.com/desktop.jpg 1200w'
        ]);

        WP_Mock::userFunction('esc_url', [
            'return_arg' => true
        ]);

        WP_Mock::userFunction('esc_attr', [
            'return_arg' => true
        ]);

        WP_Mock::userFunction('absint', [
            'return_arg' => true
        ]);
    }
}
