<?php

namespace Lxbdr\WpTemplateHelper;

interface WpTemplateHelperElement {

	public function open();

	public function close();

}

/**
 * @method static void clsx( ...$arguments )
 * @method static string _clsx( ...$arguments )
 * @method void clsx( ...$arguments )
 * @method string _clsx( ...$arguments )
 * @method static void style( array $arr )
 * @method static string _style( array $arr )
 * @method void style( array $arr )
 * @method string _style( array $arr )
 * @method static void attributes( array $arr )
 * @method static string _attributes( array $arr )
 * @method void attributes( array $arr )
 * @method string _attributes( array $arr )
 * @method static WpTemplateHelperElement maybeAnchorTag( string $link, $atts, string $alternative_tag )
 * @method WpTemplateHelperElement maybeAnchorTag( string $link, $atts, string $alternative_tag )
 */
class WpTemplateHelper implements \ArrayAccess {

	protected array $data = [];

	public function __construct( array $data = [] ) {
		$this->data = $data;
	}

	public static function fromObject( object $data ): WpTemplateHelper {
		return new self( get_object_vars( $data ) );
	}

	public function toArray(): array {
		return $this->data;
	}

	public function has( string $key ): bool {
		return $this->getNested( $key ) !== null;
	}

	public function notEmpty( string $key ): bool {
		return ! $this->empty( $key );
	}

	public function empty( string $key ): bool {
		return empty( $this->getNested( $key ) );
	}

	/**
	 * @param $key
	 *
	 * @return mixed|string value or empty string
	 */
	public function get( $key ) {
		return $this->getNested( $key ) ?? '';
	}

	public function set( string $key, $value ) {
		if ( strpos( $key, '.' ) !== false ) {
			throw new \Exception( "set nested key is not supported yet." );
		}
		$this->data[ $key ] = $value;
	}

	/**
	 * Get a nested value by specifying the key as parent.child
	 *
	 * return null if value does not exist
	 *
	 * @param string $key
	 * @param string $separator
	 *
	 * @return mixed
	 */
	protected function getNested( string $key, string $separator = '.' ) {
		return array_reduce(
			explode( $separator, $key ),
			function ( $agg, $value ) {
				return $agg[ $value ] ?? null;
			},
			$this->data
		);
	}

	public function getData(): array {
		return $this->data;
	}

	public function setData( array $data ) {
		$this->data = $data;
	}

	public function dump( $key = null ) {
		if ( ! $key ) {
			var_dump( $this->data );

			return;
		}
		var_dump( $this->getNested( $key ) );
	}

	public function _attr( string $key ): string {
		return \esc_attr( $this->getNested( $key ) ?? '' );
	}

	public function attr( string $key ) {
		echo $this->_attr( $key );
	}

	public function _url( string $key ): string {
		return \esc_url( $this->getNested( $key ) ?? '' );
	}

	public function url( string $key ): void {
		echo $this->_url( $key );
	}

	public function _html( string $key ): string {
		return \esc_html( $this->getNested( $key ) ?? '' );
	}

	public function html( string $key ) {
		echo $this->_html( $key );
	}

	public function _safeHtml( string $key ): string {
		return \wp_kses_post( $this->getNested( $key ) ?? '' );
	}

	public function safeHtml( string $key ) {
		echo $this->_safeHtml( $key );
	}

	public function sprintf( $str, $key ): string {
		return sprintf( $str, $this->getNested( $key ) ?? '' );
	}

	public function printf( $str, $key ) {
		echo $this->sprintf( $str, $key );
	}

	public function raw( $key ) {
		echo $this->getNested( $key ) ?? '';
	}

	public function __invoke( $key ) {
		$this->html( $key );
	}

	public function offsetSet( $offset, $value ): void {
		if ( is_null( $offset ) ) {
			$this->data[] = $value;
		} else {
			$this->data[ $offset ] = $value;
		}
	}

	public function offsetExists( $offset ): bool {
		return ! ! $this->getNested( $offset );
	}

	public function offsetUnset( $offset ): void {
		unset( $this->data[ $offset ] );
	}

	public function offsetGet( $offset ) {
		return $this->getNested( $offset );
	}

	/**
	 * Proxy for methods that shall be called statically or as instance method
	 *
	 * Checks if method name prefixed with static exists and camelCases $name.
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed|string|void|null
	 */
	protected static function proxySharedCalls( $name, $arguments ) {
		$method = strpos( $name, '_' ) === 0 ?
			'_static' . ltrim( ucfirst( $name ), '_' )
			: 'static' . ucfirst( $name );


		$callback = [ static::class, $method ];

		if ( method_exists( static::class, $method ) && is_callable( $callback ) ) {
			return call_user_func_array( $callback, $arguments );
		} else {
			throw new \BadMethodCallException( "Method $name does not exist." );
		}

	}

	public static function __callStatic( $name, $arguments ) {
		return static::proxySharedCalls( $name, $arguments );
	}

	public function __call( $name, $arguments ) {
		return static::proxySharedCalls( $name, $arguments );
	}

