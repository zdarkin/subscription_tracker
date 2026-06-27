<?php
/**
 * AuthController
 * Handles user registration, login, logout, and input validation.
 */

require_once dirname(__DIR__) . '/models/User.php';
require_once __DIR__ . '/Session.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ------------------------------------------------------------------
    // Show Login Form
    // ------------------------------------------------------------------
    public function showLogin(): void
    {
        $error   = Session::getFlash('error');
        $success = Session::getFlash('success');
        require_once dirname(__DIR__) . '/views/auth/login.php';
    }

    // ------------------------------------------------------------------
    // Process Login
    // ------------------------------------------------------------------
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        // Basic validation
        if (empty($identifier) || empty($password)) {
            Session::flash('error', 'Username or email and password are required.');
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->authenticate($identifier, $password);

        if (!$user) {
            Session::flash('error', 'Invalid credentials. Please try again.');
            header('Location: /login');
            exit;
        }

        // Regenerate session ID to prevent fixation
        Session::regenerate();

        Session::set('user_id',       $user['id']);
        Session::set('user_username', $user['username']);
        Session::set('user_full_name',$user['full_name']);
        Session::set('user_email',    $user['email']);
        Session::set('user_role',     $user['role']);

        if ($user['role'] === 'admin') {
            header('Location: /admin');
        } else {
            header('Location: /dashboard');
        }
        exit;
    }

    // ------------------------------------------------------------------
    // Show Registration Form
    // ------------------------------------------------------------------
    public function showRegister(): void
    {
        $error   = Session::getFlash('error');
        $success = Session::getFlash('success');
        $old     = Session::getFlash('old');
        require_once dirname(__DIR__) . '/views/auth/register.php';
    }

    // ------------------------------------------------------------------
    // Process Registration
    // ------------------------------------------------------------------
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /register');
            exit;
        }

        $username  = trim($_POST['username']  ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email']     ?? '');
        $password  = $_POST['password']       ?? '';
        $confirm   = $_POST['confirm']        ?? '';
        $errors    = [];

        // --- Validation ---
        if (empty($full_name) || strlen($full_name) < 2) {
            $errors[] = 'Full name must be at least 2 characters.';
        }

        if (!preg_match('/^[a-zA-Z\s]+$/', $full_name)) {
            $errors[] = 'Full name may only contain letters and spaces.';
        }

        if (empty($username) || strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username may only contain letters, numbers and underscores.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }

        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        // --- Duplicate checks ---
        if (empty($errors)) {
            if ($this->userModel->usernameExists($username)) {
                $errors[] = 'That username is already taken.';
            }
            if ($this->userModel->emailExists($email)) {
                $errors[] = 'An account with that email already exists.';
            }
        }

        if (!empty($errors)) {
            Session::flash('error',  implode('|', $errors));
            Session::flash('old',    ['username' => $username, 'full_name' => $full_name, 'email' => $email]);
            header('Location: /register');
            exit;
        }

        $userId = $this->userModel->create($username, $full_name, $email, $password);

        if (!$userId) {
            Session::flash('error', 'Registration failed. Please try again.');
            header('Location: /register');
            exit;
        }

        Session::flash('success', 'Account created! You can now log in.');
        header('Location: /login');
        exit;
    }

    // ------------------------------------------------------------------
    // Logout
    // ------------------------------------------------------------------
    public function logout(): void
    {
        Session::destroy();
        header('Location: /login?logout=1');
        exit;
    }
}
