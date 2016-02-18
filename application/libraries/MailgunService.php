<?php
/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/2/18
 * Time: 下午7:36
 */


use Mailgun\Mailgun;

class MailgunService
{
    function sendMessage($subject, $text)
    {
        $mg = new Mailgun("key-0d12dn67ru446qgc82j7uah42fei6l08");
        $domain = "mg.reviewcode.cn";

        $mg->sendMessage($domain, array(
            'from' => 'admin@mg.reviewcode.cn',
            'to' => 'lzwjava@gmail.com',
            'subject' => $subject,
            'text' => $text));
    }
}
