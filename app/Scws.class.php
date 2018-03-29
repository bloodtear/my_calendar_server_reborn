<?php
namespace my_calendar_server_reborn\app;



class Scws {

    private static $instance;

    public static function instance() {
        if (empty(self::$instance)) {
            self::$instance = new Scws();
        }
        return self::$instance;
    }

    private function __construct() {

    }

    public function split_text($text) {

       
        //require_once(dirname(__FILE__) .'/pscws4/PSCWS4.class.php');
        //新建对象
        $pscws = new pscws4\PSCWS4('utf8');
        //初始化参数
        $pscws->set_dict(APP_PATH . 'app/pscws4/etc/dict.utf8.xdb');
        $pscws->set_rule(APP_PATH . 'app/pscws4/etc/rules.utf8.ini');
        $pscws->set_charset('utf8');

        $pscws->send_text($text);
        $searchWord = '';
        while ($some = $pscws->get_result())
        {	
            foreach($some as $word){
                $searchWord .= $word['word'].' ';
            }
        }

        return $searchWord;
    }

 
};

