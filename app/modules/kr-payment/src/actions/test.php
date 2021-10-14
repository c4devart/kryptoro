<?php

/**
 * Check Fortumo payment
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

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("User not logged", 1);
    }



} catch (Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

$m_shop = '630301017';
$m_orderid = '1';
$m_amount = number_format(100, 2, '.', '');
$m_curr = 'USD';
$m_desc = base64_encode('KRP-56A56-VC456');

$m_key = 'rAZMtllFXKQA61gQ';

$arHash = array(
	$m_shop,
	$m_orderid,
	$m_amount,
	$m_curr,
	$m_desc
);


$arHash[] = $m_key;

$sign = strtoupper(hash('sha256', implode(':', $arHash)));


?>

<form method="post" action="https://payeer.com/merchant/">
<input type="hidden" name="m_shop" value="<?=$m_shop?>">
<input type="hidden" name="m_orderid" value="<?=$m_orderid?>">
<input type="hidden" name="m_amount" value="<?=$m_amount?>">
<input type="hidden" name="m_curr" value="<?=$m_curr?>">
<input type="hidden" name="m_desc" value="<?=$m_desc?>">
<input type="hidden" name="m_sign" value="<?=$sign?>">
<input type="submit" name="m_process" value="send" />
</form>
