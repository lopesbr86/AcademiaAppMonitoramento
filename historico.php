<?php
require_once 'conexao.php';
session_start();

// Garante que o usuário está logado (ajuste o nome da session conforme o seu sistema de login)
$idUsuario = $_SESSION['idUsuario'] ?? $_SESSION['usuario_id'] ?? null;

$historicos = [];

if ($idUsuario) {
    try {
        // Consulta usando as tabelas e colunas reais do seu Workbench
        $sql = "SELECT r.*, a.nome AS nome_academia, a.cidade 
                FROM RegistroLotacao r 
                JOIN Academia a ON r.idAcademia = a.idAcademia 
                WHERE r.idUsuario = :idUsuario 
                ORDER BY r.dataHora DESC 
                LIMIT 15";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['idUsuario' => $idUsuario]);
        $historicos = $stmt->fetchAll();
    } catch (PDOException $e) {
        $historicos = [];
    }
}

// Função para traduzir o nível de lotação (ajuste os números se necessário conforme o padrão do seu sistema)
function formatarNivelLotacao($nivel) {
    switch ((int)$nivel) {
        case 1: return ['Tranquila', 'verde'];
        case 2: return ['Moderada', 'amarelo'];
        case 3: return ['Lotada', 'vermelho'];
        default: return ['Normal', 'verde'];
    }
}

// O banco (Aiven) grava em UTC. Aqui só preparamos a data em formato ISO (UTC);
// quem converte para o fuso de cada usuário é o JavaScript, no navegador dele.
function dataIsoUtc($dataHora) {
    $data = new DateTime($dataHora, new DateTimeZone('UTC'));
    return $data->format('Y-m-d\TH:i:s\Z');
}

// Texto de reserva (horário de Brasília), usado só se o JavaScript não rodar
function formatarDataHora($dataHora) {
    $data = new DateTime($dataHora, new DateTimeZone('UTC'));
    $data->setTimezone(new DateTimeZone('America/Sao_Paulo'));
    return $data->format('d/m/Y H:i');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="icon.png">
    <meta name="theme-color" content="#0f172a">
    <link rel="manifest" href="manifest.json">
    <title>GymUp - Histórico</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="app-container">
        <header class="app-header dashboard-header">
            <div class="logo-container">
                <h1>GymUp</h1>
            </div>
            <p>Suas consultas e votos</p>
        </header>

        <main class="app-content">
            <div class="search-card">
                <h3>Suas últimas contribuições</h3>

                <?php if (!$idUsuario): ?>
                    <p style="text-align: center; color: #f87171; padding: 20px 0;">
                        Você precisa estar <a href="login.html" style="color: #60a5fa;">logado</a> para ver seu histórico.
                    </p>
                <?php elseif (!empty($historicos)): ?>
                    <?php foreach ($historicos as $item): ?>
                        <?php [$textoStatus, $classeCor] = formatarNivelLotacao($item['nivelLotacao']); ?>
                        <div class="historico-item" style="margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <div class="historico-topo" style="display: flex; justify-content: space-between; align-items: center;">
                                <strong class="historico-titulo">
                                    <?= htmlspecialchars($item['nome_academia']) ?> - <?= htmlspecialchars($item['cidade'] ?? '') ?>
                                </strong>
                                <span class="status-badge <?= $classeCor ?>">
                                    <?= $textoStatus ?>
                                </span>
                            </div>
                            <span class="historico-data" style="font-size: 0.85rem; color: #94a3b8;">
                                <time class="data-local" datetime="<?= dataIsoUtc($item['dataHora']) ?>"><?= formatarDataHora($item['dataHora']) ?></time>
                                <?php if ($item['presencaValidada']): ?>
                                    <span style="color: #34d399; margin-left: 8px;">✓ Validado</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #94a3b8; padding: 20px 0;">
                        Nenhuma contribuição encontrada ainda.
                    </p>
                <?php endif; ?>
            </div>
        </main>

        <nav class="bottom-nav">
            <a href="index.php" class="nav-item">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 11l9-8 9 8" />
                    <path d="M5 10v10h14V10" />
                </svg>
                <span>Início</span>
            </a>
            <a href="historico.php" class="nav-item active">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 7v5l3 2" />
                </svg>
                <span>Histórico</span>
            </a>
            <a href="treino.html" class="nav-item">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6.5 6.5v11M17.5 6.5v11M3 9v6M21 9v6M6.5 12h11" />
                </svg>
                <span>Treino</span>
            </a>
            <a href="perfil.html" class="nav-item">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M4 21c0-4 4-6 8-6s8 2 8 6" />
                </svg>
                <span>Perfil</span>
            </a>
        </nav>
    </div>
    <script src="pwa.js"></script>
    <script>
        // Mostra cada data no fuso horário do aparelho do usuário
        document.querySelectorAll('time.data-local').forEach(function (el) {
            var d = new Date(el.getAttribute('datetime'));
            if (isNaN(d)) return;
            el.textContent = d.toLocaleString('pt-BR', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            }).replace(',', '');
        });
    </script>
</body>

</html>