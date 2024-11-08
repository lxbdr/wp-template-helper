<?php

namespace Lxbdr\WpTemplateHelper\Traits;

trait AdvancedImgACFTrait
{

    /**
     * Registers the Advanced Image ACF Field Group and its fields.
     * The group is set as inactive to allow for cloning.
     *
     * @return void
     */
    public static function registerAdvancedImgAcfFields()
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        \acf_add_local_field_group([
            'key' => 'lx_group_advanced_img',
            'title' => __('WpTemplateHelper Advanced Image Configuration', 'lxbdr'),
            'fields' => [
                [
                    'key' => 'lx_field_advanced_img_base',
                    'label' => __('Base Image', 'lxbdr'),
                    'name' => 'base_img',
                    'type' => 'image',
                    'required' => 1,
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                ],
                [
                    'key' => 'lx_field_advanced_img_sizing',
                    'label' => __('Image Sizing', 'lxbdr'),
                    'name' => 'sizing',
                    'type' => 'select',
                    'choices' => [
                        'width-auto-height-auto' => __('Natural Size', 'lxbdr'),
                        'width-full-height-auto' => __('Full Width', 'lxbdr'),
                        'width-auto-height-full' => __('Full Height', 'lxbdr'),
                        'width-full-height-full' => __('Full Width & Height', 'lxbdr'),
                    ],
                    'default_value' => 'width-auto-height-auto',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                ],
                [
                    'key' => 'lx_field_advanced_img_custom_width',
                    'label' => __('Custom Width', 'lxbdr'),
                    'name' => 'custom_width',
                    'type' => 'text',
                    'placeholder' => __('e.g., 500px, 50%, 20rem', 'lxbdr'),
                    'instructions' => __('Enter a valid CSS width value (px, %, rem, etc.)', 'lxbdr'),
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'lx_field_advanced_img_sizing',
                                'operator' => '!=',
                                'value' => 'width-full-height-full',
                            ],
                            [
                                'field' => 'lx_field_advanced_img_sizing',
                                'operator' => '!=',
                                'value' => 'width-full-height-auto',
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'lx_field_advanced_img_custom_height',
                    'label' => __('Custom Height', 'lxbdr'),
                    'name' => 'custom_height',
                    'type' => 'text',
                    'placeholder' => __('e.g., 300px, 50vh, 15rem', 'lxbdr'),
                    'instructions' => __('Enter a valid CSS height value (px, vh, rem, etc.)', 'lxbdr'),
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'lx_field_advanced_img_sizing',
                                'operator' => '!=',
                                'value' => 'width-full-height-full',
                            ],
                            [
                                'field' => 'lx_field_advanced_img_sizing',
                                'operator' => '!=',
                                'value' => 'height-full-width-auto',
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'lx_field_advanced_img_object_fit',
                    'label' => __('Object Fit', 'lxbdr'),
                    'name' => 'object_fit',
                    'type' => 'select',
                    'choices' => [
                        'none' => __('None', 'lxbdr'),
                        'contain' => __('Contain', 'lxbdr'),
                        'cover' => __('Cover', 'lxbdr'),
                    ],
                    'default_value' => 'none',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                ],
                [
                    'key' => 'lx_field_advanced_img_focal_x',
                    'label' => __('Horizontal Focus (X)', 'lxbdr'),
                    'name' => 'focal_x',
                    'type' => 'number',
                    'default_value' => 50,
                    'min' => 0,
                    'max' => 100,
                    'step' => 1,
                    'append' => '%',
                    'instructions' => __('Set horizontal focus point (0% = left, 100% = right)', 'lxbdr'),
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'lx_field_advanced_img_object_fit',
                                'operator' => '!=',
                                'value' => 'none',
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'lx_field_advanced_img_focal_y',
                    'label' => __('Vertical Focus (Y)', 'lxbdr'),
                    'name' => 'focal_y',
                    'type' => 'number',
                    'default_value' => 50,
                    'min' => 0,
                    'max' => 100,
                    'step' => 1,
                    'append' => '%',
                    'instructions' => __('Set vertical focus point (0% = top, 100% = bottom)', 'lxbdr'),
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'lx_field_advanced_img_object_fit',
                                'operator' => '!=',
                                'value' => 'none',
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'lx_field_advanced_img_display',
                    'label' => __('Display Type', 'lxbdr'),
                    'name' => 'display',
                    'type' => 'select',
                    'choices' => [
                        'inline-block' => __('Inline Block', 'lxbdr'),
                        'block' => __('Block', 'lxbdr'),
                    ],
                    'default_value' => 'inline-block',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                ],
                [
                    'key' => 'lx_field_advanced_img_responsive',
                    'label' => __('Responsive Settings', 'lxbdr'),
                    'name' => 'sources',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => __('Add Breakpoint', 'lxbdr'),
                    'sub_fields' => [
                        [
                            'key' => 'lx_field_advanced_img_responsive_breakpoint',
                            'label' => __('Media Query', 'lxbdr'),
                            'name' => 'media_query',
                            'type' => 'text',
                            'required' => 1,
                            'min' => 0,
                            'append' => 'px',
                            'instructions' => __('Enter breakpoint width in pixels', 'lxbdr'),
                        ],
                        [
                            'key' => 'lx_field_advanced_img_responsive_img_id',
                            'label' => __('Image', 'lxbdr'),
                            'name' => 'img_id',
                            'type' => 'image',
                            'required' => 1,
                            'return_format' => 'id',
                            'preview_size' => 'medium',
                            'library' => 'all',
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'none',
                    ],
                ],
            ],
            'active' => false,
            'description' => __('Advanced image configuration fields. Clone this to your field.', 'lxbdr'),
        ]);

        \acf_add_local_field_group([
            'key' => 'lx_group_responsive_img',
            'title' => __('WpTemplateHelper Responsive Image Configuration', 'lxbdr'),
            'fields' => [
                [
                    'key' => 'lx_field_advanced_img_base',
                    'label' => __('Base Image', 'lxbdr'),
                    'name' => 'base_img',
                    'type' => 'image',
                    'required' => 1,
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                ],
                [
                    'key' => 'lx_field_advanced_img_responsive',
                    'label' => __('Responsive Settings', 'lxbdr'),
                    'name' => 'sources',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => __('Add Breakpoint', 'lxbdr'),
                    'sub_fields' => [
                        [
                            'key' => 'lx_field_advanced_img_responsive_breakpoint',
                            'label' => __('Media Query', 'lxbdr'),
                            'name' => 'media_query',
                            'type' => 'text',
                            'required' => 1,
                            'min' => 0,
                            'append' => 'px',
                            'instructions' => __('Enter breakpoint width in pixels', 'lxbdr'),
                        ],
                        [
                            'key' => 'lx_field_advanced_img_responsive_img_id',
                            'label' => __('Image', 'lxbdr'),
                            'name' => 'img_id',
                            'type' => 'image',
                            'required' => 1,
                            'return_format' => 'id',
                            'preview_size' => 'medium',
                            'library' => 'all',
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'none',
                    ],
                ],
            ],
            'active' => false,
            'description' => __('Responsive image configuration fields. Clone this to your field', 'lxbdr'),
        ]);
    }

}
