<?php

/**
 * Charge plan selected view
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";

try {
  $App = new App(true);
  $App->_loadModulesControllers();

  // Check if user is logged
  $User = new User();
  if(!$User->_isLogged()) die('User not logged');

  $Lang = new Lang($User->_getLang(), $App);

  if(empty($_GET) || !isset($_GET['cr']) && !isset($_GET['t'])) throw new Exception("Wrong arguments", 1);

  if(!array_key_exists('time', $_GET) || empty($_GET['time'])){
    $urlAg = "";
    foreach ($_GET as $key => $value) {
      $urlAg .= (strlen($urlAg) == 0 ? "?" : "&").$key."=".$value;
    }
    header('Location: directdeposit.php'.$urlAg."&time=".time());
  }

  $BlockExplorer = new BlockExplorer($App);
  $DepositAddressList = $BlockExplorer->_getDepositAddress();
  if(!array_key_exists($_GET['cr'], $DepositAddressList)) throw new Exception("Error : Wallet not found", 1);
  $DepositAddressList = $DepositAddressList[$_GET['cr']];

  $Widthdraw = new Widthdraw($User);
  $WithdrawListAssociate = $Widthdraw->_getListWidthdraw();

  $addressFromList = [];
  foreach ($WithdrawListAssociate as $key => $value) {
    if($value['type'] != "cryptocurrencies" || !array_key_exists('cryptocurrency_name', $value['infos']) || $value['infos']['cryptocurrency_name'] != $DepositAddressList->_getSymbol()) continue;
    $addressFromList[$value['infos']['address']] = $value;
  }

  $TransactionDone = $BlockExplorer->_getTransactionUserTime($addressFromList, $_GET['time']);

  $Addresslist = $BlockExplorer->_getDepositAddress();

} catch (Exception $e) {
  ?>
  <div style="width:100%;box-sizing: border-box;padding:20px;background:red;color:#fff;text-align:center;">
    <?php echo $e->getMessage(); ?>
  </div>
  <?php
  die();
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $Lang->tr('Deposit'); ?> - <?php echo $_GET['cr']; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
  </head>
  <body>
    <section class="kr-direct-ppsec">
      <header>
        <span><?php echo $Lang->tr('Deposit'); ?> <?php echo $DepositAddressList->_getSymbol(); ?></span>
      </header>
      <div class="kr-direct-qrcode">
        <div class="kr-direct-svg">
          <?php echo QRcode::svg($DepositAddressList->_getAddress()); ?>
        </div>
        <input type="text" readonly name="" value="<?php echo $DepositAddressList->_getAddress(); ?>">
        <span><?php echo $Lang->tr('Scan the QRcode bellow and send the amount you want.'); ?></span>
      </div>
    </section>

    <?php
    if(count($TransactionDone) > 0){
      foreach ($TransactionDone as $key => $value) {
        $dataPayment = json_decode($value['data_block_exp_tx'], true);
        ?>
        <section class="kr-direct-transfert-s">
          <ul>
            <li>
              <label><?php echo $Lang->tr('Date received'); ?> : </label>
              <span> <?php echo date('d/m/Y H:i:s', $value['date_block_exp_tx']); ?></span>
            </li>
            <li>
              <label><?php echo $Lang->tr('Transaction hash'); ?> : </label>
              <span> <?php echo $value['tx_block_exp_tx']; ?></span>
            </li>
            <li>
              <label><?php echo $Lang->tr('From'); ?> : </label>
              <span> <?php echo $dataPayment['from']; ?></span>
            </li>
            <li>
              <label><?php echo $Lang->tr('To'); ?> : </label>
              <span> <?php echo $dataPayment['to']; ?></span>
            </li>
            <li>
              <label><?php echo $Lang->tr('Amount sent'); ?> : </label>
              <span> <?php echo $App->_formatNumber($dataPayment['value'], 8).' '.$dataPayment['symbol']; ?></span>
            </li>
            <li>
              <label><?php echo $Lang->tr('Fees'); ?> : </label>
              <span> <?php echo $App->_formatNumber($dataPayment['value'], 8).' '.$dataPayment['symbol']; ?></span>
            </li>
            <li>
              <label><?php echo $Lang->tr('Amount received'); ?> : </label>
              <span> <?php echo $App->_formatNumber($dataPayment['value'], 8).' '.$dataPayment['symbol']; ?></span>
            </li>
            <li>
              <label><?php echo $Lang->tr('Confirmation number'); ?> : </label>
              <span> <?php echo $dataPayment['confirmations'].' / '.$Addresslist[$dataPayment['symbol']]->_getNbVerification(); ?></span>
            </li>
          </ul>
          <div>
            <?php if($value['status_block_exp_tx'] == "0"): ?>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32" fill="#000">
                <path opacity=".25" d="M16 0 A16 16 0 0 0 16 32 A16 16 0 0 0 16 0 M16 4 A12 12 0 0 1 16 28 A12 12 0 0 1 16 4"/>
                <path d="M16 0 A16 16 0 0 1 32 16 L28 16 A12 12 0 0 0 16 4z">
                  <animateTransform attributeName="transform" type="rotate" from="0 16 16" to="360 16 16" dur="0.8s" repeatCount="indefinite" />
                </path>
              </svg>
              <span><?php echo $Lang->tr('Waiting the confirmations ('.$dataPayment['confirmations'].' / '.$Addresslist[$dataPayment['symbol']]->_getNbVerification().') ...'); ?></span>
            <?php else: ?>
              <label><?xml version='1.0' encoding='iso-8859-1'?>
              <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 26 26" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 26 26">
                <path d="m.3,14c-0.2-0.2-0.3-0.5-0.3-0.7s0.1-0.5 0.3-0.7l1.4-1.4c0.4-0.4 1-0.4 1.4,0l.1,.1 5.5,5.9c0.2,0.2 0.5,0.2 0.7,0l13.4-13.9h0.1v-8.88178e-16c0.4-0.4 1-0.4 1.4,0l1.4,1.4c0.4,0.4 0.4,1 0,1.4l0,0-16,16.6c-0.2,0.2-0.4,0.3-0.7,0.3-0.3,0-0.5-0.1-0.7-0.3l-7.8-8.4-.2-.3z"/>
              </svg>
              <?php echo $Lang->tr('Payment completed'); ?></label>
            <?php endif; ?>
          </div>
        </section>
        <?php
      }
    } else {
      ?>
      <section class="kr-direct-loading">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32" fill="#000">
          <path opacity=".25" d="M16 0 A16 16 0 0 0 16 32 A16 16 0 0 0 16 0 M16 4 A12 12 0 0 1 16 28 A12 12 0 0 1 16 4"/>
          <path d="M16 0 A16 16 0 0 1 32 16 L28 16 A12 12 0 0 0 16 4z">
            <animateTransform attributeName="transform" type="rotate" from="0 16 16" to="360 16 16" dur="0.8s" repeatCount="indefinite" />
          </path>
        </svg>
        <span><?php echo $Lang->tr('Waiting the transaction ...'); ?></span>
      </section>

      <h2><?php echo $Lang->tr('My wallets'); ?></h2>
      <div class="kr-direct-alert"><?php echo $Lang->tr('You need to send from one of theses addresses bellow. Else if the transaction will not be received on your account.'); ?></div>
      <div class="kr-direct-infos">
        <span><?php echo $Lang->tr('You can add a wallet address direclty on your account settings (Withdraw / Wallet)'); ?></span>
      </div>
      <section class="">
        <ul>
          <?php
          foreach ($addressFromList as $key => $value) {
          ?>
            <li>
              <span><?php echo $value['infos']['address']; ?></span>
            </li>
          <?php } ?>
        </ul>
      </section>
    <?php } ?>
  </body>
  <script src="<?php echo APP_URL; ?>/assets/bower/jquery/dist/jquery.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>
  <script type="text/javascript">
    $(document).ready(function(){
      setTimeout(function(){
        location.reload();
      }, 9000);
    });
  </script>
  <style media="screen">
    body {
      background: #f4f6f9;
      padding: 18px;
    }

    body > section {
      background: #fff;
      border-radius: 2px;
      box-shadow: 0px 2px 5px #0000000d;
      margin-bottom: 18px;
    }

    body > section.kr-direct-ppsec {
      padding-bottom: 15px;
    }

    body > section > header {
      font-weight: bold;
      padding: 12px 12px 15px 12px;
      border-bottom: 1px solid #f4f6f9;
      font-size: 20px;
    }

    body > section > div.kr-direct-qrcode {
      display: flex; flex-direction: column;
      align-items: center;
    }

    body > section > div.kr-direct-qrcode > .kr-direct-svg > svg {
      width: 200px; height: 200px;
      max-height: 80%;
    }

    body > section > div.kr-direct-qrcode input[type="text"] {
      -moz-appearance    : none;
      -o-appearance      : none;
      -webkit-appearance : none;
      width              : 100%;
      max-width: 380px;
      padding            : 10px 12px;
      border             : 1px solid #e1e3e6;
      border-radius      : 2px;
      font-family        : 'Roboto',
                           sans-serif;
      font-size          : 15px;
      resize             : none;
      text-align: center;
      margin-bottom: 8px;
    }

    body > section > div.kr-direct-qrcode span {
      font-size: 12px;
      opacity: 0.65;
      font-weight: bold;
    }

    ul > li {
      padding: 15px;
      border-bottom: 1px solid #f4f6f9;
      font-size: 17px;
      font-weight: bold;
      display: flex; justify-content: center; align-items: center;
    }

    div.kr-direct-alert {
      background: #e86709;
      font-weight: bold;
      padding: 15px;
      color:#f4f6f9;
      border-radius: 2px;
      margin-bottom: 15px;
      text-align: center;
    }

    div.kr-direct-infos {
      font-weight: bold;
      padding: 5px 15px;
      color:#15af29;
      border-radius: 2px;
      margin-bottom: 15px;
      text-align: center;
    }

    section.kr-direct-loading {
      display: flex;
      flex-direction: column;
      align-items: center;

    }
    section.kr-direct-loading {
      padding: 20px 0px;
      font-weight: bold;
    }
    section.kr-direct-loading > svg {
      width: 30px; height: 30px;
      margin-bottom: 10px;
    }

    section.kr-direct-transfert-s > ul > li {
      display: block;
      font-weight: 100;
      font-size: 16px;
    }

    section.kr-direct-transfert-s > div {
      display: flex; flex-direction: column;
      align-items: center;
      padding: 25px 0px;
    }

    section.kr-direct-transfert-s > div > svg {
      margin-bottom: 12px;
    }

    section.kr-direct-transfert-s > div > label {
      display: flex; align-items: center;
      color:#15b315;
      font-weight: bold;
    }

    section.kr-direct-transfert-s > div > label > svg {
      width: 18px; height: 18px;
      margin-right: 8px;
      fill:#15b315;
    }
  </style>
</html>
