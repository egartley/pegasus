<?php

function get_editor_html($page, $action) {
	if ($page == null) {
		return "<p>Specified page is invalid</p>";
	}

	// script for getting values and all that other fun stuff
	$html = "<script src=\"../resources/js/jquery.js\" type=\"text/javascript\"></script><script src=\"../resources/js/editor.js\" type=\"text/javascript\"></script>";
	// style
	$html .= "<style type=\"text/css\">";
	if (file_exists("../resources/css/page-editor.css")) {
		$html .= file_get_contents("../resources/css/page-editor.css");
	} else {
		// css file not found!
		$html .= "body:before{content:\"Could not find \\\"/resources/page-editor.css\\\"\"}";
	}
	$html .= "</style>";

	// page meta
	// $html .= "<h2 style=\"display:none\">Meta</h2>";
	// $html .= "<div class=\"page-meta\" style=\"display:none\">";
	// $html .= "<p>Page ID: " . $page->id . "</p>";
	// $html .= "<p>Created: " . $page->created . "</p>";
	// $html .= "<p>Updated: " . $page->updated . "</p>";
	// $html .= "</div>";

	// page options
	// $html .= "<div class=\"page-options\" style=\"display:none\"><p><a rel=\"noopener\" href=\"/dashboard/\">Back to Dashboard</a></p></div>";

	// the actual editor
	$html .= "<div><h2>Editor</h2></div><iframe class=\"viewer\" src=\"/viewer/?id=" . $page->id . "&edit=yes";
	if ($action == "new") {
		$html .= "&temp=yes";
	}
	$html .= "\" width=\"1300\" height=\"900\"></iframe>";

	return $html;
}

?>