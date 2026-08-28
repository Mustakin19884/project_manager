<?php

require_once "../config/auth.php";
require_once "../config/db.php";


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php");
    exit;
}


$id = (int)($_POST['id'] ?? 0);

$name = trim($_POST['name'] ?? '');
$company = trim($_POST['company'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$website = trim($_POST['website'] ?? '');
$address = trim($_POST['address'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$status = $_POST['status'] ?? 'active';


if ($id <= 0 || $name === '') {

    header(
        "Location: edit.php?id=$id&error="
        . urlencode("Client name is required.")
    );

    exit;
}


if (!in_array($status, ['active', 'inactive'], true)) {

    $status = 'active';
}


if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header(
        "Location: edit.php?id=$id&error="
        . urlencode("Invalid email address.")
    );

    exit;
}


$stmt = $conn->prepare("
    UPDATE clients
    SET
        name = ?,
        company = ?,
        email = ?,
        phone = ?,
        address = ?,
        website = ?,
        notes = ?,
        status = ?
    WHERE id = ?
");


$stmt->bind_param(
    "ssssssssi",
    $name,
    $company,
    $email,
    $phone,
    $address,
    $website,
    $notes,
    $status,
    $id
);


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?success="
        . urlencode("Client updated successfully.")
    );

    exit;
}


$stmt->close();

header(
    "Location: edit.php?id=$id&error="
    . urlencode("Failed to update client.")
);

exit;