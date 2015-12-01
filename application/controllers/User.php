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
        if ($smsCode == '5555') {
            // for test
            return false;
        }
        $return = $this->curlLeanCloud("verifySmsCode/" . $smsCode . "?mobilePhoneNumber=" . $mobilePhoneNumber, null);
        if ($return['status'] == 200) {
            return false;
        } else {
            responseJson($this, ERROR_SMS_WRONG, null, $return['result']);
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
        if ($status != 200) {
            $resultJson = json_decode($result);
            $result = $resultJson->error;
        }
        return array(
            "status" => $status,
            "result" => $result
        );
    }

    public function requestSmsCode()
    {
        if (checkIfParamsNotExist($this, $_POST, array("mobilePhoneNumber"))
        ) {
            return;
        }
        $mobilePhoneNumber = $_POST["mobilePhoneNumber"];
        $data = array(
            "mobilePhoneNumber" => $mobilePhoneNumber
        );
        $return = $this->curlLeanCloud('requestSmsCode', $data);
        if ($return['status'] = 200) {
            responseJson($this, REQ_OK, $return['result'], null);
        } else {
            responseJson($this, ERROR_SMS_WRONG, null, $return['result']);
        }
    }

    public function register()
    {
        if (checkIfParamsNotExist($this, $_POST, array('username', 'mobilePhoneNumber',
            'password', 'type', 'smsCode'))
        ) {
            return;
        }
        $mobilePhoneNumber = $_POST['mobilePhoneNumber'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $type = $_POST['type'];
        $smsCode = $_POST['smsCode'];
        if ($this->checkIfUsernameUsedAndReponse($username)) {
            return;
        } elseif ($this->userDao->checkIfMobilePhoneNumberUsed($mobilePhoneNumber)) {
            responseJson($this, ERROR_MOBILE_PHONE_NUMBER_TAKEN, null, "手机号已被占用");
        } else if ($this->checkSmsCodeWrong($mobilePhoneNumber, $smsCode)) {
            return;
        } else {
            $defaultAvatarUrl = "https://avatars2.githubusercontent.com/u/5022872?v=3&s=460";
            $this->userDao->insertUser($type, $username, $mobilePhoneNumber, $defaultAvatarUrl,
                $password);
            $this->loginOrRegisterSucceed($mobilePhoneNumber);
        }
    }

    private function checkIfUsernameUsedAndReponse($username)
    {
        if ($this->userDao->checkIfUsernameUsed($username)) {
            responseJson($this, ERROR_USERNAME_TAKEN, null, "用户名已存在");
            return true;
        } else {
            return false;
        }
    }

    public function delete()
    {
        if (checkIfParamsNotExist($this, $_POST, array('mobilePhoneNumber'))) {
            return;
        }
        $mobilePhoneNumber = $_POST['mobilePhoneNumber'];
        if ($this->userDao->deleteUser($mobilePhoneNumber)) {
            responseJson($this, REQ_OK);
        } else {
            responseJson($this, ERROR_USER_NOT_EXIST);
        }
    }

    public function test()
    {
        echo getenv('CRDEBUG');
    }

    public function login()
    {
        if (checkIfParamsNotExist($this, $_POST, array("mobilePhoneNumber", "password"))) {
            return;
        }
        $mobilePhoneNumber = $_POST["mobilePhoneNumber"];
        $password = $_POST["password"];
        if ($this->userDao->checkLogin($mobilePhoneNumber, $password) == false) {
            responseJson($this, ERROR_LOGIN_FAILED, null, "手机号码不存在或者密码错误");
        } else {
            $this->loginOrRegisterSucceed($mobilePhoneNumber);
        }
    }

    public function loginOrRegisterSucceed($mobilePhoneNumber)
    {
        $user = $this->userDao->findUserByMobilePhoneNumber($mobilePhoneNumber);
        $user = $this->userDao->updateSessionTokenIfNeeded($user);
        setCookieForever(KEY_COOKIE_TOKEN, $user->sessionToken);
        responseJson($this, REQ_OK, $user, null);
    }

    public function self()
    {
        $login_url = 'Location: /';
        if ($this->checkIfInSession()) {
            $user = $this->userDao->findUserBySessionToken($_COOKIE['crtoken']);
            responseJson($this, REQ_OK, $user, null);
        } else {
            header($login_url);
        }
    }

    public function logout()
    {
        session_unset(KEY_COOKIE_TOKEN);
        deleteCookie(KEY_COOKIE_TOKEN);
        responseJson($this, REQ_OK, null, "已安全退出");
    }

    private function requestToken()
    {
        if (isset($_COOKIE[KEY_COOKIE_TOKEN])) {
            $token = $_COOKIE['crtoken'];
        } else {
            $token = $this->input->get_request_header(KEY_SESSION_HEADER, TRUE);
        }
        return $token;
    }

    private function checkIfInSession()
    {
        $token = $this->requestToken();
        if ($token == null) {
            return false;
        } else {
            $user = $this->userDao->findUserBySessionToken($token);
            return $user != null;
        }
    }

    private function checkIfNotInSessionAndResponse()
    {
        if ($this->checkIfInSession()) {
            return false;
        } else {
            responseJson($this, ERROR_NOT_IN_SESSION, null, "未登录");
        }
    }

    public function update()
    {
        if (!isset($_POST['username']) && !isset($_POST['avatarUrl'])) {
            responseJson($this, ERROR_AT_LEAST_ONE_UPDATE, "请至少提供一个可以修改的信息");
            return;
        }
        if ($this->checkIfNotInSessionAndResponse()) {
            return;
        }
        $token = $this->requestToken();
        $user = $this->userDao->findUserBySessionToken($token);
        if (isset($_POST['username'])) {
            $username = $_POST['username'];
            if ($this->checkIfUsernameUsedAndReponse($username)) {
                return;
            } else {
                $this->userDao->updateUser($user, array(
                    "username" => $username
                ));
            }
        }
        if (isset($_POST['avatarUrl'])) {
            $avatarUrl = $_POST['avatarUrl'];
            $this->userDao->updateUser($user, array(
                "avatarUrl" => $avatarUrl
            ));
        }
        $user = $this->userDao->findUserBySessionToken($token);
        responseJson($this, REQ_OK, $user);
    }
}
