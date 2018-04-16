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
        
            $calendar_session = get_request('calendar_session', "");  //calendar_session用作传递的token
            $avatar = get_request('avatar', "");  
            $nick = get_request('nick', "");  
            
            $session = app\Session::get_by_session($calendar_session);
            

            if (!empty($session) ) {
                
                $user = app\TempUser::get($session->tempid());
                
            }else {

                $session = new app\Session();
                
                // 没有session说明是新用户，没有对应的user，需要先补齐user
                $code           = get_request('code', '');
                $iv             = get_request("iv");
                $encrypted_data = get_request("encrypted_data");
                
                $wx_auth_ret = app\Wxapi::wx_auth($code);   //获取openid,unionid
                \framework\Logging::d("LOGIN", "wx_auth_ret:" . json_encode($wx_auth_ret));
                
                if (!empty($wx_auth_ret->errcode)){
                    return array('op' => 'fail', 'code' => $wx_auth_ret->errcode, 'reason' => $wx_auth_ret->errmsg);
                }
                
                $session_key = $wx_auth_ret->session_key;  
                
                if (!empty($wx_auth_ret->unionid)) {
                    $unionid = $wx_auth_ret->unionid ;
 
                }else { //unionid未获取到说明未关注公众号，则通过encrypted_data获取
                    $unsign = app\Wxapi::unsign($session_key, $encrypted_data, $iv);
                    \framework\Logging::d("LOGIN", "unsign:" . json_encode($unsign));
                    
                    if ($unsign['op'] == 'fail') {
                        return $unsign;
                    }
                    
                    $unsign = json_decode($unsign['data']);
                    $unionid = $unsign->unionId;
                }

                $openid = $wx_auth_ret->openid;
                $calendar_session = md5(time() . $openid . $session_key);

                $user = app\TempUser::createByOpenid($openid); 
                $user->setOpenId($openid);
                $user->setSessionKey($session_key);
                $user->setUnionId($unionid);

            }
            
            // 刷新用户nickname, avatar
            $user->setAvatar($avatar);
            $user->setNickname($nick);
            $ret = $user->save();
            if (empty($ret)) {
                return array('op' => 'fail', 'code' => 23242, 'reason' => "用户信息保存失败");
            }
            
            // 刷新用户session信息
            $timeout = time() + 60 * 60;
            
            $session->set_calendar_session($calendar_session);
            $session->set_tempid($user->id());
            $session->set_last_login(time());
            $session->set_expired($timeout);
            
            $ret = $session->save();

            return $ret ? array("op" => "login", 'data' => ["timeout" => $timeout, "uid" => $user->uid(), "calendar_session" => $session->calendar_session()]) : array('op' => 'fail', 'code' => 232242, 'reason' => "登录失败");;
        }
    }
    
    //刷新session
    public function refresh_session() { 
        $pre = self::pretreat();
		if (!empty($pre)) {
			return $pre;
		}
        
        $calendar_session = get_session('calendar_session');
        
        $session = app\Session::get_by_session($calendar_session);
        $user = app\TempUser::get($session->tempid());
        
        $openid = $user->openid();
        $session_key = $user->session_key();
        
        $calendar_session = md5(time() . $openid . $session_key);
        
        
        
        
        

        $timeout = time() + 60 * 60;
            
        $session->set_calendar_session($calendar_session);
        $session->set_last_login(time());
        $session->set_expired($timeout);

        $ret = $session->save();
        
        self::posttreat();
        
        return $ret ? array("op" => "refresh_session", 'data' => ["timeout" => $timeout, "calendar_session" => $session->calendar_session()]) : array('op' => 'fail', 'code' => 232242, 'reason' => "登录失败");;

    }


    public function register() {
    }


    public static function pretreat() {
        
        $calendar_session = get_request("calendar_session");
        $session = app\Session::get_by_session($calendar_session);
		
        if (empty($session)) {
            return array('op' => 'fail', "code" => '000002', "reason" => '无此用户');
        }

        set_session('userid', $session->tempid());
        set_session('username', "uid:" . $session->tempid());
        set_session('calendar_session', $calendar_session);
		
		return false;
        
    }
    
        
    public static function posttreat() {

        unset_session('userid');
        unset_session('username');
        unset_session('calendar_session');
        
    }
    
    


}





