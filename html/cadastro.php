
<?php
session_start();
require_once __DIR__ . '/config/database.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? '';

    // CADASTRO
    if ($acao === 'cadastrar') {

        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $tipo = $_POST['tipo_usuario'] ?? '';

        $tiposValidos = ['estudante', 'recem_formado'];

        if (!$nome || !$email || !$senha || !in_array($tipo, $tiposValidos, true)) {

            $erro = 'Preencha todos os campos corretamente.';

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $erro = 'Digite um e-mail válido.';

        } elseif (strlen($senha) < 8) {

            $erro = 'A senha deve ter pelo menos 8 caracteres.';

        } else {

            $consulta = $pdo->prepare(
                "SELECT id FROM usuarios WHERE email = :email"
            );

            $consulta->execute(['email' => $email]);

            if ($consulta->fetch()) {

                $erro = 'Este e-mail já está cadastrado.';

            } else {

                $hash = password_hash($senha, PASSWORD_DEFAULT);

                $sql = "INSERT INTO usuarios
                        (nome_usuario, email, senha_hash, tipo_usuario)
                        VALUES (:nome, :email, :senha, :tipo)
                        RETURNING id";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    'nome' => $nome,
                    'email' => $email,
                    'senha' => $hash,
                    'tipo' => $tipo
                ]);

                $novoUsuarioId = $stmt->fetchColumn();

                session_regenerate_id(true);

                $_SESSION['usuario_id'] = $novoUsuarioId;
                $_SESSION['nome_usuario'] = $nome;
                $_SESSION['tipo_usuario'] = $tipo;

                header('Location: index.php');
                exit;
            }
        }
    }

    // LOGIN
    if ($acao === 'entrar') {

        $email = trim($_POST['email_login'] ?? '');
        $senha = $_POST['senha_login'] ?? '';

        $stmt = $pdo->prepare(
            "SELECT id, nome_usuario, email, senha_hash, tipo_usuario
             FROM usuarios
             WHERE email = :email"
        );

        $stmt->execute(['email' => $email]);

        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {

            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nome_usuario'] = $usuario['nome_usuario'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

            header('Location: index.php');
            exit;

        } else {

            $erro = 'E-mail ou senha incorretos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Acesso | Novos Talentos</title>

    <link rel="stylesheet" href="css/cadastro.css">
    <script src="js/cadastro.js" defer></script>
</head>

<body>

<div class="container" id="container">

    <div class="form-container cadastro-container">

        <form method="POST" action="cadastro.php">

            <h1>Criar conta</h1>
            <p>Comece sua jornada com o Novos Talentos.</p>

            <?php if ($erro && ($_POST['acao'] ?? '') === 'cadastrar'): ?>
                <div class="mensagem erro">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="acao" value="cadastrar">

            <input
                type="text"
                name="nome"
                placeholder="Nome completo"
                maxlength="80"
                required
            >

            <input
                type="email"
                name="email"
                placeholder="E-mail"
                maxlength="150"
                required
            >

            <input
                type="password"
                name="senha"
                placeholder="Senha (mínimo 8 caracteres)"
                minlength="8"
                required
            >

            <select name="tipo_usuario" required>
                <option value="">Tipo de usuário</option>
                <option value="estudante">Estudante</option>
                <option value="recem_formado">Recém-formado</option>
            </select>

            <button type="submit">Cadastrar</button>

        </form>

    </div>

    <div class="form-container login-container">

        <form method="POST" action="cadastro.php">

            <h1>Bem-vindo de volta!</h1>
            <p>Entre na sua conta para continuar.</p>

            <?php if ($erro && ($_POST['acao'] ?? '') === 'entrar'): ?>
                <div class="mensagem erro">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="acao" value="entrar">

            <input
                type="email"
                name="email_login"
                placeholder="E-mail"
                required
            >

            <input
                type="password"
                name="senha_login"
                placeholder="Senha"
                required
            >

            <button type="submit">Entrar</button>

        </form>

    </div>

    <div class="painel-container">

        <div class="painel">

            <div class="painel-conteudo painel-esquerdo">

                <h1>Já tem uma conta?</h1>

                <p>Entre e continue explorando novas oportunidades.</p>

                <button class="botao-transparente" id="entrar">
                    Fazer login
                </button>

            </div>

            <div class="painel-conteudo painel-direito">

                <h1>Olá, usuário!</h1>

                <p>Crie sua conta e descubra novas oportunidades para seu futuro.</p>

                <button class="botao-transparente" id="cadastrar">
                    Criar conta
                </button>

            </div>

        </div>

    </div>

</div>

</body>
</html>
