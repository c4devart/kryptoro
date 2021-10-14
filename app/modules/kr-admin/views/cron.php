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
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Cron' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>
  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Url'); ?></td>
          <td><?php echo $Lang->tr('Cron time'); ?></td>
          <td><?php echo $Lang->tr('Last update'); ?></td>
          <td><?php echo $Lang->tr('Status'); ?></td>
        </tr>
      </thead>
      <tbody>
        <?php

        foreach ($Admin->_getCronListStatus() as $url => $infosUrl) { // Get list coins
          ?>
           <tr>
            <td>
              <div class="kr-admin-coin-nsa">
                <span><?php echo APP_URL.'/'.$url; ?></span>
              </div>
            </td>
            <td><?php echo $Lang->tr('Every').' '.($infosUrl['every'] / 60).' minute'.(($infosUrl['every'] / 60) > 1 ? 's' : ''); ?></td>
            <td><?php echo $infosUrl['last_update']; ?></td>
            <td><?php
                if($infosUrl['status'] == 2){
                  echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag">'.$Lang->tr('Active').'</span>';
                }

                if($infosUrl['status'] == 0){
                  echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-red">'.$Lang->tr('Inactive').'</span>';
                }

                if($infosUrl['status'] == 1){
                  echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-blue">'.$Lang->tr('Sync delay').'</span>';
                }
            ?></td>
          </tr>
          <?php
        }
        ?>

      </tbody>
    </table>
  </div>
</section>
