<?php
include_once 'base_model.php';

/**
 * Example CMS Order
 * Class CmsOrder
 */
class CmsOrder extends \base_model
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
                increment_id varchar (255) NOT NULL,
                order_total decimal (20,2) NOT NULL,
                fiat_currency varchar (255) NOT NULL,
                fiat_sign varchar (255) NOT NULL,
                status varchar (255),
                updated_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP NOT NULL)";

            $q = $db->prepare($sql);
            $q->execute();

            echo 'Table ' . $this->tableName . " has been installed successfully";
            echo '<br/>';
        }

    }

    public function findByIncrementId($id) {

        $sql = "SELECT * FROM ".self::table_name()." WHERE increment_id = ?";
        $q = $this->db->prepare($sql);
        $q->execute(array($id));
        $q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
        $object = $q->fetchObject('CmsOrder');


        return $object;
    }

}