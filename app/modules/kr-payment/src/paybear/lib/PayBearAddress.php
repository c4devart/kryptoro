<?php
include_once 'base_model.php';


/**
 * Cryptocurrencies addresses
 * Class PayBearAddress
 */
class PayBearAddress extends \base_model
{
    public $tableName;

    public function  __construct() {
        $this->tableName = parent::table_name();

        parent::__construct();
    }

    public function table_name() {
        return $this->tableName;
    }

    public function install_table() {

        $db = $this->getDB();

        $check = "SHOW TABLES LIKE '" . $this->tableName . "'";

        $q = $db->prepare($check);
        $q->execute();
        $result = $q->fetchAll();

        if (!empty($result)) {
            echo "Table already exist";
        } else {

        $sql = "CREATE TABLE ". $this->tableName ."(
            id int NOT NULL auto_increment PRIMARY KEY,                
            order_id varchar (255) NOT NULL,
            invoice varchar (255) NOT NULL,                
            address varchar (255) NOT NULL,
            crypto varchar (255) NOT NULL,
            updated_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP NOT NULL            
            )";

        $q = $db->prepare($sql);
        $q->execute();

        echo 'Table ' . $this->tableName . " has been installed successfully";
        echo '<br/>';

        }
    }

}