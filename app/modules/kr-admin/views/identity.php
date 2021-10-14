<?php

/**
 * Admin general settings page
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

$Identity = new Identity($User);

// Init admin object
$Admin = new Admin();
?>
<section class="kr-admin">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Identity' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>
  <form action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/saveIdentity.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable identity system'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-identitysystem" <?php echo ($App->_getIdentityEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-identitysystem">
            <label for="kr-adm-chk-identitysystem"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Block trading without identity verification'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-blocktrading" <?php echo ($App->_getIdentityTradeBlocked() ? 'checked' : ''); ?> name="kr-adm-chk-blocktrading">
            <label for="kr-adm-chk-blocktrading"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Block deposit without identity verification'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-blockdeposit" <?php echo ($App->_getIdentityDepositBlocked() ? 'checked' : ''); ?> name="kr-adm-chk-blockdeposit">
            <label for="kr-adm-chk-blockdeposit"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Block withdraw without identity verification'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-blockwithdraw" <?php echo ($App->_getIdentityWithdrawBlocked() ? 'checked' : ''); ?> name="kr-adm-chk-blockwithdraw">
            <label for="kr-adm-chk-blockwithdraw"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('New identity wizard title'); ?></label>
      </div>
      <div>
        <input type="text" name="kr-adm-identitywizardtitle" value="<?php echo $App->_getIdentityWizardtitle(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('New identity title'); ?></label>
      </div>
      <div>
        <input type="text" name="kr-adm-identitytitle" value="<?php echo $App->_getIdentityTitle(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('New identity wizard advertisement'); ?></label>
      </div>
      <div>
        <input type="text" name="kr-adm-identitywizardadvertisement" value="<?php echo $App->_getIdentityAdvertisement(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('New identity start button'); ?></label>
      </div>
      <div>
        <input type="text" name="kr-adm-identitywizardbutton" value="<?php echo $App->_getIdentityStartButton(); ?>">
      </div>
    </div>
    <div class="kr-admin-action">
      <input type="submit" class="btn btn-green" style="margin-bottom:15px;" name="" value="<?php echo $Lang->tr('Save'); ?>">
    </div>
  </form>

  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Identity step name'); ?></td>
          <td><?php echo $Lang->tr('Identity step description'); ?></td>
          <td><?php echo $Lang->tr('Identity order'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($Identity->_getListStepHard() as $key => $value) {
          ?>
          <tr>
            <td><?php echo $value['name_identity_step']; ?></td>
            <td><?php
              if($value['type_identity_step'] == "form"){
                $formInfos = json_decode($value['description_identity_step'], true);
                echo "<ul>";
                foreach ($formInfos as $keyForm => $valueForm) {
                  echo "<li style='list-style:circle; margin-bottom:4px;'>".$valueForm['title']." (ex : ".$valueForm['placeholder'].")</li>";
                }
                echo "</ul>";
              } else {
                echo $value['description_identity_step'];
              }

            ?></td>
            <td><?php echo $value['order_identity_step']; ?></td>
            <td>
              <?php if($value['order_identity_step'] > 1): ?>
                <form method="post" style="margin-bottom:5px;" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/changePositionIdentityStep.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
                  <input type="hidden" name="id_identity_step" value="<?php echo App::encrypt_decrypt('encrypt', $value['id_identity_step']); ?>">
                  <input type="hidden" name="position_identity_step_dir" value="up">
                  <input type="submit" class="btn btn-small btn-green btn-autowidth" name="remove_social" value="<?php echo $Lang->tr('Up order'); ?>">
                </form>
              <?php endif; ?>
              <?php if($value['order_identity_step'] < count($Identity->_getListStepHard())): ?>
                <form method="post" style="margin-bottom:10px;" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/changePositionIdentityStep.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
                  <input type="hidden" name="id_identity_step" value="<?php echo App::encrypt_decrypt('encrypt', $value['id_identity_step']); ?>">
                  <input type="hidden" name="position_identity_step_dir" value="down">
                  <input type="submit" class="btn btn-small btn-orange btn-autowidth" name="remove_social" value="<?php echo $Lang->tr('Down order'); ?>">
                </form>
              <?php endif; ?>
              <form method="post" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/deleteIdentityStep.php" class="kr-adm-post-evs kr-adm-post-evs-confirm kr-admin-line kr-admin-line-cls">
                <input type="hidden" name="id_identity_step" value="<?php echo App::encrypt_decrypt('encrypt', $value['id_identity_step']); ?>">
                <input type="submit" class="btn btn-small btn-red btn-autowidth" name="remove_social" value="<?php echo $Lang->tr('Delete'); ?>">
              </form>
            </td>
          </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
  </div>

  <h3><?php echo $Lang->tr('Add new identity step'); ?></h3>
  <form class="kr-adm-post-evs kr-admin-line kr-admin-line-cls" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/addIdentityStep.php" method="post">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Identity step name'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your step name'); ?>" name="step_name" value="">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Identity step type'); ?></label>
      </div>
      <div>
        <select class="" kr-step-identity-type-s="true" name="step_type">
          <option value="document"><?php echo $Lang->tr('Upload new document'); ?></option>
          <option value="doclist"><?php echo $Lang->tr('Choose document in the documents list'); ?></option>
          <option value="form"><?php echo $Lang->tr('Form'); ?></option>
        </select>
      </div>
    </div>
    <div class="kr-admin-field" kr-step-h="form">
      <div>
        <label><?php echo $Lang->tr('Identity step description'); ?></label><br/>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your step description'); ?>" name="step_descrption" value="">
      </div>
    </div>
    <div class="kr-admin-field" kr-step-h="form">
      <div>
        <label><?php echo $Lang->tr('Enable identity upload with webcam'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-webcamstep" <?php echo ($App->_creditCardEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-webcamstep">
            <label for="kr-adm-chk-webcamstep"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field" kr-step-h="form">
      <div>
        <label><?php echo $Lang->tr('Webcam document ratio'); ?></label><br/>
        <span><?php echo $Lang->tr('Ratio (Width / Height)'); ?></span>
      </div>
      <div>
        <select class="" name="step_webcam_ratio">
          <?php
          for ($i=1; $i < 5; $i++) {
            for ($s=1; $s < 5; $s++) {
              ?>
              <option value="<?php echo $i.'/'.$s; ?>"><?php echo $i.'/'.$s; ?></option>
              <?php
            }
          }
          ?>
        </select>
      </div>
    </div>
    <div class="kr-admin-field" kr-step-show="form" style="display:none;">
      <div>
        <label><?php echo $Lang->tr('Form input list'); ?></label>
      </div>
      <div class="kr-admin-field-ws">
        <?php
        for ($i=0; $i < 5; $i++) {
          ?>
          <div style="margin-bottom:15px;width:100%;">
            <input type="text" style="margin-right:5px" placeholder="Field name" name="kr-identity-form-s[]" value="">
            <input type="text" style="margin-left:5px" placeholder="Field exemple" name="kr-identity-form-sample[]" value="">
          </div>
          <?php
        }
        ?>
      </div>
    </div>
    <div class="kr-admin-action">
      <input type="submit" class="btn btn-green" style="margin-bottom:15px;" name="" value="<?php echo $Lang->tr('Add new'); ?>">
    </div>
  </form>

  <h3><?php echo $Lang->tr('Document identity list'); ?></h3>
  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Document name'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($Identity->_getDocList() as $key => $value) {
          ?>
          <tr>
            <td><?php echo $value['name_identity_doclist']; ?></td>
            <td>
              <form method="post" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/deleteIdentityDocument.php" class="kr-adm-post-evs kr-adm-post-evs-confirm kr-admin-line kr-admin-line-cls">
                <input type="hidden" name="id_identity_doclist" value="<?php echo App::encrypt_decrypt('encrypt', $value['id_identity_doclist']); ?>">
                <input type="submit" class="btn btn-small btn-red btn-autowidth" name="remove_social" value="<?php echo $Lang->tr('Delete'); ?>">
              </form>
            </td>
          </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
  </div>

  <h3><?php echo $Lang->tr('Add new identity document'); ?></h3>
  <form class="kr-adm-post-evs kr-admin-line kr-admin-line-cls" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/addIdentityDocument.php" method="post">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Document name'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your document name'); ?>" name="name_doc" value="">
      </div>
    </div>
    <div class="kr-admin-action">
      <input type="submit" class="btn btn-green" style="margin-bottom:15px;" name="" value="<?php echo $Lang->tr('Add new'); ?>">
    </div>
  </form>
</section>
