<?php
session_start();

require "../../../config/config.settings.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";


$App = new App(true);
$App->_loadModulesControllers();

$User = new User();

$Lang = new Lang(null, $App);

if($App->_enableGooglOauth()) $GoogleOauth = new GoogleOauth($User);
if($App->_enableFacebookOauth()) $FacebookOauth = new FacebookOauth($User);

$DemoUser = null;
if($App->_isDemoMode()){
  $DemoUser = $User->_generateDemoUser();
}

?>
<div class="kr-loading-fnc">
  <div> <div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div> </div>
</div>
<header>
  <img src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" alt="">
</header>
<section class="kr-login-act" act="<?php echo APP_URL; ?>/app/modules/kr-user/src/actions/login.php">
  <section class="kr-login-field">
    <input type="text" name="kr_usr_email" placeholder="<?php echo $Lang->tr('Your e-mail address'); ?>" value="<?php if(!is_null($DemoUser)) echo $DemoUser['email']; ?>">
    <div class="kr-i-msg-f-kr_usr_email"><span></span></div>
    <input type="password" name="kr_usr_pwd" placeholder="<?php echo $Lang->tr('Your password'); ?>" value="<?php if(!is_null($DemoUser)) echo $DemoUser['password']; ?>">
    <div class="kr-i-msg-f-kr_usr_pwd kr-login-i-last"><span style="<?php if($App->_isDemoMode()) echo 'color:#252525'; ?>"><?php if($App->_isDemoMode()) echo 'Demo mode, password : <b>'.$DemoUser['password'].'</b>'; ?></span></div>
    <footer>
      <a class="kr-resetpassword-view"><?php echo $Lang->tr('Forgot password ?'); ?></a>
      <button
        class="g-recaptcha btn-shadow"
        data-sitekey="<?php echo $App->_getGoogleRecaptchaSiteKey(); ?>"
        data-size="invisible"
        data-callback="kryptoLogin"><?php echo strtoupper($Lang->tr('Login')); ?></button>
    </footer>
  </section>
  <?php if($App->_enableGooglOauth() || $App->_allowSignup()): ?>
    <section class="kr-login-separator">
      <div></div>
      <span><?php echo $Lang->tr('or'); ?></span>
      <div></div>
    </section>
  <?php endif; ?>
  <section class="kr-login-oauth">
    <div class="">
      <?php if($App->_enableGooglOauth()): ?>
        <a href="<?php echo $GoogleOauth->_getAuthorizationUrl(); ?>" class="btn-shadow">
          <div class="kr-login-oauth-icn">
            <?php echo file_get_contents(APP_URL.'/assets/img/icons/oauth/google.svg'); ?>
          </div>
          <div class="kr-login-oauth-name">
            <?php echo $Lang->tr('Google'); ?>
          </div>
        </a>
      <?php endif; ?>
      <?php if($App->_enableFacebookOauth()): ?>
        <?php
        try {
          $FacebookOauthUrl = $FacebookOauth->_getAuthorizationUrl();
          ?>
          <a href="<?php echo $FacebookOauthUrl; ?>" class="btn-shadow btn-blue">
            <div class="kr-login-oauth-icn">
              <?php echo file_get_contents(APP_URL.'/assets/img/icons/oauth/facebook.svg'); ?>
            </div>
            <div class="kr-login-oauth-name">
              <?php echo $Lang->tr('Facebook'); ?>
            </div>
          </a>
          <?php
        } catch (\Exception $e) {
          error_log($e->getMessage());
        }

        ?>
      <?php endif; ?>
    </div>
    <?php if($App->_allowSignup()): ?>
      <a class="btn-shadow btn-black kr-login-signup-ctrl">
        <div class="kr-login-oauth-name">
          <?php echo $Lang->tr('Create a new account'); ?>
        </div>
      </a>
    <?php endif; ?>
  </section>
</section>
<?php if($App->_captchaSignup()): ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
