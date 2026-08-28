
<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');
$project_id = (int)($_GET['project_id'] ?? 0);
$team_member_id = (int)($_GET['team_member_id'] ?? 0);
$status = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';

$per_page = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;


/*
|--------------------------------------------------------------------------
| Allowed Values
|--------------------------------------------------------------------------
*/

$allowed_status = [
    'pending',
    'in_progress',
    'completed',
    'on_hold',
    'cancelled'
];

$allowed_priority = [
    'low',
    'medium',
    'high',
    'urgent'
];


/*
|--------------------------------------------------------------------------
| WHERE Conditions
|--------------------------------------------------------------------------
*/

$where = [];
$params = [];
$types = '';


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $where[] = "
        (
            t.title LIKE ?
            OR t.description LIKE ?
            OR p.name LIKE ?
            OR p.project_code LIKE ?
            OR tm.name LIKE ?
        )
    ";

    $value = "%{$search}%";

    $params[] = $value;
    $params[] = $value;
    $params[] = $value;
    $params[] = $value;
    $params[] = $value;

    $types .= "sssss";
}


/*
|--------------------------------------------------------------------------
| Project Filter
|--------------------------------------------------------------------------
*/

if ($project_id > 0) {

    $where[] = "t.project_id = ?";

    $params[] = $project_id;

    $types .= "i";
}


/*
|--------------------------------------------------------------------------
| Team Member Filter
|--------------------------------------------------------------------------
*/

if ($team_member_id > 0) {

    $where[] = "t.team_member_id = ?";

    $params[] = $team_member_id;

    $types .= "i";
}


/*
|--------------------------------------------------------------------------
| Status Filter
|--------------------------------------------------------------------------
*/

if (in_array($status, $allowed_status, true)) {

    $where[] = "t.status = ?";

    $params[] = $status;

    $types .= "s";
}


/*
|--------------------------------------------------------------------------
| Priority Filter
|--------------------------------------------------------------------------
*/

if (in_array($priority, $allowed_priority, true)) {

    $where[] = "t.priority = ?";

    $params[] = $priority;

    $types .= "s";
}


$where_sql = '';

if (!empty($where)) {

    $where_sql = "WHERE " . implode(" AND ", $where);

}


/*
|--------------------------------------------------------------------------
| Total Tasks
|--------------------------------------------------------------------------
*/

$count_sql = "
    SELECT COUNT(*) AS total

    FROM tasks t

    INNER JOIN projects p
        ON p.id = t.project_id

    LEFT JOIN team_members tm
        ON tm.id = t.team_member_id

    $where_sql
";


$count_stmt = $conn->prepare($count_sql);

if (!$count_stmt) {
    die("Count query failed: " . $conn->error);
}


if (!empty($params)) {

    $count_stmt->bind_param(
        $types,
        ...$params
    );

}


$count_stmt->execute();

$total_tasks = (int)$count_stmt
    ->get_result()
    ->fetch_assoc()['total'];

$count_stmt->close();


$total_pages = max(
    1,
    (int)ceil($total_tasks / $per_page)
);


/*
|--------------------------------------------------------------------------
| Fetch Tasks
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

        p.name AS project_name,
        p.project_code,

        tm.name AS member_name

    FROM tasks t

    INNER JOIN projects p
        ON p.id = t.project_id

    LEFT JOIN team_members tm
        ON tm.id = t.team_member_id

    $where_sql

    ORDER BY

        CASE

            WHEN
                t.deadline IS NOT NULL
                AND t.deadline < CURDATE()
                AND t.status NOT IN ('completed', 'cancelled')

            THEN 0

            ELSE 1

        END,

        t.deadline ASC,
        t.id DESC

    LIMIT ?
    OFFSET ?
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Task query failed: " . $conn->error);
}


$query_params = $params;
$query_types = $types . "ii";

$query_params[] = $per_page;
$query_params[] = $offset;


$stmt->bind_param(
    $query_types,
    ...$query_params
);


$stmt->execute();

$tasks = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$statistics_sql = "

    SELECT

        COUNT(*) AS total,

        SUM(
            status = 'pending'
        ) AS pending,

        SUM(
            status = 'in_progress'
        ) AS in_progress,

        SUM(
            status = 'completed'
        ) AS completed,

        SUM(
            deadline IS NOT NULL
            AND deadline < CURDATE()
            AND status NOT IN ('completed', 'cancelled')
        ) AS overdue

    FROM tasks

";


$statistics_result = $conn->query(
    $statistics_sql
);


$statistics = $statistics_result->fetch_assoc();

$total_count = (int)($statistics['total'] ?? 0);
$pending_count = (int)($statistics['pending'] ?? 0);
$progress_count = (int)($statistics['in_progress'] ?? 0);
$completed_count = (int)($statistics['completed'] ?? 0);
$overdue_count = (int)($statistics['overdue'] ?? 0);


/*
|--------------------------------------------------------------------------
| Dropdown Data
|--------------------------------------------------------------------------
*/

