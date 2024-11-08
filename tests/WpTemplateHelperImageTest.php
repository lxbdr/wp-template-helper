<?php

namespace Lxbdr\WpTemplateHelper\Tests;

use Lxbdr\WpTemplateHelper\WpTemplateHelper;
use WP_Mock;
use WP_Mock\Tools\TestCase;

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
