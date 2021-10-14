<?php
namespace InfiniWeb\FixerAPI;

use \Curl\Curl;

class Provider
{

    private $accessKey;

    public function __construct($accessKey)
    {
        $this->accessKey = $accessKey;
    }

    public function getResponse($endpoint, $data = [], $method = 'GET')
    {

        $url = $endpoint . "?access_key=" . $this->accessKey;

        if (!empty($data)) {
            $url .= "&" . http_build_query($data);
        }

        $curl = new Curl();
        $curl->get($url);

        if ($curl->error) {
            //echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
            return false;
        } else {
            return $curl->response;
        }

    }

    public function getAuthenticatedRequest()
    {

    }
    
}
