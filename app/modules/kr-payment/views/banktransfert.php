<?php

/**
 * Charge plan selected view
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

$BankTransfertError = null;
try {

  // Load app module
  $App = new App(true);
  $App->_loadModulesControllers();

  // Check if user is logged
  $User = new User();
  if(!$User->_isLogged()) die('User not logged');

  //if(empty($_GET) || !isset($_GET['g']) && !isset($_GET['t'])) throw new Exception("Error : Wrong args", 1);

  $BankTransfert = new Banktransfert($User, $App);

  $BankTransfertItem = null;
  if(!empty($_GET) && isset($_GET['t'])){
    $BankTransfertID = explode('-', App::encrypt_decrypt('decrypt', $_GET['t']));
    if(count($BankTransfertID) != 2) throw new Exception("Permission denied", 1);
    $BankTransfertItem = $BankTransfert->_getInfosBankTransfert($BankTransfertID[1]);
  }
  if(!empty($_GET) && isset($_GET['d'])){
    $BankTransfert->_removeProof($BankTransfertItem['id_banktransfert'], App::encrypt_decrypt('decrypt', $_GET['d']));
  }

  $NewBankTransfert = false;
  if(!empty($_GET) && isset($_GET['s']) && ($_GET['s'] == "new" || $_GET['s'] == "new_confirm")){
    if($_GET['s'] == "new"){
      header('Location: banktransfert_contract.php');
    } else {
      $BankTransfertItemNew = $BankTransfert->_generateNewBankTransfer();
      $BankTransfertItem = $BankTransfertItemNew['infos'];
      header('Location: banktransfert.php?t='.App::encrypt_decrypt('encrypt', time().'-'.$BankTransfertItem['id_banktransfert']).'&n=true');
    }
  }

  if(!empty($_GET) && isset($_GET['n']) && $_GET['n'] == "true") $NewBankTransfert = true;

  if(!empty($_GET) && isset($_GET['a']) && $_GET['a'] == "cancel"){
    $BankTransfert->_cancelBankTransfert($BankTransfertItem['id_banktransfert']);
    header('Location: banktransfert.php?t='.App::encrypt_decrypt('encrypt', time().'-'.$BankTransfertItem['id_banktransfert']));
  }

  if(!empty($_GET) && isset($_GET['sacc'])){
    $infosAccountBank = explode('-', App::encrypt_decrypt('decrypt', $_GET['sacc']));
    if(count($infosAccountBank) != 2) throw new Exception("Error : Permission denied", 1);

    $BankTransfert->_assignBankAccount($BankTransfertItem['id_banktransfert'], $infosAccountBank[1]);
    header('Location: banktransfert.php?t='.App::encrypt_decrypt('encrypt', time().'-'.$BankTransfertItem['id_banktransfert']));
  }

} catch (Exception $e) {
  $BankTransfertError = $e->getMessage();
}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title static-title="<?php echo $App->_getAppTitle(); ?>"><?php echo $App->_getAppTitle(); ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/app/modules/kr-payment/statics/css/banktransfert.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">

    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/site.webmanifest">
    <link rel="shortcut icon" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon.ico">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="<?php echo APP_URL; ?>/assets/img/icons/favicon/browserconfig.xml">
    <script src="https://cdn.linearicons.com/free/1.0.0/svgembedder.min.js"></script>

    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/bower/dropzone/dist/min/dropzone.min.css">
    <script src="<?php echo APP_URL; ?>/assets/bower/jquery/dist/jquery.min.js" charset="utf-8"></script>
    <script src="<?php echo APP_URL; ?>/assets/bower/dropzone/dist/min/dropzone.min.js" charset="utf-8"></script>


  </head>
  <body class="kr-nbankwire" hrefapp="<?php echo APP_URL; ?>" kr-banktransfert-i="<?php echo $BankTransfertItem['id_banktransfert']; ?>">
    <header>
      <img src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" alt="">
    </header>
    <?php if(is_null($BankTransfertError)): ?>
      <div class="kr-transfert-actionlist">
        <div>
          <?php if($BankTransfertItem['status_banktransfert'] != "3"): ?>
            <a href="banktransfert.php?t=<?php echo strip_tags($_GET['t']); ?>&a=cancel" class="btn btn-autowidth btn-small btn-orange">Cancel this bank transfert</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
    <?php if(!is_null($BankTransfertError)): ?>
      <div class="kr-transfert-error">
        <div class="kr-bantransfert-choosbank-immsg">
          <span><?php echo $BankTransfertError; ?></span>
        </div>
      </div>
    <?php else: ?>
      <div class="kr-transfert-newbanktransfert">
        <h2>Bank transfert</h2>
        <h3>KRYP-4505-58660</h3>
        <?php if($BankTransfertItem['status_banktransfert'] != "3"): ?>
          <span>Don't forget to add this transfert reference to your bank transfert comment.</span>
        <?php endif; ?>
      </div>
      <?php if(is_null($BankTransfertItem['bankaccount_banktransfert'])): ?>
      <h2 class="kr-bantransfert-choosbank-title" style="margin-top:15px;">Select your bank account destination</h2>
    <?php endif; ?>
    <?php if(!is_null($BankTransfertItem)):
      $BankAccountList = $App->_getListBankAccountAvailable();
        //var_dump($BankTransfertItem);
      ?>
      <?php if($BankTransfertItem['status_banktransfert'] != "3"): ?>
      <?php if(is_null($BankTransfertItem['bankaccount_banktransfert'])): ?>
        <h2 class="kr-bantransfert-choosbank-title">Our bank accounts <?php echo (count($BankAccountList) > 1 ? '(choose one of them)' : ''); ?></h2>
      <?php endif; ?>
      <?php if(count($App->_getListBankAccountAvailable()) == 0): ?>
        <div class="kr-bantransfert-choosbank-immsg">
          <span>No bank account available, please contact our support team : <?php echo $App->_getSupportEmail(); ?></span>
        </div>
      <?php else: ?>
      <section class="kr-bantransfert-choosbank" style="<?php echo (!is_null($BankTransfertItem['bankaccount_banktransfert']) ? "grid-template-columns:1fr;" : ""); ?>">
        <?php
        foreach ($BankAccountList as $key => $infosBankAccount) {
          if(!is_null($BankTransfertItem['bankaccount_banktransfert']) && $infosBankAccount['id_banktransfert_accountavailable'] != $BankTransfertItem['bankaccount_banktransfert']) continue;
        ?>
        <a class="<?php echo (!is_null($BankTransfertItem['bankaccount_banktransfert']) ? "kr-baccacc-selected" : ""); ?>" href="?t=<?php echo $_GET['t']; ?>&sacc=<?php echo App::encrypt_decrypt('encrypt', time().'-'.$infosBankAccount['id_banktransfert_accountavailable']); ?>">
          <header>
            <span><?php echo $infosBankAccount['bank_name__banktransfert_accountavailable']; ?></span>
            <div>
              <?php echo $infosBankAccount['currency_banktransfert_accountavailable']; ?>
            </div>
          </header>
          <ul>
            <li>
              <label>IBAN</label>
              <span><?php echo $infosBankAccount['iban_banktransfert_accountavailable']; ?></span>
            </li>
            <li>
              <label>BIC / SWIFT</label>
              <span><?php echo $infosBankAccount['bic_banktransfert_accountavailable']; ?></span>
            </li>
            <li>
              <label>BANK ADDRESS</label>
              <span><?php echo nl2br($infosBankAccount['address_banktransfert_accountavailable']); ?></span>
            </li>
            <li>
              <label>ACCONT OWNER</label>
              <span><?php echo $infosBankAccount['accountowner_banktransfert_accountavailable']; ?></span>
            </li>
          </ul>
        </a>
      <?php } ?>
      </section>
    <?php endif; ?>
  <?php endif; ?>
      <ul class="kr-banktransfert-infos">
        <li>
          <span>Bank transfet reference</span>
          <div>
            <span><?php echo $BankTransfertItem['uref_banktransfert']; ?></span>
          </div>
        </li>
        <li>
          <span>Bank transfet status</span>
          <div>
            <span class="kr-transfert-tag kr-transfert-tag-<?php echo $BankTransfertItem['status_banktransfert']; ?>">
              <?php echo $BankTransfert->StatusBank[$BankTransfertItem['status_banktransfert']]; ?>
            </span>
          </div>
        </li>
        <li>
          <span>Bank transfet processed</span>
          <div>
            <span class="kr-transfert-tag kr-transfert-tag-<?php echo ($BankTransfertItem['proecessed_banktransfert'] == 0 ? "0" : "2"); ?>">
              <?php echo ($BankTransfertItem['proecessed_banktransfert'] == "0" ? "Not processed" : "Processed"); ?>
            </span>
          </div>
        </li>
        <li>
          <span>Bank transfet created date</span>
          <div>
            <span><?php echo date('d/m/Y H:i:s', $BankTransfertItem['created_date_banktransfert']); ?></span>
          </div>
        </li>
        <li>
          <span>Bank transfet update date</span>
          <div>
            <span><?php echo date('d/m/Y H:i:s', $BankTransfertItem['update_date_banktransfert']); ?></span>
          </div>
        </li>
        <li>
          <span>Bank transfet amount</span>
          <div>
            <span><?php echo (strlen($BankTransfertItem['amount_banktransfert']) == 0 ? '-' : $BankTransfertItem['amount_banktransfert']); ?></span>
          </div>
        </li>
        <li>
          <span>Bank transfet currency</span>
          <div>
            <span><?php echo (strlen($BankTransfertItem['currency_banktransfert']) == 0 ? '-' : $BankTransfertItem['currency_banktransfert']); ?></span>
          </div>
        </li>
        <li>
          <span>Bank reference</span>
          <div>
            <span><?php echo (strlen($BankTransfertItem['bankref_banktransfert']) == 0 ? '-' : $BankTransfertItem['bankref_banktransfert']); ?></span>
          </div>
        </li>
      </ul>
      <?php if($App->_getBankTransfertProofEnable()): ?>
      <h2 class="kr-bantransfert-choosbank-title" style="margin-top:0px;">Bank transfert proof</h2>
      <?php
      $ProofList = $BankTransfert->_getListProof($BankTransfertItem['id_banktransfert']);
      if(count($ProofList) > 0):
        ?>
        <ul class="kr-banktransfert-proof">
          <?php foreach ($ProofList as $key => $proofInformation) {
            $AttachmentInfos = pathinfo($proofInformation['url_banktransfert_proof']);
            $FileName = explode('-', $AttachmentInfos['basename']);
            $FileName = join('-', array_slice($FileName, 1));
            ?>
            <li>
              <div>
                <span><?php echo $FileName; ?></span>
              </div>
              <div class="kr-banktransfert-proof-dat">
                <span><?php echo date('d/m/Y H:i:s', $proofInformation['date_banktransfert_proof']); ?></span>
              </div>
              <div>
                <div>
                  <a href="<?php echo APP_URL.$proofInformation['url_banktransfert_proof']; ?>" target=_bank class="btn btn-small btn-green btn-autowidth">View document</a>
                </div>
              </div>
              <div>
                <a href="banktransfert.php?t=<?php echo $_GET['t']; ?>&d=<?php echo App::encrypt_decrypt('encrypt', $proofInformation['id_banktransfert_proof']); ?>" class="btn btn-small btn-orange btn-autowidth">Delete</a>
              </div>
            </li>
        <?php } ?>
        </ul>
      <?php else: ?>


      <?php endif; ?>
      <?php if(count($ProofList) < $App->_getBankTransfertProofMax() && $BankTransfertItem['status_banktransfert'] < 2): ?>
        <section class="kr-banktransfert-add-proof-dz">
          <svg class="lnr lnr-cloud-upload"><use xlink:href="#lnr-cloud-upload"></use></svg>
          <span>Upload a proof here</span>
          <div>
            <div></div>
          </div>
        </section>
      <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>
  </body>
  <script type="text/javascript">
  $(document).ready(function(){

    if($('.kr-banktransfert-add-proof-dz').length > 0){
      var dz = new Dropzone('.kr-banktransfert-add-proof-dz', {
        autoDiscover: true,
        url: $('body').attr('hrefapp') + '/app/modules/kr-payment/src/actions/proof/addProofBanktransfert.php', // Drop file action
        uploadprogress(data, progress) { // Check upload progress
          $('.kr-banktransfert-add-proof-dz').addClass('kr-banktransfert-add-proof-dz-pg');
          $('.kr-banktransfert-add-proof-dz > div > div').css('width', progress + '%');
        },
        success: function(data, response) { // On upload success
          let resp = jQuery.parseJSON(response);
          if(resp.error == 1){
            $('.kr-banktransfert-add-proof-dz').removeClass('kr-banktransfert-add-proof-dz-pg');
            $('.kr-banktransfert-add-proof-dz > div > div').css('width', '0%');
            alert(resp.msg);
          } else {
            location.reload();
          }
        }
      });

      dz.on('sending', function(file, xhr, formData){
          formData.append('banktransfert_id', $('body').attr('kr-banktransfert-i'));
      });

    }


  });
  </script>
</html>
