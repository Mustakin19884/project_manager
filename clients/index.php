<?php

require_once "../config/auth.php";
require_once "../config/db.php";


// Search
$search = trim($_GET['search'] ?? '');


// Status filter
$status = $_GET['status'] ?? '';


// Pagination
$per_page = 10;

$page = max(1, (int)($_GET['page'] ?? 1));

$offset = ($page - 1) * $per_page;


// Build WHERE
$where = [];
$params = [];
$types = '';

if ($search !== '') {

    $where[] = "(name LIKE ? OR company LIKE ? OR email LIKE ?)";

    $search_value = "%{$search}%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "sss";
}

if (in_array($status, ['active', 'inactive'], true)) {

    $where[] = "status = ?";

    $params[] = $status;

    $types .= "s";
}


$where_sql = '';

if (!empty($where)) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}


// Total clients
$count_sql = "
    SELECT COUNT(*) AS total
    FROM clients
    $where_sql
";

$count_stmt = $conn->prepare($count_sql);

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();

$total_clients = $count_stmt
    ->get_result()
    ->fetch_assoc()['total'];

$count_stmt->close();

$total_pages = max(
    1,
    (int)ceil($total_clients / $per_page)
);


// Fetch clients
$sql = "
    SELECT
        id,
        name,
        company,
        email,
        phone,
        website,
        status,
        created_at
    FROM clients
    $where_sql
    ORDER BY id DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);

$query_params = $params;
$query_types = $types . "ii";

$query_params[] = $per_page;
$query_params[] = $offset;

$stmt->bind_param($query_types, ...$query_params);

$stmt->execute();

$clients = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Clients | Project Manager</title>

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
                href="index.php"
                class="block px-4 py-3 rounded-lg bg-indigo-600"
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

            <div class="px-6 py-4 flex items-center justify-between">

                <div>

                    <h2 class="text-xl font-semibold text-gray-900">
                        Clients
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Manage your project clients
                    </p>

                </div>


                <a
                    href="create.php"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition"
                >
                    + Add Client
                </a>

            </div>

        </header>


        <!-- Content -->

        <section class="p-6">


            <!-- Success -->

            <?php if (isset($_GET['success'])): ?>

                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">

                    <?= htmlspecialchars($_GET['success']) ?>

                </div>

            <?php endif; ?>


            <!-- Search / Filter -->

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">

                <form
                    method="GET"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4"
                >

                    <div class="md:col-span-2">

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($search) ?>"
                            placeholder="Search by name, company or email..."
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                        >

                    </div>


                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>

                        <select
                            name="status"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                        >

                            <option value="">
                                All Status
                            </option>

                            <option
                                value="active"
                                <?= $status === 'active' ? 'selected' : '' ?>
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                <?= $status === 'inactive' ? 'selected' : '' ?>
                            >
                                Inactive
                            </option>

                        </select>

                    </div>


                    <div class="flex items-end gap-2">

                        <button
                            type="submit"
                            class="bg-gray-900 text-white px-5 py-2.5 rounded-xl hover:bg-gray-800"
                        >
                            Filter
                        </button>

                        <a
                            href="index.php"
                            class="px-5 py-2.5 rounded-xl border border-gray-300 hover:bg-gray-50"
                        >
                            Reset
                        </a>

                    </div>

                </form>

            </div>


            <!-- Table -->

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                <div class="px-6 py-5 border-b">

                    <h3 class="font-semibold text-gray-900">
                        Client List
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        <?= $total_clients ?> client(s) found
                    </p>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Client
                                </th>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Company
                                </th>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Contact
                                </th>

                                <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Status
                                </th>

                                <th class="text-right px-6 py-4 text-xs font-semibold text-gray-500 uppercase">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100">

                        <?php if ($clients->num_rows > 0): ?>

                            <?php while ($client = $clients->fetch_assoc()): ?>

                                <tr class="hover:bg-gray-50">


                                    <!-- Client -->

                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-3">

                                            <div
                                                class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-semibold"
                                            >
                                                <?= strtoupper(
                                                    substr($client['name'], 0, 1)
                                                ) ?>
                                            </div>

                                            <div>

                                                <p class="font-medium text-gray-900">
                                                    <?= htmlspecialchars($client['name']) ?>
                                                </p>

                                                <p class="text-sm text-gray-500">
                                                    <?= htmlspecialchars($client['email'] ?? '') ?>
                                                </p>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- Company -->

                                    <td class="px-6 py-4 text-sm text-gray-600">

                                        <?= htmlspecialchars(
                                            $client['company'] ?: '—'
                                        ) ?>

                                    </td>


                                    <!-- Contact -->

                                    <td class="px-6 py-4">

                                        <p class="text-sm text-gray-700">
                                            <?= htmlspecialchars(
                                                $client['phone'] ?: '—'
                                            ) ?>
                                        </p>

                                        <?php if (!empty($client['website'])): ?>

                                            <p class="text-xs text-indigo-600 mt-1">

                                                <?= htmlspecialchars(
                                                    $client['website']
                                                ) ?>

                                            </p>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Status -->

                                    <td class="px-6 py-4">

                                        <?php if ($client['status'] === 'active'): ?>

                                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                Active
                                            </span>

                                        <?php else: ?>

                                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                Inactive
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Actions -->

                                    <td class="px-6 py-4 text-right">

                                        <div class="inline-flex items-center gap-2">

                                            <a
                                                href="edit.php?id=<?= $client['id'] ?>"
                                                class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 text-sm hover:bg-indigo-100"
                                            >
                                                Edit
                                            </a>

                                            <a
                                                href="delete.php?id=<?= $client['id'] ?>"
                                                onclick="return confirm('Are you sure you want to delete this client?');"
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
                                    colspan="5"
                                    class="px-6 py-16 text-center text-gray-500"
                                >
                                    No clients found.

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>


                <!-- Pagination -->

                <?php if ($total_pages > 1): ?>

                    <div class="px-6 py-4 border-t flex items-center justify-between">

                        <p class="text-sm text-gray-500">

                            Page <?= $page ?>
                            of <?= $total_pages ?>

                        </p>


                        <div class="flex gap-2">

                            <?php if ($page > 1): ?>

                                <a
                                    href="?<?= http_build_query([
                                        'search' => $search,
                                        'status' => $status,
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
                                        'status' => $status,
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