<?php
namespace Destiny\Common\Utils;

use Destiny\Common\Application;
use Destiny\Common\Config;

class ImageDownload {

    private static $ALLOWED_EXT = ["jpg", "jpeg", "png", "gif"];
    private static $PATH_SEPARATOR = "/";

    /**
     * Downloads a remote image. File is named using a hash of the URL.
     * Files are stored in sub folders using the first 0-9A-z of the hash.
     *
     * @param $url string
     *  url to image
     * @param $path string
     *  Full directory, must not begin with slash, must end with slash
     *  Must not include filename, must be an existing path, must include a filename and ext
     * @param $overwrite boolean
     *  If false, will not request a new image if one is found
     *  If true, filename will be derived from the md5 of the file contents, instead of the URL
     *   This is to make sure new images are not cached by the http server.
     *   @TODO Adds complexity where there shouldn't - refactor
     *
     * @return string a RELATIVE path to the file, or an empty string if something went wrong
     */
    public static function download($url, $path, $overwrite = false){

        $response = "";
        if(empty($url)) return $response;

        $log   = Application::instance()->getLogger();
        $uri   = parse_url($url, PHP_URL_PATH);
        $ext   = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
        $name  = md5($url);
        $tmp   = $path . $name . ".tmp";
        $shard = (preg_match("/[a-z0-9]/i", $name, $match)) ? $match[0] : "0";
        $final = $shard . self::$PATH_SEPARATOR . $name . "." . $ext;

        if(empty($shard)){
            $log->error("Invalid shard." . $shard);
        } else if (!file_exists($path . $shard) && !mkdir($path . $shard)) {
            $log->error("Could not make shard sub-folder. " . $path . $shard);
        } else if(empty($ext) || !in_array($ext, self::$ALLOWED_EXT)){
            $log->error("File type not supported or invalid extension." . $uri);
        } else if ($overwrite === false && file_exists($path . $final)) {
            $log->notice("Not downloading image, one already exists.");
            $response = $final;
        } else {

            $fp = null;
            $ch = null;
            try {
                $ch = curl_init($url);
                $fp = fopen($tmp, "wb");
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER         => false,
                    CURLOPT_USERAGENT      => Config::userAgent(),
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_TIMEOUT        => 20,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                curl_setopt($ch, CURLOPT_FILE, $fp);
                if(!curl_exec($ch))
                    $log->error("Curl error: " . curl_error($ch));
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            } finally {
                if ($fp != null) fclose($fp);
                if ($ch != null) curl_close($ch);
            }

            try {
                if ($code !== 200) {
                    $log->error("Invalid http response code. [" . $code . "] " . $url);
                } else if (!file_exists($tmp)) {
                    $log->error("Temp file could not be saved. " . $tmp);
                } else {
                    // Logic is mentioned on the @overwrite parameter.
                    if($overwrite === true)
                        $final = $shard . self::$PATH_SEPARATOR . md5_file($tmp) . "." . $ext;
                    $response = rename($tmp, $path . $final) ? $final : "";
                }
            } catch (\Exception $e) {
                $log->error($e);
            } finally {
                @unlink($tmp);
            }

        }
        return $response;
    }

}