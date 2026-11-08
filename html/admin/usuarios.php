
<?php
require_once __DIR__ . '/verificar-admin.php';
require_once __DIR__ . '/../config/database.php';

// Cria token de segurança
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Pesquisa
$busca = trim($_GET['busca'] ?? '');

// Consulta dos usuários
$sql = "
    SELECT
        id,
        nome_usuario,
        email,
        tipo_usuario,
        criado_em
    FROM usuarios
";

if ($busca !== '') {
    $sql .= "
        WHERE nome_usuario ILIKE :busca_nome
        OR email ILIKE :busca_email
    ";
}

$sql .= " ORDER BY criado_em DESC";

$stmt = $pdo->prepare($sql);

if ($busca !== '') {
    $stmt->bindValue(':busca_nome', '%' . $busca . '%');
    $stmt->bindValue(':busca_email', '%' . $busca . '%');
}

$stmt->execute();

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalUsuarios = count($usuarios);

// Proteção de saída
function escapar($valor)
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Usuários | Admin</title>

    <link rel="stylesheet" href="../css/admin.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >

</head>

<body>

<div class="admin-layout">

    <!-- MENU LATERAL -->

    <aside class="sidebar">

        <a href="usuarios.php" class="sidebar-logo">

            <span class="logo-symbol">
                <i class="fa-solid fa-layer-group"></i>
            </span>

            <span>
                NOVOS <strong>TALENTOS</strong>
                <small>ADMINISTRAÇÃO</small>
            </span>

        </a>

        <div class="sidebar-section">

            <span class="sidebar-label">GERENCIAMENTO</span>

            <a href="usuarios.php" class="sidebar-link active">
                <i class="fa-solid fa-users"></i>
                <span>Usuários</span>
            </a>

            <a href="perfis.php" class="sidebar-link">
                <i class="fa-solid fa-id-card"></i>
                <span>Perfis</span>
            </a>

        </div>

        <div class="sidebar-bottom">

            <a href="../index.php" class="sidebar-link">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Voltar ao site</span>
            </a>

            <a href="logout.php" class="sidebar-link logout-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Sair</span>
            </a>

        </div>

    </aside>


    <!-- CONTEÚDO PRINCIPAL -->

    <main class="admin-content">

        <!-- CABEÇALHO -->

        <header class="topbar">

            <div>

                <span class="topbar-label">
                    PAINEL ADMINISTRATIVO
                </span>

                <h1>Usuários</h1>

            </div>

            <div class="admin-profile">

                <div class="admin-avatar">
                    <i class="fa-solid fa-user-shield"></i>
                </div>

                <div>
                    <strong>Administrador</strong>
                    <span>Gestão da plataforma</span>
                </div>

            </div>

        </header>


        <!-- INTRODUÇÃO -->

        <section class="page-intro">

            <div>

                <h2>Gerenciamento de usuários</h2>

                <p>
                    Visualize e gerencie as contas cadastradas no Novos Talentos.
                </p>

            </div>

        </section>


        <!-- ESTATÍSTICAS -->

        <section class="stats-grid">

            <article class="stat-card">

                <div class="stat-icon">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div>

                    <span>Usuários encontrados</span>

                    <strong>
                        <?= $totalUsuarios ?>
                    </strong>

                </div>

            </article>

        </section>


        <!-- LISTAGEM -->

        <section class="content-panel">

            <div class="panel-heading">

                <div>

                    <h2>Todos os usuários</h2>

                    <p>
                        Lista de contas registradas na plataforma.
                    </p>

                </div>

                <span class="result-count">
                    <?= $totalUsuarios ?> registro(s)
                </span>

            </div>


            <!-- PESQUISA -->

            <form method="GET" class="search-form">

                <div class="search-input">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="search"
                        name="busca"
                        placeholder="Pesquisar por nome ou e-mail..."
                        value="<?= escapar($busca) ?>"
                    >

                </div>

                <button type="submit" class="search-button">
                    Pesquisar
                </button>

                <?php if ($busca !== ''): ?>

                    <a href="usuarios.php" class="clear-search">
                        Limpar
                    </a>

                <?php endif; ?>

            </form>


            <!-- TABELA -->

            <div class="table-container">

                <table class="users-table">

                    <thead>

                        <tr>

                            <th>ID</th>
                            <th>Usuário</th>
                            <th>E-mail</th>
                            <th>Tipo</th>
                            <th>Data de cadastro</th>
                            <th>Ação</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if ($totalUsuarios > 0): ?>

                            <?php foreach ($usuarios as $usuario): ?>

                                <tr>

                                    <!-- ID -->

                                    <td class="user-id">
                                        #<?= (int) $usuario['id'] ?>
                                    </td>


                                    <!-- USUÁRIO -->

                                    <td>

                                        <div class="user-name">

                                            <div class="user-avatar">
                                                <i class="fa-solid fa-user"></i>
                                            </div>

                                            <strong>
                                                <?= escapar($usuario['nome_usuario']) ?>
                                            </strong>

                                        </div>

                                    </td>


                                    <!-- E-MAIL -->

                                    <td>
                                        <?= escapar($usuario['email']) ?>
                                    </td>


                                    <!-- TIPO -->

                                    <td>

                                        <span class="user-type">
                                            <?= escapar($usuario['tipo_usuario']) ?>
                                        </span>

                                    </td>


                                    <!-- DATA -->

                                    <td>

                                        <?= !empty($usuario['criado_em'])
                                            ? date(
                                                'd/m/Y H:i',
                                                strtotime($usuario['criado_em'])
                                            )
                                            : 'Não informado'
                                        ?>

                                    </td>


                                    <!-- EXCLUIR -->

                                    <td>

                                        <form
                                            action="excluir-usuario.php"
                                            method="POST"
                                            onsubmit="return confirm('Deseja realmente excluir esta conta e seu perfil associado?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $usuario['id'] ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= escapar($_SESSION['csrf_token']) ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="delete-button"
                                            >

                                                <i class="fa-solid fa-trash"></i>

                                                Excluir

                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="6" class="empty-state">

                                    <i class="fa-solid fa-users-slash"></i>

                                    <strong>
                                        Nenhum usuário encontrado.
                                    </strong>

                                    <span>
                                        Tente pesquisar por outro nome ou e-mail.
                                    </span>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>


        <!-- RODAPÉ -->

        <footer class="admin-footer">

            © 2026 Novos Talentos. Painel administrativo.

        </footer>

    </main>

</div>

</body>
</html>
