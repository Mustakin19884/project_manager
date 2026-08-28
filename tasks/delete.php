<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Get Task ID
|--------------------------------------------------------------------------
*/

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Check Task Exists
|--------------------------------------------------------------------------
*/

$check_sql = "
    SELECT id, title
    FROM tasks
    WHERE id = ?
    LIMIT 1
";

$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    die("Database error: " . $conn->error);
}

$check_stmt->bind_param("i", $id);

$check_stmt->execute();

$result = $check_stmt->get_result();

$task = $result->fetch_assoc();

$check_stmt->close();


if (!$task) {

    header(
        "Location: index.php?error=not_found"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Delete Task
|--------------------------------------------------------------------------
*/

$delete_sql = "
    DELETE FROM tasks
    WHERE id = ?
";

$delete_stmt = $conn->prepare($delete_sql);

if (!$delete_stmt) {
    die("Database error: " . $conn->error);
}

$delete_stmt->bind_param("i", $id);


if ($delete_stmt->execute()) {

    $delete_stmt->close();

    header(
        "Location: index.php?success=deleted"
    );

    exit;
}


$error = $delete_stmt->error;

$delete_stmt->close();

die(
    "Failed to delete task: " .
    htmlspecialchars($error)
);

?>