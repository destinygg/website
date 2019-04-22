<?php
namespace Destiny\Common\Images;

use Destiny\Common\Config;
use Destiny\Common\Log;
use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\RandomString;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ImageDownloadUtil {

    private static $ALLOWED_EXT = ["jpg", "jpeg", "png", "gif"];
    private static $PATH_SEPARATOR = "/";

    /**
     * Downloads a remote image. File is named using a hash of the URL.
     * Files are stored in sub folders using the first 0-9A-z of the hash.
     *
     * @param string $url
     *  url to image
     * @param boolean $hashContents
     *  If false, will not request a new image if one is found
     *  If true, filename will be derived from the md5 of the file contents, instead of the URL
     *   This is to make sure new images are not cached by the http server.
     * @param string $path
     *  Full directory, must not begin with slash, must end with slash
     *  Must not include filename, must be an existing path, must include a filename and ext
     *
     * @return string a RELATIVE path to the file, or an empty string if something went wrong
     */
    public static function download($url, $hashContents = false, $path = null) {
        if (empty($url)) {
            return '';
        }
        if (empty($path)) {
            $path = Config::$a['images']['path'];
        }

        $hash = md5($url);
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        $shard = (preg_match("/[a-z0-9]/i", $hash, $match)) ? $match[0] : "0";
        $fullfolder = $path . $shard . self::$PATH_SEPARATOR;

        if (strlen($shard) <= 0) {
            Log::error("Invalid shard. $shard");
            return '';
        } else if (empty($ext) || !in_array($ext, self::$ALLOWED_EXT)) {
            Log::error("File type not supported or invalid extension. $url");
            return '';
        } else if (!file_exists($fullfolder) && !mkdir($fullfolder)) {
            Log::error("Could not make shard sub-folder. $fullfolder");
            return '';
        }

        if (!$hashContents) {
            $filename = "$hash.$ext";
            if (file_exists($fullfolder . $filename) || self::downloadImage($url, $fullfolder . $filename)) {
                Log::debug("Not downloading image, one already exists ($url).");
                return $shard . self::$PATH_SEPARATOR . $filename;
            }
        } else {
            $filename = RandomString::makeUrlSafe(32) . ".tmp";
            // TODO do a head request, or check the etag
            if (self::downloadImage($url, $fullfolder . $filename)) {
                $newfilename = md5_file($fullfolder . $filename) . ".$ext";
                if (!is_file($fullfolder . $newfilename)) {
                    Log::debug("Rename $fullfolder$filename, $fullfolder$newfilename");
                    rename($fullfolder . $filename, $fullfolder . $newfilename);
                } else {
                    Log::debug("Unlink $fullfolder$filename");
                    unlink($fullfolder . $filename);
                }
                return $shard . self::$PATH_SEPARATOR . $newfilename;
            }
        }
        return '';
    }

    /**
     * @param $url
     * @param $dest
     * @return mixed
     */
    private static function downloadImage($url, $dest) {
        try {
            Log::debug("Downloading $url");
            $client = new Client(['timeout' => 15, 'connect_timeout' => 5]);
            $r = $client->request('GET', $url, [
                'headers' => ['User-Agent' => Config::userAgent()],
                'sink' => $dest
            ]);
            $code = $r->getStatusCode();
            if ($code == Http::STATUS_OK) {
                return true;
            }
            Log::notice("Invalid http response code. [" . $code . "] $url");
        } catch (Exception $e) {
            Log::error($e->getMessage());
        } catch (GuzzleException $e) {
            Log::error($e->getMessage());
        }
        return false;
    }

}