# PHP wrapper for Fixer API

This API wrapper provides a simple way to access [fixer.io API](https://fixer.io/documentation) in order to easily consume the endpoints with a PHP application.

---

- [Installation](#installation)
- [QuickStart](#quick-start)


We are supporting multiple endpoints:

- [symbols](#symbols): Retrieve the list of currencies supported by Fixer
- [rates](#rates): Return real-time exchange rate data AND historical data
- [convert](#convert): Return real-time exchange rate data

---

### Installation

You can use composer to include this package to your project:

    composer require infiniweb/fixer-api-php

### Quick Start

You will need first to instanciate the Fixer class:

    $fixer = new \InfiniWeb\FixerAPI\Fixer();

And provide the Fixer API Key:

    $fixer->setAccessKey($apiKey);

Make sure to first get your Free or paid API Key [here https://fixer.io/product](https://fixer.io/product)

You are now ready to consume the API!

### Symbols

To get the list of Symbols, simply use the following:

    $symbols = $fixer->symbols->get();

This will return a list of symbols (ISO 4217 Currency Code) as a simple array:

    Array
    (
        [AED] => United Arab Emirates Dirham
        [AFN] => Afghan Afghani
        [ALL] => Albanian Lek
        ...

### Rates

The are various ways to get rates. It can be real-time data, historical data or series data (from a date to another date)

#### Real-time rates

You can get the latest rates for all or for specific currencies:

    $baseCurrency = "EUR";
    $symbols = array("USD", "GBP");
    $return = $fixer->rates->get($baseCurrency, $symbols);

This will return the rates of provided currencies compared to the base currency.

    Array
    (
        [timestamp] => 1528014248
        [base] => EUR
        [rates] => Array
            (
                [USD] => 1.166583
                [GBP] => 0.874168
            )
    )

#### Historical rates

You could also retrive historical rate data by including the date in the request, such as:

    $fixer->rates->get($baseCurrency, $symbols, "2018-01-19");

Note that the date needs to be following this format: `YYYY-MM-DD`

#### Time-series rates

You can get daily rates from a starting end an end date, using:

    $return = $fixer->rates->getDailyRates("2018-05-01", "2018-05-03", $baseCurrency, $symbols);

    Array
    (
        [base] => EUR
        [rates] => Array
            (
                [2018-05-01] => stdClass Object
                    (
                        [USD] => 1.199468
                        [GBP] => 0.881297
                    )
                [2018-05-02] => stdClass Object
                    (
                        [USD] => 1.195602
                        [GBP] => 0.880967
                    )
                ...


### Fluctuations

The parameters to retrieve the fluctuations are exactly the same then the time-series rates.

    $return = $fixer->rates->getDailyFluctuation("2018-05-01", "2018-05-03", $baseCurrency, $symbols);

You will then get the daily flactuations for each currencies from the start to end date:

    Array
    (
        [base] => EUR
        [rates] => Array
            (
                [USD] => stdClass Object
                    (
                        [start_rate] => 1.199468
                        [end_rate] => 1.199326
                        [change] => -0.0001
                        [change_pct] => -0.0118
                    )
                [GBP] => stdClass Object
                    (
                        [start_rate] => 0.881297
                        [end_rate] => 0.883748
                        [change] => 0.0025
                        [change_pct] => 0.2781
                    )
            )
    )

### Convert

You can request the conversion from a currency to another. If you provide a date, it will return an historical rate.

    $from = "EUR";
    $to = "USD";
    $amount = "25";
    $date = "2018-01-19";
    $return = $fixer->convert->get($from, $to, $amount, $date);

You will receive the following array:

    Array
    (
        [timestamp] => 1516406399
        [rate] => 1.222637
        [result] => 30.565925
    )

### Additional features

#### SSL support

All paid subscription plans available on Fixer.io come with 256-bit SSL encryption. You can enable SSL support by providing extra information in the class constructor:

    $config = array('ssl' => true);
    $fixer = new \InfiniWeb\FixerAPI\Fixer($config);

