<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 4/4/16
 * Time: 10:09 PM
 */
class EnrollmentDao extends BaseDao
{
    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
    }

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

    function getEnrollmentsByUserId($userId, $skip, $limit)
    {
        return $this->getEnrollments(KEY_USER_ID, $userId, $skip, $limit);
    }

    function getEnrollmentsByWorkshopId($workshopId, $skip, $limit)
    {
        return $this->getEnrollments(KEY_WORKSHOP_ID, $workshopId, $skip, $limit);
    }

    private function getEnrollments($field, $value, $skip = 0, $limit = 100)
    {
        $fields = $this->enrollmentPublicFields('e');
        $workshopFields = $this->workshopPublicFields('w', true);
        $userFields = $this->userDao->publicFields('u', true);
        $sql = "select $fields,$workshopFields,$userFields
                from enrollments as e
                left join workshops as w USING(workshopId)
                left join users as u on u.id=e.userId
                where e.$field=?
                limit $limit offset $skip";
        $binds = array($value);
        $attendances = $this->db->query($sql, $binds)->result();
        $this->handleEnrollments($attendances);
        return $attendances;
    }

    private function handleEnrollments($enrollments)
    {
        foreach ($enrollments as $enrollment) {
            $ws = $this->prefixFields($this->workshopFields(), 'w');
            $enrollment->workshop = extractFields($enrollment, $ws, 'w');
            $us = $this->prefixFields($this->userDao->publicRawFields(), 'u');
            $enrollment->user = extractFields($enrollment, $us, 'u');
        }
    }
}