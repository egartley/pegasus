<?php

// meta elements
echo "
<meta charset=\"utf-8\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1\">
<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\">
<meta http-equiv=\"cache-control\" content=\"no-cache\">";

// styling
require_once '../includes/style/get.php';
get_stylesheet_link("min.css");

?>