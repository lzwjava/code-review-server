<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/1/8
 * Time: 下午3:47
 */
class Visits extends BaseController
{
    public $reviewVisitDao;
    public $videoVisitDao;

    function __construct()
    {
        parent::__construct();
        $this->load->helper('cookie');
        $this->load->model('reviewVisitDao');
        $this->reviewVisitDao = new ReviewVisitDao();
        $this->load->model('videoVisitDao');
        $this->videoVisitDao = new VideoVisitDao();
    }

    private function generateVisitorId()
    {
        return getToken(32);
    }

    private function getVisistorId()
    {
        $vid = get_cookie(COOKIE_VID);
        if ($vid) {
            return $vid;
        } else {
            $vid = $this->generateVisitorId();
            set_cookie(COOKIE_VID, $vid, 3600 * 24 * 365 * 20);
            return $vid;
        }
    }

    public function visitReview_post($reviewId)
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_REFERRER), false)
        ) {
            return;
        }
        $referrer = $this->post(KEY_REFERRER);
        $user = $this->getSessionUser();
        $userId = null;
        if ($user) {
            $userId = $user->id;
        }
        $vid = $this->getVisistorId();
        $this->reviewVisitDao->addVisit($vid, $reviewId, $referrer, $userId);
        $this->succeed();
    }

    public function visitVideo_post($videoId)
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_REFERRER), false)) {
            return;
        }
        $referrer = $this->post(KEY_REFERRER);
        $user = $this->getSessionUser();
        $userId = null;
        if ($user) {
            $userId = $user->id;
        }
        $vid = $this->getVisistorId();
        $this->videoVisitDao->addVideoVisit($vid, $videoId, $referrer, $userId);
        $this->succeed();
    }
}
