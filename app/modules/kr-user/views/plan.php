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

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

// Check if user is logged
$User = new User();
if(!$User->_isLogged()) die('User not logged');

// Init lang object
$Lang = new Lang($User->_getLang(), $App);

// Load user charge object
$Charge = $User->_getCharge($App);

?>
<section>
  <header>
    <span><?php echo $Lang->tr('Your trial version have expired !'); ?></span>
    <div>
      <?php if($Charge->_activeAbo() || $Charge->_isTrial() || $User->_isAdmin()): ?>
        <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
      <?php endif; ?>
    </div>
  </header>
  <h2><?php echo $Lang->tr("Become"); ?> <b><?php echo $App->_getPremiumName(); ?></b></h2>
  <ul>
    <?php
    $chargeList = $Charge->_getChargesPlanList();
    foreach ($chargeList as $KeyPlan => $ChargePlan) {
      $DiscountPlan = $ChargePlan->_getDiscountPercentage();
    ?>
      <li kr-charges-sel="<?php echo $ChargePlan->_getPlanID(); ?>" <?php if(count($chargeList) != $KeyPlan) echo 'class="kr-ov-charges-nr"'; ?>>
        <?php
        if(count($chargeList) == $KeyPlan):
        ?>
          <header class="kr-mono"><?php echo $Lang->tr("Recommended"); ?></header>
        <?php endif; ?>
        <div>
          <span><?php echo $ChargePlan->_getName(); ?></span>
          <div>
            <span><?php echo $ChargePlan->_getPricePerMonth(true).' '.$Charge->_getCurrencySymbol(); ?></span>
            <?php if($DiscountPlan != null): ?>
              <label><?php echo $DiscountPlan.' %'; ?></label>
            <?php endif; ?>
          </div>
          <label><?php echo $Lang->tr("You will be charged"); ?> <?php echo $ChargePlan->_getPrice(true).' '.$Charge->_getCurrencySymbol().' '.$Lang->tr('for').' '.$ChargePlan->_getNumberMonth().' '.($ChargePlan->_getNumberMonth() > 0 ? $Lang->tr('months') : $Lang->tr('month')); ?></label>
        </div>
      </li>
    <?php } ?>
  </ul>
  <section>
    <ul>
      <?php
      foreach ($Charge->_parseChargeText() as $feature) {
        echo '<li><svg class="lnr lnr-checkmark-circle"><use xlink:href="#lnr-checkmark-circle"></use></svg> <span>'.$Lang->tr(trim($feature)).'</span></li>';
      }
      ?>
    </ul>
  </section>
</section>
