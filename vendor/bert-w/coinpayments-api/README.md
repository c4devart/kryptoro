# bert-w/coinpayments-api
A PHP implementation of the CoinPayments API.

### Installation instructions
`composer require bert-w/coinpayments-api`

### Quick start
See [CoinPayments API Doc](https://www.coinpayments.net/apidoc) for the CoinPayments API documentation.

##### Code Samples

###### Retrieving basic merchant info
```
$publicKey = 'your_public_api_key';
$privateKey = 'your_private_api_key';
 
$client = new BertW\CoinPaymentsApi\Client($publicKey, $privateKey);

$response = $client->getBasicInfo();
 
print_r($response);
// Array
// (
//     [error] => ok
//     [result] => Array
//         (
//             [username] => MerchantUsername
//             [merchant_id] => abcd32abcd32abcd32abcd32abcd32ab
//             [email] => merchantemail@example.com
//             [public_name] => 
//             [time_joined] => 1518095700
//         )
// 
// )
```

### Quick API Reference
Creating a transaction is easy:
```
$client->createTransaction([
    'amount' => 0.452811,
    'currency1' => 'USD',
    'currency2' => 'BTC',
    // optional parameters here...
```

API requests with more than 4 parameters use an associative array syntax like above (all functions with
`array $options`). Simple API calls like
`getDepositAddress`, can simply be called using `$client->getDepositAddress($currency)`.

#### Function List

```
public function getBasicInfo();
public function rates($short = null, $accepted = null);
public function balances($all = 0);
public function getDepositAddress($currency);
public function createTransaction(array $options);
public function getCallbackAddress($currency, $ipn_url = null);
public function getTxInfoMulti($txid);
public function getTxInfo($txid, $full = null);
public function getTxIds($limit = null, $start = null, $newer = null, $all = null);
public function createTransfer(array $options);
public function createWithdrawal(array $options);
public function createMassWithdrawal(array $options);
public function convert(array $options);
public function getWithdrawalHistory($limit = null, $start = null, $newer = null);
public function getWithdrawalInfo($id);
public function getConversionInfo($id);
public function getPbnInfo($pbntag);
public function getPbnList();
public function updatePbnTag(array $options);
public function claimPbnTag($tagid, $name);
```

