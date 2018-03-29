<?php

namespace my_calendar_server_reborn\controller\api;

class v1_base {
    const kRet_Success = 0;
    const kRet_Fail = 1;

    protected function packArray($arr) {
        $data = array();
        foreach ($arr as $record) {
            $data[] = $record->packInfo();
        }
        return $data;
    }

    protected function result($code) {
        $table = array(
            self::kRet_Success => "success",
            self::kRet_Fail => "fail",
        );
        $reason = $table[$code];
        return array("op" => "result", "data" => array("code" => $code, "reason" => $reason));
    }

    protected function op($op, $data) {
        return array("op" => $op, "data" => $data);
    }

    protected function checkRet($ret) {
        return $this->result(($ret !== false) ? self::kRet_Success : self::kRet_Fail);
    }

}













