<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/11/30
 * Time: 下午2:37
 */

if (!defined('BASEPATH'))
    exit ('No direct script access allowed');

class User extends CI_Controller
{

    private function checkSmsCodeWrong($mobilePhoneNumber, $smsCode)
    {
        $return = $this->curlLeanCloud("verifySmsCode/" . $smsCode . "?mobilePhoneNumber=" . $mobilePhoneNumber, null);
        if ($return['status'] == 200) {
            return false;
        } else {
            responseJson(1, null, $return['result']);
            return true;
        }
    }

    private function curlLeanCloud($path, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.leancloud.cn/1.1/" . $path);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "X-LC-Id: gkXnvKfCSt79neUM2mERmEq8",
            "X-LC-Key: hVj4ar7LOc6iauH0bNAJJQKN",
            "Content-Type: application/json"
        ));
        if ($data != null) {
            $encoded = json_encode($data);
            error_log($encoded);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        error_log("result: " . $result);
        error_log("status: " . $status);
        curl_close($ch);
        return array(
            "status" => $status,
            "result" => $result
        );
    }

    public function requestSmsCode()
    {
        header("Content-type:application/json;charset=utf-8");
        if (checkIfParamsNotExist($_POST, array("mobilePhoneNumber"))
        ) {
            return;
        }
        $mobilePhoneNumber = $_POST["mobilePhoneNumber"];
        $data = array(
            "mobilePhoneNumber" => $mobilePhoneNumber
        );
        $return = $this->curlLeanCloud('requestSmsCode', $data);
        if ($return['status'] = 200) {
            responseJson(0, $return['result'], null);
        } else {
            responseJson(1, $return['result'], null);
        }
    }

    public function register()
    {
        header("Content-type:text/html;charset=utf-8");
        if (checkIfParamsNotExist($_POST, array('username', 'mobilePhoneNumber',
            'password', 'type', 'smsCode'))
        ) {
            return;
        }
        $mobilePhoneNumber = $_POST['mobilePhoneNumber'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $type = $_POST['type'];
        $smsCode = $_POST['smsCode'];
        if ($this->userDao->checkIfUsernameUsed($username)) {
            responseJson(1, null, "用户名已存在");
            return;
        } elseif ($this->userDao->checkIfMobilePhoneNumberUsed($mobilePhoneNumber)) {
            responseJson(2, null, "手机号已被占用");
        } else if ($this->checkSmsCodeWrong($mobilePhoneNumber, $smsCode)) {
            return;
        } else {
            $defaultAvatarUrl = "https://avatars2.githubusercontent.com/u/5022872?v=3&s=460";
            $passwordMd5 = md5($password);
            $this->userDao->insertUser($username, $mobilePhoneNumber, $defaultAvatarUrl, $type,
                $passwordMd5);
        }
    }

}