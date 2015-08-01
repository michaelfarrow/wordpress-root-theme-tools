<?php

// Custom Image Quality

function root_jpeg_quality_callback($arg) {
	if(!defined('ROOT_IMAGE_QUALITY'))
		return 80;

  return (int)ROOT_IMAGE_QUALITY;
}

add_filter('jpeg_quality', 'root_jpeg_quality_callback');


// allows upscaling of images

function root_image_upscale($default, $orig_w, $orig_h, $new_w, $new_h, $crop){
    if ( !$crop ){
    	$aspect_ratio = $orig_w / $orig_h;
	    $new_ratio = $new_w / $new_h;

	    if($aspect_ratio > $new_ratio){
	    	return array( 0, 0, 0, 0, (int) $new_w, (int) $new_w / $aspect_ratio, (int) $orig_w, (int) $orig_h);
	    }else{
	    	return array( 0, 0, 0, 0, (int) $new_h * $aspect_ratio, (int) $new_h, (int) $orig_w, (int) $orig_h);
	    }

    }else{
    	$aspect_ratio = $orig_w / $orig_h;
	    $size_ratio = max($new_w / $orig_w, $new_h / $orig_h);

	    $crop_w = round($new_w / $size_ratio);
	    $crop_h = round($new_h / $size_ratio);

	    $s_x = floor( ($orig_w - $crop_w) / 2 );
	    $s_y = floor( ($orig_h - $crop_h) / 2 );

	    return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );
    }
}

add_filter('image_resize_dimensions', 'root_image_upscale', 10, 6);

add_image_size('thumbnail', 150, 150, false);
add_image_size('medium', 150, 150, false);
add_image_size('large', 150, 150, false);


// retina images

function root_retina_image_make_intermediate_size($file, $width, $height, $crop=false) {
	if ( $width || $height ) {
		$path = pathinfo($file);
		$path_name = $path['dirname'];
		$updir = wp_upload_dir();
		$original_filename = $file;
		$x2_filename = substr_replace($original_filename, '-'.$width.'x'.$height.'@2x.', strrpos($original_filename, "."), strlen("."));
		$resized_file = wp_get_image_editor($file);
		$resized_file->resize($width*2, $height*2, $crop);
		$resized_file->save($x2_filename);
		if ( !is_wp_error($resized_file) && $resized_file && $info = getimagesize($x2_filename) ) {
			$resized_file = apply_filters('root_retina_image_make_intermediate_size', $file);
			return array(
				'file' => wp_basename( $resized_file ),
				'width' => $info[0],
				'height' => $info[1],
			);
		}
	}
	return false;
}

function root_generate_images($metadata, $filename){
	global $_wp_additional_image_sizes;

	$file = $filename;
	foreach ($metadata as $k => $v) {
		if (is_array($v)) {
			foreach ($v as $key => $val) {
				if (is_array($val)) {
					root_retina_image_make_intermediate_size(
						$file,
						$val['width'],
						$val['height'],
						array_key_exists($key, $_wp_additional_image_sizes) ? $_wp_additional_image_sizes[$key]['crop'] : true
					);
				} 
			} 
		} 
    }
}

function root_generate_retina_attachment_metadata( $metadata, $attachment_id ) {
	$file = get_attached_file($attachment_id);
	$old_metadata = $metadata;
	root_generate_images($metadata, $file);
	return $old_metadata;
}
add_filter('wp_generate_attachment_metadata', 'root_generate_retina_attachment_metadata', 10, 2);

function root_image_edited($null, $filename, $image, $mime_type, $post_id){
	$image->save($filename, $mime_type);
	
	$meta = wp_get_attachment_metadata( $post_id );
	root_generate_images($meta, $filename);

	return $image;
}

function root_delete_retina_images($file){
	$uploadpath = wp_upload_dir();

	$x2_filename = substr_replace($file, '@2x.', strrpos($file, "."), strlen("."));
	if(strpos($x2_filename, $uploadpath['basedir'])===false){
		$x2_filename = path_join($uploadpath['basedir'], $x2_filename);
	}

	if (file_exists($x2_filename)) {
		unlink($x2_filename);
	}

	return $file;
}
add_filter('wp_delete_file', 'root_delete_retina_images', 1, 1);

add_filter('wp_save_image_editor_file', 'root_image_edited', 1, 5 );


// replace all content images with a set size so we can replace it later and not waste bandwidth

function root_replace_images($content){
	global $wpdb;

	$dom = new DOMDocument();
	$dom->loadHTML('<?xml encoding="UTF-8">' . $content);

	// Loop through all images
	$images = $dom->getElementsByTagName('img');
	foreach ($images as $image) {

	  // Do something with the alt
	  $class = $image->getAttribute('class');
	  $src = $image->getAttribute('src');

	  // Replace the image

	  $thepost = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE guid = '%s'", array($src)) );
	  if($thepost){
	  	$imagesrc = wp_get_attachment_image_src($thepost->ID, 'blog');
	  	if($imagesrc) $image->setAttribute("src", $imagesrc[0]);
	  }


	}

	$dom->encoding = 'UTF-8';

	// Get the new HTML string
	return $dom->saveHTML();
}
add_filter('the_content', 'root_replace_images');
