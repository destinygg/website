<?php
namespace Destiny\Common;

use GuzzleHttp\Exception\RequestException;
use JsonSerializable;

class Exception extends \Exception implements JsonSerializable {

    public function __construct($message = "", \Exception $previous = null) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        return $this->getMessage();
    }

    /**
     * If the previous exception is an instance of
     * `GuzzleHttp\Exception\RequestException`, extract and return useful
     * properties from its `GuzzleHttp\Psr7\Request` object and, if it exists,
     * its `GuzzleHttp\Psr7\Response` object.
     */
    public function extractRequestResponse(): ?array {
        $previous = $this->getPrevious();
        if (isset($previous) && ($previous instanceof RequestException)) {
            $request = $previous->getRequest();
            $result = [
                'request' => [
                    'uri' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'headers' => $request->getHeaders(),
                    'body' => $request->getBody()
                ]
            ];

            if ($previous->hasResponse()) {
                $response = $previous->getResponse();
                $result['response'] = [
                    'statusCode' => $response->getStatusCode(),
                    'headers' => $response->getHeaders(),
                    'body' => $response->getBody()
                ];
            }

            return $result;
        } else {
            return null;
        }
    }
}
