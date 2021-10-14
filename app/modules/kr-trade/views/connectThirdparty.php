<?php

session_start();

require "../../../../config/config.settings.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

$App = new App(true);
$App->_loadModulesControllers();

$User = new User();
if(!$User->_isLogged()) die('Error : User not logged');

$Lang = new Lang($User->_getLang(), $App);

if(empty($_GET) || !isset($_GET['thirdparty']) || empty($_GET['thirdparty'])) die();

$Trade = new Trade($User, $App);

$Exchange = $Trade->_getExchange($_GET['thirdparty']);
if(is_null($Exchange)) die();

$ThirdpartiConfig = $Trade->_getThirdPartyConfig();
$configFieldExchange = [];
if(array_key_exists($_GET['thirdparty'], $ThirdpartiConfig)){
  $configFieldExchange = $Trade->_getThirdPartyConfig()[$_GET['thirdparty']];
}


$nativeConfiguration = 'false';
if($_GET['cmdcfg'] == "tradingglobal") $nativeConfiguration = 'true';

?>
<section class="kr-thirdparty-setup kr-ov-nblr">
  <section>
    <header>
      <img src="<?php echo APP_URL; ?>/assets/img/icons/trade/<?php echo $Exchange->_getLogo(); ?>" alt="<?php echo $Exchange->_getExchangeName(); ?>">
    </header>
    <div style="display:none;" class="spinner spinner-dark"></div>
    <form class="kr-thirdparty-setup-form" action="<?php echo APP_URL; ?>/app/modules/kr-trade/src/actions/saveThirdpartySettings.php">
      <?php
      $i = 0;
      foreach ($configFieldExchange as $fieldName => $fieldTitle) {
        if($i == 0) echo '<section>';
        $i++;
        if($fieldName != "sandbox"):
          ?>
          <div>
            <label><?php echo $Lang->tr('Your '.$fieldTitle); ?></label>
            <input type="text" class="thirdparty_connect_field" name="<?php echo $fieldName; ?>" placeholder="<?php echo $Lang->tr('Your '.$fieldTitle); ?>" value="">
          </div>
          <?php
        else:
          if(!is_null($fieldTitle)):
            ?>
            <div>
              <label><?php echo $Lang->tr('Select your platform'); ?></label>
              <select class="" name="<?php echo $fieldName; ?>">
                <option value="0"><?php echo $Lang->tr('Sandbox'); ?></option>
                <option value="1"><?php echo $Lang->tr('Live'); ?></option>
              </select>
            </div>
            <?php
          endif;
        endif;

        if($i == 2){
          echo '</section>';
          $i = 0;
        }
      }

      if($i != 0) echo '</section>';
      ?>

      <div>
        <input type="button" onclick="closeThirdpartySetup();" class="btn-welcome-cfg-gdax-dil btn btn-shadow btn-grey btn-autowidth" name="" value="<?php echo $Lang->tr('Cancel'); ?>">
        <?php if(($App->_hiddenThirdpartyActive() && array_key_exists($Exchange->_getExchangeName(), $App->_hiddenThirdpartyServiceCfg())) || $Exchange->_isActivated()): ?>
          <input type="button" class="btn btn-shadow btn-red btn-autowidth" onclick="removeThirdpartySetup('<?php echo App::encrypt_decrypt('encrypt', $Exchange->_getExchangeName().'-'.$User->_getUserID().'-'.$nativeConfiguration); ?>');" name="" value="Remove">
        <?php endif; ?>
        <input type="hidden" name="thirdpartycfg_type" value="<?php echo $_GET['cmdcfg']; ?>">
        <input type="hidden" name="thirdparty_name" value="<?php echo App::encrypt_decrypt('encrypt', $Exchange->_getExchangeName()); ?>">
        <input type="submit" class="btn btn-shadow btn-autowidth" name="" value="<?php echo $Lang->tr('Next'); ?>">
      </div>
    </form>
  </section>
</section>
