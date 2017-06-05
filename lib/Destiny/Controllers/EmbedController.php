<?php 
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\ViewModel;

/**
 * @Controller
 */
class EmbedController {

    /**
     * @Route ("/embed/stream")
     *
     * @param ViewModel $model
     * @return string
     */
    public function embedStream(ViewModel $model){
        $model->title = 'Stream';
        return 'embed/stream';
    }
    
    /**
     * @Route ("/embed/chat")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function embedChat(array $params, ViewModel $model) {
        if(isset($params['follow']) && !empty($params['follow']) && substr( $params['follow'], 0, 1 ) == '/')
            $model->follow = $params['follow'];
        $model->title = 'Chat';
        return 'embed/chat';
    }
    
    /**
     * @Route ("/embed/onstreamchat")
     *
     * @param ViewModel $model
     * @return string
     */
    public function embedOnStreamChat(ViewModel $model) {
        $model->title = 'Chat';
        return 'embed/onstreamchat';
    }
}