<?php

try {

  if(empty($_POST) || empty($_POST['sql_host']) || empty($_POST['sql_port']) || empty($_POST['sql_user']) || empty($_POST['sql_database_name'])) throw new Exception("Fields missing", 1);

  $bdd = new PDO('mysql:host='.$_POST['sql_host'].';port='.$_POST['sql_port'].';dbname='.$_POST['sql_database_name'], $_POST['sql_user'], $_POST['sql_password'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));

  die(json_encode([
    'error' => 0,
    'msg' => 'Connexion successful'
  ]));

} catch (Exception $e) {
  die(json_encode([
    "error" => 1,
    "msg" => $e->getMessage()
  ]));
}

?>
