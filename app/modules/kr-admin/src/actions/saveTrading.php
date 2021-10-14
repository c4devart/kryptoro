<?php

/**
 * Save trading settings
 *
 * This actions permit to admin to add an plan to krypto
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

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

    // Check loggin & permission
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("Your are not logged");
    }
    if (!$User->_isAdmin()) {
        throw new Exception("Error : Permission denied");
    }

    if($App->_isDemoMode()) throw new Exception("App currently in demo mode", 1);

    $TradingCredentials = $App->_hiddenThirdpartyServiceCfg();


    $App->_saveTrading(
        (array_key_exists('kr-adm-chk-enablenativetrading', $_POST) && $_POST['kr-adm-chk-enablenativetrading'] == "on" ? 1 : 0),
        json_encode([]),
        (isset($_POST['kr-adm-depositfees']) ? $_POST['kr-adm-depositfees'] : ''),
        (isset($_POST['kr-adm-depositminimum']) ? $_POST['kr-adm-depositminimum'] : ''),
        (isset($_POST['kr-adm-depositmaximum']) ? $_POST['kr-adm-depositmaximum'] : ''),
        (isset($_POST['kr-adm-widthdrawmin']) ? $_POST['kr-adm-widthdrawmin'] : ''),
        (isset($_POST['kr-adm-widthdrawdays']) ? $_POST['kr-adm-widthdrawdays'] : ''),
        (isset($_POST['kr-adm-tradingfees']) ? $_POST['kr-adm-tradingfees'] : ''),
        (array_key_exists('kr-adm-chk-enablerealaccount', $_POST) && $_POST['kr-adm-chk-enablerealaccount'] == "on" ? 1 : 0),
        (isset($_POST['kr-adm-maximumfreedeposit']) ? $_POST['kr-adm-maximumfreedeposit'] : ''),
        (isset($_POST['kr-adm-symbolfreedeposit']) ? $_POST['kr-adm-symbolfreedeposit'] : ''),
        (isset($_POST['deposit_currencies_allowed']) ? json_encode($_POST['deposit_currencies_allowed']) : json_encode([])),
        (array_key_exists('kr-adm-chk-balancestimationshown', $_POST) && $_POST['kr-adm-chk-balancestimationshown'] == "on" ? 1 : 0),
        (array_key_exists('kr-adm-chk-balancestimationshown', $_POST) && $_POST['kr-adm-chk-balancestimationuseuser'] == "on" ? 1 : 0),
        (isset($_POST['kr-adm-balancestimationcurrency']) ? $_POST['kr-adm-balancestimationcurrency'] : ''),
        (isset($_POST['kr-adm-depositrealmoneywallet']) ? $_POST['kr-adm-depositrealmoneywallet'] : ''),
        (isset($_POST['bankwithdraw_cryptocurrency_allowed']) ? json_encode($_POST['bankwithdraw_cryptocurrency_allowed']) : json_encode([])),
        (isset($_POST['kr-adm-widthdrawfees']) ? $_POST['kr-adm-widthdrawfees'] : ''),
        (isset($_POST['banktransfert_alert_withdraw']) ? $_POST['banktransfert_alert_withdraw'] : ''),
        (isset($_POST['banktransfert_alert_deposit']) ? $_POST['banktransfert_alert_deposit'] : ''),
        (array_key_exists('kr-adm-chk-enableleaderboard', $_POST) && $_POST['kr-adm-chk-enableleaderboard'] == "on" ? 1 : 0),
        (array_key_exists('kr-adm-chk-hidemarket', $_POST) && $_POST['kr-adm-chk-hidemarket'] == "on" ? 1 : 0),
        (array_key_exists('kr-adm-chk-enablepracticeaccount', $_POST) && $_POST['kr-adm-chk-enablepracticeaccount'] == "on" ? 1 : 0),
        (array_key_exists('kr-adm-chk-directdepositenable', $_POST) && $_POST['kr-adm-chk-directdepositenable'] == "on" ? 1 : 0),
        (array_key_exists('kr-adm-chk-enableautomaticcryptocurrenciewithdraw', $_POST) && $_POST['kr-adm-chk-enableautomaticcryptocurrenciewithdraw'] == "on" ? 1 : 0),
        (array_key_exists('kr-adm-chk-enablenativetradingwithoutexchange', $_POST) && $_POST['kr-adm-chk-enablenativetradingwithoutexchange'] == "on" ? 1 : 0)
      );

    $App->_cleanCache();

    $App->_saveReferal((array_key_exists('kr-adm-chk-enablereferal', $_POST) && $_POST['kr-adm-chk-enablereferal'] == "on" ? 1 : 0),
                       $_POST['kr-adm-referalcomission']);

    die(json_encode([
      'error' => 0,
      'msg' => 'Done',
      'title' => 'Success'
    ]));


    // var_dump(App::encrypt_decrypt('decrypt', 'YnVNd05pRzVTRUpLeFBIWjd6bm9hb05ER1laNUwwREdlMkJudEZVZ1h5MjcyQkdiVHRmc2xJM1RDbVdkOGdBRw=='));
    // var_dump(App::encrypt_decrypt('decrypt', 'WllBL2NGQXdzK0JnWWJxV2NVZG83Zz09'));
    // var_dump(App::encrypt_decrypt('decrypt', 'VnAwMVZRYlVhTDRHNUlsY2dGckNDNFFhTmk3QnNGSXJWQ1lheTRNMWVmNWs3V3k3aktZNVhjQTV6U0QzUnN1VGRaRzRxWHNDcGkwUWNkMitHRVVFdFZ0dTR2amc2cDJTVGoxTFBGeHdrUEszNHAxdFBqeXFHS285akxVSyttVmw='));

    // // Return success message
    // die(json_encode([
    //   'error' => 0,
    //   'msg' => 'Done',
    //   'title' => 'Success'
    // ]));

} catch (\Exception $e) { // If throw exception, return error message
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
