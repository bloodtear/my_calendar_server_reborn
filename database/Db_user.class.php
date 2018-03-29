<?php
namespace my_calendar_server_reborn\database;
use framework\Database as fdb;

class Db_user extends fdb\Database_table {
    const STATUS_NORMAL = 0;
    const STATUS_DELETED = 1;

    private static $instance = null;
    public static function inst() {
        if (self::$instance == null)
            self::$instance = new Db_user();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct(MYSQL_PREFIX . "user");
    }

    public function get($id) {
        $id = (int)$id;
        return $this->get_one("id = $id");
    }
    
    public function get_by_phone($phone_number) {
        return $this->get_one("phone_number = '$phone_number'");
    }
    

    public function all() {
        return $this->get_all();
    }
/**
{
            "id" => $this->id(),
            "phone_number" => $this->phone_number(), 
            "email" => $this->email(), 
            "verify_code" => $this->verify_code(), 
            "verify_status" => $this->verify_status(), 
            "status" => $this->status(), 
            "expired" => $this->expired()

}
*/
    public function add($phone_number, $email, $verify_code, $verify_status, $status, $expired) {
        //$phone_number = (int)$phone_number;
        return $this->insert(array("phone_number" => $phone_number, "email" => $email, "expired"=> $expired, "verify_code" => $verify_code, "verify_status" => $verify_status, "status" => $status));
    }

    public function modify($id, $phone_number, $email, $verify_code, $verify_status, $status, $expired) {
        //$phone_number = (int)$phone_number;
        return $this->update(array("phone_number" => $phone_number, "email" => $email, "expired"=> $expired, "verify_code" => $verify_code, "verify_status" => $verify_status, "status" => $status), "id = $id");
    }
	
    public function remove($id) {
        $id = (int)$id;
        return $this->update(array("status" => self::STATUS_DELETED), "id = $id");
    }


};


