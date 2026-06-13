<?php
/**
 * Google OAuth Redirect
 */

require_once __DIR__ . '/../../app/bootstrap.php';

$controller = new \App\Controllers\OAuthController();
$controller->googleRedirect();
