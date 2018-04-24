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
        return $this->mSummary["Content"];
    }
    
    //消息id，64位整型
    public function MsgId() {
        return $this->mSummary["MsgId"];
    }
    
    // pic
    //图片链接（由系统生成）
    public function PicUrl() {
        return $this->mSummary["PicUrl"];
    }
    
    //图片消息媒体id，可以调用获取临时素材接口拉取数据。
    public function MediaId() {
        return $this->mSummary["MediaId"];
    }
    
    // MINIAPP
    //小程序appid
    public function AppId() {
        return $this->mSummary["AppId"];
    }

    //小程序页面路径
    public function PagePath() {
        return $this->mSummary["PagePath"];
    }

    //封面图片的临时cdn链接
    public function ThumbUrl() {
        return $this->mSummary["ThumbUrl"];
    }

    //封面图片的临时素材id
    public function ThumbMediaId() {
        return $this->mSummary["ThumbMediaId"];
    }

    //ENTER
    //封面图片的临时素材id
    public function Event() {
        return $this->mSummary["Event"];

    //开发者在客服会话按钮设置的session-from属性
    public function SessionFrom() {
        return $this->mSummary["SessionFrom"];
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
            "id" => $this->id(),
            "name" => $this->name(), 
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

    
    
}

