<?php

namespace Lxbdr\WpTemplateHelper\Traits;

/**
 * Trait ImgTrait
 *
 * Provides methods for handling and rendering various types of images in WordPress templates.
 * Supports basic images, responsive images, and advanced image configurations with custom
 * styling and layout options.
 *
 * @package Lxbdr\WpTemplateHelper\Traits
 */
trait ImgTrait
{
    use AdvancedImgACFTrait;

    /**
     * Outputs an image HTML element based on the provided key.
     *
     * @param string $key The key to lookup the image data
     * @param string|array $size The desired image size (default: 'full')
     * @param string|array $atts Additional HTML attributes for the img tag
     * @return void
     */
    public function img(string $key, $size = 'full', $atts = ''): void
    {
        echo $this->_img($key, $size, $atts);
    }

    /**
     * Generates an image HTML element based on the provided key.
     * Supports various input formats including image IDs, URLs, and arrays with metadata.
     *
     * @param string $key The key to lookup the image data
     * @param string|array $size The desired image size (default: 'full')
     * @param string|array $atts Additional HTML attributes for the img tag
     * @return string The generated HTML for the image
     */
    public function _img(string $key, $size = 'full', $atts = ''): string
    {
        $img = $this->getNested($key);
        if (!$img) {
            return '';
        }

        if (is_numeric($img)) {
            $id = $img;
            return \wp_get_attachment_image($id, $size, false, $atts);
        } elseif (is_array($img)) {
            $url = $img['url'] ?? null;
            $alt = $img['alt'] ?? (is_array($atts) ? ($atts['alt'] ?? '') : '');
        } elseif (is_string($img)) {
            $url = $img;
            $alt = is_array($atts) ? ($atts['alt'] ?? '') : '';
        }

        if (!isset($url)) {
            return '';
        }

        if (!isset($alt)) {
            $alt = '';
        }

        if (is_array($atts)) {
            unset($atts['alt']);
            $atts = self::_attributes($atts);
        }

        ob_start();
        ?>
        <img src="<?php echo \esc_url($url); ?>" alt="<?php echo \esc_attr($alt); ?>" <?php echo $atts; ?>>
        <?php
        return ob_get_clean();
    }

    /**
     * Generates HTML for an advanced image configuration with custom styling and layout options.
     *
     * Expected array structure:
     * [
     *     'sizing' => string           // One of: 'width-full-height-full', 'width-full-height-auto',
     *                                  //         'width-auto-height-full', 'width-auto-height-auto'
     *     'custom_width' => string     // Optional. CSS value for width (e.g., '500px', '50%')
     *     'custom_height' => string    // Optional. CSS value for height (e.g., '300px', '100vh')
     *     'focal_x' => string          // Optional. Horizontal focal point (percentage or string)
     *     'focal_y' => string          // Optional. Vertical focal point (percentage or string)
     *     'object_fit' => string       // Optional. One of: 'cover', 'contain'
     *     'display' => string          // Optional. One of: 'block', 'inline-block'
     *     'base_img' => array|string   // Base image. Can be:
     *                                  // - Image ID (numeric string)
     *                                  // - URL (string)
     *                                  // - Array with 'url' and 'alt' keys
     *     'sources' => [              // Optional. Array of responsive image sources
     *         [
     *             'img_id' => int     // WordPress attachment ID
     *             'media_query' => string // Media query for when this source applies
     *         ],
     *         // ... additional sources
     *     ]
     * ]
     *
     * @param string $key The key to lookup the advanced image configuration
     * @return string The generated HTML
     */
    public function _advancedImg(string $key): string
    {
        $group = $this->getNested($key);
        if (!$group) {
            return "";
        }

        $data = $this->getAdvancedImgData($group);

        return $this->renderAdvancedImg($data);
    }

    /**
     * Outputs HTML for an advanced image configuration with custom styling and layout options.
     *
     * Expected array structure:
     * [
     *     'sizing' => string           // One of: 'width-full-height-full', 'width-full-height-auto',
     *                                  //         'width-auto-height-full', 'width-auto-height-auto'
     *     'custom_width' => string     // Optional. CSS value for width (e.g., '500px', '50%')
     *     'custom_height' => string    // Optional. CSS value for height (e.g., '300px', '100vh')
     *     'focal_x' => string          // Optional. Horizontal focal point (percentage or string)
     *     'focal_y' => string          // Optional. Vertical focal point (percentage or string)
     *     'object_fit' => string       // Optional. One of: 'cover', 'contain'
     *     'display' => string          // Optional. One of: 'block', 'inline-block'
     *     'base_img' => array|string   // Base image. Can be:
     *                                  // - Image ID (numeric string)
     *                                  // - URL (string)
     *                                  // - Array with 'url' and 'alt' keys
     *     'sources' => [              // Optional. Array of responsive image sources
     *         [
     *             'img_id' => int     // WordPress attachment ID
     *             'media_query' => string // Media query for when this source applies
     *         ],
     *         // ... additional sources
     *     ]
     * ]
     *
     * @param string $key The key to lookup the advanced image configuration
     * @return void The generated HTML
     */
    public function advancedImg(string $key): void
    {
        echo $this->_advancedImg($key);
    }

