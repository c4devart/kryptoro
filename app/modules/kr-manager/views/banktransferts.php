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

$BankTransfert = new Banktransfert();

// Init admin object
$Manager = new Manager($App);

?>
<section class="kr-manager kr-admin">
  <nav class="kr-manager-nav">
    <ul>
      <?php foreach ($Manager->_getListSection() as $key => $section) { // Get list admin section
        $NotificationNumber = $Manager->_getNumberManagerNotification(strtolower(str_replace(' ', '', $section)));
        echo '<li type="module" kr-module="manager" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.(strtolower(str_replace(' ', '', $section)) == 'banktransferts' ? 'class="kr-manager-nav-selected"' : '').'>'.$Lang->tr($section).' '.($NotificationNumber > 0 ? '<span>'.$NotificationNumber.'</span>' : '').'</li>';
      } ?>
    </ul>
  </nav>

  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('User'); ?></td>
          <td><?php echo $Lang->tr('Ref.'); ?></td>
          <td><?php echo $Lang->tr('Created date'); ?></td>
          <td><?php echo $Lang->tr('Status'); ?></td>
          <td><?php echo $Lang->tr('Processed'); ?></td>
          <td><?php echo $Lang->tr('Amount'); ?></td>
          <td><?php echo $Lang->tr('Bank ref.'); ?></td>
          <td><?php echo $Lang->tr('Proof received'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($BankTransfert->_getListBankTransfert() as $keyBankTransfert => $infosBankTransfert) {
          $UserTransfert = new User($infosBankTransfert['id_user']);
          ?>
          <tr>
             <td>
               <div class="kr-admin-coin-nsa">
                 <span><?php echo '#'.$infosBankTransfert['id_user'].' - '.$UserTransfert->_getName(); ?></span>
               </div>
             </td>
             <td>
               <b><?php echo $infosBankTransfert['uref_banktransfert']; ?></b>
             </td>
             <td>
               <?php echo date('d/m/Y H:i:s', $infosBankTransfert['created_date_banktransfert']); ?>
             </td>
             <td>
               <?php
                if($infosBankTransfert['status_banktransfert'] == 0) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-red">'.$Lang->tr($BankTransfert->StatusBank[$infosBankTransfert['status_banktransfert']]).'</span>';
                if($infosBankTransfert['status_banktransfert'] == 1) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-orange">'.$Lang->tr($BankTransfert->StatusBank[$infosBankTransfert['status_banktransfert']]).'</span>';
                if($infosBankTransfert['status_banktransfert'] == 2) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-green">'.$Lang->tr($BankTransfert->StatusBank[$infosBankTransfert['status_banktransfert']]).'</span>';
                if($infosBankTransfert['status_banktransfert'] == 3) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-grey">'.$Lang->tr($BankTransfert->StatusBank[$infosBankTransfert['status_banktransfert']]).'</span>';
               ?>
             </td>
             <td>
               <?php
                if($infosBankTransfert['proecessed_banktransfert'] == 0) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-grey">'.$Lang->tr('Not processed').'</span>';
                if($infosBankTransfert['proecessed_banktransfert'] == 1) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-green">'.$Lang->tr('Processed').'</span>';
               ?>
             </td>
             <td>
               <?php
               echo $App->_formatNumber($infosBankTransfert['amount_banktransfert'], 2).' '.$infosBankTransfert['currency_banktransfert'];
               ?>
             </td>
             <td>
               <?php
               echo (strlen($infosBankTransfert['bankref_banktransfert']) == 0 ? '---' : $infosBankTransfert['bankref_banktransfert']);
               ?>
             </td>
             <td>
               <div style="display:flex;">
               <?php
               $listProofBankTransfert = $BankTransfert->_getListProof($infosBankTransfert['id_banktransfert']);
               if(count($listProofBankTransfert) == 0) echo '<span>No proof received</span>';
               foreach ($listProofBankTransfert as $keyProof => $valueProof) {
                 echo '<a title="'.date('d/m/Y H:i:s', $valueProof['date_banktransfert_proof']).'" class="btn btn-autowidth btn-small" target=_bank href="'.$valueProof['url_banktransfert_proof'].'">'.($keyProof + 1).'</a>';
               }
               ?>
               </div>
             </td>
             <td>
               <?php if($infosBankTransfert['status_banktransfert'] < 2): ?>
                 <button type="button" onclick="_showTransfertWizard('<?php echo App::encrypt_decrypt('encrypt', $infosBankTransfert['id_banktransfert']); ?>')" name="button" style="margin-bottom:5px;" class="btn btn-small btn-autowidth btn-green"><?php echo $Lang->tr('Validate'); ?></button>
               <?php endif; ?>
               <?php if($infosBankTransfert['status_banktransfert'] == 2 && $infosBankTransfert['proecessed_banktransfert'] == "0"): ?>
                 <button type="button" onclick="_processTransfert('<?php echo App::encrypt_decrypt('encrypt', $infosBankTransfert['id_banktransfert']); ?>')" name="button" style="margin-bottom:5px;" class="btn btn-small btn-autowidth btn-green"><?php echo $Lang->tr('Process'); ?></button>
               <?php endif; ?>
               <?php if($infosBankTransfert['status_banktransfert'] < 3): ?>
                 <button type="button" name="button" class="btn btn-small btn-autowidth btn-red"><?php echo $Lang->tr('Cancel'); ?></button>
               <?php endif; ?>

             </td>
           </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
  </div>

</section>
