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
use Destiny\Chat\ChatRedisService;
use Doctrine\DBAL\DBALException;

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
     * @return string
     */
    public function adminChat(ViewModel $model) {
        $model->title = 'Chat';
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
        $chatIntegrationService = ChatRedisService::instance();
        $chatIntegrationService->sendBroadcast($params ['message']);
        Session::setSuccessBag(sprintf('Sent broadcast: %s', $params ['message']));
        return 'redirect: /admin/chat';
    }

    /**
     * @Route ("/admin/chat/ip")
     * @Secure ({"ADMIN"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     */
    public function adminChatIp(array $params, ViewModel $model){
        $model->title = 'Chat';
        FilterParams::required($params, 'ip');
        $ids = ChatRedisService::instance()->findUserIdsByIP($params['ip']);
        $model->usersByIp = UserService::instance()->getUsersByUserIds($ids);
        $model->searchIp = $params ['ip'];
        return 'admin/chat';
    }

}
