<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/2
 * Time: 上午12:39
 */
class Reviewers extends BaseController
{
    public function valid()
    {
        if ($this->checkIfParamsNotExist($_GET, array(KEY_ID))) {
            return;
        }
        $id = $_GET[KEY_ID];
        $result = $this->reviewerDao->setReviewerValid($id);
        if ($result) {
            $this->succeed();
        } else {
            $this->failure(-1, "update failed");
        }
    }

    public function index()
    {
        $skip = $this->getSkip();
        $limit = $this->getLimit();
        $list = $this->reviewerDao->getList($skip, $limit);
        $this->succeed($list);
    }

    public function view()
    {
        if ($this->checkIfParamsNotExist($_GET, array(KEY_ID))) {
            return;
        }
        $id = $_GET[KEY_ID];
        $reviewer = $this->reviewerDao->getOne($id);
        if ($this->checkIfObjectNotExists($reviewer)) {
            return;
        }
        $this->succeed($reviewer);
    }
}
