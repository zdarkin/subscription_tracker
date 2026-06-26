<?php
/**
 * Front Controller / Router
 * Single entry point for all HTTP requests.
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/env.php';
require_once BASE_PATH . '/controllers/Session.php';

// Start secure session
Session::init();

require_once BASE_PATH . '/controllers/AuthController.php';
require_once BASE_PATH . '/controllers/DashboardController.php';
require_once BASE_PATH . '/controllers/SubscriptionController.php';
require_once BASE_PATH . '/controllers/AccountController.php';
require_once BASE_PATH . '/controllers/AdminController.php';

// -----------------------------------------------------------------
// Parse the request URI
// -----------------------------------------------------------------
$requestUri    = $_SERVER['REQUEST_URI'];
$scriptName    = dirname($_SERVER['SCRIPT_NAME']);  // e.g. /subscription-tracker/public
$path          = substr($requestUri, strlen($scriptName));
$path          = strtok($path, '?');                // strip query string
$path          = '/' . trim($path, '/');
$method        = $_SERVER['REQUEST_METHOD'];

// -----------------------------------------------------------------
// Route Table
// -----------------------------------------------------------------
$authCtrl    = new AuthController();
$dashCtrl    = new DashboardController();
$subCtrl     = new SubscriptionController();
$accountCtrl = new AccountController();
$adminCtrl   = new AdminController();

switch (true) {

    // Home → redirect based on role
    case ($path === '/' || $path === ''):
        if (Session::isLoggedIn()) {
            if (Session::isAdmin()) {
                header('Location: /admin');
            } else {
                header('Location: /dashboard');
            }
        } else {
            header('Location: /login');
        }
        exit;

    // Auth
    case ($path === '/login'    && $method === 'GET'):
        $authCtrl->showLogin();
        break;

    case ($path === '/login'    && $method === 'POST'):
        $authCtrl->login();
        break;

    case ($path === '/register' && $method === 'GET'):
        $authCtrl->showRegister();
        break;

    case ($path === '/register' && $method === 'POST'):
        $authCtrl->register();
        break;

    case ($path === '/logout'):
        $authCtrl->logout();
        break;

    // Dashboard (prevent admin access)
    case ($path === '/dashboard'):
        if (Session::isLoggedIn() && Session::isAdmin()) {
            header('Location: /admin');
            exit;
        }
        $dashCtrl->index();
        break;

    // Settings
    case ($path === '/settings' && $method === 'GET'):
        $accountCtrl->settings();
        break;

    case ($path === '/settings/profile' && $method === 'POST'):
        $accountCtrl->updateProfile();
        break;

    case ($path === '/settings/password' && $method === 'POST'):
        $accountCtrl->updatePassword();
        break;

    // Subscriptions – list (prevent admin access)
    case ($path === '/subscriptions' && $method === 'GET'):
        if (Session::isLoggedIn() && Session::isAdmin()) {
            header('Location: /admin');
            exit;
        }
        $subCtrl->index();
        break;


    // Subscriptions – create form
    case ($path === '/subscriptions/create' && $method === 'GET'):
        if (Session::isLoggedIn() && Session::isAdmin()) {
            header('Location: /admin');
            exit;
        }
        $subCtrl->create();
        break;

    // Subscriptions – store
    case ($path === '/subscriptions/create' && $method === 'POST'):
        if (Session::isLoggedIn() && Session::isAdmin()) {
            header('Location: /admin');
            exit;
        }
        $subCtrl->store();
        break;

    // Subscriptions – edit form
    case (preg_match('#^/subscriptions/edit/(\d+)$#', $path, $m) && $method === 'GET'):
        if (Session::isLoggedIn() && Session::isAdmin()) {
            header('Location: /admin');
            exit;
        }
        $subCtrl->edit((int) $m[1]);
        break;

    // Subscriptions – update
    case (preg_match('#^/subscriptions/edit/(\d+)$#', $path, $m) && $method === 'POST'):
        if (Session::isLoggedIn() && Session::isAdmin()) {
            header('Location: /admin');
            exit;
        }
        $subCtrl->update((int) $m[1]);
        break;

    // Subscriptions – delete
    case (preg_match('#^/subscriptions/delete/(\d+)$#', $path, $m) && $method === 'POST'):
        if (Session::isLoggedIn() && Session::isAdmin()) {
            header('Location: /admin');
            exit;
        }
        $subCtrl->delete((int) $m[1]);
        break;

    // Admin Panel – dashboard
    case ($path === '/admin' && $method === 'GET'):
        $adminCtrl->dashboard();
        break;

    // Admin Panel – users list
    case ($path === '/admin/users' && $method === 'GET'):
        $adminCtrl->users();
        break;

    // Admin Panel – create user
    case ($path === '/admin/users/create' && $method === 'GET'):
    case ($path === '/admin/users/create' && $method === 'POST'):
        $adminCtrl->createUser();
        break;

    // Admin Panel – edit user
    case (preg_match('#^/admin/users/(\d+)/edit$#', $path, $m) && ($method === 'GET' || $method === 'POST')):
        $adminCtrl->editUser((int) $m[1]);
        break;

    // Admin Panel – delete user
    case (preg_match('#^/admin/users/(\d+)/delete$#', $path, $m) && $method === 'POST'):
        $adminCtrl->deleteUser((int) $m[1]);
        break;

    // Admin Panel – view user subscriptions
    case (preg_match('#^/admin/users/(\d+)/subscriptions$#', $path, $m) && $method === 'GET'):
        $adminCtrl->viewUserSubscriptions((int) $m[1]);
        break;

    // Admin Panel – create user subscription on behalf of
    case (preg_match('#^/admin/users/(\d+)/subscriptions/create$#', $path, $m) && ($method === 'GET' || $method === 'POST')):
        $adminCtrl->createUserSubscription((int) $m[1]);
        break;

    // Admin Panel – edit user subscription on behalf of
    case (preg_match('#^/admin/users/(\d+)/subscriptions/(\d+)/edit$#', $path, $m) && ($method === 'GET' || $method === 'POST')):
        $adminCtrl->editUserSubscription((int) $m[1], (int) $m[2]);
        break;

    // Admin Panel – delete user subscription on behalf of
    case (preg_match('#^/admin/users/(\d+)/subscriptions/(\d+)/delete$#', $path, $m) && $method === 'POST'):
        $adminCtrl->deleteUserSubscription((int) $m[1], (int) $m[2]);
        break;

    // 404
    default:
        http_response_code(404);
        require_once BASE_PATH . '/views/404.php';
        break;
}
