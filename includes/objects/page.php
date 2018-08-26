<?php

class Page {
	public $id = "";
	public $title = "";

	function __construct($id, $title) {
		$this->id = $id;
		$this->title = $title;
	}
}

?>