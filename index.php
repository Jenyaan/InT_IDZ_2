<?php
session_start();

const USERS_FILE = 'users.txt';
const POSTS_DIR = 'posts/';

if (!is_dir(POSTS_DIR)) 
    mkdir(POSTS_DIR, 0777, true);

function getUsers() {
    if (file_exists(USERS_FILE)) {
        $data = json_decode(file_get_contents(USERS_FILE), true);
        return $data !== null ? $data : [];
    } else {
        return [];
    }
}

function saveUsers($users) {
    file_put_contents(USERS_FILE, json_encode($users));
}

function handleRegister($username, $password) {
    $users = getUsers();
    if (isset($users[$username])) return "Користувач вже існує.";
    $users[$username] = password_hash($password, PASSWORD_DEFAULT);
    saveUsers($users);
    return "Реєстрація успішна!";
}

function handleLogin($username, $password) {
    $users = getUsers();
    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        $_SESSION['username'] = $username;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    return "Невірний логін або пароль.";
}

function savePost($username, $content) {
    $file = POSTS_DIR . $username . '.txt';
    file_put_contents($file, date('Y-m-d H:i:s') . "\n$content\n\n", FILE_APPEND);
}

function getPosts($username) {
    $file = POSTS_DIR . $username . '.txt';
    if (file_exists($file)) {
        return file_get_contents($file);
    } else {
        return "Поки що немає записів.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $content = $_POST['content'] ?? '';

    if ($action === 'register') $message = handleRegister($username, $password);
    if ($action === 'login') $message = handleLogin($username, $password);
    if ($action === 'post' && isset($_SESSION['username'])) savePost($_SESSION['username'], $content);
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title>Блог</title>
</head>
<body>
<div class="container">
<?php if (isset($_SESSION['username'])): ?>
    <h1>Вітаємо, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
    <a href="?logout">Вийти</a>

    <h2>Ваші записи:</h2>
    <pre><?= htmlspecialchars(getPosts($_SESSION['username'])) ?></pre>

    <h2>Додати новий запис:</h2>
    <form method="post" style="margin: auto;">
        <textarea name="content" required></textarea>
        <input type="hidden" name="action" value="post">
        <button type="submit">Зберегти</button>
    </form>
<?php else: ?>
    <h1>Реєстрація</h1>
    <form method="post">
        <input type="text" name="username" placeholder="Ім'я користувача" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <input type="hidden" name="action" value="register">
        <button type="submit">Зареєструватися</button>
    </form>

    <h1>Вхід</h1>
    <form method="post">
        <input type="text" name="username" placeholder="Ім'я користувача" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <input type="hidden" name="action" value="login">
        <button type="submit">Увійти</button>
    </form>
<?php endif; ?>

<?php if (isset($message)): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>
</div>
</body>
</html>
