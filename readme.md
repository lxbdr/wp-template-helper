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

The `WpTemplateHelper` class provides a convenient and secure way to handle nested data structures and output content
safely in WordPress templates. It includes various methods for data access and escaping.

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

# Utility Methods

The `WpTemplateHelper` class provides several utility methods for common template operations. These methods can be used
both statically and as instance methods.

## Class Methods

### `clsx()` - Dynamic Class Names

Combines class names based on conditions. Similar to the popular `classnames` JavaScript library.

```php
// Static usage
WpTemplateHelper::_clsx(
    'btn',
    [
        'btn--primary' => true,
        'btn--large' => $isLarge,
        'btn--disabled' => !$isEnabled
    ],
    $additionalClasses
);

// Instance usage with data
$template = new WpTemplateHelper([
    'isActive' => true,
    'size' => 'large'
]);

<div class="<?php $template->clsx(
    'component',
    [
        'component--active' => $template->get('isActive'),
        'component--large' => $template->get('size') === 'large'
    ]
); ?>">
```

Output:

```html
<!-- If $isLarge = true and $isEnabled = false -->
<div class="btn btn--primary btn--large btn--disabled">

    <!-- With instance data -->
    <div class="component component--active component--large">
```

### `style()` - Inline Styles

Generates inline CSS style strings. Handles conditional styles and value filtering.

```php
// Static usage
<div style="<?php WpTemplateHelper::style([
    'display' => 'flex',
    'margin-top' => '20px',
    'color' => $textColor,
    'background' => $isDisabled ? '#ccc' : false
]); ?>">

// Instance usage
$template = new WpTemplateHelper([
    'spacing' => '2rem',
    'color' => '#333'
]);

<div style="<?php $template->style([
    'padding' => $template->get('spacing'),
    'color' => $template->get('color'),
    'display' => 'block'
]); ?>">
```

Output:

```html
<!-- Static example with $textColor = '#000' and $isDisabled = true -->
<div style="display: flex; margin-top: 20px; color: #000; background: #ccc;">

    <!-- Instance example -->
    <div style="padding: 2rem; color: #333; display: block;">
```

### `attributes()` - HTML Attributes

Generates HTML attribute strings with proper escaping.

```php
// Static usage
<div <?php WpTemplateHelper::attributes([
    'id' => 'main-content',
    'data-user' => $userName,
    'aria-label' => 'Main content area'
]); ?>>

// Instance usage
$template = new WpTemplateHelper([
    'elementId' => 'user-profile',
    'userData' => [
        'name' => 'John Doe',
        'role' => 'admin'
    ]
]);

<div <?php $template->attributes([
    'id' => $template->get('elementId'),
    'data-user' => $template->get('userData.name'),
    'data-role' => $template->get('userData.role')
]); ?>>
```

Output:

```html

<div id="main-content" data-user="John Smith" aria-label="Main content area">
    <div id="user-profile" data-user="John Doe" data-role="admin">
```

### `heading()` - Semantic Headings

Creates semantic heading elements with attributes. Validates heading tags.

```php
// Static usage
<?php WpTemplateHelper::heading('h1', 'Welcome to our site', [
    'class' => 'main-title',
    'id' => 'welcome-heading'
]); ?>

// Instance usage
$template = new WpTemplateHelper([
    'title' => 'Welcome Back',
    'subtitle' => 'Your Dashboard'
]);

<?php $template->heading('h1', 'title', [
    'class' => 'dashboard-title'
]); ?>

<?php $template->heading('h2', 'subtitle', [
    'class' => 'dashboard-subtitle'
]); ?>
```

Output:

```html
<h1 class="main-title" id="welcome-heading">Welcome to our site</h1>
<h1 class="dashboard-title">Welcome Back</h1>
<h2 class="dashboard-subtitle">Your Dashboard</h2>
```

### `maybeAnchorTag()` - Conditional Links

Creates either an anchor tag or alternative element based on link presence.

