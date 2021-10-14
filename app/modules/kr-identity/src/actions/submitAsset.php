<?php

/**
 * Add indicator to graph action
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

try {

    // Load app modules
    $App = new App(true);
    $App->_loadModulesControllers();

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("User is not logged", 1);
    }

    $Identity = new Identity($User);

    if(empty($_FILES) || empty($_POST) || !isset($_FILES['file']) || !isset($_POST['step'])){

      if(array_key_exists('identity_form_step_0', $_POST)){
        $fieldList = [];
        foreach ($_POST as $key => $value) {
          if(strpos($key, 'identity_form_step_') !== false){
            $fieldList[str_replace('identity_form_step_', '', $key)] = $value;
          }
        }

        $Identity->_postAssetForm(App::encrypt_decrypt('decrypt', $_POST['step']), $fieldList, $App);

      } else {
        if(!isset($_POST['camera']) || !isset($_POST['step'])) throw new Exception("Error : Permission denied", 1);
        $Identity->_postAssetCamera($_POST['step'], $_POST['camera'], $App, $_POST['document_type']);
      }



    } else {
      $Identity->_postAsset($_POST['step'], $_FILES['file'], $App, $_POST['document_type']);
    }
    $Identity->_changeStatus(0, null);


} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
