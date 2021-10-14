<?php

/**
 * Dashboard
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "config/config.settings.php";
require "vendor/autoload.php";
require "app/src/MySQL/MySQL.php";
require "app/src/App/App.php";
require "app/src/App/AppModule.php";
require "app/src/User/User.php";
require "app/src/Lang/Lang.php";
require "app/src/CryptoApi/CryptoIndicators.php";
require "app/src/CryptoApi/CryptoGraph.php";
require "app/src/CryptoApi/CryptoHisto.php";
require "app/src/CryptoApi/CryptoCoin.php";
require "app/src/CryptoApi/CryptoApi.php";

// Load app modules & check domain
$App = new App(true);

$App->_checkDomain();
$App->_loadModulesControllers();
try {
//echo '<pre>';
  // Check user is logged
  $User = new User();

  //$BlockExplorer = new BlockExplorer($App, $User);
  //$BlockExplorer->_checkDoneTransaction($BlockExplorer->_getAllTransaction());
  // foreach ($BlockExplorer->_getDepositAddress() as $key => $addressExplorer) {
  //   var_dump($addressExplorer->_getAddress());
  //   var_dump($addressExplorer->_getLinkedBlockExplorer()->_getHistoryTransaction($addressExplorer->_getAddress()));
  //   die();
  // }

  //$LitecoinExplorer = new LitecoinExplorer($App, $User);
  //var_dump($LitecoinExplorer->_getTransactionInfos('684568e0d96bdadcf031ca6ae1149e463c30b11cdd42a8d055f8bc612cb3792c'));
  // $BitcoinExplorer = new BitcoinExplorer($App, $User);
  // var_dump($BitcoinExplorer->_getBlockHeight());
  // var_dump($BitcoinExplorer->_getTransactionInfos("6c8f22c9251d3a407d63fa6591664812face25a022f7a922155bd3e42854a583"));

  // //
  // //
  //die();
  if(!$User->_isLogged()) header('Location: '.APP_URL);

  // Init lang object
  $Lang = new Lang($User->_getLang(), $App);

  // Init user charge
  $Charge = $User->_getCharge($App);

  // Init CryptoApi object
  $CryptoApi = new CryptoApi($User, null, $App);

  // Load dashboard object
  $Dashboard = new Dashboard($CryptoApi, $User);

  $Mobile = new Mobile_Detect();

  $Calculator = new Calculator();

  if($App->_getIdentityEnabled()) $Identity = new Identity($User);


  $Balance = null;
  if($App->_hiddenThirdpartyActive()){
    $HiddenThirdParty = new HiddenThirdParty($User, $App);
    $Balance = new Balance($User, $App);
    $BalanceList = $Balance->_getBalanceList();
    $CurrentBalance = $Balance->_getCurrentBalance();
  }

  $mobileDetected = new Mobile_Detect();

} catch (Exception $e) {
  die($e->getMessage());
}


?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <meta charset="utf-8">
    <title static-title="<?php echo $App->_getAppTitle(); ?>"><?php echo $App->_getAppTitle(); ?></title>
    <meta name="description" content="<?php echo $App->_getAppDescription(); ?>">

    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/site.webmanifest">
    <link rel="shortcut icon" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon.ico">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="<?php echo APP_URL; ?>/assets/img/icons/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

    <link rel="stylesheet" href="assets/bower/animate.css/animate.min.css?v=<?php echo App::_getVersion(); ?>">
    <link rel="stylesheet" href="assets/bower/chosen/chosen.min.css?v=<?php echo App::_getVersion(); ?>">

    <link rel="stylesheet" href="assets/bower/dropzone/dist/min/dropzone.min.css?v=<?php echo App::_getVersion(); ?>">

    <link rel="stylesheet" href="assets/bower/jquery-palette-color-picker/src/palette-color-picker.css?v=<?php echo App::_getVersion(); ?>">

    <link href="assets/bower/air-datepicker/dist/css/datepicker.min.css?v=<?php echo App::_getVersion(); ?>" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">

    <link rel="stylesheet" href="assets/css/checkboxes.min.css">

    <?php echo $App->_getAssetsList('css'); ?>

    <link rel="stylesheet" href="assets/css/responsive-global.css">
    <link rel="stylesheet" href="assets/css/responsive-tablet.css">
    <link rel="stylesheet" href="assets/css/responsive-mobile.css">

    <link rel="stylesheet" href="assets/css/themes/light.css">
  </head>
  <body kr-hm="<?php echo ($App->_getHideMarket() ? "1" : "0"); ?>" logopath-black="<?php echo $App->_getLogoBlackPath(); ?>" logopath="<?php echo $App->_getLogoPath(); ?>" hrefapp="<?php echo APP_URL; ?>" kr-timestamp="<?php echo time(); ?>" mbill="<?php echo ($mobileDetected->isMobile() || $mobileDetected->isTablet() ? 'true' : 'false'); ?>" sintro="<?php echo (!$Dashboard->_isNew() ? '1' : '0'); ?>" <?php echo ($User->_whiteMode() ? 'kr-theme="light"' : ''); ?> kr-numformat='<?php echo str_replace('"', '', $App->_getNumberFormat()); ?>' class="<?php if($Dashboard->_isNew() || ($App->_getNewsPopup() && $User->_showNewsPopupNeeded($App))) echo 'kr-nblr'; ?> " activeabo="<?php echo ($Charge->_activeAbo() || $Charge->_isTrial() || $User->_isAdmin() || $User->_isManager() || !$App->_subscriptionEnabled() ? '1' : '0'); ?>">
  <!--    <section class="kr-search">
      <header>
        <input type="text" name="" value="">
        <div class="kr-search-exchangeselector">
          <span>All exchanges</span>
          <svg class="lnr lnr-chevron-down"><use xlink:href="#lnr-chevron-down"></use></svg>
        </div>
      </header>
      <ul>
      </ul>
    </section>

  -->
  <?php if($mobileDetected->isMobile() || $mobileDetected->isTablet()): ?>
    <section class="responsive-portrait kr-ov-nblr">
      <div>
        <?php
        if($App->_isLogoDefault()){
        ?>
        <img src="<?php echo APP_URL.'/assets/img/logo.svg'; ?>" alt="">
        <?php } else { ?>
          <img src="<?php echo APP_URL.$App->_getLogoPath(); ?>" alt="">
        <?php } ?>
        <span><?php echo $Lang->tr('Please turn your device in landscape mode.'); ?></span>
      </div>
    </section>
  <?php endif; ?>

    <?php $UserIsNew = false; if($Dashboard->_isNew() && ($Charge->_activeAbo() || $Charge->_isTrial() || $User->_isAdmin() || $User->_isManager() || !$App->_subscriptionEnabled())){
      $DashboardTopList = new DashboardTopList($CryptoApi, $User);
      $DashboardTopList->_addItem($App->_getInfosStartingPair()['symbol'], $App->_getInfosStartingPair()['currency'], $App->_getInfosStartingPair()['market']);

      $WatchingList = new WatchingList($CryptoApi, $User);
      foreach ($App->_getInfosStartingWatchingList() as $key => $infosWatchingItem) {
        $WatchingList->_addItem($infosWatchingItem['symbol'], $infosWatchingItem['currency'], $infosWatchingItem['market']);
      }
      $UserIsNew = true;
      require('app/modules/kr-user/views/welcome.php');

    } ?>

    <section class="kr-notif-alt kr-ov-nblr">
    </section>

    <section class="kr-searchpop kr-ov-nblr">
      <div class="kr-search-field-content" kr-search-hide-empty="false" kr-search-callback="">
        <div class="kr-search-field-content-bicon">
          <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 56.966 56.966" style="enable-background:new 0 0 56.966 56.966;" xml:space="preserve"> <path d="M55.146,51.887L41.588,37.786c3.486-4.144,5.396-9.358,5.396-14.786c0-12.682-10.318-23-23-23s-23,10.318-23,23 s10.318,23,23,23c4.761,0,9.298-1.436,13.177-4.162l13.661,14.208c0.571,0.593,1.339,0.92,2.162,0.92 c0.779,0,1.518-0.297,2.079-0.837C56.255,54.982,56.293,53.08,55.146,51.887z M23.984,6c9.374,0,17,7.626,17,17s-7.626,17-17,17 s-17-7.626-17-17S14.61,6,23.984,6z"/> <g> </svg>
          <input type="text" class="kr-search-field" id="kr-search-field-searchpop" placeholder="BTCETH ..."  name="" value="">
          <div onclick="closeBigSearch();">
            <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
          </div>
        </div>
      </div>
    </section>

    <?php if($App->_getDonationEnabled()): ?>
    <section class="kr-donation-list kr-ov-nblr">
      <section>
        <section>
          <div onclick="_hideDonationPopup();"><svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg></div>
        </section>
        <div class="kr-donation">
          <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="510px" height="510px" viewBox="0 0 510 510" style="enable-background:new 0 0 510 510;" xml:space="preserve"> <g> <g id="favorite"> <path d="M255,489.6l-35.7-35.7C86.7,336.6,0,257.55,0,160.65C0,81.6,61.2,20.4,140.25,20.4c43.35,0,86.7,20.4,114.75,53.55 C283.05,40.8,326.4,20.4,369.75,20.4C448.8,20.4,510,81.6,510,160.65c0,96.9-86.7,175.95-219.3,293.25L255,489.6z"/> </g> </g> </svg>
          <span><?php echo $Lang->tr($App->_getDonationText()); ?></span>
        </div>
        <ul>
          <?php
          $DonationList = explode("\n", $App->_getDonationList());
          foreach ($DonationList as $key => $value) {
            $infosLine = explode(':', $value);
            ?>
            <li>
              <span><?php echo $infosLine[0]; ?></span>
              <div>
                <span><?php echo $infosLine[1]; ?></span>
                <button type="button" class="btn btn-small btn-autowidth btn-green kr-donation-sqrc" name="button">Show QR Code</button>
              </div>
              <section class="kr-donation-sqrc-img">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $infosLine[1]; ?>" alt="">
              </section>
            </li>
            <?php
          }
          ?>
        </ul>
      </section>
    </section>
  <?php endif; ?>

    <?php if($Charge->_activeAbo() || $Charge->_isTrial() || $User->_isAdmin() || $User->_isManager() || !$App->_subscriptionEnabled()): ?>

    <?php if($App->_getNewsPopup() && $User->_showNewsPopupNeeded($App)): ?>
      <section class="kr-adm-notif-popup kr-ov-nblr">
        <section>
          <?php if(!is_null($App->_getNewsPopupVideo())): ?>
            <iframe width="100%" height="240" src="https://www.youtube.com/embed/<?php echo $App->_getNewsPopupVideo(); ?>?rel=0&amp;controls=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
          <?php endif; ?>
          <h2><?php echo $App->_getNewsPopupTitle(); ?></h2>
          <div><?php echo nl2br($App->_getNewsPopupText()); ?></div>
          <footer>
            <a onclick="closeUpdateNewFeature();" class="btn btn-orange btn-shadow btn-autowidth" href="#"><?php echo $Lang->tr('I Agree / Continue'); ?></a>
          </footer>
        </section>
      </section>
    <?php endif; ?>

    <header>
      <div>
        <div>
          <div class="kr-logo">
            <?php
            if($App->_isLogoDefault()){
            ?>
            <img src="<?php echo APP_URL.'/assets/img/logo'.($User->_whiteMode() ? '_black' : ''); ?>.svg" alt="">
            <?php } else { ?>
              <img src="<?php echo APP_URL.($User->_whiteMode() ? $App->_getLogoBlackPath() : $App->_getLogoPath()); ?>" alt="">
            <?php } ?>
          </div>
          <div class="kr-change-dashboard">
            <img src="<?php echo APP_URL.'/assets/img/icons/dashboard/4_grid.svg'; ?>" alt="">
            <div class="kr-change-dashboard-selector">
              <?php
              $listDashBoard = [];
              foreach ($Dashboard->_getListDashboardAvailable() as $dashboardType) {
                $listDashBoard[explode('_', $dashboardType)[0]][] = $dashboardType;
              }
              foreach ($listDashBoard as $numberGraph => $dashboardListType) {
                ?>
                <div>
                  <div>
                    <span><?php
                      if(($numberGraph - 1) == count($listDashBoard)) echo $numberGraph.' '.$Lang->tr('charts or more');
                      else echo $numberGraph.' '.$Lang->tr('chart').($numberGraph > 0 ? 's' : '');?></span>
                  </div>
                  <ul>
                    <?php
                    foreach ($dashboardListType as $keyDashboard => $nameDashboard) {
                      ?>
                      <li kr-dashboard-cfg="<?php echo $nameDashboard; ?>"><img src="<?php echo APP_URL.'/assets/img/icons/dashboard/'.$nameDashboard.'.svg'; ?>"/></li>
                      <?php
                    }
                    ?>
                  </ul>
                </div>
                <?php
              }
              ?>

            </div>
          </div>
          <ul class="kr-top-graphlist">
            <?php
            foreach ($Dashboard->_getTopList(true) as $TopListItem) {
              try {
                $DataCoinTopList = $TopListItem->_getCoinItem();
                ?>
                <li class="kr-mono kr-top-graphlist-item <?php if(!is_null($TopListItem->_getKeyGraph())) echo 'kr-top-graphlist-item-view'; ?>" topitem="<?php echo $TopListItem->_getItemID(); ?>" container="<?php echo $TopListItem->_getKeyGraph(); ?>" symbol="<?php echo $DataCoinTopList->_getSymbol(); ?>" coinname="<?php echo $DataCoinTopList->_getCoinName(); ?>" market="<?php echo strtoupper($TopListItem->_getMarket()); ?>" currency="<?php echo $TopListItem->_getCurrency(); ?>" pasth="">
                  <div class="kr-top-graphlist-closeb">
                    <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
                  </div>
                  <div class="kr-top-graphlist-inf">
                    <label><?php echo ($App->_getHideMarket() ? '' : $TopListItem->_getMarket().':').$DataCoinTopList->_getSymbol().'/'.$TopListItem->_getCurrency(); ?></label>
                    <span kr-data="CHANGE24HOURPCT">~</span>
                  </div>
                </li>
              <?php
              } catch (\Exception $e) {
                $TopListItem->_deleteTopList();
              }
            } ?>
          </ul>
          <div class="kr-addgraph-dashboard" onclick="showBigSearch('addTopListDashboardSearchPop');">
            <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
            	 viewBox="0 0 31.444 31.444" style="enable-background:new 0 0 31.444 31.444;" xml:space="preserve">
            <path d="M1.119,16.841c-0.619,0-1.111-0.508-1.111-1.127c0-0.619,0.492-1.111,1.111-1.111h13.475V1.127
            	C14.595,0.508,15.103,0,15.722,0c0.619,0,1.111,0.508,1.111,1.127v13.476h13.475c0.619,0,1.127,0.492,1.127,1.111
            	c0,0.619-0.508,1.127-1.127,1.127H16.833v13.476c0,0.619-0.492,1.127-1.111,1.127c-0.619,0-1.127-0.508-1.127-1.127V16.841H1.119z"
            	/>
            </svg>
          </div>
        </div>

        <div>
          <?php if($App->_getIdentityEnabled()): ?>
            <div class="kr-heeader-btn kr-heeader-btn-identity" style="<?php if($Identity->_identityVerified()) echo 'display:none'; ?>">
              <input type="button" onclick="_showIdentityWizard();" class="btn btn-big btn-green btn-autowidth" name="" value="<?php echo $Lang->tr('Verify your identiy'); ?>">
            </div>
          <?php endif; ?>
          <?php $listThirdParty = null; if(!$App->_hiddenThirdpartyActive()):
            $Trade = new Trade($User, $App);
            $listThirdParty = $Trade->_getThirdPartyListAvailable();
            if(count($listThirdParty) > 0){
            $selectedThirdParty = $listThirdParty[0];
            $balanceList = $selectedThirdParty->_getBalance(true);
            $balanceSelectedSymbol = null;
            $balanceSelectedAmount = null;
            foreach ($balanceList as $key => $value) {
              if(!is_null($balanceSelectedSymbol)) continue;
              $balanceSelectedSymbol = $key;
              $balanceSelectedAmount = $value['free'];
            }

            ?>
            <div class="kr-wallet-top">
              <div class="kr-wallet-top-thirdparty">
                <div>
                  <span><?php echo $selectedThirdParty->_getName(); ?></span>
                  <span kr-balance-id="" class="kr-wallet-top-ammount">
                    <i><?php echo $App->_formatNumber($balanceSelectedAmount, 5); ?></i>
                    <i><?php echo $balanceSelectedSymbol; ?></i></span>
                </div>
                <svg class="lnr lnr-chevron-down"><use xlink:href="#lnr-chevron-down"></use></svg>
              </div>
              <section class="kr-wallet-top-thirdparty">
                <div class="kr-wallet-nativetrading">

                </div>
                <div class="kr-wallet-top-resum">
                  <h3><?php echo $Lang->tr('Balances'); ?></h3>
                  <ul>
                  <?php
                  foreach (array_slice($balanceList, 0, 12) as $key => $value) {
                    ?>
                    <li kr-wallet-exchange="<?php echo $selectedThirdParty->_getExchangeName(); ?>" kr-wallet-symbol="<?php echo $key; ?>">
                      <span><?php echo $key; ?></span>
                      <div></div>
                      <span><i><?php echo $App->_formatNumber($value['free'], ($value['free'] > 10 ? 2 : 5)); ?></i> <i><?php echo $key; ?></i></span>
                    </li>
                    <?php
                  }
                  ?>
                  </ul>
                  <div style="<?php echo (count($balanceList) > 12 ? '' : 'display:none;'); ?>" class="kr-wallet-balance-show-list" kr-balance-exchange="<?php echo $selectedThirdParty->_getExchangeName(); ?>">
                    <span><?php echo $Lang->tr('See all balances'); ?></span>
                  </div>
                </div>
                <div class="kr-wallet-top-change">
                  <h3>
                    <span><?php echo $Lang->tr('Account'); ?></span>
                  </h3>
                  <ul>
                    <?php foreach ($listThirdParty as $Exchange) {
                      ?>
                      <li kr-wallet-exch-name="<?php echo App::encrypt_decrypt('encrypt', $Exchange->_getExchangeName()); ?>">
                        <img src="<?php echo APP_URL.'/assets/img/icons/trade/'.$Exchange->_getLogo(); ?>" alt="">
                      </li>
                      <?php
                    } ?>

                  </ul>
                </div>
              </section>
            </div>
          <?php }
          endif; ?>
          <?php if($App->_hiddenThirdpartyActive() && ($App->_getTradingEnablePracticeAccount() || $App->_getTradingEnableRealAccount())): ?>
          <div class="kr-wallet-top">
            <div class="kr-wallet-top-<?php echo $CurrentBalance->_getBalanceType(); ?>">
              <div>
                <span><?php echo $Lang->tr($CurrentBalance->_getBalanceType().' account'); ?></span>
                <?php if($App->_getBalanceEstimationShown()):
                  $EstimatedValueBalance = $CurrentBalance->_getEstimationBalance();
                  $EstimatedValueSymbol = $CurrentBalance->_getEstimationSymbol();
                ?>
                  <span kr-balance-id="<?php echo $CurrentBalance->_getBalanceID(true); ?>" class="kr-wallet-top-ammount"><i><?php echo $App->_formatNumber($EstimatedValueBalance, 2); ?></i> <?php echo $EstimatedValueSymbol; ?></span>
                <?php else:

                  $BalanceResum = $CurrentBalance->_getBalanceListResum();
                  $BalanceResumFirstSymbol = array_keys($BalanceResum)[0];
                  $BalanceResumFirst = floatval($BalanceResum[$BalanceResumFirstSymbol]);
                  ?>
                  <span kr-balance-id="<?php echo $CurrentBalance->_getBalanceID(true); ?>" class="kr-wallet-top-ammount"><i><?php echo $App->_formatNumber($BalanceResumFirst, 2); ?></i> <?php echo $BalanceResumFirstSymbol; ?></span>
                <?php endif; ?>
                <?php if($App->_getBalanceEstimationShown()): ?>
                  <label><?php echo $Lang->tr('Estimate balance'); ?></label>
                <?php endif; ?>
              </div>
              <svg class="lnr lnr-chevron-down"><use xlink:href="#lnr-chevron-down"></use></svg>
            </div>
            <section>
              <div class="kr-wallet-top-resum">
                <h3><?php echo ucfirst($Lang->tr($CurrentBalance->_getBalanceType().' account')); ?></h3>
                <ul>
                <?php
                $balanceList = $CurrentBalance->_getBalanceListResum();
                foreach (array_slice($balanceList, 0, 12) as $symbol => $amount) {
                  ?>
                  <li kr-wallet-symbol="<?php echo $symbol; ?>">
                    <span><?php echo $symbol; ?></span>
                    <div></div>
                    <span><i><?php echo $App->_formatNumber($amount, ($amount > 10 ? 2 : 5)); ?></i> <i><?php echo $symbol; ?></i></span>
                  </li>
                  <?php
                }
                ?>
                </ul>
                <div class="kr-wallet-balance-show-list kr-wallet-balance-show-list-native">
                  <span><?php echo $Lang->tr('See all balances'); ?></span>
                </div>
              </div>
              <div class="kr-wallet-top-change">
                <h3>
                  <span><?php echo $Lang->tr('List accounts'); ?></span>
                  <a kr-balance-transaction-history="trshp" class="btn btn-autowidth btn-small btn-grey"><?php echo $Lang->tr('History'); ?></a>
                </h3>
                <ul>
                  <?php
                  $iconeBTCBalance = file_get_contents(APP_URL.'/assets/img/icons/crypto/BTC.svg');
                  foreach ($Balance->_getBalanceList() as $BalanceItem) {
                    if(!$App->_getBalanceEstimationShown()){
                      $BalanceResum = $BalanceItem->_getBalanceListResum();

                      $BalanceResumFirstSymbol = array_keys($BalanceResum)[0];
                      $BalanceResumFirst = floatval($BalanceResum[$BalanceResumFirstSymbol]);
                    }

                    ?>
                  <li kr-wallet-change="<?php echo $BalanceItem->_getBalanceID(true); ?>" class="kr-wallet-top-change-<?php echo $BalanceItem->_getType(); ?>">
                    <div>
                      <?php echo $iconeBTCBalance; ?>
                      <div>
                        <span><?php echo $Lang->tr($BalanceItem->_getBalanceType().' account'); ?></span>
                        <?php if($App->_getBalanceEstimationShown()):
                          $EstimatedValueBalance = $BalanceItem->_getEstimationBalance();
                          $EstimatedValueSymbol = $CurrentBalance->_getEstimationSymbol();
                          ?>
                          <label kr-balance-id="<?php echo $BalanceItem->_getBalanceID(true); ?>"><i><?php echo $App->_formatNumber($EstimatedValueBalance, 2); ?></i> <?php echo $EstimatedValueSymbol; ?></label>
                        <?php else: ?>
                          <label kr-balance-id="<?php echo $BalanceItem->_getBalanceID(true); ?>"><i><?php echo $App->_formatNumber($BalanceResumFirst, 2); ?></i> <?php echo $BalanceResumFirstSymbol; ?></label>
                        <?php endif; ?>
                        <?php if($App->_getBalanceEstimationShown()): ?>
                        <label class="kr-wallet-change-estimated"><?php echo $Lang->tr('Estimate balance'); ?></label>
                        <?php endif; ?>
                      </div>
                    </div>
                    <section>
                      <?php if($BalanceItem->_getType() == "real"): ?>
                        <?php if($App->_getIdentityEnabled()): ?>
                          <?php if($App->_getIdentityWithdrawBlocked()): ?>
                            <?php if($Identity->_identityVerified()): ?>
                              <a kr-credit-widthdraw="widthdraw" class="btn btn-grey btn-autowidth btn-small"><?php echo $Lang->tr('Widthdraw'); ?></a>
                            <?php else: ?>
                              <a onclick="_showIdentityWizard();return false;" class="btn btn-grey btn-autowidth btn-small"><?php echo $Lang->tr('Widthdraw'); ?></a>
                            <?php endif; ?>
                          <?php else: ?>
                            <a kr-credit-widthdraw="widthdraw" class="btn btn-grey btn-autowidth btn-small"><?php echo $Lang->tr('Widthdraw'); ?></a>
                          <?php endif; ?>
                        <?php else: ?>
                          <a kr-credit-widthdraw="widthdraw" class="btn btn-grey btn-autowidth btn-small"><?php echo $Lang->tr('Widthdraw'); ?></a>
                        <?php endif; ?>
                      <?php endif; ?>
                      <a kr-credit-balance="crdt" class="btn btn-<?php echo ($BalanceItem->_getType() == "practice" ? 'orange' : 'green'); ?> btn-autowidth btn-small"><?php echo $Lang->tr('Credit'); ?></a>
                    </section>
                  </li>
                  <?php } ?>
                </ul>
              </div>
            </section>
          </div>
          <?php endif; ?>
          <?php
          try {
            throw new Exception("Error Processing Request", 1);

            if($Gdax->_isActivated() && $User->_accessAllowedFeature($App, 'tradinglive')){
              $listAccount = $Gdax->_getListAccount();
              $firstAccount = $listAccount[0];
              foreach ($listAccount as $accountItem) {
                if($accountItem->currency == "USD") $firstAccount = $accountItem;
              }
              ?>
              <div class="kr-wallet-top">
                <div class="">
                  <div>
                    <svg class="lnr lnr-chevron-down"><use xlink:href="#lnr-chevron-down"></use></svg>
                  </div>
                  <div>
                    <label><?php echo $firstAccount->currency; ?></label>
                    <span class="kr-mono"><?php echo number_format($firstAccount->balance, ($firstAccount->balance < 10 ? 5 : 2), ',', ' '); ?></span>
                  </div>
                </div>
                <section>
                  <?php foreach ($listAccount as $accountItem) {
                    ?>
                    <div class="">
                      <div>
                        <?php if(file_exists('assets/img/icons/crypto/'.$accountItem->currency.'.svg')): ?>
                          <img src="<?php echo APP_URL; ?>/assets/img/icons/crypto/<?php echo $accountItem->currency; ?>.svg" alt="">
                        <?php endif; ?>
                      </div>
                      <div>
                        <label><?php echo $accountItem->currency; ?></label>
                        <span class="kr-mono"><?php echo number_format($accountItem->balance, ($accountItem->balance < 10 ? 5 : 2), ',', ' '); ?></span>
                      </div>
                    </div>
                    <?php
                  } ?>
                </section>
              </div>
              <?php
            }
          } catch (\Exception $e) {}
          ?>
          <ul>
            <li kr-action="kr-notification-center">
              <audio id="kr-notification-center-audio" controls preload="true"> <source src="<?php echo APP_URL; ?>/assets/sounds/notification.wav" type="audio/wav"> </audio>
              <div class="kr-notification-center-icon">
                <?php
                $NotificationCenter = new NotificationCenter($User);
                $NotificationUnread = $NotificationCenter->_getNumberNotificationUnseen();
                ?>
                <span style="<?php echo ($NotificationUnread == 0 ? 'display:none;' : ''); ?>"><?php echo $NotificationUnread; ?></span>
                <svg class="lnr lnr-alarm animated"><use xlink:href="#lnr-alarm"></use></svg>
              </div>
              <section class="kr-notification-center">
                <div><div class="sk-folding-cube"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div></div>
              </section>
            </li>
          </ul>
          <div class="kr-account
            <?php
            if($App->_getIdentityEnabled()):
              if($Identity->_identityInVerifcation()) echo 'kr-identity-in-verification';
              if($Identity->_identityVerified()) echo 'kr-identity-verified';
              if($Identity->_identityWizardNotStarted()) echo 'kr-identity-not-verified';
            endif;
            ?>
          kr-identity-acc">
            <div class="kr-account-pic kr-user-pic-s" style="background-image:url('<?php echo $User->_getPicture(); ?>');">
              <?php echo (is_null($User->_getPicture()) || strlen($User->_getPicture()) == 0 ? $User->_getInitial() : ''); ?>
              <?php if($App->_getIdentityEnabled()): ?>
                <div>
                  <?php
                  if($Identity->_identityInVerifcation()) echo '<svg class="lnr lnr-undo"><use xlink:href="#lnr-undo"></use></svg>';
                  if($Identity->_identityVerified()) echo '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 26 26" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 26 26"> <path d="m.3,14c-0.2-0.2-0.3-0.5-0.3-0.7s0.1-0.5 0.3-0.7l1.4-1.4c0.4-0.4 1-0.4 1.4,0l.1,.1 5.5,5.9c0.2,0.2 0.5,0.2 0.7,0l13.4-13.9h0.1v-8.88178e-16c0.4-0.4 1-0.4 1.4,0l1.4,1.4c0.4,0.4 0.4,1 0,1.4l0,0-16,16.6c-0.2,0.2-0.4,0.3-0.7,0.3-0.3,0-0.5-0.1-0.7-0.3l-7.8-8.4-.2-.3z"/> </svg>';
                  if($Identity->_identityWizardNotStarted()) echo '<svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>';
                  ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </header>


    <section class="kr-page-content">
      <nav class="kr-leftnav">
        <ul>
          <li type="module" kr-module="dashboard" kr-view="dashboard">
            <svg class="lnr lnr-chart-bars"><use xlink:href="#lnr-chart-bars"></use></svg>
            <span><?php echo $Lang->tr('Board'); ?></span>
          </li>
          <?php if((!is_null($listThirdParty) && count($listThirdParty) > 0) || $App->_hiddenThirdpartyActive()): ?>
            <li type="side" kr-side="kr-orderbook" kr-side-part="kr-orderbook">
              <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 512 512"> <g> <g> <path d="m307.7,212.5c-10.2,12.7-6.6,28.7 2.2,37.5l13.6,12.6c15.3,15.4 34.4,5.3 39.6,0l59.4-58.9c7-7.1 13.8-26 0-40l-13.6-12.6c-10-10.1-27.5-10.5-38.2-1.3l-80.9-80.1c6.4-8.1 11.2-25.3-1.6-38.2l-13.6-12.6c-10.4-10.5-29.2-10.5-39.6,0l-59.4,58.9c-12.9,13-9.4,30.5 0,40l13.6,12.6c13.3,13.5 29.6,7.4 37,2.2l14.9,14.6-230.1,228.5 50,50.5 230.9-229.2 15.8,15.5zm76.2-47.7c2.9-3 7.5-3 11.5,1.1l13.6,12.6c2.4,2.5 2.5,6.9 0,9.5l-59.4,58.9c-3.1,3.2-8.3,3.2-11.5,0l-13.6-12.6c-2-2-4-6.5 0-10.5l59.4-59zm-27.2-1.1l-35.2,35-80-79.7 35.4-35.8 79.8,80.5zm-141.4-49c-3.1,3.2-8.3,3.2-11.5,0l-13.6-12.6c-2-2-4-6.5 0-10.5l59.4-58.9c2.9-3 7.5-3 11.5,1.1l13.6,12.6c2.4,2.5 2.5,6.9 0,9.5l-59.4,58.8zm-153.2,282.1l-21.9-21.1 215.4-214.3 21.7,21.3-215.2,214.1z"/> <path d="m457.2,424.2v-55.8h-188.7v55.8h-43.8v76.8h276.3v-76.8h-43.8zm-167.8-35.8h148v35.8h-148v-35.8zm191.8,91.5h-235.6v-34.7h235.6v34.7z"/> </g> </g> </svg>
              <span><?php echo $Lang->tr('Order book'); ?></span>
            </li>
          <?php endif; ?>
          <?php if($User->_accessAllowedFeature($App, 'marketanalytic')): ?>
          <li type="module" kr-modules-hleft="true" kr-module="marketanalysis" kr-view="coinlist" kr-view-allowed="*">
            <svg class="lnr lnr-heart-pulse"><use xlink:href="#lnr-heart-pulse"></use></svg>
            <span><?php echo $Lang->tr('Market'); ?></span>
          </li>
          <?php endif; ?>
          <?php if($User->_accessAllowedFeature($App, 'blockfolio')): ?>
          <li type="module" kr-modules-hleft="true" kr-module="blockfolio" kr-view="blockfolio">
            <svg class="lnr lnr-layers"><use xlink:href="#lnr-layers"></use></svg>
            <span><?php echo $Lang->tr('Portfolio'); ?></span>
          </li>
          <?php endif; ?>
          <?php if($App->_hiddenThirdpartyActive() && $App->_getLeaderboardEnabled()): ?>
            <li type="side" kr-side="kr-leaderboard" kr-side-part="kr-leaderboard">
              <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
              	 viewBox="0 0 511.999 511.999" style="enable-background:new 0 0 511.999 511.999;" xml:space="preserve">
              <g> <g> <path d="M321.356,63.796c-56.318-28.458-124.159-17.577-168.81,27.075c-51.895,51.894-57.089,134.64-12.081,192.474 c1.591,2.044,3.969,3.114,6.373,3.114c1.732,0,3.477-0.555,4.95-1.702c3.517-2.737,4.149-7.806,1.413-11.323 c-40.019-51.422-35.394-125.002,10.757-171.153c39.71-39.712,100.038-49.39,150.122-24.083c3.976,2.012,8.83,0.414,10.84-3.563 C326.929,70.658,325.335,65.805,321.356,63.796z"/> </g> </g>
              <g> <g> <path d="M385.787,128.239c-2.01-3.977-6.861-5.573-10.841-3.565c-3.977,2.009-5.574,6.861-3.565,10.84 c25.289,50.076,15.606,110.396-24.095,150.096c-46.152,46.15-119.731,50.774-171.153,10.757 c-3.518-2.736-8.586-2.104-11.323,1.412c-2.737,3.517-2.104,8.586,1.413,11.323c26.344,20.502,57.855,30.586,89.266,30.586 c37.547,0,74.952-14.411,103.209-42.668C403.339,252.38,414.225,184.552,385.787,128.239z"/> </g> </g>
              <g> <g> <path d="M364.94,97.508c-1.999-2.262-4.099-4.496-6.242-6.638c-2.143-2.143-4.376-4.243-6.638-6.242 c-3.339-2.952-8.439-2.636-11.388,0.703c-2.953,3.338-2.639,8.437,0.7,11.388c2.015,1.78,4.005,3.652,5.915,5.561 c1.908,1.91,3.78,3.899,5.561,5.914c1.594,1.804,3.816,2.725,6.049,2.725c1.899,0,3.805-0.667,5.34-2.023 C367.577,105.946,367.891,100.847,364.94,97.508z"/> </g> </g>
              <g> <g> <path d="M446.066,208.41c5.38-8.827,5.38-19.791-0.002-28.616l-10.615-17.407c-1.398-2.294-1.939-5.018-1.521-7.67l3.164-20.103 c1.613-10.245-2.596-20.398-10.982-26.499l-16.451-11.966c-2.175-1.583-3.721-3.892-4.354-6.506l-4.791-19.799 c-2.432-10.057-10.191-17.815-20.247-20.249l-19.8-4.792c-2.614-0.633-4.925-2.179-6.506-4.354l-11.968-16.455 c-6.1-8.386-16.254-12.597-26.496-10.983l-20.11,3.164c-2.652,0.417-5.377-0.123-7.669-1.521L270.308,4.036 c-8.826-5.382-19.79-5.381-28.616,0l-17.408,10.615c-2.292,1.399-5.015,1.937-7.67,1.521l-20.104-3.164 c-10.242-1.612-20.397,2.597-26.497,10.983l-11.966,16.451c-1.583,2.175-3.893,3.721-6.508,4.354l-19.799,4.791 c-10.055,2.432-17.815,10.191-20.249,20.247l-4.792,19.8c-0.633,2.614-2.178,4.925-4.354,6.506L85.89,108.108 c-8.386,6.099-12.594,16.251-10.983,26.496l3.164,20.111c0.417,2.653-0.123,5.377-1.521,7.669l-10.617,17.411 c-5.38,8.826-5.38,19.79,0.001,28.615l10.615,17.408c1.398,2.294,1.939,5.018,1.521,7.67l-3.164,20.104 c-1.613,10.244,2.596,20.398,10.982,26.498l16.451,11.966c2.175,1.581,3.721,3.892,4.354,6.506l4.791,19.799 c2.432,10.057,10.191,17.815,20.247,20.249l8.268,2.001L84.045,465.425c-1.189,2.653-0.86,5.739,0.863,8.081 c1.722,2.342,4.569,3.577,7.457,3.231l45.022-5.383l25.936,37.191c1.518,2.175,3.995,3.453,6.616,3.453 c0.251,0,0.505-0.012,0.757-0.035c2.894-0.272,5.419-2.081,6.608-4.732l57.145-127.479l7.241,4.415 c4.413,2.691,9.36,4.036,14.308,4.036c4.948,0,9.895-1.346,14.308-4.036l7.502-4.574l57.217,127.638 c1.189,2.653,3.713,4.46,6.608,4.732c0.253,0.024,0.506,0.036,0.757,0.036c2.621,0,5.099-1.278,6.616-3.453l25.936-37.191 l45.022,5.383c2.886,0.343,5.735-0.889,7.457-3.231c1.722-2.342,2.053-5.428,0.863-8.081l-55.983-124.883l7.957-1.925 c10.055-2.432,17.815-10.191,20.249-20.247l4.792-19.8c0.633-2.614,2.179-4.925,4.354-6.506l16.455-11.968 c8.386-6.099,12.594-16.251,10.983-26.496l-3.164-20.111c-0.417-2.653,0.123-5.377,1.521-7.669L446.066,208.41z M168.408,487.627 l-20.554-29.474c-1.704-2.444-4.618-3.752-7.576-3.396l-35.679,4.266L155.578,345.3c0.932,0.685,1.768,1.506,2.457,2.455 l11.968,16.455c1.137,1.563,2.415,2.98,3.808,4.242l-26.833,59.857c-1.822,4.067-0.004,8.84,4.062,10.663 c1.072,0.481,2.193,0.708,3.295,0.708c3.082,0,6.026-1.776,7.367-4.77l26.738-59.644c2.631,0.361,5.34,0.355,8.059-0.073 l20.11-3.164c1.204-0.189,2.421-0.172,3.606,0.024L168.408,487.627z M407.732,459.021l-35.68-4.264 c-2.961-0.355-5.872,0.952-7.576,3.396l-20.554,29.474l-51.829-115.619c1.087-0.148,2.196-0.148,3.293,0.024l20.103,3.164 c2.829,0.445,5.648,0.431,8.379,0.024l26.758,59.692c1.343,2.995,4.285,4.77,7.367,4.77c1.103,0,2.224-0.227,3.295-0.708 c4.066-1.822,5.885-6.597,4.062-10.663l-26.935-60.085c1.299-1.206,2.5-2.541,3.571-4.013l11.966-16.451 c0.754-1.037,1.678-1.926,2.716-2.645L407.732,459.021z M432.287,200.008l-10.617,17.411c-3.385,5.554-4.695,12.152-3.684,18.578 l3.164,20.11c0.665,4.229-1.073,8.42-4.534,10.938l-16.456,11.969c-5.267,3.832-9.013,9.429-10.545,15.76l-4.792,19.8 c-1.005,4.151-4.207,7.354-8.359,8.359l-19.8,4.791c-6.332,1.532-11.93,5.278-15.762,10.546l-11.966,16.451 c-2.518,3.463-6.708,5.199-10.939,4.533l-20.104-3.164c-6.427-1.011-13.026,0.297-18.581,3.684l-17.407,10.615 c-3.643,2.22-8.171,2.22-11.812,0l-17.411-10.616c-4.33-2.641-9.294-4.017-14.317-4.017c-1.42,0-2.847,0.11-4.262,0.333 l-20.111,3.164c-4.23,0.666-8.42-1.073-10.938-4.534l-11.969-16.455c-3.832-5.267-9.429-9.013-15.76-10.545l-19.8-4.792 c-4.151-1.005-7.354-4.207-8.359-8.359l-4.791-19.799c-1.532-6.332-5.277-11.931-10.546-15.763l-16.45-11.967 c-3.462-2.518-5.199-6.71-4.533-10.939l3.164-20.104c1.011-6.427-0.297-13.025-3.684-18.58l-10.615-17.409 c-2.221-3.643-2.221-8.17,0-11.812l10.617-17.411c3.386-5.554,4.695-12.152,3.684-18.578l-3.164-20.11 c-0.665-4.229,1.073-8.42,4.534-10.938l16.456-11.969c5.267-3.832,9.013-9.429,10.545-15.76l4.792-19.8 c1.005-4.151,4.207-7.354,8.359-8.359l19.8-4.791c6.332-1.532,11.93-5.278,15.761-10.546l11.966-16.451 c2.518-3.462,6.708-5.201,10.939-4.534l20.104,3.164c6.428,1.013,13.027-0.297,18.581-3.684l17.407-10.615 c3.643-2.22,8.171-2.22,11.812,0l17.411,10.616c5.554,3.388,12.154,4.697,18.579,3.685l20.111-3.164 c4.228-0.663,8.42,1.073,10.938,4.534l11.969,16.455c3.832,5.267,9.429,9.013,15.76,10.545l19.8,4.792 c4.151,1.005,7.354,4.207,8.359,8.359l4.791,19.799c1.532,6.332,5.277,11.931,10.546,15.763l16.451,11.966 c3.462,2.518,5.199,6.71,4.533,10.939l-3.164,20.104c-1.011,6.427,0.297,13.025,3.684,18.58l10.615,17.409 C434.509,191.838,434.509,196.365,432.287,200.008z"/> </g> </g>
              <g> <g> <path d="M350.416,168.997c-1.778-5.474-6.433-9.296-12.149-9.974l-48.551-5.757l-20.478-44.395 c-2.411-5.226-7.484-8.473-13.24-8.473c-5.755,0-10.829,3.247-13.24,8.473l-20.478,44.396l-48.551,5.757 c-5.716,0.678-10.372,4.5-12.149,9.974c-1.778,5.471-0.258,11.3,3.966,15.207l35.895,33.195l-9.528,47.954 c-1.121,5.646,1.075,11.254,5.732,14.636c2.569,1.866,5.55,2.811,8.55,2.811c2.439,0,4.889-0.624,7.14-1.884l42.662-23.879 l42.662,23.879c5.023,2.812,11.034,2.457,15.691-0.926c4.657-3.383,6.853-8.992,5.731-14.637l-9.528-47.953l35.895-33.195 C350.674,180.299,352.194,174.471,350.416,168.997z M296.282,208.62c-2.06,1.905-2.982,4.744-2.436,7.496l9.75,49.071 l-43.658-24.437c-1.224-0.685-2.582-1.028-3.941-1.028c-1.359,0-2.716,0.342-3.941,1.028l-43.658,24.437l9.75-49.071 c0.548-2.753-0.375-5.591-2.436-7.496L178.98,174.65l49.686-5.891c2.786-0.33,5.201-2.085,6.377-4.632l20.955-45.431 l20.955,45.431c1.176,2.547,3.59,4.302,6.377,4.632l49.684,5.891L296.282,208.62z"/> </g> </g>
              </svg>
              <span><?php echo $Lang->tr('Leader board'); ?></span>
            </li>
          <?php endif; ?>
          <li type="side" kr-side="kr-calculator" kr-side-part="kr-calculator">
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 512 512"> <g> <path d="m480.1,11v79.5h-144.8c-4.9-28.3-28.6-49-56.4-49-28.6,0-51.5,21.4-56.4,49h-43.5c-4.7-28.3-27.7-49-56.4-49-28.6,0-51.5,21.4-56.4,49h-34.3v-79.5h-20.9v490h20.9v-79.5h34.3c4.9,28.3 28.6,49 56.4,49 28.6,0 51.5-21.4 56.4-49h28c4.9,28.3 28.6,49 56.4,49 27.7,0 50.5-21.4 56.1-49h160.8v79.5h20.7v-490h-20.9zm-201.2,53.4c20.9,0 36.5,16.3 36.5,38.1 0,20.7-16.7,38.1-36.5,38.1s-36.5-17.4-36.5-38.1 16.7-38.1 36.5-38.1zm-156.3,0c20.9,0 36.5,16.3 36.5,38.1 0,20.7-16.7,38.1-36.5,38.1-19.8,0-36.5-17.4-36.5-38.1s16.6-38.1 36.5-38.1zm-56.4,47.9c4.9,28.3 28.6,49 56.4,49 28.7,0 51.7-20.7 56.4-49h43.6c4.9,28.3 28.6,49 56.4,49 28.7,0 51.7-20.7 56.4-49h144.8v132.8h-28.1c-4.9-28.3-28.6-49-56.4-49-28.7,0-51.7,20.7-56.4,49h-66.5c-4.7-28.3-27.7-49-56.4-49s-51.7,20.7-56.4,49h-128.1v-132.8h34.3zm366,143.7c0,20.7-16.7,38.1-36.5,38.1s-36.5-17.4-36.5-38.1 16.7-38.1 36.5-38.1c20.9,0 36.5,17.4 36.5,38.1zm-179.3,0c0,20.7-16.7,38.1-36.5,38.1s-36.5-17.4-36.5-38.1 16.7-38.1 36.5-38.1 36.5,17.4 36.5,38.1zm-130.3,192.7c-19.8,0-36.5-17.4-36.5-38.1 0-20.7 16.7-38.1 36.5-38.1 20.9,0 36.5,17.4 36.5,38.1-0.1,20.7-16.7,38.1-36.5,38.1zm140.7,0c-19.8,0-36.5-17.4-36.5-38.1 0-20.7 16.7-38.1 36.5-38.1s36.5,17.4 36.5,38.1c0,20.7-16.7,38.1-36.5,38.1zm56.4-49c-4.7-28.3-27.7-49-56.4-49-28.6,0-51.5,21.4-56.4,49h-27.9c-4.7-28.3-27.7-49-56.4-49-28.6,0-51.5,21.4-56.4,49h-34.3v-132.8h128.1c4.9,28.3 28.6,49 56.4,49s51.5-20.7 56.4-49h66.5c4.9,28.3 28.6,49 56.4,49s51.5-20.7 56.4-49h28.1v132.8h-160.5z"/> </g> </svg>
            <span><?php echo $Lang->tr('Calc.'); ?></span>
          </li>
          <li class="kr-watching-wdsf">
            <svg class="lnr lnr-eye"><use xlink:href="#lnr-eye"></use></svg>
            <span><?php echo $Lang->tr('Watching'); ?></span>
          </li>
          <?php if($User->_accessAllowedFeature($App, 'news')): ?>
            <li type="side" kr-side="kr-infosside" kr-side-part="kr-newsside">
              <svg class="lnr lnr-earth"><use xlink:href="#lnr-earth"></use></svg>
              <span><?php echo $Lang->tr('News'); ?></span>
            </li>
          <?php endif; ?>
          <?php
          foreach ($App->_getAdditionalPages() as $keyAddtionalPage => $infosAddtionalPage) {
            ?>
            <li type="module" kr-modules-hleft="true" kr-module="dashboard" kr-view="custompage" kr-args='{"page":"<?php echo App::encrypt_decrypt('encrypt', $infosAddtionalPage['id_additional_pages']); ?>"}'>
              <?php if(strlen($infosAddtionalPage['icon_additional_pages']) > 0): ?>
                <svg class="lnr <?php echo $infosAddtionalPage['icon_additional_pages']; ?>"><use xlink:href="#<?php echo $infosAddtionalPage['icon_additional_pages']; ?>"></use></svg>
              <?php elseif(strlen($infosAddtionalPage['svg_additional_pages']) > 0): ?>
                <?php echo $infosAddtionalPage['svg_additional_pages']; ?>
              <?php endif ?>
              <span><?php echo $Lang->tr($infosAddtionalPage['name_additional_pages']); ?></span>
            </li>
            <?php
          }
          ?>
          <?php
          if($User->_isManager()):
            $Manager = new Manager($App);
            $ManagerNotificationNumber = $Manager->_getNumberManagerNotification();
          ?>
            <li type="module" kr-modules-hleft="true" kr-module="manager" kr-view="statistics" kr-view-allowed="*">
              <div class="kr-leftnav-bubble kr-leftnav-bubble-manager-notification" style="<?php echo ($ManagerNotificationNumber <= 0 ? 'display:none' : ''); ?>">
                <?php echo $ManagerNotificationNumber; ?>
              </div>
              <svg class="lnr lnr-pie-chart"><use xlink:href="#lnr-pie-chart"></use></svg>
              <span><?php echo $Lang->tr('Manager'); ?></span>
            </li>
          <?php endif; ?>
          <?php if($User->_isAdmin()): ?>
            <li type="module" kr-modules-hleft="true" kr-module="admin" kr-view="dashboard" kr-view-allowed="*">
              <svg class="lnr lnr-cog"><use xlink:href="#lnr-cog"></use></svg>
              <span><?php echo $Lang->tr('Admin'); ?></span>
            </li>
          <?php endif; ?>
        </ul>
      </nav>

      <section class="kr-rankingside">
        <header>
          <div>
            <span><?php echo $Lang->tr('Leader board'); ?></span>
            <svg onclick="toggleLeaderBoard();" class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
          </div>
        </header>
        <div class="spinner"></div>
      </section>

      <section class="kr-orderbookside">
        <header>
          <div>
            <span><?php echo $Lang->tr('Order book'); ?></span>
            <svg onclick="toggleOrderbook();" class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
          </div>
        </header>
        <div class="spinner"></div>
      </section>
      <section class="kr-orderinfoside">
        <div class="spinner"></div>
      </section>

      <section class="kr-calculatorside">
        <header>
          <span class="kr-mono"><?php echo $Lang->tr('Converter'); ?></span>
          <div onclick="toggleCalculator();">
            <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
          </div>
        </header>
        <section class="kr-calculatorside-lc">
          <?php $s = 0; foreach ($Calculator->_getListCurrencyUser($User) as $Symbol => $infosSymbol) { $s++;
            ?>
            <section class="<?php echo ($s == 1 ? 'kr-calculatorside-lcsc' : ''); ?>" symbol="<?php echo $Symbol; ?>">
              <div>
                <?php if(file_exists('assets/img/icons/crypto/'.$Symbol.'.svg')): ?>
                  <img src="<?php echo APP_URL; ?>/assets/img/icons/crypto/<?php echo $Symbol; ?>.svg" alt="">
                <?php endif; ?>
                <label><?php echo $infosSymbol['name']; ?></label>
              </div>
              <div>
                <input type="text" name="" value="<?php echo ($s == 1 ? '1' : '0'); ?>">
                <span><?php echo $Symbol; ?></span>
              </div>
            </section>
          <?php } ?>
        </section>
        <div>
          <div class="add-new-calculator"><span>+</span>
            <div class="kr-dash-pan-cry-select kr-dash-add-calculator" graph="new-graph">
              <header>
                <input type="text" name="" graph="new-graph" placeholder="<?php echo $Lang->tr('Search by name or symbol'); ?>" value="">
              </header>
              <ul class="kr-dash-pan-cry-select-lst">
              </ul>
            </div>
          </div>
        </div>
      </section>
      <section class="kr-newsside">
        <header>
          <div>
            <span><?php echo $Lang->tr('News'); ?></span>
            <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
          </div>
          <ul>
            <li kr-news-tab="News" kr-news-tab-n="<?php echo $Lang->tr('News'); ?>"><svg class="lnr lnr-earth"><use xlink:href="#lnr-earth"></use></svg></li>
            <li kr-news-tab="Social" kr-news-tab-n="<?php echo $Lang->tr('Social'); ?>">
              <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
              	 viewBox="0 0 310 310" style="enable-background:new 0 0 310 310;" xml:space="preserve">
              <g id="XMLID_826_">
              	<path id="XMLID_827_" d="M302.973,57.388c-4.87,2.16-9.877,3.983-14.993,5.463c6.057-6.85,10.675-14.91,13.494-23.73
              		c0.632-1.977-0.023-4.141-1.648-5.434c-1.623-1.294-3.878-1.449-5.665-0.39c-10.865,6.444-22.587,11.075-34.878,13.783
              		c-12.381-12.098-29.197-18.983-46.581-18.983c-36.695,0-66.549,29.853-66.549,66.547c0,2.89,0.183,5.764,0.545,8.598
              		C101.163,99.244,58.83,76.863,29.76,41.204c-1.036-1.271-2.632-1.956-4.266-1.825c-1.635,0.128-3.104,1.05-3.93,2.467
              		c-5.896,10.117-9.013,21.688-9.013,33.461c0,16.035,5.725,31.249,15.838,43.137c-3.075-1.065-6.059-2.396-8.907-3.977
              		c-1.529-0.851-3.395-0.838-4.914,0.033c-1.52,0.871-2.473,2.473-2.513,4.224c-0.007,0.295-0.007,0.59-0.007,0.889
              		c0,23.935,12.882,45.484,32.577,57.229c-1.692-0.169-3.383-0.414-5.063-0.735c-1.732-0.331-3.513,0.276-4.681,1.597
              		c-1.17,1.32-1.557,3.16-1.018,4.84c7.29,22.76,26.059,39.501,48.749,44.605c-18.819,11.787-40.34,17.961-62.932,17.961
              		c-4.714,0-9.455-0.277-14.095-0.826c-2.305-0.274-4.509,1.087-5.294,3.279c-0.785,2.193,0.047,4.638,2.008,5.895
              		c29.023,18.609,62.582,28.445,97.047,28.445c67.754,0,110.139-31.95,133.764-58.753c29.46-33.421,46.356-77.658,46.356-121.367
              		c0-1.826-0.028-3.67-0.084-5.508c11.623-8.757,21.63-19.355,29.773-31.536c1.237-1.85,1.103-4.295-0.33-5.998
              		C307.394,57.037,305.009,56.486,302.973,57.388z"/>
              </g>
              </svg>
            </li>
            <?php if($App->_getCalendarEnable()): ?>
              <li kr-news-tab="Calendar" kr-news-tab-n="<?php echo $Lang->tr('Calendar'); ?>"><svg class="lnr lnr-calendar-full"><use xlink:href="#lnr-calendar-full"></use></svg></li>
            <?php endif; ?>
          </ul>
        </header>
        <section class="kr-newsinfos-content">
          <div class="spinner"></div>
        </section>
      </section>
      <section class="kr-calendareventitem">
        <div class="spinner"></div>
      </section>

      <section class="kr-leftside">
        <div class="kr-leftside-hide-controller" onclick="_toogleLeftSide();">
          <svg class="lnr lnr-chevron-left"><use xlink:href="#lnr-chevron-left"></use></svg>
        </div>
        <div class="kr-wtchl">
          <header>
            <span><?php echo $Lang->tr('Watchlist'); ?></span>
            <!-- <div>
              <svg class="lnr lnr-chevron-left"><use xlink:href="#lnr-chevron-left"></use></svg>
            </div> -->
            <div class="kr-search-field-content" kr-search-callback="addWatchingListSearch">
              <input type="text" class="kr-search-field" placeholder="BTCETH ..."  name="" value="">
            </div>
          </header>
          <nav>
            <ul id="kr-watchinglistbyid">
              <li><?php echo $Lang->tr('Symbol'); ?></li>
              <li><?php echo $Lang->tr('Last'); ?></li>
              <li><?php echo $Lang->tr('Chng (%)'); ?></li>
            </ul>
          </nav>
          <ul class="kr-wtchl-lst">
          </ul>
        </div>
        <div class="kr-infoscurrencylf">
        </div>
      </section>
      <section class="kr-dashboard">

      </section>
      <?php if(!$App->_chatIsDisabled()): ?>
        <section class="kr-chat-right <?php if(!$User->_barChatShow()) echo 'kr-chat-right-hidded'; ?>">
          <div class="kr-chat-right-tgglebtn">
            <?php if(!$User->_barChatShow()): ?>
              <svg class="lnr lnr-chevron-left"><use xlink:href="#lnr-chevron-left"></use></svg>
            <?php else: ?>
              <svg class="lnr lnr-chevron-right"><use xlink:href="#lnr-chevron-right"></use></svg>
            <?php endif; ?>
          </div>
          <ul>
            <li kr-chat-lastmsg="99999999999999999999" class="kr-chat-right-openchat">
              <svg viewBox="0 -26 512 512" xmlns="http://www.w3.org/2000/svg"><path d="m256 100c-5.519531 0-10 4.480469-10 10s4.480469 10 10 10 10-4.480469 10-10-4.480469-10-10-10zm0 0"/><path d="m90 280c5.519531 0 10-4.480469 10-10s-4.480469-10-10-10-10 4.480469-10 10 4.480469 10 10 10zm0 0"/><path d="m336 0c-90.027344 0-163.917969 62.070312-169.632812 140.253906-85.738282 4.300782-166.367188 66.125-166.367188 149.746094 0 34.945312 13.828125 68.804688 39 95.632812 4.980469 20.53125-1.066406 42.292969-16.070312 57.296876-2.859376 2.859374-3.714844 7.160156-2.167969 10.898437 1.546875 3.734375 5.191406 6.171875 9.238281 6.171875 28.519531 0 56.003906-11.183594 76.425781-30.890625 19.894531 6.78125 45.851563 10.890625 69.574219 10.890625 90.015625 0 163.898438-62.054688 169.628906-140.222656 20.9375-.929688 42.714844-4.796875 59.945313-10.667969 20.421875 19.707031 47.90625 30.890625 76.425781 30.890625 4.046875 0 7.691406-2.4375 9.238281-6.171875 1.546875-3.738281.691407-8.039063-2.167969-10.898437-15.003906-15.003907-21.050781-36.765626-16.070312-57.296876 25.171875-26.828124 39-60.6875 39-95.632812 0-86.886719-86.839844-150-176-150zm-160 420c-23.601562 0-50.496094-4.632812-68.511719-11.800781-3.859375-1.539063-8.269531-.527344-11.078125 2.539062-12.074218 13.199219-27.773437 22.402344-44.878906 26.632813 9.425781-18.058594 11.832031-39.347656 6.097656-59.519532-.453125-1.589843-1.292968-3.042968-2.445312-4.226562-22.6875-23.367188-35.183594-53.066406-35.183594-83.625 0-70.46875 71.4375-130 156-130 79.851562 0 150 55.527344 150 130 0 71.683594-67.289062 130-150 130zm280.816406-186.375c-1.152344 1.1875-1.992187 2.640625-2.445312 4.226562-5.734375 20.171876-3.328125 41.460938 6.097656 59.519532-17.105469-4.226563-32.804688-13.433594-44.878906-26.632813-2.808594-3.0625-7.21875-4.078125-11.078125-2.539062-15.613281 6.210937-37.886719 10.511719-58.914063 11.550781-2.921875-37.816406-21.785156-73.359375-54.035156-99.75h130.4375c5.523438 0 10-4.476562 10-10s-4.476562-10-10-10h-161.160156c-22.699219-11.554688-48.1875-18.292969-74.421875-19.707031 5.746093-67.164063 70.640625-120.292969 149.582031-120.292969 84.5625 0 156 59.53125 156 130 0 30.558594-12.496094 60.257812-35.183594 83.625zm0 0"/><path d="m256 260h-126c-5.523438 0-10 4.476562-10 10s4.476562 10 10 10h126c5.523438 0 10-4.476562 10-10s-4.476562-10-10-10zm0 0"/><path d="m256 320h-166c-5.523438 0-10 4.476562-10 10s4.476562 10 10 10h166c5.523438 0 10-4.476562 10-10s-4.476562-10-10-10zm0 0"/><path d="m422 100h-126c-5.523438 0-10 4.476562-10 10s4.476562 10 10 10h126c5.523438 0 10-4.476562 10-10s-4.476562-10-10-10zm0 0"/></svg>
            </li>
            <?php

            $Chat = new Chat($User);
            foreach ($Chat->_getListRoom() as $Room) {
  ?>
            <li kr-chat-lastmsg="<?php echo $Room->_getLastMsgSendTime(); ?>" kr-chat-rid="<?php echo $Room->_getRoomID(true); ?>" class="" style="background-color:<?php echo $Room->_getRoomColor(); ?>; background-image:url('<?php echo $Room->_getRoomPicture(); ?>')">
              <div class="kr-chat-status" style="display:none;"></div>
            </li>
  <?php
            } ?>
          </ul>
        </section>
      <?php endif; ?>
    </section>
    <footer>
      <div>
        <ul class="kr-charge-status">
          <?php
          if($User->_isAdmin()):
            ?>
            <li class="kr-charge-tag kr-charge-tag-blue">
              <svg class="lnr lnr-diamond"><use xlink:href="#lnr-diamond"></use></svg><span><?php echo $Lang->tr('Admin'); ?></span>
            </li>
            <?php
          elseif($User->_isManager()):
            ?>
            <li class="kr-charge-tag kr-charge-tag-blue">
              <svg class="lnr lnr-star"><use xlink:href="#lnr-star"></use></svg><span><?php echo $Lang->tr('Manager'); ?></span>
            </li>
            <?php
          elseif($Charge->_isTrial() && !$Charge->_activeAbo() && $App->_subscriptionEnabled()):
          ?>
            <li class="kr-charge-tag kr-charge-tag-orange">
              <span><?php echo $Lang->tr('Trial version'); ?>, <b><?php echo $Charge->_getTrialNumberDay().' '.$Lang->tr('day').($Charge->_getTrialNumberDay() > 1 ? 's' : '').' '.$Lang->tr('left'); ?></b></span>
            </li>
          <?php elseif($App->_subscriptionEnabled()): ?>
            <li class="kr-charge-tag kr-charge-tag-green">
              <svg class="lnr lnr-diamond"><use xlink:href="#lnr-diamond"></use></svg><span><?php echo $Lang->tr('Premium'); ?>, <b><?php echo $Charge->_getTimeRes().' '.$Lang->tr('day').($Charge->_getTimeRes() > 1 ? 's' : '').' '.$Lang->tr('left'); ?></b></span>
            </li>
          <?php endif; ?>
          <li class="kr-toggle-white">
            <span><?php echo $Lang->tr('Dark'); ?></span>
            <div class="kr-toggle-theme-white <?php echo ($User->_whiteMode() ? 'kr-white-theme' : ''); ?>">
              <div>

              </div>
            </div>
            <span><?php echo $Lang->tr('White'); ?></span>
          </li>
        </ul>
        <?php if($App->_getDonationEnabled()): ?>
          <div class="kr-donation" onclick="_showDonationPopup();">
            <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="510px" height="510px" viewBox="0 0 510 510" style="enable-background:new 0 0 510 510;" xml:space="preserve"> <g> <g id="favorite"> <path d="M255,489.6l-35.7-35.7C86.7,336.6,0,257.55,0,160.65C0,81.6,61.2,20.4,140.25,20.4c43.35,0,86.7,20.4,114.75,53.55 C283.05,40.8,326.4,20.4,369.75,20.4C448.8,20.4,510,81.6,510,160.65c0,96.9-86.7,175.95-219.3,293.25L255,489.6z"/> </g> </g> </svg>
            <span><?php echo $Lang->tr($App->_getDonationText()); ?></span>
          </div>
        <?php endif; ?>
      </div>
      <div class="kr-footer-right-side">
        <div class="kr-footer-contact" onclick="_showContactPopup();">
          <div>
            <svg class="lnr lnr-bubble"><use xlink:href="#lnr-bubble"></use></svg>
          </div>
          <span><?php echo $Lang->tr('Contact us'); ?></span>
        </div>
        <div class="kr-current-time" mlist="<?php echo join(',', $App->_getMonthName($Lang)); ?>" dlist="<?php echo join(',', $App->_getDayName(true, $Lang)); ?>">
          <span></span>
        </div>
      </div>
    </footer>

  <?php endif; ?>

  </body>
  <script src="<?php echo APP_URL; ?>/assets/bower/jquery/dist/jquery.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/assets/bower/jquery-ui/jquery-ui.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>

  <script src="https://cdn.linearicons.com/free/1.0.0/svgembedder.min.js"></script>

  <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>

  <!-- Chart.JS -->
  <script src="<?php echo APP_URL; ?>/assets/bower/chart.js/dist/Chart.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/assets/bower/jquery.number.js/jquery.number.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/assets/bower/jquery.animateNumber.js/jquery.animateNumber.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>

  <script src="<?php echo APP_URL; ?>/assets/bower/chosen/chosen.jquery.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/assets/bower/dropzone/dist/min/dropzone.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>

  <!-- Technical indicators library -->
  <script src="<?php echo APP_URL; ?>/assets/node_modules/babel-polyfill/browser.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/assets/bower/technicalindicators/dist/browser.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>

  <script src="<?php echo APP_URL; ?>/assets/bower/sly/sly.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.0.4/socket.io.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>

  <!-- Intro -->
  <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/bower/tether-shepherd/dist/css/shepherd-theme-arrows.css?v=<?php echo App::_getVersion(); ?>" />
  <script src="<?php echo APP_URL; ?>/assets/bower/tether-shepherd/dist/js/tether.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/assets/bower/tether-shepherd/dist/js/shepherd.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>

  <!-- Stripe js -->
  <script type="text/javascript" src="https://js.stripe.com/v2/"></script>

  <script src="<?php echo APP_URL; ?>/assets/bower/air-datepicker/dist/js/datepicker.min.js?v=<?php echo App::_getVersion(); ?>"></script>
  <script src="<?php echo APP_URL; ?>/assets/bower/air-datepicker/dist/js/i18n/datepicker.en.js?v=<?php echo App::_getVersion(); ?>"></script>

  <!-- ionRangeSlider -->
  <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/bower/ion.rangeSlider/css/ion.rangeSlider.css?v=<?php echo App::_getVersion(); ?>" />
  <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/bower/ion.rangeSlider/css/ion.rangeSlider.skinFlat.css?v=<?php echo App::_getVersion(); ?>" />
  <script src="<?php echo APP_URL; ?>/assets/bower/ion.rangeSlider/js/ion.rangeSlider.min.js?v=<?php echo App::_getVersion(); ?>"></script>

  <script src="<?php echo APP_URL; ?>/assets/bower/jquery-palette-color-picker/src/palette-color-picker.min.js?v=<?php echo App::_getVersion(); ?>"></script>

  <!-- highlight -->
  <script src="<?php echo APP_URL; ?>/assets/bower/jsdiff/diff.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/assets/bower/mark.js/dist/jquery.mark.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>

  <!-- Lightbox -->
  <script src="<?php echo APP_URL; ?>/assets/bower/lightbox2/dist/js/lightbox.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>
  <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/bower/lightbox2/dist/css/lightbox.min.css?v=<?php echo App::_getVersion(); ?>" />


  <script src="<?php echo APP_URL; ?>/assets/bower/sweetalert2/dist/sweetalert2.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>
  <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/bower/sweetalert2/dist/sweetalert2.css?v=<?php echo App::_getVersion(); ?>" />

  <!-- Daterange picker -->

  <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js?v=<?php echo App::_getVersion(); ?>"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js?v=<?php echo App::_getVersion(); ?>"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css?v=<?php echo App::_getVersion(); ?>" />

  <!-- Clipboard -->
  <script src="<?php echo APP_URL; ?>/assets/bower/clipboard/dist/clipboard.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>

  <!-- Fast select -->
  <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/bower/selectize/dist/css/selectize.css?v=<?php echo App::_getVersion(); ?>">
  <script src="<?php echo APP_URL; ?>/assets/bower/selectize/dist/js/standalone/selectize.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>

  <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/bower/tippyjs/dist/tippy.css?v=<?php echo App::_getVersion(); ?>">
  <script src="<?php echo APP_URL; ?>/assets/bower/tippyjs/dist/tippy.min.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>

  <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/bower/zoombox/zoombox.css?v=<?php echo App::_getVersion(); ?>">
  <script src="<?php echo APP_URL; ?>/assets/bower/zoombox/zoombox.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>

  <!-- PACE -->
  <!-- <script data-pace-options='{ "ajax": false }' src="assets/bower/PACE/pace.js"></script> -->


  <!-- Modules -->

  <?php echo $App->_getAssetsList('js'); ?>

  <script src="<?php echo APP_URL; ?>/assets/js/pannel.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/assets/js/notifications.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/assets/js/intro.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/assets/js/highlight.js?v=<?php echo App::_getVersion(); ?>" charset="utf-8"></script>

  <?php if(true): ?>
    <script type="text/javascript">
      $(document).ready(function(){
        setTimeout(function(){
          $('.kr-top-graphlist-item').trigger('click');
        }, 3000);
      });
    </script>
  <?php endif; ?>

  <!-- Google Analytics -->
  <?php echo $App->_getGoogleAnalytics(); ?>

  <?php $Charge->_checkPaymentResult();
  if(!is_null($Balance)) $Balance->_checkPaymentResult();
  if(isset($_GET['c']) && isset($_GET['m']) && isset($_GET['t']) && (time() - $_GET['t']) < 20) echo '<script>showAlert("Ooops", "'.htmlspecialchars(base64_decode($_GET['m'])).'", "error");</script>'; ?>
</html>
