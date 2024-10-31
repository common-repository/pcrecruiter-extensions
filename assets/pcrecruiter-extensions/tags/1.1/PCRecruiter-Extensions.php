<?php
/*
Plugin Name: PCRecruiter Extensions
Plugin URI: https://www.pcrecruiter.net
Description: Embeds the PCRecruiter Web Extensions into any WordPress page.
Version: 1.1
Author: Main Sequence Technology, Inc.
Author URI: https://www.pcrecruiter.net
License: GPL3
*/
function pcr_frame($atts) {
    $a = shortcode_atts( array(
        'link' => 'about:blank',
        'background' => 'transparent',
		'initialheight' => '640',
    ), $atts);
	// If the link doesn't start with http, prepend the ASP URL
	$loadurl = $a['link'];
	if (substr( $loadurl, 0, 4 ) !== "http") {
		$aspurl = 'https://www2.pcrecruiter.net/pcrbin/'.$loadurl;
		$loadurl = $aspurl;
	};
    return "<script src=\"https://www2.pcrecruiter.net/pcrimg/inc/pcrframehost.js\"></script><link rel=\"stylesheet\" href=\"https://www2.pcrecruiter.net/pcrimg/inc/pcrframehost.css\"><iframe frameborder=\"0\" host=\"{$loadurl}\" id=\"pcrframe\" name=\"pcrframe\" src=\"about:blank\" style=\"height:{$a['initialheight']}px;width:100%;background-color:{$a['background']};border:0;margin:0;padding:0\" onload=\"pcrframeurl();\"></iframe>";
}
add_shortcode( 'PCRecruiter', 'pcr_frame' );
?>