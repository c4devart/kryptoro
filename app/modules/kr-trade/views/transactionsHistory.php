<?php

session_start();

require "../../../../config/config.settings.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

$App = new App(true);
$App->_loadModulesControllers();

$User = new User();
if(!$User->_isLogged()) die('Error : User not logged');

$Lang = new Lang($User->_getLang(), $App);

try {
  // Init CryptoApi object
  //
  $Balance = new Balance($User, $App);
  $Balance = $Balance->_getCurrentBalance();

  $Manager = new Manager($App);

} catch (\Exception $e) {
  die($e->getMessage());
}

?>

<section class="kr-balance-view">
  <header>
    <h2><?php echo $Lang->tr('Transactions history'); ?></h2>
    <div>
      <div>
        <input type="text" class="kr-history-view-search" name="" value="" placeholder="<?php echo $Lang->tr('Reference'); ?> ...">
      </div>
    </div>
  </header>

  <div class="kr-marketlist">
    <div class="kr-marketlist-header">
      <div class="kr-marketlist-n"></div>
      <div class="kr-mono"><span><?php echo $Lang->tr('Type'); ?></span></div>
      <div class="kr-mono"><span><?php echo $Lang->tr('Date'); ?></span></div>
      <div class="kr-mono"><span><?php echo $Lang->tr('Method'); ?></span></div>
      <div class="kr-mono"><span><?php echo $Lang->tr('Status'); ?></span></div>
      <div class="kr-mono"><span><?php echo $Lang->tr('Amount'); ?></span></div>
      <div class="kr-mono"><span><?php echo $Lang->tr('Fees'); ?></span></div>
      <div class="kr-mono"><span><?php echo $Lang->tr('Received'); ?></span></div>
    </div>

    <?php
    $TransactionsHistory = $Balance->_getTransactionsHistory();
    foreach ($TransactionsHistory as $dataHisto) {

      $currenyDecimal = 8;
      ?>
      <div class="kr-marketlist-item kr-balanceitem-cv" kr-history-ref="<?php echo $dataHisto['ref']; ?>">
        <div class="kr-marketlist-n">
          <div class="kr-marketlist-n-nn">
            <label class="kr-mono"><b><?php echo $dataHisto['ref']; ?></b></label>
          </div>
        </div>
        <div class="kr-mono">
          <span style="color:<?php echo ($dataHisto['type_histo'] == "deposit" ? "#29c359" : "#f14700"); ?>;"><?php echo ($dataHisto['type_histo'] == "deposit" ? "&#9662;" : "&#9652").'&nbsp;&nbsp;'.strtoupper($Lang->tr($dataHisto['type_histo'])); ?></span>
        </div>
        <div class="kr-mono">
          <span><?php echo date('d/m/Y H:i:s', $dataHisto['date_histo']); ?></span>
        </div>
        <div class="kr-mono">
          <span><?php echo $dataHisto['method']; ?></span>
        </div>
        <div class="kr-mono">
          <?php
          if($dataHisto['type_histo'] == "deposit"):
           if($dataHisto['status'] == 0) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-red">'.$Lang->tr($Manager->_getPaymentStatus($dataHisto['status'])).'</span>';
           if($App->_getPaymentApproveNeeded()){
             if($dataHisto['status'] == 1) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-orange">'.$Lang->tr($Manager->_getPaymentStatus($dataHisto['status'])).'</span>';
             if($dataHisto['status'] == 2) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-green">'.$Lang->tr($Manager->_getPaymentStatus($dataHisto['status'])).'</span>';
           } else {
             if($dataHisto['status'] == 1) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-green">'.$Lang->tr($Manager->_getPaymentStatus($dataHisto['status'])).'</span>';
           }
         else:
           if($dataHisto['status'] == 2): ?>
           <span class="kr-admin-lst-tag kr-admin-lst-tag-green"><?php echo $Lang->tr('Done'); ?></span>

         <?php elseif($dataHisto['status'] == -1):
           ?>
             <span class="kr-admin-lst-tag kr-admin-lst-tag-grey"><?php echo $Lang->tr('Canceled'); ?></span>
           <?php else: ?>
           <span class="kr-admin-lst-tag kr-admin-lst-tag-red"><?php echo $Lang->tr('Not confirmed'); ?></span>
         <?php endif;

       endif;
           ?>
        </div>

        <div class="kr-mono">
          <span><?php echo $App->_formatNumber($dataHisto['amount_histo'] + $dataHisto['fees'], $currenyDecimal). ' '.$dataHisto['currency']; ?></span>
        </div>
        <div class="kr-mono">
          <span><?php echo $App->_formatNumber($dataHisto['fees'], $currenyDecimal). ' '.$dataHisto['currency']; ?></span>
        </div>
        <div class="kr-mono">
          <span><b><?php echo $App->_formatNumber($dataHisto['amount_histo'], $currenyDecimal). ' '.$dataHisto['currency']; ?></b></span>
        </div>
      </div>
      <?php
    }
    ?>
  </div>
</section>
