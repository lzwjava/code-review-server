<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午1:02
 */
class Events extends BaseController
{
    public $eventDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('EventDao');
        $this->eventDao = new EventDao();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_NAME))) {
            return;
        }
        $name = $this->post(KEY_NAME);
        $id = $this->eventDao->addEvent($name);
        if ($this->checkIfSQLResWrong($id)) {
            return;
        }
        $this->succeed(array(KEY_EVENT_ID => $id));
    }
}
