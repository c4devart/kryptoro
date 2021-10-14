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

?>
<section class="kr-manager kr-admin">
  <nav class="kr-manager-nav">
    <ul>
      <?php foreach ($Manager->_getListSection() as $key => $section) { // Get list admin section
        $NotificationNumber = $Manager->_getNumberManagerNotification(strtolower(str_replace(' ', '', $section)));
        echo '<li type="module" kr-module="manager" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Identity' ? 'class="kr-manager-nav-selected"' : '').'>'.$Lang->tr($section).' '.($NotificationNumber > 0 ? '<span>'.$NotificationNumber.'</span>' : '').'</li>';
      } ?>
    </ul>
  </nav>

  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('User'); ?></td>
          <td><?php echo $Lang->tr('Submited date'); ?></td>
          <td><?php echo $Lang->tr('Status'); ?></td>
          <td><?php echo $Lang->tr('Document name'); ?></td>
          <td><?php echo $Lang->tr('Documents'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($Identity->_getListIdentity() as $idIdentity => $infosIdentity) {
          ?>
          <tr>
             <td>
               <div class="kr-admin-coin-nsa">
                 <span><?php echo '#'.$infosIdentity['user']->_getUserID().' - '.$infosIdentity['user']->_getName(); ?></span>
               </div>
             </td>
             <td>
               <?php echo date('d/m/Y H:i:s', $infosIdentity['identity_infos']['date_processed_identity']); ?>
             </td>
             <td>
               <?php
                if($infosIdentity['identity_infos']['status_identity'] == 0) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-red">'.$Lang->tr('Not processed').'</span>';
                if($infosIdentity['identity_infos']['status_identity'] == 1) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-orange">'.$Lang->tr('Declined').'</span>';
                if($infosIdentity['identity_infos']['status_identity'] == 2) echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag">'.$Lang->tr('Processed').'</span>';
               ?>
             </td>
             <td>
               <?php echo ($infosIdentity['identity_type'] == null ? '-' : $infosIdentity['identity_type']['name_identity_doclist']); ?>
             </td>
             <td>
               <div class="" style="display:flex;">
                 <?php
                 if(!is_null($infosIdentity['assets']) && count($infosIdentity['assets']) > 0):

                  foreach ($infosIdentity['assets'] as $keyAsset => $assetInfo) {
                    if(!array_key_exists("->".$assetInfo['id_identity_step'], $infosIdentity['step_list'])) continue;
                    if($infosIdentity['step_list']["->".$assetInfo['id_identity_step']]['type_identity_step'] == "form"):
                      $identityFormHTML = "<ul>";
                      $assetFormListProcessed = json_decode($assetInfo['value_identity_asset'], true);
                      foreach (json_decode($infosIdentity['step_list']["->".$assetInfo['id_identity_step']]['description_identity_step'], true) as $IdentityFormShow => $valueIdentityFormShow) {
                        $identityFormHTML .= "<li>".$valueIdentityFormShow['title']." : ".htmlspecialchars($assetFormListProcessed[$IdentityFormShow])."</li>";
                      }
                      $identityFormHTML .= "</ul>";
                      ?>
                        <a class="btn btn-autowidth" onclick="$.zoombox.html('<?php echo $identityFormHTML; ?>',{theme:'prettyphoto'}); return false;" style="margin-right:10px;"><?php echo $infosIdentity['step_list']["->".$assetInfo['id_identity_step']]['name_identity_step']; ?></a>
                      <?php
                    else:
                    ?>
                      <a href="<?php echo APP_URL; ?>/public/identity/<?php echo $assetInfo['value_identity_asset']; ?>" style="margin-right:10px;" class="btn btn-autowidth zoombox zgallery<?php echo $idIdentity; ?>" data-title="<?php echo $infosIdentity['step_list']["->".$assetInfo['id_identity_step']]['description_identity_step']; ?>"><?php echo $infosIdentity['step_list']["->".$assetInfo['id_identity_step']]['description_identity_step']; ?></a>
                      <?php
                    endif;
                  }
                else:
                  echo '-';
                endif;
                 ?>
               </div>
             </td>
             <td>
               <?php
               if($infosIdentity['identity_infos']['status_identity'] != 2):
               ?>
                 <button type="button" onclick="_approveIdentity('<?php echo App::encrypt_decrypt('encrypt', $infosIdentity['identity_infos']['id_identity']); ?>')" style="margin-bottom:5px;" class="btn btn-green btn-small btn-autowidth" name="button"><?php echo $Lang->tr('Approve'); ?></button>
               <?php endif; ?>

               <?php
               if($infosIdentity['identity_infos']['status_identity'] == 0):
               ?>
                 <button type="button" onclick="_declineIdentity('<?php echo App::encrypt_decrypt('encrypt', $infosIdentity['identity_infos']['id_identity']); ?>')" class="btn btn-red btn-small btn-autowidth" name="button"><?php echo $Lang->tr('Decline'); ?></button>
               </td>
             <?php endif; ?>
           </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
  </div>

</section>
