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

    <title>Add Client | Project Manager</title>

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

            <a href="index.php"
               class="block px-4 py-3 rounded-lg bg-indigo-600">
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


    <!-- Main -->

    <main class="flex-1">

        <header class="bg-white border-b">

            <div class="px-6 py-4">

                <h2 class="text-xl font-semibold">
                    Add Client
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Create a new client
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                        <!-- Name -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Client Name *
                            </label>

                            <input
                                type="text"
                                name="name"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                                placeholder="John Doe"
                            >

                        </div>


                        <!-- Company -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Company
                            </label>

                            <input
                                type="text"
                                name="company"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                                placeholder="ABC Company"
                            >

                        </div>


                        <!-- Email -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                                placeholder="john@example.com"
                            >

                        </div>


                        <!-- Phone -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Phone
                            </label>

                            <input
                                type="text"
                                name="phone"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                                placeholder="+880 1XXXXXXXXX"
                            >

                        </div>


                        <!-- Website -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Website
                            </label>

                            <input
                                type="url"
                                name="website"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                                placeholder="https://example.com"
                            >

                        </div>


                        <!-- Status -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>

                            <select
                                name="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                            >

                                <option value="active">
                                    Active
                                </option>

                                <option value="inactive">
                                    Inactive
                                </option>

                            </select>

                        </div>


                        <!-- Address -->

                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Address
                            </label>

                            <textarea
                                name="address"
                                rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                                placeholder="Client address..."
                            ></textarea>

                        </div>


                        <!-- Notes -->

                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Notes
                            </label>

                            <textarea
                                name="notes"
                                rows="4"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                                placeholder="Additional information..."
                            ></textarea>

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
                            Create Client
                        </button>

                    </div>

                </form>

            </div>

        </section>

    </main>

</div>

</body>
</html>