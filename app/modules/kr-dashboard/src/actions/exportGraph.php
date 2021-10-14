<?php

/**
 * Edit indicator action
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
      throw new Exception("User is not logged", 1);
  }

  if(empty($_POST) || !isset($_POST['container']) || empty($_POST['container'])) throw new Exception("Error : Args missing", 1);


} catch (Exception $e) {
  die($e->getMessage());
}

?>
<section class="export-popup kr-ov-nblr">
  <section>
    <header>
      <span>Export graph</span>
      <div>
        <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
      </div>
    </header>
    <ul container="<?php echo $_POST['container']; ?>">
      <li class="export-popup-act-pic">
        <span>Download screenshot</span>
        <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
           viewBox="0 0 58 58" style="enable-background:new 0 0 58 58;" xml:space="preserve">
        <g>
          <path d="M57,6H1C0.448,6,0,6.447,0,7v44c0,0.553,0.448,1,1,1h56c0.552,0,1-0.447,1-1V7C58,6.447,57.552,6,57,6z M56,50H2V8h54V50z"
            />
          <path d="M16,28.138c3.071,0,5.569-2.498,5.569-5.568C21.569,19.498,19.071,17,16,17s-5.569,2.498-5.569,5.569
            C10.431,25.64,12.929,28.138,16,28.138z M16,19c1.968,0,3.569,1.602,3.569,3.569S17.968,26.138,16,26.138s-3.569-1.601-3.569-3.568
            S14.032,19,16,19z"/>
          <path d="M7,46c0.234,0,0.47-0.082,0.66-0.249l16.313-14.362l10.302,10.301c0.391,0.391,1.023,0.391,1.414,0s0.391-1.023,0-1.414
            l-4.807-4.807l9.181-10.054l11.261,10.323c0.407,0.373,1.04,0.345,1.413-0.062c0.373-0.407,0.346-1.04-0.062-1.413l-12-11
            c-0.196-0.179-0.457-0.268-0.72-0.262c-0.265,0.012-0.515,0.129-0.694,0.325l-9.794,10.727l-4.743-4.743
            c-0.374-0.373-0.972-0.392-1.368-0.044L6.339,44.249c-0.415,0.365-0.455,0.997-0.09,1.412C6.447,45.886,6.723,46,7,46z"/>
        </g>
        </svg>
      </li>
      <li class="export-popup-act-csv">
        <span>Download graph data</span>
        <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
           width="446.969px" height="446.969px" viewBox="0 0 446.969 446.969" style="enable-background:new 0 0 446.969 446.969;"
           xml:space="preserve">
        <g>
          <path d="M430.266,23.857H16.699C7.487,23.857,0,31.354,0,40.555v365.86c0,5.654,2.841,10.637,7.155,13.663v2.632h5.989
            c1.149,0.252,2.332,0.401,3.555,0.401h413.575c1.22,0,2.401-0.149,3.545-0.401h0.821v-0.251
            c7.088-1.938,12.328-8.362,12.328-16.044V40.561C446.969,31.354,439.479,23.857,430.266,23.857z M66.006,408.396H15.459
            c-0.674-0.416-1.148-1.132-1.148-1.98v-43.35h51.695V408.396z M66.006,348.751H14.311v-47.01h51.695V348.751z M66.006,287.432
            H14.311V237.8h51.695V287.432z M66.006,223.487H14.311V169.8h51.695V223.487z M66.006,155.486H14.311v-52.493h51.695V155.486z
             M186.495,408.396H80.318v-45.33h106.176V408.396z M186.495,348.751H80.318v-47.01h106.176V348.751z M186.495,287.432H80.318V237.8
            h106.176V287.432z M186.495,223.487H80.318V169.8h106.176V223.487z M186.495,155.486H80.318v-52.493h106.176V155.486z
             M186.495,88.676H80.318v-50.51h106.176V88.676z M308.19,408.396H200.812v-45.33H308.19V408.396z M308.19,348.751H200.812v-47.01
            H308.19V348.751z M308.19,287.432H200.812V237.8H308.19V287.432z M308.19,223.487H200.812V169.8H308.19V223.487z M308.19,155.486
            H200.812v-52.493H308.19V155.486z M308.19,88.676H200.812v-50.51H308.19V88.676z M432.656,406.416c0,0.845-0.477,1.56-1.148,1.98
            H322.503v-45.33h110.153V406.416z M432.656,348.751H322.503v-47.01h110.153V348.751z M432.656,287.432H322.503V237.8h110.153
            V287.432z M432.656,223.487H322.503V169.8h110.153V223.487z M432.656,155.486H322.503v-52.493h110.153V155.486z M432.656,88.676
            H322.503v-50.51h107.763c1.312,0,2.391,1.073,2.391,2.389V88.676z M297.261,142.238h-85.921v-29.883h85.917v29.883H297.261z
             M297.261,209.757h-85.921v-29.882h85.917v29.882H297.261z M297.261,275.125h-85.921v-29.883h85.917v29.883H297.261z
             M297.261,339.843h-85.921v-29.878h85.917v29.878H297.261z M297.261,399.619h-85.921v-29.888h85.917v29.888H297.261z"/>
        </g>
        </svg>
      </li>
    </ul>
  </section>
</section>
