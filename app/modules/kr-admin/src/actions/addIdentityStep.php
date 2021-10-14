<?php

/**
 * Add RSS Feed
 *
 * This actions permit to admin to add rss feed to krypto
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

    $Identity = new Identity($User);
    if($_POST['step_type'] == "form"){
      $stepFormList = [];
      foreach ($_POST['kr-identity-form-s'] as $key => $nameStepForm) {
        if(strlen($nameStepForm) == 0) continue;
        $stepFormList[] = ['title' => $nameStepForm, 'type' => 'text', 'placeholder' => $_POST['kr-identity-form-sample'][$key]];
      }
      $Identity->_addIdentityStepForm($_POST['step_name'], $stepFormList);
    } else {
      $Identity->_addIdentityStep($_POST['step_name'], $_POST['step_descrption'], $_POST['step_type'],
                                  (isset($_POST['kr-adm-chk-webcamstep']) && $_POST['kr-adm-chk-webcamstep'] ? "on" : "0"), $_POST['step_webcam_ratio']);
    }


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
