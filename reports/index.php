<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$from_date = trim($_GET['from_date'] ?? '');
$to_date   = trim($_GET['to_date'] ?? '');

$status = trim($_GET['status'] ?? '');
$priority = trim($_GET['priority'] ?? '');
$project_id = (int)($_GET['project_id'] ?? 0);


/*
|--------------------------------------------------------------------------
| Validate Dates
|--------------------------------------------------------------------------
*/

if ($from_date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from_date)) {
    $from_date = '';
}

if ($to_date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to_date)) {
    $to_date = '';
}


/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

function getCount($conn, $sql, $params = [], $types = '')
{
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return 0;
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    $stmt->close();

    return (int)($row['total'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| Build Task Filter
|--------------------------------------------------------------------------
*/

$task_where = [];
$task_params = [];
$task_types = '';


if ($from_date !== '') {

    $task_where[] = "DATE(t.created_at) >= ?";

    $task_params[] = $from_date;
    $task_types .= "s";
}


if ($to_date !== '') {

    $task_where[] = "DATE(t.created_at) <= ?";

    $task_params[] = $to_date;
    $task_types .= "s";
}


if ($status !== '') {

    $allowed_status = [
        'pending',
        'in_progress',
        'completed',
        'on_hold',
        'cancelled'
    ];

    if (in_array($status, $allowed_status, true)) {

        $task_where[] = "t.status = ?";

        $task_params[] = $status;
        $task_types .= "s";
    }
}


if ($priority !== '') {

    $allowed_priority = [
        'low',
        'medium',
        'high',
        'urgent'
    ];

    if (in_array($priority, $allowed_priority, true)) {

        $task_where[] = "t.priority = ?";

        $task_params[] = $priority;
        $task_types .= "s";
    }
}


if ($project_id > 0) {

    $task_where[] = "t.project_id = ?";

    $task_params[] = $project_id;
    $task_types .= "i";
}


$task_where_sql = '';

if (!empty($task_where)) {

    $task_where_sql =
        "WHERE " . implode(" AND ", $task_where);
}


/*
|--------------------------------------------------------------------------
| Build Project Filter
|--------------------------------------------------------------------------
*/

$project_where = [];
$project_params = [];
$project_types = '';


if ($from_date !== '') {

    $project_where[] = "DATE(p.created_at) >= ?";

    $project_params[] = $from_date;
    $project_types .= "s";
}


if ($to_date !== '') {

    $project_where[] = "DATE(p.created_at) <= ?";

    $project_params[] = $to_date;
    $project_types .= "s";
}


if ($project_id > 0) {

    $project_where[] = "p.id = ?";

    $project_params[] = $project_id;
    $project_types .= "i";
}


$project_where_sql = '';

if (!empty($project_where)) {

    $project_where_sql =
        "WHERE " . implode(" AND ", $project_where);
}


/*
|--------------------------------------------------------------------------
| Project Statistics
|--------------------------------------------------------------------------
*/

$total_projects = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM projects p
    $project_where_sql
    ",
    $project_params,
    $project_types
);


$planning_projects = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM projects p
    $project_where_sql
    " .
    (
        empty($project_where)
        ? "WHERE p.status = 'planning'"
        : "AND p.status = 'planning'"
    ),
    $project_params,
    $project_types
);


$in_progress_projects = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM projects p
    $project_where_sql
    " .
    (
        empty($project_where)
        ? "WHERE p.status = 'in_progress'"
        : "AND p.status = 'in_progress'"
    ),
    $project_params,
    $project_types
);


$completed_projects = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM projects p
    $project_where_sql
    " .
    (
        empty($project_where)
        ? "WHERE p.status = 'completed'"
        : "AND p.status = 'completed'"
    ),
    $project_params,
    $project_types
);


$on_hold_projects = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM projects p
    $project_where_sql
    " .
    (
        empty($project_where)
        ? "WHERE p.status = 'on_hold'"
        : "AND p.status = 'on_hold'"
    ),
    $project_params,
    $project_types
);


