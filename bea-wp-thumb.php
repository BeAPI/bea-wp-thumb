<?php
/*
Plugin Name:  BEA WP Thumb
Description:  Prevent WP from generating resized images on upload
Plugin URI:   http://www.beapi.fr
Version:      1.0
Author:       BeAPI
Author URI:   http://www.beapi.fr
*/

// prevent WP from generating resized images on upload
add_filter( 'intermediate_image_sizes_advanced','bea_dynimg_image_sizes_advanced' );
function bea_dynimg_image_sizes_advanced( $sizes ) {
	if ( ! class_exists( 'WP_Thumb' ) ) {
		return $sizes;
	}

	global $dynimg_image_sizes;

	// save the sizes to a global, because the next function needs them to lie to WP about what sizes were generated
	$dynimg_image_sizes = $sizes;

	// Get all editor sizes
	$default_sizes = apply_filters( 'image_size_names_choose', array(
		'thumbnail' => __( 'Thumbnail' ),
		'medium'    => __( 'Medium' ),
		'large'     => __( 'Large' ),
	) );

	foreach ( $default_sizes as $size => $name ) {
		if ( ! isset( $sizes[ $size ] ) ) {
			continue;
		}
		$default_sizes[ $size ] = $sizes[ $size ];
	}

	// tell WordPress to generate only default sizes
	return $default_sizes;
}

/**
 * Remove WPThumb 0.9 filter for the admin
 *
 * @return bool
 */
function bea_fix_wpthumb_09_image_downsize() {
	if ( ! is_admin() || ! class_exists( 'WP_Thumb' ) ) {
		return false;
	}

	if ( ! function_exists( 'get_plugin_data' ) ) {
		/** WordPress Plugin Administration API */
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	$infos = get_plugin_data( WP_CONTENT_DIR . '/plugins/wp-thumb/wpthumb.php' );
	if ( empty( $infos['Version'] ) || version_compare( $infos['Version'], '0.10', '>=' ) ) {
		return false;
	}

	return remove_filter( 'image_downsize', 'wpthumb_post_image', 99 );
}
add_action( 'wp_loaded', 'bea_fix_wpthumb_09_image_downsize' );
