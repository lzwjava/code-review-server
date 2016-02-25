<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/11/30
 * Time: 下午2:13
 */
class UserDao extends BaseDao
{
    public $tagDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('TagDao');
        $this->tagDao = new TagDao();
    }

    private function checkIfUserUsed($field, $value)
    {
        $sql = "SELECT * FROM users WHERE $field =?";
        $array[] = $value;
        return $this->db->query($sql, $array)->num_rows() > 0;
    }

    function checkIfUsernameUsed($username)
    {
        return $this->checkIfUserUsed(KEY_USERNAME, $username);
    }

    function checkIfMobilePhoneNumberUsed($mobilePhoneNumber)
    {
        return $this->checkIfUserUsed(KEY_MOBILE_PHONE_NUMBER, $mobilePhoneNumber);
    }

    function insertUser($type, $username, $mobilePhoneNumber, $avatarUrl, $password)
    {
        $id = $this->genId();
        $data = array(
            KEY_ID => $id,
            KEY_USERNAME => $username,
            KEY_PASSWORD => sha1($password),
            KEY_MOBILE_PHONE_NUMBER => $mobilePhoneNumber,
            KEY_AVATAR_URL => $avatarUrl,
            KEY_SESSION_TOKEN => $this->genSessionToken()
        );
        $this->db->trans_start();
        $tableName = $this->tableNameByType($type);
        $this->db->insert($tableName, $data);
        $this->db->trans_complete();
    }

    private function genId()
    {
        return getToken(16);
    }

    private function genSessionToken()
    {
        return getToken(32);
    }

    private function tableNameByType($type)
    {
        if ($type == TYPE_LEARNER) {
            $tableName = TABLE_LEARNERS;
        } else if ($type == TYPE_REVIEWER) {
            $tableName = TABLE_REVIEWERS;
        } else {
            logInfo('unknown type');
            $tableName = TABLE_LEARNERS;
        }
        return $tableName;
    }

    function checkLogin($mobilePhoneNumber, $password)
    {
        $sql = "SELECT * FROM users WHERE mobilePhoneNumber=? AND password=?";
        $array[] = $mobilePhoneNumber;
        $array[] = sha1($password);
        return $this->db->query($sql, $array)->num_rows() == 1;
    }

    private function findUser($filed, $value, $cleanFields = true)
    {
        $user = $this->findTypeAndActualUser($filed, $value);
        if ($user) {
            $this->mergeTags($user);
            if ($cleanFields) {
                $this->cleanUserFieldsForAll($user);
            }
        }
        return $user;
    }

    private function getPublicFields()
    {
        return $this->mergeFields(array(KEY_ID, KEY_AVATAR_URL, KEY_USERNAME, KEY_TYPE));
    }

    private function getSessionUserFields()
    {
        return $this->mergeFields(array(
            KEY_ID,
            KEY_AVATAR_URL,
            KEY_USERNAME,
            KEY_TYPE,
            KEY_MOBILE_PHONE_NUMBER,
            KEY_SESSION_TOKEN,
            KEY_CREATED,
            KEY_UPDATED,
            KEY_INTRODUCTION,
            KEY_COMPANY,
            KEY_JOB_TITLE,
            KEY_GITHUB_USERNAME
        ));
    }

    function findPublicUser($field, $value)
    {
        $fields = $this->getPublicFields();
        return $this->getOneFromTable(TABLE_USERS, $field, $value, $fields);
    }

    function findPublicUserById($id)
    {
        return $this->findPublicUser(KEY_ID, $id);
    }

    private function findActualUser($type, $field, $value)
    {
        $tableName = $this->tableNameByType($type);
        $user = $this->getOneFromTable($tableName, $field, $value);
        return $user;
    }

    private function findTypeAndActualUser($field, $value)
    {
        $fields = $this->mergeFields(array(KEY_TYPE));
        $user = $this->getOneFromTable(TABLE_USERS, $field, $value, $fields);
        if ($user) {
            $type = $user->type;
            return $this->findActualUser($type, $field, $value);
        } else {
            return $user;
        }
    }

    // 还用在 ReviewerDao.php
    function mergeTags($user)
    {
        $user->tags = $this->tagDao->getUserTags($user->id);
    }

    function findUserBySessionToken($sessionToken)
    {
        return $this->findUser(KEY_SESSION_TOKEN, $sessionToken);
    }

    function findUserById($id)
    {
        return $this->findUser(KEY_ID, $id);
    }

    private function updateSessionToken($user)
    {
        $token = $this->genSessionToken();
        $result = $this->updateUser($user, array(
            KEY_SESSION_TOKEN => $token,
            KEY_SESSION_TOKEN_CREATED => dateWithMs()
        ));
        if ($result) {
            $user->sessionToken = $token;
        }
    }

    function updateSessionTokenIfNeeded($mobilePhoneNumber)
    {
        $user = $this->findUser(KEY_MOBILE_PHONE_NUMBER, $mobilePhoneNumber, false);
        $created = strtotime($user->sessionTokenCreated);
        $now = dateWithMs();
        $nowMillis = strtotime($now);
        $duration = $nowMillis - $created;
        if ($user->sessionToken == null || $user->sessionTokenCreated == null
            || $duration > 60 * 60 * 24 * 30
        ) {
            $this->updateSessionToken($user);
        }
        $this->cleanUserFieldsForAll($user);
        return $user;
    }

    function updateUser($user, $data)
    {
        $tableName = $this->tableNameByType($user->type);
        $this->db->where(KEY_ID, $user->id);
        $result = $this->db->update($tableName, $data);
        if ($result) {
            return $this->findUser(KEY_ID, $user->id);
        }
    }

    private function cleanUserFieldsForAll($user)
    {
        if ($user) {
            unset($user->sessionTokenCreated);
            unset($user->password);
        }
    }

    private function cleanUserFieldsForPrivacy($user)
    {
        if ($user) {
            unset($user->sessionToken);
            unset($user->mobilePhoneNumber);
            unset($user->created);
            unset($user->type);
        }
    }

    function adminUser()
    {
        $systemName = '系统管理员';
        $user = $this->findUser(KEY_USERNAME, $systemName);
        if ($user) {
            return $user;
        } else {
            $this->insertUser(TYPE_LEARNER, $systemName, '13800000000',
                'http://7xotd0.com1.z0.glb.clouddn.com/Icon-76%402x.png', md5('c8dYR8o='));
            $user = $this->findUser(KEY_USERNAME, $systemName);
        }
        return $user;
    }

}
