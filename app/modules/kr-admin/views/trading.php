<?php

/**
 * Admin news social settings
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

// Init dashboard object
$Dashboard = new Dashboard(new CryptoApi(null, null, $App), $User);

$TradingCredentials = $App->_hiddenThirdpartyServiceCfg();

$Trade = new Trade($User, $App);

$SymbolListAvailable = [];
if($App->_hiddenThirdpartyActive()){
  $Balance = new Balance($User, $App, 'real');

  $SymbolListAvailable = $Balance->_getBalanceListResum();
}

?>
<form class="kr-admin kr-adm-post-evs" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/saveTrading.php" method="post">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Trading' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>

  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable native trading'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablenativetrading" <?php echo ($App->_hiddenThirdpartyActive() ? 'checked' : ''); ?> name="kr-adm-chk-enablenativetrading">
            <label for="kr-adm-chk-enablenativetrading"></label>
        </div>
      </div>
    </div>

    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable native trading without exchange'); ?></label><br/>
        <span><?php echo $Lang->tr('Warning !'); ?></span><br/>
        <span><?php echo $Lang->tr('This option is more risky, because you need to manage by yourself the liquidy'); ?></span><br/><br/>
        <span><?php echo $Lang->tr('You need to active the exchanges you want for fetch data from exchange'); ?></span>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablenativetradingwithoutexchange" <?php echo ($App->_enableNativeTradingWithoutExchange() ? 'checked' : ''); ?> name="kr-adm-chk-enablenativetradingwithoutexchange">
            <label for="kr-adm-chk-enablenativetradingwithoutexchange"></label>
        </div>
      </div>
    </div>

    <div class="kr-admin-boxthird">
      <?php
      foreach ($Trade->_getThirdParty() as $key => $Exchange) {
        ?>
        <div kr-exchangename="<?php echo $Exchange->_getExchangeName(); ?>" onclick="_showThirdpartySetup('<?php echo $Exchange->_getExchangeName(); ?>', 'tradingglobal');" class="<?php if(array_key_exists($Exchange->_getExchangeName(), $App->_hiddenThirdpartyServiceCfg())) echo 'kr-account-config-exchange-enable'; ?>">
          <div>
            <svg class="lnr lnr-checkmark-circle"><use xlink:href="#lnr-checkmark-circle"></use></svg>
          </div>
          <img src="<?php echo APP_URL; ?>/assets/img/icons/trade/<?php echo $Exchange->_getLogo(); ?>" alt="<?php echo $Exchange->_getName(); ?>">
        </div>
      <?php } ?>
    </div>

  </div>

  <?php if(false): ?>
  <h3><?php echo $Lang->tr('Wallets receiver'); ?></h3>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable direct deposit'); ?></label><br/>
        <span><?php echo $Lang->tr('User will be able to deposit throught a QRcode direclty to your wallet'); ?></span><br/>
        <span><?php echo $Lang->tr('The wallet link can be direclty the exchange'); ?></span>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-directdepositenable" <?php echo ($App->_getDirectDepositEnable() ? 'checked' : ''); ?> name="kr-adm-chk-directdepositenable">
            <label for="kr-adm-chk-directdepositenable"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Configure the wallet where you want receive the cryptocurrencies'); ?></label>
      </div>
      <div>
        <input type="button" onclick="changeView('admin', 'walletaddress');" class="btn btn-green btn-autowidth" name="" value="Configure my wallets">
      </div>
    </div>
  </div>
<?php endif; ?>
    <h3><?php echo $Lang->tr('Balance configuration'); ?></h3>
    <div class="kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Show balance estimation'); ?></label><br/>
          <span><?php echo $Lang->tr('Based on all wallet'); ?></span>
        </div>
        <div>
          <div class="ckbx-style-14">
              <input type="checkbox" id="kr-adm-chk-balancestimationshown" <?php echo ($App->_getBalanceEstimationShown() ? 'checked' : ''); ?> name="kr-adm-chk-balancestimationshown">
              <label for="kr-adm-chk-balancestimationshown"></label>
          </div>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('User user currency select'); ?></label><br/>
        </div>
        <div>
          <div class="ckbx-style-14">
              <input type="checkbox" id="kr-adm-chk-balancestimationuseuser" <?php echo ($App->_getBalanceEstimationUserCurrency() ? 'checked' : ''); ?> name="kr-adm-chk-balancestimationuseuser">
              <label for="kr-adm-chk-balancestimationuseuser"></label>
          </div>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Show balance estimation in'); ?></label>
        </div>
        <div>
          <?php if(count($SymbolListAvailable) == 0): ?>
            <span>You must activate at least 1 exchange</span>
          <?php else: ?>
            <select name="kr-adm-balancestimationcurrency">
              <?php
              foreach ($SymbolListAvailable as $symbolReceive => $value) {
                echo '<option '.($App->_getBalanceEstimationSymbol() == $symbolReceive ? 'selected' : '').' value="'.$symbolReceive.'">'.$symbolReceive.'</option>';
              }
              if($App->_hiddenThirdpartyActive()){
                foreach ($Balance->_getListMoney() as $key => $value) {
                  echo '<option '.($App->_getBalanceEstimationSymbol() == $value ? 'selected' : '').' value="'.$value.'">'.$value.'</option>';
                }
              }
              ?>
            </select>
          <?php endif; ?>
        </div>
      </div>
    </div>



    <h3><?php echo $Lang->tr('Leaderboard'); ?></h3>
    <div class="kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Enable leaderboard (native trading need to be enabled)'); ?></label>
        </div>
        <div>
          <div class="ckbx-style-14">
              <input type="checkbox" id="kr-adm-chk-enableleaderboard" <?php echo ($App->_getLeaderboardEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enableleaderboard">
              <label for="kr-adm-chk-enableleaderboard"></label>
          </div>
        </div>
      </div>
    </div>

    <h3><?php echo $Lang->tr('Markets'); ?></h3>
    <div class="kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Hide market (native trading need to be enabled)'); ?></label>
        </div>
        <div>
          <div class="ckbx-style-14">
              <input type="checkbox" id="kr-adm-chk-hidemarket" <?php echo ($App->_getHideMarket() ? 'checked' : ''); ?> name="kr-adm-chk-hidemarket">
              <label for="kr-adm-chk-hidemarket"></label>
          </div>
        </div>
      </div>
    </div>

    <h3><?php echo $Lang->tr('Referal system configuration'); ?></h3>
    <div class="kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Enable referal (native trading need to be enabled)'); ?></label>
        </div>
        <div>
          <div class="ckbx-style-14">
              <input type="checkbox" id="kr-adm-chk-enablereferal" <?php echo ($App->_referalEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablereferal">
              <label for="kr-adm-chk-enablereferal"></label>
          </div>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Referal comission (in $, fixed amount)'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Referal comission (in $, fixed amount) ex : When a referal signup & deposit real cash, the refer win 5 $ (value = 5)'); ?>" name="kr-adm-referalcomission" value="<?php echo $App->_getReferalWinAmount(); ?>">
        </div>
      </div>
    </div>

    <h3><?php echo $Lang->tr('Deposit configuration'); ?></h3>

    <div class="kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Deposit fees (in %)'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Deposit fees (in %)'); ?>" name="kr-adm-depositfees" value="<?php echo $App->_getFeesDeposit(); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Deposit minimum (in $)'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Deposit minimum (in $)'); ?>" name="kr-adm-depositminimum" value="<?php echo $App->_getMinimalDeposit(); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Deposit maximum (in $)'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Deposit maximum (in $)'); ?>" name="kr-adm-depositmaximum" value="<?php echo $App->_getMaximalDeposit(); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Deposit currencies allowed'); ?></label>
        </div>
        <div>
          <select id="select-state-disabled" name="deposit_currencies_allowed[]" multiple class="demo-default" placeholder="Select some currencies">
            <?php
            if($App->_hiddenThirdpartyActive()){
              foreach ($Balance->_getListMoney() as $key => $value) {
                ?>
                <option <?php if(!is_null($App->_getListCurrencyDepositAvailable()) && in_array($value, $App->_getListCurrencyDepositAvailable())) echo 'selected'; ?> value="<?php echo $value; ?>"><?php echo $value; ?></option>
                <?php
              }
            }
            ?>
        </select>
        <script type="text/javascript">
        $('#select-state-disabled').selectize();
        </script>
        </div>
      </div>


      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Deposit wallet receive (if wallet real currency not available)'); ?></label>
        </div>
        <div>
          <select name="kr-adm-depositrealmoneywallet">
            <?php
            foreach ($SymbolListAvailable as $symbolReceive => $value) {
              echo '<option '.($App->_getDepositSymbolNotExistConvert() == $symbolReceive ? 'selected' : '').' value="'.$symbolReceive.'">'.$symbolReceive.'</option>';
            }
            ?>

          </select>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Bank transfert deposit agreement'); ?></label>
        </div>
        <div>
          <textarea name="banktransfert_alert_deposit" style="width:100%; height:161px;"><?php echo $App->_getDepositMessage(); ?></textarea>
        </div>
      </div>

  </div>
  <h3><?php echo $Lang->tr('Withdraw configuration'); ?></h3>
  <div class="kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Enable automatic cryptocurrencies withdraw'); ?></label>
        </div>
        <div>
          <div class="ckbx-style-14">
              <input type="checkbox" id="kr-adm-chk-enableautomaticcryptocurrenciewithdraw" <?php echo ($App->_getEnableAutomaticWithdraw() ? 'checked' : ''); ?> name="kr-adm-chk-enableautomaticcryptocurrenciewithdraw">
              <label for="kr-adm-chk-enableautomaticcryptocurrenciewithdraw"></label>
          </div>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Configure exchange needed for each cryptocurrencies'); ?></label><br/>
          <span><?php echo $Lang->tr('You can define each exchange will be used for the withdraw for each cryptocurrency'); ?></span><br/>
          <span><?php echo $Lang->tr('Ex : BTC = Binance'); ?></span><br/>
          <span><?php echo $Lang->tr('Ex : ETH = Okcoin'); ?></span><br/>
          <span><?php echo $Lang->tr('Ex : LTC = Btcmarkets'); ?></span>
        </div>
        <div>
          <input type="button" onclick="changeView('admin', 'autowithdrawconfigure');" class="btn btn-green btn-autowidth" name="" value="Configure exchanges">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Withdraw minimum (in $)'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Withdraw minimum (in $)'); ?>" name="kr-adm-widthdrawmin" value="<?php echo $App->_getMinimumWidthdraw(); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Withdraw processing time (in days)'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Withdraw processing time (in days) = ex : 3'); ?>" name="kr-adm-widthdrawdays" value="<?php echo $App->_getNumberDaysWidthdrawProcess(); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Withdraw fees (in %)'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Withdraw fees (in %)'); ?>" name="kr-adm-widthdrawfees" value="<?php echo $App->_getWidthdrawFees(); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Withdraw reference pattern'); ?></label><br/>
          <span>$ : Random number (0-9)</span><br/>
          <span>* : Random Letter (A-Z)</span>
        </div>
        <div>
          <input type="text" name="kr-adm-orderpattern" placeholder="Your withdraw reference pattern (ex : WIDRAW-$**$-$$$$)" value="<?php echo $App->_getWidthdrawPattern(); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Bank transfert alert when withdraw currency is not crypto-currency'); ?></label>
        </div>
        <div>
          <textarea name="banktransfert_alert_withdraw" style="width:100%; height:161px;"><?php echo $App->_getWidthdrawMessage(); ?></textarea>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Cryptocurrencies allowed for bank transfert withdraw'); ?></label>
        </div>
        <div>
          <select id="select-banktransfert-cryptoallowed" name="bankwithdraw_cryptocurrency_allowed[]" multiple class="demo-default" placeholder="Select some cryptocurrencies allowed for bank transfert withdraw">
              <?php

              foreach (array_keys($SymbolListAvailable) as $key => $value) {
                ?>
                <option <?php echo (!is_null($App->_getWidthdrawCryptocurrencyAvailable()) && in_array($value, $App->_getWidthdrawCryptocurrencyAvailable()) ? 'selected' : ''); ?> value="<?php echo $value; ?>"><?php echo $value; ?></option>
                <?php
              }
              ?>
          </select>
          <script type="text/javascript">
          $('#select-banktransfert-cryptoallowed').selectize();
          </script>
        </div>
      </div>
    </div>
    <h3><?php echo $Lang->tr('Trading configuration'); ?></h3>
    <div class="kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Trading fees (in %)'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Trading fees (in %)'); ?>" name="kr-adm-tradingfees" value="<?php echo $App->_hiddenThirdpartyTradingFee(); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Order reference pattern'); ?></label><br/>
          <span>$ : Random number (0-9)</span><br/>
          <span>* : Random Letter (A-Z)</span>
        </div>
        <div>
          <input type="text" name="kr-adm-orderpattern" placeholder="Your order reference pattern (ex : ORDR-$**$-$$$$)" value="<?php echo $App->_hiddenTradingOrderPatternReference(); ?>">
        </div>
      </div>
    </div>

    <h3><?php echo $Lang->tr('Real account configuration'); ?></h3>
    <div class="kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Enable real account'); ?></label>
        </div>
        <div>
          <div class="ckbx-style-14">
              <input type="checkbox" id="kr-adm-chk-enablerealaccount" <?php echo ($App->_getTradingEnableRealAccount() ? 'checked' : ''); ?> name="kr-adm-chk-enablerealaccount">
              <label for="kr-adm-chk-enablerealaccount"></label>
          </div>
        </div>
      </div>
    </div>

    <h3><?php echo $Lang->tr('Practice account configuration'); ?></h3>
    <div class="kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Enable practice account'); ?></label>
        </div>
        <div>
          <div class="ckbx-style-14">
              <input type="checkbox" id="kr-adm-chk-enablepracticeaccount" <?php echo ($App->_getTradingEnablePracticeAccount() ? 'checked' : ''); ?> name="kr-adm-chk-enablepracticeaccount">
              <label for="kr-adm-chk-enablepracticeaccount"></label>
          </div>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Maximum free deposit (in $)'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Maximum free deposit (in $) ex : 10000'); ?>" name="kr-adm-maximumfreedeposit" value="<?php echo $App->_getMaximalFreeDeposit(); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Free deposit wallet receive'); ?></label>
        </div>
        <div>
          <?php if(count($SymbolListAvailable) == 0):
            ?>
            <span>You must activate at least 1 exchange</span>
            <?php
          else :?>
          <select name="kr-adm-symbolfreedeposit">
            <?php
            foreach ($SymbolListAvailable as $symbolReceive => $value) {
              echo '<option '.($App->_getFreeDepositSymbol() == $symbolReceive ? 'selected' : '').' value="'.$symbolReceive.'">'.$symbolReceive.'</option>';
            }
            ?>

          </select>
        <?php endif; ?>
        </div>
      </div>
  </div>
    <div class="kr-admin-action">
      <input type="submit" class="btn btn-orange" name="" value="<?php echo $Lang->tr('Save'); ?>">
    </div>
</form>
