<?php
namespace Destiny\Controllers;

use Destiny\Chat\EmoteService;
use Destiny\Chat\ThemeService;
use Destiny\Common\Annotation\Audit;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Images\ImageService;
use Destiny\Common\Session\Session;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\ViewModel;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class AdminEmotesController {

    /**
     * @Route ("/admin/emotes")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"GET"})
     *
     * @throws Exception
     */
    public function emotes(ViewModel $model, array $params): string {
        $emoteService = EmoteService::instance();
        $themeService = ThemeService::instance();
        $cache = Application::getNsCache();
        $activeTheme = !empty($params['theme'] ?? null) ? $themeService->findThemeById((int) $params['theme']) : $themeService->getActiveTheme();
        $model->title = 'Emotes';
        $model->cacheKey = $cache->fetch('chatCacheKey');
        $model->theme = $activeTheme;
        $model->emotes = $emoteService->findAllEmotesWithTheme($activeTheme['id']);
        $model->themes = $themeService->findAllThemes();
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
            FilterParams::required($params, 'theme');
            $emoteService = EmoteService::instance();
            return ['exists' => $emoteService->isPrefixTaken(
                (string) $params['prefix'],
                (int) $params['theme'],
                $params['id']
            )];
        } catch (Exception $e) {
            return ['exists' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @Route ("/admin/emotes/{id}/edit")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"GET"})
     * @throws Exception
     */
    public function editEmote(array $params, ViewModel $model): string {
        FilterParams::required($params, 'id');
        $themeService = ThemeService::instance();
        $emoteService = EmoteService::instance();
        $emote = $emoteService->findEmoteById((int) $params['id']);
        if (empty($emote)) {
            throw new Exception('Emote not found.');
        }
        $model->title = 'Emote';
        $model->emote = $emote;
        $model->action = '/admin/emotes/'. $emote['id'] .'/edit';
        $model->theme = $themeService->findThemeById($emote['theme']);
        $model->themes = $themeService->findAllThemes();
        return 'admin/emote';
    }

    /**
     * @Route ("/admin/emotes/new")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"GET"})
     * @throws Exception
     */
    public function newEmote(ViewModel $model): string {
        $themeService = ThemeService::instance();
        $model->title = 'Emote';
        $model->theme = $themeService->getActiveTheme();
        $model->themes = $themeService->findAllThemes();
        $model->emote = [
            'id' => null,
            'img' => null,
            'theme' => $model->theme['id'],
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
     */
    public function newEmotePost(array $params): string {
        try {
            FilterParams::required($params, 'imageId');
            FilterParams::required($params, 'prefix');
            FilterParams::required($params, 'theme');
            FilterParams::declared($params, 'styles');
            FilterParams::declared($params, 'twitch');
            FilterParams::declared($params, 'draft');
            $emoteService = EmoteService::instance();
            $id = $emoteService->insertEmote([
                'imageId' => $params['imageId'],
                'prefix' => $params['prefix'],
                'styles' => $params['styles'],
                'twitch' => $params['twitch'],
                'draft' => $params['draft'],
                'theme' => $params['theme'],
            ]);
            Session::setSuccessBag('Emote '. $params['prefix'] .' created.');
            $emoteService->saveStaticFiles();
            return "redirect: /admin/emotes/$id/edit";
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            return 'redirect: /admin/emotes/new';
        }
    }

    /**
     * @Route ("/admin/emotes/{id}/edit")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"POST"})
     * @Audit
     */
    public function editEmotePost(array $params): string {
        try {
            FilterParams::required($params, 'id');
            FilterParams::required($params, 'imageId');
            FilterParams::required($params, 'prefix');
            FilterParams::declared($params, 'styles');
            FilterParams::declared($params, 'twitch');
            FilterParams::declared($params, 'draft');
            FilterParams::declared($params, 'theme');
            $emoteService = EmoteService::instance();
            $emote = $emoteService->findEmoteById($params['id']);
            if (empty($emote)) {
                throw new Exception('Emote not found.');
            }
            $emoteService->updateEmote($params['id'], [
                'imageId' => $params['imageId'],
                'prefix' => $params['prefix'],
                'styles' => $params['styles'],
                'twitch' => $params['twitch'],
                'draft' => $params['draft'],
                'theme' => $params['theme'],
            ]);
            Session::setSuccessBag('Emote '. $params['prefix'] .' updated.');
            $emoteService->saveStaticFiles();
            return "redirect: /admin/emotes/{$params['id']}/edit";
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            return 'redirect: /admin/emotes';
        }
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
            return $imageService->findImageById($imageService->insertImage($upload, 'emotes'));
        }, ImageService::diverseArray($_FILES['files']));
    }

    /**
     * @Route ("/admin/emotes/{id}/delete")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"POST"})
     * @Audit
     *
     * @throws Exception
     */
    public function deleteEmote(array $params): string {
        FilterParams::required($params, 'id');
        $emoteService = EmoteService::instance();
        $imageService = ImageService::instance();
        $emote = $emoteService->findEmoteById($params['id']);
        if (!empty($emote)) {
            $emoteService->removeEmoteById($emote['id']);
            $image = $imageService->findImageById($emote['imageId']);
            $imageService->removeImageFile($image, EmoteService::EMOTES_DIR);
            $imageService->removeImageById($image['id']);
        }
        Session::setSuccessBag('Emote '. $emote['prefix'] .' deleted.');
        $emoteService->saveStaticFiles();
        return 'redirect: /admin/emotes';
    }

    /**
     * @Route ("/admin/emotes/{id}/copy")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"POST"})
     * @Audit
     */
    public function emoteCopy(array $params): string {
        try {
            FilterParams::required($params, 'id');
            FilterParams::required($params, 'theme');

            $themeService = ThemeService::instance();
            $emoteService = EmoteService::instance();
            $imageService = ImageService::instance();

            $theme = $themeService->findThemeById((int) $params['theme']);
            $emote = $emoteService->findEmoteById((int) $params['id']);

            if (empty($emote)) {
                Session::setErrorBag("Emote not found.");
                return "redirect: /admin/emotes";
            }
            if (empty($theme)) {
                Session::setErrorBag("Theme not found.");
                return "redirect: /admin/emotes";
            }
            if ($emoteService->isPrefixTaken($emote['prefix'], $theme['id'])) {
                Session::setErrorBag("Prefix already taken for {$theme['label']} theme {$emote['prefix']}.");
                return "redirect: /admin/emotes/{$emote['id']}/edit";
            }
            $conn = Application::getDbConn();
            $conn->beginTransaction();
            $image = $imageService->findImageById($emote['imageId']);
            $image['name'] = $imageService->copyImageFile($image, EmoteService::EMOTES_DIR);
            $emote['imageId'] = $imageService->insertImage($image);
            $emote['theme'] = $theme['id'];
            $emoteId = $emoteService->insertEmote($emote);
            $conn->commit();
            Session::setSuccessBag("Emote copied. Set to 'draft' automatically.");
            return "redirect: /admin/emotes/$emoteId/edit";
        } catch (DBALException $e) {
            Session::setErrorBag("Could not copy emote {$e->getMessage()} .");
            return "redirect: /admin/emotes";
        } catch (Exception $e) {
            Session::setErrorBag("Could not copy emote {$e->getMessage()} .");
            return "redirect: /admin/emotes";
        }
    }

    /**
     * @Route ("/admin/emotes/{id}/preview")
     * @Secure ({"EMOTES"})
     * @HttpMethod ({"GET"})
     */
    function emotePreview(array $params, ViewModel $model): string {
        try {
            FilterParams::required($params, 'id');
            $emoteService = EmoteService::instance();
            $emote = $emoteService->findEmoteById((int) $params['id']);
            if (empty($emote)) {
                $model->error = "Invalid emote";
                return "admin/emotepreview";
            }
            $model->title = 'Emote Preview';
            $model->emote = $emote;
            $model->emoteCss = $emoteService->buildEmoteCSS($emote, false);
            $model->emoteStyle = $emoteService->buildEmoteStyleCSS($emote);
        } catch (Exception $e) {
            $model->error = $e->getMessage();
        }
        return "admin/emotepreview";
    }

    /**
     * @Route ("/admin/emotes/preview")
     * @Secure ({"EMOTES"})
     *
     * @throws Exception
     */
    function emotePreviewUnsaved(array $params, ViewModel $model): string {
        FilterParams::required($params, 'prefix');
        FilterParams::required($params, 'imageId');
        FilterParams::declared($params, 'styles');
        $emoteService = EmoteService::instance();
        $imageService = ImageService::instance();
        $image = $imageService->findImageById((int) $params['imageId']);
        $emote = [
            'prefix' => $params['prefix'],
            'styles' => $params['styles'],
            'image' => $image['id'],
            'imageName' => $image['name'],
            'width' => $image['width'],
            'height' => $image['height'],
        ];
        $model->emote = $emote;
        $model->emoteCss = $emoteService->buildEmoteCSS($emote, false);
        $model->emoteStyle = $emoteService->buildEmoteStyleCSS($emote);
        return "admin/emotepreview";
    }
}