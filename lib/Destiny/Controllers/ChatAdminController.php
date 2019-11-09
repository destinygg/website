<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatRedisService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\FilterParamsException;
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
        try {
            FilterParams::declared($params, 'ip');
            if (empty($params['page'])) {
                $params['page'] = 1;
            }
            if (empty($params['size'])) {
                $params['size'] = 100;
            }
            $ids = ChatRedisService::instance()->findUserIdsByIPWildcard($params['ip']);
            $total = count($ids);
            if ($total > $params['size']) {
                $ids = array_slice($ids, ($params['page'] - 1) * $params['size'], (int)$params['size']);
            }
            $users = UserService::instance()->getUsersByUserIds($ids);
            $model->sizes = [100, 250, 500];
            $model->searchIp = $params['ip'];
            $model->users = [
                'pages' => 5,
                'list' => $users,
                'total' => $total,
                'totalpages' => ceil($total / $params['size']),
                'page' => $params['page'],
                'limit' => $params['size'],
            ];
        } catch (FilterParamsException $e) {
            Session::setErrorBag($e->getMessage());
            return 'redirect: /admin/chat';
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            Log::error($e);
            return 'redirect: /admin/chat';
        }
        return 'admin/chat';
    }

}
