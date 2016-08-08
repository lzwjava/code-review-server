<?php
defined('BASEPATH') OR exit('No direct script access allowed');

define('QUEUE', 21671);

class Welcome extends BaseController
{
    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *        http://example.com/index.php/welcome
     *    - or -
     *        http://example.com/index.php/welcome/index
     *    - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */


    public function index_get()
    {
        $this->load->view('welcome_message');
        $op = $this->get('op');
        if ($op == 'get') {
            $this->getMessageQueue();
        } else {
            $this->addMessageQueue();
        }
    }

    private function getMessageQueue()
    {
        $queue = msg_get_queue(QUEUE);
        $msg = NULL;
        $msgType = NULL;
        if (msg_receive($queue, 1, $msgType, 1024, $msg)) {
            logInfo("receive msg type:" . $msgType . " mgs:" . json_encode($msg));
        } else {
            logInfo("could not receive");
        }
    }

    private function addMessageQueue()
    {
        $queue = msg_get_queue(QUEUE);

        $object = new stdclass;
        $object->name = 'foo';
        $object->id = uniqid();
        if (msg_send($queue, 1, $object)) {
            logInfo("added to queue, stat: " . json_encode(msg_stat_queue($queue)));
        } else {
            logInfo("could not add message to queue \n");
        }
    }

}
