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

    protected function prefixFields($fields, $prefix)
    {
        foreach ($fields as &$field) {
            $field = $prefix . $field;
        }
        return $fields;
    }

    protected function unsets(&$object, $fields)
    {
        foreach ($fields as $field) {
            unset($object->$field);
        }
    }

    protected function mergeFields($fields, $tableName = null, $alias = false)
    {
        if ($tableName) {
            foreach ($fields as &$field) {
                $aliasPart = $tableName . $field;
                $field = $tableName . '.' . $field;
                if ($alias) {
                    $field .= ' as ' . $aliasPart;
                }
            }
        }
        return implode($fields, ',');
    }

    protected function getOneFromTable($table, $field, $value, $fields = "*")
    {
        $sql = "SELECT $fields FROM $table WHERE $field=?";
        $array = $value;
        $result = $this->db->query($sql, $array)->row();
        return $result;
    }

    protected function getListFromTable($table, $field, $value, $fields = "*", $orderBy = null,
                                        $skip = 0, $limit = 100)
    {
        $order = '';
        if ($orderBy) {
            $order = ' order by ' . $orderBy . ' ';
        }
        $sql = "SELECT $fields FROM $table WHERE $field=? $order limit $limit offset $skip";
        $values[] = $value;
        $result = $this->db->query($sql, $values)->result();
        return $result;
    }

    protected function countRows($table, $field, $value)
    {
        $sql = "SELECT count(*) AS cnt FROM $table WHERE $field=?";
        $array[] = $value;
        $result = $this->db->query($sql, $array)->row();
        return $result->cnt;
    }


}
