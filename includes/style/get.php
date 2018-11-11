<?php

function get_stylesheet_link($sheet) {
	echo "<link href=\"/resources/css/" . $sheet . "\" rel=\"stylesheet\" type=\"text/css\">";
}

function get_stylesheet_inline($sheet) {
	$r = "<style type=\"text/css\">";
	try {
    	$content = file_get_contents("../resources/css/" . $sheet);
    	if ($content === false) {
        	$r .= ".page-content:before{content:\"Could not find \\\"/resources/css/" . $sheet . "\\\"\"}";
    	} else {
    		$r .= $content;
    	}
    	$r .= "</style>";
	} catch (Exception $e) {
		// todo
	}
	return $r;
}

?>