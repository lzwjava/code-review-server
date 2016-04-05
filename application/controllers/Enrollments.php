<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 4/5/16
 * Time: 12:38 PM
 */
class Enrollments extends BaseController
{
    public $enrollmentDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(EnrollmentDao::class);
        $this->enrollmentDao = new EnrollmentDao();
    }

    function workshopList_get($workshopId)
    {
        $skip = $this->skip();
        $limit = $this->limit();
        $attendances = $this->enrollmentDao->getEnrollmentsByWorkshopId($workshopId, $skip, $limit);
        $this->succeed($attendances);
    }
}
