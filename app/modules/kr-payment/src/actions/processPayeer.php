<?php

// j54cuY65zQJbSYYR
// zbMFQHF5ufeX3QmyRKp

/**
 * Process payment Fortumo
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

try {

    // Load app modules
    $App = new App(true);
    $App->_loadModulesControllers();



    if (!in_array($_SERVER['REMOTE_ADDR'], array('185.71.65.92', '185.71.65.189', '149.202.17.210'))) throw new Exception("Permission denied", 1);

    $_POST = json_decode('{"m_operation_id":"630437734",
    "m_operation_ps":"2609",
    "m_operation_date":"19.08.2018 21:52:46",
    "m_operation_pay_date":"19.08.2018 21:53:01",
    "m_shop":"630301017",
    "m_orderid":"123456",
    "m_amount":"0.00010000",
    "m_curr":"BTC",
    "m_desc":"VGVzdA==",
    "m_status":"success",
    "m_sign":"C2EC9DB00FFD51EC59F045FBEC03DDFB76C52F58DBC6CA1C6947FE5EBA366910",
    "summa_out":"0.00009905",
    "transfer_id":"630437865",
    "client_account":"P1003253466",
    "client_email":"hello@ovrley.com"}', true);

    if(empty($_POST)) throw new Exception("Access denied", 1);

    $Payeer = new Payeer($App);
    $Payeer->_checkPayment($_POST);

    die();
} catch (Exception $e) {
  var_dump($e->getMessage());
  error_log(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}


?>

// GET
{"m_operation_id":"630437734",
 "m_operation_ps":"2609",
 "m_operation_date":"19.08.2018 21:52:46",
 "m_operation_pay_date":"19.08.2018 21:53:01",
 "m_shop":"630301017",
 "m_orderid":"123456",
 "m_amount":"0.00010000",
 "m_curr":"BTC",
 "m_desc":"VGVzdA==",
 "m_status":"success",
 "m_sign":"C2EC9DB00FFD51EC59F045FBEC03DDFB76C52F58DBC6CA1C6947FE5EBA366910",
 "lang":"en"}


// POST
{"m_operation_id":"630437734",
"m_operation_ps":"2609",
"m_operation_date":"19.08.2018 21:52:46",
"m_operation_pay_date":"19.08.2018 21:53:01",
"m_shop":"630301017",
"m_orderid":"123456",
"m_amount":"0.00010000",
"m_curr":"BTC",
"m_desc":"VGVzdA==",
"m_status":"success",
"m_sign":"C2EC9DB00FFD51EC59F045FBEC03DDFB76C52F58DBC6CA1C6947FE5EBA366910",
"summa_out":"0.00009905",
"transfer_id":"630437865",
"client_account":"P1003253466",
"client_email":"hello@ovrley.com"}
