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
        if ($this->checkIfParamsNotExist($_POST, array(KEY_MOBILE_PHONE_NUMBER))
        ) {
            return;
        }
        $mobilePhoneNumber = $_POST[KEY_MOBILE_PHONE_NUMBER];
        $data = array(
            KEY_MOBILE_PHONE_NUMBER => $mobilePhoneNumber
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
        if ($this->checkIfParamsNotExist($_POST, array(KEY_USERNAME, KEY_MOBILE_PHONE_NUMBER,
            KEY_PASSWORD, KEY_TYPE, KEY_SMS_CODE))
        ) {
            return;
        }
        $mobilePhoneNumber = $_POST[KEY_MOBILE_PHONE_NUMBER];
        $username = $_POST[KEY_USERNAME];
        $password = $_POST[KEY_PASSWORD];
        $type = $_POST[KEY_TYPE];
        $smsCode = $_POST[KEY_SMS_CODE];
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
        if ($this->checkIfParamsNotExist($_POST, array(KEY_MOBILE_PHONE_NUMBER))) {
            return;
        }
        $mobilePhoneNumber = $_POST[KEY_MOBILE_PHONE_NUMBER];
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
        if ($this->checkIfParamsNotExist($_POST, array(KEY_MOBILE_PHONE_NUMBER, KEY_PASSWORD))) {
            return;
        }
        $mobilePhoneNumber = $_POST[KEY_MOBILE_PHONE_NUMBER];
        $password = $_POST[KEY_PASSWORD];
        if ($this->userDao->checkLogin($mobilePhoneNumber, $password) == false) {
            $this->failure(ERROR_LOGIN_FAILED, "手机号码不存在或者密码错误");
        } else {
            $this->loginOrRegisterSucceed($mobilePhoneNumber);
        }
    }

    public function loginOrRegisterSucceed($mobilePhoneNumber)
    {
        $user = $this->userDao->updateSessionTokenIfNeeded($mobilePhoneNumber);
        setCookieForever(KEY_COOKIE_TOKEN, $user->sessionToken);
        $this->succeed($user);
    }

    public function self()
    {
        $login_url = 'Location: /';
        if ($this->checkIfInSession()) {
            $user = $this->userDao->findUserBySessionToken($_COOKIE[KEY_COOKIE_TOKEN]);
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

    private function checkIfNotAtLeastOneParam($request, $params)
    {
        foreach ($params as $param) {
            if (isset($request[$param])) {
                return false;
            }
        }
        $this->failure(ERROR_AT_LEAST_ONE_UPDATE, "请至少提供一个可以修改的信息");
        return true;
    }

    public function update()
    {
        if ($this->checkIfNotAtLeastOneParam($_POST, array(KEY_AVATAR_URL, KEY_USERNAME,
            KEY_INTRODUCTION, KEY_EXPERIENCE))
        ) {
            return;
        }
        if ($this->checkIfNotInSessionAndResponse()) {
            return;
        }
        $user = $this->getSessionUser();
        if (isset($_POST[KEY_USERNAME])) {
            $username = $_POST[KEY_USERNAME];
            if ($this->checkIfUsernameUsedAndReponse($username)) {
                return;
            } else {
                $this->userDao->updateUser($user, array(
                    KEY_USERNAME => $username
                ));
            }
        }
        if (isset($_POST[KEY_AVATAR_URL])) {
            $avatarUrl = $_POST[KEY_AVATAR_URL];
            $this->userDao->updateUser($user, array(
                KEY_AVATAR_URL => $avatarUrl
            ));
        }
        if (isset($_POST[KEY_INTRODUCTION])) {
            $intro = $_POST[KEY_INTRODUCTION];
            $this->userDao->updateUser($user, array(
                KEY_INTRODUCTION => $intro
            ));
        }
        if (isset($_POST[KEY_EXPERIENCE])) {
            $experience = $_POST[KEY_EXPERIENCE];
            if ($experience < 0 || $experience > 30) {
                $this->failure(ERROR_PARAMETER_ILLEGAL, '工作经验年限应在 0~30 年');
                return;
            }
            $this->userDao->updateUser($user, array(
                KEY_EXPERIENCE => $experience
            ));
        }
        $user = $this->userDao->findActualUser($user);
        $this->succeed($user);
    }

    public function view()
    {

    }
}
