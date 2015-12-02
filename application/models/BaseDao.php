<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/2
 * Time: 上午12:32
 */
class BaseDao extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->db->query("SET NAMES UTF8");
    }

    protected function mergeFields($fields)
    {
        $filedStr = '';
        $first = true;
        foreach ($fields as $field) {
            if ($first) {
                $first = false;
            } else {
                $filedStr .= ',';
            }
            $filedStr .= $field;
        }
        return $filedStr;
    }
}
