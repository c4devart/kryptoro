<?php

/**
 * Logout user action
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../../config/config.settings.php";

// Destroy user session
unset($_SESSION);
session_destroy();

// Redirect user
header('Location: '.APP_URL);

?>
