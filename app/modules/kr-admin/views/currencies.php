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

// Init dashboard object
$Dashboard = new Dashboard(new CryptoApi(null, null, $App), $User);

?>

<section class="kr-admin">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Currencies' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>

  <div class="kr-admin-line">
    <?php
    foreach ($Dashboard->_getListCurrency(400) as $kCurrency => $currency) { // Get list currencies
      if($kCurrency % 4 == 0 && $kCurrency > 2) {
        echo '</div><div class="kr-admin-line">';
      }
      ?>
      <div class="kr-admin-b-currency">
        <div class="kr-admin-b-currency-symbol"><?php echo $currency['symbol_currency']; ?></div>
        <div class="kr-admin-b-currency-dtxt">
          <div class="kr-admin-b-currency-stb">
            <label><?php echo $currency['name_currency'].' <span>('.$currency['code_iso_currency'].')</span>'; ?></label>
          </div>
          <div class="kr-admin-b-currency-nuse">
            <label><?php echo $Lang->tr('Number of users'); ?></label>
            <span class="kr-mono"><?php echo $currency['num_user_currency']; ?></span>
          </div>
        </div>
      </div>
      <?php
    }
    for ($i= ($kCurrency % 4 + 1); $i < 4; $i++) {
      echo '<div class="kr-admin-b-currency" style="background:transparent;"></div>';
    }
    ?>
  </div>
</section>
