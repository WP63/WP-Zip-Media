<?php
namespace WP63\Helpers;

use ZipArchive;

/**
 * Create zip file and insert into Media Library
 *
 * @param   string      $filename   Output filename
 * @param   array       $filelist   List of file path to be added into zip file
 * @param   boolean     $update       Whether update existing $filename or create new one. Default FALSE
 *
 * @return  array                   Zip detail
 */
function create_zip_media($filename, $filelist, $update = false) {
  $upload_dir = wp_upload_dir();

  if( substr( $filename, -4 ) !== '.zip' ) {
    $filename = $filename . '.zip';
  }

  if( $update === false && file_exists( $upload_dir['path'] . '/' . $filename )) {
    for( $i = 1, $exists = false; $exists === false; $i++ ) {
      $newfilename = str_replace(".zip", "-{$i}.zip", $filename);

      if( !file_exists($upload_dir['path'] . '/' . $newfilename) ) {
        $filename = $newfilename;
        $exists = true;
      }
    }
  }

  $original_filename = $filename;
  $filename = str_replace(' ', '-', html_entity_decode($filename));

  $zip_path = $upload_dir['path'] . '/' . $filename;
  $zip_url = $upload_dir['url'] . '/' . $filename;

  $zip_file = new ZipArchive;

  if( $zip_file->open($zip_path, ZipArchive::CREATE) === true ) {
    foreach( $filelist as $file ) {
      $zip_file->addFile( $file, basename( $file ) );
    }
  }

  $zip_file->close();

  $filetype = wp_check_filetype( basename( $zip_path ), null );

  $insert = wp_insert_attachment([
    'guid' => $zip_url,
    'post_mime_type' => $filetype['type'],
    'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $zip_path ) ),
    'post_content' => '',
    'post_status' => 'publish'
  ], $zip_path);

  add_post_meta( $insert, 'is_bundle', 'true' );

  return [
    'id' => $insert,
    'filename' => preg_replace( '/\.[^.]+$/', '', basename( $zip_path ) ),
    'filesize' => filesize( $zip_path ),
    'url' => $zip_url,
  ];
}

/**
 * Get zip download data
 *
 * @param   integer      $id      File id
 * @return  array                 Zip detail
 */
function get_zip_media( $id ) {
  $is_bundle = get_post_meta( $id, 'is_bundle', true );

  if( empty( $is_bundle ) ) {
    return false;
  }

  $file = get_attached_file( $id );

  $filename = preg_replace( '/\.[^.]+$/', '', basename( $file ) );
  $filesize = filesize( $file );
  $url = wp_get_attachment_url( $id );

  return [
    'id' => $id,
    'filename' => $filename,
    'filesize' => $filesize,
    'url' => $url,
  ];
}
