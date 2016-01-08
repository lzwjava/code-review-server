<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/1/8
 * Time: 上午2:56
 */
class ReviewVisitDao extends BaseDao
{

    function addVisit($visitorId, $reviewId, $referrer, $userId)
    {
        $data = array(
            KEY_VISITOR_ID => $visitorId,
            KEY_REVIEW_ID => $reviewId,
            KEY_REFERRER => $referrer
        );
        if ($userId) {
            $data[KEY_USER_ID] = $userId;
        }
        return $this->db->insert(TABLE_REVIEW_VISITS, $data);
    }
}
