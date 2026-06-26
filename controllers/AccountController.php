<?php
/**
 * AccountController
 * Handles user settings, profile updates, and password changes.
 */

require_once dirname(__DIR__) . '/models/User.php';
require_once __DIR__ . '/Session.php';

class AccountController
{
    private User $userModel;
    private string $baseUrl = '';

    public function __construct()
    {
        $this->userModel = new User();
    }

    private function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            header("Location: {$this->baseUrl}/login");
            exit;
        }
    }

    public function settings(): void
    {
        $this->requireAuth();

        $userId = (int) Session::get('user_id');
        $user   = $this->userModel->findById($userId);

        if (!$user) {
            Session::flash('error', 'User not found.');
            header("Location: {$this->baseUrl}/dashboard");
            exit;
        }

        $error_profile    = Session::getFlash('error_profile');
        $success_profile  = Session::getFlash('success_profile');
        $error_password   = Session::getFlash('error_password');
        $success_password = Session::getFlash('success_password');

        require_once dirname(__DIR__) . '/views/settings.php';
    }

    public function updateProfile(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/settings");
            exit;
        }

        $userId   = (int) Session::get('user_id');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $errors   = [];

        // Validation
        if (empty($username) || strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username may only contain letters, numbers, and underscores.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        // Uniqueness check
        if (empty($errors)) {
            if ($this->userModel->usernameExistsExcept($username, $userId)) {
                $errors[] = 'That username is already taken.';
            }
            if ($this->userModel->emailExistsExcept($email, $userId)) {
                $errors[] = 'An account with that email already exists.';
            }
        }

        if (!empty($errors)) {
            Session::flash('error_profile', implode('|', $errors));
            header("Location: {$this->baseUrl}/settings");
            exit;
        }

        $ok = $this->userModel->updateProfile($userId, $username, $email);

        if (!$ok) {
            Session::flash('error_profile', 'Failed to update profile. Please try again.');
        } else {
            // Update session data
            Session::set('user_username', $username);
            Session::set('user_email', $email);
            Session::flash('success_profile', 'Profile updated successfully!');
        }

        header("Location: {$this->baseUrl}/settings");
        exit;
    }

    public function updatePassword(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/settings");
            exit;
        }

        $userId      = (int) Session::get('user_id');
        $currentPw   = $_POST['current_password'] ?? '';
        $newPw       = $_POST['new_password'] ?? '';
        $confirmPw   = $_POST['confirm_password'] ?? '';
        $errors      = [];

        if (empty($currentPw) || empty($newPw) || empty($confirmPw)) {
            $errors[] = 'All password fields are required.';
        }

        // Verify current password
        if (empty($errors)) {
            // Fetch user row to check current password
            $email = Session::get('user_email');
            $user  = $this->userModel->findByEmail($email);
            if (!$user || !password_verify($currentPw, $user['password'])) {
                $errors[] = 'Incorrect current password.';
            }
        }

        if (empty($errors)) {
            if (strlen($newPw) < 8) {
                $errors[] = 'New password must be at least 8 characters.';
            }
            if (!preg_match('/[A-Z]/', $newPw)) {
                $errors[] = 'New password must contain at least one uppercase letter.';
            }
            if (!preg_match('/[0-9]/', $newPw)) {
                $errors[] = 'New password must contain at least one number.';
            }
            if ($newPw !== $confirmPw) {
                $errors[] = 'New passwords do not match.';
            }
        }

        if (!empty($errors)) {
            Session::flash('error_password', implode('|', $errors));
            header("Location: {$this->baseUrl}/settings");
            exit;
        }

        $ok = $this->userModel->updatePassword($userId, $newPw);

        if (!$ok) {
            Session::flash('error_password', 'Failed to update password. Please try again.');
        } else {
            Session::flash('success_password', 'Password changed successfully!');
        }

        header("Location: {$this->baseUrl}/settings");
        exit;
    }
}
