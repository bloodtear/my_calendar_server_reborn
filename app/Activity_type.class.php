<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Activity_type {
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

    public function title() {
        return $this->mSummary["title"];
    }
	
    public function pub() {
        return $this->mSummary["pub"];
    }
	
    public function tempid() {
        return $this->mSummary["tempid"];
    }	
    
    public function num() {
        return isset($this->mSummary["num"]) ? $this->mSummary["num"] : 0;
    }
	
    public function editable() {
        if (isset($this->mSummary["editable"])) {
            return $this->mSummary["editable"];
        }
        if (isset($this->mSummary["view_userid"])) {
            if ($this->mSummary["view_userid"] == $this->tempid()) {
                return 1;
            }
        }
        return 0;
    }
	
    public function subscribed() {
        return isset($this->mSummary["subscribed"]) ? $this->mSummary["subscribed"] : 0;
    }

    public function setName($n) {
        $this->mSummary["name"] = $n;
    }


    public function save() {
        // $id = $this->id();
        // if ($id == 0) {
        //     $id = db_template::inst()->add();
        //     if ($id !== false) {
        //         $this->mSummary["id"] = $id;
        //     }
        // } else {
        //     $id = db_template::inst()->modify($id);
        // }
        // return $id;
    }

    public function packInfo() {
       return array(
            "id" => $this->id(),
            "title" => $this->title(), 
            "pub" => $this->pub(), 
            "tempid" => $this->tempid(), 
            "editable" => $this->editable(), 
            "subscribed" => $this->subscribed(), 
            "num" => $this->num(), 
        );
    }
    
    public function subscribe($userid) {
        \framework\Logging::d("userid", $userid);
        $subscribe_type = Subscribe_type::load($this->id(), $userid);
        if(!$subscribe_type) {
            $subscribe_type = Subscribe_type::subscribe_it($this->id(), $userid);
        }
        return $subscribe_type;
    }
    
    public function unsubscribe($userid) {
        $subscribe_type = Subscribe_type::unsubscribe_it($this->id(), $userid);
        return $subscribe_type;
    }

    public static function create($id) {
        $summary = database\Db_custom_activity_type::inst()->get($id);
        return new Activity_type($summary);
    }

    public static function all() {
        $items = database\Db_custom_activity_type::inst()->all();
        $arr = array();
        foreach ($items as $id => $summary) {
            $arr[$id] = new Activity_type($summary);
        }
        return $arr;
    }
    
    public static function all_by_user($userid) {
        $items = database\Db_custom_activity_type::inst()->get_by_userid($userid);
        $arr = array();
        foreach ($items as $id => $summary) {
            $act = new Activity_type($summary);
            $arr[$id] = $act->packInfo();
        }
        return $arr;
    }

    public static function &cachedAll() {
        $cache = cache::instance();
        $all = $cache->load("class.Activity_type.all", null);
        if ($all === null) {
            $all = Activity_type::all();
            $cache->save("class.Activity_type.all", $all);
        }
        return $all;
    }

    public static function remove($id) {
        return database\Db_custom_activity_type::inst()->remove($id);
    }
    
    public static function get_my_types($userid) {
        $my_types = database\Db_custom_activity_type::my_types($userid);
        
        $arr = [];
        if (!empty($my_types)) {
            foreach ($my_types as $type) {
                $tp = new Activity_type($type);
                array_push($arr, $tp->packInfo());
            }
        }
        return $arr;
    }
    
    public static function view_by_user($typeid, $userid) {
        $ret = database\Db_custom_activity_type::view_by_user($typeid, $userid);
        \framework\Logging::d('retviewvyuser', json_encode($ret));
        if (empty($ret)) {
            return false;
        }
        foreach ($ret as $r) {
            return new Activity_type($r);
        }
    }
        

    public static function modify($type_id, $title, $pub) {
        return database\Db_custom_activity_type::inst()->modify($type_id, $title, $pub, Scws::instance()->split_text($title));
    }
    
    public static function add($userid, $title, $pub) {
        return database\Db_custom_activity_type::inst()->add($userid, $title, $pub, Scws::instance()->split_text($title));
    }
    
    public static function get_by_userid($userid) {
        return database\Db_custom_activity_type::inst()->get_by_userid($userid);
    }
        
    public static function search($input, $start) {
        $my_types = database\Db_custom_activity_type::search($input, $start);
        $arr = [];
        if (!empty($my_types)) {
            foreach ($my_types as $type) {
                $tp = new Activity_type($type);
                array_push($arr, $tp->packInfo());
            }
        }
        return $arr;
    }
    
};

