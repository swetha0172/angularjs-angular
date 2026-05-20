<?php
// Allow CORS and respond to preflight OPTIONS requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
$dbFile = __DIR__ . '/products.db';

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    initDatabase($pdo);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

function initDatabase($pdo)
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            p_name TEXT NOT NULL,
            code TEXT,
            description TEXT,
            image TEXT,
            price TEXT
        )"
    );

    $count = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    if ($count == 0) {
        $seedData = [
            ['T-Shirt', '1010', 'v shirt', '', '200'],
            ['My T-Shirt', '2020', 'Red T-Shirt', '', '400'],
            ['Home product', '3243', 'desfdgd', '', '345'],
            ['Mug', '123456', 'asdads', '', '12'],
            ['Mysql Id', '324234', '234234', '', '324324']
        ];

        $stmt = $pdo->prepare('INSERT INTO products (p_name, code, description, image, price) VALUES (?, ?, ?, ?, ?)');
        foreach ($seedData as $row) {
            $stmt->execute($row);
        }
    }
}

function read()
{
    global $pdo;
    $stmt = $pdo->query('SELECT id, p_name, code, description, image, price FROM products');
    $return_arr = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $return_arr[] = [
            'id' => $row['id'],
            'name' => $row['p_name'],
            'code' => $row['code'],
            'description' => $row['description'],
            'image' => $row['image'],
            'price' => $row['price']
        ];
    }

    echo json_encode($return_arr);
}

function insert()
{
    global $pdo;
    $data = json_decode(file_get_contents('php://input'));
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request data']);
        return;
    }

    $stmt = $pdo->prepare('INSERT INTO products (p_name, code, description, image, price) VALUES (:name, :code, :description, :image, :price)');
    $stmt->execute([
        ':name' => $data->name,
        ':code' => $data->code,
        ':description' => $data->description,
        ':image' => '',
        ':price' => $data->price
    ]);

    echo $pdo->lastInsertId();
}
function update()
{
    global $pdo;

    $data = json_decode(file_get_contents('php://input'));

    $stmt = $pdo->prepare(
        'UPDATE products 
         SET p_name = :name, code = :code, description = :description, price = :price 
         WHERE id = :id'
    );

    $stmt->execute([
        ':id' => $data->id,
        ':name' => $data->name,
        ':code' => $data->code,
        ':description' => $data->description,
        ':price' => $data->price
    ]);

    echo json_encode(['success' => true]);
}
function delete()
{
    global $pdo;
    $data = json_decode(file_get_contents('php://input'));
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request data']);
        return;
    }

    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute([':id' => $data->id]);
    echo json_encode(['success' => true]);
}

switch ($_GET['action'] ?? '') {
    case 'read':
        read();
        break;
    case 'insert':
        insert();
        break;
    case 'update':
        update();
        break;
    case 'delete':
        delete();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
