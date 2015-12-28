<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/1
 * Time: 下午7:21
 */

use Qiniu\Auth;

if (!defined('BASEPATH'))
    exit ('No direct script access allowed');

class Qiniu extends BaseController
{

    private function getUpToken() {
        $bucket = 'codereview';
        $accessKey = '-ON85H3cEMUaCuj8UFpLELeEunEAqslrqYqLbn9g';
        $secretKey = 'X-oHOYDinDEhNk5nr74O1rKDvkmPq0ZQwEZfFt6x';
        $auth = new Auth($accessKey, $secretKey);

        $upToken = $auth->uploadToken($bucket);
        return $upToken;
    }

    public function token()
    {
        $upToken = $this->getUpToken();
        $bucketUrl = "http://7xotd0.com1.z0.glb.clouddn.com";
        $result = array(
            "uptoken" => $upToken,
            "bucketUrl" => $bucketUrl
        );
        $this->succeed($result);
    }
}
