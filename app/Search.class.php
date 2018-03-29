<?php
namespace my_calendar_server_reborn\app;



class Search {

    private static $instance;

    public static function instance() {
        if (empty(self::$instance)) {
            self::$instance = new Search();
        }
        return self::$instance;
    }

    private function __construct() {

    }

    public static function search($input, $start) {

        $input = Scws::instance()->split_text($input);

        $activity_search = Activity::search($input, $start);
        $activity_type_search = Activity_type::search($input, $start);
        
        return ["activity_search" => $activity_search, "activity_type_search" => $activity_type_search];
    }

 
};

