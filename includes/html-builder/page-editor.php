<?php

function get_editable_meta_textbox() {
}

function get_editor_html($page, $action) {
	// script for getting values and all that fun stuff
	$html = "<script src=\"../resources/js/jquery.js\" type=\"text/javascript\"></script><script src=\"../resources/js/editor.js\" type=\"text/javascript\"></script>";

	// page meta
	$html .= "<div class=\"page-meta\"><span style=\"display:none\" id=\"isnew\">" . $page->isnew . "</span><p>Title: <input id=\"title\" name=\"title\" style=\"width:252px\" type=\"text\" maxlength=\"200\" autocomplete=\"off\" placeholder=\"Untitled\" value=\"" . $page->title . "\"></p><p>Page ID: <span id=\"id\">" . $page->id . "</span></p><p>Created: " . $page->created . "</p><p>Updated: " . $page->updated . "</p></div>";

	// page options (save, delete, etc.)
	$html .= "<div class=\"page-options\"><p><a id=\"save\" rel=\"noopener\" style=\"cursor:pointer\">Save</a></p><p><a rel=\"noopener\" href=\"/dashboard/\">Back to Dashboard</a></p></div>";

	return $html;
}

?>