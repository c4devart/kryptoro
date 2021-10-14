# PayBear.io
## PayBear PHP Library

### API Keys
In order to use the system you need an API key. Getting a key is free and easy, sign up here: https://www.paybear.io

## Manual Installation
Download latest release files.

1.) Set your database credentials in *lib/base_model.php* file (95-98 lines)

```php
        $YOUR_DB_HOSTNAME   = 'YOUR_DB_HOSTNAME';
        $YOUR_DB_NAME       = 'YOUR_DB_NAME';
        $YOUR_DB_USERNAME   = 'YOUR_DB_USERNAME';
        $YOUR_DB_PASSWORD   = 'YOUR_DB_PASSWORD';
```


2.) Set your PayBear API key (Getting a key is free and easy, sign up here: https://www.paybear.io)

Set PayBear API key in *lib/PayBearOrder.php* file (19 line)

```php
        $api_secret_key = 'YOUR_API_SECRET_KEY_HERE';
        $api_public_key = 'YOUR_API_PUBLIC_KEY_HERE';
```

3.) After that, open *install-mysql.php* in your browser, and tables will be installed. 
For working example, you should install CMS order too. (it will be done after tables installation.)
Please see *install-mysql.php* for details:

```php
        $CmsOrder->increment_id     = '100001';
        $CmsOrder->order_total      = 19.95;
        $CmsOrder->fiat_currency    = 'usd';
        $CmsOrder->fiat_sign        = '$';
```

4.) Open *index.php* in your browser and check how it works.

####/ ************************************* /

```php
require_once 'lib/PayBear.php';
```

### Getting Started

Usage of PayBear PHP library.

```php
$payBear = new PayBear('YOUR_API_SECRET_KEY');
```

### Get Currencies
Get a list of enabled currencies:

```php
$currencies = $payBear->getCurrencies();
```

### Create payment request and get payment address for customer

```php
$currencies = $payBear->getPaymentData($crypto, $callback_url);
```
$crypto is crypto currency to accept ('eth', 'btc', 'bch', 'ltc', 'dash', 'btg', etc)

$callback_url is your server callback url 

### Get Market Rate
Get current average market rates:

```php
$currencies = $payBear->getRates($fiat_code);
```
$fiat_code is fiat currency ('usd', 'eur', 'cad', 'rub' etc)

Get exchange rates for one currency

```php
$currencies = $payBear->getRate($fiat_code, $crypto);
```

$fiat_code is fiat currency ('usd', 'eur', 'cad', 'rub' etc)

$crypto is crypto currency code ('eth', 'btc', 'bch', 'ltc', 'dash', 'btg', etc)

