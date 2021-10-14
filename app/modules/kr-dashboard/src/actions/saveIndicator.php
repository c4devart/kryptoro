<?php

/**
 * Save indicator action
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("User is not logged", 1);
    }

    // Check args given
    if (empty($_POST) || empty($_POST['container']) || empty($_POST['indicator']) || empty($_POST['key'])) {
        throw new Exception("Error : Empty post", 1);
    }

    $CryptoIndicator = new CryptoIndicators($_POST['container']);

    // Check indicator given is available
    $listIndicatorAvailable = CryptoIndicators::_getIndicatorsList();
    if (!array_key_exists($_POST['indicator'], $listIndicatorAvailable)) {
        throw new Exception("Error : Invalid indicator", 1);
    }

    // Get indicator data
    $infosIndicator = CryptoIndicators::_getIndicatorsList()[$_POST['indicator']];

    $dataSaved = [];
    $title = $_POST['indicator'];
    $argsFound = [];

    // Make indicator data
    foreach ($_POST as $fieldname => $fieldvalue) {
        $fieldExist = false;
        foreach ($infosIndicator['cfg'] as $section) {
            foreach ($section as $catValue) {
                foreach ($catValue as $fieldKey => $fieldValue) {
                    if ($fieldKey == $fieldname) {
                        $fieldExist = true;
                        if (!array_key_exists($fieldKey, $argsFound) && in_array($fieldKey, $infosIndicator['args']) && count($infosIndicator['args']) > 0) {
                            $argsFound[] = $fieldvalue;
                        }
                        $dataSaved[$fieldKey] = $fieldvalue;
                        break;
                    }
                }
                if ($fieldExist) {
                    break;
                }
            }
        }
    }

    if (count($infosIndicator['args']) > 0) {
        $title .= " (".join(',', $argsFound).")";
    }


    // Check if indicator exist
    $indicatorContainer = $CryptoIndicator->_getIndicatorInformations($_POST['indicator'], $_POST['key']);

    if (count($indicatorContainer) > 0) {
        // Update indicator data
        $CryptoIndicator->_saveIndicatorInformations("UPDATE indicators_krypto SET data_indicators=:data_indicators, title_indicators=:title_indicators
                                            WHERE key_graph=:key_graph AND symbol_indicators=:symbol_indicators AND key_indicators=:key_indicators",
                                            [
                                              'key_graph' => $_POST['container'],
                                              'symbol_indicators' => $_POST['indicator'],
                                              'title_indicators' => $title,
                                              'key_indicators' => $_POST['key'],
                                              'data_indicators' => json_encode($dataSaved)
                                            ]);

    } else {
        // Insert indicator
        $CryptoIndicator->_saveIndicatorInformations("INSERT INTO indicators_krypto (key_graph, key_indicators, symbol_indicators, data_indicators, title_indicators)
                                        VALUES (:key_graph, :key_indicators, :symbol_indicators, :data_indicators, :title_indicators)",
                                        [
                                          'key_graph' => $_POST['container'],
                                          'key_indicators' => $_POST['key'],
                                          'symbol_indicators' => $_POST['indicator'],
                                          'data_indicators' => json_encode($dataSaved),
                                          'title_indicators' => $title
                                        ]);


    }

    die(json_encode([
      'error' => 0,
      'container' => $_POST['container'],
      'indicator' => $_POST['indicator'],
      'indicator_key' =>  $_POST['key'],
      'data' => $dataSaved
    ]));

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
