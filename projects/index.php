<?php

require_once "../config/auth.php";
require_once "../config/db.php";

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';

$per_page = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(p.name LIKE ? OR p.project_code LIKE ? OR c.name LIKE ? OR c.company LIKE ?)";

    $value = "%{$search}%";

    $params[] = $value;
    $params[] = $value;
    $params[] = $value;
    $params[] = $value;

    $types .= "ssss";
}

if (in_array($status, [
    'planning',
    'in_progress',
    'on_hold',
    'completed',
    'cancelled'
], true)) {
    $where[] = "p.status = ?";
    $params[] = $status;
    $types .= "s";
}

if (in_array($priority, [
    'low',
    'medium',
    'high',
    'urgent'
], true)) {
    $where[] = "p.priority = ?";
    $params[] = $priority;
    $types .= "s";
}

$where_sql = '';

if (!empty($where)) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}


/*
|--------------------------------------------------------------------------
| Total projects
|--------------------------------------------------------------------------
*/

$count_sql = "
    SELECT COUNT(*) AS total
    FROM projects p
    LEFT JOIN clients c ON p.client_id = c.id
    $where_sql
";

$count_stmt = $conn->prepare($count_sql);

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();

$total_projects = $count_stmt
    ->get_result()
    ->fetch_assoc()['total'];

$count_stmt->close();

$total_pages = max(
    1,
    (int)ceil($total_projects / $per_page)
);


