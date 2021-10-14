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

try {

  if(empty($_POST) || !isset($_POST['page'])) throw new Exception("Error : Permission denied", 1);

  $AddtionalPageInfos = $App->_getAdditionalPages(App::encrypt_decrypt('decrypt', $_POST['page']));
  if(count($AddtionalPageInfos) == 0) throw new Exception("Error : Permission denied", 1);
  $AddtionalPageInfos = $AddtionalPageInfos[0];

} catch (\Exception $e) {
  die($e->getMessage());
}




?>
<iframe src="<?php echo $AddtionalPageInfos['url_additional_pages']; ?>" frameBorder="0" width="100%" height="100%"><span color="#f4f6f9;">Iframe error, please check your console (right click, inspect element -> console)</span></iframe>
