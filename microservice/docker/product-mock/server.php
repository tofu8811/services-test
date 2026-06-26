<?php

$instance = getenv('INSTANCE_NAME') ?: 'product-mock';
$secret = getenv('GATEWAY_SECRET') ?: 'super-secret-gateway-key';

$products = [
    ['id' => 1, 'name' => 'Laptop Dell Inspiron', 'description' => 'Mock product for gateway test', 'price' => 15000000, 'stock' => 25],
    ['id' => 2, 'name' => 'Keyboard Logitech K380', 'description' => 'Mock product for gateway test', 'price' => 650000, 'stock' => 80],
    ['id' => 3, 'name' => 'Mouse Logitech M331', 'description' => 'Mock product for gateway test', 'price' => 350000, 'stock' => 120],
    ['id' => 4, 'name' => 'Monitor LG 24 inch', 'description' => 'Mock product for gateway test', 'price' => 2800000, 'stock' => 35],
    ['id' => 5, 'name' => 'SSD Samsung 1TB', 'description' => 'Mock product for gateway test', 'price' => 1900000, 'stock' => 50],
    ['id' => 6, 'name' => 'RAM Kingston 16GB', 'description' => 'Mock product for gateway test', 'price' => 1100000, 'stock' => 45],
    ['id' => 7, 'name' => 'Headset HyperX Cloud', 'description' => 'Mock product for gateway test', 'price' => 1450000, 'stock' => 30],
    ['id' => 8, 'name' => 'Webcam Rapoo C260', 'description' => 'Mock product for gateway test', 'price' => 720000, 'stock' => 60],
    ['id' => 9, 'name' => 'USB-C Hub Baseus', 'description' => 'Mock product for gateway test', 'price' => 590000, 'stock' => 75],
    ['id' => 10, 'name' => 'Router TP-Link AX10', 'description' => 'Mock product for gateway test', 'price' => 1250000, 'stock' => 40],
];

function jsonResponse(int $status, array $payload): void
{
    global $instance;

    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Service-Secret');
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
        'service' => 'product-mock',
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

if ($method === 'GET' && $path === '/api/products') {
    jsonResponse(200, [
        'success' => true,
        'message' => 'Mock products fetched successfully',
        'data' => $products,
        'instance' => $instance,
    ]);
}

if ($method === 'GET' && preg_match('#^/api/product/(\d+)$#', $path, $matches)) {
    $id = (int) $matches[1];
    foreach ($products as $product) {
        if ((int) $product['id'] === $id) {
            jsonResponse(200, [
                'success' => true,
                'message' => 'Mock product fetched successfully',
                'data' => $product,
                'instance' => $instance,
            ]);
        }
    }

    jsonResponse(404, [
        'success' => false,
        'message' => 'Mock product not found',
        'instance' => $instance,
    ]);
}

if ($method === 'POST' && $path === '/api/product/create') {
    $payload = requestJson();
    jsonResponse(201, [
        'success' => true,
        'message' => 'Mock product created successfully',
        'data' => array_merge(['id' => 999], $payload),
        'instance' => $instance,
    ]);
}

if ($method === 'PUT' && preg_match('#^/api/product/update/(\d+)$#', $path, $matches)) {
    $id = (int) $matches[1];
    $payload = requestJson();
    jsonResponse(200, [
        'success' => true,
        'message' => 'Mock product updated successfully',
        'data' => array_merge(['id' => $id], $payload),
        'instance' => $instance,
    ]);
}

if ($method === 'DELETE' && preg_match('#^/api/product/delete/(\d+)$#', $path, $matches)) {
    jsonResponse(200, [
        'success' => true,
        'message' => 'Mock product deleted successfully',
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