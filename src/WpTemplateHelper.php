<?php

namespace Lxbdr\WpTemplateHelper;

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
 * @method static Interfaces\WpTemplateHelperElement maybeAnchorTag(string $link, $atts, string $alternative_tag )
 * @method Interfaces\WpTemplateHelperElement maybeAnchorTag(string $link, $atts, string $alternative_tag )
 * @method static void withLineBreaks( array $lines = [], string $separator = '<br/>' )
 * @method static string _withLineBreaks( array $lines = [], string $separator = '<br/>' )
 * @method void withLineBreaks( array $lines = [], string $separator = '<br/>' )
 * @method string _withLineBreaks( array $lines = [], string $separator = '<br/>' )
 */
class WpTemplateHelper implements \ArrayAccess {

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

	public function has( string $key ): bool {
		return $this->getNested( $key ) !== null;
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
	public function get( $key ) {
		return $this->getNested( $key ) ?? '';
	}

    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
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
	 * @return void
	 */
	protected function _staticWithLineBreaks( $lines = [], $separator = '<br/>' ) {
		$filtered = array_filter( $lines );

		echo implode( $separator, $filtered );
	}

	/**
	 * Filters empty lines and output with linebreaks
	 *
	 * @param $lines
	 * @param $separator
	 *
	 * @return void
	 */
	protected function staticWithLineBreaks( $lines = [], $separator = '<br/>' ) {
		echo static::_staticWithLineBreaks( $lines, $separator );
	}


	public function img( string $key, $size = 'full', $atts = '' ) {
		echo $this->_img( $key, $size, $atts );
	}

	public function _img( string $key, $size = 'full', $atts = '' ) {
		$img = $this->getNested( $key );
		if ( ! $img ) {
			return;
		}

		if ( is_numeric( $img ) ) {
			$id = $img;

			return \wp_get_attachment_image( $id, $size, false, $atts );
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

		ob_start();
		?>
		<img src="<?php
		echo \esc_url( $url ); ?>" alt="<?php
		echo \esc_attr( $alt ); ?>" <?php
		echo $atts; ?>>
		<?php

		return ob_get_clean();
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

	public function _advancedImg( string $key ) {
		$group = $this->getNested( $key );
		if ( ! $group ) {
			return "";
		}

		return $this->getAdvancedImgData( $group );
	}

	public function advancedImg( string $key ) {
		echo $this->_advancedImg( $key );
	}

	public function _responsiveImg( string $key ) {
		$group = $this->getNested( $key );

		$base_img = $group['base_img'] ?? null;

		if ( ! $base_img && ! empty( $group ) ) {
			// try regular img
			return $this->_img( $key );
		}

		if ( ! $group ) {
			return "";
		}

		$data = $this->getResponsiveImgData( $group );

		return $this->renderResponsiveImg( $data );
	}

	public function responsiveImg( string $key ) {
		echo $this->_responsiveImg( $key );
	}

	protected function getAdvancedImgData( $group ) {

		// todo: classname and style logic should be moved to template
		// todo: styles would require this class to register wp classes

		$size = $group['sizing'];

		switch ( $size ) {
			case 'width-full-height-full':
				$size = 'lx-img--full-width lx-img--full-height';
				break;
			case 'width-full-height-auto':
				$size = 'lx-img--full-width';
				break;
			case 'width-auto-height-full':
				$size = 'lx-img--full-height';
				break;
			case 'width-auto-height-auto':
			default:
				$size = '';
				// default
				break;
		}


		$styles = [];

		$custom_width = $group['custom_width'];
		if ( $custom_width ) {
			$styles[] = "--width: {$custom_width}";
		}
		$custom_height = $group['custom_height'];
		if ( $custom_height ) {
			$styles[] = "--height: {$custom_height}";
		}

		$styles = implode( "; ", $styles );

		$object_fit = $group['object_fit'] ?? null;
		$display    = $group['display'] ?? null;


		$classes = [
			'lx-img--constrained'  => $custom_width || $custom_height,
			'lx-img--cover'        => $object_fit === 'cover',
			'lx-img--contain'      => $object_fit === 'contain',
			'lx-img--block'        => $display === 'block',
			'lx-img--inline-block' => $display === 'inline-block',
		];

		$classes = \Lxbdr\WpTemplateHelper\WpTemplateHelper::_clsx( [
			"lx-img",
			$size,
			$classes
		] );


		$responsive_img_data = $this->getResponsiveImgData( $group );


		$data = [
			'container_classes' => $classes,
			'container_styles'  => $styles,
			'base_img'          => $responsive_img_data['base_img'],
			'sources_tags'      => $responsive_img_data['sources_tags'],
		];

		return $this->renderAdvancedImg( $data );

	}


	protected function getResponsiveImgData( $group ) {
		$base_img = $group['base_img'] ?? null;

		// source has img_id, media_query
		// todo: custom sizes
		$sources      = $group['sources'] ?: [];
		$sources_tags = [];


		foreach ( $sources as $source ) {

			$attachment_id = $source['img_id'] ?? null; // acf field should return id
			$media_query   = $source['media_query'] ?? "";

			$tag = $this->getPictureSourceTag( $attachment_id, $media_query );

			if ( $tag ) {
				$sources_tags[] = $tag;
			}
		}


		$data = [
			'base_img'     => $base_img,
			'sources_tags' => $sources_tags,
		];

		return $data;

	}


	protected function renderAdvancedImg( $data ) {

		$t = new \Lxbdr\WpTemplateHelper\WpTemplateHelper( $data );

		ob_start();
		?>

		<div id="<?php $t->attr( 'id' ); ?>" class="<?php $t->attr( 'container_classes' ); ?>"
			 style="<?php $t->attr( 'container_styles' ) ?>">
			<?php echo $this->renderResponsiveImg( $data ); ?>
		</div>

		<?php

		return ob_get_clean();

	}

	protected function renderResponsiveImg( $data ) {

		$t = new \Lxbdr\WpTemplateHelper\WpTemplateHelper( $data );

		ob_start();
		?>

		<picture>
			<?php foreach ( $t->get( 'sources_tags' ) as $source_tag ): ?>
				<?php echo $source_tag; ?>
			<?php endforeach; ?>
			<?php $t->img( 'base_img' ); ?>
		</picture>

		<?php

		return ob_get_clean();

	}


	protected function getPictureSourceTag( $attachment_id, $media_query = "" ) {
		if ( ! $attachment_id ) {
			return "";
		}

		$image = wp_get_attachment_image_src( $attachment_id, 'full' );

		if ( ! $image ) {
			return "";
		}

		$image_meta = wp_get_attachment_metadata( $attachment_id );

		$image_src  = $image[0];
		$size_array = array(
			absint( $image[1] ),
			absint( $image[2] ),
		);

		list( $src, $width, $height ) = $image;

		$hwstring = image_hwstring( $width, $height );

		$sizes = wp_calculate_image_sizes( $size_array, $image_src, $image_meta, $attachment_id );

		$srcset = wp_calculate_image_srcset( $size_array, $image_src, $image_meta, $attachment_id );

		$media = $media_query ?? "";
		$media = str_replace( '@media ', '', $media );
		$media = esc_attr( $media );

		$tag = "<source media=\"{$media}\" srcset=\"{$srcset}\" sizes=\"{$sizes}\" {$hwstring} >";

		return $tag;
	}

}
