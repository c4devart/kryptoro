<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'lib/PayBearOrder.php';
require_once 'lib/PayBearAddress.php';
require_once 'lib/PayBearTxn.php';

require_once 'lib/CmsOrder.php';

$payBearOrder = new PayBearOrder();

$payBearOrder->install_table();

$payBearAddress = new PayBearAddress();

$payBearAddress->install_table();

$payBearTxn = new PayBearTxn();

$payBearTxn->install_table();

/**
 * init CMS order example
 * */

$CmsOrder = new CmsOrder();

$CmsOrder->install_table();

$CmsOrder->increment_id     = '100001';
$CmsOrder->order_total      = 19.95;
$CmsOrder->fiat_currency    = 'usd';
$CmsOrder->fiat_sign        = '$';

if (empty($CmsOrder->findByIncrementId('100001'))) {
    $CmsOrder->save();
}
