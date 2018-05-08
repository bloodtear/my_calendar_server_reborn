<?php
namespace my_calendar_server_reborn\controller\api\v1;

use my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Activity_controller extends \my_calendar_server_reborn\controller\api\v1_base {
    private $mToken = null;
    private $mUser = null;


    public function pretreat() {

        $calendar_session = get_request("calendar_session");
        $session = app\Session::get_by_session($calendar_session);
		
        if (empty($session)) {
            return array('op' => 'fail', "code" => '000002', "reason" => '无此用户');
        }

        set_session('userid', $session->tempid());
        set_session('username', "uid:" . $session->tempid());
		
		return false;
        
    }

// * * * * * 
// 预览图片相关
// * * * * * 
    public function preview_images_list(){
        $images = app\Preview_image::all();
        $image_types = app\Preview_image_type::all();
        
        return $this->op("preview_images_list", ['images' => $images, "image_types" => $image_types]);
    }

    
// * * * * * 
// 查询列表相关
// * * * * * 

    public function all_my_list() {    
    //列出此人所有相关的activity， 默认choosed_type = 0, 还有此人加入的活动
        $choosed_type = get_request("choosed_type", 0);
        $userid = get_session('userid');
        $entrance = get_request('entrance', 0);

        \framework\Logging::d("entrance", ($entrance));
        $my_list = app\Activity::get_my_list_by_type($userid, $choosed_type, $entrance);
        $thiz_type = ($choosed_type != 0 ? app\Activity_type::view_by_user($choosed_type, $userid) : null);
        
        \framework\Logging::d("ret", json_encode($my_list));
        return $this->op("all_my_list", ['my_list' => $my_list, 'thiz_type' => ($thiz_type ? $thiz_type->packInfo() : null)]);
        
    }
        
        
// * * * * * 
// 分享列表相关
// * * * * * 

    public function share_list() {    
        $choosed_type = get_request("choosed_type", 0);
        $userid = get_session('userid');
                
        if (empty($choosed_type)) {
            return array('op' => 'fail', "code" => '213123', "reason" => '分类不存在');
        }

        $type = app\Activity_type::view_by_user($choosed_type, $userid);
        if (empty($type)) {
            return array('op' => 'fail', "code" => '213123', "reason" => '分类不存在');
        }
        if ($type->pub() != 1) {
            return array('op' => 'fail', "code" => '2135553', "reason" => '非公开类别');
        }
        
        $subscribe_type = $type->subscribed() ? true : false;
        $my_list = app\Activity::share_list_by_type($choosed_type);
        
        $data = ["type" => $type->packInfo(), "my_list" => $my_list, "subscribe_type" => $subscribe_type];
        //\framework\Logging::d("ret", json_encode($data));
        return $this->op("share_list", $data);
        
    }
    
// * * * * * 
// 查询单体相关
// * * * * * 

    public function search() {
        $s = get_request_assert("s");

        $act1 = new app\Activity();
        $act2 = new app\Activity();
        $data = array(
            "activities" => array(
                $act1->packInfo(),
                $act2->packInfo(),
            ),
        );
        return $this->op("activities", $data);
    }

    
    public function view() {
        $id = get_request("id");
        $userid = get_session('userid');
		\framework\Logging::d("id", ($id));
        $editable   = false;
        $subscribe  = false;
        $joined     = false;
        $notice     = false;
        $join_sheet = array();
        
        $activity = app\Activity::view_by_user($id, $userid);
        
        if (empty($activity)) {
            return array('op' => 'fail', "code" => '0010022', "reason" => '活动不存在');
        }

        if ($activity->owner() == $userid) {
            $editable = true;
        }
        
        if (!empty($activity->sign_id())) {
            $joined = true;
            $join_sheet = json_decode($activity->sign_sheet());
            $notice = $activity->sign_notice();
        }
        
        if (!empty($activity->is_subscribe())) {
            $subscribe = true;
        }
        
        $data = array(
            "info" => $activity->packInfo(true),
            "editable" => $editable,
            "joined" => $joined,
            "join_sheet" => $join_sheet,
            "notice" => $notice,
            "subscribe" => $subscribe,
        );
        return $this->op("activity_view", $data);
    }

    
// * * * * * 
// 加入/退出活动相关
// * * * * * 

    public function sign() {
        $activity_id = get_request("id");
        $userid = get_session('userid');

        $joinsheet = get_request("joinsheet");
        $notice = get_request("notice");

        \framework\Logging::d("joinsheet", $joinsheet);
        $activity = app\Activity::oneById($activity_id);
        if (empty($activity)) {
            return array('op' => 'fail', "code" => 00022201, "reason" => '活动不存在');
        }
        
        $type_detail = $activity->type_detail();
        
        if ($type_detail['pub'] == 0) {
            return array('op' => 'fail', "code" => '20302', "reason" => '此活动无法报名');
        }
        
        if ($activity->status() == 1) {
            return array('op' => 'fail', "code" => '203023', "reason" => '此活动已暂停');
        }
        
        $now_participants = $activity->now_participants();
        $max_participants = $activity->max_participants();
        if ($now_participants >= $max_participants && $max_participants != 0) {
            return array('op' => 'fail', "code" => '203402', "reason" => '此活动报名额度已经满额');
        }
        
        $sign = app\Sign::get_one($activity_id, $userid);

        if ($sign) {
            $sign->set_sheet($joinsheet);
            $sign->set_notice($notice);
            $sign->set_modify_time(time());
            $ret = $sign->save();

            //$ret ? Event::record($activity->id(), $activity->calendar_id(), "10005", $userid) : 0;
            return $ret ?  array('op' => 'activity_sign', "data" => $sign->packInfo()) : array('op' => 'fail', "code" => 1033022, "reason" => '活动报名修改失败');
        }else {
            $sign = new app\Sign();
            $sign->set_activity($activity_id);
            $sign->set_user($userid);
            $sign->set_sheet($joinsheet);
            $sign->set_notice($notice);
            $sign->set_modify_time(time());
            $ret = $sign->save();
  
            //$ret ? $record = Event::record($activity->id(), $activity->calendar_id(), "10005", $userid) : 0;
            return $ret ?  array('op' => 'activity_sign', "data" => $sign->packInfo()) : array('op' => 'fail', "code" => 1033002, "reason" => '活动报名失败');
        }

    }
    
    public function unsign() {
        $activity_id = get_request("id");
        $userid = get_session('userid');
        
        $activity = app\Activity::oneById($activity_id);
        if (empty($activity)) {
            return array('op' => 'fail', "code" => 00022201, "reason" => '活动不存在');
        }

        $sign = app\Sign::get_one($activity_id, $userid);
        if (!$sign) {
            return array('op' => 'fail', "code" => 1033002, "reason" => '用户尚未报名过此活动');
        }
        $ret = $sign->cancel();
        //$ret ? $record = Event::record($activity->id(), $activity->calendar_id(), "10006", $userid) : 0;
        return $ret ?  array('op' => 'activity_unsign', "data" => $ret) : array('op' => 'fail', "code" => 1033002, "reason" => '退出活动/取消报名失败');
    }

    public function mine() {
    }

    public function reply() {
    }

    
// * * * * * 
// 发起活动相关
// * * * * *     

    public function organize() {
        $owner      = get_session('userid');

        $title      = get_request("title");
        $images     = get_request("images");
        $content    = get_request("content");
		
        $avatar     = get_request("preview_image_id");
        $type       = get_request("type_id");
        $address    = get_request("address");
        
        $begintime  = get_request("starttime");
        $endtime    = get_request("endtime");
        
        
        $repeattype = get_request("repeattype", "0");
        $repeatend  = get_request("repeat_end");
        
        $joinsheet      = get_request("joinsheet");
        $participants   = get_request("participants", 0);
        $notice         = get_request("notice", 0);
          
        $pid = 0;

        \framework\Logging::d("ACT title", ($title));
        \framework\Logging::d("ACT images", json_encode($images));
        \framework\Logging::d("ACT content", ($content));
        \framework\Logging::d("ACT avatar", ($avatar));
        \framework\Logging::d("ACT type", ($type));
        \framework\Logging::d("ACT address", ($address));
        \framework\Logging::d("ACT begintime", ($begintime));
        \framework\Logging::d("ACT endtime", ($endtime));
        \framework\Logging::d("ACT repeattype", ($repeattype));
        \framework\Logging::d("ACT repeatend", ($repeatend));
        \framework\Logging::d("ACT joinsheet", json_encode($joinsheet));
        \framework\Logging::d("ACT participants", ($participants));
        \framework\Logging::d("ACT notice", ($notice));
		
		//return;

        if (empty($title) || empty($avatar) || empty($content) ) {
            return array('op' => 'fail', "code" => 000002, "reason" => '活动标题，简介，详情不完整');
        }
        if (empty($begintime) || empty($endtime)) {
            return array('op' => 'fail', "code" => 000003, "reason" => '活动开始时间，结束时间不完整');
        }
        if (empty($address)) {
            return array('op' => 'fail', "code" => 000004, "reason" => '活动地址不完整');
        }
        
        if ($repeattype == 0) {
            $result = app\Activity::build_one($type, $owner, $participants, $title, $content, $images, $begintime, $endtime, $repeatend, $address, $repeattype, $joinsheet, $pid, $avatar, $notice);
            
            $ret = $result['ret'];
            $activity = $result['activity'];
            //$ret ? $record = Event::record($activity->id(), $activity->calendar_id(), "10001", $operator_id) : 0;
            return $ret ?  array('op' => 'activity_organize', "data" => $activity->packInfo()) : array('op' => 'fail', "code" => 203102, "reason" => '活动发起失败');
        
        }else {
        
            \framework\Logging::d("ACT begintime", ($begintime));
            \framework\Logging::d("ACT endtime", ($endtime));
            
            \framework\Logging::d("ACT repeattype", ($repeattype));
            \framework\Logging::d("ACT repeatend", ($repeatend));
            
            //repeattypes: ["仅一次", "每天", "每周", "隔周", "每月"],
            //repeatcounts: ["once", "daily", "weekly", "fortnightly", "monthly"],
            $duration = $endtime - $begintime;
            $timestamp_array = [];
            switch ($repeattype) {
                case 0: 
                    $timestamp_start = $begintime;
                    array_push($timestamp_array, $timestamp_start);
                    break;
                case 1: 
                    $timestamp_start = $begintime;
                    while ($timestamp_start <= $repeatend) {
                        \framework\Logging::d("ACT timestamp_start", ($timestamp_start));
                        array_push($timestamp_array, $timestamp_start);
                        $timestamp_start += 60 * 60 * 24;
                    }
                    break;
                    
                case 2: 
                    $timestamp_start = $begintime;
                    while ($timestamp_start <= $repeatend) {
                        array_push($timestamp_array, $timestamp_start);
                        $timestamp_start += 60 * 60 * 24 * 7;
                    }
                    break;

                case 3: 
                    $timestamp_start = $begintime;
                    while ($timestamp_start <= $repeatend) {
                        array_push($timestamp_array, $timestamp_start);
                        $timestamp_start += 60 * 60 * 24 * 7 * 2;
                    }
                    break;

                case 4: 
                    $timestamp_array = [];
                    $timestamp_start = $begintime;
                    while ($timestamp_start <= $repeatend) {
                        array_push($timestamp_array, $timestamp_start);
                        $timestamp_start = add_month($timestamp_start);

                    }
                    break;    

                default: 
                    break;
            }
            \framework\Logging::d("timestamp_array", json_encode($timestamp_array));  
            $ret = true;
            
            $activity_list = [];
            $db_activity = database\Db_activity::inst();
            $db_activity->begin_transaction();

            $first_id = 0;
            foreach ($timestamp_array as $begintime) {
                $result = app\Activity::build_one($type, $owner, $participants, $title, $content, $images, $begintime, $begintime + $duration, $repeatend, $address, $repeattype, $joinsheet, $pid, $avatar, $notice);
                
                $add_ret = $result['ret'];
                $activity_id = $result['activity']->id();
                //$add_ret ? $record = Event::record($activity_id, $result['activity']->calendar_id(), "10001", $operator_id) : 0;
                \framework\Logging::d("add_ret", $add_ret);
                \framework\Logging::d("activity_id", $activity_id);
                array_push($activity_list, $activity_id);
                $ret = $ret && $add_ret;
                \framework\Logging::d("ret", $ret);
                if ($first_id == 0) {
                    $first_id = $activity_id;
                }
                \framework\Logging::d("first_id", $first_id);
            }

            $ret = $ret && $db_activity->modify_pid($first_id, $activity_list);
            if (!$ret) {
                $db_activity->rollback();
                return array('op' => 'fail', "code" => 1002402, "reason" => '日历活动activity发起失败');
            }
            \framework\Logging::d("activity_list", json_encode($activity_list));
            
            $db_activity->commit();
            
            return array('op' => 'activity_organize', "data" => $activity_list);

        }
        
    }

    public function edit() {
        $activity_id = get_request("activity_id");
        $userid = get_session('userid');
        
        $activity = app\Activity::oneById($activity_id);
        if (empty($activity)) {
            return array('op' => 'fail', "code" => 00022201, "reason" => '活动不存在');
        }
        
        $owner = $activity->owner();
        if ($owner != $userid) {
          return array('op' => 'fail', "code" => '0023002', "reason" => '用户无权限编辑此活动');
        }
/*
        if (!$activity->has_permission($userid)) {
            return array('op' => 'fail', "code" => '0023002', "reason" => '用户无权限编辑此活动');
        }
*/
        $title = get_request("title");
        $avatar = get_request("avatar");
        $content = get_request("content");
        $address = get_request("address");
        $images = get_request("images");
        $type = get_request("type");
        
        $batch = get_request("batch");
        
        if (empty($title) || empty($content)) {
            return array('op' => 'fail', "code" => 000002, "reason" => '活动标题，简介，详情不完整');
        }
        if (empty($address)) {
            return array('op' => 'fail', "code" => 000004, "reason" => '活动地址不完整');
        }
        
        if ($batch == 1) {
            $result = app\Activity::edit_one($activity_id, $title, $content, $address, $images, $avatar, $type);
            \framework\Logging::d('edit', json_encode($activity));

            $ret = $result['ret'];
            $activity = $result['activity'];
            
            //$record = $ret ? Event::record($activity->id(), $activity->calendar_id(), "10002", $userid) : 0;
            \framework\Logging::d('edit', json_encode($result));
            \framework\Logging::d('record', json_encode($record));
            return $ret ?  array('op' => 'activity_edit', "data" => $activity->packInfo(true)) : array('op' => 'fail', "code" => 104042, "reason" => '活动修改失败');
        }else if ($batch == 2) {
            \framework\Logging::d('edit', json_encode($batch));
            $result = app\Activity::edit_batch($activity_id, $title, $content, $address, $images, $avatar, $type);
            \framework\Logging::d('edit', json_encode($activity));

            $ret = $result['ret'];
            $activity = $result['activity'];
            
            //$record = $ret ? Event::record($activity->id(), $activity->calendar_id(), "10002", $userid) : 0;
            \framework\Logging::d('edit', json_encode($result));
            \framework\Logging::d('record', json_encode($record));
            return $ret ?  array('op' => 'activity_edit', "data" => $activity->packInfo(true)) : array('op' => 'fail', "code" => 104042, "reason" => '活动修改失败');
        }
    
        
        //return $ret ?  array('op' => 'activity_edit', "data" => $activity->packInfo()) : array('op' => 'fail', "code" => 1000042, "reason" => '活动编辑失败');
        
        
    }

       
// * * * * *
// 活动撤消
// * * * * *     

public function remove() {

    $activity_id = get_request("id");
    $userid = get_session('userid');
    
    $activity = app\Activity::oneById($activity_id);
    if (empty($activity)) {
        return array('op' => 'fail', "code" => 00022201, "reason" => '活动不存在');
    }
    if ($activity->owner() != $userid) {
        return array('op' => 'fail', "code" => 0122201, "reason" => '用户无权删除此活动');
    }
    
    $ret = app\Activity::remove($activity_id);
    //$ret ? $record = Event::record($activity->id(), $activity->calendar_id(), "10011", $userid) : 0;
    return $ret ?  array('op' => 'activity_remove', "data" => $ret) : array('op' => 'fail', "code" => 526742, "reason" => '活动撤消失败');
}

public function remove_group() {

    $del_array = get_request("del_array");
    $del_array = json_decode($del_array);
    $userid = get_session('userid');
    
    if (empty($del_array)){
        return array('op' => 'fail', "code" => 527412, "reason" => '活动group撤消失败,删除的不能为空');
    }
    
    $ret = app\Activity::remove($del_array);
    //$ret ? $record = Event::record($activity->id(), $activity->calendar_id(), "10011", $userid) : 0;
    return $ret ?  array('op' => 'activity_remove_group', "data" => $ret) : array('op' => 'fail', "code" => 52742, "reason" => '活动group撤消失败');
}


    // * * * * *
// 活动暂停，开启(作废)
// * * * * *     
    
     
    
    public function cancel() {
        $activity_id = get_request("activity_id");
        $userid = get_session('userid');
        $batch = get_request("batch");

        $activity = app\Activity::oneById($activity_id);
        if (empty($activity)) {
            return array('op' => 'fail', "code" => 00022201, "reason" => '活动不存在');
        }

        $type = $activity->type();
        $owner = $activity->owner();
        if (!$activity->has_permission($userid)) {
            return array('op' => 'fail', "code" => '0023002', "reason" => '用户无权限编辑此活动');
        }
        
        if ($activity->status() == 1) {
            return array('op' => 'fail', "code" => '00244032', "reason" => '此活动已被撤消');
        }
        $ret = $activity->cancel();
        //$ret ? $record = Event::record($activity->id(), $activity->calendar_id(), "10003", $userid) : 0;
        return $ret ?  array('op' => 'activity_cancel', "data" => $activity->packInfo()) : array('op' => 'fail', "code" => 55042, "reason" => '活动撤消失败');
        
    }
    
    public function start() {
        $activity_id = get_request("activity_id");
        $userid = get_session('userid');
        $batch = get_request("batch");

        $activity = app\Activity::oneById($activity_id);
        if (empty($activity)) {
            return array('op' => 'fail', "code" => 00022201, "reason" => '活动不存在');
        }

        $type = $activity->type();
        $owner = $activity->owner();
        if (!$activity->has_permission($userid)) {
            return array('op' => 'fail', "code" => '0023002', "reason" => '用户无权限编辑此活动');
        }
        if ($activity->status() == 0) {
            return array('op' => 'fail', "code" => '00244032', "reason" => '此活动已启动');
        }
        $ret = $activity->start();
        //$ret ? $record = Event::record($activity->id(), $activity->calendar_id(), "10004", $userid) : 0;
        return $ret ?  array('op' => 'activity_start', "data" => $activity->packInfo()) : array('op' => 'fail', "code" => 525042, "reason" => '活动启动失败');
        
    }
    
    
// * * * * * 
// 关注单体相关
// * * * * *     
    
    public function subscribe() {
        $activity_id = get_request("id");
        $userid = get_session('userid');

        $activity = app\Activity::oneById($activity_id);
        if (empty($activity)) {
            return array('op' => 'fail', "code" => 00022201, "reason" => '活动不存在');
        }
        
        $subscribe = $activity->subscribe($userid);
        //$ret ? $record = Event::record($activity->id(), $activity->calendar_id(), "10010", $userid) : 0;
        return $subscribe ?  array('op' => 'activity_subsrcibe', "data" => $subscribe) : array('op' => 'fail', "code" => 566642, "reason" => '活动关注失败');
        
    }

    public function unsubscribe() {
        $activity_id = get_request("id");
        $userid = get_session('userid');
        
        $activity = app\Activity::oneById($activity_id);
        if (empty($activity)) {
            return array('op' => 'fail', "code" => 00022201, "reason" => '活动不存在');
        }
        
        $unsubscribe = $activity->unsubscribe($userid);
        //$ret ? $record = Event::record($activity->id(), $activity->calendar_id(), "10011", $userid) : 0;
        return $unsubscribe ?  array('op' => 'activity_unsubscribe', "data" => $unsubscribe) : array('op' => 'fail', "code" => 5666742, "reason" => '活动取消关注失败');
        
    }
    

    
    public function posttreat() {

        unset_session('userid');
        unset_session('username');
        
    }
    
    
    
    
    
    
}



