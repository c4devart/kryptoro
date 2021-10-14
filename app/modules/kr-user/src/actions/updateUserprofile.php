<?php

/**
 * Change user infos action
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

try {

    // Load app modules
    $App = new App(true);
    $App->_loadModulesControllers();

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("User not logged", 1);
    }

    $Lang = new Lang($User->_getLang(), $App);

    if(!isset($_POST['kr_prof_u']) || (!$User->_isAdmin() && $_POST['kr_prof_u'] != $User->_getUserID(true))){
      throw new Exception("Error : Permission denied", 1);
    }

    $adminAction = false;
    if($User->_isAdmin() && $_POST['kr_prof_u'] != $User->_getUserID(true)){
      $User = new User(App::encrypt_decrypt('decrypt', $_POST['kr_prof_u']));
      $adminAction = true;
    }

    if(isset($_POST['kr-user-name']) && !empty($_POST['kr-user-name'])){
      $User->_setName(htmlspecialchars($_POST['kr-user-name'], ENT_QUOTES, 'UTF-8'));
    }

    if($User->_getOauth() == "standard" && isset($_POST['kr-user-pwd']) && !empty($_POST['kr-user-pwd'])){
      if(!isset($_POST['kr-user-pwd-repeat']) || $_POST['kr-user-pwd-repeat'] != $_POST['kr-user-pwd']) throw new Exception("Password not match", 1);
      $User->_setPassword($_POST['kr-user-pwd']);
    }

    if($User->_getOauth() == "standard" && isset($_POST['kr-user-email']) && !empty($_POST['kr-user-email']) && $_POST['kr-user-email'] != $User->_getEmail()){
      $User->_setEmail(htmlspecialchars($_POST['kr-user-email'], ENT_QUOTES, 'UTF-8'));
    }

    if(isset($_POST['kr-user-language']) && !empty($_POST['kr-user-language'])){
      $User->_setLanguage($_POST['kr-user-language'], $Lang);
    }

    if(isset($_POST['kr-user-typechart']) && !empty($_POST['kr-user-typechart'])){
      if($_POST['kr-user-typechart'] == "tradingview") $User->_changeUserSettings('tradingview_chart_library_use', 'true');
      else $User->_changeUserSettings('tradingview_chart_library_use', 'false');
    }

    if(isset($_POST['kr-user-currency']) && !empty($_POST['kr-user-currency'])){
      $User->_setCurrency($_POST['kr-user-currency']);
    }

    if($adminAction){
      if(isset($_POST['kr-user-userstatus']) && ($_POST['kr-user-userstatus'] == 0 || $_POST['kr-user-userstatus'] == 1)){
        $User->_setStatus($_POST['kr-user-userstatus']);
      }

      if(isset($_POST['kr-user-adminlevel']) && ($_POST['kr-user-adminlevel'] == 0 || $_POST['kr-user-adminlevel'] == 1 || $_POST['kr-user-adminlevel'] == 2)){
        $User->_setAdmin($_POST['kr-user-adminlevel']);
      }

      if(isset($_POST['kr-user-adminuserpremium']) && isset($_POST['kr-user-adminuserpremiumexpiration'])){
        $dateExpiration = new DateTime($_POST['kr-user-adminuserpremiumexpiration']);

        $Charge = $User->_getCharge($App);

        $date = $Charge->_getTimestampTrialEnd();
        if($Charge->_activeAbo()) $date = $Charge->_getTimestampChargeEnd();

        $DateCreatedWithoutSecond = new DateTime('now');
        $DateCreatedWithoutSecond->setTimestamp($date);
        $DateCreatedWithoutSecond->setTime($DateCreatedWithoutSecond->format('H'), $DateCreatedWithoutSecond->format('i'), 0);

        if($DateCreatedWithoutSecond->getTimestamp() != $dateExpiration->getTimestamp()){
          if($_POST['kr-user-adminuserpremium'] == "free"){
              $dateExpiration->sub(new DateInterval('P'.$App->_getChargeTrialDay().'D'));
            $User->_setFreetrial($dateExpiration->getTimestamp());
          } else {
            $User->_setPremium($dateExpiration->getTimestamp());
          }
        }


      }
    }


    $User->_saveChange(!$adminAction);

    die(json_encode([
      'error' => 0,
      'reload' => !$adminAction,
      'msg' => $Lang->tr('Done !')
    ]));

} catch (Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
