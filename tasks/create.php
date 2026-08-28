<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Projects
|--------------------------------------------------------------------------
*/

$projects = $conn->query("
    SELECT
        id,
        name,
        project_code
    FROM projects
    ORDER BY name ASC
");


/*
|--------------------------------------------------------------------------
| Team Members
|--------------------------------------------------------------------------
*/

$team_members = $conn->query("
    SELECT
        id,
        name
    FROM team_members
    WHERE status = 'active'
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

    <title>Create Task | Project Manager</title>

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


    <!-- Main -->

    <main class="flex-1">


        <!-- Header -->

        <header class="bg-white border-b">

            <div class="px-6 py-4">

                <h2 class="text-xl font-semibold text-gray-900">
                    Create Task
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Create and assign a new task
                </p>

            </div>

        </header>


        <section class="p-6">


            <div class="max-w-4xl mx-auto">


                <form
                    action="store.php"
                    method="POST"
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
                >


                    <!-- Task Title -->

                    <div class="mb-6">

                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Task Title
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            name="title"
                            required
                            maxlength="255"
                            placeholder="Enter task title"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                        >

                    </div>


                    <!-- Project + Team Member -->

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">


                        <!-- Project -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Project
                                <span class="text-red-500">*</span>
                            </label>


                            <select
                                name="project_id"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                                <option value="">
                                    Select Project
                                </option>


                                <?php while ($project = $projects->fetch_assoc()): ?>

                                    <option
                                        value="<?= (int)$project['id'] ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $project['name']
                                        ) ?>

                                        <?php if (!empty($project['project_code'])): ?>

                                            —
                                            <?= htmlspecialchars(
                                                $project['project_code']
                                            ) ?>

                                        <?php endif; ?>

                                    </option>

                                <?php endwhile; ?>


                            </select>

                        </div>


                        <!-- Team Member -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Assign To
                            </label>


                            <select
                                name="team_member_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                                <option value="">
                                    Unassigned
                                </option>


                                <?php while ($member = $team_members->fetch_assoc()): ?>

                                    <option
                                        value="<?= (int)$member['id'] ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $member['name']
                                        ) ?>

                                    </option>

                                <?php endwhile; ?>


                            </select>

                        </div>


                    </div>


                    <!-- Description -->

                    <div class="mb-6">

                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Description
                        </label>

                        <textarea
                            name="description"
                            rows="5"
                            placeholder="Describe the task..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                        ></textarea>

                    </div>


                    <!-- Status / Priority / Deadline -->

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">


                        <!-- Status -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Status
                            </label>


                            <select
                                name="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                                <option value="pending">
                                    Pending
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


                        <!-- Priority -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
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


                        <!-- Deadline -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Deadline
                            </label>


                            <input
                                type="date"
                                name="deadline"
                                min="<?= date('Y-m-d') ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                        </div>


                    </div>


                    <!-- Progress -->

                    <div class="mb-8">

                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Progress
                        </label>


                        <div class="flex items-center gap-4">


                            <input
                                type="range"
                                name="progress"
                                id="progress"
                                min="0"
                                max="100"
                                value="0"
                                class="flex-1"
                                oninput="document.getElementById('progressValue').innerText = this.value + '%'"
                            >


                            <span
                                id="progressValue"
                                class="w-14 text-center font-medium text-gray-700"
                            >
                                0%
                            </span>


                        </div>

                    </div>


                    <!-- Buttons -->

                    <div class="flex items-center justify-end gap-3 border-t pt-6">


                        <a
                            href="index.php"
                            class="px-5 py-2.5 border border-gray-300 rounded-xl hover:bg-gray-50"
                        >
                            Cancel
                        </a>


                        <button
                            type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-medium"
                        >
                            Create Task
                        </button>


                    </div>


                </form>


            </div>


        </section>


    </main>


</div>


</body>

</html>