<?php

namespace my_calendar_server_reborn\app;

use my_calendar_server_reborn\database;

class Activity {
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

    public function owner() {
        return $this->mSummary["owner"];
    }
    public function owner_detail() {
        $owner_id = $this->owner();
        return [
            'owner_nickname' => isset($this->mSummary["owner_nickname"]) ? $this->mSummary["owner_nickname"] : '',
            'owner_avatar'   => isset($this->mSummary["owner_avatar"]) ? $this->mSummary["owner_avatar"] : ''
        ];
    }
    public function title() {
        return $this->mSummary["title"];
    }

    public function avatar() {
        return $this->mSummary["avatar"];
    }
    public function avatar_detail() {
		return isset($this->mSummary["avatar_name"]) ? Preview_image::get_url_from_name($this->mSummary["avatar_name"]) : '';
    }
    
    public function avatar_thumbnail_detail() {
		return isset($this->mSummary["avatar_name"]) ? Preview_image::get_thumbnail_url_from_name($this->mSummary["avatar_name"]) : '';
    }

    public function images() {
        $images = $this->mSummary["images"];
        $images = $this->convert_json($images);
        return $images;
        \framework\Logging::d("images", $images);
    }
    public function images_full_list(){
        $images = $this->images();
        if (empty($images)) {
            return $images;
        }
        $arr = [];
        foreach ($images as $image) {
            $a = [];
            $a["name"] = $image;
            $a["image_url"] = rtrim(UPLOAD_URL, "/") . "/" . $image;
            $a["thumbnail_url"] = rtrim(THUMBNAIL_URL, "/") . "/thumbnail-$image";
            array_push($arr, $a);
        }
        return $arr;
    }
    public function image_url_list() {
        $images = json_decode($this->mSummary["images"]);
        if (empty($images)) {
            return $images;
        }
        $arr = [];
        foreach ($images as $image) {
            array_push($arr, rtrim(UPLOAD_URL, "/") . "/" . $image);
        }
        return $arr;
    }
    public function image_thumbnail_url_list() {
        $images = json_decode($this->mSummary["images"]);
        if (empty($images)) {
            return $images;
        }
        $arr = [];
        foreach ($images as $image) {
            array_push($arr, rtrim(THUMBNAIL_URL, "/") . "/thumbnail-$image");
        }
        return $arr;
    }
    public function begintime_detail() {
        $begintime = $this->begintime();
        return date('y-m-d h:i:s', $begintime);
    }
    public function begindate() {
        $begintime = $this->begintime();
        return date('y-m-d', $begintime);
    }
    public function endtime_detail () {
        $endtime = $this->endtime();
        return date('y-m-d h:i:s', $endtime);
    }
    
    public function createtime() {
        return $this->mSummary["createtime"];
    }
    public function notice() {
        return $this->mSummary["notice"];
    }
    public function modifytime() {
        return $this->mSummary["modifytime"];
    }
    public function begintime() {
        return $this->mSummary["begintime"];
    }
    public function endtime() {
        return $this->mSummary["endtime"];
    }
    public function repeattype() {
        return $this->mSummary["repeattype"];
    }
    public function repeatcount() {
        return $this->mSummary["repeatcount"];
    }
    public function repeatend() {
        return $this->mSummary["repeatend"];
    }
    public function address() {
        return $this->mSummary["address"];
    }
    public function content() {
        return $this->mSummary["content"];
    }
    public function max_participants() {
        return $this->mSummary["participants"];
    }
    public function participants() {
        return $this->mSummary["participants"];
    }
    
    public function now_participants() {
        return isset($this->mSummary["sign_list"]) ? count($this->mSummary["sign_list"]) : 0;
    }
    public function type() {
        return $this->mSummary["type"];
    }
	public function type_detail() {
        $type = $this->type();
		$type = Activity_type::create($type);
		return $type->packInfo();
    }
	public function type_title() {
        return isset($this->mSummary["type_title"]) ? $this->mSummary["type_title"] : '';
    }
	public function type_pub() {
        return isset($this->mSummary["pub"]) ? $this->mSummary["pub"] : 0;
    }
    public function joinsheet() {
        $sheet = $this->mSummary["joinsheet"];
        $sheet = $this->convert_json($sheet);
        return $sheet;
    }
    public function clickcount() {
        return $this->mSummary["clickcount"];
    }
    public function joinable() {
        return ($this->type() == 1 ? false : true);
    }
    public function status() {
        return $this->mSummary["status"];
    }
    public function pid() {
        return $this->mSummary["pid"];
    }
    
