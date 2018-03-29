<?php
namespace my_calendar_server_reborn\controller\api\v1;

use my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Image_controller extends \my_calendar_server_reborn\controller\api\v1_base {
    private $mToken = null;
    private $mUser = null;

// * * * * * 
// 上传图片
// * * * * *     


    public function upload_image() {
        $image = app\Upload::upload_image();   //先存图片
        $thumbnail = app\Upload::mkUploadThumbnail($image, 200, 200);
        if (!$image || !$thumbnail) {
            return array('op' => 'fail', "code" => 111, "reason" => '上传图片失败');
        }
        return array('op' => 'upload_image', "data" => $image);
    }
    
    
    

}

