<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/24
 * Time: 下午2:39
 */


use \Colors\RandomColor;

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

    private function randomColor()
    {
        $colors = array("#00BCEE", "#EF706B", "#33C96F", "#AC72D9", "#F01490", "#FAAB34");
        $index = rand(0, count($colors) - 1);
        return $colors[$index];
    }

    function mergeTagsColor($tags)
    {
        $convert = new ColorConvert();
        foreach ($tags as $tag) {
            $tag->color = $this->randomColor();
//            $color = RandomColor::one();
//            $tag->color = substr($color, 1);
            // $tag->color = $convert->stringToColorCode($tag->tagName);
        }
    }

    private function addTag($tagsTable, $fieldName, $id, $tagId)
    {
        $data = array(
            $fieldName => $id,
            KEY_TAG_ID => $tagId
        );
        return $this->db->insert($tagsTable, $data);
    }

    private function removeTag($tagsTable, $fieldName, $id, $tagId)
    {
        $where = array(
            $fieldName => $id,
            KEY_TAG_ID => $tagId
        );
        return $this->db->delete($tagsTable, $where);
    }

    function addUserTag($userId, $tagId)
    {
        return $this->addTag(TABLE_USERS_TAGS, KEY_USER_ID, $userId, $tagId);
    }

    function removeUserTag($userId, $tagId)
    {
        return $this->removeTag(TABLE_USERS_TAGS, KEY_USER_ID, $userId, $tagId);
    }

    function addReviewTag($reviewId, $tagId)
    {
        return $this->addTag(TABLE_REVIEWS_TAGS, KEY_REVIEW_ID, $reviewId, $tagId);
    }

    function removeReviewTag($reviewId, $tagId)
    {
        return $this->removeTag(TABLE_REVIEWS_TAGS, KEY_REVIEW_ID, $reviewId, $tagId);
    }

    function getUserTags($userId)
    {
        return $this->getUsersOrReviewsTags(TABLE_USERS_TAGS, KEY_USER_ID, $userId);
    }

    private function getUsersOrReviewsTags($tableName, $fieldName, $fieldValue)
    {
        $sql = "SELECT tags.* FROM $tableName JOIN tags USING (tagId) WHERE $fieldName=?";
        $array[] = $fieldValue;
        $tags = $this->db->query($sql, $array)->result();
        $this->mergeTagsColor($tags);
        return $tags;
    }

    function getReviewTags($reviewId)
    {
        return $this->getUsersOrReviewsTags(TABLE_REVIEWS_TAGS, KEY_REVIEW_ID, $reviewId);
    }
}