/*
|--------------------------------------------------------------------------
| Fetch projects
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        p.id,
        p.project_code,
        p.name,
        p.start_date,
        p.deadline,
        p.budget,
        p.priority,
        p.status,
        p.progress,

        c.name AS client_name,
        c.company AS client_company,

        u.name AS manager_name

    FROM projects p

    LEFT JOIN clients c
        ON p.client_id = c.id

    LEFT JOIN users u
        ON p.manager_id = u.id

    $where_sql

    ORDER BY p.id DESC

    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);

$query_params = $params;
$query_types = $types . "ii";

$query_params[] = $per_page;
$query_params[] = $offset;

$stmt->bind_param(
    $query_types,
    ...$query_params
);

$stmt->execute();

$projects = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Projects | Project Manager</title>

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


    <!-- Main -->

    <main class="flex-1">

        <header class="bg-white border-b">

            <div class="px-6 py-4 flex items-center justify-between">

                <div>

                    <h2 class="text-xl font-semibold text-gray-900">
                        Projects
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Manage all your projects
                    </p>

                </div>

                <a
                    href="create.php"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium"
                >
                    + New Project
                </a>

            </div>

        </header>


        <section class="p-6">


            <?php if (isset($_GET['success'])): ?>

                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">

                    <?= htmlspecialchars($_GET['success']) ?>

                </div>

            <?php endif; ?>


            <!-- Filters -->

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">

                <form
                    method="GET"
                    class="grid grid-cols-1 md:grid-cols-5 gap-4"
                >

                    <div class="md:col-span-2">

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($search) ?>"
                            placeholder="Project, code, client..."
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                        >

                    </div>


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

                            <option value="planning" <?= $status === 'planning' ? 'selected' : '' ?>>
                                Planning
                            </option>

                            <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>
                                In Progress
                            </option>

                            <option value="on_hold" <?= $status === 'on_hold' ? 'selected' : '' ?>>
                                On Hold
                            </option>

                            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>
                                Completed
                            </option>

                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>
                                Cancelled
                            </option>

                        </select>

                    </div>


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

                            <option value="low" <?= $priority === 'low' ? 'selected' : '' ?>>
                                Low
                            </option>

                            <option value="medium" <?= $priority === 'medium' ? 'selected' : '' ?>>
                                Medium
                            </option>

                            <option value="high" <?= $priority === 'high' ? 'selected' : '' ?>>
                                High
                            </option>

                            <option value="urgent" <?= $priority === 'urgent' ? 'selected' : '' ?>>
                                Urgent
                            </option>

                        </select>

                    </div>


                    <div class="flex items-end gap-2">

                        <button
                            type="submit"
                            class="bg-gray-900 text-white px-5 py-2.5 rounded-xl"
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


            <!-- Project table -->

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                <div class="px-6 py-5 border-b">

                    <h3 class="font-semibold text-gray-900">
                        Project List
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        <?= $total_projects ?> project(s)
                    </p>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Project
                                </th>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Client
                                </th>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Manager
                                </th>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Status
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

                        <?php if ($projects->num_rows > 0): ?>

                            <?php while ($project = $projects->fetch_assoc()): ?>

                                <?php

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
                                        'bg-gray-100 text-gray-600',

                                    'medium' =>
                                        'bg-blue-100 text-blue-700',

                                    'high' =>
                                        'bg-orange-100 text-orange-700',

                                    'urgent' =>
                                        'bg-red-100 text-red-700'
                                ];

                                ?>


                                <tr class="hover:bg-gray-50">


                                    <!-- Project -->

                                    <td class="px-6 py-4">

                                        <div>

                                            <a
                                                href="view.php?id=<?= $project['id'] ?>"
                                                class="font-medium text-gray-900 hover:text-indigo-600"
                                            >
                                                <?= htmlspecialchars($project['name']) ?>
                                            </a>

                                            <p class="text-xs text-gray-500 mt-1">
                                                <?= htmlspecialchars($project['project_code']) ?>
                                            </p>

                                        </div>

                                    </td>


                                    <!-- Client -->

                                    <td class="px-6 py-4">

                                        <p class="text-sm text-gray-700">

                                            <?= htmlspecialchars(
                                                $project['client_name'] ?? 'No Client'
                                            ) ?>

                                        </p>

                                        <?php if (!empty($project['client_company'])): ?>

                                            <p class="text-xs text-gray-500">
                                                <?= htmlspecialchars($project['client_company']) ?>
                                            </p>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Manager -->

                                    <td class="px-6 py-4 text-sm text-gray-700">

                                        <?= htmlspecialchars(
                                            $project['manager_name'] ?? 'Unassigned'
                                        ) ?>

                                    </td>


                                    <!-- Status -->

                                    <td class="px-6 py-4">

                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-medium <?= $status_classes[$project['status']] ?>"
                                        >

                                            <?= ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $project['status']
                                                )
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- Progress -->

                                    <td class="px-6 py-4 min-w-[180px]">

                                        <div class="flex items-center gap-3">

                                            <div class="flex-1 bg-gray-200 rounded-full h-2">

                                                <div
                                                    class="bg-indigo-600 h-2 rounded-full"
                                                    style="width: <?= (int)$project['progress'] ?>%"
                                                ></div>

                                            </div>

                                            <span class="text-sm text-gray-600">
                                                <?= (int)$project['progress'] ?>%
                                            </span>

                                        </div>

                                    </td>


                                    <!-- Deadline -->

                                    <td class="px-6 py-4 text-sm text-gray-600">

                                        <?= $project['deadline']
                                            ? date(
                                                'M d, Y',
                                                strtotime($project['deadline'])
                                            )
                                            : '—'
                                        ?>

                                    </td>


                                    <!-- Actions -->

                                    <td class="px-6 py-4 text-right">

                                        <div class="inline-flex gap-2">

                                            <a
                                                href="view.php?id=<?= $project['id'] ?>"
                                                class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-sm"
                                            >
                                                View
                                            </a>

                                            <a
                                                href="edit.php?id=<?= $project['id'] ?>"
                                                class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 text-sm"
                                            >
                                                Edit
                                            </a>

                                            <a
                                                href="delete.php?id=<?= $project['id'] ?>"
                                                onclick="return confirm('Delete this project?');"
                                                class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-sm"
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
                                    colspan="7"
                                    class="px-6 py-16 text-center text-gray-500"
                                >
                                    No projects found.
                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>


                <!-- Pagination -->

                <?php if ($total_pages > 1): ?>

                    <div class="px-6 py-4 border-t flex justify-between">

                        <span class="text-sm text-gray-500">

                            Page <?= $page ?>
                            of <?= $total_pages ?>

                        </span>


                        <div class="flex gap-2">

                            <?php if ($page > 1): ?>

                                <a
                                    href="?<?= http_build_query([
                                        'search' => $search,
                                        'status' => $status,
                                        'priority' => $priority,
                                        'page' => $page - 1
                                    ]) ?>"
                                    class="px-4 py-2 border rounded-lg"
                                >
                                    Previous
                                </a>

                            <?php endif; ?>


                            <?php if ($page < $total_pages): ?>

                                <a
                                    href="?<?= http_build_query([
                                        'search' => $search,
                                        'status' => $status,
                                        'priority' => $priority,
                                        'page' => $page + 1
                                    ]) ?>"
                                    class="px-4 py-2 border rounded-lg"
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