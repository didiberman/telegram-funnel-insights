<?php
// setup_database.php
// This script creates the SQLite database and the necessary tables if they don't already exist.
// RELATIVE PATHS and syntax error fixed.

// --- Configuration ---
$db_path = 'journey_data.sqlite'; // RELATIVE PATH

$output_messages = [];

try {
    // Create a new PDO instance (this will create the database file if it doesn't exist)
    $pdo = new PDO('sqlite:' . $db_path);
    $output_messages[] = "Successfully connected to/created database file at: {$db_path}";

    // Set error mode to exceptions for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- Create user_journey_progress table ---
    // Added video_title column
    $sql_user_journey_progress = "
    CREATE TABLE IF NOT EXISTS user_journey_progress (
        user_tracker_id TEXT PRIMARY KEY,
        email TEXT UNIQUE,
        v_variable TEXT,
        video_title TEXT, 
        current_journey_status TEXT NOT NULL,
        is_subscribed_to_mailerlite INTEGER NOT NULL DEFAULT 1,
        first_seen_timestamp TEXT NOT NULL,
        last_activity_timestamp TEXT NOT NULL
    );
    ";
    $pdo->exec($sql_user_journey_progress);
    $output_messages[] = "'user_journey_progress' table checked/created successfully (ensured video_title column exists).";

    // --- Alter table to add video_title if it doesn't exist (for existing databases) ---
    try {
        // Check if the column exists first
        $result = $pdo->query("PRAGMA table_info(user_journey_progress);");
        $columns = $result->fetchAll(PDO::FETCH_COLUMN, 1); // Get column names
        if (!in_array('video_title', $columns)) {
            $pdo->exec("ALTER TABLE user_journey_progress ADD COLUMN video_title TEXT;");
            $output_messages[] = "Added 'video_title' column to existing 'user_journey_progress' table.";
        }
    } catch (PDOException $e) {
        // This might fail if the table doesn't exist yet, which is fine as CREATE TABLE handles it.
        // Or if altering fails for other reasons, it will be logged.
        $output_messages[] = "Notice: Could not alter 'user_journey_progress' to add 'video_title' (may already exist or table not yet created): " . $e->getMessage();
    }

    // --- Create journal_answers table ---
    $sql_journal_answers = "
    CREATE TABLE IF NOT EXISTS journal_answers (
        answer_id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_tracker_id TEXT NOT NULL,
        v_variable TEXT,
        day_number TEXT NOT NULL,
        question_identifier TEXT NOT NULL,
        answer_text TEXT NOT NULL,
        submission_timestamp TEXT NOT NULL,
        FOREIGN KEY (user_tracker_id) REFERENCES user_journey_progress(user_tracker_id)
    );
    ";
    $pdo->exec($sql_journal_answers);
    $output_messages[] = "'journal_answers' table checked/created successfully.";

    // --- Create indexes for better performance (optional but recommended) ---
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_journal_answers_user_tracker_id ON journal_answers (user_tracker_id);");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_journal_answers_day_number ON journal_answers (day_number);");
    $output_messages[] = "Indexes for 'journal_answers' table checked/created.";

    $output_messages[] = "<br><strong>Database setup complete!</strong> You can now remove or secure this script.";

} catch (PDOException $e) {
    $output_messages[] = "<strong style='color:red;'>Database Error: " . $e->getMessage() . "</strong>";
    // You might want to log this error to a file in a real application
}

// Output messages to the browser
echo implode("<br>\n", $output_messages);

?>
<p><strong>Important Security Note:</strong> Once the database and tables are created successfully, you should remove this <code>setup_database.php</code> file from your server or make it inaccessible from the web to prevent unauthorized execution.</p>
<p>If you re-run this script, it will not harm existing data as it uses 'CREATE TABLE IF NOT EXISTS' and attempts to add columns gracefully.</p>

