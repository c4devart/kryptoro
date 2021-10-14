<?php

/**
 * MySQL Class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

class MySQL {

	/**
	 * SQL Host
	 * @var String
	 */
	private static $MYSQL_HOST 		= MYSQL_HOST;

	/**
	 * SQL User
	 * @var String
	 */
	private static $MYSQL_USER 		= MYSQL_USER;

	/**
	 * SQL Database
	 * @var String
	 */
	private static $MYSQL_DATABASE		= MYSQL_DATABASE;

	/**
	 * SQL Password
	 * @var String
	 */
	private static $MYSQL_PASSWD		= MYSQL_PASSWD;

	/**
	 * SQL Port
	 * @var Int
	 */
	private static $MYSQL_PORT 		= MYSQL_PORT;

	/**
	 * Last req
	 * @var Object
	 */
	private static $LAST_REQ = null;

	/**
	 * PDO BDD
	 * @var PDO
	 */
	protected static $bdd = null;

	protected function __construct() {}
  protected function __clone() {}

	/**
	 * Get SQL Connexion PDF
	 * @return PDO         	PDO Connexion
	 */
	public static function getSqlConnexion(){
		// Check if bdd is not saved in local
		if (self::$bdd === null){
			try {
				// Init BDD
			  self::$bdd = new PDO('mysql:host='.self::$MYSQL_HOST.';port='.self::$MYSQL_PORT.';dbname='.self::$MYSQL_DATABASE, self::$MYSQL_USER, self::$MYSQL_PASSWD, array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
			} catch (Exception $e) {
			  throw new Exception($e->getMessage(), 1);
			  die();
			}
		}
		return self::$bdd;
	}

	/**
	 * Fetch data in database
	 * @param  String          $query SQL Query ("SELECT * FROM ... WHERE ...")
	 * @param  Array          $def   SQL Def ['id_key' => 'xxxx', ...]
	 *
	 * @return Array                SQL Result
	 */
	public static function querySqlRequest($query, $def = []){
		$req = self::getSqlConnexion()->prepare($query);
		$req->execute($def);
		$r = $req->fetchAll(\PDO::FETCH_ASSOC);
		$req->closeCursor();
		return $r;
	}

	/**
	 * Count SQL
	 * @param  String          $query SQL Query ("SELECT * FROM ... WHERE ...")
	 * @param  Array          $def   SQL Def ['id_key' => 'xxxx', ...]
	 *
	 * @return Int                 Row counted
	 */
	public static function countSqlRequest($query, $def = []){
		$req = self::getSqlConnexion()->prepare($query);
		
	
		$req->execute($def);
		$r = $req->rowCount();
		$req->closeCursor();
		return $r;
	}

	/**
	 * Execute SQL Request (INSERT, UPDATE, DELETE, ...)
	 * @param  String          $query SQL Query ("SELECT * FROM ... WHERE ...")
	 * @param  Array          $def   SQL Def ['id_key' => 'xxxx', ...]
	 *
	 * @return Boolean                True = SQL Request passsed, False = Fail SQL
	 */
	public static function execSqlRequest($query, $def = []){
		$req= self::getSqlConnexion()->prepare($query);
		
		$status = $req->execute($def);
		
		$req->closeCursor();
		return $status;
	}

	/**
	 * Get last error detect in SQL PDO
	 * @return String       PDO Error
	 */
	public function getLastError(){
		return $this->LAST_REQ->errorInfo();
	}

}

?>
