<?php
session_start();

require_once __DIR__ . '/config/database.php';

// Recebe o ID do perfil
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: talentos.php');
    exit;
}

// Busca o perfil e os dados do usuário
$sql = "
    SELECT
        p.*,
        u.nome_usuario
    FROM perfis_talentos p
    INNER JOIN usuarios u ON u.id = p.usuario_id
    WHERE p.id = :id
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);

$talento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$talento) {
    header('Location: talentos.php');
    exit;
}

// Protege os textos exibidos na página
function escapar($valor)
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

// Verifica links externos
function linkSeguro($url)
{
    if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
        return '';
    }

    $esquema = strtolower(parse_url($url, PHP_URL_SCHEME) ?? '');

    return in_array($esquema, ['http', 'https'], true) ? $url : '';
}

// Foto do perfil
$foto = !empty($talento['foto_perfil'])
    ? $talento['foto_perfil']
    : 'img/perfil-padrao.png';

// Links profissionais
$linkedin = linkSeguro($talento['linkedin'] ?? '');
$github = linkSeguro($talento['github'] ?? '');

// Verifica usuário conectado
$usuarioLogado = isset($_SESSION['usuario_id']);

// Verifica se o perfil pertence ao usuário conectado
$ehDono = $usuarioLogado &&
    (int) $_SESSION['usuario_id'] === (int) $talento['usuario_id'];

// Cria token para as ações do perfil
if ($ehDono && empty($_SESSION['perfil_csrf_token'])) {
    $_SESSION['perfil_csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta
        name="description"
        content="Conheça o perfil profissional de <?= escapar($talento['nome_completo']) ?> no Novos Talentos.">

    <title><?= escapar($talento['nome_completo']) ?> | Novos Talentos</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/perfil.css?v=3">
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

                <?php if ($usuarioLogado): ?>

                    <a href="criar-perfil.php" class="button">
                        Criar perfil
                    </a>

                    <a href="sair.php" class="button">
                        Sair
                    </a>

                <?php else: ?>

                    <a href="cadastro.php" class="button">
                        Cadastre-se
                    </a>

                <?php endif; ?>

            </nav>

        </div>

    </header>


    <!-- CONTEÚDO DO PERFIL -->

    <main class="pagina-perfil">

        <div class="container-perfil">

            <a href="talentos.php" class="voltar-talentos">
                <span>←</span> Voltar para talentos
            </a>


            <!-- APRESENTAÇÃO -->

            <section class="perfil-cabecalho">

                <div class="perfil-foto-container">

                    <img
                        src="<?= escapar($foto) ?>"
                        alt="Foto de <?= escapar($talento['nome_completo']) ?>"
                        class="foto-perfil-grande">

                </div>

                <div class="perfil-identificacao">

                    <span class="perfil-etiqueta">
                        PERFIL PROFISSIONAL
                    </span>

                    <h1>
                        <?= escapar($talento['nome_completo']) ?>
                    </h1>

                    <p class="perfil-formacao">
                        <?= escapar($talento['formacao'] ?: 'Formação não informada') ?>
                    </p>

                    <?php if (!empty($talento['area_interesse'])): ?>

                        <p class="perfil-area">
                            <?= escapar($talento['area_interesse']) ?>
                        </p>

                    <?php endif; ?>

                    <?php if (!empty($talento['cidade']) || !empty($talento['estado'])): ?>

                        <p class="perfil-localizacao">

                            <span>⌖</span>

                            <?= escapar($talento['cidade']) ?>

                            <?= !empty($talento['cidade']) && !empty($talento['estado']) ? ' - ' : '' ?>

                            <?= escapar($talento['estado']) ?>

                        </p>

                    <?php endif; ?>


                    <!-- LINKS PROFISSIONAIS -->

                    <?php if ($linkedin || $github): ?>

                        <div class="perfil-links">

                            <?php if ($linkedin): ?>

                                <a
                                    href="<?= escapar($linkedin) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="link-linkedin">
                                    LinkedIn ↗
                                </a>

                            <?php endif; ?>

                            <?php if ($github): ?>

                                <a
                                    href="<?= escapar($github) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="link-github">
                                    GitHub ↗
                                </a>

                            <?php endif; ?>

                        </div>

                    <?php endif; ?>


                    <!-- AÇÕES DO DONO DO PERFIL -->


                    <!-- AÇÕES DO DONO DO PERFIL -->

                    <?php if ($ehDono): ?>

                        <div class="acoes-perfil">

                            <a
                                href="editar-perfil.php?id=<?= (int) $talento['id'] ?>"
                                class="acao-editar">

                                <span class="icone-acao">✎</span>
                                <span>Editar perfil</span>

                            </a>

                            <form
                                method="POST"
                                action="excluir-meu-perfil.php"
                                class="form-excluir-perfil"
                                onsubmit="return confirm('Tem certeza de que deseja excluir seu perfil? Essa ação não poderá ser desfeita.');">

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $talento['id'] ?>">

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= escapar($_SESSION['perfil_csrf_token']) ?>">

                                <button
                                    type="submit"
                                    class="acao-excluir"
                                    style="
        background-color: #FFF1F2 !important;
        background-image: none !important;
        color: #BE123C !important;
        border: 1px solid #FDA4AF !important;
        box-shadow: none !important;
        opacity: 1 !important;
    ">

                                    <span class="icone-acao">⌫</span>
                                    <span>Excluir perfil</span>

                                </button>

                            </form>

                        </div>

                    <?php endif; ?>
                </div>

            </section>


            <!-- SOBRE MIM -->

            <section class="perfil-secao">

                <div class="perfil-secao-titulo">
                    <span class="perfil-icone">01</span>
                    <h2>Sobre mim</h2>
                </div>

                <p>
                    <?= nl2br(escapar($talento['sobre'] ?: 'Nenhuma apresentação informada.')) ?>
                </p>

            </section>


            <!-- COMPETÊNCIAS -->

            <section class="perfil-secao">

                <div class="perfil-secao-titulo">
                    <span class="perfil-icone">02</span>
                    <h2>Competências e habilidades</h2>
                </div>

                <p>
                    <?= nl2br(escapar($talento['habilidades'] ?: 'Nenhuma competência informada.')) ?>
                </p>

            </section>


            <!-- EXPERIÊNCIA -->

            <section class="perfil-secao">

                <div class="perfil-secao-titulo">
                    <span class="perfil-icone">03</span>
                    <h2>Experiência</h2>
                </div>

                <p>
                    <?= nl2br(escapar($talento['experiencia'] ?: 'Nenhuma experiência informada.')) ?>
                </p>

            </section>


            <!-- OBJETIVOS -->

            <section class="perfil-secao">

                <div class="perfil-secao-titulo">
                    <span class="perfil-icone">04</span>
                    <h2>Objetivos profissionais</h2>
                </div>

                <p>
                    <?= nl2br(escapar($talento['objetivo'] ?: 'Nenhum objetivo informado.')) ?>
                </p>

            </section>

        </div>

    </main>


    <!-- RODAPÉ -->

    <footer class="footer">

        <div class="footer-bottom">

            <p>
                © 2026 Novos Talentos. Todos os direitos reservados.
            </p>

            <p>
                Desenvolvido por Gabriel Campos
                <span>↗</span>
            </p>

        </div>

    </footer>

</body>

</html>