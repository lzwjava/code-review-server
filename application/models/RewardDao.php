<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午7:40
 */
class RewardDao extends BaseDao
{
    function add($reviewId, $orderNo, $amount, $creator, $creatorIP)
    {
        $data = array(
            KEY_REVIEW_ID => $reviewId,
            KEY_ORDER_NO => $orderNo,
            KEY_AMOUNT => $amount,
            KEY_CREATOR => $creator,
            KEY_CREATOR_IP => $creatorIP
        );
        $this->db->insert(TABLE_REWARDS, $data);
        $insertId = $this->db->insert_id();
        return $insertId;
    }
}
