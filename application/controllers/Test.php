<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/1
 * Time: 下午8:01
 */
class Test extends BaseController
{

    public function _remap($method)
    {
        error_log("method: $method");
        if ($method === 'some')
        {
            $this->index();
        }
        else
        {
            $this->$method();
        }
    }

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

    public function index($page = 'home', $id = null)
    {
        error_log("page: $page id: $id");
        $this->load->helper('url');
        $url = site_url('welcome/register');
        error_log("site_url: $url");
        error_log(anchor('news/local/123', 'My News', 'title="News title"'));

        $data['title'] = "Great News!";
        error_log(url_title('Swift is open source now.', 'dash', TRUE));
        $this->load->view('test', $data);
    }
}
