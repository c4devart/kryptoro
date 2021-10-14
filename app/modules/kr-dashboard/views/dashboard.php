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

$CryptoApi = new CryptoApi(null, null, $App);

$Dashboard = new Dashboard($CryptoApi, $User);

if(!empty($_POST) && !empty($_POST['nchart'])) $Dashboard->_changeDashboardType($_POST['nchart']);

$GraphList = $Dashboard->_getDashboardGraphList();

$nchartShown = $Dashboard->_getGraphPos();

$Balance = new Balance($User, $App);


?>
<div class="kr-dash-pannel kr-dash-chart-n" nchart="<?php echo $nchartShown; ?>">
  <?php

  $nschart = 0;

  foreach (array_slice($GraphList, 0, $Dashboard->_getNumGraph()) as $NChart => $Graph) {

    if($Graph->_isEnable()):

      $TopListItem = $Graph->_getAssociateItem();
      $Coin = $TopListItem->_getCoinItem();



    ?>
    <div class="kr-dash-pan-cry kr-dash-pan-cry-vsbl" id="<?php echo $Graph->_getKeyGraph(); ?>" graph-id="<?php echo $Graph->_getGraphID(true); ?>" type-graph="<?php echo $Graph->_getTypeGraph(); ?>" container="<?php echo $Graph->_getKeyGraph(); ?>" market="<?php echo $TopListItem->_getMarket(); ?>" currency="<?php echo $TopListItem->_getCurrency(); ?>" symbol="<?php echo $Coin->_getSymbol(); ?>">

    </div>
    <?php else: ?>
      <div class="kr-dash-pan-cry" id="<?php echo $Graph->_getKeyGraph(); ?>" chart-init="false" graph-id="<?php echo $Graph->_getGraphID(true); ?>" type-graph="<?php echo $Graph->_getTypeGraph(); ?>" container="<?php echo $Graph->_getKeyGraph(); ?>" currency="<?php echo $CryptoApi->_getCurrency(); ?>" symbol="not_init">
        <div class="kr-dash-pan-lgl" onclick="showBigSearch('addGraphDashboardNotInit');">
          <img src="<?php echo APP_URL.($User->_whiteMode() ? $App->_getLogoBlackPath() : $App->_getLogoPath()); ?>" alt="">
        </div>
      </div>
    <?php

    endif;

  }
?>


</div>
<section class="kr-dash-orderlistpassed <?php if($User->_getUserSettingsKey('orderlist_show') == "false") echo 'kr-dash-orderlistpassed-hide'; ?>">
                <script type="text/javascript">
                  <?php if($User->_getUserSettingsKey('orderlist_show') == "true"): ?>
                  _toggleOrderGraphList(true);
                  <?php endif; ?>
                  <?php if($User->_getUserSettingsKey('orderlist_layer') == "true"): ?>
                  _toggleLayerOrderGraphList(true);
                  <?php endif; ?>
                </script>

  <header>
    <div>
      <span><?php echo $Lang->tr('Order book').'<i class="kr-dash-orderlistpassed-pairname"></i>'; ?></span>
    </div>
    <ul>
      <li class="kr-dash-orderlistpassed-optfull" onclick="_toggleLayerOrderGraphList();"><svg class="lnr lnr-frame-expand"><use xlink:href="#lnr-frame-expand"></use></svg></li>
      <li class="kr-dash-orderlistpassed-optlayer" onclick="_toggleLayerOrderGraphList();"><svg class="lnr lnr-layers"><use xlink:href="#lnr-layers"></use></svg></li>
      <li class="kr-dash-orderlistpassed-opthide" onclick="_toggleOrderGraphList();"><svg class="lnr lnr-chevron-down"><use xlink:href="#lnr-chevron-down"></use></svg></li>
      <li class="kr-dash-orderlistpassed-optshow" onclick="_toggleOrderGraphList();"><svg class="lnr lnr-chevron-up"><use xlink:href="#lnr-chevron-up"></use></svg></li>
    </ul>
  </header>
  <nav>
    <ul>
      <li><?php echo $Lang->tr('Date'); ?></li>
      <li><?php echo $Lang->tr('Pair'); ?></li>
      <li><?php echo $Lang->tr('Type'); ?></li>
      <?php if(!$App->_getHideMarket()): ?>
        <li><?php echo $Lang->tr('Market'); ?></li>
      <?php endif; ?>
      <li><?php echo $Lang->tr('Type'); ?></li>
      <li><?php echo $Lang->tr('Qty'); ?></li>
      <li><?php echo $Lang->tr('Gain'); ?></li>
      <li><?php echo $Lang->tr('Fees'); ?></li>
      <li><?php echo $Lang->tr('Total gain'); ?></li>
      <li><?php echo $Lang->tr('Evolution'); ?></li>
      <li></li>
    </ul>
  </nav>
  <ul class="kr-dash-orderlistpassed-lst">

  </ul>
</section>
<style media="screen">
  section.kr-dashboard {
    padding-bottom: 30px;
  }
</style>
