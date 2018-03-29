<?php

namespace my_calendar_server_reborn\controller\api\v1;

use my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Internal_user_controller extends \my_calendar_server_reborn\controller\api\v1_base {
    
    public function pretreat() {
        
        $calendar_session = get_request("calendar_session");
        $user = app\TempUser::oneBySession($calendar_session);
        if (empty($user)) {
            return array('op' => 'fail', "code" => '000002', "reason" => '无此用户');
        }

        set_session('userid', $user->id());
        set_session('username', $user->nickname());
        
    }
    
    public function posttreat() {

        unset_session('userid');
        unset_session('username');
        
    }

    public function request_code() {
        $phone_number = get_request("phone_number");
        
        $sms = app\Sms::instance();
        
        $random_code = sprintf('%06s', rand(0, 999999));;
        
        \framework\Logging::l("phone_number", $phone_number);
        //\framework\Logging::l("random_code", $random_code);
        
        
        $ret = json_decode($sms->send_verify_code($phone_number, $random_code));
        if ($ret->result != 0) {
           return array(
           'op' => 'fail', 
           "code" => '00345002', 
           "reason" => '请求验证码失败, 腾讯错误码： ' . $ret->result . '错误信息： ' . $ret->errmsg);
        }
        
        
        $user = app\User::create_by_phone($phone_number);
        
        $user->set_verify_code($random_code);
        $user->set_phone_number($phone_number);
        $user->set_expired(time() + 60 * 5);
        
        $ret = $user->save();
        
        return $ret ? $this->op("request_code", $ret) : array('op' => 'fail', "code" => '015012', "reason" => '保存临时用户失败');

    }
    
    public function verify() {
        $phone_number = get_request("phone_number");
        $verify_code = get_request("verify_code");
        $tempuserid = get_session('userid');
       
        $user = app\User::get_by_phone($phone_number);
        
        if (empty($user)) {
            return array('op' => 'fail', "code" => '0034102', "reason" => '此手机尚未请求验证码');
        }
        
        $check_result = $user->check_verify($verify_code);
        
        if (empty($check_result)) {
            return array('op' => 'fail', "code" => '0134102', "reason" => '验证码错误或者已过期');
        }
        
        $tempuser = app\TempUser::verify_or_create($tempuserid, $user->id());

        return $tempuser ? $this->op("verify", $tempuser->packInfo()) : array('op' => 'fail', "code" => '0125012', "reason" => '关联微信认证失败');

    }

    
    
    
    
    
    
public function send(){
 
	   $nationCode = get_request('nationCode');
        $phoneNumber = get_request('phoneNumber');
        $yuyue_session = get_request('yuyue_session');
  
    $tempuser = TempUser::oneBySession($yuyue_session);//获取用户信息
			if(empty($nationCode)||empty($phoneNumber)||empty($yuyue_session))
			return array("data" =>array("status"=>0,"reason"=>"信息不全") ,"op" =>"verify" );
		$templId = 50285;
		
		if (empty($tempuser)) {//如果没有对应的user，就创建一个。
			$tempuser = new TempUser();
		}
		$user = InternalUser::oneByTelephone($phoneNumber);//通过手机号 获取对应的内部用户
		if (empty($user)) {//如果没有对应的user，就创建一个。
			$user = new InternalUser();
		}

	logging::d("Id", "Id is:" .$user->id());
	logging::d("verify_status", "verify_status is:" .$user->verify_status());


		logging::d("tempuser", "Id is:" .$tempuser->id());
		$tempId = $tempuser->id();//获取对应tempid
		$type = 0;
		if($user->id()==0){//未注册
				
			$user->setTempId($tempId);
			logging::d("tempId", "tempId is:" .$tempId);
			$user->setTelephone($phoneNumber);
			logging::d("sendsms", "nationCode is:" .$nationCode);
			logging::d("sendsms", "phoneNumber is:" .$phoneNumber);
	
		}
		else if($user->id()!=0){//已注册
			if($user->verify_status()=="true"){//已注册成功，登陆
	
				if($tempId==$user->tempid()){//对应微信登陆 不做处理
					$type = 1;
				}else{//不是对应微信 获取session
					$tempuser = TempUser::oneById($user->tempid());//获取对应用户信息
					$yuyue_session =$tempuser->yuyue_session();//获取yuyue_session
					$type = 2;
				}
			}else{//未注册成功
				if($tempId==$user->tempid()){//对应微信注册 不做处理
				
				}else{//绑定有问题
					$user->setTempId($tempId);
			logging::d("tempId", "tempId is:" .$tempId);
				}
		
			}
			
		}	
			
			$verification_code = rand(1000,9999);//随机验证码
			$user->setCode($verification_code);
			logging::d("sendsms", "verification_code is:" .$verification_code);
			
			$params =array("".$verification_code);

			$sender = new SmsSingleSender( WX_SMS_SDKID,WX_SMS_SECRET);
			$result = $sender->sendWithParam($nationCode, $phoneNumber, $templId, $params);
			$result=json_decode($result);

			if($result->result == 0){
				$id = $user->save();	
			}
			$data = array("type"=>$type,"info"=>array( "id"=>$id,"yuyue_session"=>$yuyue_session),"result" =>$result);
	
		return array("data" =>$data ,"op" =>"send" );
   }

   
   public function vverify() {
	
		$nationCode = get_request('nationCode');
		$phoneNumber = get_request('phoneNumber');
		$yuyue_session = get_request('yuyue_session');
		logging::d("yuyue_session", "yuyue_session is:" .$yuyue_session );
		$verify_code = get_request('verify_code');
		$tempuser = TempUser::oneBySession($yuyue_session);//获取用户信息
		$user = InternalUser::oneByTelephone($phoneNumber);//通过手机号 获取对应的内部用户
		
		$id = -1 ;
		$reason="系统错误";
		 $data = new stdClass();
           
		if(empty($nationCode)||empty($phoneNumber)||empty($yuyue_session)||empty($verify_code)){
			logging::d("yuyue_session", "111111 is:"  );
			$data->reason ="信息不全";
			$data->status = 0;
		
		}else if(empty($tempuser)) {//如果没有对应的user，系统错误。
			logging::d("yuyue_session", "1222222 is:"  );
			$data->reason ="系统错误，请重启小程序";
			$data->status = 0;
		}else if (empty($user)) {//如果没有对应的user，系统错误。
			logging::d("yuyue_session", "33333 is:"  );
			$data->reason ="手机错误，请重新输入";
			$data->status = 0;
			
		}else{ 
			if(!$user->verify($verify_code)){
				logging::d("yuyue_session", "44444 is:"  );
				$data->reason ="验证码错误";
				$data->status = 0;
	
			}else{
			
				$tempId = $tempuser->id();//获取对应tempid			
				if($user->tempid()== $tempId){//单方绑定无误
			
					if($user->verify_status()=="true"){//已注册成功，登陆
			logging::d("yuyue_session", "status 1 $status " . $status);
						$data->status = 2;
					}else if($tempuser->uid()==0){//未注册,注册
					logging::d("yuyue_session", "status 2 $status " . $status);
						$tempuser->setUId($user->id());
						$user->setStatus("true");
						$user->setCode("00000");
						$data->status = 1;
					}else{//一个微信注册过，又用另一个手机号注册
					
						$data->reason ="无此用户";
						$data->status = 0;
						logging::d("yuyue_session", "status 3 $status " . $status);
					}
			
				}else {//不是对应微信，
				
					if($user->verify_status()=="true"){//已注册成功，登陆
						$tempuser = TempUser::oneById($user->tempid());//获取对应用户信息
						if (empty($tempuser)) {//如果没有对应的user，系统错误。
							$data->reason ="系统错误，账号无效，请联系管理员";
							$data->status = 0;
					
						}else{
							
						$yuyue_session =$tempuser->yuyue_session();//获取yuyue_session
						$user->setStatus("true");
						$user->setCode("00000");
						$data->status = 3;
						}
					}else if($tempuser->id()==0){//不应有这种情况
						$tempuser->setUId($user->id());
						$user->setTempId($tempuser->id());
						$user->setStatus("true");
						$user->setCode("00000");
						$data->status = 1;
					}else{//账号绑定错误
						$data->reason ="账号错误";
						$data->status = 0;
					}
				//	logging::d("yuyue_session", "status 4 $status " . $status);
				}
			}
		}	
		if($data->status!=0){
			logging::d("verify_action", "$status!=0  " .$status );
			
			$id = $user->save();
			$tempuser->save();
			$data->info = array( "id" => $id , "yuyue_session" => $yuyue_session);
		}else{
		//	logging::d("verify_action", "$data->status==0  " .$data->status );	
		}
		//logging::d("verify_action", " status  " .$data->status." reason " .$data->reason." id " .$data->id);

		return array("op" => "verify","data" => $data  );
   }
    public function getInfo() {

		$yuyue_session = get_request('yuyue_session');		
	$data= new stdclass();
		if(empty($yuyue_session)){
			logging::d("yuyue_session", "111111 is:"  );
			$data->reason ="信息不全";
			$data->status = 0;
		return array( "op" => "getInfo","data" => $data);
		}
		$tempuser = TempUser::oneBySession($yuyue_session);//获取用户信息
		if(empty($tempuser)) {//如果没有对应的user，系统错误。
			logging::d("yuyue_session", "1222222 is:"  );
			$data->reason ="yuyue_session错误，请重启小程序";
			$data->status = 0;
		}else if($tempuser->uid()==0){
			logging::d("yuyue_session", "00000 is:"  );
			$data->reason ="未注册";
			$data->status = 0;
		}else{
		$user = InternalUser::oneById($tempuser->uid());
		
		
		if (empty($user)) {//如果没有对应的user，系统错误。
			logging::d("yuyue_session", "33333 is:"  );
			$data->reason ="无此用户";
			$data->status = 0;
			
		}else if($user->verify_status()&&$tempuser->uid() == $user->id()&&$user->tempId()==$tempuser->id()){
			$data->uid =  $tempuser->uid();
			$data->phoneNumber =  $user->telephone();
			$data->yuyue_session = $tempuser->yuyue_session();
			$data->status = 1;
		}
		}
		return array( "op" => "getInfo","data" => $data);
   }
	
	/*
	再次登陆，免去短信验证，只能登陆与本地微信绑定帐号
	*/
    public function login() {
		
	
		$yuyue_session = get_request('yuyue_session');		
	$data= new stdclass();
		if(empty($yuyue_session)){
			logging::d("yuyue_session", "111111 is:"  );
			$data->reason ="信息不全";
			$data->status = 0;
		return array( "op" => "getInfo","data" => $data);
		}
		$tempuser = TempUser::oneBySession($yuyue_session);//获取用户信息
		if(empty($tempuser)) {//如果没有对应的user，系统错误。
			logging::d("yuyue_session", "1222222 is:"  );
			$data->reason ="yuyue_session错误，请重启小程序";
			$data->status = 0;
		}else if($tempuser->uid()==0){
			logging::d("yuyue_session", "00000 is:"  );
			$data->reason ="未注册";
			$data->status = 0;
		}else{
		$user = InternalUser::oneById($tempuser->uid());
		
		
		if (empty($user)) {//如果没有对应的user，系统错误。
			logging::d("yuyue_session", "33333 is:"  );
			$data->reason ="无此用户";
			$data->status = 0;
			
		}else if($user->verify_status()&&$tempuser->uid() == $user->id()&&$user->tempId()==$tempuser->id()){
			$tempuser->setSessionKey =  md5(time() . $tempuser->yuyue_session());
			$tempuser->save();
			$data->uid =  $tempuser->uid();
			$data->phoneNumber =  $user->telephone();
			$data->yuyue_session = $tempuser->yuyue_session();
			$data->status = 1;
		}
		}
		return array( "op" => "getInfo","data" => $data);
   }
	
}