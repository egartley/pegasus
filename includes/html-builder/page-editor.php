<?php

function get_editable_meta_textbox() {
}

function get_editor_html($page, $action) {
	// script for getting values and all that fun stuff
	$html = "<script src=\"../resources/js/jquery.js\" type=\"text/javascript\"></script><script src=\"../resources/js/editor.js\" type=\"text/javascript\"></script>";

	// page meta
	$html .= "<div class=\"page-meta\"><span style=\"display:hidden\" id=\"isnew\">" . $page->isnew . "</span><p>Title: <input id=\"title\" name=\"title\" type=\"text\" maxlength=\"128\" autocomplete=\"off\" placeholder=\"Untitled\" value=\"" . $page->title . "\"></p><p>Page ID: <span id=\"id\">" . $page->id . "</span></p><p>Created: " . $page->created . "</p><p>Updated: " . $page->updated . "</p></div>";

	// page options (save, delete, etc.)
	$html .= "<div class=\"page-options\"><p style=\"font-size:22px\"><a id=\"save\" rel=\"noopener\" style=\"cursor:pointer\">Save</a></p></div>";

	return $html;
}

?>