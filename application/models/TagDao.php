<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/24
 * Time: 下午2:39
 */
class TagDao extends BaseDao
{
    function stringToColorCode($str)
    {
        $code = dechex(crc32($str));
        $code = substr($code, 0, 6);
        return $code;
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
        foreach ($tags as $tag) {
            $tag->color = $this->stringToColorCode($tag->tagName);
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
        $sql = "SELECT tags.* FROM users_tags JOIN tags USING (tagId) WHERE userId=?";
        $array[] = $userId;
        $tags = $this->db->query($sql, $array)->result();
        $this->mergeTagsColor($tags);
        return $tags;
    }
}
