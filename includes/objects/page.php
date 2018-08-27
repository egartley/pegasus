<?php

class Page {
	public static $storageFilePath = "../data-storage/pages";

	public $filePath = "";
	public $metaFilePath = "";
	public $contentFilePath = "";

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
			$this->updateFilePaths();
			$this->load();
		}
	}

	private function updateFilePaths() {
		$this->filePath = "../data-storage/pages/" . $this->id;
		$this->metaFilePath = $this->filePath . "/meta.json";
		$this->contentFilePath = $this->filePath . "/content.json";
	}

	private function load() {
		// meta (title, dates/times, etc.)
		if ($rawmetajson = file_get_contents($this->metaFilePath)) {
			$meta = json_decode($rawmetajson, true);
			$this->title = $meta["title"];
			$this->created = $meta["created"];
			$this->updated = $meta["updated"];
		} else {
			// page's meta.json doesn't exist
			// all properties will stay at their defaults (except for id)
		}
	}

	function getContentJSON() {
		if (!file_exists($this->contentFilePath)) {
			return null;
		} else {
			return file_get_contents($this->contentFilePath);
		}
	}

	function asPrettyString_created() {
		// TODO: convert from unix to pretty
		return $this->created;
	}

	function asPrettyString_updated() {
		// TODO: convert from unix to pretty
		return $this->updated;
	}
}

?>