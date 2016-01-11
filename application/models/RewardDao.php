<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午7:40
 */
class RewardDao extends BaseDao
{

    function getPublicFields()
    {
        return $this->mergeFields(array(
            KEY_REWARD_ID,
            KEY_CREATOR,
            KEY_CREATED,
            KEY_UPDATED,
            KEY_CHARGE_ID
        ));
    }

    function addReward($orderId, $creator, $chargeId)
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

    function countRewardsByReviewerId($reviewerId)
    {
        $sql = "SELECT count(*) AS cnt FROM rewards LEFT JOIN orders ON orders.orderId = rewards.orderId
                WHERE reviewerId=?";
        $array[] = $reviewerId;
        $result = $this->db->query($sql, $array)->row();
        return $result->cnt;
    }

    function getOne($field, $value)
    {
        return $this->getOneFromTable(TABLE_REWARDS, $field, $value, $this->getPublicFields());
    }

    function getOneByChargeId($chargeId)
    {
        return $this->getOne(KEY_CHARGE_ID, $chargeId);
    }

}
