<?php
namespace my_calendar_server_reborn\database;
use framework\Database as fdb;

class Db_tempuser extends fdb\Database_table {
    const STATUS_NORMAL = 0;
    const STATUS_DELETED = 1;

    private static $instance = null;
    public static function inst() {
        if (self::$instance == null)
            self::$instance = new Db_tempuser();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct(MYSQL_PREFIX . "tempuser");
    }

    public function get($id) {
        $id = (int)$id;
        return $this->get_one("id = $id");
    }
    
    public function get_by_session($calendar_session) {
        return $this->get_one("calendar_session = '$calendar_session'");
    }
    
    public function get_by_openid($openid) {
        return $this->get_one("openid = '$openid'");
    }

    public function all() {
        return $this->get_all();
    }

    public function add($openid, $uid, $nickname, $avatar, $create_time, $last_login, $status, $calendar_session, $unionid, $session_key) {
        return $this->insert(array("session_key" => $session_key, "unionid" => $unionid, "openid" => $openid, "uid" => 0, "nickname" => $nickname, "avatar" => $avatar, "create_time" => time(), "last_login" => time(), "status" => $status, "calendar_session" => $calendar_session));
    }

    public function modify($id, $openid, $uid, $nickname, $avatar, $create_time, $last_login, $status, $calendar_session, $unionid, $session_key) {
        $id = (int)$id;
        return $this->update(array("session_key" => $session_key, "unionid" => $unionid, "openid" => $openid, "uid" => $uid, "nickname" => $nickname, "avatar" => $avatar, "create_time" => $create_time, "last_login" => time(),  "status" => $status, "calendar_session" => $calendar_session), "id = $id");
    }

    public function remove($id) {
        $id = (int)$id;
        return $this->update(array("status" => self::STATUS_DELETED), "id = $id");
    }
    
    public function get_verify_user($userid) {
        return $this->get_one("uid = '$userid'");
    }
    
    public function create_verify_user($tempuserid, $userid) {
        return $this->update(array("uid" => $userid), "id = $tempuserid");
    }
    



};


