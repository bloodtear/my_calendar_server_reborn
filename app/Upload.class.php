<?php
namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class Upload {
    
    public static function upload_image() {
        $file = $_FILES['file'];
        \framework\Logging::d('upload_image', 'file:' . json_encode($file));
        
        if(!is_uploaded_file($file['tmp_name'])){
          \framework\Logging::d('upload_image', '上传CHECK:' . '不是通过HTTPPOST上传的');
          return false;
        }
        
        if (!file_exists(UPLOAD_DIR)) {
            $ret = @mkdir(UPLOAD_DIR, 0777, true);
            if ($ret === false) {
                \framework\Logging::d('upload_image', '目录创建:' . '上传目录创建失败');
                return false;
            }
        }
        
        $type = $file['type'];
        $extension = explode('/', $type);
        $extension = $extension[1];
        
        $new_name = md5(file_get_contents($file['tmp_name'])) . '.' . $extension;
        \framework\Logging::d('upload_image', 'new_name:' . $new_name);
        
        if(file_exists(UPLOAD_DIR . "/" . $new_name)){
            return $new_name;
        }

        if(!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . "/" . $new_name)){
            \framework\Logging::d('upload_image', 'move_uploaded:' . 'move_uploaded失败');
            return false;
        }
        return $new_name;
        
    }
    
    private static function mkThumbnail($src, $width = 0, $height = 0, $filename = null) {
        \framework\Logging::d("UPLOAD" , "mkThumbnail : " . "start");
        if ($width === null && $height === null)
            return false;
        if ($width < 0)
            return false;
        if ($height < 0)
            return false;
        if ($width == 0 && $height == 0)
            return false;

        $size = getimagesize($src);
        if (!$size)
            return false;

        list($src_w, $src_h, $src_type) = $size;
        $src_mime = $size['mime'];
        switch($src_type) {
        case 1 :
            $img_type = 'gif';
            break;
        case 2 :
            $img_type = 'jpeg';
            break;
        case 3 :
            $img_type = 'png';
            break;
        case 15 :
            $img_type = 'wbmp';
            break;
        default :
            return false;
        }

        if ($width == 0)
            $width = $src_w * ($height / $src_h);
        if ($height == 0)
            $height = $src_h * ($width / $src_w);

        $imagecreatefunc = 'imagecreatefrom' . $img_type;
        $src_img = $imagecreatefunc($src);
        $dest_img = imagecreatetruecolor($width, $height);

        // 解决透明色会成黑色的问题
        $color = imagecolorallocate($dest_img, 255, 255, 255);
        // imagecolortransparent($dest_img, $color);
        imagefill($dest_img, 0, 0, $color);

        imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $width, $height, $src_w, $src_h);

        $imagefunc = 'image' . $img_type;
        if ($filename !== null) {
            $imagefunc($dest_img, $filename);
        } else {
            header('Content-Type: ' . $src_mime);
            $imagefunc($dest_img);
        }
        imagedestroy($src_img);
        imagedestroy($dest_img);
        return true;
    }

    // $result = mkThumbnail('./IMG_3324.JPG', 147, 147);

    public static function mkUploadThumbnail($filename, $width = 0, $height = 0) {
        \framework\Logging::d("UPLOAD" , "mkUploadThumbnail - start: " );
        \framework\Logging::d("UPLOAD" , "filename - start: " . $filename);
        \framework\Logging::d("UPLOAD" , "width - start: " . $width);
        \framework\Logging::d("UPLOAD" , "height - start: " . $height);
        if (empty($filename)) {
            \framework\Logging::d("UPLOAD" , "mkUploadThumbnail - filename: " . $filename);
            return null;
            
        }
        $filepath = rtrim(UPLOAD_DIR, "/") . "/$filename";
        $thumbnail = rtrim(THUMBNAIL_DIR, "/") . "/thumbnail-$filename";
        if (!is_dir(THUMBNAIL_DIR)) {
            mkdir(THUMBNAIL_DIR, 0777, true);
        }
        if (is_file($thumbnail)) {
            return rtrim(THUMBNAIL_URL, "/") . "/thumbnail-$filename";
        }
        \framework\Logging::d("UPLOAD" , "mkUploadThumbnail - filepath: " . $filepath);
        if (!is_file($filepath)) {
            return null;
        }
        Upload::mkThumbnail($filepath, $width, $height, $thumbnail);
        if (is_file($thumbnail)) {
            return rtrim(THUMBNAIL_URL, "/") . "/thumbnail-$filename";
        }
        return null;
    }
        
    public static function save_qcode($imgsrc, $id) {

        //framework\Logging::d("src", $imgsrc);
        \framework\Logging::d("id", $id);
        if (!file_exists(UPLOAD_DIR . "/qcode")) {
            $ret = @mkdir(UPLOAD_DIR . "/qcode", 0777, true);
            if ($ret === false) {
                framework\Logging::d('upload_image', '目录创建:' . '上传目录创建失败');
                return false;
            }
        }
        
        $extension = "jpg";
        \framework\Logging::d('make_qcode', 'new_name:' . $id);
        
        $new_name = $id . "." . $extension;
        if (file_exists(UPLOAD_DIR . "/qcode/" . $new_name)) {
            return $new_name;
        }
        $ret = file_put_contents(UPLOAD_DIR . "/qcode/" . $new_name, $imgsrc);
        return $ret ? $new_name : false;
    }
    
    
}

?>