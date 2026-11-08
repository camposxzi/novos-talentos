
<?php
session_start();

require_once __DIR__ . '/config/database.php';

// Verifica se o usuário está conectado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: cadastro.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];

// Aceita somente requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: talentos.php');
    exit;
}

// Verifica o token de segurança
$token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['perfil_csrf_token']) ||
    !hash_equals($_SESSION['perfil_csrf_token'], $token)
) {
    http_response_code(403);
    exit('Solicitação inválida. Atualize a página e tente novamente.');
}

// Recebe o ID do perfil
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: talentos.php');
    exit;
}

// Busca o perfil somente se pertencer ao usuário conectado
$sql = "
    SELECT id, foto_perfil
    FROM perfis_talentos
    WHERE id = :id
    AND usuario_id = :usuario_id
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'id' => $id,
    'usuario_id' => $usuarioId
]);

$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

// Impede excluir o perfil de outra pessoa
if (!$perfil) {
    header('Location: talentos.php');
    exit;
}

// Exclui somente o perfil do usuário conectado
$sql = "
    DELETE FROM perfis_talentos
    WHERE id = :id
    AND usuario_id = :usuario_id
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'id' => $id,
    'usuario_id' => $usuarioId
]);

// Remove a foto antiga, se estiver dentro da pasta de uploads
if (!empty($perfil['foto_perfil'])) {

    $pastaUploads = realpath(__DIR__ . '/uploads/perfis');
    $caminhoFoto = realpath(__DIR__ . '/' . $perfil['foto_perfil']);

    if (
        $pastaUploads !== false &&
        $caminhoFoto !== false &&
        strpos(
            $caminhoFoto,
            $pastaUploads . DIRECTORY_SEPARATOR
        ) === 0 &&
        is_file($caminhoFoto)
    ) {
        unlink($caminhoFoto);
    }
}

// Remove o token utilizado
unset($_SESSION['perfil_csrf_token']);

// Volta para a página de talentos
header('Location: talentos.php');
exit;
