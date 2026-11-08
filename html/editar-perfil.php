
<?php
session_start();

require_once __DIR__ . '/config/database.php';

// Verifica se o usuário está conectado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: cadastro.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];
$erro = '';

// Verifica o ID recebido
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: talentos.php');
    exit;
}

// Busca somente o perfil pertencente ao usuário conectado
$sql = "
    SELECT *
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

// Impede a edição de perfil inexistente ou de outra pessoa
if (!$perfil) {
    header('Location: talentos.php');
    exit;
}

// Token de segurança
if (empty($_SESSION['perfil_csrf_token'])) {
    $_SESSION['perfil_csrf_token'] = bin2hex(random_bytes(32));
}

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (
        empty($_SESSION['perfil_csrf_token']) ||
        !hash_equals($_SESSION['perfil_csrf_token'], $token)
    ) {
        $erro = 'Solicitação inválida. Atualize a página e tente novamente.';
    } else {

        $nomeCompleto = trim($_POST['nome_completo'] ?? '');
        $areaInteresse = trim($_POST['area_interesse'] ?? '');
        $formacao = trim($_POST['formacao'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $estado = strtoupper(trim($_POST['estado'] ?? ''));
        $sobre = trim($_POST['sobre'] ?? '');
        $habilidades = trim($_POST['habilidades'] ?? '');
        $experiencia = trim($_POST['experiencia'] ?? '');
        $objetivo = trim($_POST['objetivo'] ?? '');
        $linkedin = trim($_POST['linkedin'] ?? '');
        $github = trim($_POST['github'] ?? '');

        if (!$nomeCompleto || !$formacao || !$sobre) {
            $erro = 'Preencha o nome completo, a formação e o campo Sobre mim.';
        } elseif (strlen($estado) !== 2 && $estado !== '') {
            $erro = 'Informe a sigla do estado com duas letras.';
        } elseif (
            $linkedin &&
            (
                !filter_var($linkedin, FILTER_VALIDATE_URL) ||
                !in_array(strtolower(parse_url($linkedin, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)
            )
        ) {
            $erro = 'Informe um endereço válido para o LinkedIn.';
        } elseif (
            $github &&
            (
                !filter_var($github, FILTER_VALIDATE_URL) ||
                !in_array(strtolower(parse_url($github, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)
            )
        ) {
            $erro = 'Informe um endereço válido para o GitHub.';
        } else {

            // Mantém a foto atual caso nenhuma nova seja enviada
            $fotoPerfil = $perfil['foto_perfil'];

            // Processa uma nova foto, caso tenha sido enviada
            if (
                isset($_FILES['foto_perfil']) &&
                $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                if ($_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
                    $erro = 'Não foi possível enviar a foto.';
                } elseif ($_FILES['foto_perfil']['size'] > 2 * 1024 * 1024) {
                    $erro = 'A foto deve ter no máximo 2 MB.';
                } else {

                    $tiposPermitidos = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp'
                    ];

                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $tipoImagem = $finfo->file($_FILES['foto_perfil']['tmp_name']);

                    if (!isset($tiposPermitidos[$tipoImagem])) {
                        $erro = 'Envie uma imagem JPG, PNG ou WEBP.';
                    } else {

                        $pasta = __DIR__ . '/uploads/perfis/';

                        if (!is_dir($pasta)) {
                            mkdir($pasta, 0755, true);
                        }

                        $nomeArquivo = bin2hex(random_bytes(16))
                            . '.' . $tiposPermitidos[$tipoImagem];

                        if (
                            move_uploaded_file(
                                $_FILES['foto_perfil']['tmp_name'],
                                $pasta . $nomeArquivo
                            )
                        ) {
                            $fotoPerfil = 'uploads/perfis/' . $nomeArquivo;
                        } else {
                            $erro = 'Não foi possível salvar a foto.';
                        }
                    }
                }
            }

            // Atualiza os dados no banco
            if (!$erro) {

                $sql = "
                    UPDATE perfis_talentos
                    SET
                        nome_completo = :nome_completo,
                        area_interesse = :area_interesse,
                        formacao = :formacao,
                        cidade = :cidade,
                        estado = :estado,
                        sobre = :sobre,
                        habilidades = :habilidades,
                        experiencia = :experiencia,
                        objetivo = :objetivo,
                        linkedin = :linkedin,
                        github = :github,
                        foto_perfil = :foto_perfil
                    WHERE id = :id
                    AND usuario_id = :usuario_id
                ";

                try {

                    $stmt = $pdo->prepare($sql);

                    $stmt->execute([
                        'nome_completo' => $nomeCompleto,
                        'area_interesse' => $areaInteresse ?: null,
                        'formacao' => $formacao,
                        'cidade' => $cidade ?: null,
                        'estado' => $estado ?: null,
                        'sobre' => $sobre,
                        'habilidades' => $habilidades ?: null,
                        'experiencia' => $experiencia ?: null,
                        'objetivo' => $objetivo ?: null,
                        'linkedin' => $linkedin ?: null,
                        'github' => $github ?: null,
                        'foto_perfil' => $fotoPerfil,
                        'id' => $id,
                        'usuario_id' => $usuarioId
                    ]);

                    header('Location: perfil.php?id=' . $id);
                    exit;

                } catch (PDOException $e) {
                    $erro = 'Não foi possível atualizar seu perfil. Tente novamente.';
                }
            }
        }
    }
}

// Função para proteger os textos
function escapar($valor)
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

// Valores exibidos: preserva o que foi digitado se houver erro
function valorCampo($campo, $perfil)
{
    return escapar($_POST[$campo] ?? $perfil[$campo] ?? '');
}

// Foto atual
$fotoAtual = !empty($perfil['foto_perfil'])
    ? $perfil['foto_perfil']
    : 'img/perfil-padrao.png';

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar perfil | Novos Talentos</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/criar-perfil.css">
</head>

<body>

    <!-- CABEÇALHO -->

    <header class="header">

        <div class="container navbar">

            <a href="index.php" class="logo">

                <span class="logo-icon">
                    <img src="img/logo-trans.png" alt="Logo Novos Talentos">
                </span>

                <span>
                    Novos <span class="logo-highlight">Talentos</span>
                </span>

            </a>

            <nav class="nav-links" id="navLinks">

                <a href="index.php">Início</a>
                <a href="talentos.php">Talentos</a>
                <a href="como-funciona.php">Como Funciona</a>
                <a href="sair.php" class="button">Sair</a>

            </nav>

        </div>

    </header>


    <!-- FORMULÁRIO DE EDIÇÃO -->

    <main class="pagina-criar-perfil">

        <section class="formulario-perfil">

            <div class="perfil-intro">

                <span class="perfil-tag">SEU ESPAÇO PROFISSIONAL</span>

                <h1>Editar perfil</h1>

                <p>
                    Atualize suas informações profissionais e mantenha
                    seu perfil sempre atualizado.
                </p>

            </div>

            <?php if ($erro): ?>

                <div class="mensagem-erro">
                    <?= escapar($erro) ?>
                </div>

            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapar($_SESSION['perfil_csrf_token']) ?>">

                <!-- INFORMAÇÕES PESSOAIS -->

                <div class="grupo-formulario">

                    <h2>Informações pessoais</h2>

                    <div class="campo">

                        <label for="foto_perfil">Foto de perfil</label>

                        <img
                            src="<?= escapar($fotoAtual) ?>"
                            alt="Foto atual do perfil"
                            style="width: 110px; height: 110px; object-fit: cover; border-radius: 50%; margin-bottom: 12px;">

                        <input
                            type="file"
                            id="foto_perfil"
                            name="foto_perfil"
                            accept="image/jpeg,image/png,image/webp">

                        <small>
                            Selecione uma nova foto para substituir a atual.
                            JPG, PNG ou WEBP. Máximo: 2 MB.
                        </small>

                    </div>

                    <div class="campo">

                        <label for="nome_completo">Nome completo *</label>

                        <input
                            type="text"
                            id="nome_completo"
                            name="nome_completo"
                            maxlength="150"
                            value="<?= valorCampo('nome_completo', $perfil) ?>"
                            required>

                    </div>

                    <div class="campo">

                        <label for="formacao">Formação *</label>

                        <input
                            type="text"
                            id="formacao"
                            name="formacao"
                            maxlength="150"
                            value="<?= valorCampo('formacao', $perfil) ?>"
                            required>

                    </div>

                    <div class="campo">

                        <label for="area_interesse">Área de interesse</label>

                        <input
                            type="text"
                            id="area_interesse"
                            name="area_interesse"
                            maxlength="100"
                            value="<?= valorCampo('area_interesse', $perfil) ?>">

                    </div>

                    <div class="campos-duplos">

                        <div class="campo">

                            <label for="cidade">Cidade</label>

                            <input
                                type="text"
                                id="cidade"
                                name="cidade"
                                maxlength="100"
                                value="<?= valorCampo('cidade', $perfil) ?>">

                        </div>

                        <div class="campo">

                            <label for="estado">Estado</label>

                            <input
                                type="text"
                                id="estado"
                                name="estado"
                                maxlength="2"
                                value="<?= valorCampo('estado', $perfil) ?>">

                        </div>

                    </div>

                </div>


                <!-- SOBRE VOCÊ -->

                <div class="grupo-formulario">

                    <h2>Sobre você</h2>

                    <div class="campo">

                        <label for="sobre">Sobre mim *</label>

                        <textarea
                            id="sobre"
                            name="sobre"
                            rows="5"
                            required><?= valorCampo('sobre', $perfil) ?></textarea>

                    </div>

                    <div class="campo">

                        <label for="habilidades">Competências e habilidades</label>

                        <textarea
                            id="habilidades"
                            name="habilidades"
                            rows="4"><?= valorCampo('habilidades', $perfil) ?></textarea>

                    </div>

                    <div class="campo">

                        <label for="experiencia">Experiência</label>

                        <textarea
                            id="experiencia"
                            name="experiencia"
                            rows="4"><?= valorCampo('experiencia', $perfil) ?></textarea>

                    </div>

                    <div class="campo">

                        <label for="objetivo">Objetivo profissional</label>

                        <textarea
                            id="objetivo"
                            name="objetivo"
                            rows="3"><?= valorCampo('objetivo', $perfil) ?></textarea>

                    </div>

                </div>


                <!-- LINKS PROFISSIONAIS -->

                <div class="grupo-formulario">

                    <h2>Links profissionais</h2>

                    <div class="campo">

                        <label for="linkedin">LinkedIn</label>

                        <input
                            type="url"
                            id="linkedin"
                            name="linkedin"
                            value="<?= valorCampo('linkedin', $perfil) ?>"
                            placeholder="https://www.linkedin.com/in/seu-perfil">

                    </div>

                    <div class="campo">

                        <label for="github">GitHub</label>

                        <input
                            type="url"
                            id="github"
                            name="github"
                            value="<?= valorCampo('github', $perfil) ?>"
                            placeholder="https://github.com/seu-usuario">

                    </div>

                </div>


                <!-- BOTÕES -->

                <div class="acoes-formulario">

                    <a
                        href="perfil.php?id=<?= (int) $id ?>"
                        class="botao-cancelar">
                        Cancelar
                    </a>

                    <button type="submit" class="botao-salvar">
                        Salvar alterações
                    </button>

                </div>

                <p class="campos-obrigatorios">
                    * Campos obrigatórios
                </p>

            </form>

        </section>

    </main>


    <!-- RODAPÉ -->

    <footer class="footer">

        <div class="footer-bottom">

            <p>
                © 2026 Novos Talentos. Todos os direitos reservados.
            </p>

            <p>
                Desenvolvido por Gabriel Campos
            </p>

        </div>

    </footer>

</body>

</html>
