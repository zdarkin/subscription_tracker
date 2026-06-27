<?php
/**
 * AdminController
 * Handles user management and managing subscriptions on behalf of users.
 */

require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Subscription.php';
require_once __DIR__ . '/Session.php';

class AdminController
{
    private User $userModel;
    private Subscription $subModel;
    private string $baseUrl = '';

    public function __construct()
    {
        $this->userModel = new User();
        $this->subModel  = new Subscription();
    }

    private function requireAdmin(): void
    {
        if (!Session::isLoggedIn()) {
            header("Location: {$this->baseUrl}/login");
            exit;
        }
        if (!Session::isAdmin()) {
            Session::flash('error', 'Access denied. Admin privileges required.');
            header("Location: {$this->baseUrl}/dashboard");
            exit;
        }
    }

    // ------------------------------------------------------------------
    // Dashboard (Stats & Recent Users)
    // ------------------------------------------------------------------
    public function dashboard(): void
    {
        $this->requireAdmin();

        $stats = [
            'total_users'         => $this->userModel->getCount(),
            'total_subscriptions' => $this->subModel->getTotalCount(),
            'active_subscriptions' => $this->subModel->getTotalActiveCount(),
            'global_monthly_spend' => $this->subModel->getGlobalMonthlySpend(),
        ];

        $recentUsers = $this->userModel->getRecentUsers(5);

        $flash_success = Session::getFlash('success');
        $flash_error   = Session::getFlash('error');

        require_once dirname(__DIR__) . '/views/admin/dashboard.php';
    }

