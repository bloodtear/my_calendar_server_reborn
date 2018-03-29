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
                "calendar_session" => "",
                "session_key " => "",
                "nickname" => "",
                "avatar" => "",
                "create_time" => "",
                "last_login" => "",
                "token" => "",
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

    public function calendar_session() {
        return $this->mSummary["calendar_session"];
    }

    public function session_key () {
        return $this->mSummary["session_key "];
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
    
    public function last_login() {
        return $this->mSummary["last_login"];
    }
    
    public function token() {
        return $this->mSummary["token"];
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
    public function setSession($n) {
        $this->mSummary["calendar_session"] = $n;
    }
    public function setToken($n) {
        $this->mSummary["token"] = $n;
    }
    public function setSessionKey($n) {
        $this->mSummary["session_key"] = $n;
    }
    public function setOpenId($n) {
        $this->mSummary["openid"] = $n;
    }
	  public function setUId($n) {
        $this->mSummary["uid"] = $n;
    }

    //存储函数
    public function save() {
        $id = $this->id();
        if ($id == 0) {
            $id = database\Db_tempuser::inst()->add($this->openid(), $this->uid(), $this->nickname(), $this->avatar(), $this->create_time(),  $this->last_login(), $this->token(),  $this->status(), $this->calendar_session());
            if ($id !== false) {
                $this->mSummary["id"] = $id;
                $ret = db_custom_activity_type::inst()->default_init($id);
            }
        } else {
            $id = database\Db_tempuser::inst()->modify($id, $this->openid(), $this->uid(), $this->nickname(), $this->avatar(), $this->create_time(), $this->last_login(), $this->token(),  $this->status(), $this->calendar_session());
        }
        return $id;
    }

    //打包输出函数
    public function packInfo($pack_all_groups = true) {

        return array(
            "id" => $this->id(),
            "name" => $this->nickname(), 
            "avatar" => $this->avatar(), 
            "token" => $this->token(), 
            "status" => $this->status(), 
            "calendar_session" => $this->calendar_session()
            //"groups" => $groupInfo
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

// xy: 首页函数 待修改 20180202
    public static function get_my_index($userid, $start_index = 0) {
        $sql = "
        
    select aa.id activity_id, aa.title, aa.content, aa.type, aa.calendar_id, aa.modifytime, aa.status,  ab.avatar owner_avatar 
        from yyba_activity aa join yyba_tempuser ab 
        on ab.id = aa.owner 
        where aa.type = 1 and aa.owner = 5 and aa.calendar_id = 0
    union
        select ba.id activity_id, ba.title, ba.content, ba.type, ba.calendar_id , ba.modifytime,ba.status, bc.avatar owner_avatar 
        from yyba_activity ba 
        join yyba_organization_member bb 
        on bb.organization = ba.owner  
        join yyba_organization bc 
        on bc.id = ba.owner 
        where bb.user = 5 and ba.type = 2
    union
        select ca.id activity_id, ca.title, ca.content, ca.type,ca.calendar_id , ca.modifytime, ca.status,cc.avatar owner_avatar 
        from yyba_activity ca 
        join yyba_sign cb 
        on cb.activity = ca.id 
        join yyba_tempuser cc 
        on cc.id = ca.owner 
        where cb.user = 5 and ca.type = 1
        union
        (select cx.id activity_id, cx.title, cx.content, cx.type, cx.calendar_id ,cx.modifytime, cx.status,cz.avatar owner_avatar
        from yyba_activity cx 
        join yyba_sign cy 
        on cy.activity = cx.id 
        join yyba_organization cz 
        on cz.id = cx.owner 
        where cy.user = 5 and cx.type = 2)
    union
        select da.id activity_id, da.title, da.content, da.type,da.calendar_id , da.modifytime, da.status,dc.avatar owner_avatar 
        from yyba_activity da 
        join yyba_subscribe db 
        on db.activity = da.id 
        join yyba_tempuser dc 
        on dc.id = da.owner 
        where db.user = 5 and da.type = 1
        union
        (select dx.id activity_id, dx.title, dx.content, dx.type, dx.calendar_id ,dx.modifytime, dx.status,dz.avatar owner_avatar
        from yyba_activity dx 
        join yyba_subscribe dy 
        on dy.activity = dx.id 
        join yyba_organization dz 
        on dz.id = dx.owner 
        where dy.user = 5 and dx.type = 2)
        
        
    union  
        select '0' as activity_id, xa.title, xa.content, xa.type, xa.id calendar_id, xa.modify_time modifytime, xa.status,xb.avatar owner_avatar
        from yyba_calendar xa 
        join yyba_tempuser xb 
        on xb.id = xa.owner 
        where xa.owner = 5 and xa.type = 1
    union
        select  '0' as activity_id, ya.title, ya.content, ya.type,ya.id calendar_id, ya.modify_time modifytime, ya.status,yc.avatar owner_avatar
        from yyba_calendar ya 
        join yyba_organization_member yb 
        on ya.owner = yb.organization
        join yyba_organization yc 
        on yc.id = ya.owner 
        where ya.type = 2 and yb.user = 5
    union
        select  '0' as activity_id, za.title, za.content, za.type,za.id calendar_id, za.modify_time modifytime,za.status,zc.avatar owner_avatar
        from yyba_calendar za 
        join yyba_subscribe zb 
        on za.id = zb.calendar 
        join yyba_tempuser zc 
        on zc.id = za.owner 
        where zb.user = 5 and za.type = 1 
        union 
        select  '0' as activity_id, zx.title, zx.content, zx.type,zx.id calendar_id, zx.modify_time modifytime,zx.status,zz.avatar owner_avatar
        from yyba_calendar zx 
        join yyba_subscribe zy 
        on zx.id = zy.calendar 
        join yyba_organization zz 
        on zz.id = zx.owner 
        where zy.user = 5 and zx.type = 2    
       
    order by modifytime desc limit $start_index, 20
        
        ";
        $list = database\Db_base::inst()->do_query($sql);
        foreach ($list as $id => $index) {
            if ($index['type'] == 2) {
                $list[$id]['owner_avatar'] = rtrim(UPLOAD_URL, "/") . "/" . $index['owner_avatar'];
            }
            if ($index['calendar_id'] != 0 && $index['activity_id'] == 0 ) {
                $list[$id]['view_url'] = "../create/calendar?id=" . $index['calendar_id'];
            }else {
                $list[$id]['view_url'] = "../activity/detail?id=" . $index['activity_id'];
            }
        }
        return $list;
    }
};

