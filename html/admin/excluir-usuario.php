
<?php

// Verifica se o administrador está autenticado
require_once __DIR__ . '/verificar-admin.php';

// Conecta ao banco de dados
require_once __DIR__ . '/../config/database.php';

// Aceita somente requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: usuarios.php');
    exit;
}

// Confere o token CSRF
$token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    http_response_code(403);
    exit('Solicitação inválida. Atualize a página e tente novamente.');
}

// Valida o ID recebido
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: usuarios.php');
    exit;
}

try {

    // Verifica se o usuário existe
    $sql = "
        SELECT id
        FROM usuarios
        WHERE id = :id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id' => $id
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header('Location: usuarios.php');
        exit;
    }

    // Exclui o usuário
    // O ON DELETE CASCADE remove seu perfil associado
    $sql = "
        DELETE FROM usuarios
        WHERE id = :id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id' => $id
    ]);

    // Volta para a listagem
    header('Location: usuarios.php?excluido=1');
    exit;

} catch (PDOException $erro) {

    error_log('Erro ao excluir usuário: ' . $erro->getMessage());

    http_response_code(500);

    exit('Não foi possível excluir o usuário. Verifique as dependências no banco de dados.');
}
