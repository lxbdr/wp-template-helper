<?php

namespace Lxbdr\WpTemplateHelper\Tests;

use Lxbdr\WpTemplateHelper\WpTemplateHelper;
use WP_Mock;
use WP_Mock\Tools\TestCase;

class HeadingMethodsTest extends TestCase
{
    protected WpTemplateHelper $helper;

    public function setUp(): void
    {
        WP_Mock::setUp();

        $this->helper = new WpTemplateHelper([
            'title' => 'Main Title',
            'subtitle' => 'Subtitle with <special> chars',
            'nested' => [
                'heading' => 'Nested Heading'
            ],
            'empty_heading' => '',
            'null_heading' => null
        ]);

        // Set up esc_attr mock for attributes
        WP_Mock::userFunction('esc_attr', [
            'times' => '0+',
            'return_arg' => 0
        ]);
    }

    public function tearDown(): void
    {
        WP_Mock::tearDown();
    }

    /**
     * @dataProvider validHeadingTagsProvider
     */
    public function testStaticHeadingWithValidTags(string $tag)
    {
        $content = 'Test Content';
        $attributes = ['class' => 'test-class', 'id' => 'test-id'];

        $expected = "<{$tag} class=\"test-class\" id=\"test-id\">{$content}</{$tag}>";
        $this->assertEquals($expected, $this->helper::_heading($tag, $content, $attributes));
    }

    public function validHeadingTagsProvider(): array
    {
        return [
            'h1 tag' => ['h1'],
            'h2 tag' => ['h2'],
            'h3 tag' => ['h3'],
            'h4 tag' => ['h4'],
            'h5 tag' => ['h5'],
            'h6 tag' => ['h6'],
            'div tag' => ['div'],
            'span tag' => ['span']
        ];
    }

    public function testStaticHeadingWithInvalidTag()
    {
        $content = 'Test Content';
        $attributes = ['class' => 'test-class'];

        // Invalid tag should default to 'div'
        $expected = "<div class=\"test-class\">{$content}</div>";
        $this->assertEquals($expected, $this->helper::_heading('invalid', $content, $attributes));
    }

    public function testStaticHeadingWithEmptyAttributes()
    {
        $expected = "<h1>Test Content</h1>";
        $this->assertEquals($expected, $this->helper::_heading('h1', 'Test Content', []));
    }

    public function testStaticHeadingWithStringAttributes()
    {
        $attributes = 'class="manual-class" id="manual-id"';
        $expected = "<h1 {$attributes}>Test Content</h1>";
        $this->assertEquals($expected, $this->helper::_heading('h1', 'Test Content', $attributes));
    }

    /**
     * @dataProvider headingAttributesProvider
     */
    public function testStaticHeadingWithVariousAttributes(array $attributes, string $expectedAttrs)
    {
        $expected = "<h1 {$expectedAttrs}>Test Content</h1>";
        $this->assertEquals($expected, $this->helper::_heading('h1', 'Test Content', $attributes));
    }

    public function headingAttributesProvider(): array
    {
        return [
            'simple attributes' => [
                ['class' => 'test-class'],
                'class="test-class"'
            ],
            'multiple attributes' => [
                ['class' => 'test-class', 'id' => 'test-id', 'data-test' => 'value'],
                'class="test-class" id="test-id" data-test="value"'
            ],
            'boolean attributes' => [
                ['disabled' => true, 'class' => 'test-class'],
                'disabled="1" class="test-class"'
            ],
            'numeric attributes' => [
                ['width' => 100, 'height' => 200],
                'width="100" height="200"'
            ]
        ];
    }

    /**
     * @dataProvider instanceHeadingDataProvider
     */
    public function testInstanceHeading(string $key, string $expectedContent)
    {
        $result = $this->helper->_heading('h1', $key, ['class' => 'test-class']);
        $expected = "<h1 class=\"test-class\">{$expectedContent}</h1>";
        $this->assertEquals($expected, $result);
    }

    public function instanceHeadingDataProvider(): array
    {
        return [
            'simple title' => ['title', 'Main Title'],
            'nested heading' => ['nested.heading', 'Nested Heading'],
            'empty heading' => ['empty_heading', ''],
            'null heading' => ['null_heading', ''],
            'non-existent key' => ['non.existent.key', '']
        ];
    }

    public function testInstanceHeadingOutput()
    {
        ob_start();
        $this->helper->heading('h2', 'title', ['class' => 'test-class']);
        $output = ob_get_clean();

        $expected = "<h2 class=\"test-class\">Main Title</h2>";
        $this->assertEquals($expected, $output);
    }

    public function testStaticHeadingOutput()
    {
        ob_start();
        $this->helper::heading('h3', 'Test Content', ['class' => 'test-class']);
        $output = ob_get_clean();

        $expected = "<h3 class=\"test-class\">Test Content</h3>";
        $this->assertEquals($expected, $output);
    }

    public function testHeadingWithSpecialCharacters()
    {
        $result = $this->helper->_heading('h1', 'subtitle', ['class' => 'test-class']);
        $expected = "<h1 class=\"test-class\">Subtitle with <special> chars</h1>";
        $this->assertEquals($expected, $result);
    }

    public function testHeadingWithComplexAttributes()
    {
        $attributes = [
            'class' => 'heading-class another-class',
            'id' => 'unique-id',
            'data-test' => 'test-value',
            'aria-label' => 'Heading Label',
            'style' => 'color: red; font-size: 16px'
        ];

        $result = $this->helper->_heading('h1', 'title', $attributes);

        $expectedAttrs = implode(' ', array_map(
            fn($key, $value) => "{$key}=\"{$value}\"",
            array_keys($attributes),
            array_values($attributes)
        ));

        $expected = "<h1 {$expectedAttrs}>Main Title</h1>";
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider headingEdgeCasesProvider
     */
    public function testHeadingEdgeCases(string $tag, string $content, array $attributes, string $expected)
    {
        $result = $this->helper::_heading($tag, $content, $attributes);
        $this->assertEquals($expected, $result);
    }

    public function headingEdgeCasesProvider(): array
    {
        return [
            'empty content' => [
                'h1',
                '',
                ['class' => 'test'],
                '<h1 class="test"></h1>'
            ],
            'content with HTML' => [
                'h2',
                '<em>Emphasized</em> text',
                [],
                '<h2><em>Emphasized</em> text</h2>'
            ],
            'content with quotes' => [
                'h3',
                'Text with "quotes"',
                ['title' => 'Quoted "title"'],
                '<h3 title="Quoted "title"">Text with "quotes"</h3>'
            ],
            'multiple classes' => [
                'h4',
                'Content',
                ['class' => 'class1 class2 class3'],
                '<h4 class="class1 class2 class3">Content</h4>'
            ]
        ];
    }

    /**
     * Test performance with a large number of attributes
     */
    public function testHeadingPerformanceWithManyAttributes()
    {
        $attributes = array_combine(
            array_map(fn($i) => "data-attr-{$i}", range(1, 100)),
            array_map(fn($i) => "value-{$i}", range(1, 100))
        );

        $start = microtime(true);
        $result = $this->helper::_heading('div', 'Content', $attributes);
        $end = microtime(true);

        $this->assertStringContainsString('div', $result);
        $this->assertStringContainsString('Content', $result);
        $this->assertLessThan(0.1, $end - $start, 'Heading generation took too long');
    }
}
