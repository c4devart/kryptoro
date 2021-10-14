<?php

class Install extends MySQL {

  private $states = ["welcome", "infos", "check_file", "check_sql", "cronexec", "finish"];

  public function __construct(){

  }

  public function _getStates(){
    if(empty($_GET['s']) || !in_array($_GET['s'], $this->states)) return $this->states[0];
    return $_GET['s'];
  }

  public function _loadPage(){
    require("app/views/".$this->_getStates().".php");
  }

  public function _getBack(){
    $pos = array_search($this->_getStates(), $this->states);
    if($pos == 0) return null;
    return "?s=".$this->states[$pos - 1];
  }

  public function _getForward(){
    $pos = array_search($this->_getStates(), $this->states);
    if($pos == count($this->states) - 1) return null;
    return "?s=".$this->states[$pos + 1];
  }

  public function _getRefresh(){
    return "?s=".$this->_getStates();
  }

  public function _getFileCheck(){
    $fileOk = [];
    $fileMissing = [];
    foreach (array_map('str_getcsv', file('assets/file.csv')) as $key => $line) {
      if(!file_exists('../'.$line[0])) $fileMissing[] = $line[0];
      else $fileOk[] = $line[0];
    }
    return [
      'valid' => count($fileMissing) == 0,
      'missing' => $fileMissing,
      'done' => $fileOk
    ];

  }

  // public function _checkSql($Sql){
  //   $structure = json_decode(file_get_contents('assets/sql/structure.sql'), true);
  //   $tableList = [];
  //   foreach ($structure as $key => $table) {
  //     if($table['type'] == "table"){
  //       $v = parent::querySqlRequest("SHOW TABLES LIKE :table", ['table' => $table['name']]);
  //       if(count($v) == 0){
  //         $tableList[] = [
  //           'name' => $table['name'],
  //           'structure' => $table['structure']
  //         ];
  //       }
  //     }
  //   }
  //
  //   return $tableList;
  // }

  public function _processSql(){

    // $sqlRequest = "";
    //
    // foreach ($structureGlobal as $table) {
    //   $sqlRequest .= "
    //
    //   CREATE TABLE `".$table['name']."` (";
    //
    //   foreach ($table['structure'] as $structure) {
    //     $sqlRequest .= " `".$structure['name']."` ".$structure['type']."".($structure['length'] != "" ? "(".$structure['length'].")" : "")." ".($structure['type'] != "text" ? "NOT NULL" : "")." ".($structure['default'] != "" ? "DEFAULT '".$structure['default']."'" : "").",";
    //   }
    //
    //   $sqlRequest = substr($sqlRequest, 0, -1);
    //
    //   $sqlRequest .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    //
    // }
    //
    // foreach ($structureGlobal as $table) {
    //   foreach ($table['structure'] as $structure) {
    //     if($structure['primary']){
    //       $sqlRequest .= "
    //
    //         ALTER TABLE `".$table['name']."` ADD PRIMARY KEY (`".$structure['name']."`);
    //
    //         ";
    //     }
    //
    //     if($structure['auto_increment']){
    //       $sqlRequest .= "
    //
    //       ALTER TABLE `".$table['name']."`
    //         MODIFY `".$structure['name']."` int(11) NOT NULL AUTO_INCREMENT;
    //
    //         ";
    //     }
    //   }
    // }
    //
    // $v = parent::execSqlRequest($sqlRequest);

    $sqlStructure = file_get_contents('assets/sql/krypto.sql');
    $status = parent::execSqlRequest($sqlStructure);

    //if(!$status) throw new Exception("Error : Fail to create database structure", 1);
    //
    return $status;

  }

  public function _post($state){
    if(empty($_POST)) return true;
    $_SESSION[$state] = $_POST;
    if($state == "bdd") return $this->_generateBDD();
    if($state == "admin") return $this->_createAdmin();
    return true;
  }

  public function _getListPageCalled(){

    return [
      'app/src/CryptoApi/actions/SyncExchanges.php' => 'Exchanges sync',
      'app/src/CryptoApi/actions/SyncCoin.php' => 'Coins sync',
      'app/src/App/actions/cronCleanCache.php' => 'Clear cache'
    ];

  }


}

?>
