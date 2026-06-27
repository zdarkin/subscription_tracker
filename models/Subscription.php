<?php

/**
 * Subscription Model
 * Handles all database interactions for the subscriptions table.
 */

require_once dirname(__DIR__) . '/config/database.php';

class Subscription
{
    private PDO $db;

    // Supported billing cycles and their monthly multiplier
    public const CYCLES = [
        'monthly'     => 1,
        'quarterly'   => 3,
        'semi-annual' => 6,
        'annual'      => 12,
    ];

    public const CATEGORIES = [
        'Software',
        'Entertainment',
        'Cloud Storage',
        'Productivity',
        'Security',
        'Communication',
        'Finance',
        'Health',
        'Education',
        'Other',
    ];

    public const PAYMENT_METHODS = [
        'Credit Card',
        'Debit Card',
        'PayPal',
        'GCash',
        'Maya',
        'Bank Transfer',
        'Other',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ------------------------------------------------------------------
    // CRUD
    // ------------------------------------------------------------------

    public static function calculateNextRenewal(string $startDate, string $billingCycle): string
    {
        $start = new DateTime($startDate);
        $now = new DateTime('today');

        if ($start >= $now) {
            return $start->format('Y-m-d');
        }

        $cycleMonths = [
            'monthly'     => 1,
            'quarterly'   => 3,
            'semi-annual' => 6,
            'annual'      => 12,
        ];
        $monthsToAdd = $cycleMonths[$billingCycle] ?? 1;

        $cycles = 0;
        while (true) {
            $year = (int)$start->format('Y');
            $month = (int)$start->format('n');
            $day = (int)$start->format('j');

            $month += $cycles * $monthsToAdd;

            // Normalize month and year
            $year += (int)floor(($month - 1) / 12);
            $month = (($month - 1) % 12) + 1;

            // Get the number of days in the target month
            $daysInTargetMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            // Cap the day to the last day of the target month
            $targetDay = min($day, $daysInTargetMonth);

            $targetDate = new DateTime(sprintf('%04d-%02d-%02d', $year, $month, $targetDay));

            if ($targetDate >= $now) {
                return $targetDate->format('Y-m-d');
            }

            $cycles++;
        }
    }

    public function getAll(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM subscriptions WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['next_renewal_date'] = self::calculateNextRenewal($row['start_date'], $row['billing_cycle']);
        }
        usort($rows, function ($a, $b) {
            return strtotime($a['next_renewal_date']) <=> strtotime($b['next_renewal_date']);
        });
        return $rows;
    }

    public function getById(int $id, int $userId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM subscriptions WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        if ($row) {
            $row['next_renewal_date'] = self::calculateNextRenewal($row['start_date'], $row['billing_cycle']);
        }
        return $row;
    }

