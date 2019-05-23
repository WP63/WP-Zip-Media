<?php
/*
Plugin Name: WP Zip Media
Plugin URI: https://wp63.co/plugins/wp-zip-media
Description: Create compressed zip file from media library
Version: 1.0
Author: WP63
Author URI: https://wp63.co
License: GPLv2 or later
textdomain: WPZM
*/

namespace WP63\Plugins;

define( 'WPZM_ROOT', plugin_dir_path( __FILE__ ) );
require_once( WPZM_ROOT . 'libs/helpers.php');

use function WP63\Helpers\create_zip_media;
use function WP63\Helpers\get_zip_media;

/**
 * Add custom JS/CSS to wp-admin
 */
add_action( 'admin_enqueue_scripts', function (){
  wp_enqueue_script('WP63/admin.js', plugin_dir_url( __FILE__ ) . 'assets/admin.js' , ['jquery'], null, true);
});

/**
 * Add "Download selected as a zip file" into media bulk action
 */
add_filter('bulk_actions-upload', function( $actions ) {
  $actions['download'] = __('Create zip file', 'WPZM');
  return $actions;
});

/**
 * Handle download
 */
add_filter('handle_bulk_actions-upload', function( $redirect, $action, $files) {
  if( 'download' !== $action ) {
    return $redirect;
  }

  $files = array_map(function( $file ){
    return get_attached_file( $file );
  }, $files);

  $files = array_filter($files, function( $file ) {
    return !( $file === false );
  });

  if( isset( $_REQUEST['bundle_file_name'] ) ) {
    $filename = sanitize_text_field( $_REQUEST['bundle_file_name'] );
  } else {
    $filename = 'attachment-bundle-' . date('Ymd-Gi') . '.zip';
  }

  if( substr( $filename, -4 ) !== '.zip' ) {
    $filename = $filename . '.zip';
  }

  $zip = create_zip_media( $filename, $files );
  $redirect = add_query_arg('download', $zip['id'], $redirect);

  return $redirect;
}, 10, 3);

/**
 * Show admin notice for file download
 */
add_action('admin_notices', function(){
  if( !empty( $_REQUEST['download'] ) ) {
    $file = get_zip_media( sanitize_text_field( $_REQUEST['download'] ) );

    if( $file ) {
      echo '
      <div id="message" class="updated notice fade is-dismissible">
        <p>'. __('Your file is ready:', 'WPZM') .' <a href="' . $file['url'] . '">' . __('Download', 'WPZM') . '</a></p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __('Dismiss', 'WPZM') . '</span></button>
      </div>
    ';
    } else {
      echo '
      <div id="message" class="notice notice-error fade is-dismissible">
        <p>'. __('Invalid file request', 'WPZM') .'</p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __('Dismiss', 'WPZM') . '</span></button>
      </div>
    ';
    }
  }
});