$cancelled_projects = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM projects p
    $project_where_sql
    " .
    (
        empty($project_where)
        ? "WHERE p.status = 'cancelled'"
        : "AND p.status = 'cancelled'"
    ),
    $project_params,
    $project_types
);


/*
|--------------------------------------------------------------------------
| Task Statistics
|--------------------------------------------------------------------------
*/

$total_tasks = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM tasks t
    $task_where_sql
    ",
    $task_params,
    $task_types
);


$pending_tasks = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM tasks t
    $task_where_sql
    " .
    (
        empty($task_where)
        ? "WHERE t.status = 'pending'"
        : "AND t.status = 'pending'"
    ),
    $task_params,
    $task_types
);


$task_in_progress = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM tasks t
    $task_where_sql
    " .
    (
        empty($task_where)
        ? "WHERE t.status = 'in_progress'"
        : "AND t.status = 'in_progress'"
    ),
    $task_params,
    $task_types
);


$completed_tasks = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM tasks t
    $task_where_sql
    " .
    (
        empty($task_where)
        ? "WHERE t.status = 'completed'"
        : "AND t.status = 'completed'"
    ),
    $task_params,
    $task_types
);


$on_hold_tasks = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM tasks t
    $task_where_sql
    " .
    (
        empty($task_where)
        ? "WHERE t.status = 'on_hold'"
        : "AND t.status = 'on_hold'"
    ),
    $task_params,
    $task_types
);


$cancelled_tasks = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM tasks t
    $task_where_sql
    " .
    (
        empty($task_where)
        ? "WHERE t.status = 'cancelled'"
        : "AND t.status = 'cancelled'"
    ),
    $task_params,
    $task_types
);


/*
|--------------------------------------------------------------------------
| Priority Statistics
|--------------------------------------------------------------------------
*/

$low_tasks = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM tasks t
    $task_where_sql
    " .
    (
        empty($task_where)
        ? "WHERE t.priority = 'low'"
        : "AND t.priority = 'low'"
    ),
    $task_params,
    $task_types
);


$medium_tasks = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM tasks t
    $task_where_sql
    " .
    (
        empty($task_where)
        ? "WHERE t.priority = 'medium'"
        : "AND t.priority = 'medium'"
    ),
    $task_params,
    $task_types
);


$high_tasks = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM tasks t
    $task_where_sql
    " .
    (
        empty($task_where)
        ? "WHERE t.priority = 'high'"
        : "AND t.priority = 'high'"
    ),
    $task_params,
    $task_types
);


$urgent_tasks = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM tasks t
    $task_where_sql
    " .
    (
        empty($task_where)
        ? "WHERE t.priority = 'urgent'"
        : "AND t.priority = 'urgent'"
    ),
    $task_params,
    $task_types
);


/*
|--------------------------------------------------------------------------
| Deadline Statistics
|--------------------------------------------------------------------------
*/

$overdue_tasks = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM tasks t
    $task_where_sql
    " .
    (
        empty($task_where)
        ? "WHERE"
        : "AND"
    ) . "
    t.deadline IS NOT NULL
    AND t.deadline < CURDATE()
    AND t.status NOT IN ('completed', 'cancelled')
    ",
    $task_params,
    $task_types
);


$today_tasks = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM tasks t
    $task_where_sql
    " .
    (
        empty($task_where)
        ? "WHERE"
        : "AND"
    ) . "
    t.deadline = CURDATE()
    AND t.status NOT IN ('completed', 'cancelled')
    ",
    $task_params,
    $task_types
);


$week_tasks = getCount(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM tasks t
    $task_where_sql
    " .
    (
        empty($task_where)
        ? "WHERE"
        : "AND"
    ) . "
    t.deadline BETWEEN CURDATE()
    AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND t.status NOT IN ('completed', 'cancelled')
    ",
    $task_params,
    $task_types
);


/*
|--------------------------------------------------------------------------
| Average Progress
|--------------------------------------------------------------------------
*/

$avg_project_progress = 0;

$avg_sql = "
    SELECT AVG(p.progress) AS average_progress
    FROM projects p
    $project_where_sql
