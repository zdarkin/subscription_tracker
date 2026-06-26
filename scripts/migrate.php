<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    
    // Check if the column is already renamed
    $stmt = $db->query("SHOW COLUMNS FROM subscriptions LIKE 'start_date'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        // Rename column from renewal_date to start_date
        $db->exec("ALTER TABLE subscriptions CHANGE renewal_date start_date DATE NOT NULL");
        echo "Successfully altered subscriptions table: renamed 'renewal_date' to 'start_date'.\n";
    } else {
        echo "Column 'start_date' already exists. Migration skipped.\n";
    }
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
