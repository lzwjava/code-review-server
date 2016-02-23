<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/24
 * Time: 下午2:46
 */
class Tags extends BaseController
{
    public $tagDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('TagDao');
        $this->tagDao = new TagDao();
    }

    public function index_get()
    {
        $this->succeed($this->tagDao->getList());
    }
}
