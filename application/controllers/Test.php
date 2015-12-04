<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/1
 * Time: 下午8:01
 */
class Test extends BaseController
{

    private function xrange($start, $end, $step = 1)
    {
        for ($i = $start; $i <= $end; $i += $step) {
            yield $i;
        }
    }

    private function testRange() {
        foreach ($this->xrange(1, 5) as $num) {
            echo "<p>" . $num . "<p>";
        }
        foreach (range(1, 10) as $num) {
            echo $num;
        }
    }

    public function index()
    {
        $this->load->helper('url');
        $url = site_url('welcome/register');
        error_log("site_url: $url");

        error_log(anchor('news/local/123', 'My News', 'title="News title"'));
        $this->load->view('test');
    }
}
