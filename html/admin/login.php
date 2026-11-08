
<?php
session_start();


$caminhoCredenciais = dirname(__DIR__, 4)
    . DIRECTORY_SEPARATOR . 'segredos'
    . DIRECTORY_SEPARATOR . 'admin.php';

$credenciais = require $caminhoCredenciais;

$erro = '';

if (
    isset($_SESSION['admin_logado']) &&
    $_SESSION['admin_logado'] === true
) {
    header('Location: usuarios.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (
        $usuario === $credenciais['usuario'] &&
        password_verify($senha, $credenciais['senha_hash'])
    ) {
        session_regenerate_id(true);

        $_SESSION['admin_logado'] = true;

        header('Location: usuarios.php');
        exit;
    }

    $erro = 'Usuário ou senha incorretos.';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo</title>
    <link rel="stylesheet" href="../css/admin-login.css">
</head>
<body>

<div class="admin-login">
    <h1>Painel Administrativo</h1>
    <p>Entre com suas credenciais.</p>

    <?php if ($erro): ?>
        <p class="login-erro">
            <?= htmlspecialchars($erro) ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <label for="usuario">Usuário</label>
        <input type="text" id="usuario" name="usuario" required>

        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit">Entrar</button>
    </form>
</div>

</body>
</html>
