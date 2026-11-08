
<?php

require_once __DIR__ . '/verificar-admin.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: perfis.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    http_response_code(403);
    exit('Solicitação inválida.');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: perfis.php');
    exit;
}

$sql = "DELETE FROM perfis_talentos WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);

header('Location: perfis.php');
exit;
