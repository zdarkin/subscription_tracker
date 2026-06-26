<?php
/**
 * DashboardController
 * Aggregates analytics data and renders the main dashboard view.
 */

require_once dirname(__DIR__) . '/models/Subscription.php';
require_once __DIR__ . '/Session.php';

class DashboardController
{
    private Subscription $subModel;

    public function __construct()
    {
        $this->subModel = new Subscription();
    }

    public function index(): void
    {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        $userId = (int) Session::get('user_id');

        $data = [
            'username'         => Session::get('user_username'),
            'monthly_spend'    => $this->subModel->getTotalMonthlySpend($userId),
            'annual_spend'     => $this->subModel->getTotalAnnualSpend($userId),
            'active_count'     => $this->subModel->getActiveCount($userId),
            'upcoming'         => $this->subModel->getUpcomingRenewals($userId, 7),
            'category_data'    => $this->subModel->getSpendByCategory($userId),
            'all_subs'         => $this->subModel->getAll($userId),
            'flash_success'    => Session::getFlash('success'),
            'flash_error'      => Session::getFlash('error'),
        ];

        require_once dirname(__DIR__) . '/views/dashboard.php';
    }
}
