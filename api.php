<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$dataDir = __DIR__ . '/data';

// Ensure data directory exists
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

function sanitizeFilename(string $name): string
{
    // Remove path traversal attempts and keep only safe characters
    $name = basename($name);
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($name, PATHINFO_FILENAME));
    return $name ?: 'unnamed';
}

function getFilePath(string $filename): string
{
    global $dataDir;
    return $dataDir . '/' . sanitizeFilename($filename) . '.json';
}

function jsonResponse(mixed $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function errorResponse(string $message, int $code = 400): void
{
    jsonResponse(['error' => $message], $code);
}

// List all available JSON files
if (isset($_GET['list'])) {
    $files = [];
    foreach (glob($dataDir . '/*.json') as $file) {
        $files[] = pathinfo($file, PATHINFO_FILENAME);
    }
    sort($files);
    jsonResponse(['files' => $files]);
}

// Get or modify a specific file
$filename = $_GET['file'] ?? null;

if ($filename === null) {
    errorResponse('Missing "file" parameter. Use ?list to get available files, or ?file=name to access a specific file.');
}

$filePath = getFilePath($filename);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (!file_exists($filePath)) {
            errorResponse('File not found', 404);
        }
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            errorResponse('Invalid JSON in file', 500);
        }
        jsonResponse($data);
        break;

    case 'POST':
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            errorResponse('Invalid JSON input: ' . json_last_error_msg());
        }

        $result = file_put_contents(
            $filePath,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        if ($result === false) {
            errorResponse('Failed to write file', 500);
        }

        jsonResponse(['success' => true, 'file' => sanitizeFilename($filename)]);
        break;

    case 'DELETE':
        if (!file_exists($filePath)) {
            errorResponse('File not found', 404);
        }

        if (!unlink($filePath)) {
            errorResponse('Failed to delete file', 500);
        }

        jsonResponse(['success' => true, 'deleted' => sanitizeFilename($filename)]);
        break;

    default:
        errorResponse('Method not allowed', 405);
}
