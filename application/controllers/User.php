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
    public $leancloud;
    public $tagDao;

    function __construct()
    {
        parent::__construct();
        $this->load->library('LeanCloud');
        $this->load->model('tagDao');
        $this->tagDao = new TagDao();
    }

    private function checkIfSmsCodeWrong($mobilePhoneNumber, $smsCode)
    {
        if ($smsCode == '5555' && isLocalDebug()) {
            // for test
            return false;
        }
        $return = $this->leancloud->curlLeanCloud("verifySmsCode/" . $smsCode . "?mobilePhoneNumber=" .
            $mobilePhoneNumber,
            null);
        if ($return['status'] == 200) {
            return false;
        } else {
            $this->failure(ERROR_SMS_WRONG, $return['result']);
            return true;
        }
    }

    private function sendSmsCodeAndResponse($phone, $op = null)
    {
        $data = array(
            KEY_MOBILE_PHONE_NUMBER => $phone
        );
        if ($op) {
            $data['op'] = $op;
        }
        $return = $this->leancloud->curlLeanCloud('requestSmsCode', $data);
        if ($return['status'] == 200) {
            $this->succeed($return['result']);
        } else {
            $this->failure(ERROR_SMS_WRONG, $return['result']);
        }
    }

    public function requestSmsCode_post()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_MOBILE_PHONE_NUMBER))
        ) {
            return;
        }
        $mobilePhoneNumber = $_POST[KEY_MOBILE_PHONE_NUMBER];
        $this->sendSmsCodeAndResponse($mobilePhoneNumber, '注册');
    }

    private function checkIfWrongPasswordFormat($password)
    {
        if (strlen($password) != 32) {
            $this->failure(ERROR_PASSWORD_FORMAT, "密码未加密,不符合规范");
            return true;
        }
        return false;
    }

    public function register_post()
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
        if ($this->checkIfUsernameUsedAndResponse($username)) {
            return;
        } elseif ($this->userDao->checkIfMobilePhoneNumberUsed($mobilePhoneNumber)) {
            $this->failure(ERROR_MOBILE_PHONE_NUMBER_TAKEN, "手机号已被占用");
        } else if ($this->checkIfSmsCodeWrong($mobilePhoneNumber, $smsCode)) {
            return;
        } else if ($this->checkIfWrongPasswordFormat($password)) {
            return;
        } else if ($this->checkIfNotInArray($type, $this->getTypeArray())) {
            return;
        } else {
            $defaultAvatarUrl = "http://7xotd0.com1.z0.glb.clouddn.com/defaultAvatar.png";
            $this->userDao->insertUser($type, $username, $mobilePhoneNumber, $defaultAvatarUrl,
                $password);
            $this->loginOrRegisterSucceed($mobilePhoneNumber);
        }
    }

    private function checkIfUsernameUsedAndResponse($username)
    {
        if ($this->userDao->checkIfUsernameUsed($username)) {
            $this->failure(ERROR_USERNAME_TAKEN, "用户名已存在");
            return true;
        } else {
            return false;
        }
    }

    public function login_post()
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

    private function getTypeArray()
    {
        return array(TYPE_REVIEWER, TYPE_LEARNER);
    }

    public function loginOrRegisterSucceed($mobilePhoneNumber, $resetPassword = false)
    {
        $user = $this->userDao->updateSessionTokenIfNeeded($mobilePhoneNumber, $resetPassword);
        setCookieForever(KEY_COOKIE_TOKEN, $user->sessionToken);
        $this->succeed($user);
    }

    public function self_get()
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

    public function logout_get()
    {
        session_unset(KEY_COOKIE_TOKEN);
        deleteCookie(KEY_COOKIE_TOKEN);
        $this->succeed();
    }

    public function update_patch()
    {
        $keys = array(KEY_AVATAR_URL, KEY_USERNAME,
            KEY_INTRODUCTION, KEY_EXPERIENCE, KEY_GITHUB_USERNAME, KEY_JOB_TITLE, KEY_COMPANY,
            KEY_MAX_ORDERS);
        if ($this->checkIfNotAtLeastOneParam($this->patch(), $keys)
        ) {
            return;
        }
        $data = $this->patchParams($keys);
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if (isset($data[KEY_USERNAME])) {
            $username = $data[KEY_USERNAME];
            if ($username != $user->username) {
                if ($this->checkIfUsernameUsedAndResponse($username)) {
                    return;
                }
            }
        }
        if (isset($data[KEY_EXPERIENCE])) {
            $experience = $data[KEY_EXPERIENCE];
            if ($experience < 0 || $experience > 30) {
                $this->failure(ERROR_PARAMETER_ILLEGAL, '工作经验年限应在 0~30 年');
                return;
            }
        }
        $user = $this->userDao->updateUser($user, $data);
        $this->succeed($user);
    }

    public function addTag_post()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_TAG_ID))) {
            return;
        }
        $tagId = $_POST[KEY_TAG_ID];
        $this->addOrRemoveTag(true, $tagId);
    }

    public function removeTag_delete($tagId)
    {
        $this->addOrRemoveTag(false, $tagId);
    }

    private function addOrRemoveTag($add, $tagId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($add) {
            $this->tagDao->addUserTag($user->id, $tagId);
        } else {
            $this->tagDao->removeUserTag($user->id, $tagId);
        }
        $this->succeed($this->tagDao->getUserTags($user->id));
    }

    protected function checkIfPhoneNotExist($phone)
    {
        $user = $this->userDao->findUserByMobilePhoneNumber($phone);
        if (!$user) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, '该手机号码未注册');
            return true;
        } else {
            return false;
        }
    }

    function requestResetPassword_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_MOBILE_PHONE_NUMBER))) {
            return;
        }
        $phone = $this->post(KEY_MOBILE_PHONE_NUMBER);
        if ($this->checkIfPhoneNotExist($phone)) {
            return;
        }
        $this->sendSmsCodeAndResponse($phone, '重置密码');
    }

    function resetPassword_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_MOBILE_PHONE_NUMBER, KEY_PASSWORD, KEY_SMS_CODE))) {
            return;
        }
        $phone = $this->post(KEY_MOBILE_PHONE_NUMBER);
        $password = $this->post(KEY_PASSWORD);
        $smsCode = $this->post(KEY_SMS_CODE);
        if ($this->checkIfPhoneNotExist($phone)) {
            return;
        }
        if ($this->checkIfWrongPasswordFormat($password)) {
            return;
        }
        if ($this->checkIfSmsCodeWrong($phone, $smsCode)) {
            return;
        }
        $newUser = $this->userDao->updatePassword($phone, $password);
        if ($this->checkIfSQLResWrong($newUser)) {
            return;
        }
        $this->loginOrRegisterSucceed($newUser->mobilePhoneNumber, true);
    }

}
