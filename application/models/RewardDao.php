<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午7:40
 */
class RewardDao extends BaseDao
{
    function add($orderId, $creator, $chargeId)
    {
        $data = array(
            KEY_ORDER_ID => $orderId,
            KEY_CREATOR => $creator,
            KEY_CHARGE_ID => $chargeId
        );
        $this->db->insert(TABLE_REWARDS, $data);
        $insertId = $this->db->insert_id();
        return $insertId;
    }

    function countRewards($reviewerId)
    {
        $sql = "SELECT count(*) AS cnt FROM rewards LEFT JOIN orders ON orders.orderId = rewards.orderId
                LEFT JOIN charges ON charges.chargeId = rewards.chargeId WHERE paid=? AND reviewerId=?";
        $array[] = CHARGE_PAID;
        $array[] = $reviewerId;
        $result = $this->db->query($sql, $array)->row();
        return $result->cnt;
    }
}
