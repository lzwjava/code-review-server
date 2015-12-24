<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/24
 * Time: 下午2:46
 */
class Tags extends BaseController
{
    public function index()
    {
        $this->succeed($this->tagDao->getList());
    }
}
