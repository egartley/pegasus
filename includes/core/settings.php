<?php

class ApplicationSettings
{
    public static $default_permalinkStructure = "/page/@SLUG";
    public static $storageFilePath = "../data-storage/app/";
    public static $storageFileName = "settings.json";
    public static $permalinkStructure = "";

    static function set_defaults()
    {
        ApplicationSettings::$permalinkStructure = ApplicationSettings::$default_permalinkStructure;
    }

    static function set_values($jsonstring)
    {
        $json = json_decode($jsonstring, true);
        ApplicationSettings::$permalinkStructure = $json["permalink-structure"];
    }

    static function get_json_string()
    {
        return "{\"permalink-structure\":\"" . ApplicationSettings::$permalinkStructure . "\"}";
    }
}

function check_settings_storage_file($set = false)
{
    $settingsjson = ApplicationSettings::$storageFilePath . ApplicationSettings::$storageFileName;
    if (file_exists($settingsjson)) {
        // all good
        if ($set) {
            ApplicationSettings::set_values(file_get_contents($settingsjson));
        }
        return;
    }
    // file doesn't exist

    ApplicationSettings::set_defaults();

    $file = fopen($settingsjson, "w");
    if ($file === false) {
        return;
    }
    // write file with default values
    fwrite($file, ApplicationSettings::get_json_string());
    fclose($file);
}