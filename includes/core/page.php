<?php

require_once '../includes/core/page-storage.php';

class Page
{
    public static $storageFilePath = "../data-storage/pages";
    public static $tempStorageFilePath = "../data-storage/temporary-page";
    public static $maxNumberOfPages = 5000;
    public static $emptyContentRawJSON = "{\"modules\":[{\"type\":\"section-content\",\"value\":[{\"type\":\"paragraph\",\"value\":[{\"type\":\"plain\",\"value\":\"Type anything\"}]}]}],\"infobox\":{\"heading\":\"Infobox\",\"image\":{\"file\":\"/resources/png/infobox-default.png\",\"caption\":\"Add your own image\"},\"items\":[{\"type\":\"property\",\"label\":\"Property\",\"value\":\"Value\"}]}}";
    public static $defaultTitle = "Untitled";
    public static $defaultDateFormat = "F jS, Y, \a\\t g:i A";

    public $filePath = "";
    public $metaFilePath = "";
    public $contentFilePath = "";

    public $title = "Untitled";
    public $id = 0;
    public $slug = "Untitled";
    public $permalink = "";

    public $created = 0;
    public $updated = 0;

    function __construct()
    {
        $this->id = func_get_arg(0);
        if ($this->id == -1) {
            // make new page
            // get next available id
            for ($i = 0; $i < Page::$maxNumberOfPages - 1; $i++) {
                if (file_exists(Page::$storageFilePath . "/" . $i)) {
                    continue;
                }
                $this->id = $i;
                $this->title = Page::$defaultTitle;
                $this->slug = Page::$defaultTitle . "_" . $i;
                break;
            }
            // update paths and set meta/content
            $this->update_paths();
            // create new storage directory
            mkdir($this->filePath);
            $this->write_new_meta();
            $this->write_new_content();
            add_permalink($this);
        } else {
            // previously saved page (assuming)
            // so just set path strings and meta
            $this->update_paths();
            $this->load_meta_from_file();
            // update paths again so that the permalink is correct
            $this->update_paths();
        }
    }

    public static function new_page()
    {
        return new Page(-1);
    }

    public static function action_delete($deleteID)
    {
        $p = get_page($deleteID);
        if ($p !== null) {
            remove_permalink($p->slug);
            Page::delete_page_storage_directory(Page::$storageFilePath . "/" . $deleteID);
        } else {
            return false;
        }
        return true;
    }

    public static function action_save($post)
    {
        // ex. "/editor/?action=save&id=2" POST
        $p = get_page($post["id"]);
        // save meta and content
        $p->write_meta(array(
            "title" => $post["title"], // updated title
            "id" => $p->id, // id doesn't change
            "slug" => $post["slug"], // updated slug
            "created" => $p->created, // created doesn't change
            "updated" => strtotime("now") // updated just now
        ));
        $p->write_content($post);
    }

    public function get_url_slug_from_title()
    {
        // See also: http://www.faqs.org/rfcs/rfc1738.html
        // only alphanumeric and $-_.+!*'(), characters are allowed in URls
        return str_replace([" ", "`", "{", "}", "|", "\\", "^", "~", "[", "]", ";", "/", "?", ":", "@", "=", "&", "#", "<", ">"], "_", $this->title);
    }

    /*private static function get_meta_by_id($id)
    {
        return json_decode(file_get_contents(Page::$storageFilePath . "/" . $id . "/meta.json"), true);
    }*/

    private function write_new_meta()
    {
        $metafile = fopen($this->filePath . "/meta.json", "w");
        if ($metafile === false) {
            // not found or has wrong permissions
            return false;
        }
        // ensure created and update times are the same
        $now = strtotime("now");
        fwrite($metafile, json_encode(array(
            "title" => $this->title, // title from url
            "id" => $this->id, // id from url
            "slug" => $this->slug,
            "created" => $now, // created just now
            "updated" => $now // updated just now
        )));
        return fclose($metafile);
    }

    private function write_new_content()
    {
        $file = fopen($this->contentFilePath, "w");
        if ($file === false) {
            return;
        }
        fwrite($file, Page::$emptyContentRawJSON);
        fclose($file);
    }

    /**
     * Writes the given meta values to the normal meta file
     *
     * @param $meta array
     * @return bool
     */
    public function write_meta($meta)
    {
        $metafile = fopen($this->metaFilePath, "w");
        if ($metafile === false) {
            // not found or has wrong permissions
            return false;
        }
        fwrite($metafile, json_encode($meta));
        fclose($metafile);
        return true;
    }

    public function write_content($post)
    {
        $contentfile = fopen($this->contentFilePath, "w");
        if ($contentfile === false) {
            // not found or has wrong permissions
            return false;
        }
        fwrite($contentfile, urldecode($post["contentjson"]));
        fclose($contentfile);
        return true;
    }

    // Credit: http://php.net/manual/en/function.rmdir.php#117354
    private static function delete_page_storage_directory($src)
    {
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                $full = $src . '/' . $file;
                if (is_dir($full)) {
                    Page::delete_page_storage_directory($full);
                } else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }

    private function update_paths()
    {
        $this->filePath = Page::$storageFilePath . "/" . $this->id;
        $this->metaFilePath = $this->filePath . "/meta.json";
        $this->contentFilePath = $this->filePath . "/content.json";
        $this->permalink = ApplicationSettings::get_url_permalink_for_slug($this->slug);
    }

    /**
     * Sets all meta fields from the values in the meta file (make sure paths have been updated)
     */
    private function load_meta_from_file()
    {
        if ($rawmetajson = file_get_contents($this->metaFilePath)) {
            $meta = json_decode($rawmetajson, true);
            $this->title = $meta["title"];
            $this->id = $meta["id"];
            $this->slug = $meta["slug"];
            $this->created = $meta["created"];
            $this->updated = $meta["updated"];
        } else {
            // meta.json doesn't exist
            // all fields will stay at whatever they were
        }
    }

    /**
     * To be used outside of the Page class. Call this after changing a meta field value
     */
    public function public_write_meta()
    {
        $meta = array(
            "title" => $this->title,
            "id" => $this->id,
            "slug" => $this->slug,
            "created" => $this->created,
            "updated" => $this->updated
        );
        $metafile = fopen($this->metaFilePath, "w");
        if ($metafile === false) {
            // not found or has wrong permissions
            return;
        }
        fwrite($metafile, json_encode($meta));
        fclose($metafile);
    }

    function write_permalink_index_html(string $customslug = "")
    {
        require_once '../includes/html-builder/published-page.php';

        $path = "../" . $this->permalink . "/index.html";
        if ($customslug !== "") {
            // replace this slug with specified slug
            $path = str_replace($this->slug, $customslug, $path);
        }
        $indexhtml = fopen($path, "w");
        if ($indexhtml === false) {
            return false;
        }
        fwrite($indexhtml, get_published_page_html($this));
        fclose($indexhtml);
        return true;
    }

    /**
     * Returns the page's raw content JSON (content.json) as a string if it exists, otherwise returns <code>null</code>
     *
     * @return false|string|null
     */
    function get_content_rawjson()
    {
        if (!file_exists($this->contentFilePath)) {
            return null;
        } else {
            return file_get_contents($this->contentFilePath);
        }
    }

    function asPrettyString_created()
    {
        return date(Page::$defaultDateFormat, $this->created);
    }

    function asPrettyString_updated()
    {
        return date(Page::$defaultDateFormat, $this->updated);
    }

    /**
     * @return string $title ($id)
     */
    public function __toString()
    {
        return $this->title . " (" . $this->id . ")";
    }
}