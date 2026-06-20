<?php
require_once 'app/bootstrap.php';

use App\Controllers\AccessController;
use App\Controllers\AuthController;
use App\Controllers\BlockedIpController;
use App\Controllers\DashboardController;
use App\Controllers\SettingsController;
use App\Controllers\TableController;
use App\Controllers\UserController;

// Підтримка query-роуту: /admin/?route=login
$queryRoute = trim((string)($_GET['route'] ?? ''), '/');

if ($queryRoute !== '') {
	$route = '/' . $queryRoute;
} else {
	$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin/', PHP_URL_PATH) ?: '/admin/';
	$basePath = '/admin';

	if (str_starts_with($requestPath, $basePath)) {
		$requestPath = substr($requestPath, strlen($basePath));
	}

	$route = '/' . ltrim($requestPath, '/');
	$route = rtrim($route, '/');
	if ($route === '') {
		$route = '/';
	}
}

// Legacy aliases для старих URL з .php
$legacyRouteMap = [
	'/index.php' => '/',
	'/login.php' => '/login',
	'/register.php' => '/register',
	'/logout.php' => '/logout',
	'/users.php' => '/users',
	'/profile.php' => '/profile',
	'/table.php' => '/table',
	'/settings.php' => '/settings',
	'/blocked-ips.php' => '/blocked-ips',
	'/access-denied.php' => '/access-denied',
];

if (isset($legacyRouteMap[$route])) {
	$route = $legacyRouteMap[$route];
}

$authController = new AuthController();
$dashboardController = new DashboardController();
$userController = new UserController();
$tableController = new TableController();
$settingsController = new SettingsController();
$blockedIpController = new BlockedIpController();
$accessController = new AccessController();

switch ($route) {
	case '/':
		$dashboardController->index();
		break;

	case '/login':
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
			$authController->login();
		} else {
			$authController->showLogin();
		}
		break;

	case '/register':
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
			$authController->register();
		} else {
			$authController->showRegister();
		}
		break;

	case '/logout':
		$authController->logout();
		break;

	case '/users':
		$userController->index();
		break;

	case '/profile':
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
			$userController->updateProfile();
		} else {
			$userController->profile();
		}
		break;

	case '/table':
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
			$tableController->update();
		} else {
			$tableController->index();
		}
		break;

	case '/settings':
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
			$settingsController->save();
		} else {
			$settingsController->index();
		}
		break;

	case '/blocked-ips':
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
			$blockedIpController->handlePost();
		} else {
			$blockedIpController->index();
		}
		break;

	case '/access-denied':
		$accessController->denied();
		break;

	case '/api/dashboard-stats':
		$dashboardController->getDashboardStats();
		break;

	default:
		http_response_code(404);
		echo '404 Not Found';
		break;
}
