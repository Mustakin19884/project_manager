<?php

require_once "../config/auth.php";
require_once "../config/db.php";


// ============================================================
// DASHBOARD STATISTICS
// ============================================================

// Total Projects
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM projects
");

$total_projects = $result->fetch_assoc()['total'];


// Active Projects
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM projects
    WHERE status = 'in_progress'
");

$active_projects = $result->fetch_assoc()['total'];


// Completed Projects
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM projects
    WHERE status = 'completed'
");

$completed_projects = $result->fetch_assoc()['total'];


// Pending Tasks
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM tasks
    WHERE status IN ('todo', 'in_progress', 'review')
");

$pending_tasks = $result->fetch_assoc()['total'];


// Overdue Tasks
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM tasks
    WHERE due_date < CURDATE()
    AND status NOT IN ('completed', 'cancelled')
");

$overdue_tasks = $result->fetch_assoc()['total'];


// Total Team Members
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE status = 'active'
");

$total_team_members = $result->fetch_assoc()['total'];


// Total Clients
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM clients
    WHERE status = 'active'
");

$total_clients = $result->fetch_assoc()['total'];


// Recent Projects
$recent_projects = $conn->query("
    SELECT
        p.id,
        p.name,
        p.project_code,
        p.status,
        p.priority,
        p.progress,
        p.deadline,
        c.name AS client_name
    FROM projects p
    LEFT JOIN clients c
        ON p.client_id = c.id
    ORDER BY p.created_at DESC
    LIMIT 5
");

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard | Project Manager</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100">

    <div class="min-h-screen flex">

        <!-- Sidebar -->

        <aside class="w-64 bg-gray-900 text-white hidden md:block">

            <div class="p-6">

                <h1 class="text-xl font-bold">
                    Project Manager
                </h1>

            </div>

            <nav class="px-4 space-y-2">

                <a
                    href="index.php"
                    class="block px-4 py-3 rounded-lg bg-indigo-600"
                >
                    Dashboard
                </a>

                <a
                    href="../projects/index.php"
                    class="block px-4 py-3 rounded-lg hover:bg-gray-800"
                >
                    Projects
                </a>

                <a
                    href="../tasks/index.php"
                    class="block px-4 py-3 rounded-lg hover:bg-gray-800"
                >
                    Tasks
                </a>

                <a
                    href="../clients/index.php"
                    class="block px-4 py-3 rounded-lg hover:bg-gray-800"
                >
                    Clients
                </a>

                <a
                    href="../team/index.php"
                    class="block px-4 py-3 rounded-lg hover:bg-gray-800"
                >
                    Team Members
                </a>

                <a
                    href="../reports/index.php"
                    class="block px-4 py-3 rounded-lg hover:bg-gray-800"
                >
                    Reports
                </a>

                <a
                    href="../auth/logout.php"
                    class="block px-4 py-3 rounded-lg hover:bg-red-600 mt-8"
                >
                    Logout
                </a>

            </nav>

        </aside>


        <!-- Main -->

        <main class="flex-1">

            <!-- Header -->

            <header class="bg-white border-b">

                <div
                    class="px-6 py-4 flex items-center justify-between"
                >

                    <div>

                        <h2 class="text-xl font-semibold text-gray-800">
                            Dashboard
                        </h2>

                        <p class="text-sm text-gray-500">
                            Welcome back,
                            <?= htmlspecialchars($_SESSION["admin_name"]) ?>
                        </p>

                    </div>

                    <div
                        class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold"
                    >
                        <?= strtoupper(substr($_SESSION["admin_name"], 0, 1)) ?>
                    </div>

                </div>

            </header>


            <!-- Content -->

            <section class="p-6">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

    <!-- Total Projects -->

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-sm text-gray-500">
                    Total Projects
                </p>

                <h3 class="text-3xl font-bold text-gray-900 mt-2">
                    <?= $total_projects ?>
                </h3>

            </div>

            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">

                <span class="text-indigo-600 text-xl">
                    📁
                </span>

            </div>

        </div>

    </div>


    <!-- Active Projects -->

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-sm text-gray-500">
                    Active Projects
                </p>

                <h3 class="text-3xl font-bold text-gray-900 mt-2">
                    <?= $active_projects ?>
                </h3>

            </div>

            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">

                <span class="text-blue-600 text-xl">
                    🚀
                </span>

            </div>

        </div>

    </div>


    <!-- Pending Tasks -->

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-sm text-gray-500">
                    Pending Tasks
                </p>

                <h3 class="text-3xl font-bold text-gray-900 mt-2">
                    <?= $pending_tasks ?>
                </h3>

            </div>

            <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center">

                <span class="text-yellow-600 text-xl">
                    📋
                </span>

            </div>

        </div>

    </div>


    <!-- Completed Projects -->

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-sm text-gray-500">
                    Completed Projects
                </p>

                <h3 class="text-3xl font-bold text-gray-900 mt-2">
                    <?= $completed_projects ?>
                </h3>

            </div>

            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">

                <span class="text-green-600 text-xl">
                    ✓
                </span>

            </div>

        </div>

    </div>

</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">

    <!-- Overdue Tasks -->

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">

        <p class="text-sm text-gray-500">
            Overdue Tasks
        </p>

        <div class="flex items-end gap-2 mt-2">

            <h3 class="text-3xl font-bold text-red-600">
                <?= $overdue_tasks ?>
            </h3>

            <span class="text-sm text-gray-500 mb-1">
                tasks
            </span>

        </div>

    </div>


    <!-- Team Members -->

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">

        <p class="text-sm text-gray-500">
            Active Team Members
        </p>

        <h3 class="text-3xl font-bold text-gray-900 mt-2">
            <?= $total_team_members ?>
        </h3>

    </div>


    <!-- Clients -->

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">

        <p class="text-sm text-gray-500">
            Active Clients
        </p>

        <h3 class="text-3xl font-bold text-gray-900 mt-2">
            <?= $total_clients ?>
        </h3>

    </div>

</div>
</body>

</html>