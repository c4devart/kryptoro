<?php

session_start();

require "../../../../config/config.settings.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";

$App = new App(true);
$App->_loadModulesControllers();

$User = new User();
if(!$User->_isLogged()) die('Error : User not logged');

$Lang = new Lang($User->_getLang(), $App);

$Identity = new Identity($User);

if($Identity->_isFinalStep($_GET['step'])){
  ?>
  <h3>Your identity is in verification</h3>
  <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"> <g> <path d="M31.634,37.989c1.041-0.081,1.99-0.612,2.606-1.459l9.363-12.944c0.287-0.397,0.244-0.945-0.104-1.293 c-0.348-0.347-0.896-0.39-1.293-0.104L29.26,31.555c-0.844,0.614-1.375,1.563-1.456,2.604s0.296,2.06,1.033,2.797 C29.508,37.628,30.413,38,31.354,38C31.447,38,31.54,37.996,31.634,37.989z M29.798,34.315c0.035-0.457,0.269-0.874,0.637-1.142 l7.897-5.713l-5.711,7.895c-0.27,0.371-0.687,0.604-1.144,0.64c-0.455,0.03-0.902-0.128-1.227-0.453 C29.928,35.219,29.762,34.771,29.798,34.315z"/> <path d="M54.034,19.564c-0.01-0.021-0.01-0.043-0.021-0.064c-0.012-0.02-0.031-0.031-0.044-0.05 c-1.011-1.734-2.207-3.347-3.565-4.809l2.148-2.147l1.414,1.414l4.242-4.243l-4.242-4.242l-4.243,4.242l1.415,1.415l-2.148,2.147 c-1.462-1.358-3.074-2.555-4.809-3.566c-0.019-0.013-0.03-0.032-0.05-0.044c-0.021-0.012-0.043-0.011-0.064-0.022 c-3.093-1.782-6.568-2.969-10.273-3.404V5h1.5c1.379,0,2.5-1.121,2.5-2.5S36.672,0,35.293,0h-9c-1.379,0-2.5,1.121-2.5,2.5 s1.121,2.5,2.5,2.5h1.5v1.156c-1.08,0.115-2.158,0.291-3.224,0.535c-0.538,0.123-0.875,0.66-0.751,1.198 c0.123,0.538,0.66,0.876,1.198,0.751c0.92-0.211,1.849-0.37,2.78-0.477l1.073-0.083c0.328-0.025,0.63-0.043,0.924-0.057V10 c0,0.553,0.447,1,1,1s1-0.447,1-1V8.03c3.761,0.173,7.305,1.183,10.456,2.845l-0.986,1.707c-0.276,0.479-0.112,1.09,0.366,1.366 c0.157,0.091,0.329,0.134,0.499,0.134c0.346,0,0.682-0.179,0.867-0.5l0.983-1.703c3.129,1.985,5.787,4.643,7.772,7.772 l-1.703,0.983C49.57,20.91,49.406,21.521,49.683,22c0.186,0.321,0.521,0.5,0.867,0.5c0.17,0,0.342-0.043,0.499-0.134l1.707-0.986 c1.685,3.196,2.698,6.798,2.849,10.619H53.63c-0.553,0-1,0.447-1,1s0.447,1,1,1h1.975c-0.151,3.821-1.164,7.423-2.849,10.619 l-1.707-0.986c-0.478-0.276-1.09-0.114-1.366,0.366c-0.276,0.479-0.112,1.09,0.366,1.366l1.703,0.983 c-1.985,3.129-4.643,5.787-7.772,7.772l-0.983-1.703c-0.277-0.48-0.89-0.643-1.366-0.366c-0.479,0.276-0.643,0.888-0.366,1.366 l0.986,1.707c-3.151,1.662-6.695,2.672-10.456,2.845V56c0-0.553-0.447-1-1-1s-1,0.447-1,1v1.976 c-1.597-0.055-3.199-0.255-4.776-0.617c-0.538-0.129-1.075,0.213-1.198,0.751c-0.124,0.538,0.213,1.075,0.751,1.198 C26.568,59.768,28.607,60,30.63,60c0.049,0,0.096-0.003,0.145-0.004c0.007,0,0.012,0.004,0.018,0.004 c0.008,0,0.015-0.005,0.023-0.005c4.807-0.033,9.317-1.331,13.219-3.573c0.031-0.014,0.064-0.021,0.094-0.039 c0.02-0.012,0.031-0.031,0.05-0.044c4.039-2.354,7.414-5.725,9.773-9.761c0.019-0.027,0.043-0.048,0.06-0.078 c0.012-0.021,0.011-0.043,0.021-0.064C56.317,42.476,57.63,37.89,57.63,33S56.317,23.524,54.034,19.564z M53.965,8.251l1.414,1.414 l-1.414,1.415L52.55,9.665L53.965,8.251z M29.793,6.021V3h-3.5c-0.275,0-0.5-0.225-0.5-0.5s0.225-0.5,0.5-0.5h9 c0.275,0,0.5,0.225,0.5,0.5S35.568,3,35.293,3h-3.5v3.021C31.445,6.007,31.113,6,30.793,6c-0.028,0-0.06,0.002-0.088,0.002 C30.68,6.002,30.655,6,30.63,6c-0.164,0-0.328,0.011-0.492,0.014C30.022,6.017,29.913,6.016,29.793,6.021z"/> <path d="M21.793,14h-5c-0.553,0-1,0.447-1,1s0.447,1,1,1h5c0.553,0,1-0.447,1-1S22.346,14,21.793,14z"/> <path d="M21.793,21h-10c-0.553,0-1,0.447-1,1s0.447,1,1,1h10c0.553,0,1-0.447,1-1S22.346,21,21.793,21z"/> <path d="M21.793,28h-15c-0.553,0-1,0.447-1,1s0.447,1,1,1h15c0.553,0,1-0.447,1-1S22.346,28,21.793,28z"/> <path d="M21.793,35h-19c-0.553,0-1,0.447-1,1s0.447,1,1,1h19c0.553,0,1-0.447,1-1S22.346,35,21.793,35z"/> <path d="M21.793,42h-13c-0.553,0-1,0.447-1,1s0.447,1,1,1h13c0.553,0,1-0.447,1-1S22.346,42,21.793,42z"/> <path d="M21.793,49h-7c-0.553,0-1,0.447-1,1s0.447,1,1,1h7c0.553,0,1-0.447,1-1S22.346,49,21.793,49z"/> </g> </svg>

  <p>Your identity is in verification, it can take up to 24h. You will receive an email when the verification is completed.</p>

  <input type="button" onclick="_closeIdentityWizard();" class="btn btn-autowidth btn-big btn-green" name="" value="Back to the app">
  <?php
} else {

$StepInformation = $Identity->_getIdentityStep($_GET['step']);



if($StepInformation['type_identity_step'] == "document"):
?>
<h3><?php echo $StepInformation['name_identity_step']; ?></h3>
<p><?php echo $StepInformation['description_identity_step']; ?></p>
<div class="identity-uploadprogress">
  <div>

  </div>
</div>
<section class="kr-identity-document-multiple">
  <?php if($StepInformation['allow_cam_identity_step'] == "1"): ?>
    <div class="kr-identity-document-camera">
      <div>
        <?php
        $GabaritInfos = explode('/', $StepInformation['document_ratio_identity_step']);
        $StartingHeight = 80;
        $StartingWidth = 70;
        $Ratio = intval($GabaritInfos[1]) / intval($GabaritInfos[0]);
        if($Ratio < 1){
          $StartingHeight = $StartingHeight * $Ratio;
        } else {
          $StartingWidth = $StartingWidth * (intval($GabaritInfos[0]) / intval($GabaritInfos[1]));
        }

        ?>
        <section class="kr-identity-document-camera-gabarit" style="height:<?php echo $StartingHeight; ?>%;width:<?php echo $StartingWidth; ?>%;">
          <span></span>
        </section>
        <video id="identity-document-video" width="349" height="262" autoplay></video>
        <canvas id="identity-document-video-result" width="640" height="480"></canvas>
      </div>
      <section>
        <button type="button" class="btn btn-autowidth btn-green kr-identity-takephoto" name="button">Take photo</button>
        <button type="button" class="btn btn-autowidth btn-blue kr-identity-takephoto-5s" kr-tpidentity="5" name="button">Take in 5s</button>
      </section>
    </div>
  <?php endif; ?>
  <form action="<?php echo APP_URL; ?>/app/modules/kr-identity/src/actions/submitAsset.php"
        class="identity-dropzone dropzone"
        id="my-awesome-dropzone">

        <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 486.3 486.3" style="enable-background:new 0 0 486.3 486.3;" xml:space="preserve"> <g> <g> <path d="M395.5,135.8c-5.2-30.9-20.5-59.1-43.9-80.5c-26-23.8-59.8-36.9-95-36.9c-27.2,0-53.7,7.8-76.4,22.5 c-18.9,12.2-34.6,28.7-45.7,48.1c-4.8-0.9-9.8-1.4-14.8-1.4c-42.5,0-77.1,34.6-77.1,77.1c0,5.5,0.6,10.8,1.6,16 C16.7,200.7,0,232.9,0,267.2c0,27.7,10.3,54.6,29.1,75.9c19.3,21.8,44.8,34.7,72,36.2c0.3,0,0.5,0,0.8,0h86 c7.5,0,13.5-6,13.5-13.5s-6-13.5-13.5-13.5h-85.6C61.4,349.8,27,310.9,27,267.1c0-28.3,15.2-54.7,39.7-69 c5.7-3.3,8.1-10.2,5.9-16.4c-2-5.4-3-11.1-3-17.2c0-27.6,22.5-50.1,50.1-50.1c5.9,0,11.7,1,17.1,3c6.6,2.4,13.9-0.6,16.9-6.9 c18.7-39.7,59.1-65.3,103-65.3c59,0,107.7,44.2,113.3,102.8c0.6,6.1,5.2,11,11.2,12c44.5,7.6,78.1,48.7,78.1,95.6 c0,49.7-39.1,92.9-87.3,96.6h-73.7c-7.5,0-13.5,6-13.5,13.5s6,13.5,13.5,13.5h74.2c0.3,0,0.6,0,1,0c30.5-2.2,59-16.2,80.2-39.6 c21.1-23.2,32.6-53,32.6-84C486.2,199.5,447.9,149.6,395.5,135.8z"/> <path d="M324.2,280c5.3-5.3,5.3-13.8,0-19.1l-71.5-71.5c-2.5-2.5-6-4-9.5-4s-7,1.4-9.5,4l-71.5,71.5c-5.3,5.3-5.3,13.8,0,19.1 c2.6,2.6,6.1,4,9.5,4s6.9-1.3,9.5-4l48.5-48.5v222.9c0,7.5,6,13.5,13.5,13.5s13.5-6,13.5-13.5V231.5l48.5,48.5 C310.4,285.3,318.9,285.3,324.2,280z"/> </g> </g> </svg>

        <span>Upload your document (PDF, PNG, JPG, JPEG) here</span>
      </form>
</section>
<?php
  endif;

if($StepInformation['type_identity_step'] == "doclist"):
?>


  <h3><?php echo $StepInformation['name_identity_step']; ?></h3>

  <ul class="kr-identity-docselect">
    <?php
    foreach ($Identity->_getDocList() as $key => $value) {
      ?>
      <li kr-identity-doc="<?php echo $value['id_identity_doclist']; ?>">
        <div></div>
        <span><?php echo $value['name_identity_doclist']; ?></span>
      </li>
      <?php
    }
    ?>

  </ul>

<?php
endif;

if($StepInformation['type_identity_step'] == "form"):
  ?>
  <h3><?php echo $StepInformation['name_identity_step']; ?></h3>
  <form class="kr-identity-form" action="<?php echo APP_URL; ?>/app/modules/kr-identity/src/actions/submitAsset.php" method="post">
    <?php
    $StepFormList = json_decode($StepInformation['description_identity_step'], true);
    foreach ($StepFormList as $keyFormStep => $valueFormStep) {
      ?>
      <div>
        <label><?php echo $valueFormStep['title']; ?></label>
        <input type="text" name="identity_form_step_<?php echo $keyFormStep; ?>" placeholder="<?php echo $valueFormStep['placeholder']; ?>" value="">
      </div>
      <?php
    }
    ?>
    <footer>
      <input type="hidden" name="step" value="<?php echo App::encrypt_decrypt('encrypt', $StepInformation['id_identity_step']); ?>">
      <input type="submit" class="btn btn-green btn-autowidth" name="" value="<?php echo $Lang->tr('Submit'); ?>">
    </footer>
  </form>
  <?php
endif;

}
?>
