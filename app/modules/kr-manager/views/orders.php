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

$Identity = new Identity($User);

// Init admin object
$Manager = new Manager($App);

$SearchQuery = "";
if(!empty($_POST) && isset($_POST['search']) && !empty($_POST['search'])) $SearchQuery = $_POST['search'];

$SelectedDateEnd = new DateTime();
$SelectedDateStart = new DateTime();
$SelectedDateStart->sub(new DateInterval('P7D'));
if(!empty($_POST) && isset($_POST['startdate']) && isset($_POST['enddate'])){
  $SelectedDateEnd = new DateTime($_POST['enddate']);
  $SelectedDateStart = new DateTime($_POST['startdate']);
}

?>
<section class="kr-manager kr-admin">
  <nav class="kr-manager-nav">
    <ul>
      <?php foreach ($Manager->_getListSection() as $key => $section) { // Get list admin section
        $NotificationNumber = $Manager->_getNumberManagerNotification(strtolower(str_replace(' ', '', $section)));
        echo '<li type="module" kr-module="manager" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.(strtolower(str_replace(' ', '', $section)) == 'orders' ? 'class="kr-manager-nav-selected"' : '').'>'.$Lang->tr($section).' '.($NotificationNumber > 0 ? '<span>'.$NotificationNumber.'</span>' : '').'</li>';
      } ?>
    </ul>
  </nav>

  <div class="kr-manager-filter">
    <form class="kr-manager-filter-search-f" kr-manager-v="orders">
      <input type="text" name="" placeholder="Ref, User ID, Pair, Exchange" value="<?php echo $SearchQuery; ?>">
    </form>
    <input type="text" kr-view-used="orders" name="daterange" start-date="<?php echo $SelectedDateStart->format('d/m/Y'); ?>" end-date="<?php echo $SelectedDateEnd->format('d/m/Y'); ?>" value="<?php echo $SelectedDateStart->format('d/m/Y'); ?> - <?php echo $SelectedDateEnd->format('d/m/Y'); ?>" />
  </div>

  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Ref'); ?></td>
          <td><?php echo $Lang->tr('User'); ?></td>
          <td><?php echo $Lang->tr('Order date'); ?></td>
          <td><?php echo $Lang->tr('Exchange'); ?></td>
          <td><?php echo $Lang->tr('Pair'); ?></td>
          <td><?php echo $Lang->tr('Type'); ?></td>
          <td><?php echo $Lang->tr('Amount'); ?></td>
          <td><?php echo $Lang->tr('Received'); ?></td>
          <td><?php echo $Lang->tr('Fees'); ?></td>
          <td><?php echo $Lang->tr('Total deducted'); ?></td>
          <td><?php echo $Lang->tr('Total received'); ?></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($Manager->_getInternalOrderList(null, (strlen($SearchQuery) > 0 ? $SearchQuery : null), $SelectedDateStart, $SelectedDateEnd) as $key => $orderInfos) {
          $UserInfos = new User($orderInfos['id_user']);
          ?>
          <tr>
            <td>
              <b><?php echo (strlen($orderInfos['ref_internal_order']) > 0 ? $orderInfos['ref_internal_order'] : $orderInfos['id_user'].'-'.$orderInfos['id_internal_order'] ); ?></b>
            </td>
             <td>
               <div class="kr-admin-coin-nsa">
                 <span><?php echo '#'.$UserInfos->_getUserID().' - '.$UserInfos->_getName(); ?></span>
               </div>
             </td>
             <td>
               <?php echo date('d/m/Y H:i:s', $orderInfos['date_internal_order']); ?>
             </td>
             <td>
               <?php echo ucfirst($orderInfos['thirdparty_internal_order']); ?>
             </td>
             <td>
               <?php echo $orderInfos['symbol_internal_order'].'/'.$orderInfos['to_internal_order']; ?>
             </td>
             <td>
               <?php
               if($orderInfos['side_internal_order'] == "SELL") echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-red">'.$Lang->tr('Sell').'</span>';
               if($orderInfos['side_internal_order'] == "BUY") echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-green">'.$Lang->tr('Buy').'</span>';
               ?>
             </td>
             <td>
               <?php echo rtrim($App->_formatNumber(($orderInfos['side_internal_order'] == "BUY" ? $orderInfos['usd_amount_internal_order'] : $orderInfos['amount_internal_order']), 8), "0").' '.$orderInfos['symbol_internal_order']; ?>
             </td>
             <td>
               <?php echo $App->_formatNumber($orderInfos['usd_amount_internal_order'], 8).' '.($orderInfos['side_internal_order'] == "BUY" ? $orderInfos['symbol_internal_order'] : $orderInfos['to_internal_order']); ?>
             </td>
             <td>
               <span title="<?php echo $App->_formatNumber($orderInfos['fees_internal_order'], 15); ?>"><?php echo $App->_formatNumber($orderInfos['fees_internal_order'], 8).' '.($orderInfos['side_internal_order'] == "BUY" ? $orderInfos['symbol_internal_order'] : $orderInfos['to_internal_order']); ?></span>
             </td>
             <td>
               <span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-red" title="- <?php echo $App->_formatNumber($orderInfos['amount_internal_order'], 15); ?>">- <?php echo $App->_formatNumber($orderInfos['amount_internal_order'], 8).' '.($orderInfos['side_internal_order'] == "BUY" ? $orderInfos['to_internal_order'] : $orderInfos['symbol_internal_order']); ?></span>
             </td>
             <td>
               <span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-green" title="+ <?php echo $App->_formatNumber($orderInfos['usd_amount_internal_order'] - $orderInfos['fees_internal_order'], 15); ?>">+ <?php echo $App->_formatNumber($orderInfos['usd_amount_internal_order'] - $orderInfos['fees_internal_order'], 8).' '.($orderInfos['side_internal_order'] == "BUY" ? $orderInfos['symbol_internal_order'] : $orderInfos['to_internal_order']); ?></span>
             </td>
           </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
  </div>

</section>
