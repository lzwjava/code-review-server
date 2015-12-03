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
        if ($input == null) {
            $this->failure(ERROR_MISS_PARAMETERS, "please input string");
            return;
        }
        // $inputData = json_decode($input, true);
        // print_r($inputData);
        log_message('error', $input);
        $this->succeed();
    }
}
