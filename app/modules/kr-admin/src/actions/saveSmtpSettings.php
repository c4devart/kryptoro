<?php

/**
 * Change SMTP settings
 *
 * This actions permit to admin to change SMTP settings in Krypto
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
        throw new Exception("Your are not logged", 1);
    }
    if (!$User->_isAdmin()) {
        throw new Exception("Error : Permission denied", 1);
    }

    if($App->_isDemoMode()) throw new Exception("App currently in demo mode", 1);

    // Check data available
    if (empty($_POST)) {
        throw new Exception("Error : Args not valid", 1);
    }

    if($_POST['kr-adm-chk-mailtype'] != $App->_getMailType()){

      $App->_changeMailType($_POST['kr-adm-chk-mailtype']);

      die(json_encode([
        'error' => 0,
        'msg' => 'Done',
        'title' => 'Mail engine change'
      ]));

    }

    if($_POST['kr-adm-chk-mailtype'] == "smtp"){


    if($_POST['kr-adm-smtppassword'] != "********"){

      $mail = new PHPMailer\PHPMailer\PHPMailer;
        try {
          $mail->isSMTP();
          $mail->SMTPDebug = 0;
          $mail->Host = $_POST['kr-adm-smtpserver'];
          $mail->Port = $_POST['kr-adm-smtpport'];
          $mail->CharSet = 'UTF-8';
          $mail->SMTPAuth = true;
          $mail->Timeout = 6;
          if($_POST['kr-adm-security'] != "0" && ($_POST['kr-adm-security'] == "ssl" || $_POST['kr-adm-security'] == "tls")){
            $mail->SMTPSecure = ($_POST['kr-adm-security'] == "0" ? false : $_POST['kr-adm-security']);
          }
          $mail->Username = $_POST['kr-adm-smtpuser'];
          $mail->Password = ($_POST['kr-adm-smtppassword'] == "********" ? $App->_getSmtpPassword() : $_POST['kr-adm-smtppassword']);
          $mail->setFrom($_POST['kr-adm-smtpuser'], $_POST['kr-adm-smtpuser']);
          $mail->addAddress($_POST['kr-adm-smtpuser']);
          $mail->Subject = 'Test email';
          $mail->msgHTML('Test ...');
          if(!$mail->send()) throw new Exception('SMTP Connect fail');
        } catch (PHPMailer\PHPMailer\Exception $es) {
          die(json_encode([
            'error' => 1,
            'msg' => 'SMTP Error : '.$mail->ErrorInfo
          ]));
        }



        // Save SMTP settings
        $App->_saveSmtpSettings(
          (array_key_exists('kr-adm-chk-enablesmtp', $_POST) && $_POST['kr-adm-chk-enablesmtp'] == "on" ? 1 : 0), // If smtp service is enabled
          $_POST['kr-adm-smtpserver'], // SMTP Server
          $_POST['kr-adm-smtpport'], // SMTP Port
          $_POST['kr-adm-smtpuser'], // SMTP User

          // Save encrypted password
          ($_POST['kr-adm-smtppassword'] == "********" ? $App->_getSmtpPassword() : $_POST['kr-adm-smtppassword']),
          $_POST['kr-adm-security'],
          $_POST['kr-adm-chk-mailtype']
        );
      }

    } else {

      $App->_saveMailSettings(
        $_POST['kr-adm-mailfromaddr']
      );

    }

    $App->_saveWelcomeMailSettings(
      (array_key_exists('kr-adm-chk-sendwelcommail', $_POST) && $_POST['kr-adm-chk-sendwelcommail'] == "on" ? 1 : 0),
      $_POST['kr-adm-welcomemailsubject']
    );

    $App->_saveSenderEmailName($_POST['kr-adm-emailsendername']);

    $App->_saveSupport(
      $_POST['kr-adm-supportmail'],
      $_POST['kr-adm-supportphone'],
      $_POST['kr-adm-supportaddress'],
      $_POST['kr-adm-dpomail'],
      $_POST['kr-adm-dpophone']
    );

    // Return success message
    die(json_encode([
      'error' => 0,
      'msg' => 'Done',
      'title' => 'Success'
    ]));

} catch (\Exception $e) { // If throw exception, return error message
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
