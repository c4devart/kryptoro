<?php

/**
 * Google Oauth callback
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

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

// Init user object
$User = new User();

try {

    // Init GoogleOauth object
    $FacebookOauth = new FacebookOauth($User);

    // Parse respond
    $rspond = $FacebookOauth->_parseCallback();
    if ($rspond === true || $rspond == 1) {
      // Redirect to dashboard
      header('Location: '.APP_URL.'/dashboard.php');
    } else {
      var_dump($rspond);
    }
} catch (Exception $e) {
  header('Location: '.APP_URL.'/?rmsg='.base64_encode($e->getMessage()).'&rtime='.time().'&s=');
    var_dump($e->getMessage());
}

?>
