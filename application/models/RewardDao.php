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

    function countRewards($reviewerId)
    {
        $sql = "SELECT count(*) AS cnt FROM rewards LEFT JOIN reviews ON reviews.reviewId = rewards.reviewId
                LEFT JOIN orders ON orders.orderId = reviews.orderId WHERE paid=? AND reviewerId=?";
        $array[] = REWARD_UNPAID;
        $array[] = $reviewerId;
        $result = $this->db->query($sql, $array)->row();
        return $result->cnt;
    }

    function updateRewardToPaid($orderNo)
    {
        $sql = "UPDATE rewards SET paid=1 WHERE orderNo=?";
        $array[] = $orderNo;
        return $this->db->query($sql, $array);
    }

    function getOneByOrderNo($orderNo)
    {
        $sql = "select * from rewards where orderNo=?";
        $array[] = $orderNo;
        return $this->db->query($sql, $array)->row();
    }
}
