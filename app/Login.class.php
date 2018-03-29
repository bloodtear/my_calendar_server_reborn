<?php

namespace my_calendar_server_reborn\app;
use my_calendar_server_reborn\database;

class login {

    private static $instance = null;
    public static function instance() {
        if (self::$instance == null) {
            self::$instance = new login();
        }
        return self::$instance;
    }

    private $mSalt = null;
    private $mRefer = null;
    private $mUser = null;

    private function login() {
        $salt = get_session("login.salt");
        if ($salt == null) {
            $salt = md5(uniqid());
            $_SESSION["login.salt"] = $salt;
        }
        $this->mSalt = $salt;
        $this->mRefer = get_session("login.refer");
    }

    public function salt() {
        return $this->mSalt;
    }

    public function user() {
        if ($this->mUser != null) {
            return $this->mUser;
        }
        $uid = get_session("user.id");
        if ($uid == null) {
            return null;
        }
        $this->mUser = User::create($uid);
        return $this->mUser;
    }

    public static function assert() {
        $refer = $_SERVER["REQUEST_URI"];
        \framework\Logging::d("Login", "refer from $refer");
        $_SESSION["login.refer"] = $refer;
        $uid = get_session("user.id");
        if ($uid == null) {
            go("index/login");
        }
    }

    public static function hasLogin() {
        $uid = get_session("user.id");
        return ($uid != null);
    }

    public static function assertPerm($pkey) {
        if (!self::hasPerm($pkey)) {
            include(FRAMEWORK_PATH . "/notfound.php");
            die("");
        }
    }

    public static function hasPerm($pkey) {
        $uid = get_session("user.id");
        if ($uid == null) {
            \\framework\Logging::d("Perm", "not login.");
            return false;
        }
        \framework\Logging::d("Perm", "check perm $pkey for user: $pkey");

        if ($uid == 1) {
            return true;
        }

        $rr = setting::instance()->load("KEY_ENABLE_PERMISSION_CHECK");
        if ($rr == 0) {
            \framework\Logging::d("Perm", "check perm = $rr");
            return true;
        }

        if (DEBUG) {
            $user = User::create($uid);
            return $user->hasPerm($pkey);
        }

        $perms = get_session("user.permissions");
        if ($perms == null) {
            return false;
        }
        $ret = in_array($pkey, $perms);
        \framework\Logging::d("Perm", "check perm result: $pkey");
        return $ret;
    }

    public function do_login($username, $cipher) {
        $user = User::oneByName($username);
        if ($user == null) {
            \framework\Logging::d("Login", "invalid username: [$username].");
            return array("ret" => "fail", "reason" => "invalid username: [$username].");
        }
        \framework\Logging::d("Login", "user = " . json_encode($user->packInfo()));
        if ($user->id() == 1) {
            if (!ALLOW_ROOT && setting::instance()->load("KEY_ENABLE_ROOT") == 0) {
                return array("ret" => "fail", "reason" => "不允许root用户登陆.");
            }
        }

        $password = $user->password();
        $c1 = md5($username. $this->salt(). $password);
        if ($c1 == $cipher) {
            $this->mUser = $user;
            $perms = array();
            foreach ($user->groups() as $group) {
                $perms = array_merge($perms, $group->permsarr());
            }

            $_SESSION["user.id"] = $user->id();
            $_SESSION["user.name"] = $user->username();
            $_SESSION["user.nick"] = $user->nickname();
            $_SESSION["user.permissions"] = $perms;

            // jump to homepage after login.
            $refer = $this->mRefer;
            if ($refer == null) {
                $refer = HOME_URL;
            }
            \framework\Logging::i("Login", "login success, jump to $refer");
            return array("ret" => "success", "refer" => $refer);
        }
        return array("ret" => "fail", "reason" => "invalid password.");
    }

    public function bye() {
        unset($_SESSION["user.id"]);
        unset($_SESSION["login.refer"]);
        unset($_SESSION["user.permissions"]);
    }
};

