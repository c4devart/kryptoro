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

if(empty($_GET) || !isset($_GET['type'])) throw new Exception("Permission denied", 1);


$Widthdraw = new Widthdraw();
$WidthdrawConfiguration = $Widthdraw->_getWidthdrawMethod();
if(!array_key_exists($_GET['type'], $WidthdrawConfiguration)) throw new Exception("Permission denied", 1);
$WidthdrawConfiguration = $WidthdrawConfiguration[$_GET['type']];

$Balance = new Balance($User, $App, 'real');


?>
<section class="kr-thirdparty-setup kr-ov-nblr kr-widthdraw-setup">
  <section>
    <header>
      <img src="<?php echo APP_URL; ?>/app/modules/kr-trade/statics/img/widthdraw/<?php echo $_GET['type']; ?>.svg" alt="">
    </header>
    <div style="display:none;" class="spinner spinner-dark"></div>
    <form class="kr-thirdparty-setup-form" action="<?php echo APP_URL; ?>/app/modules/kr-trade/src/actions/initWidthdrawAccount.php" method="post">
      <?php
        $s = 0;
        foreach ($WidthdrawConfiguration['fields'] as $keyField => $nameFields) { ?>
        <?php if($s == 0 || $s == 1 || $s == 3 || $s == 5 || $s == 7 || $s == 9 || $s == 11 || $s == 13 || $s == 15 || $s == 17) echo '<section>'; ?>
          <div>
            <label><?php echo $nameFields; ?></label>
            <?php if($keyField == "cryptocurrency_name"):
              $ListMoney = $Balance->_getListMoney();
              ?>
              <select class="" name="<?php echo $keyField; ?>">
                <?php
                foreach ($Balance->_getBalanceListResum() as $symbl => $value) {
                  if(in_array($symbl, $ListMoney)) continue;
                  echo '<option>'.$symbl.'</option>';
                }
                ?>
              </select>
            <?php else: ?>
              <input type="text" class="thirdparty_connect_field" name="<?php echo $keyField; ?>" placeholder="<?php echo $nameFields; ?>" value="">
            <?php endif; ?>

          </div>
        <?php if($s == 0 || $s == 2 || $s == 4 || $s == 6 || $s == 8 || $s == 10 || $s == 12 || $s == 14 || $s == 16 || $s == count($WidthdrawConfiguration['fields']) - 1) echo '</section>'; ?>
      <?php $s++; } ?>


      <div>
        <input type="button" onclick="closeThirdpartySetup();" class="btn-welcome-cfg-gdax-dil btn btn-shadow btn-grey btn-autowidth" name="" value="<?php echo $Lang->tr('Cancel'); ?>">
        <input type="hidden" name="widthdraw_type" value="<?php echo $_GET['type']; ?>">
        <input type="submit" class="btn btn-shadow btn-autowidth" name="" value="<?php echo $Lang->tr('Next'); ?>">
      </div>
    </form>
  </section>
</section>
