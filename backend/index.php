<?php
// エラーをJSONレスポンスとして適切に処理するため、HTML出力を無効化
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// 全ての予期しないエラーをキャッチして適切なJSONレスポンスを返す
set_error_handler(function($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

set_exception_handler(function($exception) {
    error_log("Unhandled exception: " . $exception->getMessage());
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Internal server error']);
    exit();
});

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();

// Database configuration
$database_url = getenv('DATABASE_URL');
if ($database_url) {
    // Parse DATABASE_URL for production (Supabase format)
    $db_parts = parse_url($database_url);
    $host = $db_parts['host'];
    $port = $db_parts['port'] ?? 5432;
    $dbname = ltrim($db_parts['path'], '/');
    $user = $db_parts['user'];
    $password = $db_parts['pass'];
} else {
    // Local development configuration
    $host = 'db';
    $port = 5432;
    $dbname = 'bulletin_board';
    $user = 'user';
    $password = 'password';
}

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];

if ($method === 'POST' && $path === '/register') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['username']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password are required']);
        exit();
    }

    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $stmt->execute([$data['username'], $hashed_password]);
        http_response_code(201);
        echo json_encode(['message' => 'User created successfully']);
    } catch (PDOException $e) {
        http_response_code(409);
        echo json_encode(['error' => 'Username already exists']);
    }
} elseif ($method === 'POST' && $path === '/login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$data['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['message' => 'Login successful', 'user' => ['id' => $user['id'], 'username' => $user['username']]]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
} elseif ($method === 'GET' && $path === '/messages') {
    $stmt = $pdo->query('SELECT m.*, u.username FROM messages m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC');
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($messages);
} elseif ($method === 'POST' && $path === '/messages') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }

    try {
        // コンテンツの検証
        if (!isset($_POST['content']) || empty(trim($_POST['content']))) {
            http_response_code(400);
            echo json_encode(['error' => 'Content is required']);
            exit();
        }

        $content = trim($_POST['content']);
        $user_id = $_SESSION['user_id'];
        $image_path = null;

        // ファイルアップロード処理
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.']);
                exit();
            }

            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to upload image']);
                exit();
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            http_response_code(400);
            echo json_encode(['error' => 'File upload error: ' . $_FILES['image']['error']]);
            exit();
        }

        // メッセージをデータベースに挿入
        $stmt = $pdo->prepare('INSERT INTO messages (user_id, content, image_path) VALUES (?, ?, ?)');
        $stmt->execute([$user_id, $content, $image_path]);
        
        $message_id = $pdo->lastInsertId();
        
        // 新しく作成されたメッセージを取得
        $stmt = $pdo->prepare('SELECT m.*, u.username FROM messages m JOIN users u ON m.user_id = u.id WHERE m.id = ?');
        $stmt->execute([$message_id]);
        $newMessage = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$newMessage) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to retrieve created message']);
            exit();
        }

        http_response_code(201);
        echo json_encode($newMessage);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Database error occurred']);
    } catch (Exception $e) {
        error_log("General error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'An error occurred while processing the request']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}