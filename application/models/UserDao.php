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
        $sql = "SELECT * FROM users WHERE " . $field . " =?";
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

    function insertUser($username, $mobilePhoneNumber, $avatarUrl, $type, $password)
    {
        $data = array(
            'username' => $username,
            'password' => $password,
            'mobilePhoneNumber' => $mobilePhoneNumber,
            'avatarUrl' => $avatarUrl,
            'type' => $type
        );
        $this->db->trans_start();
        $this->db->insert('users', $data);
        $this->db->trans_complete();
    }

    function checkLogin($mobilePhoneNumber, $password)
    {
        $sql = "SELECT * FROM users WHERE mobilePhoneNumber=? AND password=?";
        $array[] = $mobilePhoneNumber;
        $array[] = $password;
        return $this->db->query($sql, $array)->num_rows() == 1;
    }

    function findUser($filed, $value)
    {
        $sql = "SELECT * FROM users WHERE " . $filed . "=?";
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

    function updateSessionTokenIfNeeded($user)
    {
        $created = strtotime($user->sessionTokenCreated);
        error_log("sessionTokenCreated " . $user->sessionTokenCreated);
        $now = dateWithMs();
        error_log("now " . $now);
        $nowMillis = strtotime($now);
        error_log("nowMillis " . $nowMillis);
        $duration = $nowMillis - $created;
        error_log("duration " . $duration);
        if ($user->sessionToken == null || $user->sessionTokenCreated == null
            || $duration > 60 * 60 * 24 * 30
        ) {
            $sql = "UPDATE users SET sessionToken = ?, sessionTokenCreated = ? WHERE id = ?";
            $array[] = uuid();
            $array[] = $now;
            $array[] = $user->id;
            $this->db->query($sql, $array);
            return $this->findUserById($user->id);
        } else {
            return $user;
        }
    }
}