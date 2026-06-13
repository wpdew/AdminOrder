<?php
/**
 * Google OAuth Callback
 */

require_once __DIR__ . '/../../app/bootstrap.php';

$controller = new \App\Controllers\OAuthController();
$controller->googleCallback();
