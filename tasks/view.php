<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Get Task ID
|--------------------------------------------------------------------------
*/

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Fetch Task
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT

        t.id,
        t.title,
        t.description,
        t.status,
        t.priority,
        t.deadline,
        t.progress,
        t.created_at,

        p.id AS project_id,
        p.name AS project_name,
        p.project_code,

        tm.id AS member_id,
        tm.name AS member_name,
        tm.email AS member_email,
        tm.role AS member_role

    FROM tasks t

    INNER JOIN projects p
        ON p.id = t.project_id

    LEFT JOIN team_members tm
        ON tm.id = t.team_member_id

    WHERE t.id = ?

    LIMIT 1
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

$task = $result->fetch_assoc();

$stmt->close();


if (!$task) {
    die("Task not found.");
}


/*
|--------------------------------------------------------------------------
| Status Classes
|--------------------------------------------------------------------------
*/

$status_classes = [

    'pending' =>
        'bg-yellow-100 text-yellow-700',

    'in_progress' =>
        'bg-blue-100 text-blue-700',

    'completed' =>
        'bg-green-100 text-green-700',

    'on_hold' =>
        'bg-gray-100 text-gray-700',

    'cancelled' =>
        'bg-red-100 text-red-700'

];


$status_class =
    $status_classes[$task['status']]
    ?? 'bg-gray-100 text-gray-700';


/*
|--------------------------------------------------------------------------
| Priority Classes
|--------------------------------------------------------------------------
*/

$priority_classes = [

    'low' =>
        'bg-gray-100 text-gray-600',

    'medium' =>
        'bg-blue-100 text-blue-700',

    'high' =>
        'bg-orange-100 text-orange-700',

    'urgent' =>
        'bg-red-100 text-red-700'

];


$priority_class =
    $priority_classes[$task['priority']]
    ?? 'bg-gray-100 text-gray-700';


/*
|--------------------------------------------------------------------------
| Overdue
|--------------------------------------------------------------------------
*/

$is_overdue = false;

