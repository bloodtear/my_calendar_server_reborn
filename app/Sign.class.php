<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Sign {
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
    public function activity() {
        return $this->mSummary["activity"];
    }
    public function calendar() {
        return $this->mSummary["calendar"];
    }
    public function user() {
        return $this->mSummary["tempid"];
    }
    public function notice() {
        return $this->mSummary["notice"];
    }
    
    public function user_detail() {
        $userid = $this->user();
        return Tempuser::oneById($userid)->packInfo();
    }
    public function sheet() {
        return json_decode($this->mSummary["sheet"], JSON_UNESCAPED_UNICODE);
    }
    public function modify_time() {
        return date("Y-m-d H:i:s",$this->mSummary["modify_time"]);
    }
    public function modify_time_stamp() {
        return isset($this->mSummary["modify_time"]) ? $this->mSummary["modify_time"] : 0;
    }

    public function set_activity($n) {
        $this->mSummary["activity"] = $n;
    }
    public function set_user($n) {
        $this->mSummary["tempid"] = $n;
    }
    public function set_calendar($n) {
        $this->mSummary["calendar"] = $n;
    }
    public function set_notice($n) {
        $this->mSummary["notice"] = $n;
    }
    public function set_sheet($n) {
        $this->mSummary["sheet"] = $n;
    }
    public function set_modify_time($n) {
        $this->mSummary["modify_time"] = $n;
    }

    public function save() {
        $id = $this->id();
        if ($id == 0) {
            $id = database\Db_sign::inst()->add($this->activity(), $this->user(), json_encode($this->sheet(), JSON_UNESCAPED_UNICODE), $this->notice(), $this->modify_time_stamp());
            if ($id !== false) {
                $this->mSummary["id"] = $id;
            }
        } else {
            $id = database\Db_sign::inst()->modify($this->id(), $this->activity(), $this->user(), json_encode($this->sheet(), JSON_UNESCAPED_UNICODE), $this->notice(), $this->modify_time_stamp());
        }
        return $id;
    }

    public function packInfo($detail = false) {
       return array(
            "id" => $this->id(),
            "activity" => $this->activity(),
            "user" => $this->user(),
            //"user_detail" => $this->user_detail(),
            //"calendar" => $this->calendar(),
            "sheet" => $this->sheet(),
            "modify_time_stamp" => $this->modify_time_stamp(),
            "modify_time" => $this->modify_time(),
            "notice" => $this->notice(),
        );
    }
    
    public static function get_one($aid, $userid){
        $summary = database\Db_sign::inst()->one($aid, $userid);
        return $summary ? new Sign($summary) : null;
    }
    
    public static function load_by_aid($aid){
        $summary = database\Db_sign::inst()->load_by_aid($aid);
        return $summary;
    }

    public static function create($id) {
        $summary = database\Db_sign::inst()->get($id);
        return new Sign($summary);
    }
    
    public static function oneById($id) {
        $signs = self::cachedAll();
        foreach ($signs as $sign) {
            if ($sign->id() == $id) {
                return $sign;
            }
        }
        return null;
    }    

    public static function all() {
        $items = database\Db_sign::inst()->all();
        $arr = array();
        foreach ($items as $id => $summary) {
            $arr[$id] = new Sign($summary);
        }
        return $arr;
    }

    public static function &cachedAll() {
        $cache = cache::instance();
        $all = $cache->load("class.Sign.all", null);
        if ($all === null) {
            $all = Sign::all();
            $cache->save("class.Sign.all", $all);
        }
        return $all;
    }

    public static function remove($id) {
        return database\Db_sign::inst()->remove($id);
    }
    
    public function cancel() {
        return database\Db_sign::inst()->cancel($this->id());
    }
};

