<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Images\ImageService;
use Destiny\Common\Log;
use Destiny\Common\Cron\TaskInterface;

/**
 * @Schedule(frequency=1,period="hour")
 */
class CleanImageUploads implements TaskInterface {

    /**
     * @return mixed|void
     */
    public function execute() {
        try {
            $imageService = ImageService::instance();
            $images = $imageService->getAllOrphanedImages();
            foreach ($images as $image) {
                $imageService->removeImageFile($image['name'], _BASEDIR . '/static/'. $image['tag'] .'/');
                $imageService->removeImageById($image['id']);
            }
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
        }
    }

}