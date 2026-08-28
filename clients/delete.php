<?php

require_once "../config/auth.php";
require_once "../config/db.php";


$id = (int)($_GET['id'] ?? 0);


if ($id <= 0) {

    header("Location: index.php");
    exit;
}


$stmt = $conn->prepare("
    DELETE FROM clients
    WHERE id = ?
");


$stmt->bind_param("i", $id);


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?success="
        . urlencode("Client deleted successfully.")
    );

    exit;
}


$stmt->close();

header(
    "Location: index.php?success="
    . urlencode("Unable to delete client.")
);

exit;