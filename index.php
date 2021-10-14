<?php

/**
 * Index app
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

// Load modules & check domain
$App = new App(true);
$App->_checkDomain();
$App->_loadModulesControllers();

try {

  // Check if user is already logged
  $User = new User();
  if($User->_isLogged()) header('Location: '.APP_URL.'/dashboard'.($App->_rewriteDashBoardName() ? '' : '.php'));

  // Init lang object
  $Lang = new Lang(null, $App);

  if(!empty($_GET) && isset($_GET['lng']) && !empty($_GET['lng'])){
    $Lang->setLangCookie($_GET['lng']);
  }

  if($App->_enableGooglOauth()){
    $GoogleOauth = new GoogleOauth($User);
  }

  $App->_checkReferalSource();

} catch (Exception $e) {
  define('ERROR_SOFTWARE', $e->getMessage());
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <meta charset="utf-8">
    <title><?php echo (!is_null($App) ? $App->_getAppTitle() : ERROR_SOFTWARE); ?></title>
    <meta name="description" content="<?php echo (!is_null($App) ? $App->_getAppDescription() : ERROR_SOFTWARE); ?>">

    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/site.webmanifest">
    <link rel="shortcut icon" href="<?php echo APP_URL; ?>/assets/img/icons/favicon/favicon.ico">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="<?php echo APP_URL; ?>/assets/img/icons/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

    <link href="https://fonts.googleapis.com/css?family=Roboto+Mono:300,500|Roboto:300,400,500,700" rel="stylesheet">


    <link rel="stylesheet" href="assets/bower/animate.css/animate.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">

    <link rel="stylesheet" href="assets/css/responsive-tablet.css">
    <link rel="stylesheet" href="assets/css/responsive-mobile.css">
    <link rel="stylesheet" href="assets/css/responsive-global.css">
  </head>
  <body class="kr-login <?php if(isset($_GET['a'])) echo 'kr-ac-'.$_GET['a']; ?>" hrefapp="<?php echo APP_URL; ?>" <?php if(isset($_GET['a']) && $_GET['a'] == "pwdr") echo 'kr-pwdr="'.$_GET['token'].'"'; ?>>

    <section class="kr-page-view">
      <section>
        <section>

        </section>
        <footer>
          <a class="btn-shadow btn-orange close-kr-page">Done</a>
        </footer>
      </section>
    </section>
    <?php if($App->_getCookieAvertEnable()): ?>
      <div class="kr-cookie-approval">
        <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"> <g> <g> <g> <circle cx="308" cy="188" r="20"/> <circle cx="203" cy="138" r="20"/> <circle cx="397" cy="179" r="20"/> <circle cx="389" cy="431" r="20"/> <path d="M479.296,271.446C482.75,274.291,487.176,276,492,276c11.046,0,20-8.954,20-20c0-18.643-2.017-37.25-5.994-55.303 c-2.376-10.787-13.045-17.608-23.834-15.229c-10.787,2.376-17.605,13.047-15.229,23.834c1.085,4.927,1.979,9.907,2.716,14.92 C463.697,222.868,457.115,222,450,222c-40.728,0-74.444,30.59-79.379,70.002C370.414,292.001,370.207,292,370,292 c-44.112,0-80,35.888-80,80c0,0.842,0.013,1.682,0.039,2.521C255.554,383.411,230,414.778,230,452 c0,6.74,0.23,12.912,0.688,18.509C123.47,457.936,40,366.54,40,256c0-119.103,96.897-216,216-216 c69.511,0,135.208,33.789,175.74,90.385c6.432,8.981,18.928,11.045,27.904,4.615c8.98-6.431,11.047-18.925,4.615-27.905 c-23.217-32.418-54.107-59.338-89.334-77.848C338.514,10.113,297.391,0,256,0C187.62,0,123.333,26.629,74.98,74.98 C26.629,123.333,0,187.62,0,256s26.629,132.667,74.98,181.02C123.333,485.371,187.619,512,255.998,512c0.003,0,0.006,0,0.01,0 c0.637,0,1.273-0.037,1.908-0.097c0.214-0.02,0.424-0.057,0.637-0.084c0.411-0.053,0.82-0.109,1.228-0.187 c0.256-0.049,0.507-0.11,0.759-0.169c0.358-0.083,0.714-0.171,1.068-0.275c0.26-0.076,0.517-0.16,0.772-0.246 c0.338-0.114,0.672-0.236,1.005-0.368c0.253-0.101,0.503-0.205,0.751-0.316c0.323-0.144,0.641-0.299,0.958-0.462 c0.245-0.125,0.487-0.251,0.726-0.386c0.301-0.17,0.596-0.354,0.89-0.541c0.241-0.153,0.482-0.306,0.716-0.469 c0.271-0.19,0.536-0.393,0.8-0.598c0.239-0.185,0.478-0.368,0.708-0.563c0.075-0.064,0.155-0.118,0.23-0.183 c0.185-0.161,0.349-0.337,0.526-0.504c0.205-0.193,0.413-0.383,0.609-0.584c0.269-0.275,0.52-0.561,0.77-0.847 c0.154-0.177,0.313-0.349,0.461-0.531c0.276-0.34,0.53-0.691,0.781-1.044c0.107-0.151,0.221-0.295,0.324-0.449 c0.279-0.417,0.533-0.846,0.777-1.279c0.062-0.109,0.132-0.213,0.192-0.324c0.296-0.549,0.567-1.108,0.809-1.678 c0.063-0.147,0.11-0.302,0.169-0.452c0.166-0.421,0.329-0.843,0.465-1.272c0.082-0.255,0.144-0.516,0.215-0.775 c0.093-0.34,0.188-0.679,0.263-1.022c0.06-0.272,0.105-0.548,0.153-0.823c0.062-0.352,0.119-0.704,0.162-1.058 c0.032-0.262,0.056-0.526,0.078-0.791c0.032-0.393,0.051-0.786,0.06-1.181c0.003-0.149,0.022-0.293,0.022-0.443 c0-0.093-0.013-0.183-0.014-0.276c-0.006-0.432-0.032-0.864-0.065-1.296c-0.017-0.207-0.024-0.416-0.047-0.621 c-0.056-0.514-0.138-1.026-0.234-1.536c-0.021-0.111-0.032-0.225-0.054-0.335c-0.125-0.606-0.281-1.207-0.463-1.803 c-0.06-0.195-0.136-0.381-0.201-0.573c-0.135-0.398-0.272-0.795-0.434-1.185c-0.095-0.229-0.203-0.449-0.306-0.673 c-0.156-0.341-0.313-0.681-0.489-1.015c-0.125-0.237-0.262-0.466-0.396-0.698c-0.12-0.206-0.225-0.418-0.353-0.621 C272.339,479.657,270,471.758,270,452c0-22.056,17.944-40,40-40c0.685,0,1.448,0.024,2.334,0.075 c6.686,0.373,13.123-2.618,17.139-7.98c4.016-5.362,5.08-12.379,2.835-18.691C330.777,381.1,330,376.59,330,372 c0-22.056,17.944-40,40-40c4.963,0,9.822,0.908,14.443,2.7c6.937,2.688,14.792,1.305,20.392-3.591 c5.601-4.896,8.021-12.496,6.284-19.729c-0.743-3.09-1.119-6.246-1.119-9.38c0-22.056,17.944-40,40-40 c17.079,0,29.331,9.479,29.453,9.574L479.296,271.446z"/> <circle cx="113" cy="260" r="20"/> <circle cx="490" cy="492" r="20"/> <circle cx="490" cy="404" r="20"/> <circle cx="170" cy="348" r="20"/> <circle cx="211" cy="281" r="20"/> </g> </g> </g> </svg>
        <span><?php echo $Lang->tr($App->_getCookieTitle()); ?></span>
        <p><?php echo $Lang->tr($App->_getCookieText()); ?></p>
        <a class="btn btn-black kr-cookie-accept"><?php echo $Lang->tr('Accept'); ?></a>
      </div>
    <?php endif; ?>

    <?php if($App->_installDirectoryExist()): ?>
      <section class="kr-msg kr-msg-error" style="display:block; position:absolute; top:10px; left:10px; right:10px;">
        Install directory need to be deleted !
      </section>
    <?php endif; ?>

    <section class="kr-notif-alt kr-ov-nblr"></section>

    <section class="kr-login-loading-full">
      <img src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" alt="<?php echo $App->_getAppTitle(); ?>">
      <div>
        <div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div></div>
      </div>
    </section>

    <form action="" method="post">

      <section class="kr-login-view">

      </section>
      <section class="kr-app-overview" nov="1">

        <div class="kr-app-ovrview-infos">
          <div>
            <h2>Mobile ready !</h2>
          </div>
          <ul>
            <li class="kr-app-ovrview-selected"></li>
            <li></li>
            <li></li>
            <li></li>
          </ul>
        </div>

        <section kr-ov-title="Mobile ready !" style="background-image:url('<?php echo APP_URL; ?>/assets/img/login/overview/1.jpg')"></section>
        <section kr-ov-title="10 technical indicators" style="background-image:url('<?php echo APP_URL; ?>/assets/img/login/overview/2.jpg')"></section>
        <section kr-ov-title="Be alerted !" style="background-image:url('<?php echo APP_URL; ?>/assets/img/login/overview/3.jpg')"></section>
        <section kr-ov-title="All news at the same place" style="background-image:url('<?php echo APP_URL; ?>/assets/img/login/overview/4.jpg')"></section>

      </section>
    </form>
    <footer>
      <div class="kr_lang_select">
        <div>
          <div><img src="<?php echo APP_URL; ?>/assets/img/icons/languages/<?php echo $Lang->getLang(); ?>.svg"/></div>
          <?php
          foreach ($Lang->getListLanguage('') as $langISO => $langName) {
            if($langISO == $Lang->getLang()) echo '<span>'.$langName.'</span>';
          }
          ?>
        </div>
        <ul>
          <?php
          foreach ($Lang->getListLanguage('') as $langISO => $langName) {
            if($langISO == $Lang->getLang()) continue;
            ?>
            <li>
              <a href="?lng=<?php echo $langISO; ?>">
                <div><img src="<?php echo APP_URL; ?>/assets/img/icons/languages/<?php echo $langISO; ?>.svg"/></div>
                <span><?php echo $langName; ?></span>
              </a>
            </li>
            <?php
          }
          ?>
        </ul>
      </div>
      <ul>
        <li><a kr-page="term_use">Terms of service</a></li>
        <li><a kr-page="condition_use">Privacy Policy</a></li>
      </ul>
    </footer>

    <?php if($App->_GoogleAdEnabled()): ?>
      <div class="kr-ads">
        <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        <!-- Homepage Leaderboard -->
        <ins class="adsbygoogle"
        style="display:inline-block;width:728px;height:90px"
        data-ad-client="<?php echo $App->_getGoogleAdClient(); ?>"
        data-ad-slot="<?php echo $App->_getGoogleAdSlot(); ?>"></ins>
        <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
      </div>
    <?php endif; ?>

  </body>
  <script src="<?php echo APP_URL; ?>/assets/bower/jquery/dist/jquery.min.js" charset="utf-8"></script>

  <script src="<?php echo APP_URL; ?>/assets/js/login.js" charset="utf-8"></script>
  <script src="<?php echo APP_URL; ?>/assets/js/notifications.js" charset="utf-8"></script>

  <?php
  if($App->_getUserActivationRequire()){
    if($User->_checkParseActivationAccount()){
      echo '<script>$(document).ready(function(){ showAlert("Success !", "Your account was now active !", "success"); });</script>';
    }
  }

  if(!empty($_GET['rmsg']) && !empty($_GET['rtime']) && $_GET['rtime'] > (time() - 4) && $_GET['rtime'] < (time() + 4)){
    echo '<script>$(document).ready(function(){ showAlert("Oops", "'.base64_decode($_GET['rmsg']).'", "error"); });</script>';
  }

  ?>

  <!-- Google Analytics -->
  <?php echo $App->_getGoogleAnalytics(); ?>
  <script src="https://cdn.linearicons.com/free/1.0.0/svgembedder.min.js"></script>
</html>
