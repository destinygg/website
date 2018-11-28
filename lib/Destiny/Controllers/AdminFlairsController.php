<?php
namespace Destiny\Controllers;

use Destiny\Chat\FlairService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Exception;
use Destiny\Common\Images\ImageService;
use Destiny\Common\Session\Session;
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
class AdminFlairsController {

    /**
     * @Route ("/admin/flairs")
     * @Secure ({"FLAIRS"})
     * @HttpMethod ({"GET","POST"})
     *
     * @param ViewModel $model
     * @return string
     * @throws DBALException
     */
    public function flairs(ViewModel $model) {
        $flairsService = FlairService::instance();
        $model->title = 'Flairs';
        $model->flairs = $flairsService->findAllFlairs();
        return 'admin/flairs';
    }


    /**
     * @Route ("/admin/flairs/{id}/edit")
     * @Secure ({"FLAIRS"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws FilterParamsException
     * @throws DBALException
     */
    public function editFlair(array $params, ViewModel $model) {
        FilterParams::required($params, 'id');
        $flairsService = FlairService::instance();
        $flair = $flairsService->findFlairById($params['id']);
        if (empty($flair)) {
            throw new RuntimeException('Flair not found.');
        }
        $model->title = 'Flair';
        $model->flair = $flair;
        $model->action = '/admin/flairs/'. Tpl::out($flair['featureId']) .'/edit';
        $model->presets = $flairsService->findAvailableFlairNames();
        return 'admin/flair';
    }

    /**
     * @Route ("/admin/flairs/new")
     * @Secure ({"FLAIRS"})
     * @HttpMethod ({"GET"})
     *
     * @param ViewModel $model
     * @return string
     * @throws DBALException
     */
    public function newFlair(ViewModel $model) {
        $flairsService = FlairService::instance();
        $model->title = 'Flair';
        $model->flair = [
            'featureId' => '',
            'featureLabel' => '',
            'featureName' => '',
            'img' => null,
            'imageId' => null,
            'locked' => 0,
            'hidden' => 1,
            'color' => '',
            'priority' => 50,
        ];
        $model->action = '/admin/flairs/new';
        $model->presets = $flairsService->findAvailableFlairNames();
        return 'admin/flair';
    }

    /**
     * @Route ("/admin/flairs/{id}/edit")
     * @Secure ({"FLAIRS"})
     * @HttpMethod ({"POST"})
     *
     * @param array $params
     * @return string
     * @throws FilterParamsException
     * @throws DBALException
     */
    public function editFeaturePost(array $params) {
        FilterParams::required($params, 'id');
        FilterParams::required($params, 'imageId');
        FilterParams::required($params, 'featureLabel');
        FilterParams::declared($params, 'hidden');
        FilterParams::declared($params, 'color');
        FilterParams::declared($params, 'priority');
        $flairsService = FlairService::instance();
        $feature = $flairsService->findFlairById($params['id']);
        if (empty($feature)) {
            throw new RuntimeException('Feature not found.');
        }
        $flairsService->updateFlair($feature['featureId'], [
            'featureLabel' => $params['featureLabel'],
            'imageId' => $params['imageId'],
            'hidden' => $params['hidden'],
            'color' => $params['color'],
            'priority' => $params['priority'],
        ]);
        $feature = $flairsService->findFlairById($params['id']);
        Session::setSuccessBag('Flair '. $feature['featureLabel'] .' updated.');
        $flairsService->saveStaticFiles();
        return 'redirect: /admin/flairs/';
    }

    /**
     * @Route ("/admin/flairs/new")
     * @Secure ({"FLAIRS"})
     * @HttpMethod ({"POST"})
     *
     * @param array $params
     * @return string
     * @throws FilterParamsException
     */
    public function newFlairPost(array $params) {
        FilterParams::required($params, 'imageId');
        FilterParams::required($params, 'featureLabel');
        FilterParams::required($params, 'featureName');
        FilterParams::declared($params, 'locked');
        FilterParams::declared($params, 'hidden');
        FilterParams::declared($params, 'color');
        FilterParams::declared($params, 'priority');
        $flairsService = FlairService::instance();
        $flairsService->insertFlair([
            'imageId' => $params['imageId'],
            'featureLabel' => $params['featureLabel'],
            'featureName' => $params['featureName'],
            'locked' => $params['locked'],
            'hidden' => $params['hidden'],
            'color' => $params['color'],
            'priority' => $params['priority'],
        ]);
        Session::setSuccessBag('Flair '. $params['featureLabel'] .' created.');
        $flairsService->saveStaticFiles();
        return 'redirect: /admin/flairs/';
    }

    /**
     * @Route ("/admin/flairs/upload")
     * @Secure ({"FLAIRS"})
     * @HttpMethod ({"POST"})
     * @ResponseBody
     *
     * @return array
     */
    public function uploadImage() {
        return array_map(function($file) {
            $imageService = ImageService::instance();
            $upload = $imageService->upload($file, FlairService::FLAIRS_DIR);
            return $imageService->findImageById($imageService->addImage($upload, 'flairs'));
        }, ImageService::diverseArray($_FILES['files']));
    }

    /**
     * @Route ("/admin/flairs/{id}/delete")
     * @Secure ({"FLAIRS"})
     * @HttpMethod ({"POST"})
     *
     * @param array $params
     * @return mixed
     *
     * @throws DBALException
     * @throws FilterParamsException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function deleteFlair(array $params) {
        FilterParams::required($params, 'id');
        $flairsService = FlairService::instance();
        $imageService = ImageService::instance();
        $flair = $flairsService->findFlairById($params['id']);
        if (!empty($flair)) {
            if ($flair['locked'] == 1) {
                throw new Exception("Cannot delete a locked flair.");
            }
            $flairsService->removeFlairById($flair['featureId']);
            $image = $imageService->findImageById($flair['imageId']);
            $imageService->removeImageFile($image['name'], FlairService::FLAIRS_DIR);
            $imageService->removeImageById($image['id']);
        }
        Session::setSuccessBag('Flair '. $flair['featureLabel'] .' deleted.');
        $flairsService->saveStaticFiles();
        return 'redirect: /admin/flairs';
    }
}