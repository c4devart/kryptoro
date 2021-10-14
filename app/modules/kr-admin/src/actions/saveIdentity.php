<?php

/**
 * Change payment settings
 *
 * This actions permit to admin to change payment settings in Krypto
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

    // Save payment in Krypto configuration
    $App->_saveIdentity([
      "identity_enabled" => isset($_POST['kr-adm-chk-identitysystem']) && $_POST['kr-adm-chk-identitysystem'] == "on",
      "identity_block_trade" => isset($_POST['kr-adm-chk-blocktrading']) && $_POST['kr-adm-chk-blocktrading'] == "on",
      "identity_block_deposit" => isset($_POST['kr-adm-chk-blockdeposit']) && $_POST['kr-adm-chk-blockdeposit'] == "on",
      "identity_block_withdraw" => isset($_POST['kr-adm-chk-blockwithdraw']) && $_POST['kr-adm-chk-blockwithdraw'] == "on",
      "identity_wizard_title" => (isset($_POST['identitywizardtitle']) ? $_POST['identitywizardtitle'] : ""),
      "identity_wizard_title" => (isset($_POST['kr-adm-identitywizardtitle']) ? $_POST['kr-adm-identitywizardtitle'] : ""),
      "identity_title" => (isset($_POST['kr-adm-identitytitle']) ? $_POST['kr-adm-identitytitle'] : ""),
      "identity_wizard_advertisement" => (isset($_POST['kr-adm-identitywizardadvertisement']) ? $_POST['kr-adm-identitywizardadvertisement'] : ""),
      "identity_start_button" => (isset($_POST['kr-adm-identitywizardbutton']) ? $_POST['kr-adm-identitywizardbutton'] : "")

    ]);

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
