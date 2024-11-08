# WpTemplateHelper

Use it to wrap data and get escaped output automatically checking for existence.

Simple example:

```php
<?php
$data = [
'foo' => 'bar',
'foo_url' => 'https://example.com',
'style' => 'color: red; font-weight: bold',
'img' => 'https://picsum.photos/100/100'
];

$t = new \Lxbdr\WpTemplateHelper\WpTemplateHelper($data);

?>

<div class="card">
    <?php
        if ($t->notEmpty('img')): ?>
            <img src="<?php $t->url('img'); ?>" alt="Beautiful picture">
        <?php
        endif; ?>
    <h1 class="card-title"><?php $t('foo'); ?></h1>
    <p style="<?php $t->attr('style'); ?>">lorem ipsum</p>
    <a href="<?php $t->url('foo_url'); ?>">Read more</a> 
</div>

<?php
// ...
```

# Data Access & Escaping

The `WpTemplateHelper` class provides a convenient and secure way to handle nested data structures and output content safely in WordPress templates. It includes various methods for data access and escaping.

## Basic Data Access

### Constructor

```php
$data = [
    'user' => [
        'name' => 'John Doe',
        'profile' => [
            'url' => 'https://example.com/john',
            'bio' => '<p>Web developer & WordPress enthusiast</p>'
        ]
    ]
];

$template = new WpTemplateHelper($data);
```

### Accessing Data

#### Using `get()`
Retrieves raw data from the specified key. Returns empty string if not found.

```php
$name = $template->get('user.name'); // "John Doe"
$nonexistent = $template->get('user.foo'); // ""
```

#### Using `has()`
Checks if a key exists in the data structure. It uses `isset` under the hood and returns true for
non-empty values.

```php
if ($template->has('user.profile')) {
    // Access nested data
}
```

#### Using `notEmpty()` / `empty()`
Check if a value exists and is not empty.

```php
if ($template->notEmpty('user.bio')) {
    // Value exists and is not empty
}

if ($template->empty('user.location')) {
    // Value is empty or doesn't exist
}
```

### Raw Output

#### Using `raw()`
Outputs the raw value without any escaping. Use with caution!

```php
$template->raw('user.name'); // Outputs: John Doe
```

#### Using `__invoke()`
Shorthand for HTML-escaped output. Same as calling `html()`.

```php
$template = new WpTemplateHelper(['message' => 'Hello <world>']);
$template('message'); // Outputs: Hello &lt;world&gt;
```

## Secure Output Methods

### HTML Content

```php
// Safe HTML output with entities escaped
$template->html('user.name');

// Allow specific HTML tags (wp_kses_post)
$template->safeHtml('user.profile.bio');
```

### URLs

```php
// In HTML attributes
<a href="<?php $template->url('user.profile.url'); ?>">
    <?php $template->html('user.name'); ?>
</a>

// Storing escaped URL
$profileUrl = $template->_url('user.profile.url');
```

### HTML Attributes

```php
<div data-user="<?php $template->attr('user.name'); ?>"
     title="<?php $template->attr('user.profile.title'); ?>">
    <!-- content -->
</div>
```

### JavaScript Content

```php
<script>
    const userName = "<?php $template->js('user.name'); ?>";
</script>
```

### XML Content

```php
<?xml version="1.0"?>
<user>
    <name><?php $template->xml('user.name'); ?></name>
</user>
```

## Methods Overview

### Output Methods (Echo)
- `html($key)` - Escape HTML entities
- `safeHtml($key)` - Allow safe HTML tags
- `url($key)` - Escape URLs
- `attr($key)` - Escape HTML attributes
- `js($key)` - Escape JavaScript strings
- `xml($key)` - Escape XML content
- `raw($key)` - Raw output (unescaped)

### Return Methods
- `_html($key)` - Return escaped HTML
- `_safeHtml($key)` - Return HTML with safe tags
- `_url($key)` - Return escaped URL
- `_attr($key)` - Return escaped attribute
- `_js($key)` - Return escaped JavaScript
- `_xml($key)` - Return escaped XML
- `get($key)` - Return raw value

