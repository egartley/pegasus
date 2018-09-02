<?php

require_once '../includes/pages/storage.php';

function get_page_list_item_html($page) {
	return "<div class=\"listing\"><span><a rel=\"noopener\" href=\"/editor/?action=edit&id=" . $page->id . "\">" . $page->title . "</a></span></div>";
}

function getAllPagesListHTML() {
	$r = "<div class=\"all-pages-list-scroller\"><div class=\"all-pages-list\">";
	$pagedirs = get_page_dirs(true);
	for ($i = 0; $i < count($pagedirs); $i++) {
		$r .= get_page_list_item_html(get_page($i));
	}
	return $r . "</div></div>";
}

?>