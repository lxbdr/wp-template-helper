<?php

namespace Lxbdr\WpTemplateHelper;

use Lxbdr\WpTemplateHelper\Traits\ImgTrait;
use Lxbdr\WpTemplateHelper\Traits\WpEscapingTrait;

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
 * @method static void heading(string $tag, string $content, string|array $attributes = [])
 * @method static string _heading(string $tag, string $content, string|array $attributes = [])
 * @method void heading(string $tag, string $key, string|array $attributes = [])
 * @method string _heading(string $tag, string $key, string|array $attributes = [])
 * @method static Interfaces\WpTemplateHelperElement maybeAnchorTag(string $link, $atts, string $alternative_tag )
 * @method Interfaces\WpTemplateHelperElement maybeAnchorTag(string $link, $atts, string $alternative_tag )
 * @method static void withLineBreaks( array $lines = [], string $separator = '<br/>' )
 * @method static string _withLineBreaks( array $lines = [], string $separator = '<br/>' )
 * @method void withLineBreaks( array $lines = [], string $separator = '<br/>' )
 * @method string _withLineBreaks( array $lines = [], string $separator = '<br/>' )
 */
class WpTemplateHelper implements \ArrayAccess {

    use WpEscapingTrait;
    use ImgTrait;

    protected string $idPrefix = '';

	protected array $data = [];

	public function __construct( array $data = [] ) {
		$this->data = $data;

//        Generate a short random string to use as prefix for unique id
        $this->regenerateIdPrefix();
	}

	public static function fromObject( object $data ): WpTemplateHelper {
		return new self( get_object_vars( $data ) );
	}

	public function toArray(): array {
		return $this->data;
	}

	public function has( string $key, string $separator = '.' ): bool {
		return $this->issetNested( $key, $separator );
	}

	public function notEmpty( string $key ): bool {
		return ! $this->empty( $key );
	}

	public function empty( string $key ): bool {
		return empty( $this->getNested( $key ) );
	}

    public function id(string $id): void
    {
        echo $this->_id($id);
    }

    public function _id(string $id): string
    {
        return \esc_attr($this->idPrefix . $id);
    }

    public function getIdPrefix(): string
    {
        return $this->idPrefix;
    }

    public function regenerateIdPrefix(): void
    {
        $this->idPrefix = substr(md5(uniqid('', true)), 0, 5) . '-';
    }

	/**
	 * @param $key
	 *
	 * @return mixed value or empty string
	 */
    #[\ReturnTypeWillChange]
	public function get( $key ): mixed
    {
		return $this->getNested( $key ) ?? '';
	}

    #[\ReturnTypeWillChange]
	public function set( string $key, $value ): void
    {
		if (str_contains($key, '.')) {
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
    #[\ReturnTypeWillChange]
    protected function getNested( string $key, string $separator = '.' ): mixed {
		return array_reduce(
			explode( $separator, $key ),
			function ( $agg, $value ) {
				return $agg[ $value ] ?? null;
			},
			$this->data
		);
	}

    protected function issetNested(string $key, string $separator = '.'): bool
    {
        $keys = explode($separator, $key);
        $current = $this->data;

        foreach ($keys as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }
            $current = $current[$segment];
        }

        return true;
    }

	public function getData(): array {
		return $this->data;
	}

	public function setData( array $data ): void
    {
		$this->data = $data;
	}

	public function dump( $key = null ): void
    {
		if ( ! $key ) {
			var_dump( $this->data );

			return;
		}
		var_dump( $this->getNested( $key ) );
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

    #[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ): void {
		if ( is_null( $offset ) ) {
			$this->data[] = $value;
		} else {
			$this->data[ $offset ] = $value;
		}
	}

    #[\ReturnTypeWillChange]
	public function offsetExists( $offset ): bool {
		return ! ! $this->getNested( $offset );
	}

    #[\ReturnTypeWillChange]
	public function offsetUnset( $offset ): void {
		unset( $this->data[ $offset ] );
	}

    #[\ReturnTypeWillChange]
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
			'_static' . ucfirst(ltrim( $name, '_' ))
			: 'static' . ucfirst( $name );


		$callback = [ static::class, $method ];

		if ( method_exists( static::class, $method ) && is_callable( $callback ) ) {
            return static::{$method}(...$arguments);
			return call_user_func_array( $callback, $arguments );
		} else {
			throw new \BadMethodCallException( "Method $name does not exist." );
		}

	}

