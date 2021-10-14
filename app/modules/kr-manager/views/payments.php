<?php

/**
 * Admin dashboard page
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

// Check loggin & permission
$User = new User();
if(!$User->_isLogged()) throw new Exception("User are not logged", 1);
if(!$User->_isAdmin() && !$User->_isManager()) throw new Exception("Permission denied", 1);

// Init language object
$Lang = new Lang($User->_getLang(), $App);

$Balance = new Balance($User, $App);
$Balance = $Balance->_getCurrentBalance();

// Init admin object
$Manager = new Manager($App);

$filter = 'default';
if(!empty($_POST) && isset($_POST['filter']) && array_key_exists($_POST['filter'], $Manager->_getPaymentFilters())) $filter = $_POST['filter'];

?>
<section class="kr-manager kr-admin">
  <nav class="kr-manager-nav">
    <ul>
      <?php foreach ($Manager->_getListSection() as $key => $section) { // Get list admin section
        $NotificationNumber = $Manager->_getNumberManagerNotification(strtolower(str_replace(' ', '', $section)));
        echo '<li type="module" kr-module="manager" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.(strtolower(str_replace(' ', '', $section)) == 'payments' ? 'class="kr-manager-nav-selected"' : '').'>'.$Lang->tr($section).' '.($NotificationNumber > 0 ? '<span>'.$NotificationNumber.'</span>' : '').'</li>';
      } ?>
    </ul>
  </nav>

  <header class="kr-manager-switch-opt">
    <span><?php echo $Lang->tr('Payment filter'); ?></span>
    <select class="kr-manager-switch-opt-swtf" kr-manager-v="payments" name="">
      <?php
      foreach ($Manager->_getPaymentFilters() as $vFilter => $tFilter) {
        if(!$App->_getPaymentApproveNeeded() && $vFilter == "needapp") continue;
        echo '<option '.($filter == $vFilter ? 'selected' : '').' value="'.$vFilter.'">'.$Lang->tr($tFilter).'</option>';
      }
      ?>
    </select>
  </header>

  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('User'); ?></td>
          <td><?php echo $Lang->tr('Ref.'); ?></td>
          <td><?php echo $Lang->tr('Created date'); ?></td>
          <td><?php echo $Lang->tr('Status'); ?></td>
          <td><?php echo $Lang->tr('Amount paid'); ?></td>
          <td><?php echo $Lang->tr('Fees'); ?></td>
          <td><?php echo $Lang->tr('Wallet received'); ?></td>
          <td><?php echo $Lang->tr('Amount received'); ?></td>
          <td><?php echo $Lang->tr('Payment gateway'); ?></td>
          <td><?php echo $Lang->tr('Proof'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        $WalletListAvailable = $Balance->_getBalanceListResum();
        foreach ($Manager->_fetchPayments() as $key => $infosPayment):
          if($infosPayment['payment_type_deposit_history'] == "Initial") continue;

          if($filter == 'default'){
            if($infosPayment['payment_status_deposit_history'] == 0) continue;
          }

          if($filter == 'npaid' && $infosPayment['payment_status_deposit_history'] != 0) continue;
          if($filter == "paid") {
            if($infosPayment['payment_status_deposit_history'] == 0) continue;
            if($App->_getPaymentApproveNeeded() && $infosPayment['payment_status_deposit_history'] == "1") continue;
          }

          if($filter == "needapp" && $App->_getPaymentApproveNeeded()){
            if($infosPayment['payment_status_deposit_history'] != 1) continue;
          }

          $UserPayment = $Manager->_getUserFetched($infosPayment['id_user']);

          $BalanceReceivedSymbol = $App->_getDepositSymbolNotExistConvert();
          if(array_key_exists($infosPayment['currency_deposit_history'], $WalletListAvailable)) $BalanceReceivedSymbol = $infosPayment['currency_deposit_history'];
          ?>
          <tr>
             <td>
               <div class="kr-admin-coin-nsa">
                 <span><?php echo '#'.$infosPayment['id_user'].' - '.$UserPayment->_getName(); ?></span>
               </div>
             </td>
             <td>
               <b><?php echo $infosPayment['ref_deposit_history']; ?></b>
             </td>
             <td>
               <?php echo date('d/m/Y H:i:s', $infosPayment['date_deposit_history']); ?>
             </td>
             <td>
               <?php
                if($infosPayment['payment_status_deposit_history'] == 0) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-red">'.$Lang->tr($Manager->_getPaymentStatus($infosPayment['payment_status_deposit_history'])).'</span>';
                if($App->_getPaymentApproveNeeded()){
                  if($infosPayment['payment_status_deposit_history'] == 1) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-orange">'.$Lang->tr($Manager->_getPaymentStatus($infosPayment['payment_status_deposit_history'])).'</span>';
                  if($infosPayment['payment_status_deposit_history'] == 2) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-green">'.$Lang->tr($Manager->_getPaymentStatus($infosPayment['payment_status_deposit_history'])).'</span>';
                } else {
                  if($infosPayment['payment_status_deposit_history'] == 1) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-green">'.$Lang->tr($Manager->_getPaymentStatus($infosPayment['payment_status_deposit_history'])).'</span>';
                }

                ?>
             </td>
             <td>
               <?php
               echo $App->_formatNumber($infosPayment['amount_deposit_history'] + $infosPayment['fees_deposit_history'], 8).' '.$infosPayment['currency_deposit_history'];
               ?>
             </td>
             <td>
               <?php
               echo $App->_formatNumber($infosPayment['fees_deposit_history'], 8).' '.$infosPayment['currency_deposit_history'];
               ?>
             </td>
             <td>
               <?php echo $infosPayment['wallet_deposit_history']; ?>
             </td>
             <td>
               <?php
               $AmountNeedReceive = $infosPayment['amount_deposit_history'] * $infosPayment['wallet_convert_m_deposit_history'];
               echo '<b>'.$App->_formatNumber($AmountNeedReceive, 8).' '.$infosPayment['wallet_deposit_history'].'</b>';
               ?>
             </td>
             <td>
               <?php
               echo $infosPayment['payment_type_deposit_history'];
               ?>
             </td>
             <td>
               <?php
               $listProof = $Manager->_getProofPaymentAssociated($infosPayment['id_deposit_history']);
               if(count($listProof) > 0){
                 echo '<div style="display:flex;">';
                 foreach ($listProof as $keyProof => $valueProof) {
                   if(strlen($valueProof['url_deposit_history_proof']) > 0){
                     echo '<a style="margin-right:5px;" title="'.date('d/m/Y H:i:s', $valueProof['sended_deposit_history_proof']).'" class="btn btn-autowidth btn-small btn-green" target=_bank href="'.APP_URL.'/'.$valueProof['url_deposit_history_proof'].'">'.($keyProof + 1).'</a>';
                   } else {
                     echo '<a style="margin-right:5px;" title="Not received" class="btn btn-autowidth btn-small" target=_bank>'.($keyProof + 1).'</a>';
                   }
                 }
                 echo '</div>';
               } else {
                 echo '-';
               }
               ?>
             </td>
             <td>
               <?php
               if($infosPayment['payment_status_deposit_history'] == 0){
                 ?>
                 <button type="button" onclick="_actionPaymentManager('<?php echo App::encrypt_decrypt('encrypt', 'accept_payment-'.$infosPayment['id_deposit_history']); ?>')" name="button" style="margin-bottom:5px;" class="btn btn-small btn-autowidth btn-green"><?php echo $Lang->tr('Validate'); ?></button>
                 <button type="button" onclick="_actionPaymentManager('<?php echo App::encrypt_decrypt('encrypt', 'askproof-'.$infosPayment['id_deposit_history']); ?>', 'askproof')" name="button" style="margin-bottom:5px;" class="btn btn-small btn-autowidth btn-orange"><?php echo $Lang->tr('Ask a proof'); ?></button>
                 <?php
               }
               if($infosPayment['payment_status_deposit_history'] != 0){
                 if($App->_getPaymentApproveNeeded() && $infosPayment['payment_status_deposit_history'] == "1"){
                   ?>
                   <button type="button" onclick="_actionPaymentManager('<?php echo App::encrypt_decrypt('encrypt', 'accept_payment-'.$infosPayment['id_deposit_history']); ?>')" name="button" style="margin-bottom:5px;" class="btn btn-small btn-autowidth btn-green"><?php echo $Lang->tr('Validate'); ?></button>
                   <?php
                 }
                 ?>
                 <button type="button" onclick="_actionPaymentManager('<?php echo App::encrypt_decrypt('encrypt', 'cancel_payment-'.$infosPayment['id_deposit_history']); ?>', 'cancel')" name="button" class="btn btn-small btn-autowidth btn-red"><?php echo $Lang->tr('Cancel'); ?></button>
                 <?php
               }
               ?>
             </td>
           </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</section>
