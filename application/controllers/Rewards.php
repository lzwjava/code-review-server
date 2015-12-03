<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午8:32
 */
class Rewards extends BaseController
{
    public function callback()
    {
        $input = trim(file_get_contents('php://input'));
        log_message('error', "the input string $input");
        $input_data = json_decode($input, true);
        log_message('error', "the input data $input_data->id");
    }
}
