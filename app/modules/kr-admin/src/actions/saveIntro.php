<?php

/**
 * Change General settings
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

    $introSelected = [];
    for ($i=0; $i < 20; $i++) {
      if(isset($_POST['intro_target_'.$i]) && !empty($_POST['intro_target_'.$i]) && $_POST['intro_target_'.$i] != "none"){
        $introSelected[$i] = [
          'title' => $_POST['intro_title_'.$i],
          'text' => $_POST['intro_text_'.$i],
          'attach' => $_POST['intro_target_'.$i],
        ];
      }
    }

    $App->_saveIntroSteps((array_key_exists('kr-adm-chk-introenable', $_POST) && $_POST['kr-adm-chk-introenable'] == "on" ? 1 : 0), json_encode($introSelected));
    $App->_saveNewspopup((array_key_exists('kr-adm-chk-newspopup', $_POST) && $_POST['kr-adm-chk-newspopup'] == "on" ? 1 : 0),
                         $_POST['kr-adm-newspopuptitle'],
                         $_POST['kr-adm-newspopupvideo'],
                         $_POST['kr-adm-newspopuptext'],
                         (array_key_exists('kr-adm-chk-newspopupadvert', $_POST) && $_POST['kr-adm-chk-newspopupadvert'] == "on" ? true : false));

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
