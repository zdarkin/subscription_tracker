-- =============================================================
-- Recurring Subscription Tracking System
-- Sample / Test Data Seed Script
-- =============================================================

USE subscription_tracker;

-- Temporarily disable foreign keys to make insertion clean
SET FOREIGN_KEY_CHECKS = 0;

-- Clean up previous test users and their related data
DELETE FROM users WHERE email LIKE '%@example.com';

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Insert Test Users
-- Passwords:
-- 'admin123'   -> $2y$12$dE1iUWUEl9QOPXsj4zdBBu7TbcaDvcJkdPfXnB9WFIDuUGwQwSf4C
-- 'password123' -> $2y$12$7FRYI1TUn1.UKRqpBRQjae68eKyafgdL8t5k80vH0UwdNa0sOZEEy

INSERT INTO users (id, username, email, password, role, created_at) VALUES
(1001, 'john_admin', 'john.admin@example.com', '$2y$12$dE1iUWUEl9QOPXsj4zdBBu7TbcaDvcJkdPfXnB9WFIDuUGwQwSf4C', 'admin', NOW()),
(1002, 'alice_smith', 'alice@example.com', '$2y$12$7FRYI1TUn1.UKRqpBRQjae68eKyafgdL8t5k80vH0UwdNa0sOZEEy', 'user', NOW()),
(1003, 'bob_jones', 'bob@example.com', '$2y$12$7FRYI1TUn1.UKRqpBRQjae68eKyafgdL8t5k80vH0UwdNa0sOZEEy', 'user', NOW()),
(1004, 'charlie_brown', 'charlie@example.com', '$2y$12$7FRYI1TUn1.UKRqpBRQjae68eKyafgdL8t5k80vH0UwdNa0sOZEEy', 'user', NOW()),
(1005, 'diana_prince', 'diana@example.com', '$2y$12$7FRYI1TUn1.UKRqpBRQjae68eKyafgdL8t5k80vH0UwdNa0sOZEEy', 'user', NOW());

-- 2. Insert Test Subscriptions
-- Includes active, paused, and cancelled services with varying billing cycles,
-- categories, and renewal dates relative to the current date.
INSERT INTO subscriptions (user_id, service_name, category, cost, billing_cycle, payment_method, renewal_date, notes, status, created_at) VALUES
-- Subscriptions for john_admin (1001)
(1001, 'Netflix Premium', 'Entertainment', 549.00, 'monthly', 'Credit Card', DATE_ADD(CURRENT_DATE, INTERVAL 2 DAY), 'Shared family account', 'active', NOW()),
(1001, 'Spotify Duo', 'Entertainment', 149.00, 'monthly', 'GCash', DATE_ADD(CURRENT_DATE, INTERVAL 5 DAY), 'Duo plan with spouse', 'active', NOW()),
(1001, 'AWS Hosting', 'Development', 1250.00, 'monthly', 'Credit Card', DATE_ADD(CURRENT_DATE, INTERVAL 12 DAY), 'Personal server hosting', 'paused', NOW()),
(1001, 'ChatGPT Plus', 'Utilities', 1140.00, 'monthly', 'Paypal', DATE_ADD(CURRENT_DATE, INTERVAL 20 DAY), 'AI assistant tool', 'active', NOW()),

-- Subscriptions for alice_smith (1002)
(1002, 'Amazon Prime Video', 'Entertainment', 150.00, 'monthly', 'Credit Card', DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), 'Included with delivery prime', 'active', NOW()),
(1002, 'YouTube Premium', 'Entertainment', 239.00, 'monthly', 'Maya', DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY), 'Family plan', 'active', NOW()),
(1002, 'Github Copilot', 'Development', 570.00, 'monthly', 'Credit Card', DATE_ADD(CURRENT_DATE, INTERVAL 15 DAY), 'Coding assistant', 'active', NOW()),
(1002, 'Adobe Creative Cloud', 'Work', 2900.00, 'monthly', 'Credit Card', DATE_ADD(CURRENT_DATE, INTERVAL 25 DAY), 'Photoshop and Premiere Pro tools', 'active', NOW()),
(1002, 'Gym Membership', 'Other', 1800.00, 'monthly', 'Cash', DATE_ADD(CURRENT_DATE, INTERVAL 10 DAY), 'Local fitness gym fee', 'paused', NOW()),

-- Subscriptions for bob_jones (1003)
(1003, 'Laracasts', 'Education', 850.00, 'monthly', 'PayPal', DATE_ADD(CURRENT_DATE, INTERVAL 4 DAY), 'PHP tutorials learning platform', 'active', NOW()),
(1003, 'Dropbox Plus', 'Utilities', 6800.00, 'annual', 'Credit Card', DATE_ADD(CURRENT_DATE, INTERVAL 45 DAY), 'Cloud storage backup storage', 'active', NOW()),
(1003, 'Medium Premium', 'Education', 285.00, 'monthly', 'Credit Card', DATE_ADD(CURRENT_DATE, INTERVAL 18 DAY), 'Tech articles publisher access', 'cancelled', NOW()),

-- Subscriptions for charlie_brown (1004)
(1004, 'Spotify Premium', 'Entertainment', 129.00, 'monthly', 'GCash', DATE_ADD(CURRENT_DATE, INTERVAL 2 DAY), 'Solo music streaming', 'active', NOW()),
(1004, 'Microsoft 365', 'Work', 3499.00, 'annual', 'Debit Card', DATE_ADD(CURRENT_DATE, INTERVAL 90 DAY), 'Word/Excel cloud suite', 'active', NOW()),
(1004, 'Slack Pro', 'Work', 450.00, 'monthly', 'Credit Card', DATE_ADD(CURRENT_DATE, INTERVAL 8 DAY), 'Team chat collaboration tool', 'active', NOW()),

-- Subscriptions for diana_prince (1005)
(1005, 'Apple One', 'Entertainment', 375.00, 'monthly', 'Credit Card', DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), 'iCloud + Music bundle', 'active', NOW()),
(1005, 'Canva Pro', 'Work', 299.00, 'monthly', 'GCash', DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY), 'Design software subscription', 'active', NOW()),
(1005, 'Zoom Pro', 'Utilities', 850.00, 'monthly', 'Paypal', DATE_ADD(CURRENT_DATE, INTERVAL 28 DAY), 'Video conferences', 'paused', NOW());
