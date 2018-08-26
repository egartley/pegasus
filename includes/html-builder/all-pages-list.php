<?php

// need way to access however pages are stored (haven't decided between SQL database(s) or plain-text JSON that is publicly accessible as opposed to a SQL database which is protected server-side and with credentials)

// hardcoded HTML has whitespace, for now, so that the final HTML on whatever page it's displayed on looks somewhat formatted (even though it doesn't really matter)

function getListingHTML($pagetitle = 'Untitled Page') {
	// probably some parameter for what page to get or something like that
	return "
			<div class=\"listing\"><span>" . $pagetitle . "</span></div>";
}

function getAllPagesListHTML() {
	// for now just hardcode it
	return "
	<div class=\"all-pages-list-scroller\">
		<div class=\"all-pages-list\">" . getListingHTML('Federal government of the United States') . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . getListingHTML() . "
		</div>
	</div>
	";
}

?>