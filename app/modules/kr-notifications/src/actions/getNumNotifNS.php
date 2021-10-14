<?php

/**
 * Get notification list
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

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
      die(json_encode([
        'error' => 99,
        'msg' => "Error : User not logged"
      ]));
    }

    // Init notification center object
    $NotificationCenter = new NotificationCenter($User);

    $Manager_notification = 0;
    if($User->_isManager()){
      $Manager = new Manager($App);
      $Manager_notification = $Manager->_getNumberManagerNotification();
    }

    $IdentityStatus = [
      "class" => "kr-identity-in-verification",
      "icon" => '<svg class="lnr lnr-undo"><use xlink:href="#lnr-undo"></use></svg>'
    ];

    $Identity = new Identity($User);

    if($Identity->_identityVerified()){
      $IdentityStatus = [
        "class" => "kr-identity-verified",
        "icon" => '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 26 26" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 26 26"> <path d="m.3,14c-0.2-0.2-0.3-0.5-0.3-0.7s0.1-0.5 0.3-0.7l1.4-1.4c0.4-0.4 1-0.4 1.4,0l.1,.1 5.5,5.9c0.2,0.2 0.5,0.2 0.7,0l13.4-13.9h0.1v-8.88178e-16c0.4-0.4 1-0.4 1.4,0l1.4,1.4c0.4,0.4 0.4,1 0,1.4l0,0-16,16.6c-0.2,0.2-0.4,0.3-0.7,0.3-0.3,0-0.5-0.1-0.7-0.3l-7.8-8.4-.2-.3z"/> </svg>'
      ];
    }

    if($Identity->_identityWizardNotStarted()){
      $IdentityStatus = [
        "class" => "kr-identity-not-verified",
        "icon" => '<svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>'
      ];
    }

    // Return message with notification count
    die(json_encode([
      'error' => 0,
      'notifications' => count($NotificationCenter->_getListNotification(500, true)),
      'notifications_number_unread' => $NotificationCenter->_getNumberNotificationUnseen(),
      'manager_notifications' => $Manager_notification,
      'identity_status' => $IdentityStatus
    ]));

} catch (Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
