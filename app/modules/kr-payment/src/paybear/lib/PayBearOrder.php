<?php
include_once 'base_model.php';
include_once 'PayBear.php';
include_once 'PayBearAddress.php';



/**
 * Class PayBearOrder
 */
class PayBearOrder extends \base_model
{
    public $tableName;
    protected $payBear;

    public function  __construct() {
        $this->tableName = parent::table_name();

        $api_secret_key = 'YOUR_API_SECRET_KEY_HERE';
        $api_public_key = 'YOUR_API_PUBLIC_KEY_HERE';

        $this->payBear = new PayBear($api_secret_key, $api_public_key);

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
                invoice varchar (255) NOT NULL,
                order_id varchar (255) NOT NULL,
                amount decimal (20,8) NOT NULL,
                confirmations int,
                max_confirmations int NOT NULL,
                address varchar (255) NOT NULL,
                crypto varchar (255) NOT NULL,                
                updated_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP NOT NULL,
                paid_at TIMESTAMP )";

        $q = $db->prepare($sql);
        $q->execute();

        echo 'Table ' . $this->tableName . " has been installed successfully";
        echo '<br/>';
        }

    }

    public function getCurrencies() {
       return $this->payBear->getCurrencies();
    }

    /**
     * Get currency data for PayBear form
     * @param $token
     * @param $fiatValue
     * @param $fiat_currency
     * @param bool $getAddress
     * @param string $order_id
     * @return mixed|null
     */
    function getCurrency($order_id, $token, $fiatValue, $fiat_currency, $getAddress = false ) {
        $token = strtolower($token);
        $rate = $this->payBear->getCoinRate($fiat_currency, $token);

        if ($rate) {

            $coinsValue = round($fiatValue / $rate, 8);

            $currencies = $this->payBear->getCurrencies();
            if (isset($currencies[$token])) {
                $currency               = $currencies[ $token ];
                $currency['coinsValue'] = $coinsValue;
                $currency['rate'] = $rate;

                if ( $getAddress ) {
                    $address  = $this->getPaymentAddress($token, $order_id, $coinsValue, $currency['maxConfirmations']);
                    $currency['address'] = $address;
                } else {
                    $currency['currencyUrl'] = sprintf( 'currencies.php?token=%s&order_id=%s', $token,  $order_id);
                }

                return $currency;
            }

        }

        return null;
    }


    /**
     * Get payment address and create payment request
     * @param $token
     * @param $order_id
     * @param $amount
     * @param $maxConfirmations
     * @return string
     */
    public function getPaymentAddress($token, $order_id, $amount, $maxConfirmations)
    {
        $PayBearAddress = new PayBearAddress();

        $addressObject = $PayBearAddress->findByArray(array('order_id' => $order_id, 'crypto' => $token));
        $payment = $this->findByOrderId($order_id);
        $address = '';

        $callbackUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/callback.php?order_id=' . $order_id;

        if (empty($addressObject)) {
            $token_address_data = $this->payBear->createPayment($token, $callbackUrl);

            if (!empty($token_address_data)) {

                if (empty($payment)) {
                    $payment = new PayBearOrder();
                }

                $payment->invoice = $token_address_data['invoice'];
                $payment->address = $token_address_data['address'];
                $payment->amount = $amount;
                $payment->max_confirmations = $maxConfirmations;
                $payment->crypto  = $token;
                $payment->order_id = $order_id;

                $payment->save();

                $addressObject = new PayBearAddress();

                $addressObject->order_id     = $order_id;
                $addressObject->invoice      = $token_address_data['invoice'];
                $addressObject->address      = $token_address_data['address'];
                $addressObject->crypto       = $token;

                $addressObject->save();

                $address = $token_address_data['address'];
            }

        } else {
            $address = $addressObject->address;

            $payment->invoice = $addressObject->invoice;
            $payment->address = $address;
            $payment->amount = $amount;
            $payment->max_confirmations = $maxConfirmations;
            $payment->crypto  = $token;
            $payment->order_id = $order_id;

            $payment->save();
        }

        return $address;
    }


    public function findByOrderId($id) {

        $sql = "SELECT * FROM ".self::table_name()." WHERE order_id = ?";
        $q = $this->db->prepare($sql);
        $q->execute(array($id));
        $q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
        $object = $q->fetchObject('PayBearOrder');

        return $object;
    }




}