<?php

session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: ../dashboard/index.php");
    exit;
}

$error = $_SESSION['login_error'] ?? null;

unset($_SESSION['login_error']);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Project Manager</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center">

    <div class="w-full max-w-md px-6">

        <div class="bg-white rounded-2xl shadow-xl p-8">

            <div class="text-center mb-8">

                <div
                    class="mx-auto mb-4 w-14 h-14 bg-indigo-600 rounded-xl flex items-center justify-center"
                >
                    <span class="text-white text-2xl font-bold">
                        PM
                    </span>
                </div>

                <h1 class="text-2xl font-bold text-gray-900">
                    Project Manager
                </h1>

                <p class="text-gray-500 mt-2">
                    Sign in to your account
                </p>

            </div>

            <?php if ($error): ?>

                <div
                    class="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700"
                >
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>

            <form
                action="authenticate.php"
                method="POST"
                class="space-y-5"
            >

                <div>

                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        autocomplete="email"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="admin@example.com"
                    >

                </div>

                <div>

                    <label
                        for="password"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="••••••••"
                    >

                </div>

                <button
                    type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition"
                >
                    Sign In
                </button>

            </form>

            <div class="mt-6 text-center text-sm text-gray-500">

                <p>
                    Project Management System
                </p>

            </div>

        </div>

    </div>

</body>

</html>