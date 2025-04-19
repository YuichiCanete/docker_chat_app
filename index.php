<?php
ob_start();

require_once 'db_connect.php';

$page = $_GET['page'] ?? 'login';
$messages = [];
$users = [];

if (!isset($_SESSION['userId']) && $page === 'chat') {
    header('Location: ?page=login');
    exit;
}

if ($page === 'chat') {
    updateMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? null;
    $methods = [
        'login' => 'loginUser',
        'logout' => 'logoutUser',
        'send' => 'sendMessage',
        'register' => 'registerUser',
    ];
    if (isset($methods[$action])) {
        $methods[$action]();
    }
}

function sendMessage()
{
    global $conn;
    $message = $_POST['message'] ?? null;
    $user = $_SESSION['userId'] ?? null;
    if (!isset($_SESSION['userId'])) {
        die('User not logged in. Please log in again.');
    }
    if ($message && $user) {
        $stmt = $conn->prepare("INSERT INTO messages (user_id, message, timestamp) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $user, $message);
        $stmt->execute();
        $stmt->close();
    } else {
        die("Message $message, User ID $user");
    }
    header('Location: ?page=chat');
    exit;
}

function updateMessage()
{
    global $conn, $messages;
    $query = "SELECT user.username, messages.message 
              FROM messages 
              JOIN user ON messages.user_id = user.id 
            ORDER BY messages.timestamp DESC
              LIMIT 15;";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = $row;
        }
    }
}

function loginUser()
{
    global $conn;

    $user_input = $_POST['user_input'] ?? null;
    $password_input = $_POST['password_input'] ?? null;

    if (!$user_input || !$password_input) {
        header('Location: ?page=login&error=Missing username or password');
        exit;
    }

    $query = "SELECT * FROM user WHERE username = '$user_input' AND password = '$password_input'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        session_start();
        $_SESSION['userId'] = $user['id'];
        header('Location: ?page=chat');
        exit;
    } else {
        header('Location: ?page=login&error=Invalid username or password');
        exit;
    }
}

function registerUser(){
    global $conn;
    $user_input = $_POST['user_input'] ?? null;
    $password_input = $_POST['password_input'] ?? null;
    if (!$user_input || !$password_input) {
        header('Location: ?page=register&error=Missing username or password');
        exit;
    }
    $query = "INSERT INTO user (username, password) VALUES ('$user_input', '$password_input')";
    if (mysqli_query($conn, $query)) {
        header('Location: ?page=login');
        exit;
    } else {
        header('Location: ?page=register&error=Registration failed');
        exit;
    }
}

function logoutUser()
{
    session_start();
    session_destroy();
    header('Location: ?page=login');
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Chat Application</title>
    <style>
        * {
            font-family: Arial, Helvetica, sans-serif;
            color: #151515;
            margin: 0;
            padding: 0;
        }

        button>a {
            text-decoration: none;
        }

        button {
            background-color: white;
            border-radius: 2.5px;
            border: 1px solid #151515;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        input {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #151515;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            width: 300px;
            margin: 0 auto;
            gap: 10px;
            border-radius: 5px;
            border: 1px solid #151515;
            padding: 20px;
            text-align: center;
        }

        .title {
            text-align: center;
            margin: 15px;
        }

        .center {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .page-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100dvh;
        }

        .flex-row {
            display: flex;
            flex-direction: row;
        }

        .flex-col {
            display: flex;
            flex-direction: column;
        }

        .chat-container {
            width: 600px;
            height: 300px;
            overflow-y: auto;
            border: 1px solid #151515;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            display: flex;
            flex-direction: column-reverse;
        }
    </style>
</head>

<body>
    <div class="page-container">
        <div class='center flex-col'>
            <?php if ($page === 'login'): ?>

                <h1 class="title">Chat Application</h1>
                <form class="login-form" method="POST">
                    <h1>Login </h1>
                    <input type="hidden" name="form_action" value="login">
                    <input type="text" placeholder="Username" required name="user_input">
                    <input type="password" placeholder="Password" required name="password_input">
                    <button type="submit">Login</button>
                    <?php if (isset($_GET['error'])): ?>
                        <p style="color: red;"><?php echo $_GET['error'] ?></p>
                    <?php endif; ?>
                    <p>Dont have an account? <a href="?page=register">register</a></p>
                </form>
            <?php elseif ($page === 'register'): ?>
                <form class="login-form" method="POST">
                    <h1>Register </h1>
                    <input type="hidden" name="form_action" value="register">
                    <input type="text" placeholder="Username" required name="user_input">
                    <input type="password" placeholder="Password" required name="password_input">
                    <button type="submit">Create Account</button>
                </form>
            <?php elseif ($page === 'chat'): ?>
                <h1 class="title">Messages</h1>
                <div style="width: 600px; height: 300px;">
                    <div class="chat-container">
                        <?php foreach ($messages as $message): ?>
                            <div class="message">
                                <strong><?php echo ($message['username']); ?>:</strong>
                                <span><?php echo ($message['message']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="form_action" value="send">
                        <input type="text" placeholder="Type a message..." required name="message">
                        <button type="submit">Send</button>
                    </form>
                    <form method="POST">
                        <input type="hidden" name="form_action" value="logout">
                        <button type="submit" name="logout">Logout</button>
                    </form>
                </div>
            <?php else: ?>
                <h1>404 Not Found</h1>
                <p>The page you are looking for does not exist.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>