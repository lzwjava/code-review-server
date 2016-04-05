<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 4/4/16
 * Time: 10:53 PM
 */
class Workshops extends BaseController
{
    public $workshopDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(WorkshopDao::class);
        $this->workshopDao = new WorkshopDao();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_NAME, KEY_AMOUNT,
            KEY_MAX_PEOPLE))
        ) {
            return;
        }
        $name = $this->post(KEY_NAME);
        $amount = $this->post(KEY_AMOUNT);
        $maxPeople = $this->post(KEY_MAX_PEOPLE);
        $id = $this->workshopDao->addWorkshop($name, $amount, $maxPeople);
        if ($this->checkIfSQLResWrong($id)) {
            return;
        }
        $this->succeed(array(KEY_WORKSHOP_ID => $id));
    }

    function pay_post($workshopId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $workshop = $this->workshopDao->getWorkshop($workshopId, $user);
        if ($this->checkIfObjectNotExists($workshop)) {
            return;
        }
        if ($workshop->restCount <= 0) {
            $this->failure(ERROR_EXCEED_MAX, '报名已满, 下次再约, 感谢关注.');
            return;
        }
        if ($workshop->enrollment != null) {
            $this->failure(ERROR_ALREADY_DO_IT, '您已报名,无需再次报名.');
            return;
        }
        $subject = truncate($user->username, 18) . '参加研讨会' . $workshop->workshopId;
        $body = $user->username . ' 参加 ' . $workshop->name;
        $metaData = array(KEY_WORKSHOP_ID => $workshopId, KEY_USER_ID => $user->id);
        $this->createChargeThenResponse($workshop->amount, $subject, $body, $metaData, $user);
    }


    function one_get($workshopId)
    {
        $user = $this->getSessionUser();
        $workshop = $this->workshopDao->getWorkshop($workshopId, $user);
        if ($this->checkIfObjectNotExists($workshop)) {
            return;
        }
        $this->succeed($workshop);
    }

}