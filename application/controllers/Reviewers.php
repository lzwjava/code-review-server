<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/2
 * Time: 上午12:39
 */
class Reviewers extends BaseController
{
    public $reviewerDao;

    function  __construct()
    {
        parent::__construct();
        $this->reviewerDao = new ReviewerDao();
    }

    public function valid_get($id)
    {
        $result = $this->reviewerDao->setReviewerValid($id);
        if ($result) {
            $this->succeed();
        } else {
            $this->failure(-1, "update failed");
        }
    }

    public function index_get()
    {
        $skip = $this->getSkip();
        $limit = $this->getLimit();
        $page = $this->get(KEY_PAGE);
        if ($page == 'home') {
            $list = $this->reviewerDao->getHomeList();
        } else {
            $list = $this->reviewerDao->getList($skip, $limit);
        }
        $this->succeed($list);
    }

    public function view_get($id)
    {
        $reviewer = $this->reviewerDao->getOne($id);
        if ($this->checkIfObjectNotExists($reviewer)) {
            return;
        }
        $this->succeed($reviewer);
    }
}
