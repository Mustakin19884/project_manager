<?php

require_once "../config/auth.php";
require_once "../config/db.php";


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php");
    exit;
}


$name = trim($_POST['name'] ?? '');
$company = trim($_POST['company'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$website = trim($_POST['website'] ?? '');
$address = trim($_POST['address'] ?? '');
$notes = trim($_POST['notes'] ?? '');

$status = $_POST['status'] ?? 'active';


if ($name === '') {

    header("Location: create.php?error=" . urlencode("Client name is required."));
    exit;
}


if (!in_array($status, ['active', 'inactive'], true)) {

    $status = 'active';
}


if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header("Location: create.php?error=" . urlencode("Invalid email address."));
    exit;
}


$stmt = $conn->prepare("
    INSERT INTO clients
    (
        name,
        company,
        email,
        phone,
        address,
        website,
        notes,
        status
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");


$stmt->bind_param(
    "ssssssss",
    $name,
    $company,
    $email,
    $phone,
    $address,
    $website,
    $notes,
    $status
);


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?success="
        . urlencode("Client created successfully.")
    );

    exit;
}


$stmt->close();

header(
    "Location: create.php?error="
    . urlencode("Failed to create client.")
);

exit;