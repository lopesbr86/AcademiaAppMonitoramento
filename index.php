<?php
require_once 'conexao.php';
session_start();

$idUsuario = $_SESSION['idUsuario'] ?? $_SESSION['usuario_id'] ?? null;
$academiaFavorita = null;

if ($idUsuario) {
    try {
        // Busca a academia favorita do usuário logado
        $sql = "SELECT a.*, r.nivelLotacao, r.dataHora 
                FROM Favorito f
                JOIN Academia a ON f.idAcademia = a.idAcademia
                LEFT JOIN RegistroLotacao r ON r.idAcademia = a.idAcademia 
                WHERE f.idUsuario = :idUsuario
                ORDER BY r.dataHora DESC LIMIT 1";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['idUsuario' => $idUsuario]);
        $academiaFavorita = $stmt->fetch();
    } catch (PDOException $e) {
        $academiaFavorita = null;
    }
}

// Função para exibir o status formatado
function formatarStatusLotacao($nivel) {
    switch ((int)$nivel) {
        case 1: return ['Tranquila', 'verde'];
        case 2: return ['Moderada', 'amarelo'];
        case 3: return ['Lotada', 'vermelho'];
        default: return ['Sem dados recentes', 'cinza'];
    }
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
    <title>GymUp - Início</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="app-container">
        <header class="app-header dashboard-header">
            <div class="logo-container">
                <h1>GymUp</h1>
            </div>
            <p>Veja como está a lotação das academias agora</p>
        </header>

        <main class="app-content">
            
            <!-- Seção de Academia Favorita (Destaque) -->
            <?php if ($academiaFavorita): ?>
                <?php [$textoStatus, $classeCor] = formatarStatusLotacao($academiaFavorita['nivelLotacao'] ?? 0); ?>
                <div class="search-card" style="border: 1px solid #3b82f6; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 0.8rem; color: #60a5fa; font-weight: bold;">⭐ SUA ACADEMIA FAVORITA</span>
                        <span class="status-badge <?= $classeCor ?>"><?= $textoStatus ?></span>
                    </div>
                    <h3 style="margin: 0 0 5px 0;"><?= htmlspecialchars($academiaFavorita['nome'] ?? $academiaFavorita['nomeAcademia'] ?? '') ?></h3>
                    <p style="color: #94a3b8; font-size: 0.9rem; margin: 0 0 15px 0;"><?= htmlspecialchars($academiaFavorita['cidade'] ?? '') ?></p>
                    <a href="votar_academia.php?id=<?= $academiaFavorita['idAcademia'] ?>" style="display: block; text-align: center; background: #2563eb; color: white; padding: 8px; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">Ver detalhes / Atualizar</a>
                </div>
            <?php endif; ?>

            <!-- Formulário de Pesquisa Normal de Academias -->
            <div class="search-card">
                <h3>Encontrar academia</h3>
                <form id="form-busca">
                    <label for="cidade">Cidade</label>
                    <input type="text" id="cidade" name="cidade" placeholder="Ex: Santos, Guarujá..." required style="width: 100%; padding: 10px; margin: 8px 0 15px 0; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: white;">

                    <label for="academia">Nome da academia (opcional)</label>
                    <input type="text" id="academia" name="academia" placeholder="Ex: General, Skyfit..." style="width: 100%; padding: 10px; margin: 8px 0 15px 0; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: white;">

                    <button type="submit" style="width: 100%; background: #2563eb; color: white; padding: 12px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Pesquisar lotação</button>
                </form>
            </div>

            <!-- Contentor onde os resultados das academias vão aparecer dinamicamente -->
            <div id="resultados-container" style="margin-top: 20px;"></div>

        </main>

        <nav class="bottom-nav">
            <a href="index.php" class="nav-item active">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 11l9-8 9 8" />
                    <path d="M5 10v10h14V10" />
                </svg>
                <span>Início</span>
            </a>
            <a href="historico.php" class="nav-item">
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
    <script src="script.js"></script>
    <script src="pwa.js"></script>
</body>

</html>