    public function sign_id() {
        return $this->mSummary["sign_id"];
    }
    
    public function sign_sheet() {
        return $this->mSummary["sign_sheet"];
    }
    
    public function sign_notice() {
        return $this->mSummary["sign_notice"];
    }
    public function is_subscribe() {
        return $this->mSummary["subscribe"];
    }
    
    public function repeat_time_zone() {
        return isset($this->mSummary["repeat_time_zone"]) ? $this->mSummary["repeat_time_zone"] : null;
    }
    public function detail_qcode() {
        $qcode = rtrim(UPLOAD_URL, "/") . "/qcode/" . $this->id() . ".jpg";
/*         
        \framework\Logging::d('file_exists qcode', json_encode(file_exists($qcode)));
        if (!file_exists($qcode)) {
            \framework\Logging::d('no qcode,' , $this->id() . ",now remake detail_qcode");
            $this->make_detail_qcode($this->id());
        } */
        return rtrim(UPLOAD_URL, "/") . "/qcode/" . $this->id() . ".jpg";
    }
    
    public function signed_user_list() {   
    
        $activity_id = $this->id();
        $sign_list = Sign::load_by_aid($activity_id);
        $this->mSummary["sign_list"] = $sign_list;
        
        return $this->mSummary["sign_list"];
    }
    
    public function has_permission($userid) {
      return $this->owner() == $userid;
        if ($this->type() == 1) {
            return $this->owner() == $userid;
        }else if ($this->type() == 2) {
            return database\Db_organization_member::inst()->one($this->owner(), $userid);
        }else {
            return false;
        }
    }

    public function setTitle($n) {
        $this->mSummary["title"] = $n;
    }
    public function set_Type($n) {
        $this->mSummary["type"] = $n;
    }
    public function setOwner($n) {
        $this->mSummary["owner"] = $n;
    }
    public function setAvatar($n) {
        $this->mSummary["avatar"] = $n;
    }
    public function setParticipants($n) {
        $this->mSummary["participants"] = $n;
    }
    public function setInfo($n) {
        $this->mSummary["info"] = $n;
    }
    public function setContent($n) {
        $this->mSummary["content"] = $n;
    }
    public function setImages($n) {
        //$n = convert_to_string($n);
        $this->mSummary["images"] = $n;
    }
    public function setBegintime($n) {
        $this->mSummary["begintime"] = $n;
    }
    public function setEndtime($n) {
        $this->mSummary["endtime"] = $n;
    }
    public function setRepeatend($n) {
        $this->mSummary["repeatend"] = $n;
    }
    public function setAddress($n) {
        $this->mSummary["address"] = $n;
    }
    public function setRepeattype($n) {
        $this->mSummary["repeattype"] = $n;
    }
    public function setRepeatcount($n) {
        $this->mSummary["repeatcount"] = $n;
    }
    public function setJoinsheet($n) {
        $this->mSummary["joinsheet"] = $n;
    }

    public function setStatus($n) {
        $this->mSummary["status"] = $n;
    }
    public function setPid($n) {
        $this->mSummary["pid"] = $n;
    }
    public function setNotice($n) {
        $this->mSummary["notice"] = $n;
    }

    public function make_detail_qcode($id){
        $page = "pages/activity/detail";
        $scene = "?id=$id";
        $imgsrc = Wxapi::get_wx_acode($page, $scene);
        $ret = Upload::save_qcode($imgsrc, $id);
        return $ret;
    }

