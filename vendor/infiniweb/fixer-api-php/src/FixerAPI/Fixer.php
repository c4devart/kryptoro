<?php
namespace InfiniWeb\FixerAPI;

class Fixer
{

    /**
     * The API base URL
     * @var string
     */
    private $baseUrl = "data.fixer.io/api/";

    /**
     * The API provider for GET operations
     * @var Provider
     */
    private $provider;

    /**
     * Object managing the currency list (ISO code and currency name)
     * @var Symbols
     */
    public $symbols;

    /**
     * Object managing the currency rates
     * @var Rates
     */
    public $rates;

    /**
     * Object managing currencies conversion
     * @var Convert
     */
    public $convert;

    public function __construct($config = [])
    {
        $this->symbols = new Symbols($this);
        $this->rates = new Rates($this);
        $this->convert = new Convert($this);

        $this->setConfig($config);
    }

    protected function setConfig($config)
    {
        $this->baseUrl = isset($config['ssl']) && $config['ssl'] ? "https://".$this->baseUrl : "http://".$this->baseUrl;
    }

    /**
     * Set the API access key
     * @param string $accessKey Access Key provided by fixer.io
     */
    public function setAccessKey($accessKey)
    {
        $this->accessKey = $accessKey;
    }

    /**
     * Override the base URL of the API.
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Get the request provider for formatted and authenticated requests
     * @return Provider
     */
    protected function getProvider()
    {
        if ($this->provider) {
            return $this->provider;
        }

        return new Provider(
            $this->accessKey
        );
    }

    /**
     * Obtain the API response
     * @param  array $endpointKey The end point key to append to the base url
     * @param  array  $params The request parameters
     * @return Object The API response
     */
    public function getResponse($endpointKey, $params = [])
    {
        $endpoint = $this->baseUrl . $endpointKey;
        $provider = $this->getProvider();
        return $this->checkResponse($provider->getResponse($endpoint, $params));
    }

    /**
     * Check response for errors
     * @param  Object $response The API response
     * @throws \Exception
     * @return Object
     */
    public function checkResponse($response)
    {
        if (!$response) {
            throw new \Exception("Error Processing Request", 1);
        }

        if (!isset($response->success) || empty($response->success)) {

            $code = 0;
            $message = "";

            if (isset($response->error) && isset($response->error->code)) {
                $code = (int)$response->error->code;
            }

            if (isset($response->error) && isset($response->error->type)) {
                $message = $response->error->type;
            }

            if (isset($response->error) && isset($response->error->info)) {
                if (!empty($message)) {
                    $message .= " - ";
                }
                $message = $response->error->info;
            }

            throw new \Exception($message, $code);
        }

        return $response;
    }

}
