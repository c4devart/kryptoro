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

// Check if user is logged
$User = new User();
if (!$User->_isLogged()) {
    throw new Exception("User is not logged", 1);
}

// Init CryptoApi modules
$CryptoApi = new CryptoApi(null, null, $App);

// Init lang object
$Lang = new Lang($User->_getLang(), $App);

try {

    // Check args given
    if (empty($_POST) || empty($_POST['graph']) || empty($_POST['indic']) || empty($_POST['key'])) {
        throw new Exception("Error : Empty post", 1);
    }

    // Get container
    $container = $_POST['graph'];

    // Get indicator list & check indicator given is available
    $listIndicatorAvailable = CryptoIndicators::_getIndicatorsList();
    if (!array_key_exists($_POST['indic'], $listIndicatorAvailable)) {
        throw new Exception("Error : Invalid indicator", 1);
    }

    // Infos indicator loaded
    $infosIndicator = CryptoIndicators::_getIndicatorsList()[$_POST['indic']];

    // Load indicator data saved

    $CryptoIndicator = new CryptoIndicators($container);
    $infosIndicatorSaved = $CryptoIndicator->_getIndicatorInformations($_POST['indic'], $_POST['key']);


    if (count($infosIndicatorSaved) > 0) {
        $dataIndicatorSaved = json_decode($infosIndicatorSaved[0]['data_indicators'], true);
        if (count($dataIndicatorSaved) > 0) {
            foreach ($dataIndicatorSaved as $dataKey => $valueData) {
                foreach ($infosIndicator['cfg'] as $indexInfoCat => $cat) {
                    foreach ($cat as $titleCatData => $infosData) {
                        $infosIndicator['cfg'][$indexInfoCat][$titleCatData][$dataKey]['type']['value'] = $valueData;
                    }
                }
            }
        }
    }

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}


?>
<div class="kr-overley kr-ov-nblr">

  <section>

    <header>

      <span><?php echo $infosIndicator['name']; ?></span>

    </header>

    <form action="<?php echo APP_URL; ?>/app/modules/kr-dashboard/src/actions/saveIndicator.php" class="kr-indicator-update-pst" method="post">
      <?php
      foreach ($infosIndicator['cfg'] as $idSection => $section) {
          echo '<section>';
          foreach ($section as $titleSection => $fields) {
              ?>
        <section>
          <span><?php echo $Lang->tr($titleSection); ?></span>
          <ul>
            <?php
            foreach ($fields as $keyField => $infoField) {
                if (empty($infoField['type']['field'])) {
                    continue;
                } ?>
              <li>
                <label><?php echo $Lang->tr($infoField['title']); ?></label>
                <?php
                if ($infoField['type']['field'] == "number") {
                    ?>
                  <input type="text" class="kr-indicator-cfg-txt" name="<?php echo $keyField; ?>" default="<?php echo $infoField['type']['default']; ?>" value="<?php echo $infoField['type']['value']; ?>">
                  <?php
                } elseif ($infoField['type']['field'] == "color") {
                    ?>
                  <div class="kr-indicator-cfg-select kr-indicator-cfg-color" default="<?php echo $infoField['type']['default']; ?>">
                    <div>
                      <input type="hidden" class="kr-indicator-cfg-postv" name="<?php echo $keyField; ?>" value="<?php echo $infoField['type']['value']; ?>">
                      <div class="kr-indicator-cfg-color-cell kr-indicator-cfg-val" style="background-color:<?php echo $infoField['type']['value']; ?>">
                      </div>
                      <svg class="lnr lnr-chevron-down"><use xlink:href="#lnr-chevron-down"></use></svg>
                    </div>
                    <ul>
                      <?php
                      foreach (CryptoIndicators::_getColorLineAvailable() as $color) {
                          echo '<li><div class="kr-indicator-cfg-color-cell" color="'.$color.'" style="background-color:'.$color.'"></div></li>';
                      } ?>
                    </ul>
                  </div>
                  <?php
                } elseif ($infoField['type']['field'] == "line") {
                    ?>
                  <div class="kr-indicator-cfg-select kr-indicator-cfg-line" default="<?php echo $infoField['type']['default']; ?>">
                    <div>
                      <input type="hidden" class="kr-indicator-cfg-postv" name="<?php echo $keyField; ?>" value="<?php echo $infoField['type']['value']; ?>">
                      <div class="kr-indicator-cfg-line kr-indicator-cfg-val" style="border-bottom-width:<?php echo $infoField['type']['value']; ?>px;"></div>
                      <svg class="lnr lnr-chevron-down"><use xlink:href="#lnr-chevron-down"></use></svg>
                    </div>
                    <ul>
                      <?php
                      foreach (CryptoIndicators::_getLineAvailable() as $lineHeight) {
                          echo '<li>
                          <div class="kr-indicator-cfg-line" border="'.$lineHeight.'" style="border-bottom-width:'.$lineHeight.'px;"></div>
                        </li>';
                      } ?>
                    </ul>
                  </div>
                  <?php
                } ?>
              </li>
              <?php
            } ?>

          </ul>
        </section>
        <?php
          }
          echo '</section>';
      }
      ?>

      <footer>

        <div>
          <input type="button" class="btn btn-overley btn-resetindicator" name="" value="<?php echo $Lang->tr('Reset'); ?>">
        </div>
        <div>
          <input type="button" class="btn btn-overley btn-closeovrley" value="<?php echo $Lang->tr('Cancel'); ?>">

          <input type="hidden" name="container" value="<?php echo $container; ?>">
          <input type="hidden" name="indicator" value="<?php echo $_POST['indic']; ?>">
          <input type="hidden" name="key" value="<?php echo $_POST['key']; ?>">
          <input type="submit" class="btn btn-orange btn-overley" value="<?php echo $Lang->tr('Apply'); ?>">
        </div>

      </footer>

    </form>
  </section>

</div>
