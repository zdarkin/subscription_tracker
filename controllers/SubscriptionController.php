<?php

/**
 * SubscriptionController
 * Handles CRUD operations for user subscriptions.
 */

require_once dirname(__DIR__) . '/models/Subscription.php';
require_once __DIR__ . '/Session.php';

class SubscriptionController
{
    private Subscription $subModel;
    private string $baseUrl = '';

    public function __construct()
    {
        $this->subModel = new Subscription();
    }

    private function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            header("Location: {$this->baseUrl}/login");
            exit;
        }
    }

    // ------------------------------------------------------------------
    // List All
    // ------------------------------------------------------------------
    public function index(): void
    {
        $this->requireAuth();
        $userId = (int) Session::get('user_id');

        $subscriptions = $this->subModel->getAll($userId);
        $flash_success = Session::getFlash('success');
        $flash_error   = Session::getFlash('error');

        require_once dirname(__DIR__) . '/views/subscriptions/index.php';
    }

    // ------------------------------------------------------------------
    // Show Details
    // ------------------------------------------------------------------
    public function show(int $id): void
    {
        $this->requireAuth();
        $userId = (int) Session::get('user_id');

        $subscription = $this->subModel->getById($id, $userId);

        if (!$subscription) {
            Session::flash('error', 'Subscription not found or access denied.');
            header("Location: {$this->baseUrl}/subscriptions");
            exit;
        }

        $flash_success = Session::getFlash('success');
        $flash_error   = Session::getFlash('error');

        require_once dirname(__DIR__) . '/views/subscriptions/show.php';
    }

    // ------------------------------------------------------------------
    // Create Form
    // ------------------------------------------------------------------
    public function create(): void
    {
        $this->requireAuth();
        $flash_error = Session::getFlash('error');
        $old         = Session::getFlash('old') ?? [];

        require_once dirname(__DIR__) . '/views/subscriptions/create.php';
    }

    // ------------------------------------------------------------------
    // Store
    // ------------------------------------------------------------------
    public function store(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/subscriptions/create");
            exit;
        }

        $errors = $this->validateInput($_POST);

        if (!empty($errors)) {
            Session::flash('error', implode('|', $errors));
            Session::flash('old',   $_POST);
            header("Location: {$this->baseUrl}/subscriptions/create");
            exit;
        }

        $data = [
            'user_id'        => (int) Session::get('user_id'),
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
            header("Location: {$this->baseUrl}/subscriptions/create");
            exit;
        }

        Session::flash('success', 'Subscription added successfully!');
        header("Location: {$this->baseUrl}/subscriptions");
        exit;
    }

    // ------------------------------------------------------------------
    // Edit Form
    // ------------------------------------------------------------------
    public function edit(int $id): void
    {
        $this->requireAuth();
        $userId = (int) Session::get('user_id');

        $subscription = $this->subModel->getById($id, $userId);

        if (!$subscription) {
            Session::flash('error', 'Subscription not found or access denied.');
            header("Location: {$this->baseUrl}/subscriptions");
            exit;
        }

        $flash_error = Session::getFlash('error');
        $old         = Session::getFlash('old') ?? $subscription;

        require_once dirname(__DIR__) . '/views/subscriptions/edit.php';
    }

    // ------------------------------------------------------------------
    // Update
    // ------------------------------------------------------------------
    public function update(int $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/subscriptions");
            exit;
        }

        $userId = (int) Session::get('user_id');
        $errors = $this->validateInput($_POST);

        if (!empty($errors)) {
            Session::flash('error', implode('|', $errors));
            Session::flash('old',   $_POST);
            header("Location: {$this->baseUrl}/subscriptions/edit/{$id}");
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

        $ok = $this->subModel->update($id, $userId, $data);

        if (!$ok) {
            Session::flash('error', 'Update failed. Please try again.');
            header("Location: {$this->baseUrl}/subscriptions/edit/{$id}");
            exit;
        }

        Session::flash('success', 'Subscription updated successfully!');
        header("Location: {$this->baseUrl}/subscriptions");
        exit;
    }

    // ------------------------------------------------------------------
    // Delete
    // ------------------------------------------------------------------
    public function delete(int $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/subscriptions");
            exit;
        }

        $userId = (int) Session::get('user_id');

        // Check ownership
        $subscription = $this->subModel->getById($id, $userId);
        if (!$subscription) {
            Session::flash('error', 'Subscription not found or access denied.');
            header("Location: {$this->baseUrl}/subscriptions");
            exit;
        }

        $ok = $this->subModel->delete($id);

        if (!$ok) {
            Session::flash('error', 'Deletion failed. Please try again.');
        } else {
            Session::flash('success', 'Subscription deleted successfully.');
        }

        header("Location: {$this->baseUrl}/subscriptions");
        exit;
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------
    private function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    private function validateInput(array $post): array
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
