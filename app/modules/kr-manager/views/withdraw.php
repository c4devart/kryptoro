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
if(!$User->_isAdmin() && !$User->_isManager()) throw new Exception("Permission denied", 1);

// Init language object
$Lang = new Lang($User->_getLang(), $App);

$Manager = new Manager($App);

$SearchQuery = "";
if(!empty($_POST) && isset($_POST['search']) && !empty($_POST['search'])) $SearchQuery = $_POST['search'];

$filter = 'default';
if(!empty($_POST) && isset($_POST['filter']) && array_key_exists($_POST['filter'], $Manager->_getWidthdrawFilters())) $filter = $_POST['filter'];

// Init admin object
$Admin = new Admin();

?>
<section class="kr-manager kr-admin">
  <nav class="kr-manager-nav">
    <ul>
      <?php foreach ($Manager->_getListSection() as $key => $section) { // Get list admin section
        $NotificationNumber = $Manager->_getNumberManagerNotification(strtolower(str_replace(' ', '', $section)));
        echo '<li type="module" kr-module="manager" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Withdraw' ? 'class="kr-manager-nav-selected"' : '').'>'.$Lang->tr($section).' '.($NotificationNumber > 0 ? '<span>'.$NotificationNumber.'</span>' : '').'</li>';
      } ?>
    </ul>
  </nav>

  <div class="kr-manager-filter">
    <form class="kr-manager-filter-search-f" kr-manager-v="withdraw">
      <input type="text" name="" placeholder="<?php echo $Lang->tr('Ref, User ID, Type, Currency'); ?>" value="<?php echo $SearchQuery; ?>">
    </form>
    <div class="kr-manager-switch-opt">
      <span><?php echo $Lang->tr('Withdraw filter'); ?></span>
      <select class="kr-manager-switch-opt-swtf" kr-manager-v="withdraw" name="">
        <?php
        foreach ($Manager->_getWidthdrawFilters() as $vFilter => $tFilter) {
          if(!$App->_getPaymentApproveNeeded() && $vFilter == "needapp") continue;
          echo '<option '.($filter == $vFilter ? 'selected' : '').' value="'.$vFilter.'">'.$Lang->tr($tFilter).'</option>';
        }
        ?>
      </select>
    </div>
  </div>

  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Ref.'); ?></td>
          <td><?php echo $Lang->tr('Name'); ?></td>
          <td><?php echo $Lang->tr('Email'); ?></td>
          <td><?php echo $Lang->tr('Withdraw method'); ?></td>
          <td><?php echo $Lang->tr('Withdraw infos'); ?></td>
          <td><?php echo $Lang->tr('Date'); ?></td>
          <td><?php echo $Lang->tr('Amount'); ?></td>
          <td><?php echo $Lang->tr('Fees'); ?></td>
          <td><?php echo $Lang->tr('Total'); ?></td>
          <td><?php echo $Lang->tr('Status'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($Admin->_getWithdrawList($SearchQuery) as $WithdrawItem) { // Get list users
          $UserItem = $WithdrawItem['user_details'];


          if($filter == 'default' && strlen($SearchQuery) == 0){
            if($WithdrawItem['status_widthdraw_history'] == 0) continue;
            if($WithdrawItem['status_widthdraw_history'] == -1) continue;
          }

          if($filter == 'not_confirmed' && strlen($SearchQuery) == 0){
            if($WithdrawItem['status_widthdraw_history'] != 0) continue;
          }

          if($filter == 'done' && strlen($SearchQuery) == 0){
            if($WithdrawItem['status_widthdraw_history'] != 2) continue;
          }

          if($filter == "pending" && strlen($SearchQuery) == 0){
            if($WithdrawItem['status_widthdraw_history'] != 1) continue;
          }

          if($filter == "canceled" && strlen($SearchQuery) == 0){
            if($WithdrawItem['status_widthdraw_history'] != -1) continue;
          }

          if($WithdrawItem['method_widthdraw_history'] != 0){
            $Widthdraw = new Widthdraw();
            $WidthdrawConfiguration = $Widthdraw->_getWidthdrawMethod();
            $infosWithdraw = $Widthdraw->_getInformationWithdrawMethod($WithdrawItem['method_widthdraw_history']);
          }

          ?>
          <tr>
            <td>
              <b><?php echo (strlen($WithdrawItem['ref_widthdraw_history']) > 0 ? $WithdrawItem['ref_widthdraw_history'] : $WithdrawItem['id_user'].'-'.$WithdrawItem['id_widthdraw_history']); ?></b>
            </td>
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
            <?php if($WithdrawItem['method_widthdraw_history'] != 0){ ?>
            <td><?php echo ucfirst($Lang->tr($infosWithdraw['type_user_widthdraw'])); ?></td>
              <?php } else {
                ?>
                <td><?php echo $Lang->tr('Paypal'); ?></td>
                <?php
              } ?>
            <td>
              <?php
                if($WithdrawItem['method_widthdraw_history'] == 0){
                  echo 'Paypal : '.$WithdrawItem['paypal_widthdraw_history'];
                } else {
                  echo '<button class="btn btn-small btn-autowidth" onclick="_showWithdrawMethod(\''.App::encrypt_decrypt('encrypt', $WithdrawItem['method_widthdraw_history']).'\')" type="button" name="button">'.$Lang->tr('Show withdraw receiver infos.').'</button>';
                }
                ?>
            </td>

            <td>
              <div class="kr-admin-user-oauth">
                <?php echo date('d/m/Y H:i:s', $WithdrawItem['date_widthdraw_history']); ?>
              </div>
            </td>
            <td><?php echo $App->_formatNumber($WithdrawItem['amount_widthdraw_history'], ($WithdrawItem['amount_widthdraw_history'] > 10 ? 2 : 7)).' '.$WithdrawItem['symbol_widthdraw_history']; ?></td>
            <td><?php echo $App->_formatNumber($WithdrawItem['fees_widthdraw_history'], ($WithdrawItem['fees_widthdraw_history'] > 10 ? 2 : 7)).' '.$WithdrawItem['symbol_widthdraw_history']; ?></td>
            <td><b><?php echo $App->_formatNumber($WithdrawItem['amount_widthdraw_history'] - $WithdrawItem['fees_widthdraw_history'], ($WithdrawItem['amount_widthdraw_history'] - $WithdrawItem['fees_widthdraw_history'] > 10 ? 2 : 7)).' '.$WithdrawItem['symbol_widthdraw_history']; ?></b></td>
            <td>
              <?php
              if($WithdrawItem['status_widthdraw_history'] == 1):
              ?>
              <form class="kr-admin kr-adm-post-evs" style="margin-bottom:5px;" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/doneWithdraw.php" method="post">
                <input type="hidden" name="request_id" value="<?php echo App::encrypt_decrypt('encrypt', time().'-'.$WithdrawItem['id_widthdraw_history']); ?>">
                <input type="submit" class="btn btn-small btn-autowidth btn-green" value="<?php echo $Lang->tr('Set as done'); ?>">
              </form>
              <form class="kr-admin kr-adm-post-evs" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/cancelWithdraw.php" method="post">
                <input type="hidden" name="request_id" value="<?php echo App::encrypt_decrypt('encrypt', time().'-'.$WithdrawItem['id_widthdraw_history']); ?>">
                <input type="submit" class="btn btn-small btn-autowidth btn-red" value="<?php echo $Lang->tr('Cancel'); ?>">
              </form>
              <?php
              elseif($WithdrawItem['status_widthdraw_history'] == 2): ?>
              <span class="kr-admin-lst-tag kr-admin-lst-tag-green"><?php echo $Lang->tr('Done'); ?></span>

            <?php elseif($WithdrawItem['status_widthdraw_history'] == -1):
              ?>
                <span class="kr-admin-lst-tag kr-admin-lst-tag-grey"><?php echo $Lang->tr('Canceled'); ?></span>
              <?php else: ?>
              <span class="kr-admin-lst-tag kr-admin-lst-tag-red"><?php echo $Lang->tr('Not confirmed'); ?></span>
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
