<?php

/**
 * Admin Subscriptions settings
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

// Init admin object
$Admin = new Admin();

// Init charge object
$Charge = $User->_getCharge($App);

?>
<section class="kr-admin">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Subscriptions' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>
  <form method="post"action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/saveSubscription.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Enable subscriptions'); ?></label>
        </div>
        <div>
          <?php
          if(!$App->_creditCardEnabled() && !$App->_paypalEnabled() && !$App->_coingateEnabled() && !$App->_fortumoEnabled()):
            ?>
            <section class="kr-msg kr-msg-error" style="display:flex;">
              <?php echo $Lang->tr('Credit card or Paypal payment need to be enabled !'); ?>
            </section>
          <?php else: ?>
            <div class="ckbx-style-14">
                <input type="checkbox" id="kr-adm-chk-subscriptionenabled" <?php echo ($App->_subscriptionEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-subscriptionenabled">
                <label for="kr-adm-chk-subscriptionenabled"></label>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Enable free trial'); ?></label>
        </div>
        <div>
          <div class="ckbx-style-14">
              <input type="checkbox" id="kr-adm-chk-enablefreetrial" <?php echo ($App->_freetrialEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablefreetrial">
              <label for="kr-adm-chk-enablefreetrial"></label>
          </div>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Free trial duration (in day)'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Free trial duration'); ?>" name="kr-adm-freetrialduration" value="<?php echo $App->_getChargeTrialDay(); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Premium features'); ?></label>
        </div>
        <div>
          <textarea name="kr-adm-premiumfeatures"><?php echo str_replace('<br>', '', $App->_getChargeText()); ?></textarea>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Free users features'); ?></label>
        </div>
        <div>
          <ul>
            <?php foreach ($App->_getFeaturesAllowedFree() as $feature => $val) {
              ?>
              <li>
                <span><?php echo $feature; ?></span>
                <div class="ckbx-style-14">
                    <input type="checkbox" id="kr-adm-chk-feature-<?php echo $feature; ?>" <?php echo ($val == 1 ? 'checked' : ''); ?> name="kr-adm-chk-feature-<?php echo $feature; ?>">
                    <label for="kr-adm-chk-feature-<?php echo $feature; ?>"></label>
                </div>
              </li>
              <?php
            } ?>
          </ul>
        </div>
      </div>
    </div>
    <div class="kr-admin-field-ws" style="display:flex;justify-content:flex-end;">
      <input type="submit" class="btn btn-orange" name="" value="<?php echo $Lang->tr('Save'); ?>">
    </div>
  </form>

  <h3><?php echo $Lang->tr('Add a new plan'); ?></h3>
  <form method="post"action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/addPlanSubscription.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Name plan'); ?></label>
      </div>
      <div class="kr-admin-field-ws">
        <input type="text" placeholder="<?php echo $Lang->tr('Name plan'); ?>" name="kr-adm-nameplan" value="">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Price total'); ?></label>
      </div>
      <div class="kr-admin-field-ws">
        <input type="text" placeholder="<?php echo $Lang->tr('Price total'); ?> (ex : 19.50)" name="kr-adm-totalprice" value="">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Duration in days'); ?></label>
      </div>
      <div class="kr-admin-field-ws">
        <input type="text" placeholder="<?php echo $Lang->tr('Duration'); ?> (ex : 31)" name="kr-adm-durationdays" value="">
        <input type="submit" class="btn-shadow btn-orange" name="" value="<?php echo $Lang->tr('Add this plan'); ?>">
      </div>
    </div>
  </form>
  <h3><?php echo $Lang->tr('Plans available'); ?></h3>
  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Name plan'); ?></td>
          <td><?php echo $Lang->tr('Price plan'); ?></td>
          <td><?php echo $Lang->tr('Duration in days'); ?></td>
          <td><?php echo $Lang->tr('Duration in month'); ?></td>
          <td><?php echo $Lang->tr('Price per month'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($Charge->_getChargesPlanList() as $Plan) { // Get list plan available
          ?>
          <tr>
            <td><?php echo $Plan->_getName(); ?></td>
            <td><?php echo $Plan->_getPrice(true); ?></td>
            <td><?php echo $Plan->_getDuration(); ?></td>
            <td><?php echo $Plan->_getNumberMonth(); ?></td>
            <td><?php echo $Plan->_getPricePerMonth(true); ?></td>
            <td>
              <form method="post" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/removePlanSubscription.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
                <input type="hidden" name="plan_id" value="<?php echo $Plan->_getPlanID(); ?>">
                <input type="submit" class="btn btn-small btn-autowidth" name="remove_plan" value="Delete">
              </form>
            </td>
          </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
