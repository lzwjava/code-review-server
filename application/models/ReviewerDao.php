<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/2
 * Time: 上午12:31
 */
class ReviewerDao extends BaseDao
{
    public $orderDao;
    public $rewardDao;
    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('OrderDao');
        $this->orderDao = new OrderDao();
        $this->load->model('RewardDao');
        $this->rewardDao = new RewardDao();
        $this->load->model('UserDao');
        $this->userDao = new UserDao();
    }

    public function setReviewerValid($id)
    {
        $sql = "UPDATE reviewers SET valid=1 WHERE id=?";
        $array[] = $id;
        return $this->db->query($sql, $array);
    }

    private function publicFields()
    {
        return $this->mergeFields(array(KEY_ID, KEY_USERNAME,
            KEY_AVATAR_URL, KEY_CREATED, KEY_INTRODUCTION, KEY_EXPERIENCE,
            KEY_MAX_ORDERS, KEY_GITHUB_USERNAME));
    }

    function getHomeList()
    {
        $sql = "SELECT id FROM reviewers WHERE valid = 1";
        $reviewers = $this->db->query($sql)->result();
        $limit = 3;
        if (count($reviewers) > $limit) {
            $keys = array_rand($reviewers, $limit);
            $someReviewers = array();
            foreach ($keys as $key) {
                array_push($someReviewers, $reviewers[$key]);
            }
        } else {
            $someReviewers = $reviewers;
        }
        $resultReviewers = array();
        foreach ($someReviewers as $reviewer) {
            array_push($resultReviewers, $this->getOne($reviewer->id));
        }
        return $resultReviewers;
    }

    public function getList($skip = 0, $limit = 100)
    {
        $fields = $this->publicFields();
        $sql = "SELECT $fields FROM reviewers where valid=1 ORDER BY created limit $limit OFFSET
$skip";
        $result = $this->db->query($sql)->result();
        foreach ($result as $reviewer) {
            $this->mergeCount($reviewer);
        }
        return $result;
    }

    private function mergeCount($reviewer)
    {
        $reviewer->orderCount = $this->orderDao->countFinishOrders($reviewer->id);
        $reviewer->rewardCount = $this->rewardDao->countRewardsByReviewerId($reviewer->id);
        $reviewer->busy = $this->isReviewerBusy($reviewer);
        $this->userDao->mergeTags($reviewer);
    }

    public function getOne($id)
    {
        $fields = $this->publicFields();
        $sql = "SELECT $fields FROM reviewers WHERE valid=1 AND id=?";
        $array[] = $id;
        $reviewer = $this->db->query($sql, $array)->row();
        if ($reviewer) {
            $this->mergeCount($reviewer);
        }
        return $reviewer;
    }

    private function update($id, $data)
    {

    }

    function isReviewerBusy($reviewer)
    {
        $todo = $this->orderDao->countPaidOrders($reviewer->id);
        return $todo >= $reviewer->maxOrders;
    }

}

