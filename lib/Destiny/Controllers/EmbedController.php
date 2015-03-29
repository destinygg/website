<?php 
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Config;
use Destiny\Common\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\ViewModel;

/**
 * @Controller
 */
class EmbedController {

    /**
     * @Route ("/embed/stream")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function embedStream(array $params, ViewModel $model){
        $user = null;
        if (Session::hasRole ( UserRole::USER )) {
            $creds = Session::getCredentials ();
            $user = array ();
            $user ['nick'] = $creds->getUsername ();
            $user ['features'] = $creds->getFeatures ();
        }
        $model->title = 'Stream';
        $model->user = $user;
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
        $user = null;
        if (Session::hasRole ( UserRole::USER )) {
            $creds = Session::getCredentials ();
            $user = array ();
            $user ['nick'] = $creds->getUsername ();
            $user ['features'] = $creds->getFeatures ();
        }
        $model->options = $this->getChatOptionParams ( $params );
        $model->user = $user;

        // Login follow url
        if(isset($params['follow']) && !empty($params['follow']) && substr( $params['follow'], 0, 1 ) == '/') {
            $model->follow = $params['follow'];
        }

        $model->title = 'Chat';
        return 'embed/chat';
    }
    
    /**
     * @Route ("/embed/onstreamchat")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function embedOnstreamChat(array $params, ViewModel $model) {
        $options = $this->getChatOptionParams ( $params );
        $options['maxlines'] = 30;

        $model->options = $options;
        $model->user = null;
        $model->title = 'Chat';
        return 'embed/onstreamchat';
    }
    
    /**
     * Get the chat params from the get request
     * Make sure they are all valid
     *
     * @param array $params
     */
    private function getChatOptionParams(array $params) {
        $emotes = Config::$a ['chat'] ['customemotes'];
        natcasesort( $emotes );

        $twemotes = Config::$a ['chat'] ['twitchemotes'];
        natcasesort( $twemotes );

        return array (
            'host' => Config::$a ['chat'] ['host'],
            'port' => Config::$a ['chat'] ['port'],
            'maxlines' => Config::$a ['chat'] ['maxlines'],
            'emoticons' => array_values( $emotes ),
            'twitchemotes' => array_values( $twemotes ),
        );
    }
}
?>