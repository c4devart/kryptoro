<?php

/**
 * Admin coin list page
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

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

// Init Crypto API Object
$CryptoApi = new CryptoApi(null, null, $App);

// Manage pagination
$pagenum = 0;
if(!empty($_POST) && !empty($_POST['page']) && is_numeric($_POST['page'])) $pagenum = ($_POST['page'] - 1);

?>

<section class="kr-admin">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Coins' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>
  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Name'); ?></td>
          <td><?php echo $Lang->tr('Symbol'); ?></td>
          <td><?php echo $Lang->tr('Status'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($CryptoApi->_getCoinsList(50, true, false, null, $pagenum * 50, false, true) as $Coin) { // Get list coins
          ?>
           <tr>
            <td>
              <div class="kr-admin-coin-nsa">
                <?php if(!is_null($Coin->_getIcon())): ?>
                  <img src="<?php echo $Coin->_getIcon(); ?>" alt="">
                <?php endif; ?>
                <span><?php echo $Coin->_getCoinName(); ?></span>
              </div>
            </td>
            <td><?php echo $Coin->_getSymbol(); ?></td>
            <td><?php echo ($Coin->_isEnabled() ? '<span class="kr-admin-lst-c-status kr-admin-lst-tag">'.$Lang->tr('Enabled').'</span>' : '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-red">'.$Lang->tr('Inactive').'</span>'); ?></td>
            <td style="width:121px;">
              <form class="kr-admin-tggle-coin-status" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/toggleCurrency.php" method="post">
                <input type="hidden" name="symbol" value="<?php echo $Coin->_getSymbol(); ?>">
                <input type="submit" alt-st="<?php echo $Lang->tr(($Coin->_isEnabled() ? 'Inactive' : 'Enabled')); ?>" alt="<?php echo $Lang->tr(($Coin->_isEnabled() ? 'Enable' : 'Disable')); ?>" class="btn btn-black btn-small" name="" value="<?php echo $Lang->tr(($Coin->_isEnabled() ? 'Disable' : 'Enable')); ?>">
              </form>
            </td>
          </tr>
          <?php
        }
        ?>

      </tbody>
    </table>
  </div>
  <ul class="kr-admin-pagination kr-admin-pagination-coins">
    <?php for ($i=0; $i < (ceil(count($Admin->_getListCoins()) / 50)); $i++) { // Pagination system
      echo '<li style="'.($pagenum == $i ? 'background-color:#ef6c00;color:#fff;' : '').'" kr-page="'.($i + 1).'">'.($i + 1).'</li>';
    } ?>
  </ul>
</section>
