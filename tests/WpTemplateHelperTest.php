<?php


use Lxbdr\WpTemplateHelper\WpTemplateHelper;
use PHPUnit\Framework\TestCase;

class WpTemplateHelperTest extends TestCase {

	public function testItTests() {

		$this->assertTrue( true );

	}

	public function testItMocksWpFunctions() {

		$html    = '<h1>hello world</h1>';
		$escaped = '&gt;h1&lt;hello world&gt;/h1&lt;';

		$t = new WpTemplateHelper( [
			'foo' => $html
		] );


		WP_Mock::userFunction( 'esc_html' )
		       ->with( $html )
		       ->andReturn( $escaped );

		$this->assertEquals( $escaped, $t->_html( 'foo' ) );

	}

	public function testItCallsSameMethodStaticallyAndOnInstance() {

		$t = new WpTemplateHelper( [] );

		$arg = [
			'foo' => true,
			'bar' => false,
		];

		$expected = 'foo';

		$this->assertEquals( $expected, $t->_clsx( $arg ) );

		$this->assertEquals( $t->_clsx( $arg ), WpTemplateHelper::_clsx( $arg ) );
	}

	public function testItGetsNestedValues() {

		$t = new WpTemplateHelper( [
			'foo' => [
				'bar' => [
					'baz' => 'hello world'
				]
			]
		] );

		$this->assertEquals( 'hello world', $t->get( 'foo.bar.baz' ) );
	}

	public function testItOutputsImgFromId() {

		$id = 123;

		$t = new WpTemplateHelper( [
			'img' => $id
		] );

		$mock_output = '<img src="mock.jpg">';

		WP_Mock::userFunction( 'wp_get_attachment_image' )
		       ->withSomeOfArgs( $id )
		       ->andReturn( $mock_output );
//		       ->with( $id );

		ob_start();

		$t->img( 'img' );

		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( $mock_output, $output );

	}

	public function testItOutputsImgFromArray() {
		$url = 'https://picsum.photos/300/300.jpg';
		$alt = 'lorem';

		$t = new WpTemplateHelper( [
			'img' => [
				'id'  => 123,
				'url' => $url,
				'alt' => $alt,
			]
		] );

		ob_start();

		$t->img( 'img' );

		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( $url, $output );
		$this->assertStringContainsString( $alt, $output );
	}

	public function testItOutputsImgFromUrl() {
		$url = 'https://picsum.photos/300/300.jpg';

		$t = new WpTemplateHelper( [
			'img' => $url
		] );

		ob_start();

		$t->img( 'img' );

		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( $url, $output );
	}
}
