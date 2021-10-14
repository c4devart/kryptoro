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

} catch (Exception $e) {
    die(json_encode([
      'error' => 1,
      'msg' => $e->getMessage()
    ]));
}

$Calendar = new Calendar($App);
foreach ($Calendar->_getEvents() as $EventsCalendar) {

if($EventsCalendar['show_date']):
?>
  <h4 class="kr-calendar-spacetitle">
    <span><?php echo $EventsCalendar['month_date']; ?></span>
    <span><?php echo ($EventsCalendar['this_month'] ? 'THIS MONTH' : '');?></span>
  </h4>
<?php endif; ?>
<section class="kr-calendar-item" kr-calendar-item="<?php echo $EventsCalendar['id']; ?>" onclick="loadCalendarItem(<?php echo $EventsCalendar['id']; ?>)">

  <div class="kr-calendar-evinfos">
    <div class="kr-calendar-infos">
      <?php if(!is_null($EventsCalendar['coin_picture'])): ?>
        <img src="<?php echo APP_URL; ?>/assets/img/icons/crypto/<?php echo $EventsCalendar['coin_picture']; ?>.svg" alt="">
      <?php endif; ?>
      <div class="kr-calendar-title">
        <span><?php echo $EventsCalendar['title']; ?></span>
      </div>
    </div>
    <div class="kr-calendar-date">
      <span><?php echo $EventsCalendar['formate_date']; ?></span>
    </div>
  </div>
  <div class="kr-calendar-percentage">
    <div style="width:<?php echo $EventsCalendar['percentage']; ?>%;">

    </div>
  </div>
</section>
<?php } ?>
