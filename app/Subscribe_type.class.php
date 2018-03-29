<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;


class Subscribe_type {
    private $mSummary = null;

    public function __construct($summary = array()) {
        if (empty($summary)) {
            $summary = array(
                "id" => 0,
            );
        }
        $this->mSummary = $summary;
    }

    public function id() {
        return $this->mSummary["id"];
    }

    public function name() {
        return $this->mSummary["name"];
    }

    public function setName($n) {
        $this->mSummary["name"] = $n;
    }


    public function save() {
        // $id = $this->id();
        // if ($id == 0) {
        //     $id = db_Subscribe_type_type::inst()->add();
        //     if ($id !== false) {
        //         $this->mSummary["id"] = $id;
        //     }
        // } else {
        //     $id = db_Subscribe_type_type::inst()->modify($id);
        // }
        // return $id;
    }

    public function packInfo() {
       return array(
            "id" => $this->id(),
            "name" => $this->name(), 
        );
    }

    public static function create($id) {
        $summary = db_subscribe_type::inst()->get($id);
        return new Subscribe_type($summary);
    }

    public static function all() {
        $items = db_subscribe_type::inst()->all();
        $arr = array();
        foreach ($items as $id => $summary) {
            $arr[$id] = new Subscribe_type($summary);
        }
        return $arr;
    }

    public static function &cachedAll() {
        $cache = cache::instance();
        $all = $cache->load("class.Subscribe_type.all", null);
        if ($all === null) {
            $all = Subscribe_type::all();
            $cache->save("class.Subscribe_type.all", $all);
        }
        return $all;
    }

    public static function remove($id) {
        return database\Db_subscribe_type::inst()->remove($id);
    }
    
    public static function load($typeid, $userid){
        return database\Db_subscribe_type::inst()->get($typeid, $userid);
    }
    
    public static function load_Subscribe_type_activity_list($userid){
        return database\Db_subscribe_type::inst()->get_activity_by_user($userid);
    }    
    
    public static function load_Subscribe_type_calendar_list($userid){
        return database\Db_subscribe_type::inst()->get_calendar_by_user($userid);
    }
    
    public static function Subscribe_it($typeid, $userid){
        return database\Db_subscribe_type::inst()->add($typeid, $userid);
    }
    
    public static function unSubscribe_it($typeid, $userid){
        return database\Db_subscribe_type::inst()->remove($typeid, $userid);
    }
};

