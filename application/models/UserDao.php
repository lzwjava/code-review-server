<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/11/30
 * Time: 下午2:13
 */
class UserDao extends BaseDao
{
    function checkIfUserUsed($field, $value)
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

        $data = array(
            KEY_ID => $this->genId(),
            KEY_USERNAME => $username,
            KEY_PASSWORD => md5($password),
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
            error_log('unknown type');
            $tableName = TABLE_LEARNERS;
        }
        return $tableName;
    }

    function checkLogin($mobilePhoneNumber, $password)
    {
        $sql = "SELECT * FROM users WHERE mobilePhoneNumber=? AND password=?";
        $array[] = $mobilePhoneNumber;
        $array[] = md5($password);
        return $this->db->query($sql, $array)->num_rows() == 1;
    }

    function findUser($filed, $value)
    {
        $user = $this->findRawUser($filed, $value);
        if ($user) {
            $this->cleanUser($user);
        }
        return $user;
    }

    function findPublicUser($field, $value) {
        $user = $this->findRawUser($field, $value);
        if ($user) {
            $this->cleanUser($user);
            unset($user->sessionToken);
            unset($user->mobilePhoneNumber);
            unset($user->created);
            unset($user->type);
        }
        return $user;
    }

    function findRawUser($filed, $value)
    {
        $sql = "SELECT * FROM users WHERE $filed=?";
        $array[] = $value;
        $user = $this->db->query($sql, $array)->row();
        return $user;
    }

    function findActualUser($user)
    {
        $tableName = $this->tableNameByType($user->type);
        $sql = "select * from $tableName where id=?";
        $array[] = $user->id;
        $user = $this->db->query($sql, $array)->row();
        if ($user) {
            $this->cleanUser($user);
        }
        return $user;
    }

    function findUserById($id)
    {
        return $this->findUser(KEY_ID, $id);
    }

    function findUserByMobilePhoneNumber($mobilePhoneNumber)
    {
        return $this->findUser(KEY_MOBILE_PHONE_NUMBER, $mobilePhoneNumber);
    }

    function findUserBySessionToken($sessionToken)
    {
        return $this->findUser(KEY_SESSION_TOKEN, $sessionToken);
    }

    function deleteUser($mobilePhoneNumber)
    {
        $user = $this->findUserByMobilePhoneNumber($mobilePhoneNumber);
        if ($user != null) {
            $tableName = $this->tableNameByType($user->type);
            $sql = "delete from $tableName where mobilePhoneNumber=?";
            $array[] = $mobilePhoneNumber;
            $this->db->query($sql, $array);
            return true;
        } else {
            return false;
        }
    }

    function updateSessionTokenIfNeeded($mobilePhoneNumber)
    {
        $user = $this->findRawUser(KEY_MOBILE_PHONE_NUMBER, $mobilePhoneNumber);
        $created = strtotime($user->sessionTokenCreated);
        $now = dateWithMs();
        $nowMillis = strtotime($now);
        $duration = $nowMillis - $created;
        if ($user->sessionToken == null || $user->sessionTokenCreated == null
            || $duration > 60 * 60 * 24 * 30
        ) {
            $tableName = $this->tableNameByType($user->type);
            $sql = "UPDATE $tableName SET sessionToken = ?, sessionTokenCreated = ? WHERE id = ?";
            $array[] = $this->genSessionToken();
            $array[] = $now;
            $array[] = $user->id;
            $this->db->query($sql, $array);
            $actualUser = $this->findUserById($user->id);
        } else {
            $actualUser = $user;
        }
        $this->cleanUser($actualUser);
        return $actualUser;
    }

    function updateUser($user, $info)
    {
        $tableName = $this->tableNameByType($user->type);
        foreach ($info as $key => $value) {
            $sql = "update $tableName set $key=? where id=?";
            $array[] = $value;
            $array[] = $user->id;
            $this->db->query($sql, $array);
        }
    }

    function cleanUser($user)
    {
        unset($user->sessionTokenCreated);
        unset($user->password);
    }
}
