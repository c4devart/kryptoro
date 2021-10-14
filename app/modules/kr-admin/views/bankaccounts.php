<?php

/**
 * Admin payment settings
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
if(!$User->_isAdmin()) throw new Exception("Permission denied", 1);

// Init language object
$Lang = new Lang($User->_getLang(), $App);

$Balance = new Balance($User, $App);

// Init admin object
$Admin = new Admin();
?>
<section class="kr-admin">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Bank accounts' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>

  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Bank account name'); ?></td>
          <td><?php echo $Lang->tr('Bank account currency'); ?></td>
          <td><?php echo $Lang->tr('Bank account IBAN'); ?></td>
          <td><?php echo $Lang->tr('Bank account BIC/SWIFT'); ?></td>
          <td><?php echo $Lang->tr('Bank account address'); ?></td>
          <td><?php echo $Lang->tr('Bank account owner name'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($App->_getListBankAccountAvailable() as $key => $value) {
          ?>
          <tr>
            <td><?php echo $value['bank_name__banktransfert_accountavailable']; ?></td>
            <td><?php echo $value['currency_banktransfert_accountavailable']; ?></td>
            <td><?php echo $value['iban_banktransfert_accountavailable']; ?></td>
            <td><?php echo $value['bic_banktransfert_accountavailable']; ?></td>
            <td><?php echo nl2br($value['address_banktransfert_accountavailable']); ?></td>
            <td><?php echo $value['accountowner_banktransfert_accountavailable']; ?></td>
            <td>
              <form method="post" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/deleteBankaccount.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
                <input type="hidden" name="id_bankaccount" value="<?php echo App::encrypt_decrypt('encrypt', $value['id_banktransfert_accountavailable']); ?>">
                <input type="submit" class="btn btn-small btn-autowidth" name="remove_social" value="<?php echo $Lang->tr('Delete'); ?>">
              </form>
            </td>
          </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
  </div>

  <h3><?php echo $Lang->tr('Add new bank account'); ?></h3>
  <form class="kr-adm-post-evs kr-admin-line kr-admin-line-cls" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/addBankAccount.php" method="post">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Bank account name'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your bank account name'); ?>" name="bank_name" value="">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Bank account currency'); ?></label><br/>
        <span><?php echo $Lang->tr('* This is optional'); ?></span>
      </div>
      <div>
        <select class="" name="bank_currency">
          <option value="">----</option>
          <?php
          foreach ($Balance->_getListMoney() as $key => $value) {
            echo '<option value="'.$value.'">'.$value.'</option>';
          }
          ?>
        </select>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Bank account IBAN'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your bank account IBAN'); ?>" name="bank_iban" value="">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Bank account BIC/SWIFT'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your bank account BIC/SWIFT'); ?>" name="bank_bic" value="">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Bank account address'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your bank account address'); ?>" name="bank_address" value="">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Bank account owner name'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your bank account owner name'); ?>" name="bank_accountowner" value="">
      </div>
    </div>
    <div class="kr-admin-action">
      <input type="submit" class="btn btn-green" style="margin-bottom:15px;" name="" value="<?php echo $Lang->tr('Add new'); ?>">
    </div>
  </form>
</section>
