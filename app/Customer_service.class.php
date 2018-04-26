<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Customer_service {
    private $mSummary = null;

    public function __construct($summary = array()) {
        if (empty($summary)) {
            $summary = array(
                "id" => 0,
            );
        }
        $this->mSummary = $summary;
    }
    
    //小程序的原始ID
    public function ToUserName() {
        return $this->mSummary["ToUserName"];
    }
    
    //发送者的openid
    public function FromUserName() {
        return $this->mSummary["FromUserName"];
    }
    
    //消息创建时间(整型）
    public function CreateTime() {
        return $this->mSummary["CreateTime"];
    }
    
    //MsgType : event  miniprogrampage  image  text
    public function MsgType() {
        return $this->mSummary["MsgType"];
    }
    
    //TEXT
    //文本消息内容
    public function Content() {
        return $this->mSummary["Content"] ?? null;
    }
    
    //消息id，64位整型
    public function MsgId() {
        return $this->mSummary["MsgId"];
    }
    
    // pic
    //图片链接（由系统生成）
    public function PicUrl() {
        return $this->mSummary["PicUrl"] ?? null;
    }
    
    //图片消息媒体id，可以调用获取临时素材接口拉取数据。
    public function MediaId() {
        return $this->mSummary["MediaId"] ?? null;
    }
    
    // MINIAPP
    //小程序appid
    public function AppId() {
        return $this->mSummary["AppId"] ?? null;
    }

    //小程序页面路径
    public function PagePath() {
        return $this->mSummary["PagePath"] ?? null;
    }

    //封面图片的临时cdn链接
    public function ThumbUrl() {
        return $this->mSummary["ThumbUrl"] ?? null;
    }

    //封面图片的临时素材id
    public function ThumbMediaId() {
        return $this->mSummary["ThumbMediaId"] ?? null;
    }

    //ENTER
    //封面图片的临时素材id
    public function Event() {
        return $this->mSummary["Event"] ?? null;
    }
    
    //开发者在客服会话按钮设置的session-from属性
    public function SessionFrom() {
        return $this->mSummary["SessionFrom"] ?? null;
    }



    public function save() {
        // $id = $this->id();
        // if ($id == 0) {
        //     $id = db_template::inst()->add();
        //     if ($id !== false) {
        //         $this->mSummary["id"] = $id;
        //     }
        // } else {
        //     $id = db_template::inst()->modify($id);
        // }
        // return $id;
    }

    public function packInfo() {
       return array(
            "SessionFrom" => $this->SessionFrom(),
            "Event" => $this->Event(),
            "ThumbMediaId" => $this->ThumbMediaId(),
            "ThumbUrl" => $this->ThumbUrl(),
            "PagePath" => $this->PagePath(),
            "AppId" => $this->AppId(),
            "MediaId" => $this->MediaId(),
            "PicUrl" => $this->PicUrl(),
            "MsgId" => $this->MsgId(),
            "MsgType" => $this->MsgType(),
            "CreateTime" => $this->CreateTime(),
            "FromUserName" => $this->FromUserName(),
            "ToUserName" => $this->ToUserName()
        );
    }

    public static function check_sign() {
        
        $signature  = get_request("signature");
        $timestamp  = get_request("timestamp");
        $nonce      = get_request("nonce");
        $token      = WX_SERVICE_TOKEN;
        
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        return $tmpStr == $signature;
    }

    public function send_welcome_msg() {
        return Wxapi::send_welcome_msg($this->FromUserName());
    }
    
    public static function autoReply($openid) {
        $json = self::makeTextJson(
            $openid, 
            "[自动回复]请关注 [ 小柠檬科技 ] 公众号，谢谢！");
        return Wxapi::sendMsg($json);
    }
    
    public static function welcomeMsg($openid) {
        $json = self::makeLinkJson(
            $openid, 
            "如何获取消息提醒", 
            "关注公众号(点击右上角小柠檬科技公众号)", 
            "http://mp.weixin.qq.com/s?__biz=MzUyOTE2MDMzMg==&mid=100000004&idx=1&sn=e9c95e8d93624d6ff03a382e5426667f&chksm=7a6400e74d1389f1d288e50b12c500e183d14bff67103d5aadb604270b73803d445e9ad64325&mpshare=1&scene=1&srcid=0425bo2MPKJyZ02NL8P3szS2#rd", 
            "https://mp.weixin.qq.com/mp/qrcode?scene=10000004&size=102&__biz=MzUyOTE2MDMzMg==&mid=100000004&idx=1&sn=e9c95e8d93624d6ff03a382e5426667f&send_time=");
        return Wxapi::sendMsg($json);
    }


    public static function makeTextJson($openid, $content) {
        $arr = array(
            "touser"  => $openid,
            "msgtype" => "text",
            "text" => 
            array(
                "content" => $content
            )
        );
        return json_decode(json_encode($arr));
    }

    
    public static function makeLinkJson($openid, $title, $description, $url, $thumb_url) {
        $arr = array(
            "touser"  => $openid,
            "msgtype" => "link",
            "link" => 
            array(
                "title" => $title,
                "description" => $description,
                "url" => $url,
                "thumb_url" => $thumb_url
            )
        );
        return json_decode(json_encode($arr));
    }

    
    
}

