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

  // Load app module
  $App = new App(true);
  $App->_loadModulesControllers();


  // Check if user is logged
  $User = new User();
  if(!$User->_isLogged()) die('User not logged');


  if(empty($_GET) || !isset($_GET['g']) && !isset($_GET['t'])) throw new Exception("Error : Wrong args", 1);

  if(!$App->_getBlockonomicsEnabled() || !in_array($_GET['cr'], $App->_getListBlockonomicsCurrencyAllowed())) throw new Exception("Blockonomics is not enable or symbol is not available", 1);

  $Blockonomics = new Blockonomics($App);
  $AddressDeposit = $Blockonomics->_generateNewPaymentAddress($User);

} catch (Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $App->_getAppTitle(); ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <style media="screen">
      body { background: #f4f6f9; display: flex; flex-direction: column; align-items: center; padding: 15px;}
      body > img {
        max-width: 200px;
        max-height: 200px;
        margin: 25px 0px;
      }
      body > h2 { font-size: 18px; text-align: center; }
      body > p {
        text-align: center;
        text-transform: uppercase;
        font-size: 13px;
      }


      body > section.kr-qrcode {
        width: 150px; height: 150px;
        max-width: 100%;
        margin-top: 35px;
        display: flex; justify-content: center; align-items: center;
        background: #fff;
        padding: 10px;
        border-radius: 3px;
        box-shadow: 0px 2px 5px rgba(0,0,0,0.1);
        margin-bottom: 10px;
      }

      body > section.kr-qrcode > img {
        width: 100%;
        user-select: none;
      }

      div.kr-credit-cryptocc-addrinp {
        display: flex; align-items: center;
        height: 40px;
        background: #fff;
        width: 80%;
        border-radius: 2px;
        box-shadow: 0px 2px 5px rgba(0,0,0,0.1);
        margin-top: 10px;
      }

      div.kr-credit-cryptocc-addrinp > input[type="text"] {
        flex:1;
        -moz-appearance    : none;
        -o-appearance      : none;
        -webkit-appearance : none;
        background: none;
        padding: 0px 10px;
        height: 40px;
        font-size: 17px;
        border: none;
        color:#252525;

        outline: none;
      }

      div.kr-credit-cryptocc-addrinp > div {
        width: 40px; height: 40px;
        min-width: 40px;
        display: flex; justify-content: center; align-items: center;
        cursor: pointer;
        background: rgba(0, 0, 0, 0.15);
        border-radius: 0px 2px 2px;
      }

      div.kr-credit-cryptocc-addrinp > div:hover {
        background: rgba(0, 0, 0, 0.2);
      }

      div.kr-credit-cryptocc-addrinp > div > svg {
        height: 19px; width: 19px;
        fill:#252525;
        opacity: 0.5;
      }
    </style>
  </head>
<body class="kr-dsl" hrefapp="<?php echo APP_URL; ?>">
    <section class="kr-notif-alt kr-ov-nblr">
    </section>
    <img src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" alt="">
    <h2>Send Bitcoin directly to your wallet.</h2>
    <p>You can deposit as much bitcoin as you want. The system will automatically detect the amount sent.</p>
    <section class="kr-qrcode">
      <img src="https://krypto.dev.ovrley.com/public/qrcode/<?php echo $AddressDeposit ?>.png" alt="">
    </section>
    <p>You can scan direclty the QRcode or write this address</p>
    <div class="kr-credit-cryptocc-addrinp">
      <input type="text" readonly name="" id="kr-deposit-addrinp" value="<?php echo $AddressDeposit; ?>">
      <div data-clipboard-target="#kr-deposit-addrinp">
        <svg class="lnr lnr-file-empty"><use xlink:href="#lnr-file-empty"></use></svg>
      </div>
    </div>
  </body>
  <script src="<?php echo APP_URL; ?>/assets/bower/jquery/dist/jquery.min.js" charset="utf-8"></script>
  <script src="https://cdn.linearicons.com/free/1.0.0/svgembedder.min.js"></script>
  <script src="<?php echo APP_URL; ?>/assets/bower/clipboard/dist/clipboard.min.js" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/assets/js/notifications.js" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/app/modules/kr-trade/statics/js/blockonomics.js" charset="utf-8"></script>
  <script type="text/javascript">
    blockonomicsCloseOM = true;
    $(document).ready(function(){
      ClipBoard = new ClipboardJS('[data-clipboard-target]');
      ClipBoard.on('success', function(e) {
        showAlert('Copied !', '');
        e.clearSelection();
      });
    });
  </script>
</html>
