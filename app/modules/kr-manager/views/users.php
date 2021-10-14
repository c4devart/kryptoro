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

$SearchQuery = null;
if(!empty($_POST) && isset($_POST['search']) && !empty($_POST['search'])) $SearchQuery = $_POST['search'];


?>
<section class="kr-manager kr-admin">
  <nav class="kr-manager-nav">
    <ul>
      <?php foreach ($Manager->_getListSection() as $key => $section) { // Get list admin section
        $NotificationNumber = $Manager->_getNumberManagerNotification(strtolower(str_replace(' ', '', $section)));
        echo '<li type="module" kr-module="manager" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Users' ? 'class="kr-manager-nav-selected"' : '').'>'.$Lang->tr($section).' '.($NotificationNumber > 0 ? '<span>'.$NotificationNumber.'</span>' : '').'</li>';
      } ?>
    </ul>
  </nav>
  <div class="kr-manager-filter">
    <form class="kr-manager-filter-search-f" kr-manager-v="users">
      <input type="text" name="" placeholder="User ID, Name, Email" value="<?php echo $SearchQuery; ?>">
    </form>
  </div>
  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Name'); ?></td>
          <td><?php echo $Lang->tr('Email'); ?></td>
          <?php if($App->_subscriptionEnabled()): ?>
            <td><?php echo $Lang->tr('Subscription'); ?></td>
          <?php endif; ?>
          <td><?php echo $Lang->tr('Signin method'); ?></td>
          <td><?php echo $Lang->tr('Last login'); ?></td>
          <td><?php echo $Lang->tr('Notifications enabled'); ?></td>
          <td><?php echo $Lang->tr('Currency'); ?></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($Manager->_getUsersList($SearchQuery) as $UserItem) { // Get list users
          $Charge = $UserItem->_getCharge($App);
          ?>
          <tr onclick="showManagerUserInfos('<?php echo App::encrypt_decrypt('encrypt', time().'-'.$UserItem->_getUserID()); ?>');">
            <td>
              <div class="kr-admin-user-v">
                <div class="" style="background-image:url('<?php echo $UserItem->_getPicture(); ?>')"></div>
                <span>
                  <?php if($UserItem->_isAdmin()): ?>
                  <svg class="lnr lnr-diamond"><use xlink:href="#lnr-diamond"></use></svg>
                  <?php endif;
                  if($UserItem->_isManager()): ?>
                  <svg style="fill:orange;" class="lnr lnr-star"><use xlink:href="#lnr-star"></use></svg>
                  <?php endif;echo $UserItem->_getName(); ?>
                  <i class="<?php echo ($UserItem->_isActive() ? 'kr-admin-user-i-active' : ''); ?>"></i>
                </span>
              </div>
            </td>
            <td><?php echo $UserItem->_getEmail(); ?></td>
            <?php if($App->_subscriptionEnabled()): ?>
              <td>
                <?php if($Charge->_activeAbo()): ?>
                  <span class="kr-admin-lst-tag kr-admin-lst-tag-green"><svg class="lnr lnr-diamond"><use xlink:href="#lnr-diamond"></use></svg> <?php echo $Lang->tr('Premium'); ?> (<?php echo $Charge->_getTimeRes().' '.$Lang->tr('day').($Charge->_getTimeRes() > 1 ? 's' : ''); ?>)</span>
                <?php elseif($Charge->_isTrial()): ?>
                  <span class="kr-admin-lst-tag kr-admin-lst-tag-orange"><?php echo $Lang->tr('Trial version'); ?> (<?php echo $Charge->_getTrialNumberDay().' '.$Lang->tr('day').($Charge->_getTrialNumberDay() > 1 ? 's' : ''); ?>)</span>
                <?php else: ?>
                  <span><?php echo $Lang->tr('Inactive'); ?></span>
                <?php endif; ?>
              </td>
            <?php endif; ?>
            <td>
              <div class="kr-admin-user-oauth">
                <?php
                  if($UserItem->_getOauth() != 'standard') echo file_get_contents(APP_URL.'/assets/img/icons/oauth/'.$UserItem->_getOauth().'.svg');
                  echo ucfirst($UserItem->_getOauth());
                  ?>
              </div>
            </td>
            <td><?php echo (is_null($UserItem->_getLastLogin()) ? '-' : $UserItem->_getLastLogin()->format('d/m/Y H:i')); ?></td>
            <td><?php echo (is_null($UserItem->_getPushbulletKey()) ? '-' : $Lang->tr('Enabled')); ?></td>
            <td><?php echo $UserItem->_getCurrency(); ?></td>
          </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
  </div>
</section>
