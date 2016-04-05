<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 4/4/16
 * Time: 10:09 PM
 */
class WorkshopDao extends BaseDao
{
    function addWorkshop($name, $amount, $maxPeople)
    {
        $data = array(
            KEY_NAME => $name,
            KEY_AMOUNT => $amount,
            KEY_MAX_PEOPLE => $maxPeople
        );
        $this->db->insert(TABLE_WORKSHOPS, $data);
        return $this->db->insert_id();
    }


    function getWorkshop($workshopId, $user)
    {
        if ($user) {
            $userId = $user->id;
        } else {
            $userId = 'null';
        }
        $fields = $this->workshopPublicFields('w');
        $eFields = $this->enrollmentPublicFields('e', true);
        $sql = "SELECT $fields,$eFields,
                count(ee.enrollmentId) as enrollCount
                FROM workshops as w
                left join enrollments as e on e.userId=? and e.workshopId=w.workshopId
                left join enrollments as ee on ee.workshopId=w.workshopId
                where w.workshopId=?";
        $binds = array($userId, $workshopId);
        $workshop = $this->db->query($sql, $binds)->row();
        $this->handleWorkshops(array($workshop));
        return $workshop;
    }

    private function handleWorkshops($workshops)
    {
        foreach ($workshops as $workshop) {
            $prefixFields = $this->prefixFields($this->enrollmentFields(), 'e');
            $workshop->enrollment = extractFields($workshop, $prefixFields, 'e');
            $workshop->restCount = $workshop->maxPeople - $workshop->enrollCount;
        }
    }


}
