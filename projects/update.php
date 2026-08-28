<?php

require_once "../config/auth.php";
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);

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

$progress = (int)($_POST['progress'] ?? 0);

$members = $_POST['members'] ?? [];


if ($id <= 0 || $name === '') {

    header(
        "Location: edit.php?id=$id&error=" .
        urlencode("Project name is required.")
    );

    exit;
}


$progress = max(0, min(100, $progress));

$valid_priorities = [
    'low',
    'medium',
    'high',
    'urgent'
];

$valid_statuses = [
    'planning',
    'in_progress',
    'on_hold',
    'completed',
    'cancelled'
];


if (!in_array($priority, $valid_priorities, true)) {
    $priority = 'medium';
}

if (!in_array($status, $valid_statuses, true)) {
    $status = 'planning';
}


/*
|--------------------------------------------------------------------------
| Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {


    /*
    |--------------------------------------------------------------------------
    | Update Project
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        UPDATE projects
        SET
            client_id = ?,
            manager_id = ?,
            name = ?,
            description = ?,
            start_date = ?,
            deadline = ?,
            budget = ?,
            priority = ?,
            status = ?,
            progress = ?
        WHERE id = ?
    ");


    $stmt->bind_param(
        "iissssdssii",
        $client_id,
        $manager_id,
        $name,
        $description,
        $start_date,
        $deadline,
        $budget,
        $priority,
        $status,
        $progress,
        $id
    );


    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Remove Existing Members
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM project_members
        WHERE project_id = ?
    ");

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Add Selected Members
    |--------------------------------------------------------------------------
    */

    if (!empty($members)) {

        $stmt = $conn->prepare("
            INSERT INTO project_members
            (
                project_id,
                user_id
            )
            VALUES (?, ?)
        ");

        foreach ($members as $user_id) {

            $user_id = (int)$user_id;

            if ($user_id <= 0) {
                continue;
            }

            $stmt->bind_param(
                "ii",
                $id,
                $user_id
            );

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
        }

        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $conn->commit();


    header(
        "Location: view.php?id=$id&success=" .
        urlencode("Project updated successfully.")
    );

    exit;


} catch (Exception $e) {

    $conn->rollback();

    header(
        "Location: edit.php?id=$id&error=" .
        urlencode("Update failed: " . $e->getMessage())
    );

    exit;
}