<?php
namespace my_calendar_server_reborn\controller\api\v1;

use my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Search_controller extends \my_calendar_server_reborn\controller\api\v1_base {
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
// 搜索
// * * * * *     


    public function search() {
        $input = get_request('input');
        $start = get_request('start', 0);
        
        $data = app\Search::search($input, $start);
        
        return array('op' => 'search', "data" => $data);
    }
    
    
        
    public function posttreat() {

        unset_session('userid');
        unset_session('username');
        
    }

}

