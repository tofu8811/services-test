<?php

$instance = getenv('INSTANCE_NAME') ?: 'order-mock-3002';
$secret = getenv('GATEWAY_SECRET') ?: 'super-secret-gateway-key';

$orders = [
    [
        'id' => 1,
        'user_id' => 1,
        'status' => 'pending',
        'total' => 15650000,
        'items' => [
            ['product_id' => 1, 'name' => 'Laptop Dell Inspiron', 'quantity' => 1, 'price' => 15000000],
            ['product_id' => 2, 'name' => 'Keyboard Logitech K380', 'quantity' => 1, 'price' => 650000],
        ],
    ],
    [
        'id' => 2,
        'user_id' => 2,
        'status' => 'paid',
        'total' => 3150000,
        'items' => [
            ['product_id' => 4, 'name' => 'Monitor LG 24 inch', 'quantity' => 1, 'price' => 2800000],
            ['product_id' => 3, 'name' => 'Mouse Logitech M331', 'quantity' => 1, 'price' => 350000],
        ],
    ],
    [
        'id' => 3,
        'user_id' => 1,
        'status' => 'shipping',
        'total' => 1900000,
        'items' => [
            ['product_id' => 5, 'name' => 'SSD Samsung 1TB', 'quantity' => 1, 'price' => 1900000],
        ],
    ],
];

function jsonResponse(int $status, array $payload): void
{
    global $instance;

    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Service-Secret, X-Gateway-Secret, X-User-ID, X-User-Email');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('X-Mock-Instance: ' . $instance);

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requestJson(): array
{
    $body = file_get_contents('php://input');
    if ($body === false || trim($body) === '') {
        return [];
    }

    $decoded = json_decode($body, true);
    return is_array($decoded) ? $decoded : [];
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($method === 'OPTIONS') {
    jsonResponse(204, []);
}

if ($path === '/' || $path === '/health' || $path === '/api/health') {
    jsonResponse(200, [
        'status' => 'ok',
        'service' => 'order-mock',
        'instance' => $instance,
    ]);
}

if ($method === 'POST' && $path === '/api/service-accounts/token') {
    jsonResponse(200, [
        'access_token' => base64_encode($instance . ':' . $secret),
        'token_type' => 'Bearer',
        'expires_in' => 3600,
        'instance' => $instance,
    ]);
}

if ($method === 'GET' && $path === '/api/orders') {
    jsonResponse(200, [
        'success' => true,
        'message' => 'Mock orders fetched successfully',
        'data' => $orders,
        'instance' => $instance,
    ]);
}

if ($method === 'GET' && preg_match('#^/api/order/(\d+)$#', $path, $matches)) {
    $id = (int) $matches[1];
    foreach ($orders as $order) {
        if ((int) $order['id'] === $id) {
            jsonResponse(200, [
                'success' => true,
                'message' => 'Mock order fetched successfully',
                'data' => $order,
                'instance' => $instance,
            ]);
        }
    }

    jsonResponse(404, [
        'success' => false,
        'message' => 'Mock order not found',
        'instance' => $instance,
    ]);
}

if ($method === 'POST' && $path === '/api/order/create') {
    $payload = requestJson();
    jsonResponse(201, [
        'success' => true,
        'message' => 'Mock order created successfully',
        'data' => array_merge([
            'id' => 999,
            'status' => 'pending',
            'total' => 0,
        ], $payload),
        'instance' => $instance,
    ]);
}

if ($method === 'PUT' && preg_match('#^/api/order/update/(\d+)$#', $path, $matches)) {
    $id = (int) $matches[1];
    $payload = requestJson();
    jsonResponse(200, [
        'success' => true,
        'message' => 'Mock order updated successfully',
        'data' => array_merge(['id' => $id], $payload),
        'instance' => $instance,
    ]);
}

if ($method === 'DELETE' && preg_match('#^/api/order/delete/(\d+)$#', $path, $matches)) {
    jsonResponse(200, [
        'success' => true,
        'message' => 'Mock order deleted successfully',
        'data' => ['id' => (int) $matches[1]],
        'instance' => $instance,
    ]);
}

jsonResponse(404, [
    'success' => false,
    'message' => 'Mock route not found',
    'path' => $path,
    'instance' => $instance,
]);