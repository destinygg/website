<?php
namespace Destiny\Controllers;

use Destiny\Common\Exception;
use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\User\UserService;
use Destiny\Common\Session;
use Destiny\Chat\ChatIntegrationService;

/**
 * @Controller
 */
class ChatAdminController {
    
    /**
     * @Route ("/admin/chat")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     *
     * @param ViewModel $model
     * @throws Exception
     * @return string
     */
    public function adminChat(ViewModel $model) {
        $chatIntegrationService = ChatIntegrationService::instance ();
        $model->totalCombos = $chatIntegrationService->countChatCombos();
        $model->title = 'Chat';
        if (Session::get ( 'modelSuccess' )) {
            $model->success = Session::get ( 'modelSuccess' );
            Session::set ( 'modelSuccess' );
        }
        if (Session::get ( 'modelError' )) {
            $model->error = Session::get ( 'modelError' );
            Session::set ( 'modelError' );
        }
        return 'admin/chat';
    }
    
    /**
     * @Route ("/admin/chat/broadcast")
     * @Secure ({"ADMIN"})
     *
     * @param array $params         
     * @param ViewModel $model          
     * @throws Exception
     * @return string
     */
    public function adminChatBroadcast(array $params, ViewModel $model){
        $model->title = 'Chat';
        FilterParams::required($params, 'message');
        
        $chatIntegrationService = ChatIntegrationService::instance ();
        $chatIntegrationService->sendBroadcast ( $params ['message'] );

        Session::set ( 'modelSuccess', sprintf ( 'Sent broadcast: %s', $params ['message'] ) );
        return 'redirect: /admin/chat';
    }
    
    /**
     * @Route ("/admin/chat/ip")
     * @Secure ({"ADMIN"})
     *
     * @param array $params         
     * @param ViewModel $model          
     * @throws Exception
     * @return string
     */
    public function adminChatIp(array $params, ViewModel $model){
        $model->title = 'Chat';
        FilterParams::required ( $params, 'ip' );
        
        $userService = UserService::instance ();
        $model->usersByIp = $userService->findUsersWithIP ( $params ['ip'] );
        $model->searchIp = $params ['ip'];
        
        return 'admin/chat';
    }

    /**
     * @Route ("/admin/chat/removecombos")
     * @Secure ({"ADMIN"})
     *
     * @return string
     */
    public function adminChatRemoveCombos(){
        $chatIntegrationService = ChatIntegrationService::instance ();

        if ($chatIntegrationService->deleteChatCombos()) {
            Session::set('modelSuccess', 'All chat combos except 10 highest ones have been removed.');
        } else {
            Session::set('modelError', 'Something went wrong, check web.log for more info!');
        }

        return 'redirect: /admin/chat';
    }

    /**
     * @Route ("/admin/chat/removeallcombos")
     * @Secure ({"ADMIN"})
     *
     * @return string
     */
    public function adminChatRemoveAllCombos(){
        $chatIntegrationService = ChatIntegrationService::instance ();

        if ($chatIntegrationService->purgeChatCombos()) {
            Session::set('modelSuccess', 'Every single chat combo has been removed.');
        } else {
            Session::set('modelError', 'Something went wrong, check web.log for more info!');
        }

        return 'redirect: /admin/chat';
    }

}
