<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/11/30
 * Time: 下午2:37
 */

if (!defined('BASEPATH'))
    exit ('No direct script access allowed');

class User extends BaseController
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
            $this->failure(ERROR_SMS_WRONG, $return['result']);
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
        if ($this->checkIfParamsNotExist($_POST, array("mobilePhoneNumber"))
        ) {
            return;
        }
        $mobilePhoneNumber = $_POST["mobilePhoneNumber"];
        $data = array(
            "mobilePhoneNumber" => $mobilePhoneNumber
        );
        $return = $this->curlLeanCloud('requestSmsCode', $data);
        if ($return['status'] = 200) {
            $this->succeed($return['result']);
        } else {
            $this->failure(ERROR_SMS_WRONG, $return['result']);
        }
    }

    public function register()
    {
        if ($this->checkIfParamsNotExist($_POST, array('username', 'mobilePhoneNumber',
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
            $this->failure(ERROR_MOBILE_PHONE_NUMBER_TAKEN, "手机号已被占用");
        } else if ($this->checkSmsCodeWrong($mobilePhoneNumber, $smsCode)) {
            return;
        } else {
            $defaultAvatarUrl = "http://7xotd0.com1.z0.glb.clouddn.com/android.png";
            $this->userDao->insertUser($type, $username, $mobilePhoneNumber, $defaultAvatarUrl,
                $password);
            $this->loginOrRegisterSucceed($mobilePhoneNumber);
        }
    }

    private function checkIfUsernameUsedAndReponse($username)
    {
        if ($this->userDao->checkIfUsernameUsed($username)) {
            $this->failure(ERROR_USERNAME_TAKEN, "用户名已存在");
            return true;
        } else {
            return false;
        }
    }

    public function delete()
    {
        if ($this->checkIfParamsNotExist($_POST, array('mobilePhoneNumber'))) {
            return;
        }
        $mobilePhoneNumber = $_POST['mobilePhoneNumber'];
        if ($this->userDao->deleteUser($mobilePhoneNumber)) {
            $this->succeed();
        } else {
            $this->failure(ERROR_USER_NOT_EXIST, "user not exists");
        }
    }

    public function test()
    {
        echo getenv('CRDEBUG');
    }

    public function login()
    {
        if ($this->checkIfParamsNotExist($_POST, array("mobilePhoneNumber", "password"))) {
            return;
        }
        $mobilePhoneNumber = $_POST["mobilePhoneNumber"];
        $password = $_POST["password"];
        if ($this->userDao->checkLogin($mobilePhoneNumber, $password) == false) {
            $this->failure(ERROR_LOGIN_FAILED, "手机号码不存在或者密码错误");
        } else {
            $this->loginOrRegisterSucceed($mobilePhoneNumber);
        }
    }

    public function loginOrRegisterSucceed($mobilePhoneNumber)
    {
        $user = $this->userDao->findUserByMobilePhoneNumber($mobilePhoneNumber);
        $user = $this->userDao->updateSessionTokenIfNeeded($user);
        setCookieForever(KEY_COOKIE_TOKEN, $user->sessionToken);
        $this->succeed($user);
    }

    public function self()
    {
        $login_url = 'Location: /';
        if ($this->checkIfInSession()) {
            $user = $this->userDao->findUserBySessionToken($_COOKIE['crtoken']);
            $this->succeed($user);
        } else {
            header($login_url);
        }
    }

    public function logout()
    {
        session_unset(KEY_COOKIE_TOKEN);
        deleteCookie(KEY_COOKIE_TOKEN);
        $this->succeed();
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
            $this->failure(ERROR_NOT_IN_SESSION, "未登录");
            return true;
        }
    }

    public function update()
    {
        if (!isset($_POST['username']) && !isset($_POST['avatarUrl'])
            && !isset($_POST[KEY_INTRODUCTION])
        ) {
            $this->failure(ERROR_AT_LEAST_ONE_UPDATE, "请至少提供一个可以修改的信息");
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
        if (isset($_POST[KEY_INTRODUCTION])) {
            $intro = $_POST[KEY_INTRODUCTION];
            $this->userDao->updateUser($user, array(
                KEY_INTRODUCTION => $intro
            ));
        }
        $user = $this->userDao->findActualUser($user);
        $this->succeed($user);
    }
}
