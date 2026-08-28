<?php

require_once "../config/auth.php";
require_once "../config/db.php";

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Project
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        p.*,

        c.name AS client_name,
        c.company AS client_company,
        c.email AS client_email,
        c.phone AS client_phone,

        u.name AS manager_name,
        u.email AS manager_email

    FROM projects p

    LEFT JOIN clients c
        ON p.client_id = c.id

    LEFT JOIN users u
        ON p.manager_id = u.id

    WHERE p.id = ?

    LIMIT 1
");

if (!$stmt) {
    die("Database Error: " . $conn->error);
}

$stmt->bind_param("i", $id);

$stmt->execute();

$project = $stmt->get_result()->fetch_assoc();

$stmt->close();


if (!$project) {

    header(
        "Location: index.php?error=" .
        urlencode("Project not found.")
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Get Team Members
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        u.id,
        u.name,
        u.email,
        u.role

    FROM project_members pm

    INNER JOIN users u
        ON pm.user_id = u.id

    WHERE pm.project_id = ?

    ORDER BY u.name ASC
");

$stmt->bind_param("i", $id);

$stmt->execute();

$team_members = $stmt->get_result();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Task Statistics
|--------------------------------------------------------------------------
|
| This query assumes the tasks table has:
|
| project_id
| status
|
*/

$task_stats = [
    'total' => 0,
    'todo' => 0,
    'in_progress' => 0,
    'review' => 0,
    'completed' => 0
];

$task_table_exists = false;

$table_check = $conn->query("
    SHOW TABLES LIKE 'tasks'
");

if ($table_check && $table_check->num_rows > 0) {

    $task_table_exists = true;

    $stmt = $conn->prepare("
        SELECT
            COUNT(*) AS total,

            SUM(
                CASE
                    WHEN status = 'todo'
                    THEN 1 ELSE 0
                END
            ) AS todo,

            SUM(
                CASE
                    WHEN status = 'in_progress'
                    THEN 1 ELSE 0
                END
            ) AS in_progress,

            SUM(
                CASE
                    WHEN status = 'review'
                    THEN 1 ELSE 0
                END
            ) AS review,

            SUM(
                CASE
                    WHEN status = 'completed'
                    THEN 1 ELSE 0
                END
            ) AS completed

        FROM tasks

        WHERE project_id = ?
    ");

    if ($stmt) {

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $stats = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if ($stats) {

            $task_stats['total'] =
                (int)($stats['total'] ?? 0);

            $task_stats['todo'] =
                (int)($stats['todo'] ?? 0);

            $task_stats['in_progress'] =
                (int)($stats['in_progress'] ?? 0);

            $task_stats['review'] =
                (int)($stats['review'] ?? 0);

            $task_stats['completed'] =
                (int)($stats['completed'] ?? 0);
        }
    }
}


/*
|--------------------------------------------------------------------------
| Task Completion Percentage
|--------------------------------------------------------------------------
*/

$task_completion = 0;

if ($task_stats['total'] > 0) {

    $task_completion = round(
        (
            $task_stats['completed']
            / $task_stats['total']
        ) * 100
    );
}


/*
|--------------------------------------------------------------------------
| Status / Priority classes
|--------------------------------------------------------------------------
*/

$status_classes = [

    'planning' =>
        'bg-gray-100 text-gray-700',

    'in_progress' =>
        'bg-blue-100 text-blue-700',

    'on_hold' =>
        'bg-yellow-100 text-yellow-700',

    'completed' =>
        'bg-green-100 text-green-700',

    'cancelled' =>
        'bg-red-100 text-red-700'
];


$priority_classes = [

    'low' =>
        'bg-gray-100 text-gray-700',

    'medium' =>
        'bg-blue-100 text-blue-700',

    'high' =>
        'bg-orange-100 text-orange-700',

    'urgent' =>
        'bg-red-100 text-red-700'
];


$status_class =
    $status_classes[$project['status']]
    ?? 'bg-gray-100 text-gray-700';


$priority_class =
    $priority_classes[$project['priority']]
    ?? 'bg-gray-100 text-gray-700';


/*
|--------------------------------------------------------------------------
| Deadline
|--------------------------------------------------------------------------
*/

$deadline_text = 'Not set';

$deadline_class = 'text-gray-600';

if (!empty($project['deadline'])) {

    $deadline_timestamp =
        strtotime($project['deadline']);

    $deadline_text =
        date('M d, Y', $deadline_timestamp);


    if (
        $project['status'] !== 'completed'
        &&
        $deadline_timestamp < strtotime(date('Y-m-d'))
    ) {

        $deadline_class = 'text-red-600';

    } else {

        $deadline_class = 'text-gray-700';
    }
}


/*
|--------------------------------------------------------------------------
| Progress
|--------------------------------------------------------------------------
*/

$progress = max(
    0,
    min(
        100,
        (int)$project['progress']
    )
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($project['name']) ?>
        | Project Manager
    </title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>


<body class="bg-gray-100">


<div class="min-h-screen flex">


    <!-- =========================================================
         SIDEBAR
    ========================================================== -->

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
                href="index.php"
                class="block px-4 py-3 rounded-lg bg-indigo-600"
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


    <!-- =========================================================
         MAIN
    ========================================================== -->

    <main class="flex-1 min-w-0">


        <!-- Header -->

        <header class="bg-white border-b">

            <div class="px-6 py-5">

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">


                    <div>

                        <div class="flex items-center gap-3 mb-2">

                            <a
                                href="index.php"
                                class="text-gray-500 hover:text-gray-900"
                            >
                                ← Projects
                            </a>

                            <span class="text-gray-300">
                                /
                            </span>

                            <span class="text-sm text-gray-500">
                                <?= htmlspecialchars($project['project_code']) ?>
                            </span>

                        </div>


                        <h2 class="text-2xl font-bold text-gray-900">

                            <?= htmlspecialchars($project['name']) ?>

                        </h2>


                        <div class="flex flex-wrap items-center gap-2 mt-3">


                            <span
                                class="px-3 py-1 rounded-full text-xs font-medium <?= $status_class ?>"
                            >

                                <?= ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $project['status']
                                    )
                                ) ?>

                            </span>


                            <span
                                class="px-3 py-1 rounded-full text-xs font-medium <?= $priority_class ?>"
                            >

                                <?= ucfirst(
                                    $project['priority']
                                ) ?>

                                Priority

                            </span>

                        </div>

                    </div>


                    <div class="flex gap-2">

                        <a
                            href="edit.php?id=<?= $id ?>"
                            class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700"
                        >
                            Edit Project
                        </a>

                    </div>

                </div>

            </div>

        </header>


        <!-- Content -->

        <section class="p-6">


            <?php if (!empty($_GET['success'])): ?>

                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">

                    <?= htmlspecialchars($_GET['success']) ?>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 TOP STAT CARDS
            ================================================== -->

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">


                <!-- Progress -->

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">

                    <div class="flex items-center justify-between mb-3">

                        <p class="text-sm text-gray-500">
                            Project Progress
                        </p>

                        <span class="font-bold text-indigo-600">
                            <?= $progress ?>%
                        </span>

                    </div>


                    <div class="w-full bg-gray-200 rounded-full h-2.5">

                        <div
                            class="bg-indigo-600 h-2.5 rounded-full"
                            style="width: <?= $progress ?>%"
                        ></div>

                    </div>

                </div>


                <!-- Tasks -->

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">

                    <p class="text-sm text-gray-500">
                        Total Tasks
                    </p>

                    <p class="text-2xl font-bold text-gray-900 mt-2">
                        <?= $task_stats['total'] ?>
                    </p>

                    <p class="text-xs text-gray-500 mt-1">
                        <?= $task_stats['completed'] ?> completed
                    </p>

                </div>


                <!-- Deadline -->

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">

                    <p class="text-sm text-gray-500">
                        Deadline
                    </p>

                    <p class="text-lg font-bold mt-2 <?= $deadline_class ?>">
                        <?= $deadline_text ?>
                    </p>

                    <?php if (!empty($project['deadline'])): ?>

                        <p class="text-xs text-gray-500 mt-1">
                            Project deadline
                        </p>

                    <?php endif; ?>

                </div>


                <!-- Budget -->

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">

                    <p class="text-sm text-gray-500">
                        Budget
                    </p>

                    <p class="text-2xl font-bold text-gray-900 mt-2">

                        <?= number_format(
                            (float)$project['budget'],
                            2
                        ) ?>

                    </p>

                    <p class="text-xs text-gray-500 mt-1">
                        Project budget
                    </p>

                </div>

            </div>


            <!-- =================================================
                 MAIN GRID
            ================================================== -->

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">


                <!-- =================================================
                     LEFT / MAIN COLUMN
                ================================================== -->

                <div class="lg:col-span-2 space-y-6">


                    <!-- Project Information -->

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">


                        <div class="px-6 py-5 border-b">

                            <h3 class="font-semibold text-gray-900">
                                Project Information
                            </h3>

                        </div>


                        <div class="p-6">


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                                <!-- Start Date -->

                                <div>

                                    <p class="text-sm text-gray-500">
                                        Start Date
                                    </p>

                                    <p class="font-medium text-gray-900 mt-1">

                                        <?= !empty($project['start_date'])
                                            ? date(
                                                'M d, Y',
                                                strtotime(
                                                    $project['start_date']
                                                )
                                            )
                                            : 'Not set'
                                        ?>

                                    </p>

                                </div>


                                <!-- Deadline -->

                                <div>

                                    <p class="text-sm text-gray-500">
                                        Deadline
                                    </p>

                                    <p class="font-medium <?= $deadline_class ?> mt-1">

                                        <?= $deadline_text ?>

                                    </p>

                                </div>


                                <!-- Client -->

                                <div>

                                    <p class="text-sm text-gray-500">
                                        Client
                                    </p>

                                    <p class="font-medium text-gray-900 mt-1">

                                        <?= htmlspecialchars(
                                            $project['client_name']
                                            ?? 'No Client'
                                        ) ?>

                                    </p>

                                    <?php if (!empty($project['client_company'])): ?>

                                        <p class="text-sm text-gray-500 mt-1">

                                            <?= htmlspecialchars(
                                                $project['client_company']
                                            ) ?>

                                        </p>

                                    <?php endif; ?>

                                </div>


                                <!-- Manager -->

                                <div>

                                    <p class="text-sm text-gray-500">
                                        Project Manager
                                    </p>

                                    <p class="font-medium text-gray-900 mt-1">

                                        <?= htmlspecialchars(
                                            $project['manager_name']
                                            ?? 'Unassigned'
                                        ) ?>

                                    </p>

                                    <?php if (!empty($project['manager_email'])): ?>

                                        <p class="text-sm text-gray-500 mt-1">

                                            <?= htmlspecialchars(
                                                $project['manager_email']
                                            ) ?>

                                        </p>

                                    <?php endif; ?>

                                </div>

                            </div>


                            <!-- Description -->

                            <?php if (!empty($project['description'])): ?>

                                <div class="mt-6 pt-6 border-t">

                                    <p class="text-sm text-gray-500 mb-2">
                                        Description
                                    </p>

                                    <div class="text-gray-700 leading-7 whitespace-pre-line">

                                        <?= htmlspecialchars(
                                            $project['description']
                                        ) ?>

                                    </div>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- Task Overview -->

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">


                        <div class="px-6 py-5 border-b flex items-center justify-between">

                            <div>

                                <h3 class="font-semibold text-gray-900">
                                    Task Overview
                                </h3>

                                <p class="text-sm text-gray-500 mt-1">
                                    Current project task progress
                                </p>

                            </div>


                            <a
                                href="../tasks/create.php?project_id=<?= $id ?>"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700"
                            >
                                + Add Task
                            </a>

                        </div>


                        <div class="p-6">


                            <?php if (!$task_table_exists): ?>

                                <div class="p-5 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-700">

                                    Tasks module is not created yet.

                                </div>

                            <?php else: ?>


                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">


                                    <div class="p-4 bg-gray-50 rounded-xl">

                                        <p class="text-sm text-gray-500">
                                            To Do
                                        </p>

                                        <p class="text-2xl font-bold mt-1">
                                            <?= $task_stats['todo'] ?>
                                        </p>

                                    </div>


                                    <div class="p-4 bg-blue-50 rounded-xl">

                                        <p class="text-sm text-blue-600">
                                            In Progress
                                        </p>

                                        <p class="text-2xl font-bold mt-1">
                                            <?= $task_stats['in_progress'] ?>
                                        </p>

                                    </div>


                                    <div class="p-4 bg-yellow-50 rounded-xl">

                                        <p class="text-sm text-yellow-600">
                                            Review
                                        </p>

                                        <p class="text-2xl font-bold mt-1">
                                            <?= $task_stats['review'] ?>
                                        </p>

                                    </div>


                                    <div class="p-4 bg-green-50 rounded-xl">

                                        <p class="text-sm text-green-600">
                                            Completed
                                        </p>

                                        <p class="text-2xl font-bold mt-1">
                                            <?= $task_stats['completed'] ?>
                                        </p>

                                    </div>

                                </div>


                                <!-- Task Completion -->

                                <div class="mt-6">

                                    <div class="flex justify-between mb-2">

                                        <span class="text-sm font-medium text-gray-700">
                                            Task Completion
                                        </span>

                                        <span class="text-sm text-gray-500">
                                            <?= $task_completion ?>%
                                        </span>

                                    </div>


                                    <div class="w-full bg-gray-200 rounded-full h-2">

                                        <div
                                            class="bg-green-500 h-2 rounded-full"
                                            style="width: <?= $task_completion ?>%"
                                        ></div>

                                    </div>

                                </div>


                                <div class="mt-6">

                                    <a
                                        href="../tasks/index.php?project_id=<?= $id ?>"
                                        class="inline-block px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50"
                                    >
                                        View All Tasks →
                                    </a>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>


                </div>


                <!-- =================================================
                     RIGHT SIDEBAR
                ================================================== -->

                <div class="space-y-6">


                    <!-- Client Card -->

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">


                        <div class="px-6 py-5 border-b">

                            <h3 class="font-semibold text-gray-900">
                                Client
                            </h3>

                        </div>


                        <div class="p-6">


                            <?php if (!empty($project['client_name'])): ?>

                                <div class="flex items-center gap-3">


                                    <div class="w-11 h-11 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-semibold">

                                        <?= strtoupper(
                                            substr(
                                                $project['client_name'],
                                                0,
                                                1
                                            )
                                        ) ?>

                                    </div>


                                    <div>

                                        <p class="font-semibold text-gray-900">

                                            <?= htmlspecialchars(
                                                $project['client_name']
                                            ) ?>

                                        </p>

                                        <?php if (!empty($project['client_company'])): ?>

                                            <p class="text-sm text-gray-500">

                                                <?= htmlspecialchars(
                                                    $project['client_company']
                                                ) ?>

                                            </p>

                                        <?php endif; ?>

                                    </div>

                                </div>


                                <?php if (!empty($project['client_email'])): ?>

                                    <div class="mt-5 pt-5 border-t">

                                        <p class="text-xs text-gray-500">
                                            Email
                                        </p>

                                        <p class="text-sm text-gray-700 mt-1 break-all">

                                            <?= htmlspecialchars(
                                                $project['client_email']
                                            ) ?>

                                        </p>

                                    </div>

                                <?php endif; ?>


                                <?php if (!empty($project['client_phone'])): ?>

                                    <div class="mt-4">

                                        <p class="text-xs text-gray-500">
                                            Phone
                                        </p>

                                        <p class="text-sm text-gray-700 mt-1">

                                            <?= htmlspecialchars(
                                                $project['client_phone']
                                            ) ?>

                                        </p>

                                    </div>

                                <?php endif; ?>

                            <?php else: ?>

                                <p class="text-gray-500">
                                    No client assigned.
                                </p>

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- Manager Card -->

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">


                        <div class="px-6 py-5 border-b">

                            <h3 class="font-semibold text-gray-900">
                                Project Manager
                            </h3>

                        </div>


                        <div class="p-6">


                            <?php if (!empty($project['manager_name'])): ?>

                                <div class="flex items-center gap-3">


                                    <div class="w-11 h-11 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-semibold">

                                        <?= strtoupper(
                                            substr(
                                                $project['manager_name'],
                                                0,
                                                1
                                            )
                                        ) ?>

                                    </div>


                                    <div>

                                        <p class="font-semibold text-gray-900">

                                            <?= htmlspecialchars(
                                                $project['manager_name']
                                            ) ?>

                                        </p>

                                        <p class="text-sm text-gray-500">
                                            Project Manager
                                        </p>

                                    </div>

                                </div>


                                <?php if (!empty($project['manager_email'])): ?>

                                    <p class="text-sm text-gray-600 mt-4 break-all">

                                        <?= htmlspecialchars(
                                            $project['manager_email']
                                        ) ?>

                                    </p>

                                <?php endif; ?>

                            <?php else: ?>

                                <p class="text-gray-500">
                                    No manager assigned.
                                </p>

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- Team Members -->

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">


                        <div class="px-6 py-5 border-b flex items-center justify-between">

                            <div>

                                <h3 class="font-semibold text-gray-900">
                                    Team Members
                                </h3>

                                <p class="text-xs text-gray-500 mt-1">

                                    <?= $team_members->num_rows ?>
                                    member(s)

                                </p>

                            </div>

                        </div>


                        <div class="p-6">


                            <?php if ($team_members->num_rows > 0): ?>


                                <div class="space-y-4">

                                    <?php while ($member = $team_members->fetch_assoc()): ?>


                                        <div class="flex items-center gap-3">


                                            <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-700 flex items-center justify-center font-semibold">

                                                <?= strtoupper(
                                                    substr(
                                                        $member['name'],
                                                        0,
                                                        1
                                                    )
                                                ) ?>

                                            </div>


                                            <div class="min-w-0">

                                                <p class="font-medium text-gray-900 truncate">

                                                    <?= htmlspecialchars(
                                                        $member['name']
                                                    ) ?>

                                                </p>

                                                <p class="text-xs text-gray-500 truncate">

                                                    <?= htmlspecialchars(
                                                        $member['email']
                                                    ) ?>

                                                </p>

                                            </div>

                                        </div>


                                    <?php endwhile; ?>

                                </div>


                            <?php else: ?>

                                <div class="text-center py-5">

                                    <p class="text-gray-500 text-sm">
                                        No team members assigned.
                                    </p>

                                    <a
                                        href="edit.php?id=<?= $id ?>"
                                        class="inline-block mt-3 text-sm text-indigo-600 hover:text-indigo-700"
                                    >
                                        Assign Members →
                                    </a>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>


                </div>

            </div>

        </section>

    </main>

</div>


</body>
</html>