<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;
class setting {
    private static $instance = null;
    public static function instance() {
        if (self::$instance == null)
            self::$instance = new setting();
        return self::$instance;
    }

    private $configs = array();
    
    private function __construct() {
        $configs = db_setting::inst()->load_all();
        foreach ($configs as $config) {
            $key = $config["name"];
            $this->configs[$key] = $config;
        }
    }

    public function load($key, $def = null) {
        return isset($this->configs[$key]) ? $this->configs[$key]["value"] : $def;
    }

    public function save($key, $val) {
        $id = db_setting::inst()->save($key, $val);
        if ($id !== false) {
            $this->configs[$key] = array("id" => $id, "name" => $key, "value" => $val);
        }
        return $id;
    }

    public function load_all() {
        return $this->configs;
    }

    public static function update($id, $value) {
        return db_setting::inst()->modify($id, $value);
    }
};