    // ------------------------------------------------------------------
    // Trigger Email Alert Worker (POST API)
    // ------------------------------------------------------------------
    public function triggerWorker(): void
    {
        $this->requireAdmin();

        // Trigger via local HTTP request to bypass disabled shell exec() on shared hosting
        $token = $_ENV['WORKER_TOKEN'] ?? '';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $url = $protocol . $host . '/scripts/email_worker.php?token=' . urlencode($token);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        header('Content-Type: application/json');
        if ($curlError) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to connect to email worker.',
                'output'  => 'CURL Error: ' . $curlError
            ]);
        } elseif ($httpCode !== 200) {
            echo json_encode([
                'success' => false,
                'message' => 'Email worker returned HTTP code: ' . $httpCode,
                'output'  => $response
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Email worker finished successfully.',
                'output'  => $response
            ]);
        }
        exit;
    }

    // ------------------------------------------------------------------
    // Users List (with search)
    // ------------------------------------------------------------------
    public function users(): void
    {
        $this->requireAdmin();

        $query = trim($_GET['q'] ?? '');
        if ($query !== '') {
            $users = $this->userModel->searchUsers($query);
        } else {
            $users = $this->userModel->getAll();
        }

        $flash_success = Session::getFlash('success');
        $flash_error   = Session::getFlash('error');

        require_once dirname(__DIR__) . '/views/admin/users.php';
    }

    // ------------------------------------------------------------------
    // Create User on Behalf
    // ------------------------------------------------------------------
    public function createUser(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $role     = trim($_POST['role'] ?? 'user');
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm'] ?? '';
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
            if (!in_array($role, ['admin', 'user'], true)) {
                $errors[] = 'Invalid role selected.';
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

            // Duplicates
            if (empty($errors)) {
                if ($this->userModel->usernameExists($username)) {
                    $errors[] = 'Username is already taken.';
                }
                if ($this->userModel->emailExists($email)) {
                    $errors[] = 'Email is already registered.';
                }
            }

            if (!empty($errors)) {
                Session::flash('error', implode('|', $errors));
                Session::flash('old', $_POST);
                header("Location: {$this->baseUrl}/admin/users/create");
                exit;
            }

            $userId = $this->userModel->createOnBehalf($username, $email, $password, $role);

            if (!$userId) {
                Session::flash('error', 'Failed to create user. Please try again.');
                header("Location: {$this->baseUrl}/admin/users/create");
                exit;
            }

            Session::flash('success', 'User account created successfully.');
            header("Location: {$this->baseUrl}/admin/users");
            exit;
        }

        $flash_error = Session::getFlash('error');
        $old         = Session::getFlash('old') ?? [];
        require_once dirname(__DIR__) . '/views/admin/user_create.php';
    }

    // ------------------------------------------------------------------
    // Edit User Credentials on Behalf
    // ------------------------------------------------------------------
    public function editUser(int $id): void
    {
        $this->requireAdmin();

        $user = $this->userModel->findById($id);
        if (!$user) {
            Session::flash('error', 'User not found.');
            header("Location: {$this->baseUrl}/admin/users");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $role     = trim($_POST['role'] ?? 'user');
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm'] ?? '';
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
            if (!in_array($role, ['admin', 'user'], true)) {
                $errors[] = 'Invalid role selected.';
            }

            // If password is being changed
            if (!empty($password)) {
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
            }

            // Duplicates
            if (empty($errors)) {
                if ($this->userModel->usernameExistsExcept($username, $id)) {
                    $errors[] = 'Username is already taken.';
                }
                if ($this->userModel->emailExistsExcept($email, $id)) {
                    $errors[] = 'Email is already registered.';
                }
            }

            if (!empty($errors)) {
                Session::flash('error', implode('|', $errors));
                header("Location: {$this->baseUrl}/admin/users/{$id}/edit");
                exit;
            }

            $ok = $this->userModel->updateUserOnBehalf($id, $username, $email, $role, !empty($password) ? $password : null);

            if (!$ok) {
                Session::flash('error', 'Failed to update user. Please try again.');
                header("Location: {$this->baseUrl}/admin/users/{$id}/edit");
                exit;
            }

            // If admin updated their own username/email/role, update session
            if ($id === (int) Session::get('user_id')) {
                Session::set('user_username', $username);
                Session::set('user_email', $email);
                Session::set('user_role', $role);
                if ($role !== 'admin') {
                    // Admin downgraded themselves, redirect to dashboard
                    header("Location: {$this->baseUrl}/dashboard");
                    exit;
                }
            }

            Session::flash('success', 'User credentials updated successfully.');
            header("Location: {$this->baseUrl}/admin/users");
            exit;
        }

        $flash_error = Session::getFlash('error');
        $old         = $user;
        require_once dirname(__DIR__) . '/views/admin/user_edit.php';
    }

    // ------------------------------------------------------------------
    // Delete User
    // ------------------------------------------------------------------
    public function deleteUser(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/admin/users");
            exit;
        }

        if ($id === (int) Session::get('user_id')) {
            Session::flash('error', 'You cannot delete your own admin account.');
            header("Location: {$this->baseUrl}/admin/users");
            exit;
        }

        $ok = $this->userModel->delete($id);

        if (!$ok) {
            Session::flash('error', 'Failed to delete user. Please try again.');
        } else {
            Session::flash('success', 'User account and all associated subscriptions deleted successfully.');
        }

        header("Location: {$this->baseUrl}/admin/users");
        exit;
    }

    // ------------------------------------------------------------------
    // Subscriptions on Behalf of User
    // ------------------------------------------------------------------
    public function viewUserSubscriptions(int $userId): void
    {
        $this->requireAdmin();

        $user = $this->userModel->findById($userId);
        if (!$user) {
            Session::flash('error', 'User not found.');
            header("Location: {$this->baseUrl}/admin/users");
            exit;
        }

        $subscriptions = $this->subModel->getAll($userId);
        $flash_success = Session::getFlash('success');
        $flash_error   = Session::getFlash('error');

        require_once dirname(__DIR__) . '/views/admin/user_subscriptions.php';
    }

    public function createUserSubscription(int $userId): void
    {
        $this->requireAdmin();

        $user = $this->userModel->findById($userId);
        if (!$user) {
            Session::flash('error', 'User not found.');
            header("Location: {$this->baseUrl}/admin/users");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateSubscriptionInput($_POST);

            if (!empty($errors)) {
                Session::flash('error', implode('|', $errors));
                Session::flash('old', $_POST);
                header("Location: {$this->baseUrl}/admin/users/{$userId}/subscriptions/create");
                exit;
            }

            $data = [
                'user_id'        => $userId,
                'service_name'   => $this->sanitize($_POST['service_name']),
                'category'       => $this->sanitize($_POST['category'] ?? 'Other'),
                'cost'           => (float) $_POST['cost'],
                'billing_cycle'  => $_POST['billing_cycle'],
                'payment_method' => $this->sanitize($_POST['payment_method'] ?? 'Credit Card'),
                'start_date'     => $_POST['start_date'],
                'notes'          => $this->sanitize($_POST['notes'] ?? ''),
                'status'         => $_POST['status'] ?? 'active',
            ];

            $id = $this->subModel->create($data);

            if (!$id) {
                Session::flash('error', 'Failed to add subscription. Please try again.');
                header("Location: {$this->baseUrl}/admin/users/{$userId}/subscriptions/create");
                exit;
            }

            Session::flash('success', 'Subscription added successfully on behalf of the user!');
            header("Location: {$this->baseUrl}/admin/users/{$userId}/subscriptions");
            exit;
        }

        $flash_error = Session::getFlash('error');
        $old         = Session::getFlash('old') ?? [];
        require_once dirname(__DIR__) . '/views/admin/user_subscription_create.php';
    }

    public function editUserSubscription(int $userId, int $subId): void
    {
        $this->requireAdmin();

        $user = $this->userModel->findById($userId);
        if (!$user) {
            Session::flash('error', 'User not found.');
            header("Location: {$this->baseUrl}/admin/users");
            exit;
        }

        $subscription = $this->subModel->getById($subId, $userId);
        if (!$subscription) {
            Session::flash('error', 'Subscription not found.');
            header("Location: {$this->baseUrl}/admin/users/{$userId}/subscriptions");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateSubscriptionInput($_POST);

            if (!empty($errors)) {
                Session::flash('error', implode('|', $errors));
                Session::flash('old', $_POST);
                header("Location: {$this->baseUrl}/admin/users/{$userId}/subscriptions/{$subId}/edit");
                exit;
            }

            $data = [
                'service_name'   => $this->sanitize($_POST['service_name']),
                'category'       => $this->sanitize($_POST['category'] ?? 'Other'),
                'cost'           => (float) $_POST['cost'],
                'billing_cycle'  => $_POST['billing_cycle'],
                'payment_method' => $this->sanitize($_POST['payment_method'] ?? 'Credit Card'),
                'start_date'     => $_POST['start_date'],
                'notes'          => $this->sanitize($_POST['notes'] ?? ''),
                'status'         => $_POST['status'] ?? 'active',
            ];

            $ok = $this->subModel->update($subId, $userId, $data);

            if (!$ok) {
                Session::flash('error', 'Failed to update subscription. Please try again.');
                header("Location: {$this->baseUrl}/admin/users/{$userId}/subscriptions/{$subId}/edit");
                exit;
            }

            Session::flash('success', 'Subscription updated successfully on behalf of the user!');
            header("Location: {$this->baseUrl}/admin/users/{$userId}/subscriptions");
            exit;
        }

        $flash_error = Session::getFlash('error');
        $old         = Session::getFlash('old') ?? $subscription;
        require_once dirname(__DIR__) . '/views/admin/user_subscription_edit.php';
    }

    public function deleteUserSubscription(int $userId, int $subId): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/admin/users/{$userId}/subscriptions");
            exit;
        }

        // Verify the subscription belongs to this user
        $subscription = $this->subModel->getById($subId, $userId);
        if (!$subscription) {
            Session::flash('error', 'Subscription not found.');
            header("Location: {$this->baseUrl}/admin/users/{$userId}/subscriptions");
            exit;
        }

        $ok = $this->subModel->delete($subId);

        if (!$ok) {
            Session::flash('error', 'Failed to delete subscription. Please try again.');
        } else {
            Session::flash('success', 'Subscription deleted successfully on behalf of the user.');
        }

        header("Location: {$this->baseUrl}/admin/users/{$userId}/subscriptions");
        exit;
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------
    private function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    private function validateSubscriptionInput(array $post): array
    {
        $errors = [];

        if (empty(trim($post['service_name'] ?? ''))) {
            $errors[] = 'Service name is required.';
        }

        $cost = $post['cost'] ?? '';
        if (!is_numeric($cost) || (float) $cost <= 0) {
            $errors[] = 'Cost must be a positive number.';
        }

        $validCycles = array_keys(Subscription::CYCLES);
        if (!in_array($post['billing_cycle'] ?? '', $validCycles, true)) {
            $errors[] = 'Invalid billing cycle.';
        }

        $date = $post['start_date'] ?? '';
        if (empty($date) || !strtotime($date)) {
            $errors[] = 'A valid subscription start date is required.';
        }

        $validStatuses = ['active', 'paused', 'cancelled'];
        if (!in_array($post['status'] ?? '', $validStatuses, true)) {
            $errors[] = 'Invalid status.';
        }

        return $errors;
    }
}