    public function save() {
        $id = $this->id();
        if ($id == 0) {
            $id = database\Db_activity::inst()->add($this->owner(), $this->title(), $this->avatar(), $this->convert_to_string($this->images()), $this->begintime(), $this->endtime(), $this->repeattype(), $this->repeatend(), $this->address(), $this->content(), $this->participants(), $this->convert_to_string($this->joinsheet()), $this->type(), $this->pid(), $this->notice(), Scws::instance()->split_text($this->title()));
            if ($id !== false) {
                $this->mSummary["id"] = $id;
                $ret = $this->make_detail_qcode($id);
            }
        } else {
            $id = database\Db_activity::inst()->modify($this->id(), $this->title(), $this->avatar(), $this->convert_to_string($this->images()), $this->begintime(), $this->endtime(), $this->repeattype(), $this->repeatend(), $this->address(), $this->content(), $this->participants(), $this->convert_to_string($this->joinsheet()), $this->pid(), $this->status(),$this->notice(), Scws::instance()->split_text($this->title()));
        }
        return $id;
    }

    public function packInfo($detail = true) {
       return array(
            "id" => $this->id(),
            "type" => $this->type(),
            //"type_detail" => $detail ? $this->type_detail() : null,
            "type_title" => $this->type_title(),
            "type_pub" => $this->type_pub(),
            "owner" => $detail ? $this->owner_detail() : null,
            "title" => $this->title(),
            "avatar_detail" => $this->avatar_detail(),
            "avatar_thumbnail_detail" => $this->avatar_thumbnail_detail(),
            "images" => $this->images(),
            "image_url_list" => $this->image_url_list(),
            "image_thumbnail_url_list" => $this->image_thumbnail_url_list(),
            "images_full_list" => $this->images_full_list(),
            "content" => $this->content(),
            "begintime" => $this->begintime(),
            "begintime_detail" => $this->begintime_detail(),
            "begindate" => $this->begindate(),
            "endtime_detail" => $this->endtime_detail(),
            "endtime" => $this->endtime(),
            "repeatend" => $this->repeatend(),
            "address" => $this->address(),
            "repeattype" => $this->repeattype(),
            "status" => $this->status(),
            "max_participants" => $detail ? $this->max_participants() : null,
            "signed_user_list" => $detail ? $this->signed_user_list() : null,
            "now_participants" => $detail ? $this->now_participants() : null,
            "pid" => $this->pid(),
            "detail_qcode" => $this->detail_qcode(),
            "repeat_time_zone" => $this->repeat_time_zone(),
            "joinsheet" => $this->joinsheet(),
        );
    }

    public static function create($id) {
        $summary = db_activity::inst()->get($id);
        return new Activity($summary);
    }
    
    public static function build_one($type, $owner, $participants, $title, $content, $images, $begintime, $endtime, $repeatend, $address, $repeattype, $joinsheet, $pid, $avatar, $notice) {
        $activity = new Activity();
            
        $activity->set_Type($type);
        $activity->setOwner($owner);

        $activity->setParticipants($participants);
        
        $activity->setAvatar($avatar);
        $activity->setTitle($title);
        $activity->setContent($content);
        $activity->setImages($images);
        
        $activity->setBegintime($begintime);
        $activity->setEndtime($endtime);
        $activity->setRepeatend($repeatend);
        
        $activity->setAddress($address);
        
        $activity->setRepeattype($repeattype);

        $activity->setJoinsheet($joinsheet);
        $activity->setPid($pid);
        $activity->setNotice($notice);
        
        $activity->setStatus(0);

        $ret = $activity->save();
        
        return array("ret" => $ret, "activity" => $activity);
    }
    
    public static function edit_one($activity_id, $title, $content, $address, $images, $avatar, $type) {
        
        $activity = Activity::oneById($activity_id);

        $activity->setTitle($title);
        $activity->setContent($content);
        $activity->setImages($images);
        $activity->setAddress($address);
        $activity->setAvatar($avatar);
        $activity->set_Type($type);
        
        $ret = $activity->save();
        
        return array("ret" => $ret, "activity" => $activity);
        
    }

    public static function edit_batch($activity_id, $title, $content, $address, $images, $avatar, $type) {
        
        $activity = Activity::oneById($activity_id);
        $pid = $activity->pid();

        return db_activity::inst()->modify_batch($title, $content, $address, $images, $avatar, $type, $pid);
        
    }
    
    public static function oneById($id) {
        
        $data = database\Db_activity::inst()->get($id);
        
        return $data ? new Activity($data) : null;
        
    }

