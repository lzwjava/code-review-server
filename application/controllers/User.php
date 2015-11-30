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
        if (getenv('CRDEBUG')) {
            return false;
        }
        $return = $this->curlLeanCloud("verifySmsCode/" . $smsCode . "?mobilePhoneNumber=" . $mobilePhoneNumber, null);
        if ($return['status'] == 200) {
            return false;
        } else {
            responseJson(SMS_ERROR, null, $return['result']);
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
            responseJson(REQUEST_SUCCEED, $return['result'], null);
        } else {
            responseJson(SMS_ERROR, null, $return['result']);
        }
    }

    public function register()
    {
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
            responseJson(USERNAME_TAKEN, null, "用户名已存在");
        } elseif ($this->userDao->checkIfMobilePhoneNumberUsed($mobilePhoneNumber)) {
            responseJson(MOBILE_PHONE_NUMBER_TAKEN, null, "手机号已被占用");
        } else if ($this->checkSmsCodeWrong($mobilePhoneNumber, $smsCode)) {
            return;
        } else {
            $defaultAvatarUrl = "https://avatars2.githubusercontent.com/u/5022872?v=3&s=460";
            $this->userDao->insertUser($type, $username, $mobilePhoneNumber, $defaultAvatarUrl,
                $password);
            $this->loginOrRegisterSucceed($mobilePhoneNumber);
        }
    }

    public function test() {
        echo getenv('CRDEBUG');
    }

    public function login()
    {
        if (checkIfParamsNotExist($_POST, array("mobilePhoneNumber", "password"))) {
            return;
        }
        $mobilePhoneNumber = $_POST["mobilePhoneNumber"];
        $password = $_POST["password"];
        if ($this->userDao->checkLogin($mobilePhoneNumber, $password) == false) {
            responseJson(LOGIN_FAILED, null, "手机号码不存在或者密码错误");
        } else {
            $this->loginOrRegisterSucceed($mobilePhoneNumber);
        }
    }

    public function loginOrRegisterSucceed($mobilePhoneNumber) {
        $user = $this->userDao->findUserByMobilePhoneNumber($mobilePhoneNumber);
        $user = $this->userDao->updateSessionTokenIfNeeded($user);
        setCookieForever('crtoken', $user->sessionToken);
        responseJson(REQUEST_SUCCEED, $user, null);
    }

    public function self()
    {
        $login_url = 'Location: /';
        if (!isset($_COOKIE['crtoken'])) {
            header($login_url);
        } else {
            $user = $this->userDao->findUserBySessionToken($_COOKIE['crtoken']);
            if ($user == null) {
                header($login_url);
            } else {
                responseJson(REQUEST_SUCCEED, $user, null);
            }
        }
    }

    public function logout()
    {
        session_unset('crtoken');
        deleteCookie('crtoken');
        responseJson(REQUEST_SUCCEED, null, "已安全退出");
    }
}
