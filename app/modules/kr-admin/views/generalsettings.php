<?php

/**
 * Admin general settings page
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

$Trade = new Trade($User, $App);
?>
<form class="kr-admin kr-adm-post-evs" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/saveGeneralsettings.php" enctype="multipart/form-data">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'General settings' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Your logo'); ?></label><br/>
        <span><?php echo $Lang->tr("It's recommended to use a SVG logo for increase the quality"); ?></span><br/><br/>
        <span style="font-weight:bold;"><?php echo $Lang->tr('Max upload size ').': '.App::_getMaxUploadSizeAllowed().' MB'; ?></span>
      </div>
      <div>
        <img style="max-width:250px; max-height:250px;" src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" alt=""><br/><br/>
          <input type="file" name="kr-logo-black" value="">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Your logo (version white)'); ?></label><br/>
        <span><?php echo $Lang->tr("It's recommended to use a SVG logo for increase the quality"); ?></span><br/><br/>
        <span style="font-weight:bold;"><?php echo $Lang->tr('Max upload size ').': '.App::_getMaxUploadSizeAllowed().' MB'; ?></span>
      </div>
      <div>
        <img style="max-width:250px; max-height:250px;" src="<?php echo APP_URL.$App->_getLogoPath(); ?>" alt=""><br/><br/>
        <input type="file" name="kr-logo-white" value="">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Title'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your website title'); ?>" name="kr-adm-title" value="<?php echo $App->_getAppTitle(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('App description'); ?></label>
      </div>
      <div>
        <textarea name="kr-adm-description"><?php echo $App->_getAppDescription(); ?></textarea>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Referal link'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your referal link (ex : Changelly)'); ?>" name="kr-adm-referallink" value="<?php echo $App->_getReferalLink(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Maintenance mode'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-maintenancemode" <?php echo ($App->_isMaintenanceMode() ? 'checked' : ''); ?> name="kr-adm-chk-maintenancemode">
            <label for="kr-adm-chk-maintenancemode"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Number format'); ?></label>
      </div>
      <div>
        <?php
        $numberFormatAvailable = [
            '".":","' => '5,500.50',
            '",":"."' => '5.500,50',
            '",":""' => '5500,50',
            '".":""' => '5500.50',
            '".":" "' => '5 500.50',
            '",":" "' => '5 500,50'
        ];
        ?>
        <select name="kr-adm-numberformart">
          <?php
          foreach ($numberFormatAvailable as $format => $Sample) {
            echo '<option '.($format == $App->_getNumberFormat() ? 'selected' : '').' value=\''.$format.'\'>'.$Sample.'</option>';
          }
          ?>
        </select>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Switch charts to tradingview charts'); ?></label><br/>
        <span>/!\ Some exchanges / pairs can be not available /!\</span>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-tradingviewchart" <?php echo ($App->_tradingviewchartEnable() ? 'checked' : ''); ?> name="kr-adm-chk-tradingviewchart">
            <label for="kr-adm-chk-tradingviewchart"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Allow user to switch chart type'); ?></label><br/>
        <span>User will be able to choose between default chart and tradingview chart library</span>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-allowchartswitch" <?php echo ($App->_allowSwitchChart() ? 'checked' : ''); ?> name="kr-adm-chk-allowchartswitch">
            <label for="kr-adm-chk-allowchartswitch"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Disable chat'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-disablechat" <?php echo ($App->_chatIsDisabled() ? 'checked' : ''); ?> name="kr-adm-chk-disablechat">
            <label for="kr-adm-chk-disablechat"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Starting pair when new user signup'); ?></label><br/>
        <span><?php echo $Lang->tr('Only 1 pair is allowed'); ?></span>
      </div>
      <div>
        <input type="text" placeholder="MARKET:SYMBOL/CURRENCY (ex : BINANCE:ETH/BTC)" name="kr-adm-startingpair" value="<?php echo $App->_getStartingPair();?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Starting watching list pair when new user signup'); ?></label><br/>
        <span><?php echo $Lang->tr('1 pair each for each line'); ?></span><br/>
        <span><?php echo $Lang->tr('Format : MARKET:SYMBOL/CURRENCY'); ?></span><br/>
        <span><?php echo $Lang->tr('Format : MARKET:SYMBOL/CURRENCY'); ?></span>
      </div>
      <div>
        <textarea name="kr-adm-startingpairwatchinglist" style="width:100%;" cols="80"><?php echo $App->_getStartingPairWatchinglist();?></textarea>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Remove the .php to the dashboard page extension'); ?></label><br/>
        <span><?php echo $Lang->tr('Mod rewrite is : ').(App::_modeURLRewriteIsEnabled() ? '<i style="color:green;">Enable</i>': '<i style="color:red;">Disable</i>'); ?></span>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-rewritedashboard" <?php echo ($App->_rewriteDashBoardName() ? 'checked' : ''); ?> name="kr-adm-chk-rewritedashboard">
            <label for="kr-adm-chk-rewritedashboard"></label>
        </div>
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Language'); ?></label>
      </div>
      <div>
        <select name="kr-adm-defaultlanguage">
          <?php foreach ($Lang->getListLanguage('../../../../') as $langisocode => $language) {
            ?>
            <option <?php if($App->_getDefaultLanguage() == $langisocode) echo 'selected="selected"'; ?> value="<?php echo $langisocode; ?>"><?php echo $language; ?></option>
            <?php
          } ?>
        </select>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable autolanguage'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-autolanguage" <?php echo ($App->_getAutodectionLanguage() ? 'checked' : ''); ?> name="kr-adm-chk-autolanguage">
            <label for="kr-adm-chk-autolanguage"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable POEditor Language'); ?></label><br/>
        <span>Editor link : <a href="https://poeditor.com" target=_bank>https://poeditor.com</a></span>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-poeditorenable" <?php echo ($App->_getPOEditorEnable() ? 'checked' : ''); ?> name="kr-adm-chk-poeditorenable">
            <label for="kr-adm-chk-poeditorenable"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('POEditor API Key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your POEditor API Key'); ?>" name="kr-adm-poeditor-apikey" value="<?php echo (!empty($App->_getPOEditorAPIKey()) ? '**************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('POEditor Project Selector'); ?></label>
      </div>
      <div>
        <?php if($App->_getPOEditorEnable() && !empty($App->_getPOEditorAPIKey())):
          try {
            echo '<select name="kr-adm-poeditor-project">';
              echo '<option value="">-- Select your project --</option>';
            foreach ($Lang->_getPOEditorProjectList() as $key => $value) {
              echo '<option '.($App->_getPOEditorProject() == $value->getId() ? 'selected' : '').' value="'.$value->getId().'">'.$value->getName().'</option>';
            }
            echo '</select>';

          } catch (Exception $e) {
            echo '<span style="color:red;">'.$e->getMessage().'</span>';
            echo '<select name="kr-adm-poeditor-project" style="display:none;"><option value=""></option></select>';
          }
          ?>
        <?php else: ?>
          <span>You need to enable POEditor, set your API Key access and save for select your project</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Allow signup'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-allowsignup" <?php echo ($App->_allowSignup() ? 'checked' : ''); ?> name="kr-adm-chk-allowsignup">
            <label for="kr-adm-chk-allowsignup"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('User need verify account'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-signupverifiy" <?php echo ($App->_getUserActivationRequire() ? 'checked' : ''); ?> name="kr-adm-chk-signupverifiy">
            <label for="kr-adm-chk-signupverifiy"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Need captcha signup'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-captchasignup" <?php echo ($App->_captchaSignup() ? 'checked' : ''); ?> name="kr-adm-chk-captchasignup">
            <label for="kr-adm-chk-captchasignup"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Google Recaptcha Site Key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Google Recaptcha Site Key'); ?>" name="kr-adm-recaptcha-sitekey" value="<?php echo (!empty($App->_getGoogleRecaptchaSiteKey()) ? '**************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Google Recaptcha Secret Key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Google Recaptcha Secret Key'); ?>" name="kr-adm-recaptcha-secretkey" value="<?php echo (!empty($App->_getGoogleRecaptchaSecretKey()) ? '**************' : ''); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Sigin with google'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-googleoauth" <?php echo ($App->_enableGooglOauth() ? 'checked' : ''); ?> name="kr-adm-chk-googleoauth">
            <label for="kr-adm-chk-googleoauth"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Google App ID'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Google App ID'); ?>" name="kr-adm-googleoauth-appid" value="<?php echo (!empty($App->_getGoogleAppID()) ? '**************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Google App Secret'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Google App Secret'); ?>" name="kr-adm-googleoauth-appsecret" value="<?php echo (!empty($App->_getGoogleAppSecret()) ? '**************' : ''); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Sigin with Facebook'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-facebookoauth" <?php echo ($App->_enableFacebookOauth() ? 'checked' : ''); ?> name="kr-adm-chk-facebookoauth">
            <label for="kr-adm-chk-facebookoauth"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Facebook App ID'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Facebook App ID'); ?>" name="kr-adm-facebookoauth-appid" value="<?php echo (!empty($App->_getFacebookAppID()) ? '**************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Facebook App Secret'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Facebook App Secret'); ?>" name="kr-adm-facebookoauth-appsecret" value="<?php echo (!empty($App->_getFacebookAppSecret()) ? '**************' : ''); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Google Analytics'); ?></label>
      </div>
      <div>
        <textarea name="kr-adm-googleanalytics" placeholder="Your Google Analytics JS code"><?php echo $App->_getGoogleAnalytics(); ?></textarea>
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Google Ad'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-googleadenabled" <?php echo ($App->_GoogleAdEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-googleadenabled">
            <label for="kr-adm-chk-googleadenabled"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Google Ad Client'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Google Ad Client'); ?>" name="kr-adm-googleadenabclient" value="<?php echo $App->_getGoogleAdClient(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Google Ad Slot'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Google Ad slot'); ?>" name="kr-adm-googleadslot" value="<?php echo $App->_getGoogleAdSlot(); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Cookie popup'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-cookiepopupenable" <?php echo ($App->_getCookieAvertEnable() ? 'checked' : ''); ?> name="kr-adm-chk-cookiepopupenable">
            <label for="kr-adm-chk-cookiepopupenable"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Cookie Popup title'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Cookie popup title'); ?>" name="kr-adm-cookietitle" value="<?php echo $App->_getCookieTitle(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Cookie Popup text'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Cookie popup text'); ?>" name="kr-adm-cookietext" value="<?php echo $App->_getCookieText(); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Blacklisted countries'); ?></label>
      </div>
      <div>
        <select id="select-blacklisted-countries" name="blacklisted_countries[]" multiple class="demo-default" placeholder="Select blacklisted countries">
          <?php

          foreach ($App->_getListCountries() as $key => $value) {
            ?>
            <option <?php if(($App->_getBlacklistedCountries()) && count($App->_getBlacklistedCountries()) > 0 && in_array($value['iso_country'], $App->_getBlacklistedCountries())) echo 'selected'; ?> value="<?php echo $value['iso_country']; ?>"><?php echo $value['name_country']; ?></option>
            <?php
          }
          ?>
      </select>
      <script type="text/javascript">
      $('#select-blacklisted-countries').selectize();
      </script>
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Donation'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-donationenable" <?php echo ($App->_getDonationEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-donationenable">
            <label for="kr-adm-chk-donationenable"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Donation text'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your donation text'); ?>" name="kr-adm-donationtext" value="<?php echo $App->_getDonationText(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Donation address list'); ?></label><br/>
        <span><?php echo $Lang->tr('Put 1 address per line'); ?></span><br/>
        <span><?php echo $Lang->tr('Line format : BTC:ADDRESS'); ?></span>
      </div>
      <div>
        <textarea name="donation_list_c" style="width:100%;"><?php echo $App->_getDonationList(); ?></textarea>
      </div>
    </div>
  </div>
  <div class="kr-admin-action">
    <input type="submit" class="btn btn-orange" name="" value="<?php echo $Lang->tr('Save'); ?>">
  </div>
</form>
