<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Setting {
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
    
    public function value() {
        return $this->mSummary["value"];
    }
    
    public function status() {
        return $this->mSummary["status"];
    }

    public function expired() {
        return $this->mSummary["expired"];
    }

    public function setName($n) {
        $this->mSummary["name"] = $n;
    }

    public function setValue($n) {
        $this->mSummary["value"] = $n;
    }

    public function setExpired($n) {
        $this->mSummary["expired"] = $n;
    }

    public function setStatus($n) {
        $this->mSummary["status"] = $n;
    }


    public function save() {
        $id = $this->id();
        if ($id == 0) {
            $id = database\Db_setting::inst()->add($this->name(),$this->value(),$this->expired(),$this->status());
            if ($id !== false) {
                $this->mSummary["id"] = $id;
            }
        }
        else {
            $id = database\Db_setting::inst()->modify($id,$this->name(),$this->value(),$this->expired(),$this->status());
        }
        return $id;
    }

    public function packInfo() {
       return array(
            "id" => $this->id(),
            "name" => $this->name(), 
            "value" => $this->value(), 
            "status" => $this->status(), 
            "expired" => $this->expired(), 
        );
    }

    public static function get_by_name($name) {
        $summary = database\Db_setting::inst()->get_by_name($name);
        if (empty($summary)) {
            return null;
        }
        return new Setting($summary);
    }

   

    public static function remove($id) {
        return db_template::inst()->remove($id);
    }
};

