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
            
            \framework\Logging::d("LOGIN", "nick:" . $nick);
            \framework\Logging::d("LOGIN", "avatar:" . $avatar);
            
            $user = app\TempUser::oneBySession($calendar_session); //拿到具体的tempuser信息,tempuser是wx小程序的user,
            
            if (empty($user) ) {
                
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
                
                $user = app\TempUser::createByOpenid($openid);  //创建TempUser,修改属性，保存   
                $user->setOpenId($openid);
                $user->setSessionKey($session_key);
                $user->setUnionId($unionid);
                $user->setSession($calendar_session);
                \framework\Logging::d("LOGIN", "calendar_session now is :" . $calendar_session);
                
            }
            
            $user->setAvatar($avatar);
            $user->setNickname($nick);
            $user->save();

            $data = new \stdClass();
            $data->timeout = time() + 60 * 60;
            $data->uid =$user->uid();
            $data->calendar_session = $user->calendar_session();

            return array("op" => "login", 'data' => $data);
        }
    }
    
    //刷新session
    public function refresh_session() { 
        $pre = self::pretreat();
		if (!empty($pre)) {
			return $pre;
		}
        
        $calendar_session = get_session('calendar_session');
        $user = app\TempUser::oneBySession($calendar_session);
        
        $openid = $user->openid();
        $session_key = $user->session_key();
        
        $calendar_session = md5(time() . $openid . $session_key);
        
        $user->setSession($calendar_session);
        $user->save();
        
        $data = new \stdClass();
        $data->timeout = time() + 60 * 60;
        $data->calendar_session = $user->calendar_session();
        
        self::posttreat();
        
        return array("op" => "refresh_session", 'data' => $data);
    }


    public function register() {
    }


    public static function pretreat() {
        
        $calendar_session = get_request("calendar_session");
        $user = app\TempUser::oneBySession($calendar_session);
		
        if (empty($user)) {
            return array('op' => 'fail', "code" => '000002', "reason" => '无此用户');
        }

        set_session('userid', $user->id());
        set_session('username', $user->nickname());
        set_session('calendar_session', $calendar_session);
		
		return false;
        
    }
    
        
    public static function posttreat() {

        unset_session('userid');
        unset_session('username');
        
    }
    
    


}





