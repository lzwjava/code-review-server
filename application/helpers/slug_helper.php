<?php
/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/2/24
 * Time: 下午9:35
 */

use Cocur\Slugify\Slugify;
use Overtrue\Pinyin\Pinyin;

if (!function_exists('makeSlug')) {
    function makeSlug($content)
    {
        $slugify = new Slugify();
        $pin = Pinyin::trans($content);
        $slug = $slugify->slugify($pin);
        return $slug;
    }
};
