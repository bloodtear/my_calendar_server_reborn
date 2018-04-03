<?php
namespace my_calendar_server_reborn\controller\api\v1;

use my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Activity_type_controller extends \my_calendar_server_reborn\controller\api\v1_base {
    private $mToken = null;
    private $mUser = null;

    public function pretreat() {
        
        $calendar_session = get_request("calendar_session");
        $user = app\TempUser::oneBySession($calendar_session);
        if (empty($user)) {
            return array('op' => 'fail', "code" => '000002', "reason" => '无此用户');
        }

        set_session('userid', $user->id());
        set_session('username', $user->nickname());
        
    }

 
    public function custom_type_modify(){
        $type_id = get_request("type_id");
        $title = get_request("title");
        $pub = get_request("pub");

        $userid = get_session('userid');
        if ($type_id != 0) {
            $ret = app\Activity_type::modify($type_id, $title, $pub);
        }else {
            $ret = app\Activity_type::add($userid, $title, $pub);
        }
        
        return $ret ? $this->op("custom_type_modify", $ret) : array('op' => 'fail', "code" => '01012', "reason" => '修改自定义类型失败');
    }

    public function custom_type_remove(){
        $type_id = get_request("type_id");
        $userid = get_session('userid');
        
        $arr = app\Activity_type::get_by_userid($userid);
        \framework\Logging::d("rrr", count($arr));
        if (count($arr) == 1) {
            return array('op' => 'fail', "code" => '0013', "reason" => '最后一个分类无法删除');
        }
        $ret = app\Activity_type::remove($type_id);
        return $ret ? $this->op("custom_type_remove", $ret) : array('op' => 'fail', "code" => '02012', "reason" => '删除自定义类型失败');
    }

    public function my_custom_types(){
        $userid = get_session('userid');
        
        $my_types = app\Activity_type::get_my_types($userid);
        $my_joined_list = app\Activity::my_joined_list($userid);
        $my_subscribed_list = app\Activity::my_subscribed_list($userid);

        $arr = [];
        
        if (!empty($my_types)) {
            $type = new \stdClass;
            $type->title = '全部';
            $type->pub = 0;
            $type->id = 0;
            array_push($arr, $type);
        }
        if (!empty($my_joined_list)) {
            $type = new \stdClass;
            $type->title = '我加入的';
            $type->pub = 0;
            $type->id = -1;
            array_push($arr, $type);
        }
        
        if (!empty($my_subscribed_list)) {
            $type = new \stdClass;
            $type->title = '我关注的';
            $type->pub = 0;
            $type->id = -2;
            array_push($arr, $type);
        }
        
        foreach ($my_types as $type) {
            array_push($arr, $type);
        }
        
        $data = ["my_types" => $arr];
        return $this->op("my_custom_types", $data);
    }
   
   
// * * * * * 
// 关注分类相关
// * * * * *     

    public function subscribe_type() {
        $type_id = get_request("type_id");
        $userid = get_session('userid');
        
        $type = app\Activity_type::create($type_id);
        if (empty($type)) {
            return array('op' => 'fail', "code" => 00022201, "reason" => '分类不存在');
        }

        $subscribe_type = $type->subscribe($userid);
        //$ret ? $record = Event::record($activity->id(), $activity->calendar_id(), "10010", $userid) : 0;
        return $subscribe_type ?  array('op' => 'subscribe_type', "data" => $subscribe_type) : array('op' => 'fail', "code" => 566642, "reason" => '分类关注失败');
        
    }

    public function unsubscribe_type() {
        $type_id = get_request("type_id");
        $userid = get_session('userid');
        
        $type = app\Activity_type::create($type_id);
        if (empty($type)) {
            return array('op' => 'fail', "code" => 00022201, "reason" => '分类不存在');
        }
        
        $unsubscribe_type = $type->unsubscribe($userid);
        //$ret ? $record = Event::record($activity->id(), $activity->calendar_id(), "10010", $userid) : 0;
        return $unsubscribe_type ?  array('op' => 'unsubscribe_type', "data" => $unsubscribe_type) : array('op' => 'fail', "code" => 566642, "reason" => '分类取消关注失败');
        
    }
    
    public function posttreat() {

        unset_session('userid');
        unset_session('username');
        
    }

}

