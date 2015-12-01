<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/2
 * Time: 上午12:39
 */
class Reviewer extends BaseController
{
    public function valid()
    {
        if ($this->checkIfParamsNotExist($_GET, array('id'))) {
            return;
        }
        $id = $_GET['id'];
        $result = $this->reviewerDao->setReviewerValid($id);
        if ($result) {
            $this->succeed();
        } else {
            $this->failure(-1, "update failed");
        }
    }

    public function index()
    {
        $skip = 0;
        $limit = 100;
        if (isset($_GET[KEY_SKIP])) {
            $skip = (int)$_GET[KEY_SKIP];
        }
        if (isset($_GET[KEY_LIMIT])) {
            $limit = (int)$_GET[KEY_LIMIT];
        }
        $list = $this->reviewerDao->getList($skip, $limit);
        $this->succeed($list);
    }

    public function view($id = null)
    {
        if ($id == null) {
            $this->failure(ERROR_MISS_PARAMETERS, "must provide id");
            return;
        }
        $reviewer = $this->reviewerDao->getOne($id);
        if ($reviewer) {
            $this->succeed($reviewer);
        } else {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "reviewer with that id not exits");
        }
    }
}