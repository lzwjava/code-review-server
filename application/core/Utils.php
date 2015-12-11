<?php
/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/11/30
 * Time: 下午3:44
 */

if (!function_exists('dateWithMs')) {
    function dateWithMs()
    {
        list ($t1, $t2) = explode(' ', microtime());
        $date = new DateTime ();
        $date->setTimestamp($t2);

        return $date->format("Y-m-d H:i:s") . substr($t1, 1, 7);
    }
}

if (!function_exists('uuid')) {
    function uuid($len = null)
    {
        $uuid = md5(uniqid());
        if ($len == null) {
            return $uuid;
        } else {
            return substr($uuid, 0, $len);
        }
    }
}

if (!function_exists('setCookieForever')) {
    function setCookieForever($name, $value)
    {
        setcookie($name, $value, time() + 3600 * 24 * 165 * 20, "/");
    }
}

if (!function_exists('getToken')) {

    function getToken($length = 16)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[rand(0, $max)];
        }
        return $token;
    }
}

if (!function_exists('dbField')) {
    function dbField($table, $key)
    {
        return $table . '.' . $key;
    }
}

if (!function_exists('deleteCookie')) {
    function deleteCookie($name)
    {
        setcookie($name, "", time() - 10000, "/");
    }
}

if (!function_exists('logInfo')) {
    function logInfo($info) {
        if (getenv('CRDEBUG')) {
            error_log($info);
        } else {
            log_message('error', $info);
        }
    }
}
