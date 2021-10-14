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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("Error : User not logged", 1);
    }

    // Init lang object
    $Lang = new Lang($User->_getLang(), $App);

    // Init notifiction center object
    $NotificationCenter = new NotificationCenter($User);

    // Get list notifications
    $listNotifications = [];
    foreach ($NotificationCenter->_getListNotification() as $notification) {
        $listNotifications[$notification->_getNotificationID()] = $notification->_getNotification($Lang);
    }

    // Define all notifications as seend
    $NotificationCenter->_setAllSeen();

    // Send return value (notifications list)
    die(json_encode([
      'error' => 0,
      'notifications' => json_encode($listNotifications)
    ]));

} catch (Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
