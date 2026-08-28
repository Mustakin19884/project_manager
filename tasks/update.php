<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Only POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php");

    exit;

}


/*
|--------------------------------------------------------------------------
| Input
|--------------------------------------------------------------------------
*/

$id = (int)($_POST['id'] ?? 0);

$title = trim($_POST['title'] ?? '');

$project_id = (int)($_POST['project_id'] ?? 0);

$team_member_id = (int)($_POST['team_member_id'] ?? 0);

$description = trim($_POST['description'] ?? '');

$status = $_POST['status'] ?? 'pending';

$priority = $_POST['priority'] ?? 'medium';

$deadline = trim($_POST['deadline'] ?? '');

$progress = (int)($_POST['progress'] ?? 0);


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if ($id <= 0) {

    die("Invalid task ID.");

}


if ($title === '') {

    die("Task title is required.");

}


if ($project_id <= 0) {

    die("Please select a project.");

}


$allowed_status = [
    'pending',
    'in_progress',
    'on_hold',
    'completed',
    'cancelled'
];


$allowed_priority = [
    'low',
    'medium',
    'high',
    'urgent'
];


if (!in_array($status, $allowed_status, true)) {

    die("Invalid task status.");

}


if (!in_array($priority, $allowed_priority, true)) {

    die("Invalid task priority.");

}


$progress = max(
    0,
    min(100, $progress)
);


/*
|--------------------------------------------------------------------------
| Normalize Empty Values
|--------------------------------------------------------------------------
*/

$team_member_value = $team_member_id > 0
    ? $team_member_id
    : null;


$deadline_value = $deadline !== ''
    ? $deadline
    : null;


/*
|--------------------------------------------------------------------------
| Update Task
|--------------------------------------------------------------------------
*/

$sql = "

    UPDATE tasks

    SET

        project_id = ?,
        team_member_id = ?,
        title = ?,
        description = ?,
        status = ?,
        priority = ?,
        deadline = ?,
        progress = ?

    WHERE id = ?

";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


/*
|--------------------------------------------------------------------------
| Bind Parameters
|--------------------------------------------------------------------------
*/

$stmt->bind_param(
    "iisssssii",
    $project_id,
    $team_member_value,
    $title,
    $description,
    $status,
    $priority,
    $deadline_value,
    $progress,
    $id
);


/*
|--------------------------------------------------------------------------
| Execute
|--------------------------------------------------------------------------
*/

if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?success=updated"
    );

    exit;

}


$error = $stmt->error;

$stmt->close();


die(
    "Failed to update task: " .
    htmlspecialchars($error)
);

?>