<?php

// meta elements
echo "
<meta charset=\"utf-8\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1\">
<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\">
<link href=\"/resources/ico/favicon.ico\" rel=\"icon\">
<link href=\"/resources/ico/favicon.ico\" rel=\"shortcut icon\" type=\"images/x-icon\">";

// styling
require_once '../includes/style/get.php';
get_stylesheet_link("min.css");