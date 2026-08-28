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
    SELECT *
    FROM projects
    WHERE id = ?
    LIMIT 1
");

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
| Clients
|--------------------------------------------------------------------------
*/

$clients = $conn->query("
    SELECT id, name, company
    FROM clients
    WHERE status = 'active'
    ORDER BY name ASC
");


/*
|--------------------------------------------------------------------------
| Project Managers
|--------------------------------------------------------------------------
*/

$managers = $conn->query("
    SELECT id, name
    FROM users
    WHERE role = 'project_manager'
    AND status = 'active'
    ORDER BY name ASC
");


/*
|--------------------------------------------------------------------------
| Team Members
|--------------------------------------------------------------------------
*/

$members = $conn->query("
    SELECT id, name, email, role
    FROM users
    WHERE status = 'active'
    AND role IN ('team_member', 'project_manager')
    ORDER BY name ASC
");


/*
|--------------------------------------------------------------------------
| Already Assigned Members
|--------------------------------------------------------------------------
*/

$assigned_ids = [];

$stmt = $conn->prepare("
    SELECT user_id
    FROM project_members
    WHERE project_id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $assigned_ids[] = (int)$row['user_id'];
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Project | Project Manager</title>

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


        <!-- Header -->

        <header class="bg-white border-b">

            <div class="px-6 py-4">

                <h2 class="text-xl font-semibold text-gray-900">
                    Edit Project
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    <?= htmlspecialchars($project['project_code']) ?>
                </p>

            </div>

        </header>


        <section class="p-6">

            <div class="max-w-5xl mx-auto">


                <?php if (!empty($_GET['error'])): ?>

                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">

                        <?= htmlspecialchars($_GET['error']) ?>

                    </div>

                <?php endif; ?>


                <form
                    action="update.php"
                    method="POST"
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
                >

                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int)$project['id'] ?>"
                    >


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                        <!-- Project Name -->

                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Project Name *
                            </label>

                            <input
                                type="text"
                                name="name"
                                required
                                value="<?= htmlspecialchars($project['name']) ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                        </div>


                        <!-- Client -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Client
                            </label>

                            <select
                                name="client_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                            >

                                <option value="">
                                    Select Client
                                </option>

                                <?php while ($client = $clients->fetch_assoc()): ?>

                                    <option
                                        value="<?= $client['id'] ?>"
                                        <?= (int)$project['client_id'] === (int)$client['id'] ? 'selected' : '' ?>
                                    >

                                        <?= htmlspecialchars($client['name']) ?>

                                        <?php if (!empty($client['company'])): ?>

                                            — <?= htmlspecialchars($client['company']) ?>

                                        <?php endif; ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>


                        <!-- Manager -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Project Manager
                            </label>

                            <select
                                name="manager_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                            >

                                <option value="">
                                    Select Manager
                                </option>

                                <?php while ($manager = $managers->fetch_assoc()): ?>

                                    <option
                                        value="<?= $manager['id'] ?>"
                                        <?= (int)$project['manager_id'] === (int)$manager['id'] ? 'selected' : '' ?>
                                    >

                                        <?= htmlspecialchars($manager['name']) ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>


                        <!-- Start Date -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Start Date
                            </label>

                            <input
                                type="date"
                                name="start_date"
                                value="<?= htmlspecialchars($project['start_date'] ?? '') ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                            >

                        </div>


                        <!-- Deadline -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Deadline
                            </label>

                            <input
                                type="date"
                                name="deadline"
                                value="<?= htmlspecialchars($project['deadline'] ?? '') ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                            >

                        </div>


                        <!-- Budget -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Budget
                            </label>

                            <input
                                type="number"
                                name="budget"
                                min="0"
                                step="0.01"
                                value="<?= htmlspecialchars($project['budget'] ?? 0) ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                            >

                        </div>


                        <!-- Priority -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Priority
                            </label>

                            <select
                                name="priority"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                            >

                                <?php

                                $priorities = [
                                    'low' => 'Low',
                                    'medium' => 'Medium',
                                    'high' => 'High',
                                    'urgent' => 'Urgent'
                                ];

                                ?>

                                <?php foreach ($priorities as $value => $label): ?>

                                    <option
                                        value="<?= $value ?>"
                                        <?= $project['priority'] === $value ? 'selected' : '' ?>
                                    >
                                        <?= $label ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- Status -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>

                            <select
                                name="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                            >

                                <?php

                                $statuses = [
                                    'planning' => 'Planning',
                                    'in_progress' => 'In Progress',
                                    'on_hold' => 'On Hold',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled'
                                ];

                                ?>

                                <?php foreach ($statuses as $value => $label): ?>

                                    <option
                                        value="<?= $value ?>"
                                        <?= $project['status'] === $value ? 'selected' : '' ?>
                                    >
                                        <?= $label ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- Progress -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Progress (%)
                            </label>

                            <input
                                type="number"
                                name="progress"
                                min="0"
                                max="100"
                                value="<?= (int)$project['progress'] ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                            >

                        </div>


                        <!-- Description -->

                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>

                            <textarea
                                name="description"
                                rows="5"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                            ><?= htmlspecialchars($project['description'] ?? '') ?></textarea>

                        </div>


                        <!-- Team Members -->

                        <div class="md:col-span-2">

                            <div class="border border-gray-200 rounded-2xl overflow-hidden">

                                <div class="px-5 py-4 bg-gray-50 border-b">

                                    <h3 class="font-semibold text-gray-900">
                                        Assign Team Members
                                    </h3>

                                    <p class="text-sm text-gray-500 mt-1">
                                        Select the members who will work on this project.
                                    </p>

                                </div>


                                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-3">

                                    <?php if ($members->num_rows > 0): ?>

                                        <?php while ($member = $members->fetch_assoc()): ?>

                                            <label
                                                class="flex items-center gap-3 p-4 border rounded-xl hover:bg-gray-50 cursor-pointer"
                                            >

                                                <input
                                                    type="checkbox"
                                                    name="members[]"
                                                    value="<?= $member['id'] ?>"
                                                    <?= in_array(
                                                        (int)$member['id'],
                                                        $assigned_ids,
                                                        true
                                                    ) ? 'checked' : '' ?>
                                                    class="w-4 h-4 text-indigo-600"
                                                >

                                                <div>

                                                    <p class="font-medium text-gray-900">

                                                        <?= htmlspecialchars(
                                                            $member['name']
                                                        ) ?>

                                                    </p>

                                                    <p class="text-xs text-gray-500">

                                                        <?= htmlspecialchars(
                                                            $member['email']
                                                        ) ?>

                                                    </p>

                                                </div>

                                            </label>

                                        <?php endwhile; ?>

                                    <?php else: ?>

                                        <p class="text-gray-500 md:col-span-2">
                                            No active team members available.
                                        </p>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Buttons -->

                    <div class="flex justify-end gap-3 mt-8 pt-6 border-t">

                        <a
                            href="view.php?id=<?= $id ?>"
                            class="px-5 py-2.5 border border-gray-300 rounded-xl"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700"
                        >
                            Update Project
                        </button>

                    </div>

                </form>

            </div>

        </section>

    </main>

</div>

</body>
</html>