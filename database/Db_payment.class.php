<?php
namespace my_calendar_server_reborn\database;
use framework\Database as fdb;

class Db_payment extends fdb\database {
    private static $instance = null;
    public static function inst() {
        if (self::$instance == null)
            self::$instance = new Db_payment();
        return self::$instance;
    }

    public function __construct() {
        try {
            $this->init(DB_DBNAME);
        } catch (PDOException $e) {
            logging::e("PDO.Exception", $e, false);
            die($e);
        }
    }
    //$this->create_table(TABLE_PAYMENT,  array("nickname" => "TEXT", "avatar" => "TEXT", "money" => "TEXT", "pay_time" => "TEXT","display" =>"TEXT","status" =>"TEXT","out_trade_no"=>$out_trade_no));
           

    public function add($nickname, $avatar, $money, $pay_time, $display,$status,$out_trade_no){
        return $this->insert(TABLE_PAYMENT, array("nickname" => $nickname, "avatar" => $avatar, "money" => $money, "pay_time" => $pay_time, "display" => $display,'status'=>$status,"out_trade_no"=>$out_trade_no));
    }
    

    public function del($id) {
        return $this->delete(TABLE_PAYMENT, "id = '$id'");
    }

    public function get_all_payment() {
		 return $this->get_all_table(TABLE_PAYMENT, ' 1 = 1 ',' ORDER BY pay_time desc LIMIT 100;');
    } 
	public function get_all_table($table, $where = "", $addons = "") {
        if (!empty($where)) {
            $where = "WHERE $where";
        }

        $query = "SELECT * FROM $table $where $addons ORDER BY id  desc";
        return $this->get_all($query);
    }
      
    public function get_service($id) {
        return $this->get_one_table(TABLE_SERVICES, "id = $id");
    }

   


};


