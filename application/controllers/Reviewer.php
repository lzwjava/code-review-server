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
}