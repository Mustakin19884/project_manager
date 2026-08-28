<?php

require_once "../config/auth.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Team Member | Project Manager</title>

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

            <a href="../projects/index.php"
               class="block px-4 py-3 rounded-lg hover:bg-gray-800">
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

            <a href="index.php"
               class="block px-4 py-3 rounded-lg bg-indigo-600">
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
                    Add Team Member
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Create a new user account
                </p>

            </div>

        </header>


        <section class="p-6">

            <div class="max-w-3xl mx-auto">


                <?php if (isset($_GET['error'])): ?>

                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">

                        <?= htmlspecialchars($_GET['error']) ?>

                    </div>

                <?php endif; ?>


                <form
                    action="store.php"
                    method="POST"
                    class="bg-white rounded-2xl shadow-sm border p-6"
                >

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name *
                            </label>

                            <input
                                type="text"
                                name="name"
                                required
                                placeholder="John Doe"
                                class="w-full px-4 py-3 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                        </div>


                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email *
                            </label>

                            <input
                                type="email"
                                name="email"
                                required
                                placeholder="john@example.com"
                                class="w-full px-4 py-3 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                        </div>


                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Password *
                            </label>

                            <input
                                type="password"
                                name="password"
                                required
                                minlength="6"
                                placeholder="Minimum 6 characters"
                                class="w-full px-4 py-3 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                        </div>


                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm Password *
                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                required
                                minlength="6"
                                placeholder="Repeat password"
                                class="w-full px-4 py-3 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500"
                            >

                        </div>


                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Role
                            </label>

                            <select
                                name="role"
                                class="w-full px-4 py-3 border rounded-xl outline-none"
                            >

                                <option value="team_member">
                                    Team Member
                                </option>

                                <option value="project_manager">
                                    Project Manager
                                </option>

                            </select>

                        </div>


                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>

                            <select
                                name="status"
                                class="w-full px-4 py-3 border rounded-xl outline-none"
                            >

                                <option value="active">
                                    Active
                                </option>

                                <option value="inactive">
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="flex justify-end gap-3 mt-8 pt-6 border-t">

                        <a
                            href="index.php"
                            class="px-5 py-2.5 border rounded-xl"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700"
                        >
                            Create Member
                        </button>

                    </div>

                </form>

            </div>

        </section>

    </main>

</div>

</body>
</html>