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
        ), TABLE_REVIEWS);
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
        $review = $this->getOneFromTable(TABLE_REVIEWS, $field, $value, $this->getPublicFields());
        if ($review) {
            $this->mergeChildrenOfReviews(array($review));
        }
        return $review;
    }

    function update($reviewId, $data)
    {
        $this->db->where(KEY_REVIEW_ID, $reviewId);
        $this->db->update(TABLE_REVIEWS, $data);
    }

    function getList($displaying = 1, $skip, $limit)
    {
        $fields = $this->getPublicFields();
        $reviews = $this->getListFromTable(TABLE_REVIEWS, KEY_DISPLAYING, $displaying, $fields,
            'updated DESC', $skip, $limit);
        $this->mergeChildrenOfReviews($reviews);
        return $reviews;
    }

    private function mergeChildrenOfReviews($reviews)
    {
        foreach ($reviews as $review) {
            $review->tags = $this->tagDao->getReviewTags($review->reviewId);
        }
    }

    function getListForReviewer($reviewerId, $skip, $limit)
    {
        $fields = $this->getPublicFields();
        $sql = "select $fields from reviews join orders USING (orderId) " .
            "where reviewerId=? limit $limit offset $skip";
        $values[] = $reviewerId;
        $reviews = $this->db->query($sql, $values)->result();
        $this->mergeChildrenOfReviews($reviews);
        return $reviews;
    }
}
