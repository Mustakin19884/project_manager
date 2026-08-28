<?php

require_once "../config/auth.php";
require_once "../config/db.php";

$search = trim($_GET['search'] ?? '');
$role   = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

$where = [];
$params = [];
$types = '';

if ($search !== '') {

    $where[] = "(name LIKE ? OR email LIKE ?)";

    $value = "%{$search}%";

    $params[] = $value;
    $params[] = $value;

    $types .= "ss";
}

if (in_array($role, ['admin', 'project_manager', 'team_member'], true)) {

    $where[] = "role = ?";

    $params[] = $role;

    $types .= "s";
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

$sql = "
    SELECT
        id,
        name,
        email,
        role,
        status,
        created_at
    FROM users
    $where_sql
    ORDER BY id DESC
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$members = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Team Members | Project Manager</title>

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
                href="../clients/index.php"
                class="block px-4 py-3 rounded-lg hover:bg-gray-800"
            >
                Clients
            </a>

            <a
                href="index.php"
                class="block px-4 py-3 rounded-lg bg-indigo-600"
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
                        Team Members
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Manage project managers and team members
                    </p>

                </div>

                <a
                    href="create.php"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium"
                >
                    + Add Member
                </a>

            </div>

        </header>


        <section class="p-6">


            <?php if (isset($_GET['success'])): ?>

                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">

                    <?= htmlspecialchars($_GET['success']) ?>

                </div>

            <?php endif; ?>


            <?php if (isset($_GET['error'])): ?>

                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">

                    <?= htmlspecialchars($_GET['error']) ?>

                </div>

            <?php endif; ?>


            <!-- Filters -->

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">

                <form
                    method="GET"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4"
                >

                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($search) ?>"
                            placeholder="Name or email..."
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                        >

                    </div>


                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Role
                        </label>

                        <select
                            name="role"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl outline-none"
                        >

                            <option value="">
                                All Roles
                            </option>

                            <option
                                value="admin"
                                <?= $role === 'admin' ? 'selected' : '' ?>
                            >
                                Admin
                            </option>

                            <option
                                value="project_manager"
                                <?= $role === 'project_manager' ? 'selected' : '' ?>
                            >
                                Project Manager
                            </option>

                            <option
                                value="team_member"
                                <?= $role === 'team_member' ? 'selected' : '' ?>
                            >
                                Team Member
                            </option>

                        </select>

                    </div>


                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>

                        <select
                            name="status"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl outline-none"
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
                            class="bg-gray-900 text-white px-5 py-2.5 rounded-xl"
                        >
                            Filter
                        </button>

                        <a
                            href="index.php"
                            class="px-5 py-2.5 border rounded-xl"
                        >
                            Reset
                        </a>

                    </div>

                </form>

            </div>


            <!-- Table -->

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                <div class="px-6 py-5 border-b">

                    <h3 class="font-semibold">
                        Team List
                    </h3>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-gray-50">

                        <tr>

                            <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                Member
                            </th>

                            <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                Role
                            </th>

                            <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                Status
                            </th>

                            <th class="text-left px-6 py-4 text-xs uppercase text-gray-500">
                                Joined
                            </th>

                            <th class="text-right px-6 py-4 text-xs uppercase text-gray-500">
                                Actions
                            </th>

                        </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100">

                        <?php if ($members->num_rows > 0): ?>

                            <?php while ($member = $members->fetch_assoc()): ?>

                                <tr class="hover:bg-gray-50">


                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-3">

                                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-semibold">

                                                <?= strtoupper(
                                                    substr($member['name'], 0, 1)
                                                ) ?>

                                            </div>

                                            <div>

                                                <p class="font-medium text-gray-900">

                                                    <?= htmlspecialchars(
                                                        $member['name']
                                                    ) ?>

                                                </p>

                                                <p class="text-sm text-gray-500">

                                                    <?= htmlspecialchars(
                                                        $member['email']
                                                    ) ?>

                                                </p>

                                            </div>

                                        </div>

                                    </td>


                                    <td class="px-6 py-4">

                                        <?php

                                        $role_labels = [
                                            'admin' => 'Admin',
                                            'project_manager' => 'Project Manager',
                                            'team_member' => 'Team Member'
                                        ];

                                        ?>

                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">

                                            <?= $role_labels[$member['role']]
                                                ?? ucfirst($member['role']) ?>

                                        </span>

                                    </td>


                                    <td class="px-6 py-4">

                                        <?php if ($member['status'] === 'active'): ?>

                                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                Active
                                            </span>

                                        <?php else: ?>

                                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                Inactive
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td class="px-6 py-4 text-sm text-gray-600">

                                        <?= !empty($member['created_at'])
                                            ? date(
                                                'M d, Y',
                                                strtotime($member['created_at'])
                                            )
                                            : '—'
                                        ?>

                                    </td>


                                    <td class="px-6 py-4 text-right">

                                        <div class="inline-flex gap-2">

                                            <a
                                                href="edit.php?id=<?= $member['id'] ?>"
                                                class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-sm"
                                            >
                                                Edit
                                            </a>

                                            <?php if ($member['role'] !== 'admin'): ?>

                                                <a
                                                    href="delete.php?id=<?= $member['id'] ?>"
                                                    onclick="return confirm('Are you sure you want to delete this member?');"
                                                    class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-sm"
                                                >
                                                    Delete
                                                </a>

                                            <?php endif; ?>

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
                                    No team members found.
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