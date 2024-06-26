<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午2:30
 */
class ReviewDao extends BaseDao
{
    public $tagDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('TagDao');
        $this->tagDao = new TagDao();
    }

    private function getPublicFields()
    {
        return $this->mergeFields(array(
            KEY_REVIEW_ID,
            KEY_ORDER_ID,
            KEY_TITLE,
            KEY_CONTENT,
            KEY_CREATED,
            KEY_UPDATED,
            KEY_DISPLAYING,
            KEY_COVER_URL
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
        $reviews = $this->getReviews($field, $value, 0, 1);
        if (count($reviews) > 0) {
            return $reviews[0];
        } else {
            return null;
        }
    }

    function update($reviewId, $data)
    {
        $this->db->where(KEY_REVIEW_ID, $reviewId);
        return $this->db->update(TABLE_REVIEWS, $data);
    }

    private function getReviews($field, $value, $skip, $limit)
    {
        $fields = $this->getPublicFields();
        $sql = "SELECT $fields,
                 count(distinct(rewards.orderId)) as rewardCount,
                 count(distinct(review_visits.visitId)) as visitCount,
                 count(distinct(commentId)) as commentCount
                 FROM reviews
                 left JOIN rewards USING(orderId)
                 left join comments USING(reviewId)
                 left join review_visits USING(reviewId)
                 left join orders using (orderId)
                 WHERE $field=? group by reviewId ORDER BY reviews.created
                 DESC limit $limit offset $skip";
        $array[] = $value;
        $reviews = $this->db->query($sql, $array)->result();
        $this->mergeChildrenOfReviews($reviews);
        return $reviews;
    }

    function countReviews($displaying)
    {
        return $this->countRows(TABLE_REVIEWS, KEY_DISPLAYING, $displaying);
    }

    function getDisplayingReviews($displaying = 1, $skip, $limit)
    {
        return $this->getReviews(KEY_DISPLAYING, $displaying, $skip, $limit);
    }

    private function mergeChildrenOfReviews($reviews)
    {
        foreach ($reviews as $review) {
            $review->tags = $this->tagDao->getReviewTags($review->reviewId);
        }
    }

    function getListForReviewer($reviewerId, $skip, $limit)
    {
        return $this->getReviews(KEY_REVIEWER_ID, $reviewerId, $skip, $limit);
    }
}
