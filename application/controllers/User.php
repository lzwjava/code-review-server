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
            logInfo($encoded);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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

    private function checkIfWrongPasswordFormat($password)
    {
        if (strlen($password) != 32) {
            $this->failure(ERROR_PASSWORD_FORMAT, "密码未加密,不符合规范");
            return true;
        }
        return false;
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
        } else if ($this->checkIfWrongPasswordFormat($password)) {
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

    public function login()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_MOBILE_PHONE_NUMBER, KEY_PASSWORD))) {
            return;
        }
        $mobilePhoneNumber = $_POST[KEY_MOBILE_PHONE_NUMBER];
        $password = $_POST[KEY_PASSWORD];
        if ($this->checkIfWrongPasswordFormat($password)) {
            return;
        } else if ($this->userDao->checkLogin($mobilePhoneNumber, $password) == false) {
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
        $user = $this->getSessionUser();
        if ($user == null) {
            // $login_url = 'Location: /';
            // header($login_url);
            $this->failure(ERROR_NOT_IN_SESSION, "当前没有用户登录");
        } else {
            $this->succeed($user);
        }
    }

    public function logout()
    {
        session_unset(KEY_COOKIE_TOKEN);
        deleteCookie(KEY_COOKIE_TOKEN);
        $this->succeed();
    }

    public function update()
    {
        if ($this->checkIfNotAtLeastOneParam($_POST, array(KEY_AVATAR_URL, KEY_USERNAME,
            KEY_INTRODUCTION, KEY_EXPERIENCE, KEY_GITHUB_USERNAME, KEY_JOB_TITLE, KEY_COMPANY,
            KEY_MAX_ORDERS))
        ) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $data = array();
        if (isset($_POST[KEY_USERNAME])) {
            $username = $_POST[KEY_USERNAME];
            if ($username != $user->username) {
                if ($this->checkIfUsernameUsedAndReponse($username)) {
                    return;
                }
            }
            $data[KEY_USERNAME] = $username;
        }
        if (isset($_POST[KEY_AVATAR_URL])) {
            $data[KEY_AVATAR_URL] = $_POST[KEY_AVATAR_URL];
        }
        if (isset($_POST[KEY_INTRODUCTION])) {
            $data[KEY_INTRODUCTION] = $_POST[KEY_INTRODUCTION];
        }
        if (isset($_POST[KEY_EXPERIENCE])) {
            $experience = $_POST[KEY_EXPERIENCE];
            if ($experience < 0 || $experience > 30) {
                $this->failure(ERROR_PARAMETER_ILLEGAL, '工作经验年限应在 0~30 年');
                return;
            }
            $data[KEY_EXPERIENCE] = $experience;
        }
        if (isset($_POST[KEY_GITHUB_USERNAME])) {
            $data[KEY_GITHUB_USERNAME] = $_POST[KEY_GITHUB_USERNAME];
        }
        if (isset($_POST[KEY_JOB_TITLE])) {
            $data[KEY_JOB_TITLE] = $_POST[KEY_JOB_TITLE];
        }
        if (isset($_POST[KEY_COMPANY])) {
            $data[KEY_COMPANY] = $_POST[KEY_COMPANY];
        }
        if (isset($_POST[KEY_MAX_ORDERS])) {
            $data[KEY_MAX_ORDERS] = $_POST[KEY_MAX_ORDERS];
        }
        $user = $this->userDao->updateUser($user, $data);
        $this->succeed($user);
    }

    public function tag()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_OP, KEY_TAG_ID))) {
            return;
        }
        $op = $_POST[KEY_OP];
        $tagId = $_POST[KEY_TAG_ID];
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($op != KEY_OP_ADD && $op != KEY_OP_REMOVE) {
            $this->failure(ERROR_PARAMETER_ILLEGAL, "无效的操作");
        } else {
            if ($op == KEY_OP_ADD) {
                $this->tagDao->addUserTag($user->id, $tagId);
            } else {
                $this->tagDao->removeUserTag($user->id, $tagId);
            }
            $this->succeed($this->tagDao->getUserTags($user->id));
        }
    }
}
