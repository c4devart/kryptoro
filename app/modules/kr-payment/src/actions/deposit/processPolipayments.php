<?php

/**
 * Process payment Fortumo
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";

$App = new App(true);
$App->_loadModulesControllers();
$Polipayments = new Polipayments($App);
try {

    if(array_key_exists('Token', $_GET)){
      $paymentCheck = $Polipayments->_checkPayment($_GET["token"]);
    } elseif(array_key_exists('token', $_GET)){
      $paymentCheck = $Polipayments->_checkPayment($_GET["token"]);
    } else {
      throw new Exception("Error : Invalid return token, missing", 1);
    }

    die("<script>window.close();</script>");

} catch (Exception $e) {

  $infosError = json_decode($e->getMessage(), true);

  error_log(json_encode([
    'error' => 1,
    'msg' => $infosError['ErrorMessage']
  ]));

  ?>
  <!DOCTYPE html>
  <html lang="en" dir="ltr">
    <head>
      <meta charset="utf-8">
      <title static-title="<?php echo $App->_getAppTitle(); ?>">PoliPayments - <?php echo $App->_getAppTitle(); ?></title>
      <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
      <style media="screen">
        body {
          display: flex; flex-direction: column;
          align-items: center;
          padding: 15px;
          background: #f4f6f9;

        }
        h2 {
          margin: 32px 0px;
          width: 100%;
          text-align: center;
        }
        table {
          border-collapse: collapse;
          width: 100%;
          background: #fff;
          margin: 25px 0px;
          box-shadow: 0px 2px 5px #00000014;
        }
        table td {
          padding: 12px;
        }
        footer {
          display: flex; width: 100%;
          justify-content: space-between;
          align-items: center;
        }
      </style>

    </head>
    <body>

      <img style="max-width:300px; width:90%; max-height:300px;" src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" alt="">

      <h2><?php echo Polipayments::_getErrorSentense($infosError['TransactionStatusCode']); ?></h2>

      <?php if(!is_null($infosError['ErrorCode'])): ?>
        <div style="width:100%;box-sizing: border-box;padding:20px;background:red;color:#fff;text-align:center;">
          <?php echo $infosError['ErrorMessage']; ?>
        </div>
      <?php endif; ?>

      <table>
        <tr>
          <td>Payment reference</td>
          <td><?php echo $infosError['MerchantReference']; ?></td>
        </tr>
        <tr>
          <td>Amount</td>
          <td><?php echo $App->_formatNumber($infosError['PaymentAmount'], 2).' '.$infosError['CurrencyCode']; ?></td>
        </tr>
        <tr>
          <td>Currency name</td>
          <td><?php echo $infosError['CurrencyName']; ?></td>
        </tr>
      </table>

      <footer>
        <a onclick="window.close();" class="btn btn-grey btn-autowidth">Cancel my payment</a>
        <?php if(Polipayments::_retryPayment($infosError['TransactionStatusCode'])): ?>
          <a href="<?php echo $Polipayments->_getRetryPaymentURL($_GET['token']); ?>" class="btn btn-green btn-autowidth">Retry my payment</a>
        <?php endif; ?>
      </footer>

    </body>
  </html>
  <?php

}


?>
