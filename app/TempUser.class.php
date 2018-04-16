<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;


class TempUser extends User {
    private $mSummary = null;
    private $mGroups = null;

    public function __construct($summary = array()) {
        if (empty($summary)) {
            $summary = array(
                "id" => 0,
                "openid" => "",
                "session_key " => "",
                "nickname" => "",
                "avatar" => "",
                "create_time" => "",
                "status" => 0,
                "uid" => 0,
            );
        }
        $this->mSummary = $summary;
    }

    //获取参数函数
    public function id() {
        return $this->mSummary["id"];
    }

    public function type() {
        return $this->mSummary["type"];
    }

    public function openid() {
        return $this->mSummary["openid"];
    }
    
    public function unionid() {
        return $this->mSummary["unionid"];
    }

    public function session_key () {
        return $this->mSummary["session_key"];
    }

    public function uid() {
        return $this->mSummary["uid"];
    }
    
    public function nickname() {
        return $this->mSummary["nickname"];
    }


    public function avatar() {
        return $this->mSummary["avatar"];
    }

    public function create_time() {
        return $this->mSummary["create_time"];
    }
    
    public function active_time() {
        return $this->mSummary["active_time"];
    }
    
    public function status() {
        return $this->mSummary["status"];
    }

    //修改参数函数
    public function setNickname($n) {
        $this->mSummary["nickname"] = $n;
    }
    
    public function setAvatar($n) {
        $this->mSummary["avatar"] = $n;
    }
    
    public function setSessionKey($n) {
        $this->mSummary["session_key"] = $n;
    }
    
    public function setOpenId($n) {
        $this->mSummary["openid"] = $n;
    }
    
    public function setUnionId($n) {
        $this->mSummary["unionid"] = $n;
    }
    
	  public function setUId($n) {
        $this->mSummary["uid"] = $n;
    }

    //存储函数
    public function save() {
        $id = $this->id();
        if ($id == 0) {
            $id = database\Db_tempuser::inst()->add($this->openid(), $this->uid(), $this->nickname(), $this->avatar(), $this->create_time(), $this->status(), $this->unionid(), $this->session_key());
            if ($id !== false) {
                $this->mSummary["id"] = $id;
                $ret = database\Db_custom_activity_type::inst()->default_init($id);
            }
        } else {
            $id = database\Db_tempuser::inst()->modify($id, $this->openid(), $this->uid(), $this->nickname(), $this->avatar(), $this->create_time(), $this->status(), $this->unionid(), $this->session_key());
        }
        return $id;
    }

    //打包输出函数
    public function packInfo($pack_all_groups = true) {

        return array(
            "id" => $this->id(),
            "name" => $this->nickname(), 
            "avatar" => $this->avatar(), 
            "status" => $this->status()
        );
    }

    public static function create($uid) {
        $user = database\Db_tempuser::inst()->get($uid);
        return new TempUser($user);
    }

    public static function all($include_deleted = false) {
        $users = database\Db_tempuser::inst()->all();
        $arr = array();
        foreach ($users as $uid => $user) {
            if (!$include_deleted) {
                if ($user["status"] == database\Db_tempuser::STATUS_DELETED) {
                    continue;
                }
            }
            $arr[$uid] = new TempUser($user);
        }
        return $arr;
    }

    public static function &cachedAll() {
        $cache = cache::instance();
        $all = $cache->load("class.tempuser.all", null);
        if ($all === null) {
            $all = TempUser::all();
            $cache->save("class.tempuser.all", $all);
        }
        return $all;
    }

    public static function oneByName($username) {
        $users = self::cachedAll();
        foreach ($users as $user) {
            if ($user->username() == $username) {
                return $user;
            }
        }
        return null;
    }
    public static function oneById($id) {
        $users = self::cachedAll();
        foreach ($users as $user) {
            if ($user->id() == $id) {
                return $user;
            }
        }
        return null;
    }
    
    public static function oneBySession($calendar_session) { 
        $ret = database\Db_tempuser::inst()->get_by_session($calendar_session);
        return $ret ? new TempUser($ret) : null;
    }
    
    public static function get($id) { 
        $ret = database\Db_tempuser::inst()->get($id);
        return $ret ? new TempUser($ret) : null;
    }

    public static function createByOpenid($openid) {
        $ret = database\Db_tempuser::inst()->get_by_openid($openid);
        if ($ret) {
            return new TempUser($ret);
        }
        return new TempUser;
    }
    
    public static function verify_or_create($tempuserid, $userid) {
        $data = database\Db_tempuser::inst()->get_verify_user($userid);
        if ($data) {
            return new TempUser($data);
        }
        $ret = database\Db_tempuser::inst()->create_verify_user($tempuserid, $userid);
        if (empty($ret)) {
            return false;
        }
        $data = database\Db_tempuser::inst()->get_verify_user($userid);
        if ($data) {
            return new TempUser($data);
        }
    }
    

    public static function remove($uid) {
        return database\Db_tempuser::inst()->remove($uid);
    }
    
    public function my_index($start_index) {
        $userid = $this->id();
        return self::get_my_index($userid, $start_index);
    }

}
