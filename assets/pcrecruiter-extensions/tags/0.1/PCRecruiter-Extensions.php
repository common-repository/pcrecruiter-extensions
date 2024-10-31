<?php
/*
Plugin Name: PCRecruiter Extensions
Plugin URI: https://www.pcrecruiter.net
Description: Embeds the PCRecruiter Web Extensions into any WordPress page.
Version: 1.0
Author: Main Sequence Technology, Inc.
Author URI: https://www.pcrecruiter.net
License: GPL3
*/
function pcr_frame($atts) {
    $a = shortcode_atts( array(
        'link' => 'https://www2.pcrecruiter.net/pcrimg/spacer.gif',
        'background' => 'transparent',
		'initialheight' => '640',
    ), $atts);
    return "<script src=\"https://www2.pcrecruiter.net/pcrimg/inc/pcrframehost.js\"></script><link rel=\"stylesheet\" href=\"https://www2.pcrecruiter.net/pcrimg/inc/pcrframehost.css\"><iframe frameborder=\"0\" host=\"{$a['link']}\" id=\"pcrframe\" name=\"pcrframe\" src=\"https://www2.pcrecruiter.net/pcrimg/spacer.gif\" style=\"height:{$a['initialheight']}px;width:100%;background-color:{$a['background']};border:0;margin:0;padding:0\" onload=\"pcrframeurl();\"></iframe>";
}
add_shortcode( 'PCRecruiter', 'pcr_frame' );
?>