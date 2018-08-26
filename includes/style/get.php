<?php

$STYLE_min = 'min.css';
$STYLE_all_pages_list = 'all-pages-list.css';

function getStylesheet($sheet) {
	echo "<link href=\"/resources/css/" . $sheet . "\" rel=\"stylesheet\" type=\"text/css\">";
}

?>