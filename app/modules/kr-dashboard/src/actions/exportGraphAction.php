<?php

/**
 * Edit indicator action
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

// Check if user is logged
$User = new User();
if (!$User->_isLogged()) {
    throw new Exception("User is not logged", 1);
}

// Init CryptoApi modules
$CryptoApi = new CryptoApi(null, null, $App);

// Init lang object
$Lang = new Lang($User->_getLang(), $App);

try {

    // Check args given
    if (empty($_GET) || empty($_GET['symbol'])) throw new Exception("Error : Empty args", 1);

    $Coin = $CryptoApi->_getCoin($_GET['symbol']);

    $CryptoGraph = new CryptoGraph($Coin->_getHistoMin(1440));

    $CryptoGraphHours = new CryptoGraph($Coin->_getHistoHour());
    $listCandles = array_merge($CryptoGraphHours->_getCandles(), $CryptoGraph->_getCandles());

    $CryptoGraphDays = new CryptoGraph($Coin->_getHistoDay());
    $listCandles = array_merge($CryptoGraphDays->_getCandles(), $listCandles);

    $listCandles = array_values($listCandles);

    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename="Export-'.$Coin->_getSymbol().'-'.date('d-m-Y-H-i').'.csv"');
    //
    // // do not cache the file
    header('Pragma: no-cache');
    header('Expires: 0');
    //
    // // create a file pointer connected to the output stream
    $file = fopen('php://output', 'w');
    //
    // // send the column headers
    fputcsv($file, array('Date', 'Value', 'Volume', 'Open', 'Close', 'Low', 'High'));

    $data = [];
    foreach ($listCandles as $candle) {
      $data[] = [
        $candle['date'],
        $candle['value'],
        $candle['volume'],
        $candle['open'],
        $candle['close'],
        $candle['low'],
        $candle['high']
      ];
    }

    foreach ($data as $row){
      fputcsv($file, $row);
    }

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
?>