	public static function __callStatic( $name, $arguments ) {
		return static::proxySharedCalls( $name, $arguments );
	}

	public function __call( $name, $arguments ) {
        $method = str_starts_with($name, '_') ?
            '_instance' . ucfirst(ltrim( $name, '_' ))
            : 'instance' . ucfirst( $name );

        if (method_exists($this, $method) && is_callable([$this, $method])) {
            return $this->{$method}(...$arguments);
//            return call_user_func_array([$this, $method], $arguments);
        }

		return static::proxySharedCalls( $name, $arguments );
	}

	protected static function clsxInner( $value ) {
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

	protected static function _staticClsx( ...$arguments ) {
		$tmp = [];
		foreach ( $arguments as $k => $v ) {
			$tmp[] = static::clsxInner( $v );
		}

		return implode( ' ', array_filter( $tmp ) );
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
				$styles[] = "{$prop}: {$value};";
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
			$atts[] = "{$att}=\"{$value}\"";
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
	 * @return \Lxbdr\WpTemplateHelper\Interfaces\WpTemplateHelperElement
	 */
	public static function staticMaybeAnchorTag( string $link, $atts = '', string $alternative_tag = 'div' ) {
		if ( is_array( $atts ) ) {
			$atts = self::_attributes( $atts );
		}

		$is_link = ! empty( $link );

		if ( $is_link ) {
			$link = \esc_url( $link );

			return new class( $link, $atts ) implements Interfaces\WpTemplateHelperElement {
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

		return new class( $atts, $alternative_tag ) implements Interfaces\WpTemplateHelperElement {
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

	/**
	 * Filters empty lines and output with linebreaks
	 *
	 * @param array $lines
	 * @param string $separator
	 *
	 * @return string
	 */
	protected static function _staticWithLineBreaks( array $lines = [], string $separator = '<br/>' ): string {
		$filtered = array_filter( $lines );

		return implode( $separator, $filtered );
	}

	/**
	 * Filters empty lines and output with linebreaks
	 *
	 * @param $lines
	 * @param $separator
	 *
	 * @return void
	 */
	protected static function staticWithLineBreaks( $lines = [], $separator = '<br/>' ) {
		echo static::_staticWithLineBreaks( $lines, $separator );
	}

    /**
     * Get lines from keys and output with linebreaks
     *
     * @param string[] $lines
     * @param string $separator
     * @return string
     */
    public function _instanceWithLineBreaks(array $keys = [], string $separator = '<br/>'): string
    {
        $lines = array_map(function($key) {
            return $this->getNested($key);
        }, array_filter($keys));

        return self::_staticWithLineBreaks($lines, $separator);
    }

    /**
     * Get lines from keys and output with linebreaks
     *
     * @param string[] $keys
     * @param string $separator
     * @return void
     */
    public function instanceWithLineBreaks(array $keys = [], string $separator = '<br/>'): void
    {
        echo $this->_withLineBreaks($keys, $separator);
    }

    protected static function _staticHeading( string $tag, string $content, string|array $attributes = [] ) {
        if ( is_array( $attributes ) ) {
            $attsString = self::_attributes( $attributes );
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

        if ($attsString) {
            return "<{$tag} {$attsString}>{$content}</{$tag}>";
        }

        return "<{$tag}>{$content}</{$tag}>";
    }

    protected static function staticHeading( string $tag, string $content, string|array $attributes = [] ) {
        echo self::_staticHeading( $tag, $content, $attributes );
    }

	protected function _instanceHeading( string $tag, string $key, string|array $attributes = [] ) {

        $content = $this->getNested( $key );

        $content = (string) $content;

        return self::_staticHeading( $tag, $content, $attributes );

	}

    protected function instanceHeading( string $tag, string $key, string|array $attributes = [] ) {
        echo $this->_heading( $tag, $key, $attributes );
    }



}
