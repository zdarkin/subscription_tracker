<?php
require_once dirname(__DIR__) . '/config/database.php';

try {
    $db = Database::getInstance();
    
    // Check if full_name column exists
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'full_name'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo "Adding full_name column to users table...\n";
        $db->exec("ALTER TABLE users ADD COLUMN full_name VARCHAR(100) NOT NULL AFTER username");
        echo "Column added successfully.\n";
    } else {
        echo "full_name column already exists.\n";
    }
    
    // Seed/Update names for mock data users
    $names = [
        'admin' => 'System Administrator',
        'alice_smith' => 'Alice Smith',
        'bob_jones' => 'Bob Jones',
        'charlie_brown' => 'Charlie Brown',
        'diana_prince' => 'Diana Prince',
        'james_smith' => 'James Smith',
        'mary_johnson' => 'Mary Johnson',
        'john_williams' => 'John Williams',
        'patricia_brown' => 'Patricia Brown',
        'robert_jones' => 'Robert Jones',
        'jennifer_garcia' => 'Jennifer Garcia',
        'michael_miller' => 'Michael Miller',
        'linda_davis' => 'Linda Davis',
        'william_rodriguez' => 'William Rodriguez',
        'elizabeth_martinez' => 'Elizabeth Martinez',
        'david_hernandez' => 'David Hernandez',
        'barbara_lopez' => 'Barbara Lopez',
        'richard_gonzalez' => 'Richard Gonzalez',
        'susan_wilson' => 'Susan Wilson',
        'joseph_anderson' => 'Joseph Anderson',
        'jessica_thomas' => 'Jessica Thomas',
        'thomas_taylor' => 'Thomas Taylor',
        'sarah_moore' => 'Sarah Moore',
        'charles_jackson' => 'Charles Jackson',
        'karen_martin' => 'Karen Martin',
    ];
    
    $updateStmt = $db->prepare("UPDATE users SET full_name = ? WHERE username = ?");
    foreach ($names as $username => $fullName) {
        $updateStmt->execute([$fullName, $username]);
    }
    
    // Update any user that might have empty full_name to their username
    $db->exec("UPDATE users SET full_name = username WHERE full_name = '' OR full_name IS NULL");
    
    echo "Database upgrade and mock data seeding completed successfully.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
