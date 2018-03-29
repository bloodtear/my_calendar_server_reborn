<?php
namespace my_calendar_server_reborn\database;
use framework\Database as fdb;

class Db_base extends fdb\Database {

    private static $instance = null;
    public static function inst() {
        if (self::$instance == null)
            self::$instance = new Db_base();
        return self::$instance;
    }

    public function __construct($db = DB_DBNAME) {
        $this->dbname = $db;
        try {
            parent::instance();
        } catch (PDOException $e) {
            logging::e("PDO.Exception", $e, false);
            die($e);
        }
    }
    
    public function do_query($sql){
        return $this->query_get_all($sql);
    }

};


