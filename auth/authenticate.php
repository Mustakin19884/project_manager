<?php

session_start();

require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {

    $_SESSION["login_error"] = "Please enter email and password.";

    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, name, email, password, status
     FROM admins
     WHERE email = ?
     LIMIT 1"
);

$stmt->bind_param("s", $email);

$stmt->execute();

$result = $stmt->get_result();

$admin = $result->fetch_assoc();

$stmt->close();

if (!$admin) {

    $_SESSION["login_error"] = "Invalid email or password.";

    header("Location: login.php");
    exit;
}

if ($admin["status"] !== "active") {

    $_SESSION["login_error"] = "Your account is inactive.";

    header("Location: login.php");
    exit;
}

if (!password_verify($password, $admin["password"])) {

    $_SESSION["login_error"] = "Invalid email or password.";

    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Login successful
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);

$_SESSION["admin_id"] = $admin["id"];
$_SESSION["admin_name"] = $admin["name"];
$_SESSION["admin_email"] = $admin["email"];


/*
|--------------------------------------------------------------------------
| Update last login
|--------------------------------------------------------------------------
*/

$update = $conn->prepare(
    "UPDATE admins
     SET last_login = NOW()
     WHERE id = ?"
);

$update->bind_param("i", $admin["id"]);

$update->execute();

$update->close();


header("Location: ../dashboard/index.php");
exit;