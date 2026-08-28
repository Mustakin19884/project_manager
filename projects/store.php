<?php

require_once "../config/auth.php";
require_once "../config/db.php";


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php");
    exit;
}


$name = trim($_POST['name'] ?? '');

$client_id = !empty($_POST['client_id'])
    ? (int)$_POST['client_id']
    : null;

$manager_id = !empty($_POST['manager_id'])
    ? (int)$_POST['manager_id']
    : null;

$description = trim($_POST['description'] ?? '');

$start_date = !empty($_POST['start_date'])
    ? $_POST['start_date']
    : null;

$deadline = !empty($_POST['deadline'])
    ? $_POST['deadline']
    : null;

$budget = (float)($_POST['budget'] ?? 0);

$priority = $_POST['priority'] ?? 'medium';

$status = $_POST['status'] ?? 'planning';


if ($name === '') {

    header(
        "Location: create.php?error="
        . urlencode("Project name is required.")
    );

    exit;
}


if (!in_array($priority, [
    'low',
    'medium',
    'high',
    'urgent'
], true)) {

    $priority = 'medium';
}


if (!in_array($status, [
    'planning',
    'in_progress',
    'on_hold',
    'completed',
    'cancelled'
], true)) {

    $status = 'planning';
}


if ($budget < 0) {
    $budget = 0;
}


/*
|--------------------------------------------------------------------------
| Generate Project Code
|--------------------------------------------------------------------------
*/

$project_code = 'PRJ-' . date('Ymd') . '-' . strtoupper(
    substr(uniqid(), -6)
);


/*
|--------------------------------------------------------------------------
| Insert
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    INSERT INTO projects
    (
        client_id,
        manager_id,
        project_code,
        name,
        description,
        start_date,
        deadline,
        budget,
        priority,
        status,
        progress
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
");

$stmt->bind_param(
    "iisssssdss",
    $client_id,
    $manager_id,
    $project_code,
    $name,
    $description,
    $start_date,
    $deadline,
    $budget,
    $priority,
    $status
);

if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?success="
        . urlencode("Project created successfully.")
    );

    exit;
}

$stmt->close();

header(
    "Location: create.php?error="
    . urlencode("Failed to create project.")
);

exit;