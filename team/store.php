<?php

require_once "../config/auth.php";
require_once "../config/db.php";


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php");
    exit;
}


$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

$password = $_POST['password'] ?? '';
$password_confirmation = $_POST['password_confirmation'] ?? '';

$role = $_POST['role'] ?? 'team_member';
$status = $_POST['status'] ?? 'active';


if ($name === '' || $email === '' || $password === '') {

    header(
        "Location: create.php?error="
        . urlencode("All required fields must be filled.")
    );

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header(
        "Location: create.php?error="
        . urlencode("Invalid email address.")
    );

    exit;
}


if (strlen($password) < 6) {

    header(
        "Location: create.php?error="
        . urlencode("Password must be at least 6 characters.")
    );

    exit;
}


if ($password !== $password_confirmation) {

    header(
        "Location: create.php?error="
        . urlencode("Passwords do not match.")
    );

    exit;
}


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


/*
|--------------------------------------------------------------------------
| Check duplicate email
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id
    FROM users
    WHERE email = ?
    LIMIT 1
");

$stmt->bind_param("s", $email);

$stmt->execute();

$exists = $stmt->get_result()->num_rows > 0;

$stmt->close();


if ($exists) {

    header(
        "Location: create.php?error="
        . urlencode("This email is already registered.")
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Hash Password
|--------------------------------------------------------------------------
*/

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/*
|--------------------------------------------------------------------------
| Insert User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    INSERT INTO users
    (
        name,
        email,
        password,
        role,
        status
    )
    VALUES (?, ?, ?, ?, ?)
");


$stmt->bind_param(
    "sssss",
    $name,
    $email,
    $hashed_password,
    $role,
    $status
);


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?success="
        . urlencode("Team member created successfully.")
    );

    exit;
}


$stmt->close();


header(
    "Location: create.php?error="
    . urlencode("Failed to create team member.")
);

exit;