$projects_list = $conn->query("
    SELECT id, name
    FROM projects
    ORDER BY name ASC
");


$members_list = $conn->query("
    SELECT id, name
    FROM team_members
    ORDER BY name ASC
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

    <title>Tasks | Project Manager</title>

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
                        Tasks
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Manage tasks, deadlines and team assignments
                    </p>

                </div>


                <a
                    href="create.php"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium"
                >
                    + New Task
                </a>


            </div>

        </header>


        <section class="p-6">


            <!-- =====================================================
                 SUCCESS MESSAGE
            ====================================================== -->

            <?php if (isset($_GET['success'])): ?>

    <?php

    $success_messages = [

        'created' =>
            'Task created successfully.',

        'updated' =>
            'Task updated successfully.',

        'deleted' =>
            'Task deleted successfully.'

    ];

    $success_key = $_GET['success'];

    ?>

    <?php if (isset($success_messages[$success_key])): ?>

        <div
            class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center justify-between"
        >

            <span>
                <?= htmlspecialchars(
                    $success_messages[$success_key]
                ) ?>
            </span>

            <button
                type="button"
                onclick="this.parentElement.remove()"
                class="text-green-700 hover:text-green-900 font-bold"
            >
                ×
            </button>

        </div>

    <?php endif; ?>

<?php endif; ?>


<?php if (isset($_GET['error'])): ?>

    <?php

    $error_messages = [

        'not_found' =>
            'The requested task was not found.'

    ];

    $error_key = $_GET['error'];

    ?>

    <?php if (isset($error_messages[$error_key])): ?>

        <div
            class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center justify-between"
        >

            <span>
                <?= htmlspecialchars(
                    $error_messages[$error_key]
                ) ?>
            </span>

            <button
                type="button"
                onclick="this.parentElement.remove()"
                class="text-red-700 hover:text-red-900 font-bold"
            >
                ×
            </button>

        </div>

    <?php endif; ?>

<?php endif; ?>


            <!-- =====================================================
                 STATISTICS
            ====================================================== -->

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">


                <!-- Total -->

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

                    <p class="text-sm text-gray-500">
                        Total Tasks
                    </p>

                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        <?= $total_count ?>
                    </p>

                </div>


                <!-- Pending -->

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

                    <p class="text-sm text-gray-500">
                        Pending
                    </p>

                    <p class="text-3xl font-bold text-yellow-600 mt-2">
                        <?= $pending_count ?>
                    </p>

                </div>


                <!-- In Progress -->

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

                    <p class="text-sm text-gray-500">
                        In Progress
                    </p>

                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        <?= $progress_count ?>
                    </p>

                </div>


                <!-- Completed -->

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

                    <p class="text-sm text-gray-500">
                        Completed
                    </p>

                    <p class="text-3xl font-bold text-green-600 mt-2">
                        <?= $completed_count ?>
                    </p>

                </div>


                <!-- Overdue -->

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

                    <p class="text-sm text-gray-500">
                        Overdue
                    </p>

                    <p class="text-3xl font-bold text-red-600 mt-2">
                        <?= $overdue_count ?>
                    </p>

                </div>


            </div>


            <!-- =====================================================
                 FILTERS
            ====================================================== -->

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">


                <form
                    method="GET"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4"
                >


                    <!-- Search -->

                    <div class="lg:col-span-2">

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($search) ?>"
                            placeholder="Task, project, member..."
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                        >

                    </div>


                    <!-- Project -->

                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Project
                        </label>

                        <select
                            name="project_id"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                        >

                            <option value="">
                                All Projects
                            </option>


                            <?php while ($project = $projects_list->fetch_assoc()): ?>

                                <option
                                    value="<?= (int)$project['id'] ?>"
                                    <?= $project_id == $project['id']
                                        ? 'selected'
                                        : ''
                                    ?>
                                >

                                    <?= htmlspecialchars(
                                        $project['name']
                                    ) ?>

                                </option>

                            <?php endwhile; ?>


                        </select>

                    </div>


                    <!-- Team Member -->

                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Team Member
                        </label>

                        <select
                            name="team_member_id"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                        >

                            <option value="">
                                All Members
                            </option>


                            <?php while ($member = $members_list->fetch_assoc()): ?>

                                <option
                                    value="<?= (int)$member['id'] ?>"
                                    <?= $team_member_id == $member['id']
                                        ? 'selected'
                                        : ''
                                    ?>
                                >

                                    <?= htmlspecialchars(
                                        $member['name']
                                    ) ?>

                                </option>

                            <?php endwhile; ?>


                        </select>

                    </div>


                    <!-- Status -->

                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>

                        <select
                            name="status"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                        >

                            <option value="">
                                All Status
                            </option>

                            <option
                                value="pending"
                                <?= $status === 'pending'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                Pending
                            </option>

                            <option
                                value="in_progress"
                                <?= $status === 'in_progress'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                In Progress
                            </option>

                            <option
                                value="completed"
                                <?= $status === 'completed'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                Completed
                            </option>

                            <option
                                value="on_hold"
                                <?= $status === 'on_hold'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                On Hold
                            </option>

                            <option
                                value="cancelled"
                                <?= $status === 'cancelled'
                                    ? 'selected'
                                    : ''
                                ?>
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
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                        >

                            <option value="">
                                All Priority
                            </option>

                            <option
                                value="low"
                                <?= $priority === 'low'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                Low
                            </option>

                            <option
                                value="medium"
                                <?= $priority === 'medium'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                Medium
                            </option>

                            <option
                                value="high"
                                <?= $priority === 'high'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                High
                            </option>

                            <option
                                value="urgent"
                                <?= $priority === 'urgent'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                Urgent
                            </option>

                        </select>

                    </div>


                    <!-- Buttons -->

                    <div class="flex items-end gap-2">

                        <button
                            type="submit"
                            class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2.5 rounded-xl"
                        >
                            Filter
                        </button>


                        <a
                            href="index.php"
                            class="px-5 py-2.5 border border-gray-300 rounded-xl hover:bg-gray-50"
                        >
                            Reset
                        </a>

                    </div>


                </form>

            </div>


            <!-- =====================================================
                 TASK TABLE
            ====================================================== -->

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">


                <div class="px-6 py-5 border-b">

                    <h3 class="font-semibold text-gray-900">
                        Task List
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        <?= $total_tasks ?> task(s)
                    </p>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full">


                        <thead class="bg-gray-50">

                            <tr>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Task
                                </th>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Project
                                </th>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Assigned To
                                </th>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Status
                                </th>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Priority
                                </th>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Progress
                                </th>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Deadline
                                </th>

                                <th class="text-right px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100">


                        <?php if ($tasks->num_rows > 0): ?>


                            <?php while ($task = $tasks->fetch_assoc()): ?>


                                <?php

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


                                /*
                                |--------------------------------------------------------------------------
                                | Deadline
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
                                        [
                                            'completed',
                                            'cancelled'
                                        ],
                                        true
                                    )
                                ) {

                                    $is_overdue = true;

                                }

                                ?>


                                <tr class="hover:bg-gray-50">


                                    <!-- Task -->

                                    <td class="px-6 py-4">

                                        <div>

                                            <a
                                                href="view.php?id=<?= (int)$task['id'] ?>"
                                                class="font-medium text-gray-900 hover:text-indigo-600"
                                            >

                                                <?= htmlspecialchars(
                                                    $task['title']
                                                ) ?>

                                            </a>


                                            <?php if (!empty($task['description'])): ?>

                                                <p class="text-xs text-gray-500 mt-1">

                                                    <?= htmlspecialchars(
                                                        mb_substr(
                                                            $task['description'],
                                                            0,
                                                            60
                                                        )
                                                    ) ?>

                                                    <?php if (
                                                        mb_strlen(
                                                            $task['description']
                                                        ) > 60
                                                    ): ?>

                                                        ...

                                                    <?php endif; ?>

                                                </p>

                                            <?php endif; ?>


                                        </div>

                                    </td>


                                    <!-- Project -->

                                    <td class="px-6 py-4">

                                        <p class="text-sm text-gray-700">

                                            <?= htmlspecialchars(
                                                $task['project_name']
                                            ) ?>

                                        </p>

                                        <p class="text-xs text-gray-500">

                                            <?= htmlspecialchars(
                                                $task['project_code']
                                            ) ?>

                                        </p>

                                    </td>


                                    <!-- Member -->

                                    <td class="px-6 py-4 text-sm text-gray-700">

                                        <?= htmlspecialchars(
                                            $task['member_name']
                                                ?? 'Unassigned'
                                        ) ?>

                                    </td>


                                    <!-- Status -->

                                    <td class="px-6 py-4">

                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-medium <?= $status_classes[$task['status']] ?? 'bg-gray-100 text-gray-700' ?>"
                                        >

                                            <?= ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $task['status']
                                                )
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- Priority -->

                                    <td class="px-6 py-4">

                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-medium <?= $priority_classes[$task['priority']] ?? 'bg-gray-100 text-gray-700' ?>"
                                        >

                                            <?= ucfirst(
                                                $task['priority']
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- Progress -->

                                    <td class="px-6 py-4 min-w-[160px]">

                                        <div class="flex items-center gap-3">

                                            <div class="flex-1 bg-gray-200 rounded-full h-2">

                                                <div
                                                    class="bg-indigo-600 h-2 rounded-full"
                                                    style="width: <?= (int)$task['progress'] ?>%"
                                                ></div>

                                            </div>

                                            <span class="text-sm text-gray-600">

                                                <?= (int)$task['progress'] ?>%

                                            </span>

                                        </div>

                                    </td>


                                    <!-- Deadline -->

                                    <td class="px-6 py-4 text-sm">


                                        <?php if (!empty($task['deadline'])): ?>

                                            <?php if ($is_overdue): ?>

                                                <span class="text-red-600 font-semibold">

                                                    <?= date(
                                                        'M d, Y',
                                                        strtotime(
                                                            $task['deadline']
                                                        )
                                                    ) ?>

                                                </span>

                                                <p class="text-xs text-red-500">
                                                    Overdue
                                                </p>

                                            <?php else: ?>

                                                <span class="text-gray-600">

                                                    <?= date(
                                                        'M d, Y',
                                                        strtotime(
                                                            $task['deadline']
                                                        )
                                                    ) ?>

                                                </span>

                                            <?php endif; ?>


                                        <?php else: ?>

                                            <span class="text-gray-400">
                                                —
                                            </span>

                                        <?php endif; ?>


                                    </td>


                                    <!-- Actions -->

                                    <td class="px-6 py-4 text-right">

                                        <div class="inline-flex gap-2">


                                            <a
                                                href="view.php?id=<?= (int)$task['id'] ?>"
                                                class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-sm hover:bg-gray-200"
                                            >
                                                View
                                            </a>


                                            <a
                                                href="edit.php?id=<?= (int)$task['id'] ?>"
                                                class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 text-sm hover:bg-indigo-100"
                                            >
                                                Edit
                                            </a>


                                            <a
                                                href="delete.php?id=<?= (int)$task['id'] ?>"
                                                onclick="return confirm('Delete this task?');"
                                                class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-sm hover:bg-red-100"
                                            >
                                                Delete
                                            </a>


                                        </div>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <tr>

                                <td
                                    colspan="8"
                                    class="px-6 py-16 text-center text-gray-500"
                                >

                                    <div class="text-lg font-medium">
                                        No tasks found
                                    </div>

                                    <p class="text-sm mt-1">
                                        Try changing your filters or create a new task.
                                    </p>

                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>

                    </table>

                </div>


                <!-- =================================================
                     PAGINATION
                ================================================== -->

                <?php if ($total_pages > 1): ?>

                    <div class="px-6 py-4 border-t flex items-center justify-between">


                        <span class="text-sm text-gray-500">

                            Page <?= $page ?>
                            of <?= $total_pages ?>

                        </span>


                        <div class="flex gap-2">


                            <?php if ($page > 1): ?>

                                <a
                                    href="?<?= http_build_query([
                                        'search' => $search,
                                        'project_id' => $project_id,
                                        'team_member_id' => $team_member_id,
                                        'status' => $status,
                                        'priority' => $priority,
                                        'page' => $page - 1
                                    ]) ?>"
                                    class="px-4 py-2 border rounded-lg hover:bg-gray-50"
                                >
                                    Previous
                                </a>

                            <?php endif; ?>


                            <?php if ($page < $total_pages): ?>

                                <a
                                    href="?<?= http_build_query([
                                        'search' => $search,
                                        'project_id' => $project_id,
                                        'team_member_id' => $team_member_id,
                                        'status' => $status,
                                        'priority' => $priority,
                                        'page' => $page + 1
                                    ]) ?>"
                                    class="px-4 py-2 border rounded-lg hover:bg-gray-50"
                                >
                                    Next
                                </a>

                            <?php endif; ?>


                        </div>

                    </div>

                <?php endif; ?>


            </div>


        </section>

    </main>

</div>


</body>

</html>