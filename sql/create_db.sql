-- =============================================================
-- Recurring Subscription Tracking System
-- Database Schema
-- =============================================================

CREATE DATABASE IF NOT EXISTS subscription_tracker
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE subscription_tracker;

-- ---------------------------------------------------------
-- Table: users
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    username     VARCHAR(50)     NOT NULL,
    full_name    VARCHAR(100)    NOT NULL,
    email        VARCHAR(150)    NOT NULL,
    password     VARCHAR(255)    NOT NULL,
    role         ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email    (email),
    UNIQUE KEY uq_users_username (username)
);

-- ---------------------------------------------------------
-- Table: subscriptions
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscriptions (
    id              INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED        NOT NULL,
    service_name    VARCHAR(100)        NOT NULL,
    category        VARCHAR(50)         NULL DEFAULT 'Other',
    cost            DECIMAL(10,2)       NOT NULL,
    billing_cycle   ENUM('monthly','quarterly','semi-annual','annual') NOT NULL DEFAULT 'monthly',
    payment_method  VARCHAR(80)         NOT NULL DEFAULT 'Credit Card',
    start_date      DATE                NOT NULL,
    notes           TEXT                NULL,
    status          ENUM('active','paused','cancelled') NOT NULL DEFAULT 'active',
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_subscriptions_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_subscriptions_start  (start_date),
    INDEX idx_subscriptions_user   (user_id),
    INDEX idx_subscriptions_status (status)
);

-- ---------------------------------------------------------
-- Table: email_logs  (tracks sent alerts, prevents duplicates)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_logs (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    subscription_id INT UNSIGNED    NOT NULL,
    user_id         INT UNSIGNED    NOT NULL,
    sent_at         TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    renewal_date    DATE            NOT NULL,
    lead_days       INT UNSIGNED    NOT NULL DEFAULT 3,
    PRIMARY KEY (id),
    UNIQUE KEY uq_alert_per_renewal_days (subscription_id, renewal_date, lead_days),
    INDEX idx_email_logs_sub (subscription_id),
    CONSTRAINT fk_email_logs_sub
        FOREIGN KEY (subscription_id) REFERENCES subscriptions (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_email_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);