### Data Access Methods
- `has($key)` - Check key existence
- `empty($key)` - Check if empty
- `notEmpty($key)` - Check if not empty

## Best Practices

1. **Always Use Appropriate Escaping**
```php
// Bad
<a href="<?php $template->raw('link'); ?>">

// Good
<a href="<?php $template->url('link'); ?>">
```

2. **Use Dot Notation for Nested Data**
```php
// Access deeply nested data
$template->html('user.settings.preferences.theme');
```

3. **Combine with HTML Structure**
```php
// Clean and secure template code
<div class="user-profile">
    <h2><?php $template->html('user.name'); ?></h2>
    <div class="bio">
        <?php $template->safeHtml('user.profile.bio'); ?>
    </div>
    <a href="<?php $template->url('user.profile.url'); ?>"
       title="<?php $template->attr('user.profile.title'); ?>">
        View Profile
    </a>
</div>
```

4. **Use Return Methods for Variable Assignment**
```php
$userName = $template->_html('user.name');
$profileUrl = $template->_url('user.profile.url');

// Use in complex logic
if ($template->has('user.profile') && $template->_url('user.profile.url')) {
    // Process data
}
```

## Security Note

- Always use the appropriate escaping method for the context
- Never use `raw()` for user-provided data
- Use `safeHtml()` when you need to allow specific HTML tags
- Consider context when choosing between `html()` and `attr()`

# Utilities


# Image functions

WpTemplateHelper provides methods for handling and rendering various types of images in WordPress templates. It supports basic images, responsive images, and advanced image configurations with custom styling and layout options.

## Basic Image Methods

### `img($key, $size = 'full', $atts = '')`
### `_img($key, $size = 'full', $atts = '')`

Renders/returns an HTML image element based on the provided key. Supports various input formats including image IDs, URLs, and arrays with metadata.

```php
// Using a WordPress image ID
$data = ['profile_image' => 123];
$template = new WpTemplateHelper($data);

// Echo the image
$template->img('profile_image');
// Output: <img src="path/to/image.jpg" alt="Image Alt Text">

// With custom size and attributes
$template->img('profile_image', 'thumbnail', ['class' => 'rounded']);
// Output: <img src="path/to/thumbnail.jpg" alt="Image Alt Text" class="rounded">

// Using direct URL
$data = ['hero_image' => 'https://example.com/image.jpg'];
$template->img('hero_image');
// Output: <img src="https://example.com/image.jpg" alt="">

// Using array with URL and alt text
$data = [
    'team_member' => [
        'url' => 'https://example.com/member.jpg',
        'alt' => 'Team Member Photo'
    ]
];
$template->img('team_member');
// Output: <img src="https://example.com/member.jpg" alt="Team Member Photo">
```

## Responsive Image Methods

### `responsiveImg($key)`
### `_responsiveImg($key)`

Generates HTML for a responsive image with multiple sources based on media queries.

```php
$data = [
    'hero' => [
        'base_img' => [
            'url' => 'path/to/mobile.jpg',
            'alt' => 'Hero Image'
        ],
        'sources' => [
            [
                'img_id' => 123, // WordPress image ID
                'media_query' => '(min-width: 768px)'
            ],
            [
                'img_id' => 456,
                'media_query' => '(min-width: 1024px)'
            ]
        ]
    ]
];

$template = new WpTemplateHelper($data);
$template->responsiveImg('hero');

// Output:
// <picture>
//     <source media="(min-width: 1024px)" srcset="path/to/desktop.jpg 1200w" sizes="100vw">
//     <source media="(min-width: 768px)" srcset="path/to/tablet.jpg 800w" sizes="100vw">
//     <img src="path/to/mobile.jpg" alt="Hero Image">
// </picture>
```

## Advanced Image Methods

### `advancedImg($key)`
### `_advancedImg($key)`

Renders an image with advanced configuration options including custom sizing, focal points, and object-fit properties.