```php
// Static usage
<?php
$tag = WpTemplateHelper::maybeAnchorTag(
    'https://example.com',
    ['class' => 'btn'],
    'span'
);
$tag->open();
echo 'Click me';
$tag->close();
?>

// Instance usage with data
$template = new WpTemplateHelper([
    'link' => [
        'url' => 'https://example.com',
        'target' => '_blank'
    ]
]);

<?php
$tag = $template->maybeAnchorTag(
   'link.url',
    [
        'class' => 'btn',
        'target' => $template->get('link.target')
    ],
    'div'
);
?>
<?php $tag->open(); ?>
    Click Here
<?php $tag->close(); ?>
```

Output:

```html
<!-- With valid URL -->
<a href="https://example.com" class="btn">Click me</a>

<!-- Without URL -->
<span class="btn">Click me</span>
```

### `withLineBreaks()` - Line Break Formatting

Joins array elements with line breaks, filtering empty values.

```php
// Static usage
<?php WpTemplateHelper::withLineBreaks([
    'First line',
    'Second line',
    '',  // Will be filtered out
    'Third line'
]); ?>

// Instance usage with data
$template = new WpTemplateHelper([
    'address' => [
        'street' => '123 Main St',
        'city' => 'Springfield',
        'state' => 'ST',
        'zip' => '12345'
    ]
]);

<?php $template->withLineBreaks([
    'address.street',
    'address.city',
    'address.state',
    'address.zip'
]); ?>
```

Output:

```html
First line<br/>Second line<br/>Third line

123 Main St<br/>Springfield<br/>ST<br/>12345
```

## Using Return Methods

All methods have a corresponding return version prefixed with underscore (`_`):

```php
// Store result in variable
$classes = WpTemplateHelper::_clsx(['btn', 'btn--primary' => true]);
$styles = WpTemplateHelper::_style(['color' => 'red']);
$attributes = WpTemplateHelper::_attributes(['id' => 'main']);
$heading = WpTemplateHelper::_heading('h1', 'Title', ['class' => 'main']);
$content = WpTemplateHelper::_withLineBreaks(['Line 1', 'Line 2']);

// Using with conditions
if (WpTemplateHelper::_clsx(['active' => $condition])) {
    // Do something
}
```

## Best Practices

1. **Use Type-Appropriate Methods**
    - Use `clsx()` for dynamic class names
    - Use `style()` for inline styles
    - Use `attributes()` for HTML attributes
    - Use `heading()` for semantic headings
    - Use `maybeAnchorTag()` for conditional links
    - Use `withLineBreaks()` for formatted text blocks

2. **Choose Static vs Instance Methods**
    - Use static methods for standalone utilities
    - Use instance methods when working with template data

3. **Combine with Data Access**

```php
$template = new WpTemplateHelper($data);

<div <?php $template->attributes([
    'class' => $template->_clsx([
        'component',
        'component--active' => $template->get('isActive')
    ]),
    'style' => $template->_style([
        'color' => $template->get('textColor')
    ])
]); ?>>
```

### ID Management

The `WpTemplateHelper` provides methods for generating and managing unique IDs for HTML elements. Each instance
maintains its own ID prefix to ensure uniqueness across multiple template instances.

### `id()` / `_id()` - Prefixed IDs

Generates a unique, prefixed ID for HTML elements. The prefix is automatically generated and helps prevent ID collisions
when multiple instances of templates are used on the same page.

```php
// Instance usage
$template = new WpTemplateHelper([
    'title' => 'My Page',
    'content' => 'Page content'
]);

// Echo the ID
<div id="<?php $template->id('header'); ?>">
    <nav id="<?php $template->id('main-nav'); ?>">
</div>

// Store ID in variable
$headerId = $template->_id('header');
$navId = $template->_id('main-nav');
```

Output:

```html
<!-- Each instance has a unique prefix (e.g., "abc12-") -->
<div id="abc12-header">
    <nav id="abc12-main-nav">
</div>
```

### `getIdPrefix()` - Current Prefix

Retrieves the current ID prefix being used by the instance.

