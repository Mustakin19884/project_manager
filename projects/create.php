<?php

require_once "../config/auth.php";
require_once "../config/db.php";


$clients = $conn->query("
    SELECT id, name, company
    FROM clients
    WHERE status = 'active'
    ORDER BY name ASC
");


$managers = $conn->query("
    SELECT id, name
    FROM users
    WHERE status = 'active'
    AND role = 'project_manager'
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

    <title>New Project | Project Manager</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>


<body class="bg-gray-100">

<div class="min-h-screen flex">


    <aside class="w-64 bg-gray-900 text-white hidden md:block">

        <div class="p-6">

            <h1 class="text-xl font-bold">
                Project Manager
            </h1>

        </div>

        <nav class="px-4 space-y-2">

            <a href="../dashboard/index.php"
               class="block px-4 py-3 rounded-lg hover:bg-gray-800">
                Dashboard
            </a>

            <a href="index.php"
               class="block px-4 py-3 rounded-lg bg-indigo-600">
                Projects
            </a>

            <a href="../tasks/index.php"
               class="block px-4 py-3 rounded-lg hover:bg-gray-800">
                Tasks
            </a>

            <a href="../clients/index.php"
               class="block px-4 py-3 rounded-lg hover:bg-gray-800">
                Clients
            </a>

            <a href="../team/index.php"
               class="block px-4 py-3 rounded-lg hover:bg-gray-800">
                Team Members
            </a>

            <a href="../reports/index.php"
               class="block px-4 py-3 rounded-lg hover:bg-gray-800">
                Reports
            </a>

            <a href="../auth/logout.php"
               class="block px-4 py-3 rounded-lg hover:bg-red-600 mt-8">
                Logout
            </a>

        </nav>

    </aside>


    <main class="flex-1">

        <header class="bg-white border-b">

            <div class="px-6 py-4">

                <h2 class="text-xl font-semibold">
                    Create New Project
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Add a new project to your workspace
                </p>

            </div>

        </header>


        <section class="p-6">

            <div class="max-w-5xl mx-auto">

                <?php if (isset($_GET['error'])): ?>

                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">

                        <?= htmlspecialchars($_GET['error']) ?>

                    </div>

                <?php endif; ?>


                <form
                    action="store.php"
                    method="POST"
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
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
                                placeholder="Website Redesign"
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
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                                <option value="">
                                    Select Client
                                </option>

                                <?php while ($client = $clients->fetch_assoc()): ?>

                                    <option value="<?= $client['id'] ?>">

                                        <?= htmlspecialchars($client['name']) ?>

                                        <?php if (!empty($client['company'])): ?>

                                            —
                                            <?= htmlspecialchars($client['company']) ?>

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
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                                <option value="">
                                    Select Manager
                                </option>

                                <?php while ($manager = $managers->fetch_assoc()): ?>

                                    <option value="<?= $manager['id'] ?>">
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
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
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
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
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
                                value="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                        </div>


                        <!-- Priority -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Priority
                            </label>

                            <select
                                name="priority"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                                <option value="low">
                                    Low
                                </option>

                                <option value="medium" selected>
                                    Medium
                                </option>

                                <option value="high">
                                    High
                                </option>

                                <option value="urgent">
                                    Urgent
                                </option>

                            </select>

                        </div>


                        <!-- Status -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>

                            <select
                                name="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                                <option value="planning" selected>
                                    Planning
                                </option>

                                <option value="in_progress">
                                    In Progress
                                </option>

                                <option value="on_hold">
                                    On Hold
                                </option>

                                <option value="completed">
                                    Completed
                                </option>

                                <option value="cancelled">
                                    Cancelled
                                </option>

                            </select>

                        </div>


                        <!-- Description -->

                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>

                            <textarea
                                name="description"
                                rows="6"
                                placeholder="Describe the project..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            ></textarea>

                        </div>

                    </div>


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
                            Create Project
                        </button>

                    </div>

                </form>

            </div>

        </section>

    </main>

</div>

</body>
</html>