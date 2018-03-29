<?php

namespace my_calendar_server_reborn\controller\api\v1;

class Setting_controller extends v1_base {
    public function preaction($action) {
    }


    public function listsettings() {
        $settings = setting::instance()->load_all();
        foreach ($settings as $k => $option) {
            if ($option["type"] == 1) {
                $settings[$k]["value"] = (int)$settings[$k]["value"];
            }
        }
        return $this->op("listsettings", $settings);
    }

    public function editsetting() {
        $id = get_request_assert("id");
        $val = get_request_assert("value");
        $ret = setting::instance()->update($id, $val);
        return $this->checkRet($ret);
    }

}













