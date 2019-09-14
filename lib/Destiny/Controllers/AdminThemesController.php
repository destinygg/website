<?php
namespace Destiny\Controllers;

use Destiny\Chat\EmoteService;
use Destiny\Chat\ThemeService;
use Destiny\Common\Annotation\Audit;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Session\Session;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\ViewModel;

/**
 * @Controller
 */
class AdminThemesController {

    /**
     * @Route ("/admin/themes")
     * @Secure ({"EMOTES","FLAIRS"})
     * @HttpMethod ({"GET"})
     *
     * @throws Exception
     */
    public function themes(ViewModel $model): string {
        $themeService = ThemeService::instance();
        $model->title = 'Themes';
        $model->themes = $themeService->findAllThemes();
        return 'admin/themes';
    }

    /**
     * @Route ("/admin/themes/{id}/edit")
     * @Secure ({"EMOTES","FLAIRS"})
     * @HttpMethod ({"GET"})
     *
     * @throws Exception
     */
    public function editTheme(array $params, ViewModel $model): string {
        FilterParams::required($params, 'id');
        $themeService = ThemeService::instance();
        $theme = $themeService->findThemeById((int) $params['id']);
        if (empty($theme)) {
            throw new Exception('Theme not found.');
        }
        $model->title = 'Theme';
        $model->action = "/admin/themes/{$params['id']}/edit";
        $model->theme = $theme;
        return 'admin/theme';
    }

    /**
     * @Route ("/admin/themes/new")
     * @Secure ({"EMOTES","FLAIRS"})
     * @HttpMethod ({"GET"})
     */
    public function newTheme(ViewModel $model): string {
        $model->title = 'Theme';
        $model->action = '/admin/themes/new';
        $model->theme = ['id' => null, 'prefix' => '', 'label' => '', 'active' => 0, 'color' => '#000000'];
        return 'admin/theme';
    }

    /**
     * @Route ("/admin/themes/new")
     * @Secure ({"EMOTES","FLAIRS"})
     * @HttpMethod ({"POST"})
     * @Audit
     *
     * @throws Exception
     */
    public function newThemePost(array $params): string {
        FilterParams::required($params, 'prefix');
        FilterParams::required($params, 'label');
        FilterParams::declared($params, 'active');
        FilterParams::declared($params, 'color');
        $themeService = ThemeService::instance();
        if ($themeService->existsByPrefix($params['prefix'])) {
            Session::setErrorBag("Theme prefix already exists {$params['prefix']}");
            return 'redirect: /admin/themes';
        }
        $themeId = $themeService->insertTheme([
            'prefix' => $params['prefix'],
            'label' => $params['label'],
            'active' => $params['active'],
            'color' => $params['color'],
        ]);
        if (boolval($params['active'])) {
            $emoteService = EmoteService::instance();
            $emoteService->saveStaticFiles();
        }
        Session::setSuccessBag("Theme {$params['label']} created.");
        return "redirect: /admin/themes/$themeId/edit";
    }

    /**
     * @Route ("/admin/themes/{id}/edit")
     * @Secure ({"EMOTES","FLAIRS"})
     * @HttpMethod ({"POST"})
     * @Audit
     *
     * @throws Exception
     */
    public function editThemePost(array $params): string {
        FilterParams::required($params, 'id');
        FilterParams::required($params, 'prefix');
        FilterParams::required($params, 'label');
        FilterParams::declared($params, 'active');
        FilterParams::declared($params, 'color');
        $themeService = ThemeService::instance();
        $theme = $themeService->findThemeById((int) $params['id']);
        if (empty($theme)) {
            Session::setErrorBag('Invalid theme');
            return 'redirect: /admin/themes';
        }
        $themeService->updateTheme($theme['id'], [
            'prefix' => $params['prefix'],
            'label' => $params['label'],
            'active' => $params['active'],
            'color' => $params['color'],
        ]);
        if (boolval($params['active'])) {
            $emoteService = EmoteService::instance();
            $emoteService->saveStaticFiles();
        }
        $themeService->ensureOneActiveTheme();
        Session::setSuccessBag("Theme {$params['label']} saved.");
        return "redirect: /admin/themes/{$theme['id']}/edit";
    }

    /**
     * @Route ("/admin/themes/{id}/delete")
     * @Secure ({"EMOTES","FLAIRS"})
     * @HttpMethod ({"POST"})
     * @Audit
     */
    public function deleteTheme(array $params) {
        try {
            FilterParams::required($params, 'id');
            $themeService = ThemeService::instance();
            $emoteService = EmoteService::instance();
            $theme = $themeService->findThemeById((int) $params['id']);
            if ((int) $params['id'] === ThemeService::BASE_THEME_ID) {
                Session::setErrorBag("Cannot delete the base theme.");
                return "redirect: /admin/themes/{$params['id']}/edit";
            }
            if (boolval($theme['active'])) {
                Session::setErrorBag("Cannot delete the active theme.");
                return "redirect: /admin/themes/{$params['id']}/edit";
            }
            $themeService->removeThemeById((int) $params['id']);
            $emoteService->removeEmoteByTheme((int) $params['id']);
            Session::setSuccessBag("Deleted theme successfully!");
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            Log::error("Could not delete theme. {$e->getMessage()}");
        }
        return "redirect: /admin/themes";
    }
}