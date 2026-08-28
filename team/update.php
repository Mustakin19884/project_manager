<?php

require_once "../config/auth.php";
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

$password = $_POST['password'] ?? '';
$password_confirmation = $_POST['password_confirmation'] ?? '';

$role = $_POST['role'] ?? 'team_member';
$status = $_POST['status'] ?? 'active';


if ($id <= 0 || $name === '' || $email === '') {

    header(
        "Location: edit.php?id=$id&error=" .
        urlencode("Name and email are required.")
    );

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header(
        "Location: edit.php?id=$id&error=" .
        urlencode("Invalid email address.")
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Check duplicate email
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id
    FROM users
    WHERE email = ?
    AND id != ?
    LIMIT 1
");

$stmt->bind_param(
    "si",
    $email,
    $id
);

$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {

    $stmt->close();

    header(
        "Location: edit.php?id=$id&error=" .
        urlencode("This email is already used by another user.")
    );

    exit;
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| Get current user
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT role
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$current_user = $stmt->get_result()->fetch_assoc();

$stmt->close();


if (!$current_user) {

    header(
        "Location: index.php?error=" .
        urlencode("User not found.")
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Admin protection
|--------------------------------------------------------------------------
*/

if ($current_user['role'] === 'admin') {

    $role = 'admin';
    $status = 'active';
}


if ($current_user['role'] !== 'admin') {

    if (!in_array($role, [
        'team_member',
        'project_manager'
    ], true)) {
        $role = 'team_member';
    }

    if (!in_array($status, [
        'active',
        'inactive'
    ], true)) {
        $status = 'active';
    }
}


/*
|--------------------------------------------------------------------------
| Password update
|--------------------------------------------------------------------------
*/

if ($password !== '') {

    if (strlen($password) < 6) {

        header(
            "Location: edit.php?id=$id&error=" .
            urlencode("Password must be at least 6 characters.")
        );

        exit;
    }

    if ($password !== $password_confirmation) {

        header(
            "Location: edit.php?id=$id&error=" .
            urlencode("Passwords do not match.")
        );

        exit;
    }

    $hashed_password = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    $stmt = $conn->prepare("
        UPDATE users
        SET
            name = ?,
            email = ?,
            password = ?,
            role = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "sssssi",
        $name,
        $email,
        $hashed_password,
        $role,
        $status,
        $id
    );

} else {

    $stmt = $conn->prepare("
        UPDATE users
        SET
            name = ?,
            email = ?,
            role = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssssi",
        $name,
        $email,
        $role,
        $status,
        $id
    );
}


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?success=" .
        urlencode("Team member updated successfully.")
    );

    exit;
}


$error = $stmt->error;

$stmt->close();


header(
    "Location: edit.php?id=$id&error=" .
    urlencode("Update failed: " . $error)
);

exit;