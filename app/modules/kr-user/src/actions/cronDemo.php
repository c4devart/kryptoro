<?php

/**
 * Cron demo action
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

    if(!$App->_isDemoMode()) throw new Exception("Error : App not in demo mode", 1);

    $Sql = new MySQL();
    foreach ($Sql->querySqlRequest("SELECT * FROM user_krypto WHERE created_date_user < :created_date_user",
                                    [
                                      'created_date_user' => time() - 3600
                                    ]) as $key => $value) {


      $r = $Sql->execSqlRequest("DELETE FROM visits_krypto WHERE id_user=:id_user", ['id_user' => $value['id_user']]);
      $r = $Sql->execSqlRequest("DELETE FROM charges_krypto WHERE id_user=:id_user", ['id_user' => $value['id_user']]);
      $r = $Sql->execSqlRequest("DELETE FROM dashboard_krypto WHERE id_user=:id_user", ['id_user' => $value['id_user']]);
      $r = $Sql->execSqlRequest("DELETE FROM graph_krypto WHERE id_user=:id_user", ['id_user' => $value['id_user']]);
      $r = $Sql->execSqlRequest("DELETE FROM notification_center_krypto WHERE id_user=:id_user", ['id_user' => $value['id_user']]);
      $r = $Sql->execSqlRequest("DELETE FROM notification_krypto WHERE id_user=:id_user", ['id_user' => $value['id_user']]);
      $r = $Sql->execSqlRequest("DELETE FROM top_list_krypto WHERE id_user=:id_user", ['id_user' => $value['id_user']]);
      $r = $Sql->execSqlRequest("DELETE FROM watching_krypto WHERE id_user=:id_user", ['id_user' => $value['id_user']]);
      $r = $Sql->execSqlRequest("DELETE FROM user_krypto WHERE id_user=:id_user", ['id_user' => $value['id_user']]);
    }

} catch (Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
