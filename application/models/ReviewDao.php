<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午2:30
 */
class ReviewDao extends BaseDao
{
    function add($orderId, $content)
    {
        $data = array(
            KEY_ORDER_ID => $orderId,
            KEY_CONTENT => $content
        );
        $this->db->trans_start();
        $this->db->insert(TABLE_REVIEWS, $data);
        $insertId = $this->db->insert_id();
        $this->db->trans_complete();
        return $insertId;
    }

    function getOne($reviewId)
    {
        $sql = "SELECT * FROM reviews WHERE reviewId=?";
        $array[] = $reviewId;
        return $this->db->query($sql, $array)->row();
    }

    function updateContent($reviewId, $content)
    {
        $this->update($reviewId, array(
            KEY_CONTENT => $content
        ));
    }

    function update($reviewId, $data)
    {
        $this->db->where(KEY_REVIEW_ID, $reviewId);
        $this->db->update(TABLE_REVIEWS, $data);
    }
}
