<?php

/**
 * Charge list plan
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

// Check if user is logged
$User = new User();
if(!$User->_isLogged()) die('User not logged');

// Init lang object
$Lang = new Lang($User->_getLang(), $App);

$adminView = false;
if($User->_isAdmin() && isset($_SESSION['kr_account_view_user']) && !empty($_SESSION['kr_account_view_user']) && $_SESSION['kr_account_view_user'] != $User->_getUserID()){
  $User = new User($_SESSION['kr_account_view_user']);
  $adminView = true;
}

$Widthdraw = new Widthdraw($User);
$WithdrawListAssociate = $Widthdraw->_getListWidthdraw();
?>
<section class="user-widthdraw-setup">
  <header>
    <button type="button" class="btn btn-small btn-autowidth btn-green" name="button"><?php echo strtoupper('Add new'); ?></button>
  </header>
  <ul class="user-widthdraw-setup-list-av">
    <?php
    foreach ($Widthdraw->_getWidthdrawMethod() as $fnameWidthdraw => $infoWidthdrawMethod) {
    ?>
      <li onclick="initWidthdrawMethod('<?php echo $fnameWidthdraw; ?>');">
        <img src="<?php echo APP_URL; ?>/app/modules/kr-trade/statics/img/widthdraw/<?php echo $fnameWidthdraw; ?>.svg" alt="">
      </li>
    <?php } ?>
  </ul>
</section>

<?php if(count($WithdrawListAssociate) == 0):

else: ?>
<ul class="user-widthdraw-list">
  <?php
  foreach ($WithdrawListAssociate as $infosWidthdraw) {
  ?>
  <li>
    <section>
      <div>
        <span><?php echo $infosWidthdraw['structure']['name']; ?></span>
      </div>
      <ul>
        <?php
        foreach ($infosWidthdraw['structure']['preview'] as $keyPreview) {
        ?>
          <li>
            <label><?php echo $infosWidthdraw['structure']['fields'][$keyPreview]; ?></label>
            <span><?php
            if($keyPreview == "iban"){
              $iban = new CMPayments\IBAN($infosWidthdraw['infos'][$keyPreview]);
              echo $iban->format();
            } else {
              echo $infosWidthdraw['infos'][$keyPreview];
            }
            ?></span>
          </li>
        <?php } ?>
        <li>
          <button type="button" class="btn btn-small btn-blue btn-autowidth btn-showdetails-widthdraw" kr-widthdraw-details="<?php echo $infosWidthdraw['id']; ?>" name="button">Details</button>
        </li>
      </ul>
    </section>
    <div class="detailswidthraw-<?php echo $infosWidthdraw['id']; ?>">
      <ul>
        <?php foreach ($infosWidthdraw['structure']['fields'] as $keyFieldWidthdraw => $nameFieldWidthdraw) { ?>
          <li>
            <label><?php echo $nameFieldWidthdraw; ?></label>
            <span><?php
              if($keyFieldWidthdraw == "iban"){
                $iban = new CMPayments\IBAN($infosWidthdraw['infos'][$keyFieldWidthdraw]);
                echo $iban->format();
              } else {
                echo $infosWidthdraw['infos'][$keyFieldWidthdraw];
              }

            ?></span>
          </li>
        <?php } ?>
      </ul>
      <footer>
        <button type="button" class="btn btn-small btn-red btn-autowidth widthdraw-method-remove" kr-widthdraw-idr="<?php echo App::encrypt_decrypt('encrypt', $infosWidthdraw['id']); ?>" name="button">Remove</button>
      </footer>
    </div>
  </li>
  <?php } ?>
</ul>
<?php endif; ?>
