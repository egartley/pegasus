<?php

function get_editable_meta_textbox() {
}

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
		// css file no found!
		$html .= "body:before{content:\"Could not find \\\"/resources/page-editor.css\\\"\"}";
	}
	$html .= "</style>";

	// page meta (broken into seperate lines for better readability)
	$html .= "<div class=\"page-meta\">";
	$html .= "<form method=\"post\" action=\"/editor/\">";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"save\">";
	$html .= "<input type=\"hidden\" name=\"isnew\" value=\"" . $page->isnew . "\">";
	$html .= "<p>Title: <input type=\"text\" name=\"title\" value=\"" . $page->title . "\" placeholder=\"Untitled\"></p>";
	$html .= "<p>Page ID: <input type=\"text\" name=\"id\" value=\"" . $page->id . "\" readonly></p>";
	$html .= "<p>Created: <input type=\"text\" name=\"created\" value=\"" . $page->created . "\" readonly></p>";
	$html .= "<p>Updated: <input type=\"text\" name=\"updated\" value=\"" . $page->updated . "\" readonly></p>";
	$html .= "<p><input type=\"submit\" value=\"Save\"></p>";
	$html .= "</form></div>";

	// page options
	$html .= "<div class=\"page-options\"><p><a rel=\"noopener\" href=\"/dashboard/\">Back to Dashboard</a></p></div>";

	if ($action == "edit") {
		require_once '../includes/html-builder/page-content.php';
		$html .= "<iframe class=\"viewer\" src=\"/viewer/?id=" . $page->id . "\" width=\"1250\" height=\"900\">" . "</iframe>";
	}

	return $html;
}

?>