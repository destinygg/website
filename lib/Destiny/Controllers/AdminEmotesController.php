<?php
namespace Destiny\Controllers;

use Destiny\Chat\EmoteService;
use Destiny\Common\Annotation\Audit;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Application;
use Destiny\Common\Images\ImageService;
use Destiny\Common\Session\Session;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\FilterParamsException;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\ViewModel;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Exception;
use RuntimeException;

/**
 * @Controller
 */
class AdminEmotesController {

    /**
     * @Route ("/admin/emotes")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"GET"})
     *
     * @throws DBALException
     */
    public function emotes(ViewModel $model): string {
        $emoteService = EmoteService::instance();
        $cache = Application::getNsCache();
        $model->title = 'Emotes';
        $model->emotes = $emoteService->findAllEmotes();
        $model->cacheKey = $cache->fetch('chatCacheKey');
        return 'admin/emotes';
    }

    /**
     * @Route ("/admin/emotes/prefix")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"POST"})
     * @ResponseBody
     * @Audit
     */
    public function checkPrefix(array $params): array {
        try {
            FilterParams::declared($params, 'id');
            FilterParams::required($params, 'prefix');
            $emoteService = EmoteService::instance();
            $emote = $emoteService->findEmoteByPrefix($params['prefix']);
            return (empty($emote) || $emote['id'] == $params['id']) ? ['success' => true] : ['error' => 'exists'];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * @Route ("/admin/emotes/{id}/edit")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"GET"})
     *
     * @throws DBALException
     * @throws FilterParamsException
     */
    public function editEmote(array $params, ViewModel $model): string {
        FilterParams::required($params, 'id');
        $emoteService = EmoteService::instance();
        $emote = $emoteService->findEmoteById($params['id']);
        if (empty($emote)) {
            throw new RuntimeException('Emote not found.');
        }
        $model->title = 'Emote';
        $model->emote = $emote;
        $model->action = '/admin/emotes/'. Tpl::out($emote['id']) .'/edit';
        return 'admin/emote';
    }

    /**
     * @Route ("/admin/emotes/new")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"GET"})
     */
    public function newEmote(ViewModel $model): string {
        $model->title = 'Emote';
        $model->emote = [
            'id' => null,
            'img' => null,
            'imageId' => null,
            'draft' => 1,
            'twitch' => 0,
            'prefix' => '',
            'styles' => '',
        ];
        $model->action = '/admin/emotes/new';
        return 'admin/emote';
    }

    /**
     * @Route ("/admin/emotes/new")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"POST"})
     * @Audit
     *
     * @throws FilterParamsException
     * @throws DBALException
     */
    public function newEmotePost(array $params): string {
        FilterParams::required($params, 'imageId');
        FilterParams::required($params, 'prefix');
        FilterParams::declared($params, 'styles');
        FilterParams::declared($params, 'twitch');
        FilterParams::declared($params, 'draft');
        $emoteService = EmoteService::instance();
        $emoteService->insertEmote([
            'imageId' => $params['imageId'],
            'prefix' => $params['prefix'],
            'styles' => $params['styles'],
            'twitch' => $params['twitch'],
            'draft' => $params['draft'],
        ]);
        Session::setSuccessBag('Emote '. $params['prefix'] .' created.');
        $emoteService->saveStaticFiles();
        return 'redirect: /admin/emotes/';
    }

    /**
     * @Route ("/admin/emotes/{id}/edit")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"POST"})
     * @Audit
     *
     * @throws DBALException
     * @throws FilterParamsException
     */
    public function editEmotePost(array $params): string {
        FilterParams::required($params, 'id');
        FilterParams::required($params, 'imageId');
        FilterParams::required($params, 'prefix');
        FilterParams::declared($params, 'styles');
        FilterParams::declared($params, 'twitch');
        FilterParams::declared($params, 'draft');
        $emoteService = EmoteService::instance();
        $emote = $emoteService->findEmoteById($params['id']);
        if (empty($emote)) {
            throw new RuntimeException('Emote not found.');
        }
        $emoteService->updateEmote($params['id'], [
            'imageId' => $params['imageId'],
            'prefix' => $params['prefix'],
            'styles' => $params['styles'],
            'twitch' => $params['twitch'],
            'draft' => $params['draft'],
        ]);
        Session::setSuccessBag('Emote '. $params['prefix'] .' updated.');
        $emoteService->saveStaticFiles();
        return 'redirect: /admin/emotes/';
    }

    /**
     * @Route ("/admin/emotes/upload")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"POST"})
     * @ResponseBody
     * @Audit
     */
    public function uploadImage(): array {
        return array_map(function($file) {
            $imageService = ImageService::instance();
            $upload = $imageService->upload($file, EmoteService::EMOTES_DIR);
            return $imageService->findImageById($imageService->addImage($upload, 'emotes'));
        }, ImageService::diverseArray($_FILES['files']));
    }

    /**
     * @Route ("/admin/emotes/{id}/delete")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"POST"})
     * @Audit
     *
     * @throws DBALException
     * @throws FilterParamsException
     * @throws InvalidArgumentException
     */
    public function deleteEmote(array $params): string {
        FilterParams::required($params, 'id');
        $emoteService = EmoteService::instance();
        $imageService = ImageService::instance();
        $emote = $emoteService->findEmoteById($params['id']);
        if (!empty($emote)) {
            $emoteService->removeEmoteById($emote['id']);
            $image = $imageService->findImageById($emote['imageId']);
            $imageService->removeImageFile($image['name'], EmoteService::EMOTES_DIR);
            $imageService->removeImageById($image['id']);
        }
        Session::setSuccessBag('Emote '. $emote['prefix'] .' deleted.');
        $emoteService->saveStaticFiles();
        return 'redirect: /admin/emotes';
    }

}