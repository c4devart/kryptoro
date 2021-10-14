<?php

/**
 * Admin dashboard page
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

  // Check loggin & permission
  $User = new User();
  if(!$User->_isLogged()) throw new Exception("User are not logged", 1);
  if(!$User->_isAdmin() && !$User->_isManager()) throw new Exception("Permission denied", 1);

  // Init language object
  $Lang = new Lang($User->_getLang(), $App);

  $BankTransfert = new Banktransfert();
  if(empty($_POST) || !isset($_POST['transfert_id'])) throw new Exception("Permission denied", 1);

  $InfosBankTransfert = $BankTransfert->_getInfosBankTransfert(App::encrypt_decrypt('decrypt', $_POST['transfert_id']));


} catch (\Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}


// Init admin object
$Manager = new Manager($App);

?>
<section class="bank_transfert_wizard kr-ov-nblr">
  <section>
    <header>
      <span><?php echo $Lang->tr('Validate bank transfert'); ?> - <?php echo $InfosBankTransfert['uref_banktransfert']; ?></span>
      <div onclick="_closeBankTransfert();">
        <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
      </div>
    </header>
    <form class="content_bank_transfert_wizard" method="post" action="<?php echo APP_URL; ?>/app/modules/kr-manager/src/actions/validateBankTransfert.php">

      <div class="content_bank_transfert_wizard_line">
        <span><?php echo $Lang->tr('Transfert received date'); ?></span>
        <input type="datetime-local" name="bt_vcs_date" value="<?php echo date('Y-m-d', time()).'T'.date('H:i', time()); ?>">
      </div>

      <div class="content_bank_transfert_wizard_line">
        <span><?php echo $Lang->tr('Bank account received'); ?></span>
        <select name="bt_vcs_accountreceived">
          <?php foreach ($App->_getListBankAccountAvailable() as $key => $value) {
            echo '<option '.($InfosBankTransfert['bankaccount_banktransfert'] == $value['id_banktransfert_accountavailable'] ? 'selected' : '').' value="'.$value['id_banktransfert_accountavailable'].'">'.$value['bank_name__banktransfert_accountavailable'].' - '.$value['accountowner_banktransfert_accountavailable'].' - '.$value['iban_banktransfert_accountavailable'].'</option>';
          } ?>
        </select>
      </div>

      <div class="content_bank_transfert_wizard_line">
        <span><?php echo $Lang->tr('Bank transfert reference'); ?></span>
        <input type="text" name="bt_vcs_ref" value="">
      </div>

      <div class="content_bank_transfert_wizard_line">
        <span><?php echo $Lang->tr('Amount received in bank account'); ?></span>
        <div class="content_bank_transfert_wizard_line_mlc">
          <div>
            <span><?php echo $Lang->tr('Amount'); ?></span>
            <input type="text" name="bt_vcs_amount" value="<?php echo $InfosBankTransfert['amount_banktransfert']; ?>">
          </div>
          <div>
            <span><?php echo $Lang->tr('Currency'); ?></span>
            <input type="text" name="bt_vcs_currency" value="<?php echo $InfosBankTransfert['currency_banktransfert']; ?>">
          </div>
        </div>

      </div>

      <div class="content_bank_transfert_wizard_line">
        <span><?php echo $Lang->tr('Amount transfered to the user account'); ?></span>
        <div class="content_bank_transfert_wizard_line_mlc">
          <div>
            <span><?php echo $Lang->tr('Wallet'); ?></span>
            <?php
            $Balance = new Balance($User, $App, 'real');
            $SymbolListAvailable = $Balance->_getBalanceListResum();
            ?>
            <select name="bt_vcs_symbol_wallet">
              <?php foreach ($SymbolListAvailable as $symbolReceive => $value) {
                echo '<option '.($App->_getDepositSymbolNotExistConvert() == $symbolReceive ? 'selected' : '').' value="'.$symbolReceive.'">'.$symbolReceive.'</option>';
              } ?>
            </select>
          </div>
          <div>
            <span><?php echo $Lang->tr('Amount'); ?></span>
            <input class="content_bank_transfert_wizard_line_mlc_amount_wf" type="text" name="bt_vcs_amount_wallet" value="">
          </div>
          <div>
            <span><?php echo $Lang->tr('Fees').' ('.(($App->_getFeesDeposit() + $Balance->_getPaymentGatewayFee('banktransfert'))).'%)'; ?></span>
            <input class="content_bank_transfert_wizard_line_mlc_amount_fe" readonly kr-bk-fees="<?php echo ($App->_getFeesDeposit() + $Balance->_getPaymentGatewayFee('banktransfert')); ?>" type="text" name="bt_vcs_wallet_fees" value="0.00">
          </div>
        </div>
        <label><?php echo $Lang->tr('User will receive'); ?> : <i class="content_bank_transfert_wizard_line_mlc_amount_total">0.00</i> <i class="content_bank_transfert_wizard_line_mlc_amount_symbol"><?php echo $App->_getDepositSymbolNotExistConvert(); ?></i></label>
      </div>

      <footer>
        <input type="hidden" name="bt_vcs_trid" value="<?php echo App::encrypt_decrypt('encrypt', $InfosBankTransfert['id_banktransfert']); ?>">
        <button type="button" onclick="_closeBankTransfert();" class="btn btn-autowidth" name="button"><?php echo $Lang->tr('Cancel'); ?></button>
        <input type="submit" class="btn btn-autowidth btn-green" name="" value="<?php echo $Lang->tr('Validate'); ?>">
      </footer>

    </form>
  </section>
</section>