if (
    !empty($task['deadline'])
    &&
    $task['deadline'] < date('Y-m-d')
    &&
    !in_array(
        $task['status'],
        ['completed', 'cancelled'],
        true
    )
) {

    $is_overdue = true;

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
        (int)$task['progress']
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
        <?= htmlspecialchars($task['title']) ?>
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
                href="../projects/index.php"
                class="block px-4 py-3 rounded-lg hover:bg-gray-800"
            >
                Projects
            </a>


            <a
                href="index.php"
                class="block px-4 py-3 rounded-lg bg-indigo-600"
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

    <main class="flex-1">


        <!-- Header -->

        <header class="bg-white border-b">

            <div class="px-6 py-4 flex items-center justify-between">


                <div>

                    <h2 class="text-xl font-semibold text-gray-900">
                        Task Details
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        View task information and progress
                    </p>

                </div>


                <div class="flex items-center gap-2">


                    <a
                        href="index.php"
                        class="px-4 py-2.5 border border-gray-300 rounded-xl hover:bg-gray-50 text-sm"
                    >
                        ← Back
                    </a>


                    <a
                        href="edit.php?id=<?= $id ?>"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium"
                    >
                        Edit Task
                    </a>


                </div>


            </div>

        </header>


        <section class="p-6">


            <div class="max-w-5xl mx-auto">


                <!-- =================================================
                     TASK HEADER CARD
                ================================================== -->

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">


                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">


                        <div class="flex-1">


                            <div class="flex flex-wrap items-center gap-3 mb-3">


                                <span
                                    class="px-3 py-1 rounded-full text-xs font-medium <?= $status_class ?>"
                                >

                                    <?= ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $task['status']
                                        )
                                    ) ?>

                                </span>


                                <span
                                    class="px-3 py-1 rounded-full text-xs font-medium <?= $priority_class ?>"
                                >

                                    <?= ucfirst(
                                        $task['priority']
                                    ) ?>

                                </span>


                                <?php if ($is_overdue): ?>

                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700"
                                    >
                                        Overdue
                                    </span>

                                <?php endif; ?>


                            </div>


                            <h1 class="text-2xl font-bold text-gray-900">

                                <?= htmlspecialchars(
                                    $task['title']
                                ) ?>

                            </h1>


                            <p class="text-sm text-gray-500 mt-2">

                                Task #<?= (int)$task['id'] ?>

                            </p>


                        </div>


                        <!-- Project -->

                        <div class="lg:text-right">


                            <p class="text-xs text-gray-500 uppercase font-semibold">
                                Project
                            </p>


                            <a
                                href="../projects/view.php?id=<?= (int)$task['project_id'] ?>"
                                class="text-indigo-600 hover:text-indigo-700 font-medium"
                            >

                                <?= htmlspecialchars(
                                    $task['project_name']
                                ) ?>

                            </a>


                            <p class="text-xs text-gray-500 mt-1">

                                <?= htmlspecialchars(
                                    $task['project_code']
                                ) ?>

                            </p>


                        </div>


                    </div>


                </div>


                <!-- =================================================
                     MAIN GRID
                ================================================== -->

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">


                    <!-- =================================================
                         LEFT
                    ================================================== -->

                    <div class="lg:col-span-2 space-y-6">


                        <!-- Description -->

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">


                            <h3 class="font-semibold text-gray-900 mb-4">
                                Description
                            </h3>


                            <?php if (!empty($task['description'])): ?>

                                <div class="text-gray-600 leading-7 whitespace-pre-line">

                                    <?= htmlspecialchars(
                                        $task['description']
                                    ) ?>

                                </div>

                            <?php else: ?>

                                <p class="text-gray-400">
                                    No description provided.
                                </p>

                            <?php endif; ?>


                        </div>


                        <!-- Progress -->

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">


                            <div class="flex items-center justify-between mb-4">


                                <h3 class="font-semibold text-gray-900">
                                    Progress
                                </h3>


                                <span class="text-lg font-bold text-indigo-600">

                                    <?= $progress ?>%

                                </span>


                            </div>


                            <div class="w-full bg-gray-200 rounded-full h-3">


                                <div
                                    class="bg-indigo-600 h-3 rounded-full transition-all"
                                    style="width: <?= $progress ?>%"
                                ></div>


                            </div>


                            <div class="flex justify-between text-xs text-gray-400 mt-2">

                                <span>0%</span>

                                <span>50%</span>

                                <span>100%</span>

                            </div>


                        </div>


                        <!-- Task Information -->

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">


                            <h3 class="font-semibold text-gray-900 mb-5">
                                Task Information
                            </h3>


                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">


                                <!-- Status -->

                                <div>

                                    <p class="text-xs text-gray-500 uppercase font-semibold mb-2">
                                        Status
                                    </p>

                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-medium <?= $status_class ?>"
                                    >

                                        <?= ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $task['status']
                                            )
                                        ) ?>

                                    </span>

                                </div>


                                <!-- Priority -->

                                <div>

                                    <p class="text-xs text-gray-500 uppercase font-semibold mb-2">
                                        Priority
                                    </p>

                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-medium <?= $priority_class ?>"
                                    >

                                        <?= ucfirst(
                                            $task['priority']
                                        ) ?>

                                    </span>

                                </div>


                                <!-- Deadline -->

                                <div>

                                    <p class="text-xs text-gray-500 uppercase font-semibold mb-2">
                                        Deadline
                                    </p>


                                    <?php if (!empty($task['deadline'])): ?>

                                        <p
                                            class="<?= $is_overdue
                                                ? 'text-red-600 font-semibold'
                                                : 'text-gray-700'
                                            ?>"
                                        >

                                            <?= date(
                                                'M d, Y',
                                                strtotime(
                                                    $task['deadline']
                                                )
                                            ) ?>

                                        </p>


                                        <?php if ($is_overdue): ?>

                                            <p class="text-xs text-red-500 mt-1">
                                                This task is overdue
                                            </p>

                                        <?php endif; ?>


                                    <?php else: ?>

                                        <p class="text-gray-400">
                                            No deadline
                                        </p>

                                    <?php endif; ?>


                                </div>


                                <!-- Created -->

                                <div>

                                    <p class="text-xs text-gray-500 uppercase font-semibold mb-2">
                                        Created
                                    </p>

                                    <p class="text-gray-700">

                                        <?= !empty($task['created_at'])
                                            ? date(
                                                'M d, Y',
                                                strtotime(
                                                    $task['created_at']
                                                )
                                            )
                                            : '—'
                                        ?>

                                    </p>

                                </div>


                            </div>


                        </div>


                    </div>


                    <!-- =================================================
                         RIGHT
                    ================================================== -->

                    <div class="space-y-6">


                        <!-- Assigned Member -->

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">


                            <h3 class="font-semibold text-gray-900 mb-5">
                                Assigned To
                            </h3>


                            <?php if (!empty($task['member_id'])): ?>


                                <div class="flex items-center gap-4">


                                    <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-lg">

                                        <?= strtoupper(
                                            substr(
                                                $task['member_name'],
                                                0,
                                                1
                                            )
                                        ) ?>

                                    </div>


                                    <div>

                                        <p class="font-medium text-gray-900">

                                            <?= htmlspecialchars(
                                                $task['member_name']
                                            ) ?>

                                        </p>


                                        <?php if (!empty($task['member_role'])): ?>

                                            <p class="text-sm text-gray-500">

                                                <?= htmlspecialchars(
                                                    $task['member_role']
                                                ) ?>

                                            </p>

                                        <?php endif; ?>


                                    </div>


                                </div>


                                <?php if (!empty($task['member_email'])): ?>

                                    <div class="mt-5 pt-5 border-t">

                                        <p class="text-xs text-gray-500 uppercase font-semibold mb-2">
                                            Email
                                        </p>

                                        <a
                                            href="mailto:<?= htmlspecialchars($task['member_email']) ?>"
                                            class="text-sm text-indigo-600 hover:text-indigo-700 break-all"
                                        >

                                            <?= htmlspecialchars(
                                                $task['member_email']
                                            ) ?>

                                        </a>

                                    </div>

                                <?php endif; ?>


                            <?php else: ?>


                                <div class="text-center py-6">

                                    <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-gray-400">

                                        —

                                    </div>


                                    <p class="text-gray-500 mt-3">
                                        No team member assigned
                                    </p>


                                </div>


                            <?php endif; ?>


                        </div>


                        <!-- Project -->

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">


                            <h3 class="font-semibold text-gray-900 mb-5">
                                Project
                            </h3>


                            <p class="text-xs text-gray-500 uppercase font-semibold mb-2">
                                Project Name
                            </p>


                            <a
                                href="../projects/view.php?id=<?= (int)$task['project_id'] ?>"
                                class="font-medium text-indigo-600 hover:text-indigo-700"
                            >

                                <?= htmlspecialchars(
                                    $task['project_name']
                                ) ?>

                            </a>


                            <p class="text-xs text-gray-500 mt-2">

                                <?= htmlspecialchars(
                                    $task['project_code']
                                ) ?>

                            </p>


                        </div>


                        <!-- Quick Actions -->

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">


                            <h3 class="font-semibold text-gray-900 mb-4">
                                Quick Actions
                            </h3>


                            <div class="space-y-2">


                                <a
                                    href="edit.php?id=<?= $id ?>"
                                    class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium"
                                >
                                    Edit Task
                                </a>


                                <a
                                    href="delete.php?id=<?= $id ?>"
                                    onclick="return confirm('Are you sure you want to delete this task?');"
                                    class="block w-full text-center bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2.5 rounded-xl text-sm font-medium"
                                >
                                    Delete Task
                                </a>


                            </div>


                        </div>


                    </div>


                </div>


            </div>


        </section>


    </main>


</div>


</body>

</html>