	protected static function _staticClsx( $value ) {
		if ( is_string( $value ) ) {
			return $value;
		} elseif ( is_array( $value ) ) {
			$tmp = [];
			foreach ( $value as $k => $v ) {
				if ( is_numeric( $k ) ) {
					// non-associative array
					// recurse each value
					$tmp[] = static::_clsx( $v );
				} else {
					// associative array
					// add key if value is truthy
					if ( $v ) {
						$tmp[] = $k;
					}
				}
			}

			return implode( ' ', array_filter( $tmp ) );
		}

		return '';
	}


	protected static function staticClsx( ...$arguments ) {
		echo static::_staticClsx( $arguments );
	}

	protected static function _staticStyle( array $arr ): string {
		if ( empty( $arr ) ) {
			return '';
		}
		$styles = [];

		foreach ( $arr as $prop => $value ) {
			// strict check empty string and false
			if ( $value !== '' && $value !== false ) {
				$styles[] = "${prop}: ${value};";
			}
		}

		return \esc_attr( implode( " ", $styles ) );
	}

	protected static function staticStyle( array $arr ) {
		echo static::_staticStyle( $arr );
	}

	protected static function _staticAttributes( array $arr ): string {
		if ( empty( $arr ) ) {
			return '';
		}
		$atts = [];

		foreach ( $arr as $att => $value ) {
			$value  = \esc_attr( $value );
			$atts[] = "${att}=\"${value}\"";
		}

		return implode( " ", $atts );
	}

	protected static function staticAttributes( array $arr ) {
		echo static::_attributes( $arr );
	}

	/**
	 * Returns a class for an anchor tag if link is not empty
	 *
	 * Returns a class that hat the methods open() and close()
	 *
	 * @param string $link
	 * @param string $atts
	 * @param string $alternative_tag
	 *
	 * @return WpTemplateHelperElement
	 */
	public static function staticMaybeAnchorTag( string $link, $atts = '', string $alternative_tag = 'div' ) {
		if ( is_array( $atts ) ) {
			$atts = self::_attributes( $atts );
		}

		$is_link = ! empty( $link );

		if ( $is_link ) {
			$link = \esc_url( $link );

			return new class( $link, $atts ) implements WpTemplateHelperElement {
				protected $link;
				protected $atts;

				public function __construct( $link, $atts ) {
					$this->link = $link;
					$this->atts = $atts;
				}

				public function open() {
					echo "<a href=\"{$this->link}\" {$this->atts}>";
				}

				public function close() {
					echo '</a>';
				}
			};
		}

		return new class( $atts, $alternative_tag ) implements WpTemplateHelperElement {
			protected $atts;
			protected $tag;

			public function __construct( string $atts, $tag = 'div' ) {
				$this->atts = $atts;
				$this->tag  = $tag;
			}

			public function open() {
				echo "<{$this->tag} {$this->atts}>";
			}

			public function close() {
				echo "</{$this->tag}>";
			}
		};
	}

	public function link( string $key, $text = null, $atts = '' ) {
		$link = $this->getNested( $key );
		if ( ! is_array( $link ) && ! is_string( $link ) ) {
			return;
		}

		if ( is_string( $link ) && $text !== null ) {
			?>
			<a href="<?php
			\esc_url( $link ); ?>"><?php
				echo $text; ?></a>
			<?php
			return;
		}

		// is object
		$url  = $link['url'] ?? null;
		$text = $link['title'] ?? $text;

		if ( ! $url || $text === null ) {
			return;
		}

		$target = $link['target'] ?? '_self';
		if ( is_array( $atts ) ) {
			$atts = self::_attributes( $atts );
		}
		?>
		<a href="<?php echo \esc_url( $url ); ?>" target="<?php echo \esc_attr( $target ); ?>" <?php echo $atts; ?>>
			<?php echo $text; ?>
		</a>

		<?php
	}

	public function img( string $key, $size = 'full', $atts = '' ) {
		$img = $this->getNested( $key );
		if ( ! $img ) {
			return;
		}

		if ( is_numeric( $img ) ) {
			$id = $img;
			echo \wp_get_attachment_image( $id, $size, false, $atts );

			return;
		} elseif ( is_array( $img ) ) {
			$url = $img['url'] ?? null;
			$alt = $img['alt'] ?? ( is_array( $atts ) ? ( $atts['alt'] ?? '' ) : '' );
		} elseif ( is_string( $img ) ) {
			$url = $img;
			$alt = is_array( $atts ) ? ( $atts['alt'] ?? '' ) : '';
		}

		if ( ! $url ) {
			return;
		}

		if ( is_array( $atts ) ) {
			unset( $atts['alt'] );
			$atts = self::_attributes( $atts );
		}
		?>
		<img src="<?php
		echo \esc_url( $img ); ?>" alt="<?php
		echo \esc_attr( $alt ); ?>" <?php
		echo $atts; ?>>
		<?php
	}

	public function heading( $tag, $content, $attributes = [] ) {
		if ( is_array( $attributes ) ) {
			$attsString = $this->_attributes( $attributes );
		} else if ( is_string( $attributes ) ) {
			$attsString = $attributes;
		} else {
			$attsString = '';
		}

		// check if tag is valid
		$validTags = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span' ];
		if ( ! in_array( $tag, $validTags ) ) {
			$tag = 'div';
		}

		echo "<{$tag} {$attsString}>$content</{$tag}>";
	}

}
