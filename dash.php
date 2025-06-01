<?php
// journey_dashboard.php
// A web-based dashboard to view user journey progress and reminder status.
// Updated for simplified journey status system, RELATIVE PATHS, and displaying journal answers.

// --- Configuration ---
$db_path = 'journey_data.sqlite'; // RELATIVE PATH to SQLite database

// --- Basic Password Protection ---
$CORRECT_PASSWORD = "4321"; // Password set as per user request

session_start();

// Logout logic
if (isset($_GET["logout"])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: dash.php"); // Redirect to clear GET param and force login screen
    exit;
}

// Login check and processing
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    if (isset($_POST["password"])) {
        if (trim($_POST["password"]) === $CORRECT_PASSWORD) {
            $_SESSION["loggedin"] = true;
            session_regenerate_id(true);
            header("Location: dash.php"); 
            exit;
        } else {
            $login_error = "Invalid password.";
        }
    }
    echo "<style>body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 90vh; background-color: #f4f4f4; } form { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); } input { display: block; margin-bottom: 10px; padding: 8px; width: 200px; } button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; } .error { color: red; margin-bottom: 10px; }</style>";
    echo "<form method=\"post\">";
    if (isset($login_error)) {
        echo "<p class=\"error\">" . htmlspecialchars($login_error) . "</p>";
    }
    echo "<h2>Login to Journey Dashboard</h2>";
    echo "<label for=\"password\">Password:</label><input type=\"password\" name=\"password\" id=\"password\" required>";
    echo "<button type=\"submit\">Login</button>";
    echo "</form>";
    exit;
}

// --- Helper Function to Calculate Reminder Due Status (Simplified Logic) ---
function calculateSimplifiedReminderDueStatus($current_status, $last_activity_timestamp_str) {
    if (empty($last_activity_timestamp_str)) return "N/A (No activity)";
    try {
        $last_activity_dt = new DateTime($last_activity_timestamp_str, new DateTimeZone("UTC"));
        $now_dt = new DateTime("now", new DateTimeZone("UTC"));
        $diff_seconds = $now_dt->getTimestamp() - $last_activity_dt->getTimestamp();
        $diff_hours = $diff_seconds / 3600;
        $diff_days = $diff_hours / 24;
    } catch (Exception $e) {
        return "Error parsing date";
    }
    $reminder_due_message = "Up to date";
    $next_step_target = "";
    switch ($current_status) {
        case "signed_up": $next_step_target = "Day 1 Journal"; break;
        case "day1_submitted": $next_step_target = "Day 2 Journal"; break;
        case "day2_submitted": $next_step_target = "Day 3 Journal"; break;
        case "day3_submitted": $next_step_target = "Book Call"; break;
        case "call_booked": case "unsubscribed": return "<span style=\"color:green;\">Journey Complete/Ended</span>";
        default: return "<span style=\"color:grey;\">Unknown Status</span>";
    }
    if ($diff_hours >= 6) {
        if ($diff_hours >= 24) {
            $reminder_due_message = "<strong style=\"color:red;\">Daily reminder for {$next_step_target} likely active</strong> (since " . round($diff_days, 1) . " days ago)";
        } else {
            $reminder_due_message = "<strong style=\"color:orange;\">Initial 6hr reminder for {$next_step_target} likely sent</strong> (since " . round($diff_hours, 1) . " hrs ago)";
        }
    } else {
        $reminder_due_message = "Reminder for {$next_step_target} pending (in " . round(6 - $diff_hours, 1) . " hrs)";
    }
    return $reminder_due_message;
}

