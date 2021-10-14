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

$error = 0;
$msgAction = 'Your widthdraw is on the way !';
try {

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("Error : User is not logged", 1);
    }

    if(!$App->_hiddenThirdpartyActive()) throw new Exception("Permission denied", 1);

    if(empty($_GET) || !isset($_GET['token']) || empty($_GET['token'])) throw new Exception("Error : Permissions denied", 1);

    $Balance = new Balance($User, $App, 'real');
    $Balance->_askWidthdrawApprove($_GET['token']);



} catch (\Exception $e) {
  $error = 1;
  $msgAction = $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php echo $App->_getAppTitle(); ?> - Widthdraw confirmation</title>
    <style media="screen">
      html {
        margin: 0; padding: 0;
        font-size: 16px; font-family: sans-serif;
        height: 100%; width: 100%;
      }
      body {
        background: #f4f6f9;
        margin: 0; padding: 0;
        height: 100%;
        width: 100%;
        display: flex; justify-content: center; align-items: center;
      }

      body > div {
        width: 350px;
        padding: 30px;
        max-width: 100%;
        background: #fff;
        box-shadow: 0px 2px 2px 0px rgba(0,0,0,0.15);
        display: flex; flex-direction: column;
        align-items: center;
      }
      body > div > img {
        max-width: 70%;
        max-height: 150px;
      }

      body > div > h2 {
        text-align: center;
        font-size: 17px;
        font-weight: 500;
      }

      body > div.error > h2 {
        color:red;
      }
      body > div > p {
        text-align: center;
      }

      body > div > span {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: bold;
        opacity: 0.4;
        text-align: center;
      }

      body > div > a {
        text-decoration: none;
        color:#fff;
        background: #e04801;
        padding: 10px 10px;
        border-radius: 2px;
        margin-top: 15px;
        text-transform: uppercase;
        font-size: 13px;
      }
    </style>
  </head>
  <body>
    <div class="<?php echo ($error == 1 ? 'error' : 'success'); ?>">
      <img src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" alt="<?php echo $App->_getAppTitle(); ?>"/>
      <h2><?php echo $msgAction; ?></h2>
      <?php if($error == 0): ?>
        <p>You have approve the request, it will be processed in <?php echo $App->_getNumberDaysWidthdrawProcess(); ?> day<?php echo ($App->_getNumberDaysWidthdrawProcess() > 1 ? 's' : ''); ?> maximum.</p>
      <?php endif; ?>
      <span>You will be redirected to the application in 5 seconds</span>
      <a href="<?php echo APP_URL; ?>">Back to the application</a>
    </div>
  </body>
  <script type="text/javascript">
    window.setTimeout(function(){
        window.location.href = "<?php echo APP_URL; ?>";
    }, 5000);
  </script>
</html>
