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
            $user ['username'] = $creds->getUsername ();
            $user ['features'] = $creds->getFeatures ();
        }
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
            $user ['username'] = $creds->getUsername ();
            $user ['features'] = $creds->getFeatures ();
        }
        $model->options = $this->getChatOptionParams ( $params );
        $model->user = $user;
        return 'embed/chat';
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
        return array (
            'host' => Config::$a ['chat'] ['host'],
            'port' => Config::$a ['chat'] ['port'],
            'maxlines' => Config::$a ['chat'] ['maxlines'],
            'emoticons' => array_values( $emotes ),
        );
    }
}
?>