```php
$template = new WpTemplateHelper([]);
$prefix = $template->getIdPrefix(); // e.g., "abc12-"

// Useful for coordinating IDs with aria-labels or other references
<button id="<?php $template->id('trigger'); ?>"
        aria-controls="<?php $template->id('dropdown'); ?>">
    Toggle Menu
</button>
<div id="<?php $template->id('dropdown'); ?>"
     aria-labelledby="<?php $template->id('trigger'); ?>">
    <!-- Dropdown content -->
</div>
```

### `regenerateIdPrefix()` - New Prefix

Forces generation of a new random ID prefix. Useful when you need to ensure a fresh set of IDs.

```php
$template = new WpTemplateHelper([]);

// Initial prefix
echo $template->getIdPrefix(); // e.g., "abc12-"

// Generate new prefix
$template->regenerateIdPrefix();
echo $template->getIdPrefix(); // e.g., "xyz89-"
```

## Common Use Cases

### ARIA Relationships

```php
$template = new WpTemplateHelper([
    'tabs' => [
        ['title' => 'Tab 1', 'content' => 'Content 1'],
        ['title' => 'Tab 2', 'content' => 'Content 2']
    ]
]);
?>

<div class="tabs">
    <div role="tablist">
        <?php foreach ($template->get('tabs') as $index => $tab): ?>
            <button role="tab"
                    id="<?php $template->id("tab-$index"); ?>"
                    aria-controls="<?php $template->id("panel-$index"); ?>">
                <?php echo esc_html($tab['title']); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($template->get('tabs') as $index => $tab): ?>
        <div role="tabpanel"
             id="<?php $template->id("panel-$index"); ?>"
             aria-labelledby="<?php $template->id("tab-$index"); ?>">
            <?php echo esc_html($tab['content']); ?>
        </div>
    <?php endforeach; ?>
</div>
```

### Forms and Labels

```php
$template = new WpTemplateHelper([
    'fields' => [
        'name' => 'Full Name',
        'email' => 'Email Address',
        'message' => 'Your Message'
    ]
]);
?>

<form id="<?php $template->id('contact-form'); ?>">
    <?php foreach ($template->get('fields') as $field => $label): ?>
        <?php 
        $fieldId = $template->_id("field-$field");
        $labelId = $template->_id("label-$field");
        ?>
        <div class="form-group">
            <label id="<?php echo esc_attr($labelId); ?>"
                   for="<?php echo esc_attr($fieldId); ?>">
                <?php echo esc_html($label); ?>
            </label>
            <input id="<?php echo esc_attr($fieldId); ?>"
                   aria-labelledby="<?php echo esc_attr($labelId); ?>"
                   name="<?php echo esc_attr($field); ?>">
        </div>
    <?php endforeach; ?>
</form>
```

## Best Practices

The main reason to use this helper is to prevent duplicate IDs in templates which are used multiple times on the same
page. This can cause issues with JavaScript, CSS, and accessibility.
Keep in mind that the ID prefix is unique to each instance of the helper and is randomly generated on instantiation.

# Image functions

WpTemplateHelper provides methods for handling and rendering various types of images in WordPress templates. It supports
basic images, responsive images, and advanced image configurations with custom styling and layout options.

## Basic Image Methods

### `img($key, $size = 'full', $atts = '')`

### `_img($key, $size = 'full', $atts = '')`

Renders/returns an HTML image element based on the provided key. Supports various input formats including image IDs,
URLs, and arrays with metadata.

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

The trait provides a static method `getAdvancedImgCss()` that returns the necessary CSS for advanced image features.
Include this CSS in your theme or plugin:

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

WpTemplateHelper provides a method to register field groups which contain all fields for using the advanced image
feature.
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

## Block editor block wrapper

Sometimes we want to use a block which uses nested blocks. This requires a call to `do_blocks` with the rendered content
of the inner blocks and therefore some use of messy `ob_start` and `ob_get_clean` calls.
Use `::blockWrapper($block_name, $attributes)` to get an instance of a block wrapper and call `open()` and `close()` on
it.

```php
$block = \Lxbdr\WpTemplateHelper\WpTemplateHelper::blockWrapper('core/paragraph', [
    'className' => 'my-class',
    'style' => 'color: red'
]);
$block->open();
?>
<p>My content</p>
<?php
$block->close();
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
