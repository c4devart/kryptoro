<?php

/**
 * Admin news social settings
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

// Check loggin & permission
$User = new User();
if(!$User->_isLogged()) throw new Exception("User are not logged", 1);
if(!$User->_isAdmin()) throw new Exception("Permission denied", 1);

// Init language object
$Lang = new Lang($User->_getLang(), $App);

// Init admin object
$Admin = new Admin();

$BlockExplorer = new BlockExplorer($App, $User);

$Balance = new Balance($User, $App);
$Balance = $Balance->_getCurrentBalance();


?>
<section class="kr-admin">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Trading' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>

  <h3><?php echo $Lang->tr('Wallet addresses'); ?></h3>

  <form class="kr-adm-post-evs" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/saveWallets.php" method="post">
    <footer style="display:flex;justify-content:flex-end;padding:15px 15px 0px 15px;">
      <input type="submit" class="btn btn-green btn-autowidth" name="" value="<?php echo $Lang->tr('Save'); ?>">
    </footer>
    <div class="kr-admin-table">
      <table>
        <thead>
          <tr>
            <td><?php echo $Lang->tr('Symbol'); ?></td>
            <td><?php echo $Lang->tr('Address'); ?></td>
            <td><?php echo $Lang->tr('Number confirmations need'); ?></td>
            <td></td>
          </tr>
        </thead>
        <tbody>
          <?php
          $DepositAddressList = $BlockExplorer->_getDepositAddress();
          foreach ($Balance->_getBalanceListResum() as $symbol => $value) {
            if($Balance->_symbolIsMoney($symbol)) continue;
            $Address = null;
            if(array_key_exists($symbol, $DepositAddressList)) $Address = $DepositAddressList[$symbol];
            ?>
            <tr>
              <td><?php echo $symbol; ?></td>
              <td><input type="text" class="kr-admn-inptx" name="addr_<?php echo $symbol; ?>" value="<?php echo (is_null($Address) ? '' : $Address->_getAddress()); ?>"/></td>
              <td><input type="number" class="kr-admn-inptx" name="confirm_<?php echo $symbol; ?>" min="1" step="1" max="9999" value="<?php echo (is_null($Address) ? '' : $Address->_getNbVerification()); ?>"/></td>
            </tr>
            <?php
          }
          ?>
        </tbody>
      </table>
    </div>
    <footer style="display:flex;justify-content:flex-end;padding:0px 15px 15px 15px;">
      <input type="submit" class="btn btn-green btn-autowidth" name="" value="<?php echo $Lang->tr('Save'); ?>">
    </footer>
  </form>

</section>
