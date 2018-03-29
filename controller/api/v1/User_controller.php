<?php
namespace my_calendar_server_reborn\controller\api\v1;
use my_calendar_server_reborn\app;

class User_controller extends \my_calendar_server_reborn\controller\api\v1_base {
    public function preaction($action) {
    }

    public function login() {
        $from = get_request('from');
        \framework\Logging::d("LOGIN", "FROM:" . $from);
        
        if ($from == 'weapp') { //具体的来源，现在只有微信小程序，也就是WXAPP
            $calendar_session = get_request('calendar_session', "");  //calendar_session用作传递的userid
            $avatar = get_request('avatar', "");  //calendar_session用作传递的userid
            $nick = get_request('nick', "");  //calendar_session用作传递的userid
            \framework\Logging::d("LOGIN", "nick:" . $nick);
            \framework\Logging::d("LOGIN", "avatar:" . $avatar);
            $user = app\TempUser::oneBySession($calendar_session); //拿到具体的tempuser信息,tempuser是wx小程序的user,
            
            if (empty($user)) {                             //如果没有对应的user，就创建一个。    
                $code = get_request('code', '');
                $wx_auth_ret = app\Wxapi::wx_auth($code);   //获取openid
                if (!empty($wx_auth_ret->errcode)){
                    return array('op' => 'fail', 'code' => $wx_auth_ret->errcode, 'reason' => $wx_auth_ret->errmsg);
                }
                $openid = $wx_auth_ret->openid;
                $session_key = $wx_auth_ret->session_key;
                $calendar_session = md5(time() . $openid . $session_key);
                $token = md5(time());

                $user = app\TempUser::createByOpenid($openid);  //创建TempUser,修改属性，保存
                $user->setOpenId($openid);
                $user->setSessionKey($session_key);
                $user->setToken($token);
                $user->setSession($calendar_session);
                \framework\Logging::d("LOGIN", "calendar_session now is :" . $calendar_session);
            }
            
            $user->setAvatar($avatar);
            $user->setNickname($nick);
            $user->save();

            $data = new \stdClass();
            $data->timeout = time() + 7200;
            $data->uid =$user->uid();
            $data->token = $user->token();
            $data->calendar_session = $user->calendar_session();

            return array("op" => "login", 'data' => $data);
        }
    }

    public function refreshtoken() { //刷新token
        $calendar_session = get_request('calendar_session', "");
        $user = app\TempUser::oneBySession($calendar_session);
        
        $token = md5(time());
        $user->setToken($token);
        $user->save();
        
        $data = new \stdClass();
        $data->timeout = time() + 7200;
        $data->token = $user->token();
        
        return array("op" => "refreshtoken", 'data' => $data);
    }

    public function bind() {
    }

    public function register() {
    }





}