";

$avg_stmt = $conn->prepare($avg_sql);

if ($avg_stmt) {

    if (!empty($project_params)) {
        $avg_stmt->bind_param(
            $project_types,
            ...$project_params
        );
    }

    $avg_stmt->execute();

    $row = $avg_stmt
        ->get_result()
        ->fetch_assoc();

    $avg_project_progress =
        round((float)($row['average_progress'] ?? 0));

    $avg_stmt->close();
}


$avg_task_progress = 0;

$avg_sql = "
    SELECT AVG(t.progress) AS average_progress
    FROM tasks t
    $task_where_sql
";

$avg_stmt = $conn->prepare($avg_sql);

if ($avg_stmt) {

    if (!empty($task_params)) {
        $avg_stmt->bind_param(
            $task_types,
            ...$task_params
        );
    }

    $avg_stmt->execute();

    $row = $avg_stmt
        ->get_result()
        ->fetch_assoc();

    $avg_task_progress =
        round((float)($row['average_progress'] ?? 0));

    $avg_stmt->close();
}


/*
|--------------------------------------------------------------------------
| Team Members
|--------------------------------------------------------------------------
*/

$total_members = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM team_members"
);


/*
|--------------------------------------------------------------------------
| Projects Dropdown
|--------------------------------------------------------------------------
*/

