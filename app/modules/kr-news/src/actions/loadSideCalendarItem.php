<?php

/**
 * Article new view
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
        throw new Exception("User not logged", 1);
    }

    // Init language object
    $Lang = new Lang($User->_getLang(), $App);

    if(empty($_POST) || !isset($_POST['itemid']) || empty($_POST['itemid']) || !is_numeric($_POST['itemid'])) throw new Exception("Permission denied", 1);


    $CryptoApi = new CryptoApi($User, null, $App);

    $Calendar = new Calendar($App);
    $Event = $Calendar->_getEventItem($_POST['itemid'], $CryptoApi);
    if(is_null($Event)) throw new Exception("Not found", 1);


} catch (Exception $e) {
    die(json_encode([
      'error' => 1,
      'msg' => $e->getMessage()
    ]));
}

?>
<header>
  <div>
    <span><?php echo $Event['title']; ?></span>
    <svg onclick="closeCalendarItemView();" class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
  </div>
  <span><?php echo $Event['formate_date']; ?></span>
</header>
<?php if(!is_null($Event['coins_kr'])): ?>
<section class="kr-calendareventitem-coininfos">
  <div>
    <img src="<?php echo $Event['coins_kr']->_getIcon(); ?>" alt="">
    <div>
      <span><?php echo $Event['coins_kr']->_getCoinName(); ?></span>
      <span><?php echo $App->_formatNumber($Event['coins_kr']->_getPrice(), ($Event['coins_kr']->_getPrice() > 10 ? 2 : 4)).' '.$CryptoApi->_getCurrencySymbol(); ?></span>
    </div>
  </div>
  <ul>
    <li class="<?php echo ($Event['coins_kr']->_getCoin24Evolv() < 0 ? 'kr-calendareventitem-coinsinfo-negativ' : ''); ?>">
      <span>Change</span>
      <span><?php echo $App->_formatNumber($Event['coins_kr']->_getCoin24Evolv(), 2); ?>%</span>
    </li>
  </ul>
</section>
<?php endif; ?>
<section class="kr-calendareventitem-content">
  <div class="kr-calendareventitem-content-vote">
    <div class="kr-calendareventitem-content-vote-i">
      <span><?php echo $Event['vote_count']; ?> votes</span>
      <i>&mdash;</i>
      <span><?php echo $Event['percentage']; ?>%</span>
    </div>
    <div class="kr-calendareventitem-content-vote-pb">
      <div style="width:<?php echo $Event['percentage']; ?>%;"></div>
    </div>
  </div>
  <a href="<?php echo $Event['source']; ?>" class="btn btn-grey btn-small" target=_bank>Go to <?php echo substr(str_replace(['http://', 'https://'], ['', ''], $Event['source']), 0, 30).(strlen($Event['source']) > 30 ? '...' : ''); ?>
  </a>
  <p><?php echo $Event['description']; ?></p>
  <img src="<?php echo $Event['proof']; ?>" alt="">
  <div class="kr-calendareventitem-content-source">
    <span>Source : <a href="https://coinmarketcal.com/" target=_bank>Coinmarketcal.com</a></span>
  </div>
</section>
