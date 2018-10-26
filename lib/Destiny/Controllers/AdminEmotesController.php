<?php
namespace Destiny\Controllers;

use Destiny\Chat\EmoteService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\ImageService;
use Destiny\Common\Session;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\FilterParamsException;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\ViewModel;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
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
     * @param ViewModel $model
     * @return string
     * @throws DBALException
     */
    public function emotes(ViewModel $model) {
        $emoteService = EmoteService::instance();
        $model->title = 'Emotes';
        $model->emotes = $emoteService->findAllEmotes();
        return 'admin/emotes';
    }

    /**
     * @Route ("/admin/emotes/prefix")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"POST"})
     * @ResponseBody()
     *
     * @param array $params
     * @return array
     */
    public function checkPrefix(array $params) {
        try {
            FilterParams::declared($params, 'id');
            FilterParams::required($params, 'prefix');
            $emoteService = EmoteService::instance();
            $emote = $emoteService->findEmoteByPrefix($params['prefix']);
            return (empty($emote) || $emote['id'] == $params['id']) ? ['success' => true] : ['error' => 'exists'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * @Route ("/admin/emotes/{id}/edit")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws DBALException
     * @throws FilterParamsException
     */
    public function editEmote(array $params, ViewModel $model) {
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
     *
     * @param ViewModel $model
     * @return string
     */
    public function newEmote(ViewModel $model) {
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
     *
     * @param array $params
     * @return string
     * @throws FilterParamsException
     */
    public function newEmotePost(array $params) {
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
     *
     * @param array $params
     * @return string
     * @throws DBALException
     * @throws FilterParamsException
     */
    public function editEmotePost(array $params) {
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
     *
     * @return array
     */
    public function uploadImage() {
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
     *
     * @param array $params
     * @return mixed
     *
     * @throws DBALException
     * @throws FilterParamsException
     * @throws InvalidArgumentException
     */
    public function deleteEmote(array $params) {
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