$projects_dropdown = $conn->query("
    SELECT id, name
    FROM projects
    ORDER BY name ASC
");


/*
|--------------------------------------------------------------------------
| Team Performance
|--------------------------------------------------------------------------
*/

$team_result = false;

$team_sql = "

    SELECT

        tm.id,
        tm.name,

        COUNT(t.id) AS assigned_tasks,

        SUM(
            CASE
                WHEN t.status = 'completed'
                THEN 1
                ELSE 0
            END
        ) AS completed_tasks,

        SUM(
            CASE
                WHEN t.status = 'pending'
                THEN 1
                ELSE 0
            END
        ) AS pending_tasks,

        COALESCE(
            ROUND(AVG(t.progress)),
            0
        ) AS average_progress

    FROM team_members tm

    LEFT JOIN tasks t
        ON t.team_member_id = tm.id

";


if (!empty($task_where)) {

    $team_sql .= "
        AND " .
        implode(" AND ", $task_where);
}


$team_sql .= "

    GROUP BY tm.id, tm.name

    ORDER BY average_progress DESC

    LIMIT 10

";


$team_stmt = $conn->prepare($team_sql);

if ($team_stmt) {

    if (!empty($task_params)) {

        $team_stmt->bind_param(
            $task_types,
            ...$task_params
        );
    }

    $team_stmt->execute();

    $team_result = $team_stmt->get_result();
}


/*
|--------------------------------------------------------------------------
| Recent Tasks
|--------------------------------------------------------------------------
*/

$recent_tasks = false;

$recent_sql = "

    SELECT

        t.id,
        t.title,
        t.status,
        t.priority,
        t.progress,
        t.deadline,
        t.created_at,

        p.name AS project_name,

        tm.name AS member_name

    FROM tasks t

    LEFT JOIN projects p
        ON p.id = t.project_id

    LEFT JOIN team_members tm
        ON tm.id = t.team_member_id

    $task_where_sql

    ORDER BY t.id DESC

    LIMIT 10

";


$recent_stmt = $conn->prepare($recent_sql);

if ($recent_stmt) {

    if (!empty($task_params)) {

        $recent_stmt->bind_param(
            $task_types,
            ...$task_params
        );
    }

    $recent_stmt->execute();

    $recent_tasks =
        $recent_stmt->get_result();
}


/*
|--------------------------------------------------------------------------
| Filter Query
|--------------------------------------------------------------------------
*/

$filter_query = http_build_query([
    'from_date' => $from_date,
    'to_date' => $to_date,
    'status' => $status,
    'priority' => $priority,
    'project_id' => $project_id
]);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reports | Project Manager</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>

        @media print {

            aside,
            .no-print,
            header .actions {
                display: none !important;
            }

            main {
                width: 100% !important;
            }

            body {
                background: white !important;
            }

            section {
                padding: 20px !important;
            }

            .print-break {
                break-inside: avoid;
            }

        }

    </style>

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
                href="../dashboard/index.php"
                class="block px-4 py-3 rounded-lg hover:bg-gray-800"
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
                href="index.php"
                class="block px-4 py-3 rounded-lg bg-indigo-600"
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

            <div class="px-6 py-5 flex items-center justify-between">


                <div>

                    <h2 class="text-xl font-semibold text-gray-900">
                        Reports
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Project, task and team performance reports
                    </p>

                </div>


                <div class="actions no-print flex gap-2">

                    <button
                        onclick="window.print()"
                        class="px-4 py-2.5 rounded-xl bg-gray-900 text-white hover:bg-gray-800"
                    >
                        🖨 PDF / Print
                    </button>


                    <a
    href="./export.php?type=csv&<?= htmlspecialchars($filter_query) ?>"
    class="px-4 py-2.5 rounded-xl bg-green-600 text-white hover:bg-green-700"
>
    ↓ CSV Export
</a>

                </div>


            </div>

        </header>


        <section class="p-6">


            <!-- FILTER -->

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6 no-print">


                <div class="flex items-center justify-between mb-5">

                    <div>

                        <h3 class="font-semibold text-gray-900">
                            Report Filters
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Filter reports by date, project, status and priority
                        </p>

                    </div>

                </div>


                <form
                    method="GET"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4"
                >


                    <!-- From -->

                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            From Date
                        </label>

                        <input
                            type="date"
                            name="from_date"
                            value="<?= htmlspecialchars($from_date) ?>"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                        >

                    </div>


                    <!-- To -->

                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            To Date
                        </label>

                        <input
                            type="date"
                            name="to_date"
                            value="<?= htmlspecialchars($to_date) ?>"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                        >

                    </div>


                    <!-- Project -->

                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Project
                        </label>

                        <select
                            name="project_id"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                        >

                            <option value="">
                                All Projects
                            </option>


                            <?php if ($projects_dropdown): ?>

                                <?php while ($p = $projects_dropdown->fetch_assoc()): ?>

                                    <option
                                        value="<?= (int)$p['id'] ?>"
                                        <?= $project_id == $p['id'] ? 'selected' : '' ?>
                                    >

                                        <?= htmlspecialchars($p['name']) ?>

                                    </option>

                                <?php endwhile; ?>

                            <?php endif; ?>

                        </select>

                    </div>


                    <!-- Status -->

                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Task Status
                        </label>

                        <select
                            name="status"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                        >

                            <option value="">
                                All Status
                            </option>

                            <option
                                value="pending"
                                <?= $status === 'pending' ? 'selected' : '' ?>
                            >
                                Pending
                            </option>

                            <option
                                value="in_progress"
                                <?= $status === 'in_progress' ? 'selected' : '' ?>
                            >
                                In Progress
                            </option>

                            <option
                                value="completed"
                                <?= $status === 'completed' ? 'selected' : '' ?>
                            >
                                Completed
                            </option>

                            <option
                                value="on_hold"
                                <?= $status === 'on_hold' ? 'selected' : '' ?>
                            >
                                On Hold
                            </option>

                            <option
                                value="cancelled"
                                <?= $status === 'cancelled' ? 'selected' : '' ?>
                            >
                                Cancelled
                            </option>

                        </select>

                    </div>


                    <!-- Priority -->

                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Priority
                        </label>

                        <select
                            name="priority"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                        >

                            <option value="">
                                All Priority
                            </option>

                            <option
                                value="low"
                                <?= $priority === 'low' ? 'selected' : '' ?>
                            >
                                Low
                            </option>

                            <option
                                value="medium"
                                <?= $priority === 'medium' ? 'selected' : '' ?>
                            >
                                Medium
                            </option>

                            <option
                                value="high"
                                <?= $priority === 'high' ? 'selected' : '' ?>
                            >
                                High
                            </option>

                            <option
                                value="urgent"
                                <?= $priority === 'urgent' ? 'selected' : '' ?>
                            >
                                Urgent
                            </option>

                        </select>

                    </div>


                    <div class="lg:col-span-5 flex gap-2">

                        <button
                            type="submit"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700"
                        >
                            Apply Filters
                        </button>


                        <a
                            href="index.php"
                            class="px-5 py-2.5 rounded-xl border border-gray-300 hover:bg-gray-50"
                        >
                            Reset
                        </a>

                    </div>


                </form>


                <?php if ($from_date || $to_date || $status || $priority || $project_id): ?>

                    <div class="mt-4 text-sm text-gray-500">

                        Active filters:

                        <?php if ($from_date): ?>
                            <span class="ml-2 px-2 py-1 bg-gray-100 rounded">
                                From: <?= htmlspecialchars($from_date) ?>
                            </span>
                        <?php endif; ?>


                        <?php if ($to_date): ?>
                            <span class="ml-2 px-2 py-1 bg-gray-100 rounded">
                                To: <?= htmlspecialchars($to_date) ?>
                            </span>
                        <?php endif; ?>


                        <?php if ($status): ?>
                            <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-700 rounded">
                                Status: <?= ucfirst(str_replace('_', ' ', $status)) ?>
                            </span>
                        <?php endif; ?>


                        <?php if ($priority): ?>
                            <span class="ml-2 px-2 py-1 bg-orange-100 text-orange-700 rounded">
                                Priority: <?= ucfirst($priority) ?>
                            </span>
                        <?php endif; ?>

                    </div>

                <?php endif; ?>


            </div>


            <!-- REPORT TITLE FOR PRINT -->

            <div class="hidden print:block mb-6">

                <h1 class="text-2xl font-bold">
                    Project Management Report
                </h1>

                <p class="text-sm text-gray-500">

                    Generated:
                    <?= date('M d, Y h:i A') ?>

                </p>

            </div>


            <!-- TOP CARDS -->

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">


                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 print-break">

                    <p class="text-sm text-gray-500">
                        Total Projects
                    </p>

                    <h3 class="text-3xl font-bold text-gray-900 mt-2">
                        <?= $total_projects ?>
                    </h3>

                    <p class="text-xs text-gray-500 mt-2">
                        <?= $completed_projects ?> completed
                    </p>

                </div>


                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 print-break">

                    <p class="text-sm text-gray-500">
                        Total Tasks
                    </p>

                    <h3 class="text-3xl font-bold text-gray-900 mt-2">
                        <?= $total_tasks ?>
                    </h3>

                    <p class="text-xs text-gray-500 mt-2">
                        <?= $completed_tasks ?> completed
                    </p>

                </div>


                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 print-break">

                    <p class="text-sm text-gray-500">
                        Team Members
                    </p>

                    <h3 class="text-3xl font-bold text-gray-900 mt-2">
                        <?= $total_members ?>
                    </h3>

                </div>


                <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-5 print-break">

                    <p class="text-sm text-red-500">
                        Overdue Tasks
                    </p>

                    <h3 class="text-3xl font-bold text-red-600 mt-2">
                        <?= $overdue_tasks ?>
                    </h3>

                </div>


            </div>


            <!-- PROGRESS -->

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">


                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

                    <div class="flex justify-between mb-3">

                        <h3 class="font-semibold">
                            Average Project Progress
                        </h3>

                        <span class="font-bold text-indigo-600">
                            <?= $avg_project_progress ?>%
                        </span>

                    </div>


                    <div class="h-3 bg-gray-200 rounded-full">

                        <div
                            class="h-3 bg-indigo-600 rounded-full"
                            style="width: <?= $avg_project_progress ?>%"
                        ></div>

                    </div>

                </div>


                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

                    <div class="flex justify-between mb-3">

                        <h3 class="font-semibold">
                            Average Task Progress
                        </h3>

                        <span class="font-bold text-indigo-600">
                            <?= $avg_task_progress ?>%
                        </span>

                    </div>


                    <div class="h-3 bg-gray-200 rounded-full">

                        <div
                            class="h-3 bg-indigo-600 rounded-full"
                            style="width: <?= $avg_task_progress ?>%"
                        ></div>

                    </div>

                </div>


            </div>


            <!-- STATUS -->

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">


                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

                    <h3 class="font-semibold mb-5">
                        Project Status
                    </h3>


                    <div class="space-y-4">

                        <?php

                        $project_statuses = [
                            'Planning' => $planning_projects,
                            'In Progress' => $in_progress_projects,
                            'On Hold' => $on_hold_projects,
                            'Completed' => $completed_projects,
                            'Cancelled' => $cancelled_projects
                        ];

                        ?>


                        <?php foreach ($project_statuses as $name => $count): ?>

                            <?php

                            $percentage =
                                $total_projects > 0
                                ? round(($count / $total_projects) * 100)
                                : 0;

                            ?>

                            <div>

                                <div class="flex justify-between text-sm mb-1">

                                    <span>
                                        <?= $name ?>
                                    </span>

                                    <span class="font-medium">
                                        <?= $count ?>
                                    </span>

                                </div>


                                <div class="h-2 bg-gray-200 rounded-full">

                                    <div
                                        class="h-2 bg-indigo-600 rounded-full"
                                        style="width: <?= $percentage ?>%"
                                    ></div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>


                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

                    <h3 class="font-semibold mb-5">
                        Task Status
                    </h3>


                    <div class="grid grid-cols-2 gap-4">


                        <div class="bg-yellow-50 rounded-xl p-4">

                            <p class="text-sm text-yellow-700">
                                Pending
                            </p>

                            <p class="text-2xl font-bold text-yellow-800">
                                <?= $pending_tasks ?>
                            </p>

                        </div>


                        <div class="bg-blue-50 rounded-xl p-4">

                            <p class="text-sm text-blue-700">
                                In Progress
                            </p>

                            <p class="text-2xl font-bold text-blue-800">
                                <?= $task_in_progress ?>
                            </p>

                        </div>


                        <div class="bg-green-50 rounded-xl p-4">

                            <p class="text-sm text-green-700">
                                Completed
                            </p>

                            <p class="text-2xl font-bold text-green-800">
                                <?= $completed_tasks ?>
                            </p>

                        </div>


                        <div class="bg-red-50 rounded-xl p-4">

                            <p class="text-sm text-red-700">
                                Overdue
                            </p>

                            <p class="text-2xl font-bold text-red-800">
                                <?= $overdue_tasks ?>
                            </p>

                        </div>


                    </div>

                </div>


            </div>


            <!-- DEADLINE + PRIORITY -->

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">


                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

                    <h3 class="font-semibold mb-5">
                        Deadline Overview
                    </h3>


                    <div class="grid grid-cols-3 gap-4 text-center">


                        <div>

                            <p class="text-2xl font-bold text-red-600">
                                <?= $overdue_tasks ?>
                            </p>

                            <p class="text-xs text-gray-500">
                                Overdue
                            </p>

                        </div>


                        <div>

                            <p class="text-2xl font-bold text-orange-600">
                                <?= $today_tasks ?>
                            </p>

                            <p class="text-xs text-gray-500">
                                Due Today
                            </p>

                        </div>


                        <div>

                            <p class="text-2xl font-bold text-indigo-600">
                                <?= $week_tasks ?>
                            </p>

                            <p class="text-xs text-gray-500">
                                Next 7 Days
                            </p>

                        </div>


                    </div>

                </div>


                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

                    <h3 class="font-semibold mb-5">
                        Task Priority
                    </h3>


                    <div class="grid grid-cols-4 text-center">


                        <div>

                            <p class="text-xl font-bold">
                                <?= $low_tasks ?>
                            </p>

                            <p class="text-xs text-gray-500">
                                Low
                            </p>

                        </div>


                        <div>

                            <p class="text-xl font-bold text-blue-600">
                                <?= $medium_tasks ?>
                            </p>

                            <p class="text-xs text-gray-500">
                                Medium
                            </p>

                        </div>


                        <div>

                            <p class="text-xl font-bold text-orange-600">
                                <?= $high_tasks ?>
                            </p>

                            <p class="text-xs text-gray-500">
                                High
                            </p>

                        </div>


                        <div>

                            <p class="text-xl font-bold text-red-600">
                                <?= $urgent_tasks ?>
                            </p>

                            <p class="text-xs text-gray-500">
                                Urgent
                            </p>

                        </div>


                    </div>

                </div>


            </div>


            <!-- TEAM PERFORMANCE -->

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6 print-break">


                <div class="px-6 py-5 border-b">

                    <h3 class="font-semibold">
                        Team Performance
                    </h3>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                    Team Member
                                </th>

                                <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                    Assigned
                                </th>

                                <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                    Completed
                                </th>

                                <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                    Pending
                                </th>

                                <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                    Progress
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y">


                        <?php if ($team_result && $team_result->num_rows > 0): ?>


                            <?php while ($member = $team_result->fetch_assoc()): ?>


                                <tr>

                                    <td class="px-6 py-4 font-medium">

                                        <?= htmlspecialchars(
                                            $member['name']
                                        ) ?>

                                    </td>


                                    <td class="px-6 py-4">

                                        <?= (int)$member['assigned_tasks'] ?>

                                    </td>


                                    <td class="px-6 py-4 text-green-600">

                                        <?= (int)$member['completed_tasks'] ?>

                                    </td>


                                    <td class="px-6 py-4 text-yellow-600">

                                        <?= (int)$member['pending_tasks'] ?>

                                    </td>


                                    <td class="px-6 py-4">

                                        <?= (int)$member['average_progress'] ?>%

                                    </td>

                                </tr>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <tr>

                                <td
                                    colspan="5"
                                    class="px-6 py-10 text-center text-gray-500"
                                >
                                    No team data available.
                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>

                    </table>

                </div>

            </div>


            <!-- RECENT TASKS -->

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden print-break">


                <div class="px-6 py-5 border-b">

                    <h3 class="font-semibold">
                        Task Report
                    </h3>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                    Task
                                </th>

                                <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                    Project
                                </th>

                                <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                    Member
                                </th>

                                <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                    Status
                                </th>

                                <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                    Priority
                                </th>

                                <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                    Progress
                                </th>

                                <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                    Deadline
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y">


                        <?php if ($recent_tasks && $recent_tasks->num_rows > 0): ?>


                            <?php while ($task = $recent_tasks->fetch_assoc()): ?>


                                <tr>

                                    <td class="px-6 py-4 font-medium">

                                        <?= htmlspecialchars(
                                            $task['title']
                                        ) ?>

                                    </td>


                                    <td class="px-6 py-4 text-sm">

                                        <?= htmlspecialchars(
                                            $task['project_name']
                                            ?? '—'
                                        ) ?>

                                    </td>


                                    <td class="px-6 py-4 text-sm">

                                        <?= htmlspecialchars(
                                            $task['member_name']
                                            ?? 'Unassigned'
                                        ) ?>

                                    </td>


                                    <td class="px-6 py-4 text-sm">

                                        <?= ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $task['status']
                                            )
                                        ) ?>

                                    </td>


                                    <td class="px-6 py-4 text-sm">

                                        <?= ucfirst(
                                            $task['priority']
                                        ) ?>

                                    </td>


                                    <td class="px-6 py-4">

                                        <?= (int)$task['progress'] ?>%

                                    </td>


                                    <td class="px-6 py-4 text-sm">

                                        <?= !empty($task['deadline'])
                                            ? date(
                                                'M d, Y',
                                                strtotime(
                                                    $task['deadline']
                                                )
                                            )
                                            : '—'
                                        ?>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <tr>

                                <td
                                    colspan="7"
                                    class="px-6 py-10 text-center text-gray-500"
                                >
                                    No tasks found.
                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>

                    </table>

                </div>

            </div>


        </section>

    </main>

</div>


</body>

</html>