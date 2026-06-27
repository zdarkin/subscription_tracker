<?php
/**
 * User Model
 * Handles all database interactions for the users table.
 */

require_once dirname(__DIR__) . '/config/database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ------------------------------------------------------------------
    // Find Methods
    // ------------------------------------------------------------------

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT id, username, full_name, email, role, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findByUsername(string $username): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findByEmailOrUsername(string $identifier): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch();
    }

    // ------------------------------------------------------------------
    // Auth Methods
    // ------------------------------------------------------------------

    /**
     * Register a new user.
     * @return int|false  New user ID or false on failure.
     */
    public function create(string $username, string $full_name, string $email, string $plainPassword): int|false
    {
        $hash = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $this->db->prepare(
            'INSERT INTO users (username, full_name, email, password, role) VALUES (?, ?, ?, ?, ?)'
        );

        try {
            $stmt->execute([$username, $full_name, $email, $hash, 'user']);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('[User::create] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify credentials and return user row on success.
     */
    public function authenticate(string $identifier, string $plainPassword): array|false
    {
        $user = $this->findByEmailOrUsername($identifier);
        if (!$user) {
            return false;
        }

        if (!password_verify($plainPassword, $user['password'])) {
            return false;
        }

        // Re-hash if the cost factor has changed
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $this->updatePassword($user['id'], $plainPassword);
        }

        return $user;
    }

    /**
     * Update a user's password (used for rehash and profile change).
     */
    public function updatePassword(int $id, string $plainPassword): bool
    {
        $hash = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
        return $stmt->execute([$hash, $id]);
    }

    /**
     * Update profile details.
     */
    public function updateProfile(int $id, string $username, string $full_name, string $email): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET username = ?, full_name = ?, email = ? WHERE id = ?');
        return $stmt->execute([$username, $full_name, $email, $id]);
    }

    // ------------------------------------------------------------------
    // Validation Helpers
    // ------------------------------------------------------------------

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        return (bool) $stmt->fetch();
    }

    public function emailExistsExcept(string $email, int $excludeId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
        $stmt->execute([$email, $excludeId]);
        return (bool) $stmt->fetch();
    }

    public function usernameExistsExcept(string $username, int $excludeId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1');
        $stmt->execute([$username, $excludeId]);
        return (bool) $stmt->fetch();
    }

    // ------------------------------------------------------------------
    // Admin / User Management Queries
    // ------------------------------------------------------------------

    public function getAll(): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.username, u.full_name, u.email, u.role, u.created_at, COUNT(s.id) AS subscription_count
             FROM users u
             LEFT JOIN subscriptions s ON u.id = s.user_id
             GROUP BY u.id
             ORDER BY u.created_at DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCount(): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getRecentUsers(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.username, u.full_name, u.email, u.role, u.created_at, COUNT(s.id) AS subscription_count
             FROM users u
             LEFT JOIN subscriptions s ON u.id = s.user_id
             GROUP BY u.id
             ORDER BY u.created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function searchUsers(string $query): array
    {
        $term = '%' . $query . '%';
        $stmt = $this->db->prepare(
            'SELECT u.id, u.username, u.full_name, u.email, u.role, u.created_at, COUNT(s.id) AS subscription_count
             FROM users u
             LEFT JOIN subscriptions s ON u.id = s.user_id
             WHERE u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?
             GROUP BY u.id
             ORDER BY u.created_at DESC'
        );
        $stmt->execute([$term, $term, $term]);
        return $stmt->fetchAll();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function getUserWithStats(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.username, u.full_name, u.email, u.role, u.created_at, COUNT(s.id) AS subscription_count
             FROM users u
             LEFT JOIN subscriptions s ON u.id = s.user_id
             WHERE u.id = ?
             GROUP BY u.id'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createOnBehalf(string $username, string $full_name, string $email, string $plainPassword, string $role): int|false
    {
        $hash = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, full_name, email, password, role) VALUES (?, ?, ?, ?, ?)'
        );
        try {
            $stmt->execute([$username, $full_name, $email, $hash, $role]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('[User::createOnBehalf] ' . $e->getMessage());
            return false;
        }
    }

    public function updateUserOnBehalf(int $id, string $username, string $full_name, string $email, string $role, ?string $plainPassword = null): bool
    {
        if (!empty($plainPassword)) {
            $hash = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $this->db->prepare('UPDATE users SET username = ?, full_name = ?, email = ?, role = ?, password = ? WHERE id = ?');
            return $stmt->execute([$username, $full_name, $email, $role, $hash, $id]);
        } else {
            $stmt = $this->db->prepare('UPDATE users SET username = ?, full_name = ?, email = ?, role = ? WHERE id = ?');
            return $stmt->execute([$username, $full_name, $email, $role, $id]);
        }
    }
}
