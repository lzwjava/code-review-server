<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/1/18
 * Time: 下午1:27
 */
class Applications extends BaseController
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('applicationDao');
    }

    function create_post()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($user->type !== TYPE_LEARNER) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT, '您已经是大神了');
            return;
        }
        $learnerId = $user->id;
        $aid = $this->applicationDao->addApplication($learnerId);
        $this->succeed($this->applicationDao->viewApplication($aid));
    }

    function agree_get($applicationId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $ok = $this->applicationDao->agreeApplication($applicationId);
        if (!$ok) {
            $this->failure(ERROR_RUN_SQL_FAILED, '无法转换为大神,内部错误');
        } else {
            $this->succeed();
        }
    }
}
