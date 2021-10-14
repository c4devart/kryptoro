<?php

session_start();

require("../app/src/Lang/Lang.php");
require("../config/config.settings.php");
require("../app/src/MySQL/MySQL.php");

require("app/src/Install.php");

$Install = new Install();

if(!empty($_POST)){
  $resultPost = $Install->_post($_GET['s']);
  if($Install->_getForward() != null && $resultPost == true) header('Location: '.$Install->_getForward());
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
    <link href="https://fonts.googleapis.com/css?family=Roboto+Mono:300,500|Roboto:300,400,500,700" rel="stylesheet">
    <script src="https://cdn.linearicons.com/free/1.0.0/svgembedder.min.js"></script>

    <link rel="stylesheet" href="../assets/bower/animate.css/animate.min.css">

    <link rel="stylesheet" href="assets/css/install.css">
    <script src="../assets/bower/jquery/dist/jquery.min.js" charset="utf-8"></script>
  </head>
  <body>
    <form class="kr-<?php echo $Install->_getStates(); ?>" action="index.php?s=<?php echo $Install->_getStates(); ?>" method="post">
      <input type="hidden" name="states" value="<?php echo $Install->_getStates(); ?>">
      <header>
        <img src="../assets/img/logo_black.svg" alt="">
      </header>
      <?php $Install->_loadPage(); ?>
    </form>
    <footer>
      <a href="http://community.ovrley.com/category/7/guides" target="_blank">Need help for installation ?</a>
    </footer>
  </body>

  <script src="assets/js/install.js" charset="utf-8"></script>
</html>