    public static function all() {
        
        $items = database\Db_activity::inst()->all();
        $arr = array();
        
        foreach ($items as $id => $summary) {
            $arr[$id] = new Activity($summary);
        }
        
        return $arr;
        
    }

    public static function &cachedAll() {
        
        $cache = cache::instance();
        $all = $cache->load("class.activity.all", null);
        if ($all === null) {
            $all = Activity::all();
            $cache->save("class.activity.all", $all);
        }
        return $all;
        
    }

    public static function remove($id) {
        
        return database\Db_activity::inst()->remove($id);
        
    }
    
    public function cancel() {
        
        $this->setStatus(1);
        
        return $this->save();
        
    }
    
    public function start() {
        
        $this->setStatus(0);
        
        return $this->save();
        
    }
    
    public function subscribe($userid) {
        
        $subscribe = Subscribe::load($this->id(), $userid);
        
        if(!$subscribe) {
            $subscribe = Subscribe::subscribe_it($this->id(), $userid);
        }
        
        return $subscribe;
        
    }
    
    public function unsubscribe($userid) {
        
        $subscribe = Subscribe::unsubscribe_it($this->id(), $userid);
        
        return $subscribe;
        
    }
        
    public function convert_json($string) {
        if (!is_string($string)) {
            return $string;
        }else {
            $string = json_decode($string);
            return $this->convert_json($string);
        }
    }

    public function convert_to_string($json) {
        if (is_string($json)) {
            return $json;
        }else {
            $json = json_encode($json);
            return $this->convert_to_string($json);
        }
    }
    
    
    public static function get_my_list_by_type($userid, $choosed_type) {
        
        if ($choosed_type == -1) {
            $my_list = self::my_joined_list($userid);
        }else if ($choosed_type == -2) {
            $my_list = self::my_subscribed_list($userid);
        }else if ($choosed_type == 0) {
            $my_list = self::my_all_list($userid);
        }else {
            $my_list = self::my_list_by_type($userid, $choosed_type);
        }

        $array = self::get_repeat_zone_from_list ($my_list);
        $data = array("my_list" => $array);
        
        return $data;
        
    }
    
    public static function my_joined_list($userid) {
        return database\Db_activity::my_joined_list($userid);
    }
    
    public static function my_subscribed_list($userid) {
        return database\Db_activity::my_subscribed_list($userid);
    }
    
    public static function my_all_list($userid) {
        return database\Db_activity::my_all_list($userid);
    }
    
    public static function my_list_by_type($userid, $choosed_type) {
        return database\Db_activity::my_list_by_type($userid, $choosed_type);
    }
    
    public static function share_list_by_type($choosed_type) {
        
        $my_list = database\Db_activity::share_list($choosed_type);
        
        // 提取重复活动的siblings,存入数组
        $array = self::get_repeat_zone_from_list ($my_list);
        $data = array("my_list" => $array);
        
        return $data;
        
    }
    
    public static function get_repeat_zone_from_list ($my_list) {
        
        $array = [];
        $repeat_time_zone = [];
        
        foreach ($my_list as $activity) {
            $id         = $activity['id'];
            $pid        = $activity['pid'];
            $begintime  = $activity['begintime'];
            $endtime    = $activity['endtime'];
            
            if ($pid != 0) {
                $repeat_time_zone[$pid][$id] = ["begintime" => $begintime, "endtime" => $endtime];
            }
        }
        
        foreach ($my_list as $activity) {
            $pid = $activity['pid'];
            if ($pid != 0) {
                $activity['repeat_time_zone'] = $repeat_time_zone[$pid];
            }
            $act = new Activity($activity);
            array_push($array, $act->packInfo(false));
        }
        
        return $array;
        
    }
  
    public static function view_by_user ($id, $userid) {
        
        $activity = database\Db_activity::view_by_user($id, $userid);
        \framework\Logging::d('view act', json_encode($activity));
        if (empty($activity)) {
            return false;
        }
        
        foreach ($activity as $activity) {
            return new Activity($activity);
        }
        
    }
    
    public static function search($input, $start) {
        $data = database\Db_activity::search($input, $start);
        return self::get_repeat_zone_from_list($data);
    }

    
    
    
};


