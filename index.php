<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List JWP Dani</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        .selesai {
            text-decoration: line-through;
            color: gray;
        }
    </style>


    <?php
        session_start();

        function initTasks() {;
            $tasks = [
                ["id" => 1, "title" => "Belajar PHP", "status" => "belum"],
                ["id" => 2, "title" => "Kerjakan tugas UX", "status" => "selesai"],
            ];
            if (!isset($_SESSION['tasks'])) {
                $_SESSION['tasks'] = $tasks;
            }
        }

        if (!isset($_SESSION['tasks'])) {
            initTasks();
        }

        if (isset($_POST['add'])) {
            $task = $_POST['task'];
            if (!empty($task)) {
                $_SESSION['tasks'][] = [
                    "id" => getNewId(),
                    "title" => $task,
                    "status" => "belum"
                ];
            }
        }

        function getNewId(): int {
            $tasks = $_SESSION['tasks'] ?? [];
            if (empty($tasks)) return 1;
            usort($tasks, fn($a, $b) => $b['id'] <=> $a['id']);
            return $tasks[0]['id'] + 1;
        }

        // Simpan perubahan status dari checkbox toggle
        if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
            $id = (int)$_POST['id'];
            $status = $_POST['status'] === "true" ? "selesai" : "belum";

            foreach ($_SESSION['tasks'] ?? [] as &$task) {
                if ($task['id'] === $id) {
                    $task['status'] = $status;
                    break;
                }
            }
            unset($task);
            session_write_close();
            exit;
        }

        // Proses hapus
        if (isset($_POST['delete'])) {
            $id = (int)$_POST['id'];
            $_SESSION['tasks'] = array_filter($_SESSION['tasks'], fn($task) => $task['id'] !== $id);
        }

        // Proses edit
        if (isset($_POST['edit'])) {
            $id = (int)$_POST['id'];
            $title = trim($_POST['title']);
            foreach ($_SESSION['tasks'] as &$task) {
                if ($task['id'] === $id) {
                    $task['title'] = $title;
                    break;
                }
            }
            unset($task);
        }

        // Reset data untuk debugging
        // if (isset($_POST['clear'])) {
        //     unset($_SESSION['tasks']);
        //     initTasks();
        // }
    ?>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-2xl p-6 w-full max-w-md">
        <!-- Judul -->
        <h2 class="text-2xl font-bold text-center mb-4 text-indigo-600">To Do List JWP Dani</h2>

        <!-- Form tambah tugas -->
        <form method="POST" class="flex mb-4">
            <input type="text" name="task" placeholder="Tambah tugas baru..." required
                class="flex-grow border border-gray-300 rounded-l-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit" name="add"
                class="bg-indigo-600 text-white px-4 py-2 rounded-r-lg hover:bg-indigo-700 transition">Tambah</button>
        </form>

        <!-- Reset daftar tugas untuk debugging -->
        <!-- <form method="post" class="mb-4 text-center">
            <button type="submit" name="clear"
                class="bg-red-500 text-white ml-2 px-4 py-2 rounded-lg hover:bg-red-600 transition">Reset</button>
        </form> -->

        <!-- Daftar tugas -->
        <ul class="space-y-2">
            <?php foreach ($_SESSION['tasks'] ?? [] as $task): ?>
                <li class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg border">
                    <!-- Checkbox -->
                    <input type="checkbox" name="status[<?= $task['id'] ?>]"
                        class="toggle h-5 w-5 text-indigo-500 rounded border-gray-300"
                        data-id="<?= $task['id'] ?>"
                        <?= $task['status'] === "selesai" ? "checked" : "" ?>>

                    <!-- Kolom title -->
                    <form method="post" class="flex items-center w-full ml-2">
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">

                        <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>"
                            class="border-0 bg-transparent flex-grow focus:outline-none
                            <?= $task['status'] === 'selesai' ? 'line-through text-gray-500' : '' ?>">

                        <button type="submit" name="edit"
                            class="bg-blue-500 text-white px-2 py-1 rounded mr-2">Edit</button>
                        <button type="submit" name="delete"
                            class="bg-red-500 text-white px-2 py-1 rounded"
                            onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>

<script>
    // Detect perubahan dari input dengan class "toggle", dalam hal ini checkbox tiap task
    document.querySelectorAll(".toggle").forEach(checkbox => {
        checkbox.addEventListener("change", function() {
            let id = this.dataset.id;
            let checked = this.checked;
            
            // Update class "selesai" untuk menambah strikethrough
            let form = this.nextElementSibling;  
            let titleInput = form.querySelector("input[name='title']");

            if (titleInput) {
                if (checked) {
                    titleInput.classList.add("selesai");
                } else {
                    titleInput.classList.remove("selesai");
                }
            }

            fetch("", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "action=update_status&id=" + id + "&status=" + checked,
                credentials: "same-origin"
            });
        });
    });
</script>

</html>
