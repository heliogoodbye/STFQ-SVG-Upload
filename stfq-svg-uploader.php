<?php
/*
Plugin Name: STFQ SVG Upload
Description: Allow users to upload SVG files to the media library and sanitize them on upload.
Version: 1.0
Author: Strangefrequency LLC
Author URI: https://strangefrequency.com/
License: GNU General Public License v3.0
*/

// Allow SVG files to be uploaded
function stfq_allow_svg_upload( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'stfq_allow_svg_upload' );

// Sanitize SVG files on upload
function stfq_sanitize_svg( $file ) {
    // Get the file extension
    $file_ext = pathinfo( $file['name'], PATHINFO_EXTENSION );

    // If it's an SVG file, sanitize it
    if ( 'svg' === $file_ext ) {
        $svg_content = file_get_contents( $file['tmp_name'] );
        // Sanitize the SVG content
        $sanitized_svg = stfq_custom_sanitize_svg( $svg_content );
        // Update the file with sanitized content
        file_put_contents( $file['tmp_name'], $sanitized_svg );
    }
    return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'stfq_sanitize_svg' );

function stfq_custom_sanitize_svg( $svg_content ) {
    // Create a DOMDocument object
    $dom = new DOMDocument();

    // Load the SVG content into the DOMDocument
    $dom->loadXML( $svg_content );

    // Get all elements in the SVG
    $svg_elements = $dom->getElementsByTagName('svg');

    // Loop through each SVG element
    foreach ($svg_elements as $svg_element) {
        // Remove potentially harmful attributes
        $attributes_to_remove = array(
            'onload', 'onclick', 'onmouseover', 'onmouseout', 'onmousemove', 'onmousedown', 'onmouseup', 'onkeydown', 'onkeypress', 'onkeyup'
        );
        foreach ($attributes_to_remove as $attribute) {
            if ($svg_element->hasAttribute($attribute)) {
                $svg_element->removeAttribute($attribute);
            }
        }
    }

    // Get all <script> elements in the SVG
    $script_elements = $dom->getElementsByTagName('script');

    // Remove all <script> elements from the SVG
    foreach ($script_elements as $script_element) {
        $script_element->parentNode->removeChild($script_element);
    }

    // Save the sanitized SVG content
    $sanitized_svg = $dom->saveXML();

    return $sanitized_svg;
}