    public function create(array $data): int|false
    {
        $stmt = $this->db->prepare(
            'INSERT INTO subscriptions
                (user_id, service_name, category, cost, billing_cycle, payment_method, start_date, notes, status)
             VALUES
                (:user_id, :service_name, :category, :cost, :billing_cycle, :payment_method, :start_date, :notes, :status)'
        );

        try {
            $stmt->execute([
                ':user_id'        => $data['user_id'],
                ':service_name'   => $data['service_name'],
                ':category'       => $data['category']       ?? 'Other',
                ':cost'           => $data['cost'],
                ':billing_cycle'  => $data['billing_cycle'],
                ':payment_method' => $data['payment_method'] ?? 'Credit Card',
                ':start_date'     => $data['start_date'],
                ':notes'          => $data['notes']          ?? null,
                ':status'         => $data['status']         ?? 'active',
            ]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('[Subscription::create] ' . $e->getMessage());
            return false;
        }
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE subscriptions SET
                service_name   = :service_name,
                category       = :category,
                cost           = :cost,
                billing_cycle  = :billing_cycle,
                payment_method = :payment_method,
                start_date     = :start_date,
                notes          = :notes,
                status         = :status
             WHERE id = :id AND user_id = :user_id'
        );

        try {
            return $stmt->execute([
                ':service_name'   => $data['service_name'],
                ':category'       => $data['category']       ?? 'Other',
                ':cost'           => $data['cost'],
                ':billing_cycle'  => $data['billing_cycle'],
                ':payment_method' => $data['payment_method'] ?? 'Credit Card',
                ':start_date'     => $data['start_date'],
                ':notes'          => $data['notes']          ?? null,
                ':status'         => $data['status']         ?? 'active',
                ':id'             => $id,
                ':user_id'        => $userId,
            ]);
        } catch (PDOException $e) {
            error_log('[Subscription::update] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete – callable only by admins (enforced at controller level).
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM subscriptions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // ------------------------------------------------------------------
    // Dashboard / Analytics
    // ------------------------------------------------------------------

    /**
     * Total monthly spend normalised across billing cycles.
     */
    public function getTotalMonthlySpend(int $userId): float
    {
        $subs  = $this->db->prepare(
            "SELECT cost, billing_cycle FROM subscriptions
             WHERE user_id = ? AND status = 'active'"
        );
        $subs->execute([$userId]);
        $rows  = $subs->fetchAll();

        $total = 0.0;
        foreach ($rows as $row) {
            $divisor = self::CYCLES[$row['billing_cycle']] ?? 1;
            $total  += (float) $row['cost'] / $divisor;
        }
        return round($total, 2);
    }

    /**
     * Subscriptions renewing within the next N days.
     */
    public function getUpcomingRenewals(int $userId, int $days = 7): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM subscriptions
             WHERE user_id = ? AND status = 'active'"
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        $upcoming = [];
        $today = new DateTime('today');
        $limitDate = (clone $today)->modify("+$days days");

        foreach ($rows as $row) {
            $next = self::calculateNextRenewal($row['start_date'], $row['billing_cycle']);
            $nextDate = new DateTime($next);

            if ($nextDate >= $today && $nextDate <= $limitDate) {
                $row['next_renewal_date'] = $next;
                $upcoming[] = $row;
            }
        }

        usort($upcoming, function ($a, $b) {
            return strtotime($a['next_renewal_date']) <=> strtotime($b['next_renewal_date']);
        });

        return $upcoming;
    }

    public function getActiveCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM subscriptions WHERE user_id = ? AND status = 'active'"
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Paused subscription count.
     */
    public function getPausedCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM subscriptions WHERE user_id = ? AND status = 'paused'"
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Cancelled subscription count.
     */
    public function getCancelledCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM subscriptions WHERE user_id = ? AND status = 'cancelled'"
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Total annual spend.
     */
    public function getTotalAnnualSpend(int $userId): float
    {
        return round($this->getTotalMonthlySpend($userId) * 12, 2);
    }

    /**
     * Spend by category for charting.
     */
    public function getSpendByCategory(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT category, SUM(cost / CASE billing_cycle
                WHEN 'monthly'     THEN 1
                WHEN 'quarterly'   THEN 3
                WHEN 'semi-annual' THEN 6
                WHEN 'annual'      THEN 12
                ELSE 1 END) AS monthly_spend
             FROM subscriptions
             WHERE user_id = ? AND status = 'active'
             GROUP BY category
             ORDER BY monthly_spend DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // ------------------------------------------------------------------
    // Email Worker Queries
    // ------------------------------------------------------------------

    /**
     * Get all active subscriptions renewing in exactly $daysAhead days, with user email.
     * Excludes already-alerted renewals for this milestone via the email_logs table.
     */
    public function getDueForAlert(int $daysAhead = 3): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, u.email AS user_email, u.username
             FROM subscriptions s
             JOIN users u ON s.user_id = u.id
             WHERE s.status = 'active'"
        );
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $due = [];
        $targetDateStr = date('Y-m-d', strtotime("+$daysAhead days"));

        foreach ($rows as $row) {
            $nextRenewal = self::calculateNextRenewal($row['start_date'], $row['billing_cycle']);
            if ($nextRenewal === $targetDateStr) {
                // Check if already alerted for this specific renewal date
                $checkStmt = $this->db->prepare(
                    "SELECT 1 FROM email_logs 
                     WHERE subscription_id = ? 
                       AND renewal_date = ? 
                       AND lead_days = ?"
                );
                $checkStmt->execute([$row['id'], $nextRenewal, $daysAhead]);
                if (!$checkStmt->fetchColumn()) {
                    $row['renewal_date'] = $nextRenewal; // set it so the worker can use $item['renewal_date']
                    $due[] = $row;
                }
            }
        }

        return $due;
    }

    /**
     * Log a sent alert to prevent duplicate emails for a specific milestone.
     */
    public function logAlert(int $subscriptionId, int $userId, string $renewalDate, int $leadDays): bool
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO email_logs (subscription_id, user_id, renewal_date, lead_days)
             VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$subscriptionId, $userId, $renewalDate, $leadDays]);
    }

    /**
     * Admin: Total subscriptions across all users.
     */
    public function getTotalCount(): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM subscriptions');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Admin: Total active subscriptions across all users.
     */
    public function getTotalActiveCount(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM subscriptions WHERE status = 'active'");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Admin: Total normalized monthly spend across all users.
     */
    public function getGlobalMonthlySpend(): float
    {
        $stmt = $this->db->prepare("SELECT cost, billing_cycle FROM subscriptions WHERE status = 'active'");
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $total = 0.0;
        foreach ($rows as $row) {
            $divisor = self::CYCLES[$row['billing_cycle']] ?? 1;
            $total  += (float) $row['cost'] / $divisor;
        }
        return round($total, 2);
    }
}
