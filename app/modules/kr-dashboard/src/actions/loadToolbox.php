<?php

/**
 * Load toolbox data
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

  $App = new App(true);
  $App->_loadModulesControllers();

  $User = new User();
  if (!$User->_isLogged()) {
      throw new Exception("Permission denied", 1);
  }

  die(json_encode([
    'error' => 0,
    'configuration' => DashboardToolbox::_getConfigurationItem('line'),
    'palette_color' => DashboardToolbox::_getColorAvailableList()
  ]));

} catch (\Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
