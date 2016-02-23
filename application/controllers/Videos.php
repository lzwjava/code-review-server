<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/1/16
 * Time: 下午10:14
 */
class Videos extends BaseController
{

    public $videoDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('videoDao');
        $this->videoDao = new VideoDao();
    }

    public function createVideo_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_TITLE, KEY_SOURCE, KEY_SPEAKER))) {
            return;
        }
        $title = $this->post(KEY_TITLE);
        $source = $this->post(KEY_SOURCE);
        $speaker = $this->post(KEY_SPEAKER);
        $videoId = $this->videoDao->addVideo($title, $speaker, $source);
        $video = $this->videoDao->getVideo($videoId);
        $this->succeed($video);
    }

    public function getVideoList_get()
    {
        $videos = $this->videoDao->getVideoList();
        $this->succeed($videos);
    }

    public function one_get($videoId)
    {
        $video = $this->videoDao->getVideo($videoId);
        $this->succeed($video);
    }

}