```php
$data = [
    'featured' => [
        'sizing' => 'width-full-height-full',
        'custom_width' => '500px',
        'custom_height' => '300px',
        'focal_x' => '30',
        'focal_y' => '70',
        'object_fit' => 'cover',
        'display' => 'block',
        'base_img' => [
            'url' => 'path/to/image.jpg',
            'alt' => 'Featured Image'
        ],
        'sources' => [] // Optional responsive sources
    ]
];

$template = new WpTemplateHelper($data);
$template->advancedImg('featured');

// Output:
// <div class="lx-img lx-img--full-width lx-img--full-height lx-img--cover lx-img--block" 
//      style="--width: 500px; --height: 300px; --focal-x: 30%; --focal-y: 70%">
//     <picture>
//         <img src="path/to/image.jpg" alt="Featured Image">
//     </picture>
// </div>
```

### Available Sizing Options
- `width-full-height-full`: Image takes full width and height of container
- `width-full-height-auto`: Image takes full width with auto height
- `width-auto-height-full`: Image takes full height with auto width
- `width-auto-height-auto`: Image dimensions are automatic

### Object Fit Options
- `cover`: Image covers the entire container
- `contain`: Image is contained within the container

### Display Options
- `block`: Display as block element
- `inline-block`: Display as inline-block element

## CSS Integration

The trait provides a static method `getAdvancedImgCss()` that returns the necessary CSS for advanced image features. Include this CSS in your theme or plugin:

```php
// In your theme's functions.php or plugin file
add_action('wp_head', function() {
    echo '<style>' . WpTemplateHelper::getAdvancedImgCss() . '</style>';
});

// Or via enqueue scripts to attach it to an existing handle
add_action('wp_enqueue_scripts', function() {
	$style = \Lxbdr\WpTemplateHelper\WpTemplateHelper::getAdvancedImgCss();
	wp_add_inline_style('wp-block-library', $style);
});
```

## ACF Integration

WpTemplateHelper provides a method to register field groups which contain all fields for using the advanced image feature.
Call `registerAdvancedImgAcfFields()` to register the field groups with ACF.
The field names are not prefixed and meant to be used in a clone field within a group.
Important: The clone field should be a subfield of a group field otherwise it might not save correctly if multiple
images/clone fields are used.

```php
\add_action( 'acf/init', function () {

	\Lxbdr\WpTemplateHelper\WpTemplateHelper::registerAdvancedImgAcfFields();
} );
```

Example usage in a group:

```php
array(
			'key' => 'field_672e120b01cf8',
			'label' => 'Clone img group wrapper',
			'name' => 'clone_img_group_wrapper',
			'aria-label' => '',
			'type' => 'group',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'layout' => 'block',
			'sub_fields' => array(
				array(
					'key' => 'field_672e122901cf9',
					'label' => 'clone_img_group',
					'name' => 'clone_img_group',
					'aria-label' => '',
					'type' => 'clone',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'clone' => array(
						0 => 'lx_group_advanced_img',
					),
					'display' => 'seamless',
					'layout' => 'block',
					'prefix_label' => 0,
					'prefix_name' => 0,
				),
			),
		),
	),

// access img fields like this
$img_data = get_field('clone_img_group_wrapper');

// and using the WpTemplateHelper
$data = [
'my_img' => get_field('clone_img_group_wrapper')
];
$t = new \Lxbdr\WpTemplateHelper\WpTemplateHelper($data);
$t->advancedImg('my_img');
```

## Notes

- The trait relies on WordPress core functions for image handling
- Images are automatically escaped for security
- All methods prefixed with underscore (e.g., `_img()`) return the HTML string instead of echoing
- Responsive images automatically use WordPress's built-in srcset and sizes attributes
- Advanced image configurations support CSS custom properties for dynamic styling

## Best Practices

1. Always provide alt text for accessibility
2. Use responsive images for better performance on different devices
3. Consider using advanced image configuration for complex layouts
4. Set appropriate focal points for images that will be cropped
5. Include the CSS when using advanced image features