// Function to fetch journal answers for a user
function getJournalAnswers($pdo, $user_tracker_id) {
    $stmt = $pdo->prepare("SELECT day_number, question_identifier, answer_text, submission_timestamp FROM journal_answers WHERE user_tracker_id = :user_tracker_id ORDER BY submission_timestamp DESC, day_number ASC, question_identifier ASC");
    $stmt->execute([":user_tracker_id" => $user_tracker_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Journey Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f9f9f9; color: #333; }
        h1 { text-align: center; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 10px 12px; text-align: left; font-size: 0.9em; vertical-align: top; }
        th { background-color: #f0f0f0; color: #555; }
        tr:nth-child(even) { background-color: #fdfdfd; }
        .logout-link { display: block; text-align: right; margin-bottom: 15px; }
        .logout-link a { color: #007bff; text-decoration: none; }
        .logout-link a:hover { text-decoration: underline; }
        .status-signed_up { color: #007bff; }
        .status-day1_submitted, .status-day2_submitted, .status-day3_submitted { color: #28a745; }
        .status-call_booked { color: #17a2b8; }
        .status-unsubscribed { color: #6c757d; text-decoration: line-through; }
        .journal-answers-container { margin-top: 5px; padding-left: 15px; border-left: 2px solid #eee; font-size: 0.9em; }
        .journal-answers-container p { margin: 2px 0; }
        .journal-answers-container strong { color: #555; }
        .no-answers { color: #999; font-style: italic; }
        .toggle-answers { cursor: pointer; color: #007bff; font-size: 0.8em; text-decoration: underline; margin-left: 10px; }
    </style>
    <script>
        function toggleAnswers(userId) {
            var answersDiv = document.getElementById('answers-' + userId);
            var toggleLink = document.getElementById('toggle-' + userId);
            if (answersDiv.style.display === 'none' || answersDiv.style.display === '') {
                answersDiv.style.display = 'block';
                toggleLink.textContent = 'Hide Answers';
            } else {
                answersDiv.style.display = 'none';
                toggleLink.textContent = 'Show Answers';
            }
        }
    </script>
</head>
<body>
    <h1>User Journey Dashboard</h1>
    <div class="logout-link"><a href="?logout=true">Logout</a></div>

    <table>
        <thead>
            <tr>
                <th>User Email</th>
                <th>V Variable</th>
                <th>Video Title</th>
                <th>Current Journey Status</th>
                <th>Last Activity</th>
                <th>Subscribed (ML)</th>
                <th>Reminder Status (Inferred)</th>
                <th>First Seen</th>
                <th>Journal Answers</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                $pdo = new PDO("sqlite:" . $db_path);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $pdo->query("SELECT user_tracker_id, email, v_variable, video_title, current_journey_status, is_subscribed_to_mailerlite, first_seen_timestamp, last_activity_timestamp FROM user_journey_progress ORDER BY last_activity_timestamp DESC");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($users) > 0) {
                    foreach ($users as $user) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($user["email"]) . "</td>";
                        echo "<td>" . htmlspecialchars($user["v_variable"] ?: "N/A") . "</td>";
                        echo "<td>" . htmlspecialchars($user["video_title"] ?: "N/A") . "</td>";
                        echo "<td class=\"status-" . htmlspecialchars(strtolower($user["current_journey_status"])) . "\">" . htmlspecialchars($user["current_journey_status"]) . "</td>";
                        echo "<td>" . htmlspecialchars($user["last_activity_timestamp"]) . "</td>";
                        echo "<td>" . ($user["is_subscribed_to_mailerlite"] ? "Yes" : "<span style=\"color:red;\">No</span>") . "</td>";
                        echo "<td>" . calculateSimplifiedReminderDueStatus($user["current_journey_status"], $user["last_activity_timestamp"]) . "</td>";
                        echo "<td>" . htmlspecialchars($user["first_seen_timestamp"]) . "</td>";
                        
                        // Fetch and display journal answers
                        echo "<td>";
                        $journal_answers = getJournalAnswers($pdo, $user["user_tracker_id"]);
                        if (count($journal_answers) > 0) {
                            echo "<span class=\"toggle-answers\" id=\"toggle-" . htmlspecialchars($user["user_tracker_id"]) . "\" onclick=\"toggleAnswers('" . htmlspecialchars($user["user_tracker_id"]) . "')\">Show Answers</span>";
                            echo "<div id=\"answers-" . htmlspecialchars($user["user_tracker_id"]) . "\" class=\"journal-answers-container\" style=\"display:none;\">";
                            $current_day_for_grouping = null;
                            foreach ($journal_answers as $answer) {
                                if ($current_day_for_grouping !== $answer["day_number"]) {
                                    if ($current_day_for_grouping !== null) echo "<br>"; // Add space between days
                                    echo "<strong>Day " . htmlspecialchars(ucfirst(str_replace("day", "", $answer["day_number"]))) . "</strong> (" . htmlspecialchars($answer["submission_timestamp"]) . "):<br>";
                                    $current_day_for_grouping = $answer["day_number"];
                                }
                                echo "<em>" . htmlspecialchars($answer["question_identifier"]) . ":</em> " . nl2br(htmlspecialchars($answer["answer_text"])) . "<br>";
                            }
                            echo "</div>";
                        } else {
                            echo "<span class=\"no-answers\">No journal entries.</span>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan=\"9\" style=\"text-align:center;\">No users found in the journey progress table.</td></tr>";
                }
            } catch (PDOException $e) {
                echo "<tr><td colspan=\"9\" style=\"text-align:center; color:red;\">Database Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>

