<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 4/4/16
 * Time: 10:09 PM
 */
class EnrollmentDao extends BaseDao
{
    function addEnrollment($userId, $workshopId, $chargeId)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_WORKSHOP_ID => $workshopId,
            KEY_CHARGE_ID => $chargeId
        );
        $this->db->insert(TABLE_ENROLLMENTS, $data);
        return $this->db->insert_id();
    }
}