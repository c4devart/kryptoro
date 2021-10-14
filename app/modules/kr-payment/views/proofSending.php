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

try {

  // Load app module
  $App = new App(true);
  $App->_loadModulesControllers();

  // Check if user is logged
  $User = new User();
  if(!$User->_isLogged()) header('Location: '.APP_URL);

  if(empty($_GET) || !isset($_GET['s'])) throw new Exception("Permission denied", 1);

  $Manager = new Manager($App);

  $proofPaymentDecrypted = App::encrypt_decrypt('decrypt', $_GET['s']);
  $proofPaymentDecrypted = explode('-', $proofPaymentDecrypted);
  if(count($proofPaymentDecrypted) != 2 || $proofPaymentDecrypted[0] != "proof") throw new Exception("Permission denied", 1);
  $proofPaymentDecrypted = $proofPaymentDecrypted[1];

  $infosProofPayment = $Manager->_getPaymentProofInfos($proofPaymentDecrypted);
  if($infosProofPayment['id_user'] != $User->_getUserID()) throw new Exception("Permission denied", 1);

  $InfosPaymentComplete = $Manager->_getPaymentInfos($infosProofPayment['id_deposit_history']);

} catch (Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title static-title="<?php echo $App->_getAppTitle(); ?>"><?php echo $App->_getAppTitle(); ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/app/modules/kr-payment/statics/css/proofsending.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">

    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/site.webmanifest">
    <link rel="shortcut icon" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon.ico">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="<?php echo APP_URL; ?>/assets/img/icons/favicon/browserconfig.xml">

  </head>
  <body class="kr-proofsending" hrefapp="<?php echo APP_URL; ?>" kr-proof-s="<?php echo $_GET['s']; ?>">
    <header>
      <img src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" alt="">
    </header>
    <div>
      <h3>Payment <?php echo $InfosPaymentComplete['ref_deposit_history']; ?></h3>
      <?php if(strlen($infosProofPayment['url_deposit_history_proof']) < 5): ?>
        <span>You need to send a payment proof for validate the payment</span>
        <p><?php echo nl2br($infosProofPayment['reason__deposit_history_proof']); ?></p>
      <?php else: ?>
        <div class="kr-proofsending-sended">
          <svg class="lnr lnr-checkmark-circle"><use xlink:href="#lnr-checkmark-circle"></use></svg>
          <span>Your proof has been sent.</span>
        </div>
      <?php endif; ?>
    </div>
    <?php if(strlen($infosProofPayment['url_deposit_history_proof']) < 5): ?>
      <section class="kr-proofsending-dz">
        <div class="kr-proofsending-dz-progress">
          <div></div>
        </div>
        <svg class="lnr lnr-cloud-upload"><use xlink:href="#lnr-cloud-upload"></use></svg>
        <span>Please, upload your proof here (by dragging file here, or clicking here)</span>
      </section>
    <?php endif; ?>
  </body>

  <script src="<?php echo APP_URL; ?>/assets/bower/jquery/dist/jquery.min.js" charset="utf-8"></script>

  <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/bower/dropzone/dist/min/dropzone.min.css">
  <script src="<?php echo APP_URL; ?>/assets/bower/chosen/chosen.jquery.min.js" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/assets/bower/dropzone/dist/min/dropzone.min.js" charset="utf-8"></script>

  <script src="<?php echo APP_URL; ?>/app/modules/kr-payment/statics/js/proofsending.js" charset="utf-8"></script>

  <script src="https://cdn.linearicons.com/free/1.0.0/svgembedder.min.js"></script>
</html>
