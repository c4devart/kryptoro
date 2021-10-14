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

$BankTransfertError = null;
try {

  // Load app module
  $App = new App(true);
  $App->_loadModulesControllers();

  // Check if user is logged
  $User = new User();
  if(!$User->_isLogged()) die('User not logged');

  $Lang = new Lang($User->_getLang(), $App);

} catch (Exception $e) {
  $BankTransfertError = $e->getMessage();
}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title static-title="<?php echo $App->_getAppTitle(); ?>"><?php echo $App->_getAppTitle(); ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/app/modules/kr-payment/statics/css/banktransfert.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">

    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/site.webmanifest">
    <link rel="shortcut icon" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon.ico">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="<?php echo APP_URL; ?>/assets/img/icons/favicon/browserconfig.xml">
    <script src="https://cdn.linearicons.com/free/1.0.0/svgembedder.min.js"></script>

    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/bower/dropzone/dist/min/dropzone.min.css">
    <script src="<?php echo APP_URL; ?>/assets/bower/jquery/dist/jquery.min.js" charset="utf-8"></script>
    <script src="<?php echo APP_URL; ?>/assets/bower/dropzone/dist/min/dropzone.min.js" charset="utf-8"></script>


  </head>
  <body class="kr-nbankwire-contract" hrefapp="<?php echo APP_URL; ?>">
    <header>
      <img src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" alt="">
    </header>
    <h2><?php echo $Lang->tr('Cryptocurrency deposit agreement'); ?></h2>
    <div>
      <?php echo nl2br($App->_getDepositMessage()); ?>
    </div>
    <footer>
      <input type="button" onclick="window.close();" class="btn btn-big btn-autowidth btn-red" name="" value="<?php echo $Lang->tr('Decline'); ?>">
      <a class="btn btn-big btn-autowidth btn-green" href="<?php echo APP_URL; ?>/app/modules/kr-payment/views/banktransfert.php?s=new_confirm"><?php echo $Lang->tr('Agree'); ?></a>
    </footer>
  </body>

</html>
