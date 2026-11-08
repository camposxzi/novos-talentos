<?php
session_start();

require_once __DIR__ . '/config/database.php';

// Verifica se o usuário está conectado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: cadastro.php');
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$erro = '';

// Verifica se já existe um perfil para este usuário
$consulta = $pdo->prepare(
    "SELECT id FROM perfis_talentos WHERE usuario_id = :usuario_id"
);
$consulta->execute(['usuario_id' => $usuarioId]);

$perfilExistente = $consulta->fetch(PDO::FETCH_ASSOC);

if ($perfilExistente) {
    header('Location: perfil.php?id=' . $perfilExistente['id']);
    exit;
}

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
    } elseif ($linkedin && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
        $erro = 'Informe um endereço válido para o LinkedIn.';
    } elseif ($github && !filter_var($github, FILTER_VALIDATE_URL)) {
        $erro = 'Informe um endereço válido para o GitHub.';
    } else {

        $fotoPerfil = null;

        // Processa a foto, caso tenha sido enviada
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

        // Salva os dados no banco
        if (!$erro) {

            $sql = "INSERT INTO perfis_talentos (
                        usuario_id,
                        nome_completo,
                        area_interesse,
                        formacao,
                        cidade,
                        estado,
                        sobre,
                        habilidades,
                        experiencia,
                        objetivo,
                        linkedin,
                        github,
                        foto_perfil
                    ) VALUES (
                        :usuario_id,
                        :nome_completo,
                        :area_interesse,
                        :formacao,
                        :cidade,
                        :estado,
                        :sobre,
                        :habilidades,
                        :experiencia,
                        :objetivo,
                        :linkedin,
                        :github,
                        :foto_perfil
                    )
                    RETURNING id";

            try {
                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    'usuario_id' => $usuarioId,
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
                    'foto_perfil' => $fotoPerfil
                ]);

                $perfilId = $stmt->fetchColumn();

                header('Location: perfil.php?id=' . $perfilId);
                exit;
            } catch (PDOException $e) {
                $erro = 'Não foi possível salvar seu perfil. Tente novamente.';
            }
        }
    }
}

function escapar($valor)
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Criar perfil | Novos Talentos</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/criar-perfil.css">
</head>

<body>

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

    <main class="pagina-criar-perfil">

        <section class="formulario-perfil">

            <div class="perfil-intro">
                <span class="perfil-tag">SEU ESPAÇO PROFISSIONAL</span>

                <h1>Crie seu perfil</h1>

                <p>
                    Apresente suas habilidades, experiências e objetivos.
                    Mostre às empresas o seu potencial.
                </p>
            </div>

            <?php if ($erro): ?>
                <div class="mensagem-erro">
                    <?= escapar($erro) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="grupo-formulario">

                    <h2>Informações pessoais</h2>

                    <div class="campo">
                        <label for="foto_perfil">Foto de perfil</label>
                        <input
                            type="file"
                            id="foto_perfil"
                            name="foto_perfil"
                            accept="image/jpeg,image/png,image/webp">
                        <small>JPG, PNG ou WEBP. Tamanho máximo: 2 MB.</small>
                    </div>

                    <div class="campo">
                        <label for="nome_completo">Nome completo *</label>
                        <input
                            type="text"
                            id="nome_completo"
                            name="nome_completo"
                            maxlength="150"
                            value="<?= escapar($_POST['nome_completo'] ?? '') ?>"
                            placeholder="Digite seu nome completo"
                            required>
                    </div>

                    <div class="campo">
                        <label for="formacao">Formação *</label>
                        <input
                            type="text"
                            id="formacao"
                            name="formacao"
                            maxlength="150"
                            value="<?= escapar($_POST['formacao'] ?? '') ?>"
                            placeholder="Ex.: Ensino Médio, Administração..."
                            required>
                    </div>

                    <div class="campo">
                        <label for="area_interesse">Área de interesse</label>
                        <input
                            type="text"
                            id="area_interesse"
                            name="area_interesse"
                            maxlength="100"
                            value="<?= escapar($_POST['area_interesse'] ?? '') ?>"
                            placeholder="Ex.: Tecnologia, Marketing, Administração...">
                    </div>

                    <div class="campos-duplos">

                        <div class="campo">
                            <label for="cidade">Cidade</label>
                            <input
                                type="text"
                                id="cidade"
                                name="cidade"
                                maxlength="100"
                                value="<?= escapar($_POST['cidade'] ?? '') ?>"
                                placeholder="Sua cidade">
                        </div>

                        <div class="campo">
                            <label for="estado">Estado</label>
                            <input
                                type="text"
                                id="estado"
                                name="estado"
                                maxlength="2"
                                value="<?= escapar($_POST['estado'] ?? '') ?>"
                                placeholder="Ex.: MG">
                        </div>

                    </div>

                </div>

                <div class="grupo-formulario">

                    <h2>Sobre você</h2>

                    <div class="campo">
                        <label for="sobre">Sobre mim *</label>
                        <textarea
                            id="sobre"
                            name="sobre"
                            rows="5"
                            placeholder="Conte um pouco sobre você, seus interesses e sua trajetória..."
                            required><?= escapar($_POST['sobre'] ?? '') ?></textarea>
                    </div>

                    <div class="campo">
                        <label for="habilidades">Competências e habilidades</label>
                        <textarea
                            id="habilidades"
                            name="habilidades"
                            rows="4"
                            placeholder="Ex.: Comunicação, trabalho em equipe, informática..."><?= escapar($_POST['habilidades'] ?? '') ?></textarea>
                    </div>

                    <div class="campo">
                        <label for="experiencia">Experiência</label>
                        <textarea
                            id="experiencia"
                            name="experiencia"
                            rows="4"
                            placeholder="Conte sobre suas experiências profissionais, projetos ou atividades..."><?= escapar($_POST['experiencia'] ?? '') ?></textarea>
                    </div>

                    <div class="campo">
                        <label for="objetivo">Objetivo profissional</label>
                        <textarea
                            id="objetivo"
                            name="objetivo"
                            rows="3"
                            placeholder="Quais são seus objetivos profissionais?"><?= escapar($_POST['objetivo'] ?? '') ?></textarea>
                    </div>

                </div>

                <div class="grupo-formulario">

                    <h2>Links profissionais</h2>

                    <div class="campo">
                        <label for="linkedin">LinkedIn</label>
                        <input
                            type="url"
                            id="linkedin"
                            name="linkedin"
                            value="<?= escapar($_POST['linkedin'] ?? '') ?>"
                            placeholder="https://www.linkedin.com/in/seu-perfil">
                    </div>

                    <div class="campo">
                        <label for="github">GitHub</label>
                        <input
                            type="url"
                            id="github"
                            name="github"
                            value="<?= escapar($_POST['github'] ?? '') ?>"
                            placeholder="https://github.com/seu-usuario">
                    </div>

                </div>

                <div class="acoes-formulario">

                    <a href="index.php" class="botao-cancelar">
                        Cancelar
                    </a>

                    <button type="submit" class="botao-salvar">
                        Salvar meu perfil
                    </button>

                </div>

                <p class="campos-obrigatorios">
                    * Campos obrigatórios
                </p>

            </form>

        </section>

    </main>

    <footer class="footer">
        <div class="footer-bottom">
            <p>© 2026 Novos Talentos. Todos os direitos reservados.</p>
            <p>Desenvolvido por Gabriel Campos</p>
        </div>
    </footer>

</body>

</html>