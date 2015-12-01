<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/11/30
 * Time: 下午2:13
 */
class UserDao extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->db->query("SET NAMES UTF8");
    }

    function checkIfUserUsed($field, $value)
    {
        $sql = "SELECT * FROM users WHERE $field =?";
        $array[] = $value;
        return $this->db->query($sql, $array)->num_rows() > 0;
    }

    function checkIfUsernameUsed($username)
    {
        return $this->checkIfUserUsed('username', $username);
    }

    function checkIfMobilePhoneNumberUsed($mobilePhoneNumber)
    {
        return $this->checkIfUserUsed('mobilePhoneNumber', $mobilePhoneNumber);
    }

    function insertUser($type, $username, $mobilePhoneNumber, $avatarUrl, $password)
    {

        $data = array(
            'id' => $this->genId(),
            'username' => $username,
            'password' => md5($password),
            'mobilePhoneNumber' => $mobilePhoneNumber,
            'avatarUrl' => $avatarUrl,
            'sessionToken' => $this->genSessionToken()
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
            $tableName = 'learners';
        } else if ($type == TYPE_REVIEWER) {
            $tableName = 'reviewers';
        } else {
            error_log('unknown type');
            $tableName = 'learners';
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
        $sql = "SELECT * FROM users WHERE $filed=?";
        $array[] = $value;
        $user = $this->db->query($sql, $array)->row();
        if ($user) {
            unset ($user->password);
        }
        return $user;
    }

    function findUserById($id)
    {
        return $this->findUser("id", $id);
    }

    function findUserByMobilePhoneNumber($mobilePhoneNumber)
    {
        return $this->findUser("mobilePhoneNumber", $mobilePhoneNumber);
    }

    function findUserBySessionToken($sessionToken)
    {
        return $this->findUser("sessionToken", $sessionToken);
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

    function updateSessionTokenIfNeeded($user)
    {
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
        unset($actualUser->sessionTokenCreated);
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
}