    /**
     * Generates HTML for a responsive image with multiple sources based on media queries.
     *
     * Expected array structure:
     * [
     *     'base_img' => array|string   // Required. Base image. Can be:
     *                                  // - Image ID (numeric string)
     *                                  // - URL (string)
     *                                  // - Array with 'url' and 'alt' keys
     *     'sources' => [              // Optional. Array of responsive image sources
     *         [
     *             'img_id' => int     // WordPress attachment ID for this source
     *             'media_query' => string // Media query when this source should be used
     *                                    // Example: '(min-width: 768px)'
     *         ],
     *         // ... additional sources
     *     ]
     * ]
     *
     * Example usage:
     * [
     *     'base_img' => [
     *         'url' => 'path/to/mobile.jpg',
     *         'alt' => 'Description'
     *     ],
     *     'sources' => [
     *         [
     *             'img_id' => 123,
     *             'media_query' => '(min-width: 768px)'
     *         ],
     *         [
     *             'img_id' => 456,
     *             'media_query' => '(min-width: 1024px)'
     *         ]
     *     ]
     * ]
     *
     * @param string $key The key to lookup the responsive image configuration
     * @return string The generated HTML
     */
    public function _responsiveImg(string $key)
    {
        $group = $this->getNested($key);

        $base_img = $group['base_img'] ?? null;

        if (!$base_img && !empty($group)) {
            // try regular img
            return $this->_img($key);
        }

        if (!$group) {
            return "";
        }

        $data = $this->getResponsiveImgData($group);
        return $this->renderResponsiveImg($data);
    }

    /**
     * Outputs HTML for a responsive image with multiple sources based on media queries.
     *
     * Expected array structure:
     * [
     *     'base_img' => array|string   // Required. Base image. Can be:
     *                                  // - Image ID (numeric string)
     *                                  // - URL (string)
     *                                  // - Array with 'url' and 'alt' keys
     *     'sources' => [              // Optional. Array of responsive image sources
     *         [
     *             'img_id' => int     // WordPress attachment ID for this source
     *             'media_query' => string // Media query when this source should be used
     *                                    // Example: '(min-width: 768px)'
     *         ],
     *         // ... additional sources
     *     ]
     * ]
     *
     * Example usage:
     * [
     *     'base_img' => [
     *         'url' => 'path/to/mobile.jpg',
     *         'alt' => 'Description'
     *     ],
     *     'sources' => [
     *         [
     *             'img_id' => 123,
     *             'media_query' => '(min-width: 768px)'
     *         ],
     *         [
     *             'img_id' => 456,
     *             'media_query' => '(min-width: 1024px)'
     *         ]
     *     ]
     * ]
     *
     * @param string $key The key to lookup the responsive image configuration
     * @return void The generated HTML
     */
    public function responsiveImg(string $key): void
    {
        echo $this->_responsiveImg($key);
    }

    /**
     * Processes advanced image configuration data and prepares it for rendering.
     *
     * @param array $group The advanced image configuration data
     * @return array Processed image data with container classes, styles, and responsive image data
     */
    protected function getAdvancedImgData($group)
    {
        // Handle image sizing classes
        $size = $group['sizing'];
        switch ($size) {
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
                break;
        }

        // Process custom dimensions and focal point
        $styles = [];
        $custom_width = $group['custom_width'];
        if ($custom_width) {
            $styles[] = "--width: {$custom_width}";
        }
        $custom_height = $group['custom_height'];
        if ($custom_height) {
            $styles[] = "--height: {$custom_height}";
        }

        // Process focal point coordinates
        $focal_x = $group['focal_x'] ?? '50%';
        $focal_y = $group['focal_y'] ?? '50%';

        // Ensure focal points are in percentage format
        $focal_x = is_numeric($focal_x) ? $focal_x . '%' : $focal_x;
        $focal_y = is_numeric($focal_y) ? $focal_y . '%' : $focal_y;

        $styles[] = "--focal-x: {$focal_x}";
        $styles[] = "--focal-y: {$focal_y}";

        $styles = implode("; ", $styles);

        // Configure display and object-fit properties
        $object_fit = $group['object_fit'] ?? null;
        $display = $group['display'] ?? null;

        $classes = [
            'lx-img--constrained' => $custom_width || $custom_height,
            'lx-img--cover' => $object_fit === 'cover',
            'lx-img--contain' => $object_fit === 'contain',
            'lx-img--block' => $display === 'block',
            'lx-img--inline-block' => $display === 'inline-block',
            'lx-img--has-focal' => isset($group['focal_x']) || isset($group['focal_y']), // Add class when focal point is set
        ];

        $classes = static::_clsx([
            "lx-img",
            $size,
            $classes
        ]);

        $responsive_img_data = $this->getResponsiveImgData($group);

        return [
            'container_classes' => $classes,
            'container_styles' => $styles,
            'base_img' => $responsive_img_data['base_img'],
            'sources_tags' => $responsive_img_data['sources_tags'],
        ];
    }