function add_month($stamp){
        
    $y = date("Y",$stamp);
    $m = date("n",$stamp);
    $d = date("d",$stamp);
    $h = date("H",$stamp);
    $m = date("m",$stamp);
    $s = date("s",$stamp);
    
    $r = increate_month($y, $m);
    $y = $r['y'];
    $m = $r['m'];
    while(!check_valid($y, $m, $d)){
        $r = increate_month($y, $m);
        $y = $r['y'];
        $m = $r['m'];
    }
    
    $new_date = "$y-$m-$d $h:$m:$s";

    \framework\Logging::d("add_month", $new_date);
    \framework\Logging::d("add_month strtotime", strtotime($new_date));
    return strtotime($new_date);
}


function check_valid($y, $m, $d) {
    switch ($m) {
        case 1:
        case 3:
        case 5:
        case 7:
        case 8:
        case 10:
        case 12:
            if ($d > 31 || $d < 0) {
                return false;
            }
            break;
        case 4:
        case 6:
        case 9:
        case 11:
            if ($d > 30 || $d < 0) {
                return false;
            }
            break;
        case 2:
            if ($y % 4 == 0) {
                if ($d > 29 || $d < 0) {
                    return false;
                }
            }else {
                if ($d > 28 || $d < 0) {
                    return false;
                }
            }
            break;
        default:
            return false;
            break;
    }
    return true;
}

function increate_month($y, $m){
    if ($m != 12) {
        $m++;
    }else {
        $m = 1;
        $y++;
    }
    return array("y" => $y, "m" => $m);
}

