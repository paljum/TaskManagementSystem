<?php
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}


function verify_csrf() {
    if (
        !isset($_REQUEST['csrf_token']) ||
        $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        die("Invalid CSRF token");
    }
}


$current_user_id = $_SESSION['user_id'] ?? 0;
$is_admin = ($_SESSION['role'] ?? '') === 'admin';



if (isset($_GET['complete'])) {
    verify_csrf();

    $id = (int) $_GET['complete'];

    $stmt = mysqli_prepare($conn, "SELECT status, user_id FROM tasks WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $task = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($task && ($is_admin || $task['user_id'] == $current_user_id)) {
        $new_status = ($task['status'] === 'completed') ? 'pending' : 'completed';

        $stmt = mysqli_prepare($conn, "UPDATE tasks SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $id);
        mysqli_stmt_execute($stmt);
    }

    header("Location: index.php");
    exit();
}


if (isset($_GET['delete'])) {
    verify_csrf();

    $id = (int) $_GET['delete'];

    if ($is_admin) {
        $stmt = mysqli_prepare($conn, "DELETE FROM tasks WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM tasks WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $current_user_id);
    }

    mysqli_stmt_execute($stmt);
    header("Location: index.php");
    exit();
}


if (isset($_POST['save_task'])) {
    verify_csrf();

    $task_name = trim($_POST['task']);
    $task_id   = $_POST['task_id'] ?? '';

    if ($task_name !== '') {
        if ($task_id) {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE tasks SET task_name = ? WHERE id = ? AND user_id = ?"
            );
            mysqli_stmt_bind_param($stmt, "sii", $task_name, $task_id, $current_user_id);
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO tasks (task_name, status, user_id) VALUES (?, 'pending', ?)"
            );
            mysqli_stmt_bind_param($stmt, "si", $task_name, $current_user_id);
        }

        mysqli_stmt_execute($stmt);
        header("Location: index.php");
        exit();
    }
}


$edit_id = '';
$edit_name = '';

if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT task_name FROM tasks WHERE id = ? AND user_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $edit_id, $current_user_id);
    mysqli_stmt_execute($stmt);

    if ($row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
        $edit_name = $row['task_name'];
    }
}


$search = "%" . ($_GET['search'] ?? "") . "%";

if ($is_admin) {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT tasks.*, users.username
         FROM tasks
         JOIN users ON tasks.user_id = users.id
         WHERE tasks.task_name LIKE ? OR users.username LIKE ?
         ORDER BY tasks.id DESC"
    );
    mysqli_stmt_bind_param($stmt, "ss", $search, $search);
} else {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM tasks
         WHERE user_id = ? AND task_name LIKE ?
         ORDER BY id DESC"
    );
    mysqli_stmt_bind_param($stmt, "is", $current_user_id, $search);
}

mysqli_stmt_execute($stmt);
$tasks = mysqli_stmt_get_result($stmt);


function renderTaskCard($row, $is_admin) { ?>
    <div class="task-card <?= $row['status'] === 'completed' ? 'task-completed' : '' ?>">
        <div style="display:flex; gap:12px; align-items:center;">
            <a href="index.php?complete=<?= $row['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>"
               class="tick-box <?= $row['status'] === 'completed' ? 'checked' : '' ?>">
                <?= $row['status'] === 'completed' ? '✓' : '' ?>
            </a>

            <span>
                <strong><?= htmlspecialchars($row['task_name']) ?></strong><br>
                <small>
                    Status: <?= htmlspecialchars($row['status']) ?>
                    <?php if ($is_admin): ?>
                        | User: <?= htmlspecialchars($row['username'] ?? '') ?>
                    <?php endif; ?>
                </small>
            </span>
        </div>

        <div class="card-actions">
            <a href="index.php?edit=<?= $row['id'] ?>">Edit</a>
            <a href="index.php?delete=<?= $row['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>"
               onclick="return confirm('Delete?')">Delete</a>
        </div>
    </div>
<?php }



if (isset($_GET['ajax'])) {
    while ($row = mysqli_fetch_assoc($tasks)) {
        renderTaskCard($row, $is_admin);
    }
    exit();
}
?>
 <!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body class="auth-wrapper">
<div class="container">
    <h2>Dashboard</h2>
    <p style="text-align:center;">Welcome, <?= htmlspecialchars($_SESSION['user']) ?></p>

    <input type="text" id="liveSearch" placeholder="Search tasks..." class="inline-group">

    <form method="POST" class="inline-group">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>"> 
        <input type="hidden" name="task_id" value="<?= $edit_id ?>">
        <input type="text" name="task" placeholder="What needs to be done?" required value="<?= htmlspecialchars($edit_name) ?>">
        <button type="submit" name="save_task"><?= $edit_id ? 'Update' : 'Add' ?></button>
    </form>

    <div class="task-grid" id="taskResults">
        <?php while ($row = mysqli_fetch_assoc($tasks)) { renderTaskCard($row, $is_admin); } ?>
    </div>

    <div style="margin-top:30px; text-align:center;">
        <a href="logout.php" style="color: var(--text-silver); text-decoration: none;">Logout</a>
    </div>
</div>

<script>
document.getElementById('liveSearch').addEventListener('input', function() {
    fetch('index.php?ajax=1&search=' + encodeURIComponent(this.value))
        .then(res => res.text())
        .then(html => document.getElementById('taskResults').innerHTML = html);
});
</script>
</body>
</html>
