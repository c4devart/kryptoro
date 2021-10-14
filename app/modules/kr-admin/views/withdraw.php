<?php

/**
 * Admin users list
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

?>
<section class="kr-admin">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Withdraw' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>
  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Name'); ?></td>
          <td><?php echo $Lang->tr('Email'); ?></td>
          <td><?php echo $Lang->tr('Paypal email'); ?></td>
          <td><?php echo $Lang->tr('Date'); ?></td>
          <td><?php echo $Lang->tr('Amount'); ?></td>
          <td><?php echo $Lang->tr('Status'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($Admin->_getWithdrawList() as $WithdrawItem) { // Get list users
          $UserItem = $WithdrawItem['user_details'];
          ?>
          <tr>
            <td>
              <div class="kr-admin-user-v">
                <div class="" style="background-image:url('<?php echo $UserItem->_getPicture(); ?>')"></div>
                <span>
                  <?php if($UserItem->_isAdmin()): ?>
                  <svg class="lnr lnr-diamond"><use xlink:href="#lnr-diamond"></use></svg>
                  <?php endif; echo $UserItem->_getName(); ?>
                  <i class="<?php echo ($UserItem->_isActive() ? 'kr-admin-user-i-active' : ''); ?>"></i>
                </span>
              </div>
            </td>
            <td><?php echo $UserItem->_getEmail(); ?></td>
            <td>
              <?php echo $WithdrawItem['paypal_widthdraw_history']; ?>
            </td>
            <td>
              <div class="kr-admin-user-oauth">
                <?php echo date('d/m/Y H:i:s', $WithdrawItem['date_widthdraw_history']); ?>
              </div>
            </td>
            <td><?php echo $App->_formatNumber($WithdrawItem['amount_widthdraw_history'], 2); ?> $</td>
            <td>
              <?php
              if($WithdrawItem['status_widthdraw_history'] == 1):
              ?>
              <form class="kr-admin kr-adm-post-evs" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/doneWithdraw.php" method="post">
                <input type="hidden" name="request_id" value="<?php echo App::encrypt_decrypt('encrypt', time().'-'.$WithdrawItem['id_widthdraw_history']); ?>">
                <input type="submit" class="btn btn-small btn-autowidth" value="<?php echo $Lang->tr('Set as done'); ?>">
              </form>
              <?php
              elseif($WithdrawItem['status_widthdraw_history'] == 2): ?>

              <span class="kr-admin-lst-tag kr-admin-lst-tag-green">Done</span>

              <?php else:
              ?>
              <span class="kr-admin-lst-tag kr-admin-lst-tag-red">Not confirmed</span>
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