    /**
     * Processes responsive image data and generates source tags for different media queries.
     *
     * @param array $group The responsive image configuration data
     * @return array Processed image data with base image and source tags
     */
    protected function getResponsiveImgData($group)
    {
        $base_img = $group['base_img'] ?? null;
        $sources = $group['sources'] ?: [];
        $sources_tags = [];

        foreach ($sources as $source) {
            $attachment_id = $source['img_id'] ?? null;
            $media_query = $source['media_query'] ?? "";

            $tag = $this->getPictureSourceTag($attachment_id, $media_query);
            if ($tag) {
                $sources_tags[] = $tag;
            }
        }

        return [
            'base_img' => $base_img,
            'sources_tags' => $sources_tags,
        ];
    }

    /**
     * Renders an advanced image with its container and styles.
     *
     * @param array $data The processed image data with container classes, styles, and responsive image data
     * @return string The rendered HTML
     */
    protected function renderAdvancedImg($data)
    {
        $t = new static($data);

        ob_start();
        ?>
        <div id="<?php $t->attr('id'); ?>" class="<?php $t->attr('container_classes'); ?>"
             style="<?php $t->attr('container_styles') ?>">
            <?php echo $this->renderResponsiveImg($data); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Renders a responsive image with its source tags.
     *
     * @param array $data The processed image data
     * @return string The rendered HTML
     */
    protected function renderResponsiveImg($data)
    {
        $t = new \Lxbdr\WpTemplateHelper\WpTemplateHelper($data);

        ob_start();
        ?>
        <picture>
            <?php foreach ($t->get('sources_tags') as $source_tag): ?>
                <?php echo $source_tag; ?>
            <?php endforeach; ?>
            <?php $t->img('base_img'); ?>
        </picture>
        <?php
        return ob_get_clean();
    }

    /**
     * Generates a source tag for a picture element based on attachment ID and media query.
     *
     * @param int|null $attachment_id WordPress attachment ID
     * @param string $media_query Media query for the source tag
     * @return string The generated source tag HTML
     */
    protected function getPictureSourceTag($attachment_id, $media_query = "")
    {
        if (!$attachment_id) {
            return "";
        }

        $image = \wp_get_attachment_image_src($attachment_id, 'full');
        if (!$image) {
            return "";
        }

        $image_meta = \wp_get_attachment_metadata($attachment_id);
        $image_src = $image[0];
        $size_array = array(
            \absint($image[1]),
            \absint($image[2]),
        );

        list($src, $width, $height) = $image;
        $hwstring = \image_hwstring($width, $height);
        $sizes = \wp_calculate_image_sizes($size_array, $image_src, $image_meta, $attachment_id);
        $srcset = \wp_calculate_image_srcset($size_array, $image_src, $image_meta, $attachment_id);

        $media = $media_query ?? "";
        $media = str_replace('@media ', '', $media);
        $media = esc_attr($media);

        return "<source media=\"{$media}\" srcset=\"{$srcset}\" sizes=\"{$sizes}\" {$hwstring} >";
    }

    /**
     * Generates the CSS rules for the advanced image component.
     * This includes all classes that can be generated by getAdvancedImgData.
     *
     * @return string The complete CSS ruleset
     */
    protected function getAdvancedImgCss()
    {
        return <<<CSS

    .lx-img {
        --width: auto;
        --height: auto;
        --focal-x: 50%;
        --focal-y: 50%;
        position: relative;
        display: inline-block;
    }

    .lx-img img {
        max-width: 100%;
        height: auto;
    }

    /* Sizing Classes */
    .lx-img.lx-img--full-width {
        width: 100%;
    }

    .lx-img.lx-img--full-width img {
        width: 100%;
    }

    .lx-img.lx-img--full-height {
        height: 100%;
    }

    .lx-img.lx-img--full-height img {
        height: 100%;
    }

    /* Custom Dimensions */
    .lx-img.lx-img--constrained {
        width: var(--width, auto);
        height: var(--height, auto);
    }

    .lx-img.lx-img--constrained img {
        width: 100%;
        height: 100%;
    }

    /* Object Fit Classes */
    .lx-img.lx-img--cover img {
        object-fit: cover;
    }

    .lx-img.lx-img--contain img {
        object-fit: contain;
    }

    /* Focal Point */
    .lx-img.lx-img--has-focal img {
        object-position: var(--focal-x, 50%) var(--focal-y, 50%);
    }

    /* Display Classes */
    .lx-img.lx-img--block {
        display: block;
    }

    .lx-img.lx-img--inline-block {
        display: inline-block;
    }

    /* Responsive Images */
    .lx-img picture {
        display: block;
        width: 100%;
        height: 100%;
    }

    .lx-img source {
        width: 100%;
        height: 100%;
    }
CSS;
    }

    /**
     * Abstract method to retrieve nested values from data array.
     * Must be implemented by classes using this trait.
     *
     * @param string $key The nested key to retrieve
     * @param string $separator The separator used in nested keys (default: '.')
     * @return mixed The retrieved value
     */
    #[\ReturnTypeWillChange]
    abstract protected function getNested(string $key, string $separator = '.'): mixed;
}
