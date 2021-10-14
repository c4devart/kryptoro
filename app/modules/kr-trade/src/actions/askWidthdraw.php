<?php

/**
 * Load data balance
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoNotification.php";
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
        throw new Exception("Error : User is not logged", 1);
    }

    $Lang = new Lang($User->_getLang(), $App);

    if(!$App->_hiddenThirdpartyActive()) throw new Exception("Permission denied", 1);

    $Balance = new Balance($User, $App, 'real');
    $symbolFetched = "";
    $BalanceList = $Balance->_getBalanceListResum();
    if(count($BalanceList) > 0){
      $symbolFetched = array_keys($BalanceList)[0];
      if(!empty($_GET) && isset($_GET['symbol']) && array_key_exists($_GET['symbol'], $BalanceList)) $symbolFetched = $_GET['symbol'];
    }


    if(!is_null($App->_getWidthdrawCryptocurrencyAvailable())){
      $Widthdraw = new Widthdraw($User);

      $IsRealMoney = $Balance->_symbolIsMoney($symbolFetched);

      if(in_array($symbolFetched, $App->_getWidthdrawCryptocurrencyAvailable()) && !$IsRealMoney) $listWithdrawAvailable = ['banktransfert', 'cryptocurrencies'];
      else $listWithdrawAvailable = ['cryptocurrencies'];

      if($IsRealMoney) $listWithdrawAvailable = ['paypal', 'banktransfert'];

      $PaymentMethodList = $Widthdraw->_getWidthdrawMethod($listWithdrawAvailable);
    }


} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>

<section class="kr-balance-credit">
  <section style="height:480px;">
    <header>
      <span><?php echo $Lang->tr('Ask a widthdraw'); ?></span>
      <div onclick="_closeCreditForm();"> <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg> </div>
    </header>
    <div class="spinner" style="display:none;"> </div>
    <?php if(array_sum($BalanceList) > 0): ?>
    <section class="kr-balance-checkemail" style="display:none;">
      <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"> <g> <g> <path d="M312.461,332.734H199.539c-8.511,0-15.434,6.923-15.434,15.434v34.634c0,8.511,6.923,15.435,15.434,15.435h112.923 c8.511,0,15.435-6.923,15.435-15.435v-34.634C327.895,339.658,320.972,332.734,312.461,332.734z M308.051,378.393H203.948v-25.814 h104.103V378.393z"/> </g> </g> <g> <g> <path d="M506.976,246.958l0.159-0.08L432.73,99.774c-6.015-11.89-18.025-19.275-31.346-19.275h-14.141V66.824 c0-5.48-4.442-9.922-9.922-9.922H134.68c-5.48,0-9.922,4.442-9.922,9.922v13.675h-14.141c-13.321,0-25.331,7.385-31.346,19.275 L4.865,246.878l0.159,0.08C1.837,252.207,0,258.363,0,264.939v155.409c0,19.162,15.59,34.751,34.752,34.751h442.497 c19.162,0,34.751-15.59,34.751-34.751V264.939C512,258.363,510.163,252.207,506.976,246.958z M387.242,102.548h14.141 c4.959,0,9.43,2.751,11.671,7.179l60.93,120.462h-41.431v-37.066c0-5.48-4.442-9.922-9.922-9.922h-12.275v-53.227 c0-5.48-4.442-9.922-9.922-9.922h-13.192V102.548z M412.71,203.044v27.144h-52.359c-8.984,0-17.174,5.293-20.865,13.482 l-14.296,31.71c-0.136,0.299-0.435,0.493-0.764,0.493H187.575c-0.329,0-0.628-0.194-0.764-0.494l-14.295-31.708 c-3.692-8.19-11.882-13.483-20.866-13.483H99.291v-27.144H412.71z M144.602,76.746h222.796v43.305H144.602V76.746z M390.512,139.895v43.305H121.488v-43.305H390.512z M98.946,109.727c2.24-4.429,6.712-7.179,11.671-7.179h14.141v17.503h-13.192 c-5.48,0-9.922,4.442-9.922,9.922v53.227H89.369c-5.48,0-9.922,4.442-9.922,9.922v37.066H38.016L98.946,109.727z M477.249,433.049 H34.752c-7.004,0-12.703-5.699-12.703-12.701V264.939c0-7.003,5.698-12.701,12.703-12.701H151.65c0.328,0,0.629,0.194,0.765,0.495 l14.295,31.708c3.692,8.19,11.881,13.481,20.865,13.481h136.85c8.984,0,17.174-5.292,20.865-13.48l14.296-31.709v-0.001 c0.136-0.3,0.435-0.494,0.764-0.494h116.898c7.004,0,12.701,5.699,12.701,12.701v155.409h0.001 C489.951,427.352,484.253,433.049,477.249,433.049z"/> </g> </g> </svg>

      <h2><?php echo $Lang->tr('You will receive a email confirmation'); ?></h2>
      <span><?php echo $Lang->tr('Please follow this step for complete your withdrawal request.'); ?></span>
    </section>
    <section class="kr-balance-approvecontract" style="display:none;">

      <h2><?php echo $Lang->tr('Cryptocurrency withdraw agreement'); ?></h2>
      <div>
        <?php echo nl2br($App->_getWidthdrawMessage()); ?>
      </div>
      <footer>
        <input type="button" onclick="_declineWithdrawContract('<?php echo $symbolFetched; ?>');" class="btn btn-big btn-autowidth btn-red" name="" value="<?php echo $Lang->tr('Decline'); ?>">
        <input type="button" onclick="_agreeWithdrawContract();" class="btn btn-big btn-autowidth btn-green" name="" value="<?php echo $Lang->tr('Agree'); ?>">
      </footer>

    </section>
  <?php endif; ?>
    <?php if(array_sum($BalanceList) == 0): ?>
      <section class="kr-balance-withdraw-empty">

        <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 339.452 339.452" style="enable-background:new 0 0 339.452 339.452;" xml:space="preserve"> <g> <g> <path d="M325.452,280.557h-4.72c0.048-0.666,0.048-1.334,0-2v-252c0-7.732-6.268-14-14-14h-136.8c-7.732,0-14,6.268-14,14v104 c-13.025,3.16-24.423,11.024-32,22.08c-9.292,13.387-12.164,30.208-7.84,45.92c5.76,22.08,6.48,51.36-5.44,63.52 c-6.863,6.083-16.517,7.897-25.12,4.72c-9.254-1.465-17.155-7.474-21.04-16c-12.72-29.36,19.04-84.88,19.12-85.12 c1.92-2.88,46.16-71.6,0.96-112c-1.84-1.92-45.44-48-79.12-43.52c-3.424,0.442-5.842,3.576-5.4,7c0.442,3.424,3.576,5.842,7,5.4 c8.094-0.486,16.162,1.316,23.28,5.2l-8.96,10.8c-4.856,6.017-3.914,14.831,2.103,19.687c0.098,0.079,0.197,0.157,0.297,0.233 l30.08,24c2.473,1.982,5.551,3.055,8.72,3.04h1.2c3.688-0.416,7.058-2.288,9.36-5.2l8.72-10.96c27.04,34.56-8,89.6-8.88,90.48 c-1.44,2.48-35.04,61.28-20,96c5.56,12.056,16.739,20.568,29.84,22.72c4.035,0.978,8.169,1.488,12.32,1.52 c8.97,0.27,17.654-3.175,24-9.52c16-16.72,14.72-50.8,8.48-74.96c-6.637-22.24,5.95-45.662,28.16-52.4v135.36 c-0.048,0.666-0.048,1.334,0,2h-4c-7.73-0.177-14.14,5.946-14.316,13.676c-0.003,0.108-0.004,0.216-0.004,0.324v21.04 c-0.002,7.732,6.264,14.002,13.996,14.004c0.108,0,0.216-0.001,0.324-0.004h173.68c7.732,0,14-6.268,14-14v-21.04 C339.452,286.826,333.184,280.557,325.452,280.557z M72.652,61.917l-8.88,10.96c-0.339,0.412-0.829,0.671-1.36,0.72 c-0.514,0.067-1.034-0.078-1.44-0.4l-30.08-24c-0.762-0.778-0.762-2.022,0-2.8l8.88-11.04c0.339-0.412,0.829-0.671,1.36-0.72 c0.514-0.067,1.034,0.078,1.44,0.4l30.08,24c0.419,0.333,0.68,0.826,0.72,1.36C73.367,60.985,73.104,61.541,72.652,61.917z M167.772,26.557c0-1.105,0.895-2,2-2h137.12c1.105,0,2,0.895,2,2v252c0,1.105-0.895,2-2,2h-136.96 c-1.101,0.088-2.065-0.733-2.154-1.834c-0.004-0.055-0.007-0.111-0.006-0.166V26.557z M327.772,315.597c0,1.105-0.895,2-2,2h-174 c-1.105,0-2-0.895-2-2v-21.04c0-1.105,0.895-2,2-2h173.68c1.09-0.177,2.118,0.564,2.294,1.654c0.019,0.114,0.027,0.23,0.026,0.346 V315.597z"/> </g> </g> <g> <g> <path d="M279.772,43.597h-82.88c-7.732,0-14,6.268-14,14v82.96c0,7.732,6.268,14,14,14h82.88c7.732,0,14-6.268,14-14v-82.96 C293.772,49.866,287.504,43.597,279.772,43.597z M281.772,140.637c-0.043,1.073-0.926,1.921-2,1.92h-82.88c-1.105,0-2-0.895-2-2 v-82.96c0-1.105,0.895-2,2-2h82.88c1.105,0,2,0.895,2,2V140.637z"/> </g> </g> <g> <g> <path d="M270.412,177.198h-64c-3.314,0-6,2.686-6,6s2.686,6,6,6h64c3.314,0,6-2.686,6-6S273.726,177.198,270.412,177.198z"/> </g> </g> <g> </svg>

          <span><?php echo $Lang->tr("You don't have any filled balance available"); ?></span>

      </section>
    <?php else: ?>
    <section class="kr-balance-widthdraw">
      <nav>
        <ul>
          <?php
          foreach ($BalanceList as $symbol => $valueBalance) {
            if($valueBalance <= 0) continue;
          ?>
            <li class="<?php if($symbolFetched == $symbol) echo 'kr-balance-widthdraw-selected'; ?>" onclick="_askWidthdraw('<?php echo $symbol; ?>');">
              <label><?php echo $symbol; ?></label>
              <span><?php echo $App->_formatNumber($valueBalance, ($valueBalance > 10 ? 2 : 6)); ?></span>
            </li>
          <?php } ?>
        </ul>
      </nav>
      <form class="kr-createwidthdraw" style="width:0px !important;" action="<?php echo APP_URL; ?>/app/modules/kr-trade/src/actions/askWidthdrawAction.php" method="post">
        <?php

        $precision = 2;
        if($IsRealMoney){
          $InfosCurrency = $Balance->_getInfosMoney($symbolFetched);
        }
        else {
          $InfosCurrency = $Balance->_getInfoCryptoCurrency($symbolFetched);
          $precision = 5;
        }

        $BalanceListResum = $Balance->_getBalanceListResum();

        $MinimalDeposit = $App->_getMinimumWidthdraw() * floatval($InfosCurrency['usd_rate_currency']);

        if($BalanceListResum[$symbolFetched] < $MinimalDeposit){
          ?>
          <section class="kr-widthdraw-chooseacc-msg">
            <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 339.452 339.452" style="enable-background:new 0 0 339.452 339.452;" xml:space="preserve"> <g> <g> <path d="M325.452,280.557h-4.72c0.048-0.666,0.048-1.334,0-2v-252c0-7.732-6.268-14-14-14h-136.8c-7.732,0-14,6.268-14,14v104 c-13.025,3.16-24.423,11.024-32,22.08c-9.292,13.387-12.164,30.208-7.84,45.92c5.76,22.08,6.48,51.36-5.44,63.52 c-6.863,6.083-16.517,7.897-25.12,4.72c-9.254-1.465-17.155-7.474-21.04-16c-12.72-29.36,19.04-84.88,19.12-85.12 c1.92-2.88,46.16-71.6,0.96-112c-1.84-1.92-45.44-48-79.12-43.52c-3.424,0.442-5.842,3.576-5.4,7c0.442,3.424,3.576,5.842,7,5.4 c8.094-0.486,16.162,1.316,23.28,5.2l-8.96,10.8c-4.856,6.017-3.914,14.831,2.103,19.687c0.098,0.079,0.197,0.157,0.297,0.233 l30.08,24c2.473,1.982,5.551,3.055,8.72,3.04h1.2c3.688-0.416,7.058-2.288,9.36-5.2l8.72-10.96c27.04,34.56-8,89.6-8.88,90.48 c-1.44,2.48-35.04,61.28-20,96c5.56,12.056,16.739,20.568,29.84,22.72c4.035,0.978,8.169,1.488,12.32,1.52 c8.97,0.27,17.654-3.175,24-9.52c16-16.72,14.72-50.8,8.48-74.96c-6.637-22.24,5.95-45.662,28.16-52.4v135.36 c-0.048,0.666-0.048,1.334,0,2h-4c-7.73-0.177-14.14,5.946-14.316,13.676c-0.003,0.108-0.004,0.216-0.004,0.324v21.04 c-0.002,7.732,6.264,14.002,13.996,14.004c0.108,0,0.216-0.001,0.324-0.004h173.68c7.732,0,14-6.268,14-14v-21.04 C339.452,286.826,333.184,280.557,325.452,280.557z M72.652,61.917l-8.88,10.96c-0.339,0.412-0.829,0.671-1.36,0.72 c-0.514,0.067-1.034-0.078-1.44-0.4l-30.08-24c-0.762-0.778-0.762-2.022,0-2.8l8.88-11.04c0.339-0.412,0.829-0.671,1.36-0.72 c0.514-0.067,1.034,0.078,1.44,0.4l30.08,24c0.419,0.333,0.68,0.826,0.72,1.36C73.367,60.985,73.104,61.541,72.652,61.917z M167.772,26.557c0-1.105,0.895-2,2-2h137.12c1.105,0,2,0.895,2,2v252c0,1.105-0.895,2-2,2h-136.96 c-1.101,0.088-2.065-0.733-2.154-1.834c-0.004-0.055-0.007-0.111-0.006-0.166V26.557z M327.772,315.597c0,1.105-0.895,2-2,2h-174 c-1.105,0-2-0.895-2-2v-21.04c0-1.105,0.895-2,2-2h173.68c1.09-0.177,2.118,0.564,2.294,1.654c0.019,0.114,0.027,0.23,0.026,0.346 V315.597z"/> </g> </g> <g> <g> <path d="M279.772,43.597h-82.88c-7.732,0-14,6.268-14,14v82.96c0,7.732,6.268,14,14,14h82.88c7.732,0,14-6.268,14-14v-82.96 C293.772,49.866,287.504,43.597,279.772,43.597z M281.772,140.637c-0.043,1.073-0.926,1.921-2,1.92h-82.88c-1.105,0-2-0.895-2-2 v-82.96c0-1.105,0.895-2,2-2h82.88c1.105,0,2,0.895,2,2V140.637z"/> </g> </g> <g> <g> <path d="M270.412,177.198h-64c-3.314,0-6,2.686-6,6s2.686,6,6,6h64c3.314,0,6-2.686,6-6S273.726,177.198,270.412,177.198z"/> </g> </g> <g> </svg>
            <span><?php echo $Lang->tr('You need to have at least').' '.round($MinimalDeposit, 2).' '.$symbolFetched; ?></span>
          </section>
          <?php
        } else {
          ?>

          <?php

        ?>
        <h2><span><?php echo $symbolFetched ?></span> <?php echo $Lang->tr('WITHDRAW'); ?></h2>
        <div class="kr-balance-range-content">
          <input type="text" class="kr-balance-range-inp" name="" value="<?php echo round($MinimalDeposit, ($IsRealMoney ? 2 : 5)); ?>">
          <div>
            <div class="kr-balance-range" kr-chosamount-precision="<?php echo $precision; ?>">
              <input type="text" id="kr-credit-chosamount" kr-chosamount-decimal="<?php echo $precision; ?>" kr-chosamount-step="<?php echo ($IsRealMoney ? 1 : 0.00001); ?>" kr-chosamount-symbol="<?php echo $InfosCurrency['symbol_currency']; ?>" kr-chosamount-max="<?php echo round($BalanceListResum[$symbolFetched], $precision); ?>" kr-chosamount-min="<?php echo round($MinimalDeposit, ($IsRealMoney ? 2 : 5)); ?>" name="kr-credit-chosamount" value="" />
            </div>
          </div>
        </div>

        <ul>
          <li>
            <label><?php echo $Lang->tr('Amount'); ?></label>
            <span kr-widthdraw-amount="true" kr-widthdraw-amount-decimal="<?php echo ($IsRealMoney ? 2 : 5); ?>"><i><?php echo $App->_formatNumber($MinimalDeposit, ($IsRealMoney ? 2 : 5)); ?></i> <?php echo $symbolFetched; ?></span>
          </li>
          <li>
            <label><?php echo $Lang->tr('Fees'); ?> (<?php echo $App->_formatNumber($App->_getWidthdrawFees(), 2); ?> %)</label>
            <span kr-widthdraw-fees="true" kr-widthdraw-amount-fees="<?php echo $App->_getWidthdrawFees(); ?>"><i><?php echo $App->_formatNumber($MinimalDeposit * $App->_getWidthdrawFees() / 100, ($IsRealMoney ? 2 : 5)); ?></i> <?php echo $symbolFetched; ?></span>
          </li>
          <li class="kr-balance-range-preview-total">
            <label><?php echo $Lang->tr('Total'); ?></label>
            <span kr-widthdraw-total="true"><i><?php echo $App->_formatNumber($MinimalDeposit - ($MinimalDeposit * $App->_getWidthdrawFees() / 100), ($IsRealMoney ? 2 : 5)); ?></i> <?php echo $symbolFetched; ?></span>
          </li>
          <?php if($User->_getCurrency() != $symbolFetched):
            $ConvertT = $Balance->_convertCurrency(1, $symbolFetched, $User->_getCurrency());
            ?>
            <li class="kr-balance-range-preview-convert" kr-widthdraw-convert-t="<?php echo $ConvertT; ?>">
              <label><?php echo $Lang->tr('Total in ').' '.$User->_getCurrency(); ?></label>
              <span kr-widthdraw-convert="true"><i><?php echo $App->_formatNumber($MinimalDeposit * $ConvertT, 2); ?></i> <?php echo $User->_getCurrency(); ?></span>
            </li>
          <?php endif; ?>
        </ul>

        <?php
        $listReceiver = [];
        foreach ($PaymentMethodList as $keyPaymentMethod => $infosPaymentMethod) {
          $listReceiver[] = [
            'receiver' => $Widthdraw->_getListWidthdraw($keyPaymentMethod),
            'paymentmethod' => $infosPaymentMethod
          ];
        }

        $receiverFound = false;
        foreach ($listReceiver as $key => $value) {
          if(!$receiverFound){
            if(count($value['receiver']) > 0) $receiverFound = true;
          }
        }

        $listReceivedAvailable = [];

        foreach ($listReceiver as $key => $value) {
          foreach($value['receiver'] as $kReceiver => $infosReceiver){
            $optionText = [];
            foreach ($value['paymentmethod']['preview'] as $keyPreview) {
              $optionText[] = $infosReceiver['infos'][$keyPreview];
            }

            if(!in_array($infosReceiver['type'], $listWithdrawAvailable)){
              continue;
            }

            if(!$IsRealMoney && $infosReceiver['type'] == "cryptocurrencies"){
              if($infosReceiver['infos']['cryptocurrency_name'] != $symbolFetched) continue;
              $listReceivedAvailable[App::encrypt_decrypt('encrypt', $infosReceiver['id'])] = join($optionText, ' - ');
            } else {
              $listReceivedAvailable[App::encrypt_decrypt('encrypt', $infosReceiver['id'])] = strtoupper($Lang->tr($infosReceiver['type'])).' - '.join($optionText, ' - ');
            }

          }
        }

        if(count($listReceiver) > 0 && $receiverFound && count($listReceivedAvailable) > 0):
        ?>
          <section class="kr-widthdraw-chooseacc">
            <span><?php echo $Lang->tr('Choose receiver account'); ?></span>
            <select class="" name="kr_widthdraw_method">
              <?php

              foreach ($listReceivedAvailable as $reiverID => $receiverInfos) {
                echo '<option value="'.$reiverID.'">'.$receiverInfos.'</option>';
              }
              ?>
            </select>
          </section>

          <footer>
            <?php if($User->_googleTwoFactorEnable($User->_getUserID())): ?>
              <section class="kr-widthdraw-gauth">
                <span><?php echo $Lang->tr('Enter your Google Authenticator Code'); ?></span>
                <input type="text" name="kr_widthdraw_gauth" maxlength="6" placeholder="&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;" value="">
              </section>
            <?php else: ?>
              <div></div>
            <?php endif; ?>
            <section>
              <input type="hidden" name="kr_widthdraw_symbol" value="<?php echo $symbolFetched ?>">
              <input type="hidden" name="kr_widthdraw_amount" value="<?php echo round($MinimalDeposit, ($IsRealMoney ? 2 : 5)); ?>">
              <input type="hidden" id="kr_withdraw_agreement_completed" name="kr_withdraw_agreement_completed" value="0">
              <input type="submit" class="btn btn-autowidth btn-orange" name="" value="Validate">
            </section>
          </footer>
        <?php else: ?>
          <section class="kr-widthdraw-chooseacc-msg">
            <span><?php echo $Lang->tr('You need to setup a widthdraw method'); ?></span>
            <button type="button" onclick="showAccountView({}, 'widthdraw');_closeCreditForm();return false;" class="btn btn-orange btn-autowidth btn-adm-user-c" name="button"><?php echo strtoupper('Set up withdraw method'); ?></button>
          </section>
        <?php endif; ?><?php } ?>

      </form>
    </section>
  <?php endif; ?>
  </section>
</section>
