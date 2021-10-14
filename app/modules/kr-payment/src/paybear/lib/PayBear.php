<?php

/**
 * Class PayBear
 */
class PayBear
{
    /**
     * @var string API Secret Key
     */
    public $api_secret_key;

    /**
     * @var string API Public Key
     */
    public $api_public_key;

    /**
     * @var string gateway url
     */
    public $api_domain = 'https://api.paybear.io';

    /**
     * @var array list of enabled currencies
     */
    public static $_currencies = null;

    /**
     * @var array current average market rates
     */
    public static $_rates = null;

    /**
     * @var array exchange rates for one currency
     */
    public static $_rate = null;

    public function __construct( $api_secret_key, $api_public_key = '' ) {

        $this->api_secret_key = $api_secret_key;
        $this->api_public_key = $api_public_key;

    }

    /**
     * Get a list of enabled currencies
     *
     * @return array
     */
    public function getCurrencies() {

        if (self::$_currencies === null) {
            $url = sprintf('%s/v2/currencies?token=%s', $this->api_domain, $this->api_secret_key);
            $response = $this->request($url);

            if (isset($response) && $response['success']) {
                self::$_currencies = $response['data'];
            }
        }

        return self::$_currencies;
    }

    /**
     * Get the current average market rates
     *
     * @param string $fiat_code Fiat currency (usd, eur, cad, rub etc)
     * @return array
     */
    public function getRates($fiat_code) {

        if (self::$_rates[$fiat_code] === null) {
            $url = sprintf('%s/v2/exchange/%s/rate', $this->api_domain, $fiat_code);
            $response = $this->request($url);

            if (isset($response) && $response['success']) {
                self::$_rates[$fiat_code] = $response['data'];
            }
        }

        return self::$_rates[$fiat_code];
    }

    /**
     * Get exchange rates for one currency
     *
     * @param string $fiat_code
     * @param string $crypto
     * @return array
     */
    public function getRate($fiat_code, $crypto) {

        if (self::$_rate[$crypto][$fiat_code] === null) {
            $url = sprintf('%s/v2/%s/exchange/%s/rate', $this->api_domain, $crypto, $fiat_code);
            $response = $this->request($url);

            if (isset($response) && $response['success']) {
                self::$_rate[$crypto][$fiat_code] = $response['data'];
            }
        }

        return self::$_rate[$crypto][$fiat_code];
    }

    /**
     * Create payment request and get payment address
     *
     * @param string $crypto Crypto currency to accept (eth, btc, bch, ltc, dash, btg, etc)
     * @param string $callbackUrl server callback url (urlencoded)
     * @return array
     */
    public function createPayment($crypto, $callbackUrl = '') {

        $url = sprintf('%s/v2/%s/payment/%s?token=%s', $this->api_domain, $crypto, urlencode($callbackUrl), $this->api_secret_key);

        $response = $this->request($url);

        if (isset($response['data'])) {
            return $response['data'];
        }

        return null;
    }

    /**
     * Get currency data for PayBear form
     * @param $token
     * @param $fiatValue
     * @param $fiat_currency
     * @param bool $getAddress
     * @param string $callbackUrl
     * @return mixed|null
     */
    function getCurrency($token, $fiatValue, $fiat_currency, $getAddress = false, $callbackUrl = '' ) {
        $token = strtolower($token);
        $rate = $this->getCoinRate($fiat_currency, $token);

        if ($rate) {

            $coinsValue = round($fiatValue / $rate, 8);

            $currencies = $this->getCurrencies();
            if (isset($currencies[$token])) {
                $currency               = $currencies[ $token ];
                $currency['coinsValue'] = $coinsValue;
                $currency['rate'] = $rate;

                if ( $getAddress ) {
                    $address_data  = $this->createPayment($token, $callbackUrl);
                    $currency['address'] = $address_data['address'];
                } else {
                    $currency['currencyUrl'] = sprintf( 'currencies.php?token=%s', $token );
                }

                return $currency;
            }

        }

        return null;
    }

    /**
     * Get average coin rate
     * @param $fiat_currency
     * @param $token
     * @return bool|mixed
     */
    function getCoinRate($fiat_currency, $token ) {
        $rate = $this->getRate($fiat_currency, $token);

        return isset($rate['mid']) ? $rate['mid'] : false;
    }

    public static function request($url) {

        $curl      = curl_init();

        $curl_options = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false
        );

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];

        curl_setopt_array($curl, $curl_options);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response    = json_decode(curl_exec($curl), true);

        return $response;
    }

}
