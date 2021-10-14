<?php

/**
 * Change subscription settings
 *
 * This actions permit to admin to change SMTP settings in Krypto
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

try {

    // Check loggin & permission
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("Your are not logged", 1);
    }
    if (!$User->_isAdmin()) {
        throw new Exception("Error : Permission denied", 1);
    }

    if($App->_isDemoMode()) throw new Exception("App currently in demo mode", 1);

    // Check data available
    if (empty($_POST)) {
        throw new Exception("Error : Args not valid", 1);
    }

    $ChangedFeatures = [];
    foreach ($App->_getFeaturesAllowedFree() as $features => $val) {
      $ChangedFeatures[$features] = (isset($_POST['kr-adm-chk-feature-'.$features]) && $_POST['kr-adm-chk-feature-'.$features] == "on" ? '1' : '0');
    }


    // Save krypto subscription
    $App->_saveSubscription(
      (isset($_POST['kr-adm-chk-subscriptionenabled']) && $_POST['kr-adm-chk-subscriptionenabled'] == "on" ? '1' : '0'), // Subscription enable
      (isset($_POST['kr-adm-chk-enablefreetrial']) && $_POST['kr-adm-chk-enablefreetrial'] == "on" ? '1' : '0'), // If krypto allow to make free trial
      (isset($_POST['kr-adm-freetrialduration']) && is_numeric($_POST['kr-adm-freetrialduration']) && intval($_POST['kr-adm-freetrialduration']) >= 0 ? $_POST['kr-adm-freetrialduration'] : '14'),
      str_replace('<br />', '<br>', nl2br($_POST['kr-adm-premiumfeatures'])),
      $ChangedFeatures
    );



    // Return success message
    die(json_encode([
      'error' => 0,
      'msg' => 'Done',
      'title' => 'Success'
    ]));

} catch (\Exception $e) { // If throw exception, return error message
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
