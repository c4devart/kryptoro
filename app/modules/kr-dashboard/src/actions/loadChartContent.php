<?php

/**
 * Load chart content
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoOrder.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

  // Check if user is logged
  $User = new User();
  if (!$User->_isLogged()) {
      throw new Exception("User is not logged", 1);
  }

  // Init lang object
  $Lang = new Lang($User->_getLang(), $App);

  // Check given args
  if (empty($_GET) || empty($_GET['container']) || empty($_GET['coin'])) {
      die('error');
  }

  if(strtoupper($_GET['market']) == "COINBASE") $_GET['market'] = "GDAX";
  if(strtoupper($_GET['market']) == "CEXIO") $_GET['market'] = "CEX";

  // Init crypto api
  $CryptoApi = new CryptoApi($User, [$_GET['currency'], null], $App, $_GET['market']);

  // Init coin associate to the graph
  $Coin = new CryptoCoin($CryptoApi, $_GET['coin'], null, $_GET['market']);

  // Get container
  $container = $_GET['container'];

  // Load indicator graph
  $Indicators = new CryptoIndicators($container);

  // Init dashboard object
  $Dashboard = new Dashboard($CryptoApi, $User);

  // Init CryptoOrder
  $OrderCoin = new CryptoOrder($Coin);

  $Trade = new Trade($User, $App);

  $availableTrading = false;
  if($App->_hiddenThirdpartyActive()){
    $listThirdParty = $Trade->_thirdparySymbolTrading($Coin->_getSymbol(), $CryptoApi->_getCurrency(), $_GET['market']);
  } else {
    $listThirdParty = $Trade->_thirdparySymbolTrading($Coin->_getSymbol(), $CryptoApi->_getCurrency(), $_GET['market']);
  }
  $availableTrading = count($listThirdParty) > 0;


  if(!$User->_accessAllowedFeature($App, 'tradinglive')) $availableTrading = false;

} catch (\Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

$DashboardGraph = null;
try {
  $DashboardGraph = new DashboardGraph($CryptoApi, $User, null, []);
  $DashboardGraph->_loadGraphByKey($container);
} catch (Exception $s) {
  $DashboardGraph = null;
}


// if($availableTrading){
//   if($App->_hiddenThirdpartyActive()) $listThirdParty = $Trade->_thirdparySymbolTrading($Coin->_getSymbol(), $CryptoApi->_getCurrency(), $_GET['market']);
//   else $listThirdParty = $Trade->_thirdparySymbolTrading($Coin->_getSymbol(), $CryptoApi->_getCurrency());
// }


if (!$App->_tradingviewchartEnable() && (!$App->_allowSwitchChart() || !$User->_tradingviewChartLibraryUse())):

  $WatchingListObject = new WatchingList($CryptoApi, $User);

?>
<section class="kr-dash-pan-hedr">
  <ul>
    <li symbol="<?php echo $Coin->_getSymbol(); ?>" currency="<?php echo $CryptoApi->_getCurrency(); ?>" market="<?php echo $Coin->_getMarket(); ?>" class="kr-dash-pan-cry-select-lst-tdn kr-watching-list-exist-addaction <?php echo ($WatchingListObject->_symbolExist($Coin->_getSymbol(), $CryptoApi->_getCurrency()) ? 'watching-list-present' : ''); ?>"><svg class="lnr lnr-bookmark"><use xlink:href="#lnr-bookmark"></use></svg></li>
    <?php if ($Dashboard->_getNumGraph() > 1): ?>
      <li class="kr-dash-tgglfullscreen"><svg class="lnr lnr-frame-expand"><use xlink:href="#lnr-frame-expand"></use></svg></li>
    <?php endif; ?>
    <li class="kr-dash-close"><svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg></li>
  </ul>
  <div class="kr-search-field-content" kr-search-callback="changeGraphPairSymbol">
    <input type="text" class="kr-search-field"  name="" value="<?php echo ($Coin->_getMarket() != "CCCAGG" ? ($Coin->_getMarket() == "GDAX" ? "COINBASE:" : (!$App->_getHideMarket() ? $Coin->_getMarket().':' : '')) : '').$Coin->_getSymbol().$CryptoApi->_getCurrency(); ?>">
  </div>
  <?php
  $range = ["5m" => [44641, 999999999999],
            "1m" => [20161, 44640],
            "2w" => [10082, 20160],
            "7d" => [1441, 10081],
            "1d" => [721, 1440],
            "12h" => [121, 720],
            "2h" => [61, 120],
            "1h" => [31, 60],
            "30min" => [0, 30]];
  ?>
  <div class="kr-dash-pan-hedr-ca">
    <div>
      <span><?php echo array_keys($range)[count($range) - 1]; ?></span>
    </div>
    <ul class="kr-dash-pan-hedr-smli kr-dash-pan-ranges">
      <?php
      foreach ($range as $rangeTitle => $rangeValue) {
        ?>
        <li rangemin="<?php echo $rangeValue[0]; ?>" rangemax="<?php echo $rangeValue[1]; ?>"><span><?php echo $Lang->tr($rangeTitle); ?></span></li>
        <?php
      }
      ?>
    </ul>
  </div>
  <div class="kr-dash-pan-hedr-ca">
    <div>
      <span><?php echo $Lang->tr('1m'); ?></span>
    </div>
  </div>
  <div class="kr-dash-pan-hedr-ca">
    <div class="kr-dash-pan-hedr-ca-bigicon">
      <?php if(is_null($DashboardGraph) || $DashboardGraph->_getTypeGraph() != "line"): ?>
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:a="http://ns.adobe.com/AdobeSVGViewerExtensions/3.0/" x="0px" y="0px" width="17.4px" height="10.7px" viewBox="0 0 17.4 10.7" style="enable-background:new 0 0 17.4 10.7;" xml:space="preserve"> <defs> </defs> <path d="M6.5,10l4.6-4.7h2.7l3.7-4.7L16.8,0l-3.4,4.3h-2.6L6.5,8.6l-2.7-3L0,10l0.6,0.7L3.8,7L6.5,10z"/> </svg>
      <?php else: ?>
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:a="http://ns.adobe.com/AdobeSVGViewerExtensions/3.0/" x="0px" y="0px" width="12px" height="20px" viewBox="0 0 12 20" style="enable-background:new 0 0 12 20;" xml:space="preserve"> <defs> </defs> <path d="M8,7v6h3V7H8z M7.5,6h4C11.8,6,12,6.2,12,6.5v7c0,0.3-0.2,0.5-0.5,0.5h-4C7.2,14,7,13.8,7,13.5v-7C7,6.2,7.2,6,7.5,6z"/> <path d="M9,3h1v3.3H9V3z M9,13.8h1V17H9V13.8z"/> <path d="M1,4v12h3V4H1z M0.5,3h4C4.8,3,5,3.2,5,3.5v13C5,16.8,4.8,17,4.5,17h-4C0.2,17,0,16.8,0,16.5v-13C0,3.2,0.2,3,0.5,3z"/> <path d="M2,0h1v3.5H2V0z M2,16.5h1V20H2V16.5z"/> </svg>
      <?php endif; ?>
    </div>
    <ul class="kr-dash-pan-hedr-smli kr-dash-pan-hedr-smliwsvg" id-container="">
      <li kr-graph-ctype="candlestick">
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:a="http://ns.adobe.com/AdobeSVGViewerExtensions/3.0/" x="0px" y="0px" width="12px" height="20px" viewBox="0 0 12 20" style="enable-background:new 0 0 12 20;" xml:space="preserve"> <defs> </defs> <path d="M8,7v6h3V7H8z M7.5,6h4C11.8,6,12,6.2,12,6.5v7c0,0.3-0.2,0.5-0.5,0.5h-4C7.2,14,7,13.8,7,13.5v-7C7,6.2,7.2,6,7.5,6z"/> <path d="M9,3h1v3.3H9V3z M9,13.8h1V17H9V13.8z"/> <path d="M1,4v12h3V4H1z M0.5,3h4C4.8,3,5,3.2,5,3.5v13C5,16.8,4.8,17,4.5,17h-4C0.2,17,0,16.8,0,16.5v-13C0,3.2,0.2,3,0.5,3z"/> <path d="M2,0h1v3.5H2V0z M2,16.5h1V20H2V16.5z"/> </svg>
        <span><?php echo $Lang->tr('Candlestick'); ?></span>
      </li>
      <li kr-graph-ctype="line">
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:a="http://ns.adobe.com/AdobeSVGViewerExtensions/3.0/" x="0px" y="0px" width="17.4px" height="10.7px" viewBox="0 0 17.4 10.7" style="enable-background:new 0 0 17.4 10.7;" xml:space="preserve"> <defs> </defs> <path d="M6.5,10l4.6-4.7h2.7l3.7-4.7L16.8,0l-3.4,4.3h-2.6L6.5,8.6l-2.7-3L0,10l0.6,0.7L3.8,7L6.5,10z"/> </svg>
        <span><?php echo $Lang->tr('Line'); ?></span>
      </li>
      <!-- <li kr-graph-ctype="bar">
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:a="http://ns.adobe.com/AdobeSVGViewerExtensions/3.0/" x="0px" y="0px" width="16.2px" height="16.9px" viewBox="0 0 16.2 16.9" style="enable-background:new 0 0 16.2 16.9;" xml:space="preserve"> <defs> </defs> <rect x="3.1" y="1" class="st0" width="1" height="15.9"/> <rect x="12.1" class="st0" width="1" height="16"/> <rect x="4.1" y="11.9" class="st0" width="3.1" height="1"/> <rect x="9" y="13" class="st0" width="3.1" height="1"/> <rect y="3.9" class="st0" width="3.1" height="1"/> <rect x="13.1" y="1" class="st0" width="3.1" height="1"/> </svg>
        <span>Bar</span>
      </li> -->
    </ul>
  </div>
  <div class="kr-dash-pan-hedr-ca">
    <div class="kr-dash-pan-hedr-iwsvgs">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 28" width="28" height="28"><path d="M5.5 18.207l4.65-4.65a1.906 1.906 0 0 1 2.7 0l2.593 2.593a2.906 2.906 0 0 0 4.114 0l4.65-4.65-.707-.707-4.65 4.65a1.906 1.906 0 0 1-2.7 0l-2.593-2.593a2.906 2.906 0 0 0-4.114 0l-4.65 4.65.707.707z"></path></svg>
      <span><?php echo $Lang->tr('Indicators'); ?></span>
    </div>
    <ul class="kr-dash-pan-cust">
      <li class="kr-dash-pan-ads-sld">
        <div class="kr-dash-pan-ads">
          <section>
            <div class="kr-dash-pan-ads-lst">
              <ul>
                <?php
                foreach (CryptoIndicators::_getIndicatorsList() as $symbolIndicator => $indicator) {
                    echo '<li kr-indicator="'.$symbolIndicator.'" kr-graph="'.$container.'">'.$indicator['name'].'</li>';
                }
                ?>
              </ul>
            </div>
            <div class="kr-dash-pan-ads-i" kr-idic-init="false">
              <ul>
                <?php
                foreach ($Indicators->_getListIndicatorsContainer() as $Indicator) {
                    ?>
                  <li kr-cid="<?php echo $Indicator->_getIndicator(); ?>" kr-id-args="<?php echo join(',', $Indicator->_getArgs()); ?>" kr-tid="<?php echo $Indicator->_getSymbol(); ?>">
                    <span><?php echo $Indicator->_getTitle(); ?></span>
                    <ul>
                      <li><svg class="lnr lnr-cog"><use xlink:href="#lnr-cog"></use></svg></li>
                      <li><svg class="lnr lnr-eye"><use xlink:href="#lnr-eye"></use></svg></li>
                      <li><svg class="lnr lnr-trash"><use xlink:href="#lnr-trash"></use></svg></li>
                    </ul>
                  </li>
                  <?php
                }
                ?>

              </ul>
            </div>
          </section>
        </div>
      </li>
    </ul>
  </div>
  <?php if($User->_accessAllowedFeature($App, 'exportgraph')): ?>
    <div class="kr-dash-export kr-dash-pan-hedr-ca">
      <div class="kr-dash-pan-hedr-iwsvgs">
        <svg class="kr-dash-pan-hedr-iwsvgs-linic lnr lnr-exit-up"><use xlink:href="#lnr-exit-up"></use></svg>
        <span><?php echo $Lang->tr('Export'); ?></span>
      </div>
    </div>
  <?php endif; ?>
  <div class="kr-dash-pan-hedr-ca" onclick="createNewNotification('<?php echo $_GET['coin']; ?>', '<?php echo $_GET['currency']; ?>', '-1', '<?php echo $_GET['market']; ?>');">
    <div class="kr-dash-pan-hedr-iwsvgs">
      <svg class="kr-dash-pan-hedr-iwsvgs-linic lnr lnr-clock"><use xlink:href="#lnr-clock"></use></svg>
      <span><?php echo $Lang->tr('Alert'); ?></span>
    </div>
  </div>
  <?php if(false): ?>
  <div class="kr-dash-pan-hedr-ca">
    <div class="kr-dash-pan-hedr-iwsvgs">
      <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="height:17px; width:17px;" xml:space="preserve"> <g transform="translate(1)"> <g> <g> <path d="M399.32,399.36c-13.653,12.8-13.653,34.133,0,47.787c6.827,6.827,15.36,10.24,23.893,10.24s17.067-3.413,23.893-10.24 c13.653-12.8,13.653-34.133,0-47.787C434.307,385.707,412.973,385.707,399.32,399.36z M435.16,436.053 c-6.827,6.827-17.067,6.827-23.893,0s-6.827-17.067,0-23.893c3.413-3.413,7.68-5.12,11.947-5.12s8.533,1.707,11.947,5.12 C441.987,418.987,441.987,429.227,435.16,436.053z"/> <path d="M505.133,409.6L351.533,256l60.587-60.587c0,0,0.001-0.001,0.001-0.001l35.839-35.839c0,0,0.001-0.001,0.001-0.001 l52.052-52.052c14.507-14.507,14.507-38.4,0-52.907l-43.52-43.52C449.667,4.267,440.28,0,430.04,0s-19.627,4.267-26.453,11.093 l-52.053,52.053l-35.84,35.84l-60.587,60.587L103.213,7.68c-9.387-9.387-26.453-9.387-35.84,0L6.787,68.267 c-10.24,9.387-10.24,25.6,0,35.84l17.92,17.92l0,0c0,0,0,0,0,0l48.64,48.64c0,0,0,0,0,0s0,0,0,0l47.786,47.786c0,0,0,0,0,0 c0,0,0,0,0.001,0.001L158.68,256L21.694,392.986c-2.554,1.127-3.931,3.43-4.667,6.374l-17.92,102.4 c0,3.413,0.853,5.973,2.56,7.68C3.373,511.147,5.08,512,7.64,512c0.853,0,0.853,0,1.707,0l98.445-18.2 c0.681,0.18,1.416,0.28,2.248,0.28c2.56,0,5.12-0.853,5.973-2.56l139.093-139.093l11.093,11.093c0,0,0,0,0.001,0.001 c0,0,0.001,0.001,0.001,0.001l23.892,23.892c0,0,0,0,0.001,0.001c0,0,0.001,0.001,0.001,0.001l118.612,118.612 c4.267,4.267,9.387,5.973,14.507,5.973s10.24-1.707,13.653-5.973l68.267-68.267C512.813,430.08,512.813,417.28,505.133,409.6z M110.04,472.747l-29.858-29.858l283.716-282.898l29.449,29.449L110.04,472.747z M415.533,23.04c7.68-7.68,21.333-7.68,29.013,0 l43.52,43.52c7.68,7.68,7.68,21.333,0,29.013l-46.08,46.08L369.453,69.12L415.533,23.04z M357.507,81.067l36.267,36.267 L430.04,153.6l-23.893,23.893l-72.533-72.533L357.507,81.067z M163.373,236.8l18.347-18.347c3.413-3.413,3.413-8.533,0-11.947 s-8.533-3.413-11.947,0l-18.347,18.347l-12.373-12.373l29.867-29.867c3.413-3.413,3.413-8.533,0-11.947s-8.533-3.413-11.947,0 l-29.867,29.867l-11.947-11.947l17.92-17.92c3.413-3.413,3.413-8.533,0-11.947s-8.533-3.413-11.947,0l-17.92,17.92 l-11.947-11.947l29.867-29.867c3.413-3.413,3.413-8.533,0-11.947s-8.533-3.413-11.947,0L79.32,152.747l-12.8-12.8l17.92-17.92 c3.413-3.413,3.413-8.533,0-11.947s-8.533-3.413-11.947,0L54.573,128l-11.947-11.947l29.867-29.867 c3.413-3.413,3.413-8.533,0-11.947s-8.533-3.413-11.947,0L30.68,104.107L18.733,92.16c-3.413-3.413-3.413-8.533,0-11.947 L79.32,19.627c1.707-1.707,3.413-2.56,5.973-2.56c1.707,0,4.267,0.853,5.973,2.56L243.16,171.52l-72.533,72.533L163.373,236.8z M321.667,117.76l29.858,29.858L67.809,430.516L38.36,401.067L321.667,117.76z M31.533,418.133l27.996,27.996 c0.426,0.975,1.047,1.9,1.871,2.724l30.846,30.845l-73.512,12.674L31.533,418.133z M493.187,425.813L424.92,494.08 c-0.853,0.853-3.413,0.853-4.267,0l-88.32-88.32l30.293-30.293c3.413-3.413,3.413-8.533,0-11.947 c-3.413-3.413-8.533-3.413-11.947,0l-30.293,30.293l-12.373-12.373l17.92-17.92c3.413-3.413,3.413-8.533,0-11.947 c-3.413-3.413-8.533-3.413-11.947,0l-17.92,17.92l-11.947-11.947l29.867-29.867c3.413-3.413,3.413-8.533,0-11.947 c-3.413-3.413-8.533-3.413-11.947,0L272.173,345.6l-5.12-5.12l72.533-72.533l153.6,153.6 C494.04,422.4,494.04,424.96,493.187,425.813z"/> </g> </g> </g> </svg>
      <span><?php echo $Lang->tr('Drawing'); ?></span>
    </div>
    <ul class="kr-dash-pan-hedr-smli kr-dash-pan-hedr-smliwsvg" container="<?php echo $container; ?>">
      <li kr-drawingtool="trendline">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 28" width="28" height="28"><g fill-rule="nonzero"><path d="M7.354 21.354l14-14-.707-.707-14 14z"></path><path d="M22.5 7c.828 0 1.5-.672 1.5-1.5s-.672-1.5-1.5-1.5-1.5.672-1.5 1.5.672 1.5 1.5 1.5zm0 1c-1.381 0-2.5-1.119-2.5-2.5s1.119-2.5 2.5-2.5 2.5 1.119 2.5 2.5-1.119 2.5-2.5 2.5zM5.5 24c.828 0 1.5-.672 1.5-1.5s-.672-1.5-1.5-1.5-1.5.672-1.5 1.5.672 1.5 1.5 1.5zm0 1c-1.381 0-2.5-1.119-2.5-2.5s1.119-2.5 2.5-2.5 2.5 1.119 2.5 2.5-1.119 2.5-2.5 2.5z"></path></g></svg>
        <span><?php echo $Lang->tr('Trend Line'); ?></span>
      </li>
      <li kr-drawingtool="text">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 28" width="28" height="28"><path d="m9.5 5C8.68 5 8 5.67 8 6.5v2h1v-2c0-.27.23-.5.5-.5H14v16h-2v1h5v-1h-2V6h4.5c.28 0 .5.22.5.5v2h1v-2c0-.83-.67-1.5-1.5-1.5h-10z"></path></svg>
        <span><?php echo $Lang->tr('Text'); ?></span>
      </li>
    </ul>
  </div>
<?php endif; ?>
</section>
<?php endif; ?>
<?php if($availableTrading):

  $priceMarketUnit = $listThirdParty[0]->_getPriceTrade($listThirdParty[0]::_formatPair($Coin->_getSymbol(), $CryptoApi->_getCurrency()));
  $infosPairMarket = $listThirdParty[0]->_getInfosPair($Coin->_getSymbol(), $CryptoApi->_getCurrency());

  $Balance = new Balance($User, $App);
  $SymbolTrade = $Balance->_symbolAbrev($CryptoApi->_getCurrencySymbol());
  $Precision = 6;
  if($Balance->_symbolIsMoney($CryptoApi->_getCurrencySymbol())) $Precision = 2;

  ?>
<div class="kr-dash-pan-action kr-dash-pan-action-limit" thirdparty="<?php echo $listThirdParty[0]->_getExchangeName(); ?>" container="<?php echo $container; ?>" currency="<?php echo $CryptoApi->_getCurrency(); ?>" symbol="<?php echo $Coin->_getSymbol(); ?>">
  <?php if(false): ?>
    <div class="kr-dash-pan-action-slcthird" <?php if($App->_hiddenThirdpartyActive()) echo 'style="display:none";'; ?>>
      <div kr-trading-price="<?php echo $priceMarketUnit; ?>" kr-chart-trade-tp="<?php echo $listThirdParty[0]->_getExchangeName(); ?>">
        <img src="<?php echo APP_URL; ?>/assets/img/icons/trade/<?php echo $listThirdParty[0]->_getLogo(); ?>" alt="">
        <svg class="lnr lnr-chevron-down"><use xlink:href="#lnr-chevron-down"></use></svg>
      </div>
      <ul>
        <?php
        foreach (array_slice($listThirdParty, 1) as $ThirdPartySelector) {
          ?>
          <li class="kr-dash-pan-chg-exg" kr-trading-price="<?php echo $ThirdPartySelector->_getPriceTrade($ThirdPartySelector::_formatPair($Coin->_getSymbol(), $CryptoApi->_getCurrency())); ?>" kr-chart-trade-tp="<?php echo $ThirdPartySelector->_getExchangeName(); ?>"><img src="<?php echo APP_URL; ?>/assets/img/icons/trade/<?php echo $ThirdPartySelector->_getLogo(); ?>" alt=""></li>
          <?php
        }
        ?>
      </ul>
    </div>
  <?php endif; ?>
  <div class="kr-dash-pan-action-amount">
    <div class="kr-dash-pan-action-amount-s">
      <span><?php echo $Lang->tr('Amount'); ?></span>
      <div>
        <span><?php echo $Coin->_getSymbol(); ?></span>
        <input type="number" min="<?php echo $infosPairMarket['min_thirdparty_crypto']; ?>" step="<?php echo $infosPairMarket['min_thirdparty_crypto']; ?>" placeholder="<?php echo $infosPairMarket['min_thirdparty_crypto']; ?>" name="" value="<?php echo rtrim(number_format($infosPairMarket['min_thirdparty_crypto'], 10), "0"); ?>">
      </div>
    </div>
    <ul>
      <li trade-act="minus">-</li>
      <li trade-act="plus">+</li>
    </ul>
    <div class="kr-dash-pan-action-amount-esyslc">
      <ul>
        <?php
        for ($i=1; $i <= 5; $i++) {
          $premadeSum = ($infosPairMarket['min_thirdparty_crypto'] * exp($i));
          ?>
          <li kr-premade-v="<?php echo $App->_formatNumber($premadeSum, ($Precision == 2 ? 2 : 3)); ?>">
            <span><?php echo $App->_formatNumber($premadeSum, ($Precision == 2 ? 2 : 3)).' '.$Coin->_getSymbol(); ?></span>
          </li>
          <?php
        }
        ?>
      </ul>
    </div>
  </div>
  <div class="kr-dash-pan-action-qtd" kr-market-multticker="<?php echo $priceMarketUnit; ?>">
    <label><?php echo $CryptoApi->_getCurrencySymbol(); ?> <?php echo $Lang->tr('quantity'); ?></label>
    <span><?php echo $App->_formatNumber($infosPairMarket['min_thirdparty_crypto'] * $priceMarketUnit, 6); ?></span>
  </div>
  <div class="kr-dash-pan-action-btn kr-dash-pan-action-btn-buy">
    <img class="kr-dash-pan-action-btn-img-b" kr-cipc="<?php echo $container; ?>" src="<?php echo APP_URL; ?>/app/modules/kr-dashboard/statics/img/icons/buy_market.svg" alt="">
    <span><?php echo $Lang->tr('Buy'); ?></span>
  </div>
  <div class="kr-dash-pan-action-btn kr-dash-pan-action-btn-sell">
    <img class="kr-dash-pan-action-btn-img-s" kr-cipc="<?php echo $container; ?>" src="<?php echo APP_URL; ?>/app/modules/kr-dashboard/statics/img/icons/sell_market.svg" alt="">
    <span><?php echo $Lang->tr('Sell'); ?></span>
    <div class="kr-dash-pan-action-confirm">

      <header>
        <span><?php echo $Lang->tr('Confirmation'); ?></span>
        <div>
          <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
        </div>
      </header>
      <ul>
        <li kr-order-lmi-h="true">
          <span><?php echo $Lang->tr('Unit price'); ?></span>
          <span kr-confirm-v="unit_price" kr-confirm-v-up="<?php echo $priceMarketUnit; ?>"><i><?php echo $App->_formatNumber($priceMarketUnit, ($priceMarketUnit > 10 ? 2 : 6)); ?></i> <?php echo $SymbolTrade; ?></span>
        </li>
        <li kr-order-lmi-s="true" style="display:none;">
          <span><?php echo $Lang->tr('Purchase price'); ?></span>
          <span kr-confirm-v="purchase_price"><i><?php echo $App->_formatNumber($priceMarketUnit, ($priceMarketUnit > 10 ? 2 : 6)); ?></i> <?php echo $SymbolTrade; ?></span>
        </li>
        <li>
          <span><?php echo $Lang->tr('Investment'); ?></span>
          <span kr-confirm-v="amount"><i><?php echo $App->_formatNumber($infosPairMarket['min_thirdparty_crypto'] * $priceMarketUnit, $Precision); ?></i> <?php echo $SymbolTrade; ?></span>
        </li>
        <li kr-confirm-qntd="spvmx">
          <span><?php echo $Coin->_getSymbol(); ?> <?php echo $Lang->tr('Quantity'); ?></span>
          <span kr-confirm-v="investment"><i><?php echo $App->_formatNumber($infosPairMarket['min_thirdparty_crypto'], 6); ?></i></span>
        </li>
        <?php
        $TotalTrade = $infosPairMarket['min_thirdparty_crypto'] * $priceMarketUnit;

        if($App->_hiddenThirdpartyActive()): ?>
          <li>
            <span><?php echo $Lang->tr('Commission'); ?></span>
            <span kr-confirm-v="fees" kr-confirm-v-up="<?php echo $App->_hiddenThirdpartyTradingFee(); ?>"><i class="kr-confirm-sminfc"><?php echo $App->_formatNumber($App->_hiddenThirdpartyTradingFee(), 2); ?>% =</i> <i><?php echo $App->_formatNumber($TotalTrade * ($App->_hiddenThirdpartyTradingFee() / 100), $Precision); ?></i> <?php echo $SymbolTrade; ?></span>
          </li>
        <?php endif; ?>
      </ul>
      <div>
        <span><?php echo $Lang->tr('Total'); ?></span>

        <?php if($App->_hiddenThirdpartyActive()): ?>
          <span kr-confirm-v="total"><i><?php echo $App->_formatNumber($TotalTrade - ($TotalTrade * ($App->_hiddenThirdpartyTradingFee() / 100)), $Precision); ?></i> <?php echo $SymbolTrade; ?></span>
        <?php else: ?>
          <span kr-confirm-v="total"><i><?php echo $App->_formatNumber($TotalTrade, $Precision); ?></i> <?php echo $SymbolTrade; ?></span>
        <?php endif; ?>
      </div>
      <a class="btn btn-green btn-kr-action-placetrade"><?php echo $Lang->tr('Confirm buying'); ?></a>
    </div>
  </div>
  <div class="kr-dash-pan-action-limitprice kr-dash-pan-action-limitprice-selected">
    <div class="kr-dash-pan-action-limitprice-form" style="display:none;">
      <header>
        <span><?php echo $Lang->tr('Purchase at ...'); ?></span>
      </header>
      <div class="kr-dash-pan-action-limitprice-select">
        <div class="kr-dash-pan-action-limitprice-inpt" kr-lm-container="<?php echo $container; ?>">
          <span><?php echo $Lang->tr('Asset price'); ?></span>
          <input type="number" class="kr-limitprice-buy" placeholder="<?php echo $Lang->tr('Market price'); ?>" kr-limitprice-buy-ac="false" cc-price="<?php echo $priceMarketUnit; ?>" name="" value="">
        </div>
        <div class="kr-dash-pan-action-limitprice-pm" kr-lm-container="<?php echo $container; ?>" kr-lm-min="<?php echo $infosPairMarket['min_thirdparty_crypto'] * $priceMarketUnit; ?>" kr-lm-step="<?php echo (float)$infosPairMarket['min_thirdparty_crypto'] * $priceMarketUnit; ?>">
          <div kr-lm="plus">+</div>
          <div kr-lm="minus">-</div>
        </div>
      </div>
      <div class="kr-dash-pan-action-limitprice-rst" onclick="_setOrderByMarket('<?php echo $container; ?>')">
        <span><?php echo $Lang->tr('Revert to Market Price'); ?></span>
      </div>
    </div>
    <div class="kr-dash-pan-action-limitprice-btn" onclick="_showLimitOrder('<?php echo $container; ?>')">
      <span><?php echo $Lang->tr('Purchase at ...'); ?></span>
    </div>
    <div class="kr-dash-pan-action-limitprice-infos" onclick="_showLimitOrder('<?php echo $container; ?>')" style="display:none;">
      <span>-</span>
      <div>
        <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="348.333px" height="348.334px" viewBox="0 0 348.333 348.334" style="enable-background:new 0 0 348.333 348.334;" xml:space="preserve"> <g> <path d="M336.559,68.611L231.016,174.165l105.543,105.549c15.699,15.705,15.699,41.145,0,56.85 c-7.844,7.844-18.128,11.769-28.407,11.769c-10.296,0-20.581-3.919-28.419-11.769L174.167,231.003L68.609,336.563 c-7.843,7.844-18.128,11.769-28.416,11.769c-10.285,0-20.563-3.919-28.413-11.769c-15.699-15.698-15.699-41.139,0-56.85 l105.54-105.549L11.774,68.611c-15.699-15.699-15.699-41.145,0-56.844c15.696-15.687,41.127-15.687,56.829,0l105.563,105.554 L279.721,11.767c15.705-15.687,41.139-15.687,56.832,0C352.258,27.466,352.258,52.912,336.559,68.611z"/> </g> </svg>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
<?php //var_dump($listThirdParty); ?>
<?php if($App->_tradingviewchartEnable() || ($App->_allowSwitchChart() && $User->_tradingviewChartLibraryUse())):
  $tradingViewChartID = uniqid();
  ?>
<div class="tradingview-widget-container" style="height:100%;<?php if($availableTrading) echo 'margin-right:121px;'; ?>">
  <div id="tradingview_<?php echo $tradingViewChartID; ?>"  style="height:100%;"></div>
  <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
  <script type="text/javascript">
  tradingviewChart = new TradingView.widget(
      {
      "autosize": true,
      "symbol": "<?php echo ($availableTrading ? strtoupper($listThirdParty[0]->_getExchangeReal()).":" : '').$Coin->_getSymbol().$CryptoApi->_getCurrency(); ?>",
      "interval": "1",
      "timezone": "Etc/UTC",
      "theme": "Dark",
      "style": "1",
      "locale": "en",
      "toolbar_bg": "#f1f3f6",
      "enable_publishing": false,
      "hide_side_toolbar": false,
      "withdateranges": true,
      "custom_css_url": "<?php echo APP_URL; ?>/app/modules/kr-dashboard/statics/css/tradingview.css",
      "container_id": "tradingview_<?php echo $tradingViewChartID; ?>"
    }
  );

  </script>
  <style media="screen">
    .pane-legend-title__details { display: none; }
  </style>
</div>
<?php else: ?>
  <div class="kr-graph-tooledit" kr-toolbox-container="<?php echo $container; ?>">
    <div class="kr-graph-tooledit-draggble">
      <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:a="http://ns.adobe.com/AdobeSVGViewerExtensions/3.0/" x="0px" y="0px" width="40px" height="91px" viewBox="0 0 40 91" style="enable-background:new 0 0 40 91;" xml:space="preserve"> <defs> </defs> <g> <path d="M36.9,91h-3.8c-1.7,0-3.1-1.4-3.1-3.1v-3.8c0-1.7,1.4-3.1,3.1-3.1h3.8c1.7,0,3.1,1.4,3.1,3.1v3.8C40,89.6,38.6,91,36.9,91z "/> </g> <g> <path d="M6.9,91H3.1C1.4,91,0,89.6,0,87.9v-3.8C0,82.4,1.4,81,3.1,81h3.8c1.7,0,3.1,1.4,3.1,3.1v3.8C10,89.6,8.6,91,6.9,91z"/> </g> <g> <path d="M36.9,64h-3.8c-1.7,0-3.1-1.4-3.1-3.1v-3.8c0-1.7,1.4-3.1,3.1-3.1h3.8c1.7,0,3.1,1.4,3.1,3.1v3.8C40,62.6,38.6,64,36.9,64z "/> </g> <g> <path d="M6.9,64H3.1C1.4,64,0,62.6,0,60.9v-3.8C0,55.4,1.4,54,3.1,54h3.8c1.7,0,3.1,1.4,3.1,3.1v3.8C10,62.6,8.6,64,6.9,64z"/> </g> <g> <path d="M36.9,37h-3.8c-1.7,0-3.1-1.4-3.1-3.1v-3.8c0-1.7,1.4-3.1,3.1-3.1h3.8c1.7,0,3.1,1.4,3.1,3.1v3.8C40,35.6,38.6,37,36.9,37z "/> </g> <g> <path d="M6.9,37H3.1C1.4,37,0,35.6,0,33.9v-3.8C0,28.4,1.4,27,3.1,27h3.8c1.7,0,3.1,1.4,3.1,3.1v3.8C10,35.6,8.6,37,6.9,37z"/> </g> <g> <path d="M36.9,10h-3.8C31.4,10,30,8.6,30,6.9V3.1C30,1.4,31.4,0,33.1,0h3.8C38.6,0,40,1.4,40,3.1v3.8C40,8.6,38.6,10,36.9,10z"/> </g> <g> <path d="M6.9,10H3.1C1.4,10,0,8.6,0,6.9V3.1C0,1.4,1.4,0,3.1,0h3.8C8.6,0,10,1.4,10,3.1v3.8C10,8.6,8.6,10,6.9,10z"/> </g> </svg>
    </div>
    <?php
    foreach (DashboardToolbox::_getConfigurationItem() as $typeToolBox => $configuration) {
      foreach ($configuration as $asset) {
      ?>
      <div class="kr-graph-tooledit-cell kr-graph-tooledit-<?php echo $asset['type']; ?>" toolboxedit-type="<?php echo $typeToolBox; ?>" toolboxedit-asset="<?php echo $asset['assets']; ?>">
        <div class="kr-graph-tooledit-preview"></div>
        <?php if(!is_null($asset['dropdown'])): ?>
          <ul>
            <?php
            foreach ($asset['dropdown'] as $itemKey => $itemInterface) {
              if($asset['type'] == "color"){
                echo '<li kr-dropdown-item="'.(is_numeric($itemKey) ? $itemInterface : $itemKey).'"></li>';
              } else if($asset['type'] == "thickness"){
                echo '<li kr-dropdown-item="'.(is_numeric($itemKey) ? $itemInterface : $itemKey).'"><div></div></li>';
              }

            }
            ?>
          </ul>
        <?php endif; ?>
      </div>
      <?php
    }
    }
    ?>

  </div>
<div class="kr-dash-pan-graph <?php if($availableTrading) echo 'kr-dash-pan-graph-trading-a'; ?>" market="<?php echo ($availableTrading ? $listThirdParty[0]->_getExchangeName() : 'CCCAGG'); ?>" scrollv="4" id="graph-<?php echo $container; ?>" id="container">

</div>
<?php endif; ?>
