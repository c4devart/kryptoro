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
  if(!$User->_isLogged()) die('User not logged');

  if(empty($_GET) || !isset($_GET['g'])) throw new Exception("Error : Wrong args", 1);


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
    <title></title>
    <style media="screen">
    html {
      margin: 0; padding: 0;
      height: 100%;
    }
      body {
        background: #f4f4f4;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        height: 100%;
      }
      body > img {
        width: 90%;
        max-width: 250px;
        margin-bottom: 50px;
      }
    </style>
    <script src="https://assets.fortumo.com/fmp/fortumopay.js" type="text/javascript"></script>
  </head>
  <body>
    <img src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" alt="">
    <a id="fmp-button" href="#" rel="<?php echo $App->_getFortumoServiceKey(); ?>\<?php echo $_GET['g']; ?>">
    <img src="https://assets.fortumo.com/fmp/fortumopay_150x50_red.png" width="150" height="50" alt="Mobile Payments by Fortumo" border="0" />
    </a>
  </body>
</html>
