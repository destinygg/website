<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatRedisService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Exception;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\ViewModel;

/**
 * @Controller
 */
class ChatAdminController {
    
    /**
     * @Route ("/admin/chat")
     * @Secure ({"MODERATOR"})
     * @HttpMethod ({"GET"})
     */
    public function adminChat(ViewModel $model): string {
        $model->title = 'Chat';
        return 'admin/chat';
    }
    
    /**
     * @Route ("/admin/chat/broadcast")
     * @Secure ({"MODERATOR"})
     * @throws Exception
     */
    public function adminChatBroadcast(array $params, ViewModel $model): string {
        $model->title = 'Chat';
        FilterParams::required($params, 'message');
        ChatRedisService::instance()->sendBroadcast($params ['message']);
        Session::setSuccessBag(sprintf('Sent broadcast: %s', $params ['message']));
        return 'redirect: /admin/chat';
    }

    /**
     * @Route ("/admin/chat/ip")
     * @Secure ({"MODERATOR"})
     * @throws Exception
     */
    public function adminChatIp(array $params, ViewModel $model): string {
        $model->title = 'Chat';
        $max = 100;
        FilterParams::required($params, 'ip');
        $ids = ChatRedisService::instance()->findUserIdsByIPWildcard($params['ip']);
        $ids = array_unique($ids);
        if (count($ids) > $max) {
            $ids = array_slice($ids, 0, $max);
        }
        $model->usersByIp = UserService::instance()->getUsersByUserIds($ids);
        $model->searchIp = $params ['ip'];
        return 'admin/chat';
    }

}
