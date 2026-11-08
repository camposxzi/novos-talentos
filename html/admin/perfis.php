
<?php
require_once __DIR__ . '/verificar-admin.php';
require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$busca = trim($_GET['busca'] ?? '');

$sql = "
    SELECT
        p.id,
        p.nome_completo,
        p.formacao,
        p.area_interesse,
        p.cidade,
        p.estado,
        p.criado_em,
        u.nome_usuario,
        u.email
    FROM perfis_talentos p
    INNER JOIN usuarios u ON u.id = p.usuario_id
";

if ($busca !== '') {
    $sql .= "
        WHERE p.nome_completo ILIKE :nome
        OR u.nome_usuario ILIKE :usuario
        OR u.email ILIKE :email
    ";
}

$sql .= " ORDER BY p.criado_em DESC";

$stmt = $pdo->prepare($sql);

if ($busca !== '') {
    $termo = '%' . $busca . '%';

    $stmt->execute([
        'nome' => $termo,
        'usuario' => $termo,
        'email' => $termo
    ]);
} else {
    $stmt->execute();
}

$perfis = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalPerfis = count($perfis);

function escapar($valor) {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gerenciar Perfis | Administração</title>

    <link rel="stylesheet" href="../css/admin.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<div class="admin-layout">

    <!-- MENU LATERAL -->

    <aside class="sidebar">

        <div class="sidebar-logo">
            <div class="logo-symbol">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <div>
                ADMIN
                <small>PAINEL DE CONTROLE</small>
            </div>
        </div>

        <div class="sidebar-section">

            <span class="sidebar-label">GERENCIAMENTO</span>

            <a href="usuarios.php" class="sidebar-link">
                <i class="fa-solid fa-users"></i>
                <span>Usuários</span>
            </a>

            <a href="perfis.php" class="sidebar-link active">
                <i class="fa-solid fa-id-card"></i>
                <span>Perfis</span>
            </a>

        </div>

        <div class="sidebar-bottom">

            <a href="../index.php" class="sidebar-link">
                <i class="fa-solid fa-house"></i>
                <span>Voltar ao site</span>
            </a>

            <a href="logout.php" class="sidebar-link logout-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Sair do painel</span>
            </a>

        </div>

    </aside>

    <!-- CONTEÚDO PRINCIPAL -->

    <main class="admin-content">

        <!-- TOPO -->

        <header class="topbar">

            <div>
                <span class="topbar-label">ADMINISTRAÇÃO / PERFIS</span>
                <h1>Gerenciar perfis</h1>
            </div>

            <div class="admin-profile">
                <div class="admin-avatar">
                    <i class="fa-solid fa-user-shield"></i>
                </div>

                <div>
                    <strong>Administrador</strong>
                    <span>Acesso administrativo</span>
                </div>
            </div>

        </header>

        <!-- INTRODUÇÃO -->

        <section class="page-intro">

            <h2>Perfis cadastrados</h2>

            <p>
                Visualize, pesquise e gerencie os perfis
                profissionais cadastrados na plataforma.
            </p>

        </section>

        <!-- INDICADOR -->

        <section class="stats-grid">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="fa-solid fa-id-card"></i>
                </div>

                <div>
                    <span>Perfis encontrados</span>
                    <strong><?= $totalPerfis ?></strong>
                </div>

            </div>

        </section>

        <!-- TABELA -->

        <section class="content-panel">

            <div class="panel-heading">

                <div>
                    <h2>Lista de perfis</h2>
                    <p>Gerenciamento dos talentos cadastrados.</p>
                </div>

                <span class="result-count">
                    <?= $totalPerfis ?> resultado(s)
                </span>

            </div>

            <!-- PESQUISA -->

            <form method="GET" class="search-form">

                <div class="search-input">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="search"
                        name="busca"
                        placeholder="Pesquisar por nome, usuário ou e-mail..."
                        value="<?= escapar($busca) ?>"
                    >

                </div>

                <button type="submit" class="search-button">
                    Pesquisar
                </button>

                <?php if ($busca !== ''): ?>

                    <a href="perfis.php" class="clear-search">
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
                            <th>Nome completo</th>
                            <th>Formação</th>
                            <th>Localização</th>
                            <th>Usuário responsável</th>
                            <th>Cadastro</th>
                            <th>Ação</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (count($perfis) > 0): ?>

                            <?php foreach ($perfis as $perfil): ?>

                                <tr>

                                    <td class="user-id">
                                        #<?= (int) $perfil['id'] ?>
                                    </td>

                                    <td>
                                        <div class="user-name">

                                            <div class="user-avatar">
                                                <i class="fa-solid fa-user"></i>
                                            </div>

                                            <div>
                                                <strong>
                                                    <?= escapar($perfil['nome_completo']) ?>
                                                </strong>

                                                <small>
                                                    <?= escapar($perfil['area_interesse'] ?: 'Área não informada') ?>
                                                </small>
                                            </div>

                                        </div>
                                    </td>

                                    <td>
                                        <?= escapar($perfil['formacao'] ?: 'Não informada') ?>
                                    </td>

                                    <td>
                                        <?php
                                        $cidade = $perfil['cidade'] ?? '';
                                        $estado = $perfil['estado'] ?? '';

                                        $localizacao = trim(
                                            $cidade .
                                            (($cidade && $estado) ? ' - ' : '') .
                                            $estado
                                        );
                                        ?>

                                        <?= escapar($localizacao ?: 'Não informada') ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= escapar($perfil['nome_usuario']) ?>
                                        </strong>

                                        <small>
                                            <?= escapar($perfil['email']) ?>
                                        </small>
                                    </td>

                                    <td>
                                        <?= date('d/m/Y', strtotime($perfil['criado_em'])) ?>
                                    </td>

                                    <td>

                                        <form
                                            method="POST"
                                            action="excluir-perfil.php"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este perfil? A conta do usuário será mantida.');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $perfil['id'] ?>"
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
                                <td colspan="7" class="empty-state">

                                    <i class="fa-solid fa-folder-open"></i>

                                    <strong>Nenhum perfil encontrado</strong>

                                    <span>
                                        Não existem perfis correspondentes à pesquisa.
                                    </span>

                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <footer class="admin-footer">
            Novos Talentos · Painel Administrativo
        </footer>

    </main>

</div>

</body>
</html>
