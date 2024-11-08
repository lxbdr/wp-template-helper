<?php

namespace Lxbdr\WpTemplateHelper\Traits;

trait ImgTrait
{

    public function img( string $key, $size = 'full', $atts = '' ) {
        echo $this->_img( $key, $size, $atts );
    }

    public function _img( string $key, $size = 'full', $atts = '' ): string {
        $img = $this->getNested( $key );
        if ( ! $img ) {
            return '';
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

        if ( ! isset($url) ) {
            return '';
        }

        if (! isset($alt)) {
            $alt = '';
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

        $classes = static::_clsx( [
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

        $image = \wp_get_attachment_image_src( $attachment_id, 'full' );

        if ( ! $image ) {
            return "";
        }

        $image_meta = \wp_get_attachment_metadata( $attachment_id );

        $image_src  = $image[0];
        $size_array = array(
            \absint( $image[1] ),
            \absint( $image[2] ),
        );

        list( $src, $width, $height ) = $image;

        $hwstring = \image_hwstring( $width, $height );

        $sizes = \wp_calculate_image_sizes( $size_array, $image_src, $image_meta, $attachment_id );

        $srcset = \wp_calculate_image_srcset( $size_array, $image_src, $image_meta, $attachment_id );

        $media = $media_query ?? "";
        $media = str_replace( '@media ', '', $media );
        $media = esc_attr( $media );

        $tag = "<source media=\"{$media}\" srcset=\"{$srcset}\" sizes=\"{$sizes}\" {$hwstring} >";

        return $tag;
    }

    /**
     * Get nested value from data array.
     * This method should be implemented by the using class.
     *
     * @param string $key The nested key to retrieve
     * @return mixed The value
     */
    #[\ReturnTypeWillChange]
    abstract protected function getNested(string $key, string $separator = '.'): mixed;

}
