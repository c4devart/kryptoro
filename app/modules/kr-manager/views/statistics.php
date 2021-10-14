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

// Init admin object
$Manager = new Manager($App);

$SelectedDateEnd = new DateTime('now');
$SelectedDateStart = new DateTime('now');

$SelectedDateStart->sub(new DateInterval('P7D'));
$Statistics = new Statistics();
if(!empty($_POST) && isset($_POST['startdate']) && isset($_POST['enddate'])){
  $SelectedDateEnd = new DateTime($_POST['enddate']);
  $SelectedDateStart = new DateTime($_POST['startdate']);
  $Statistics = new Statistics(new DateTime($_POST['startdate']), new DateTime($_POST['enddate']));
}

?>
<section class="kr-manager">
  <nav class="kr-manager-nav">
    <ul>
      <?php foreach ($Manager->_getListSection() as $key => $section) { // Get list admin section
        $NotificationNumber = $Manager->_getNumberManagerNotification(strtolower(str_replace(' ', '', $section)));
        echo '<li type="module" kr-module="manager" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Statistics' ? 'class="kr-manager-nav-selected"' : '').'>'.$Lang->tr($section).' '.($NotificationNumber > 0 ? '<span>'.$NotificationNumber.'</span>' : '').'</li>';
      } ?>
    </ul>
  </nav>

  <section class="kr-manager-stats">

    <header>
      <span><?php echo $Lang->tr('Statistics'); ?> - <?php echo $Statistics->_getStartingDate()->format('d/m/Y'); ?> to <?php echo $Statistics->_getEndingDate()->format('d/m/Y'); ?></span>
      <input type="text" kr-view-used="statistics" name="daterange" start-date="<?php echo $SelectedDateStart->format('m/d/Y'); ?>" end-date="<?php echo $SelectedDateEnd->format('m/d/Y'); ?>" value="<?php echo $SelectedDateStart->format('d/m/Y'); ?> - <?php echo $SelectedDateEnd->format('d/m/Y'); ?>" />
    </header>

    <section class="kr-manager-stats-graph" kr-stats-mananger-xlist="<?php echo join(',', $Statistics->_generateListDate()); ?>"
                                            kr-stats-mananger-datasetlist="<?php echo join(',', $Statistics->_generateDataSet($Statistics->_getListUser())); ?>;
                                                                           <?php echo join(',', $Statistics->_generateDataSet($Statistics->_getListDeposit())); ?>;
                                                                           <?php echo join(',', $Statistics->_generateDataSet($Statistics->_getListWidthdraw())); ?>;
                                                                           <?php echo ($App->_getIdentityEnabled() ? join(',', $Statistics->_generateDataSet($Statistics->_getListIdentity())).';' : ''); ?>
                                                                           <?php echo join(',', $Statistics->_generateDataSet($Statistics->_getListOrderPassed())); ?>;
                                                                           <?php echo join(',', $Statistics->_generateDataSet($Statistics->_getListSubscription())); ?>"
                                            kr-stats-mananger-datesettag="New user;Deposit;Widthdraw;<?php echo ($App->_getIdentityEnabled() ? 'Identity verification;' : ''); ?>Order;Subscription"
                                            kr-stats-mananger-color="#4286f4,#f44141,#f4ac41,#41f47c,#c441f4,#41d9f4">
      <canvas id="kr-stats-manager-chart"></canvas>
    </section>

    <ul class="kr-manager-stats-infos">
      <li>
        <label><?php echo $Lang->tr('New user'); ?></label>
        <span><?php echo $App->_formatNumber($Statistics->_getSumDateSet($Statistics->_getListUser()), 0); ?></span>
      </li>
      <li>
        <label><?php echo $Lang->tr('Deposit'); ?></label>
        <span><?php echo $App->_formatNumber($Statistics->_getSumDateSet($Statistics->_getListDeposit()), 0); ?></span>
      </li>
      <li>
        <label><?php echo $Lang->tr('Withdraw'); ?></label>
        <span><?php echo $App->_formatNumber($Statistics->_getSumDateSet($Statistics->_getListWidthdraw()), 0); ?></span>
      </li>
      <?php if($App->_getIdentityEnabled()): ?>
        <li>
          <label><?php echo $Lang->tr('Identity verification'); ?></label>
          <span><?php echo $App->_formatNumber($Statistics->_getSumDateSet($Statistics->_getListIdentity()), 0); ?></span>
        </li>
      <?php endif; ?>
      <li>
        <label><?php echo $Lang->tr('Order passed'); ?></label>
        <span><?php echo $App->_formatNumber($Statistics->_getSumDateSet($Statistics->_getListOrderPassed()), 0); ?></span>
      </li>
      <li>
        <label><?php echo $Lang->tr('New subscription'); ?></label>
        <span><?php echo $App->_formatNumber($Statistics->_getSumDateSet($Statistics->_getListSubscription()), 0); ?></span>
      </li>
    </ul>

  </section>


  <?php
  $Benef = $Statistics->_getFeesList($App);
  ?>
  <div class="kr-stats-margin-content">
    <table class="kr-stats-margin">
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Currency'); ?></td>
          <td><?php echo $Lang->tr('Total trade'); ?></td>
          <td><?php echo $Lang->tr('Total deposit'); ?></td>
          <td><?php echo $Lang->tr('Total withdraw'); ?></td>
          <td><?php echo $Lang->tr('Fees'); ?></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($Benef as $currency => $infosBenf) {
          ?>
          <tr>
            <td><?php echo $currency; ?></td>
            <td><?php echo $App->_formatNumber($infosBenf['total_trade'], ($infosBenf['total_trade'] > 10 ? 2 : 7)); ?></td>
            <td><?php echo $App->_formatNumber($infosBenf['total_deposit'], ($infosBenf['total_deposit'] > 10 ? 2 : 7)); ?></td>
            <td><?php echo $App->_formatNumber($infosBenf['total_withdraw'], ($infosBenf['total_withdraw'] > 10 ? 2 : 7)); ?></td>
            <td><?php echo $App->_formatNumber($infosBenf['fees'], ($infosBenf['fees'] > 10 ? 2 : 7)); ?></td>
          </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
  </div>
</section>
