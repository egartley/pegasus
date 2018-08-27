<?php

class Page {
	public $title = "Untitled";
	public $id = 0;

	public $created = "-6106017600";
	public $updated = "1484913600";

	public function __toString() {
		return $this->title . " (" . $this->id . ")";
	}

	function __construct($id, $load) {
		$this->id = $id;
		if ($load) {
			$this->load();
		}
	}

	private function load() {
		// meta (title, dates/times, etc.)
		if ($rawmetajson = file_get_contents("../data-storage/pages/" . $this->id . "/meta.json")) {
			$meta = json_decode($rawmetajson, true);
			$this->title = $meta["title"];
			$this->created = $meta["created"];
			$this->updated = $meta["updated"];
		} else {
			// page's meta.json doesn't exist
			// all properties will stay at their defaults (except for id)
		}
	}

	function asPrettyString_created() {
		// TODO: convert from unix to pretty
		return $created;
	}

	function asPrettyString_updated() {
		// TODO: convert from unix to pretty
		return $updated;
	}
}

?>