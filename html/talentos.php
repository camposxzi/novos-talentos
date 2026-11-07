
<?php
require_once __DIR__ . '/config/database.php';

$sql = "
    SELECT
        p.id,
        p.nome_completo,
        p.formacao,
        p.cidade,
        p.estado,
        p.sobre,
        p.foto_perfil,
        p.linkedin
    FROM perfis_talentos p
    ORDER BY p.criado_em DESC
";

$stmt = $pdo->query($sql);
$talentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

function escapar($valor) {
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Conheça estudantes e profissionais cadastrados no Novos Talentos.">

    <title>Talentos | Novos Talentos</title>

    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/talentos.css">
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
                <a href="talentos.php" class="active">Talentos</a>
                <a href="como-funciona.html">Como Funciona</a>
                <a href="cadastro.php" class="button">Cadastre-se</a>
            </nav>

        </div>
    </header>

    <main class="pagina-talentos">

        <section class="talentos-intro">
            <span class="section-tag">NOSSA COMUNIDADE</span>
            <h1>Conheça nossos talentos</h1>
            <p>Estudantes e profissionais prontos para novas oportunidades.</p>
        </section>

        <section class="lista-talentos">

            <?php if (!empty($talentos)): ?>

                <?php foreach ($talentos as $talento): ?>

                    <article class="card-talento">

                        <img
                            class="foto-talento"
                            src="<?= escapar($talento['foto_perfil'] ?: './img/perfil-padrao.png') ?>"
                            alt="Foto de <?= escapar($talento['nome_completo']) ?>"
                        >

                        <h2><?= escapar($talento['nome_completo']) ?></h2>

                        <p class="formacao">
                            <?= escapar($talento['formacao'] ?: 'Formação não informada') ?>
                        </p>

                        <p class="localizacao">
                            <span>📍</span>
                            <?= escapar($talento['cidade'] ?: 'Cidade não informada') ?>
                            <?= !empty($talento['estado'])
                                ? ' - ' . escapar($talento['estado'])
                                : '' ?>
                        </p>

                        <p class="descricao">
                            <?= escapar($talento['sobre'] ?: 'Apresentação não informada.') ?>
                        </p>

                        <a
                            href="perfil.php?id=<?= (int) $talento['id'] ?>"
                            class="botao-perfil"
                        >
                            Ver perfil
                        </a>

                        <?php if (!empty($talento['linkedin'])): ?>
                            <a
                                href="<?= escapar($talento['linkedin']) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="link-linkedin"
                            >
                                LinkedIn
                            </a>
                        <?php endif; ?>

                    </article>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="sem-talentos">
                    <h2>Ainda não existem perfis cadastrados.</h2>
                </div>

            <?php endif; ?>

        </section>

    </main>

    <footer class="footer">

        <div class="footer-main">

            <div class="footer-brand">
                <div class="footer-logo">
                    <img src="img/logo-trans.png" alt="Logo Novos Talentos">
                    <span>Novos <strong>Talentos</strong></span>
                </div>

                <p>Conectando talentos em novas oportunidades.</p>
            </div>

            <div class="footer-column">
                <h3>Sobre o projeto</h3>
                <p>
                    O Novos Talentos é uma plataforma criada para aproximar
                    estudantes e profissionais de empresas e novas oportunidades.
                    Aqui, o seu talento tem espaço.
                </p>
            </div>

            <div class="footer-column">
                <h3>Links</h3>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="talentos.php">Talentos</a></li>
                    <li><a href="como-funciona.html">Como funciona</a></li>
                </ul>
            </div>

            <div class="footer-column footer-contact">
                <h3>Contato</h3>

                <a href="mailto:sipergabriel95@gmail.com">
                    sipergabriel95@gmail.com
                </a>

                <div class="social-links">

                    <div class="linkedin-tooltip">
                        <a
                            href="https://www.linkedin.com/in/gabriel-campos-316942425"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="LinkedIn"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 512 512"
                                fill="currentColor"
                                class="linkedin-svg">
                                <path d="M444.17 32H70.28C49.85 32 32 46.7 32 66.89v374.72C32 461.91 49.85 480 70.28 480h373.78c20.54 0 35.94-18.21 35.94-38.39V66.89C480.12 46.7 464.6 32 444.17 32zm-273.3 373.43h-64.18V205.88h64.18zM141 175.54h-.46c-20.54 0-33.84-15.29-33.84-34.43 0-19.49 13.65-34.42 34.65-34.42s33.85 14.82 34.31 34.42c-.01 19.14-13.31 34.43-34.66 34.43zm264.43 229.89h-64.18V296.32c0-26.14-9.34-44-32.56-44-17.74 0-28.24 12-32.91 23.69-1.75 4.2-2.22 9.92-2.22 15.76v113.66h-64.18V205.88h64.18v27.77c9.34-13.3 23.93-32.44 57.88-32.44 42.13 0 74 27.77 74 87.64z"/>
                            </svg>
                        </a>
                        <span class="linkedin-label">LinkedIn</span>
                    </div>

                    <div class="group github-tooltip">
                        <a
                            href="https://github.com/camposxzi"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="GitHub"
                        >
                            <svg stroke-linejoin="round"
                                stroke-linecap="round"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 24 24"
                                class="github-icon">
                                <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>
                            </svg>
                        </a>
                        <span class="github-label">GitHub</span>
                    </div>

                </div>
            </div>

        </div>

        <div class="footer-bottom">
            <p>
                © <?= date('Y') ?> Novos Talentos. Todos os direitos reservados.
            </p>

            <p>
                Desenvolvido por Gabriel Campos
                <span>↗</span>
            </p>
        </div>

    </footer>

</body>
</html>
