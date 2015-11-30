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
}