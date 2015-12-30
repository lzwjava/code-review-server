<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/24
 * Time: 下午2:39
 */

require_once(APPPATH . 'helpers/ColorConvert.php');

class TagDao extends BaseDao
{

    function insertDefaultTags()
    {
        $tags = array("图像", "动画", "IM 通信", "音视频", "支付", "测试发布", "AutoLayout",
            "iOS 底层", "地图", "主流 SDK 使用", "UI");
    }

    function getList()
    {
        $sql = "SELECT * FROM tags";
        $tags = $this->db->query($sql)->result();
        $this->mergeTagsColor($tags);
        return $tags;
    }

    function mergeTagsColor($tags)
    {
        $convert = new ColorConvert();
        foreach ($tags as $tag) {
            $tag->color = $convert->stringToColorCode($tag->tagName);
        }
    }

    function addUserTag($userId, $tagId)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_TAG_ID => $tagId
        );
        return $this->db->insert(TABLE_USERS_TAGS, $data);
    }

    function removeUserTag($userId, $tagId)
    {
        $where = array(
            KEY_USER_ID => $userId,
            KEY_TAG_ID => $tagId
        );
        return $this->db->delete(TABLE_USERS_TAGS, $where);
    }

    function addOrderTag($orderId, $tagId)
    {
        $data = array(
            KEY_ORDER_ID => $orderId,
            KEY_TAG_ID => $tagId
        );
        return $this->db->insert(TABLE_ORDERS_TAGS, $data);
    }

    function removeOrderTag($orderId, $tagId)
    {
        $where = array(
            KEY_ORDER_ID => $orderId,
            KEY_TAG_ID => $tagId
        );
        return $this->db->delete(TABLE_ORDERS_TAGS, $where);
    }

    function getUserTags($userId)
    {
        return $this->getUsersOrOrdersTags(TABLE_USERS_TAGS, KEY_USER_ID, $userId);
    }

    private function getUsersOrOrdersTags($tableName, $fieldName, $fieldValue)
    {
        $sql = "SELECT tags.* FROM $tableName JOIN tags USING (tagId) WHERE $fieldName=?";
        $array[] = $fieldValue;
        $tags = $this->db->query($sql, $array)->result();
        $this->mergeTagsColor($tags);
        return $tags;
    }

    function getOrderTags($orderId)
    {
        return $this->getUsersOrOrdersTags(TABLE_ORDERS_TAGS, KEY_ORDER_ID, $orderId);
    }
}
