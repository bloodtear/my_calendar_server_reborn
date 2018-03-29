<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class User {
    private $mSummary = null;
    private $mGroups = null;

    public function __construct($summary = array()) {
        if (empty($summary)) {
            $summary = array(
                "id" => 0,
                "phone_number" => 0,
                "email" => "",
                "verify_code" => 0,
                "verify_status" => 0,
                "status" => 0,
                "expired" => 0,
            );
        }
        $this->mSummary = $summary;
    }

    public function id() {
        return $this->mSummary["id"];
    }

    public function phone_number() {
        return $this->mSummary["phone_number"];
    }

    public function email() {
        return $this->mSummary["email"];
    }

    public function verify_code() {
        return $this->mSummary["verify_code"];
    }

    public function verify_status() {
        return $this->mSummary["verify_status"];
    }

    public function status() {
        return $this->mSummary["status"];
    }

    public function expired() {
        return $this->mSummary["expired"];
    }

    public function set_phone_number($n) {
        $this->mSummary["phone_number"] = $n;
    }

    public function set_email($p) {
        $this->mSummary["email"] = $p;
    }

    public function set_verify_code($n) {
        $this->mSummary["verify_code"] = $n;
    }

    public function set_verify_status($t) {
        $this->mSummary["verify_status"] = $t;
    }

    public function set_status($mail) {
        $this->mSummary["status"] = $mail;
    }

    public function set_expired($c) {
        $this->mSummary["expired"] = $c;
    }

    public function save() {
        $id = $this->id();
        if ($id == 0) {
            $id = database\Db_user::inst()->add($this->phone_number(), $this->email(), $this->verify_code(), $this->verify_status(), $this->status(), $this->expired());
            if ($id !== false) {
                $this->mSummary["id"] = $id;
            }
        } else {
            $id = database\Db_user::inst()->modify($id, $this->phone_number(), $this->email(), $this->verify_code(), $this->verify_status(), $this->status(), $this->expired());
        }
        return $id;
    }

    public function packInfo($pack_all_groups = true) {

        return array(
            "id" => $this->id(),
            "phone_number" => $this->phone_number(), 
            "email" => $this->email(), 
            "verify_code" => $this->verify_code(), 
            "verify_status" => $this->verify_status(), 
            "status" => $this->status(), 
            "expired" => $this->expired()
        );
    }
    
    public static function create_by_phone($phone) {
        $data = database\Db_user::inst()->get_by_phone($phone);
        if (empty($data)) {
            return new User();
        }
        return new User($data);
    }
    
    public static function get_by_phone($phone) {
        $data = database\Db_user::inst()->get_by_phone($phone);
        if (empty($data)) {
            return false;
        }
        return new User($data);
    }
    
    public function check_verify($verify_code) {
        \framework\Logging::l('expired',$this->expired());
        \framework\Logging::l('time',time());
        \framework\Logging::l('thisverify_code',$this->verify_code());
        \framework\Logging::l('verify_code', $verify_code);
        return ($this->verify_code() == $verify_code && (time() < $this->expired()) )? true : false;
    }

};

