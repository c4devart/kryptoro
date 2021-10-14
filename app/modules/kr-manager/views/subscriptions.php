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

?>
<section class="kr-manager kr-admin">
  <nav class="kr-manager-nav">
    <ul>
      <?php foreach ($Manager->_getListSection() as $key => $section) { // Get list admin section
        $NotificationNumber = $Manager->_getNumberManagerNotification(strtolower(str_replace(' ', '', $section)));
        echo '<li type="module" kr-module="manager" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.(strtolower(str_replace(' ', '', $section)) == 'subscriptions' ? 'class="kr-manager-nav-selected"' : '').'>'.$Lang->tr($section).' '.($NotificationNumber > 0 ? '<span>'.$NotificationNumber.'</span>' : '').'</li>';
      } ?>
    </ul>
  </nav>

  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('ID Subscription'); ?></td>
          <td><?php echo $Lang->tr('User'); ?></td>
          <td><?php echo $Lang->tr('Date subscription'); ?></td>
          <td><?php echo $Lang->tr('Number days'); ?></td>
          <td><?php echo $Lang->tr('Subscription expire in'); ?></td>
          <td><?php echo $Lang->tr('Type payment'); ?></td>
          <td><?php echo $Lang->tr('Charge infos.'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($Manager->_getSubscriptions() as $key => $value) {
          try {
            $UserCharge = new User($value['id_user']);
          } catch (\Exception $e) {
            continue;
          }

          $DateSubscription = new DateTime();
          $DateSubscription->setTimestamp($value['date_charges']);

          $DateNow = new DateTime('now');
          $DateExpire = new DateTime('now');
          $DateExpire->setTimestamp($value['date_charges']);
          $DateExpire->add(new DateInterval('P'.$value['ndays_charges'].'D'));


          ?>
          <tr>
            <td><?php echo $value['id_charges']; ?></td>
            <td><?php echo $UserCharge->_getUserID().' - '.$UserCharge->_getName(); ?></td>
            <td><?php echo $DateSubscription->format('d/m/Y H:i:s'); ?></td>
            <td>
              <?php echo $DateExpire->diff($DateNow)->days.' days left';  ?>
            </td>
            <td><?php echo $DateExpire->format('d/m/Y'); ?></td>
            <td><?php echo (strlen($value['type_payment']) == 0 ? '-' : $value['type_payment']); ?></td>
            <td><?php echo (strlen($value['key_charges']) == 0 ? '-' : $value['key_charges']); ?></td>
          </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
  </div>

</section>
