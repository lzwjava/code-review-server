<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/1
 * Time: 下午8:01
 */
class Test extends BaseController
{

    function xrange($start, $end, $step = 1)
    {
        for ($i = $start; $i <= $end; $i += $step) {
            yield $i;
        }
    }

    public function index()
    {
        foreach ($this->xrange(1, 100) as $num) {
            echo $num, "\n";
        }
        //$this->load->view('test');
    }
}
