<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class InternalUser {
    private $mSummary = null;
    private $mGroups = null;
/**
{
	id //唯一序号
	tempid //临时表对应id
	telephone // 电话号
	email //邮箱	
	verify_code // 验证码
	verify_status // 验证状态
	status //是否存在
	
}
*/ function __construct($summary=array()) {
        if (empty($summary)) {
			
            $summary = array(
                "id" => 0,
				"tempid" => 0,
                "telephone" => "",
                "email" => "",
                "verify_code" => "",
                "verify_status" => "false",
				 "status" => "",
				
            );
        }
        $this->mSummary = $summary;

    }
  

    public function id() {
        return $this->mSummary["id"];
    }

  

    public function telephone() {
        return $this->mSummary["telephone"];
    }

    public function email() {
        return $this->mSummary["email"];
    }

   
    public function tempid() {
        return $this->mSummary["tempid"]; 
    }
	private function verify_code() {
        return $this->mSummary["verify_code"];
    }
     public function verify_status() {
        return $this->mSummary["verify_status"];
    }
	
	public function status() {
        return $this->mSummary["status"];
    }
    


    //修改参数函数
   
    public function setTelephone($t) {
        $this->mSummary["telephone"] = $t;
    }

    public function setEmail($mail) {
        $this->mSummary["email"] = $mail;
    }
	public function setTempId($tempid) {
        $this->mSummary["tempid"] = $tempid;
    }
	public function setCode($verify_code) {
        $this->mSummary["verify_code"] = $verify_code;
    }
	public function setStatus($status) {
        $this->mSummary["verify_status"] = $status;
    }
	public function verify($verify_code){
		if(!empty( $verify_code)){
			if($this->mSummary["verify_code"]==$verify_code){
				return true;
			}
			return false;
		}
		return null;
	}
   

    


    public function save() {
        $id = $this->id();
		
        if ($id == 0) {
            $flag = db_user::inst()->add($this->tempid(), $this->telephone(), $this->email(),$this->verify_code(),$this->verify_status(),$this->status());
            if ($flag !== false) {
                $this->mSummary["id"] = $id;
            }
        } else {
            $flag = db_user::inst()->modify($this->id(),$this->tempid(), $this->telephone(), $this->email(),$this->verify_code(),$this->verify_status(),$this->status());
        }
        return $id;
    }
	
    public static function create($uid) {
        $user = db_user::inst()->get($uid);
        return new User($user);
    }
	public static function createByTelephone($telephone) {
        $user = db_user::inst()->getByTelephone($telephone);
        return new User($user);
    }
	

	
    public static function all($include_deleted = false) {
        $users = db_user::inst()->all();
        $arr = array();
        foreach ($users as $uid => $user) {
            if (!$include_deleted) {
                if ($user["status"] == db_user::STATUS_DELETED) {
                    continue;
                }
            }
            $arr[$uid] = new InternalUser($user);
        }
        return $arr;
    }

    public static function &cachedAll() {
        $cache = cache::instance();
        $all = $cache->load("class.InternalUser.all", null);
        if ($all === null) {
            $all = InternalUser::all();
            $cache->save("class.InternalUser.all", $all);
        }
        return $all;
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
    public static function oneByTelephone($telephone) {
        $users = self::cachedAll();
        foreach ($users as $user) {
            if ($user->telephone() == $telephone) {
                return $user;
            }
        }
        return null;
    }
    public static function remove($uid) {
        return db_user::inst()->remove($uid);
    }
};

