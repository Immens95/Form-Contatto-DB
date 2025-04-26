<?php
add_action('init', 'fsdb_register_block');

function fsdb_register_block() {
    wp_register_script('fsdb-block', FSDB_URL . 'js/form-block.js', ['wp-blocks', 'wp-element', 'wp-editor', 'wp-block-editor'], '1.0', true);
    register_block_type('fsdb/form', [
        'editor_script' => 'fsdb-block',
        'render_callback' => 'fsdb_render_form_block',
        'attributes' => [],
          'supports' => [
            'color' => [
              'gradients' => true,
              'background' => true,
              'text' => true,
              'button' => true,
            ],
            'spacing' => [
              'margin' => true,
              'padding' => true
            ],
            'typography' => [
              'fontSize' => true,
              'lineHeight' => true,
              'textAlign' => true,
              'fontWeight' => true,
              'fontFamily' => true
            ],
            'align' => true,
            'alignWide' => true,
            'background' => [
              'backgroundImage' => true,
              'backgroundSize' => true
            ],
            'border' => [
              'color' => true,
              'width' => true,
              'radius' => true,
              'style' => true
            ],
            'position' => [
              'sticky' => true
            ],
            'shadow' => true,
            'customClassName' => true
          ]
    ]);
}

add_action('wp_enqueue_scripts', 'fsdb_enqueue_frontend');

function fsdb_enqueue_frontend() {
    wp_enqueue_script('fsdb-submit', FSDB_URL . 'js/form-submit.js', ['jquery'], '1.0', true);
    wp_enqueue_style('fsdb-style', FSDB_URL . 'style.css');
    wp_localize_script('fsdb-submit', 'fsdb_ajax', [
        'url' => rest_url('fsdb/v1/send')
    ]);
}


add_action('admin_enqueue_scripts', 'fsdb_enqueue_backend');

function fsdb_enqueue_backend() {
    wp_enqueue_style('fsdb-style', FSDB_URL . 'style.css');
}
