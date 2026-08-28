<?php

require_once "../config/auth.php";
require_once "../config/db.php";

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT id, name, email, role, status
    FROM users
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database Error: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$member = $result->fetch_assoc();

$stmt->close();

if (!$member) {
    header("Location: index.php?error=" . urlencode("Team member not found."));
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Team Member | Project Manager</title>

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

        <!-- Header -->

        <header class="bg-white border-b">

            <div class="px-6 py-4">

                <h2 class="text-xl font-semibold text-gray-900">
                    Edit Team Member
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Update team member information
                </p>

            </div>

        </header>


        <!-- Content -->

        <section class="p-6">

            <div class="max-w-3xl mx-auto">


                <!-- Error -->

                <?php if (!empty($_GET['error'])): ?>

                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">

                        <?= htmlspecialchars($_GET['error']) ?>

                    </div>

                <?php endif; ?>


                <!-- Form -->

                <form
                    action="update.php"
                    method="POST"
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
                >

                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int)$member['id'] ?>"
                    >


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                        <!-- Name -->

                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name *
                            </label>

                            <input
                                type="text"
                                name="name"
                                required
                                value="<?= htmlspecialchars($member['name']) ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                        </div>


                        <!-- Email -->

                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email *
                            </label>

                            <input
                                type="email"
                                name="email"
                                required
                                value="<?= htmlspecialchars($member['email']) ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                        </div>


                        <!-- New Password -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                New Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                minlength="6"
                                placeholder="Leave blank to keep current password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                            <p class="text-xs text-gray-500 mt-2">
                                Leave blank if you don't want to change the password.
                            </p>

                        </div>


                        <!-- Confirm Password -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm New Password
                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                minlength="6"
                                placeholder="Repeat new password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                        </div>


                        <!-- Role -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Role
                            </label>

                            <select
                                name="role"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none"
                                <?= $member['role'] === 'admin' ? 'disabled' : '' ?>
                            >

                                <option
                                    value="team_member"
                                    <?= $member['role'] === 'team_member' ? 'selected' : '' ?>
                                >
                                    Team Member
                                </option>

                                <option
                                    value="project_manager"
                                    <?= $member['role'] === 'project_manager' ? 'selected' : '' ?>
                                >
                                    Project Manager
                                </option>

                                <?php if ($member['role'] === 'admin'): ?>

                                    <option value="admin" selected>
                                        Admin
                                    </option>

                                <?php endif; ?>

                            </select>

                            <?php if ($member['role'] === 'admin'): ?>

                                <input
                                    type="hidden"
                                    name="role"
                                    value="admin"
                                >

                            <?php endif; ?>

                        </div>


                        <!-- Status -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>

                            <?php if ($member['role'] === 'admin'): ?>

                                <input
                                    type="hidden"
                                    name="status"
                                    value="active"
                                >

                                <div class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-gray-600">
                                    Active
                                </div>

                                <p class="text-xs text-gray-500 mt-2">
                                    Admin account cannot be deactivated here.
                                </p>

                            <?php else: ?>

                                <select
                                    name="status"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none"
                                >

                                    <option
                                        value="active"
                                        <?= $member['status'] === 'active' ? 'selected' : '' ?>
                                    >
                                        Active
                                    </option>

                                    <option
                                        value="inactive"
                                        <?= $member['status'] === 'inactive' ? 'selected' : '' ?>
                                    >
                                        Inactive
                                    </option>

                                </select>

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- Buttons -->

                    <div class="flex justify-end gap-3 mt-8 pt-6 border-t">

                        <a
                            href="index.php"
                            class="px-5 py-2.5 border border-gray-300 rounded-xl hover:bg-gray-50"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700"
                        >
                            Update Member
                        </button>

                    </div>

                </form>

            </div>

        </section>

    </main>

</div>

</body>

</html>