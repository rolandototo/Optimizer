<?php
/**
 * Plugin Name: Optimizer
 * Description: A WordPress plugin that optimizes images, scripts, and stylesheets for faster site performance.
 * Version: 1.0.0
 * Author: Rolando Escobar
 * Author URI: https://rolandototo.dev/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */


// Optimize images
function optimize_images( $attachment_id ) {
    $attachment = get_post( $attachment_id );
    $file_path = get_attached_file( $attachment_id );
    $file_info = wp_check_filetype( $file_path );

    if ( ! $file_info['ext'] ) {
        return false;
    }

    $image_sizes = get_intermediate_image_sizes();

    foreach ( $image_sizes as $size ) {
        $image = wp_get_attachment_image_src( $attachment_id, $size );
        $image_path = dirname( $file_path ) . '/' . $image[0];

        if ( file_exists( $image_path ) ) {
            $optimized_image = apply_filters( 'optimizer_optimize_image', $image_path );

            if ( $optimized_image ) {
                $image_meta = wp_get_attachment_metadata( $attachment_id );
                $image_meta['sizes'][$size]['file'] = $optimized_image;
                wp_update_attachment_metadata( $attachment_id, $image_meta );
            }
        }
    }

    return true;
}
add_action( 'add_attachment', 'optimize_images' );

// Optimize scripts
function optimize_scripts() {
    if ( ! is_admin() ) {
        wp_deregister_script( 'jquery' );
        wp_register_script( 'jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', array(), '3.6.0' );
        wp_enqueue_script( 'jquery' );
    }
}
add_action( 'wp_enqueue_scripts', 'optimize_scripts' );

// Optimize stylesheets
function optimize_stylesheets() {
    if ( ! is_admin() ) {
        wp_enqueue_style( 'style', get_stylesheet_uri(), array(), filemtime( get_stylesheet_directory() . '/style.css' ) );
    }
}
add_action( 'wp_enqueue_scripts', 'optimize_stylesheets' );