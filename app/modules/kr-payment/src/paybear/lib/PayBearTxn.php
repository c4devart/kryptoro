<?php
include_once 'base_model.php';


/**
 * Class PayBearTxn
 */
class PayBearTxn extends \base_model
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
            txn_hash varchar (255) NOT NULL,                            
            txn_amount decimal (20,8) NOT NULL,            
            confirmation int,
            updated_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP NOT NULL)";

        $q = $db->prepare($sql);
        $q->execute();

        echo 'Table ' . $this->tableName . " has been installed successfully";
        echo '<br/>';
        }

    }

    /**
     * Get total amount of confirmed payment
     * @param $order_id
     * @param $maxConfirmations
     * @return int
     */
    public function getTotalConfirmed($order_id, $maxConfirmations)
    {
        $txns = $this->findByOrderId($order_id);
        $totalConfirmed = 0;
        if (count($txns) > 0)
            foreach ($txns as $txn) {
                if ($txn->confirmation >= $maxConfirmations) {
                    $totalConfirmed += $txn->txn_amount;
                }
            }

        return $totalConfirmed;
    }

    public function getTotalPaid($order_id)
    {
        $txns = $this->findByOrderId($order_id);
        $totalPaid = 0;
        if (count($txns) > 0)
            foreach ($txns as $txn) {
                $totalPaid += $txn->txn_amount;
            }

        return $totalPaid;
    }

    public function getTxnConfirmations($order_id)
    {
        $txns = $this->findByOrderId($order_id);
        $confirmations = array();
        if (count($txns) > 0)
            foreach ($txns as $txn) {
                $confirmations[] = $txn->confirmation;
            }

        return (count($confirmations)) ? min($confirmations) : null;
    }

    public function isNewOrder($order_id)
    {
        $txns = $this->findByOrderId($order_id);
        if (count($txns) > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function setTxn($params, $order_id)
    {

        $txn_hash = $params->inTransaction->hash;

        $txn = $this->findByHash($txn_hash);

        if (empty($txn)) {
            $txn = new PayBearTxn();
        }

        $txn->order_id = $order_id;
        $txn->invoice = $params->invoice;
        $txn->txn_hash = $txn_hash;
        $txn->txn_amount = $params->inTransaction->amount / pow(10, $params->inTransaction->exp);
        $txn->confirmation = $params->confirmations;

        $txn->save();
    }

    public function findByHash($txn_hash) {

        $sql = "SELECT * FROM ".self::table_name()." WHERE txn_hash = ?";
        $q = $this->db->prepare($sql);
        $q->execute(array($txn_hash));
        $q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
        $object = $q->fetchObject('PayBearTxn');

        return $object;
    }

    public function findByOrderId($order_id) {

        $sql = "SELECT * FROM ".self::table_name()." WHERE order_id = ?";
        $q = $this->db->prepare($sql);
        $q->execute(array($order_id));
        $q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
        $objects = $q->fetchAll();

        return $objects;
    }

}