<?php

require_once "../config/auth.php";
require_once "../config/db.php";


$id = (int)($_GET['id'] ?? 0);


if ($id <= 0) {

    header("Location: index.php");
    exit;
}


$stmt = $conn->prepare("
    SELECT *
    FROM clients
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);

$stmt->execute();

$client = $stmt->get_result()->fetch_assoc();

$stmt->close();


if (!$client) {

    header("Location: index.php");
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

    <title>Edit Client | Project Manager</title>

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


    <main class="flex-1">

        <header class="bg-white border-b">

            <div class="px-6 py-4">

                <h2 class="text-xl font-semibold">
                    Edit Client
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Update client information
                </p>

            </div>

        </header>


        <section class="p-6">

            <div class="max-w-4xl mx-auto">

                <?php if (isset($_GET['error'])): ?>

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
                        value="<?= $client['id'] ?>"
                    >


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Client Name *
                            </label>

                            <input
                                type="text"
                                name="name"
                                required
                                value="<?= htmlspecialchars($client['name']) ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                            >

                        </div>


                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Company
                            </label>

                            <input
                                type="text"
                                name="company"
                                value="<?= htmlspecialchars($client['company'] ?? '') ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                            >

                        </div>


                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                value="<?= htmlspecialchars($client['email'] ?? '') ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                            >

                        </div>


                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Phone
                            </label>

                            <input
                                type="text"
                                name="phone"
                                value="<?= htmlspecialchars($client['phone'] ?? '') ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                            >

                        </div>


                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Website
                            </label>

                            <input
                                type="url"
                                name="website"
                                value="<?= htmlspecialchars($client['website'] ?? '') ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                            >

                        </div>


                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>

                            <select
                                name="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                            >

                                <option
                                    value="active"
                                    <?= $client['status'] === 'active' ? 'selected' : '' ?>
                                >
                                    Active
                                </option>

                                <option
                                    value="inactive"
                                    <?= $client['status'] === 'inactive' ? 'selected' : '' ?>
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>


                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Address
                            </label>

                            <textarea
                                name="address"
                                rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                            ><?= htmlspecialchars($client['address'] ?? '') ?></textarea>

                        </div>


                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Notes
                            </label>

                            <textarea
                                name="notes"
                                rows="4"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                            ><?= htmlspecialchars($client['notes'] ?? '') ?></textarea>

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
                            Update Client
                        </button>

                    </div>

                </form>

            </div>

        </section>

    </main>

</div>

</body>
</html>