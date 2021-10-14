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

$Balance = new Balance($User, $App);
$Balance = $Balance->_getCurrentBalance();
$BalanceList = $Balance->_getBalanceListResum();

$Widthdraw = new Widthdraw();
$WidthdrawList = $Widthdraw->_getExchangeByCoins(array_keys($BalanceList), array_keys($App->_hiddenThirdpartyServiceCfg()));

?>
<section class="kr-admin">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Trading' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>

  <h3><?php echo $Lang->tr('Withdraw exchanges cryptocurrencies associate'); ?></h3>

  <form class="kr-adm-post-evs" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/saveWithdrawExchange.php" method="post">
    <footer style="display:flex;justify-content:flex-end;padding:15px 15px 0px 15px;">
      <input type="submit" class="btn btn-green btn-autowidth" name="" value="<?php echo $Lang->tr('Save'); ?>">
    </footer>
    <div class="kr-admin-table">
      <table>
        <thead>
          <tr>
            <td><?php echo $Lang->tr('Symbol'); ?></td>
            <td><?php echo $Lang->tr('Exchange'); ?></td>
            <td></td>
          </tr>
        </thead>
        <tbody>
          <?php

          $ExchangeAssociate = $Widthdraw->_getWithrawExchangeAssociate();

          foreach ($BalanceList as $symbol => $value) {
            if($Balance->_symbolIsMoney($symbol)) continue;
            ?>
            <tr>
              <td><?php echo $symbol; ?></td>
              <td>
                <?php if(array_key_exists($symbol, $WidthdrawList)): ?>
                  <select name="with_<?php echo $symbol; ?>">
                    <?php
                    foreach ($WidthdrawList[$symbol] as $keyEx => $valueEx) {
                      echo '<option '.($ExchangeAssociate[$symbol] == $keyEx ? 'selected' : '').' value="'.strtolower($keyEx).'">'.ucfirst($keyEx).'</option>';
                    }
                    ?>
                  </select>
                  <?php if(count($WidthdrawList[$symbol]) > 1): ?>
                    <span style="font-size:12px;">(<?php echo count($WidthdrawList[$symbol]).' '.$Lang->tr('exchanges available'); ?>)</span>
                  <?php endif; ?>
                <?php else: ?>
                  <span><?php echo $Lang->tr('No exchange available to this symbol'); ?></span>
                <?php endif; ?>
              </td>
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
