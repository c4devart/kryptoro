<?php

namespace BertW;
use GuzzleHttp\Client as GuzzleClient;

class Client implements ClientInterface
{
    /** @var string */
    const BASE_URI = 'https://www.coinpayments.net/api.php';

    /** @var GuzzleClient */
    public $client;

    /** @var string Your CoinPayments public API key. */
    protected $publicKey;

    /** @var string Your CoinPayments private API key. */
    protected $privateKey;

    /**
     * @param string $publicKey
     * @param string $privateKey
     * @param array $guzzleOptions Options to pass to the GuzzleClient constructor.
     */
    public function __construct($publicKey, $privateKey, array $guzzleOptions = [])
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;

        $this->client = new GuzzleClient(array_merge_recursive($guzzleOptions, [
            'base_uri' => $this::BASE_URI
        ]));
    }

    /**
     * Make a POST request to a specific API command.
     * @param string $cmd
     * @param array $fields
     * @param array $requestOptions Allow custom options to be sent with the Guzzle request.
     * @return mixed
     */
    private function request($cmd, array $fields = [], array $requestOptions = [])
    {
        $additionalFields = [
            'version' => 1,
            'cmd' => $cmd,
            'key' => $this->publicKey,
            'format' => 'json'
        ];

        // Validate the input. May throw exception when failed to validate.
        $validatedFields = Validator::create($cmd, $fields)->validate();

        $query = http_build_query(array_merge($validatedFields, $additionalFields), '', '&');
        $hmac = $this::generateHMAC($query, $this->privateKey);
        $response = $this->client->request('POST', '', array_merge_recursive($requestOptions, [
            'headers' => [
                'HMAC' => $hmac,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body' => $query
        ]));
        return \GuzzleHttp\json_decode((string)$response->getBody(), true);
    }

    /**
     * Generate an HMAC using sha512 and a private key.
     * @param string $str
     * @param string $key A private key of some sort.
     * @return string
     */
    public static function generateHMAC($str, $key)
    {
        return hash_hmac('sha512', $str, $key);
    }

    public function getBasicInfo()
    {
        return $this->request('get_basic_info');
    }

    public function rates($short = null, $accepted = null)
    {
        return $this->request('rates', [
            'short' => $short,
            'accepted' => $accepted
        ]);
    }

    public function balances($all = 0)
    {
        return $this->request('balances',[
            'all' => $all
        ]);
    }

    public function getDepositAddress($currency)
    {
        return $this->request('get_deposit_address', [
            'currency' => $currency
        ]);
    }

    public function createTransaction(array $options)
    {
        return $this->request('create_transaction', $options);
    }

    public function getCallbackAddress($currency, $ipn_url = null)
    {
        return $this->request('get_callback_address', [
            'currency' => $currency,
            'ipn_url' => $ipn_url
        ]);
    }

    public function getTxInfoMulti($txid)
    {
        return $this->request('get_tx_info_multi', [
            'txid' => $txid
        ]);
    }

    public function getTxInfo($txid, $full = null)
    {
        return $this->request('get_tx_info', [
            'txid' => $txid,
            'full' => $full
        ]);
    }

    public function getTxIds($limit = null, $start = null, $newer = null, $all = null)
    {
        return $this->request('get_tx_ids', [
            'limit' => $limit,
            'start' => $start,
            'newer' => $newer,
            'all' => $all
        ]);
    }

    public function createTransfer(array $options)
    {
        return $this->request('get_tx_ids', $options);
    }

    public function createWithdrawal(array $options)
    {
        // TODO: Implement createWithdrawal() method.
    }

    public function createMassWithdrawal(array $options)
    {
        // TODO: Implement createMassWithdrawal() method.
    }

    public function convert(array $options)
    {
        // TODO: Implement convert() method.
    }

    public function getWithdrawalHistory($limit = null, $start = null, $newer = null)
    {
        return $this->request('get_withdrawal_history', [
            'limit' => $limit,
            'start' => $start,
            'newer' => $newer
        ]);
    }

    public function getWithdrawalInfo($id)
    {
        return $this->request('get_withdrawal_info', [
            'id' => $id
        ]);
    }

    public function getConversionInfo($id)
    {
        return $this->request('get_conversion_info', [
            'id' => $id
        ]);
    }

    public function getPbnInfo($pbntag)
    {
        return $this->request('get_pbn_info', [
            'pbntag' => $pbntag
        ]);
    }

    public function getPbnList()
    {
        return $this->request('get_pbn_list');
    }

    public function updatePbnTag(array $options)
    {
        return $this->request('update_pbn_tag', $options);
    }

    public function claimPbnTag($tagid, $name)
    {
        return $this->request('claim_pbn_tag', [
            'tagid' => $tagid,
            'name' => $name
        ]);
    }
}
