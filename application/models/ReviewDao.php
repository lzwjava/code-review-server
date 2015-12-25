<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午2:30
 */
class ReviewDao extends BaseDao
{

    private function getPublicFields()
    {
        return $this->mergeFields(array(
            KEY_REVIEW_ID,
            KEY_ORDER_ID,
            KEY_TITLE,
            KEY_CONTENT,
            KEY_CREATED,
            KEY_UPDATED
        ));
    }

    function add($orderId, $title, $content)
    {
        $data = array(
            KEY_ORDER_ID => $orderId,
            KEY_TITLE => $title,
            KEY_CONTENT => $content
        );
        $this->db->trans_start();
        $this->db->insert(TABLE_REVIEWS, $data);
        $insertId = $this->db->insert_id();
        $this->db->trans_complete();
        return $insertId;
    }

    function getOneByOrderId($orderId)
    {
        return $this->getOneFromReviews(KEY_ORDER_ID, $orderId);
    }

    function getOne($reviewId)
    {
        return $this->getOneFromReviews(KEY_REVIEW_ID, $reviewId);
    }

    function getOneFromReviews($field, $value)
    {
        return $this->getOneFromTable(TABLE_REVIEWS, $field, $value, $this->getPublicFields());
    }

    function update($reviewId, $data)
    {
        $this->db->where(KEY_REVIEW_ID, $reviewId);
        $this->db->update(TABLE_REVIEWS, $data);
    }
}
