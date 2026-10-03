<?php
// Demonstração do módulo "Alterar senha" no navegador.
// Abra: http://localhost:8000/modulo-alterar-senha/index.php
// Usa um repositório em memória: nada é gravado no banco.
require_once __DIR__ . '/autoload.php';

const SENHA_ATUAL_DEMO = 'senha123';

function h(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

function novoUsuario(string $tipo): Usuario
{
    $hash = password_hash(SENHA_ATUAL_DEMO, PASSWORD_DEFAULT);

    if ($tipo === 'administrador') {
        return new Administrador(1, 'Carlos Admin', 'carlos@exemplo.com', $hash, Administrador::NIVEL_GERENTE);
    }

    return new UsuarioComum(1, 'Ju Teste', 'ju@exemplo.com', $hash, 120);
}

// Cada cenário cria um usuário novo, para que um não interfira no outro.
function executarCenario(array $cenario): array
{
    $usuario = novoUsuario($cenario['tipo']);
    $repositorio = new UsuarioRepositorioMemoria();
    $repositorio->adicionar($usuario);
    $hashAntes = $usuario->getSenhaHash();

    $alterador = new AlteradorDeSenha($repositorio);

    $resultado = ['status' => 'sucesso', 'mensagem' => 'Senha alterada com sucesso.', 'excecao' => null];

    try {
        $alterador->alterar($usuario->getId(), $cenario['atual'], $cenario['nova'], $cenario['confirmacao']);
    } catch (AlteracaoSenhaException $e) {
        $resultado = ['status' => $e->getStatus(), 'mensagem' => $e->getMessage(), 'excecao' => get_class($e)];
    }

    $resultado['classe'] = get_class($usuario);
    $resultado['hashMudou'] = $usuario->getSenhaHash() !== $hashAntes;
    $resultado['passou'] = $resultado['status'] === $cenario['esperado'];

    return $resultado;
}

// Tenta uma alteração por um setter e diz se foi aceita ou recusada.
function tentarAlterar(callable $acao): string
{
    try {
        $acao();
        return 'aceito';
    } catch (InvalidArgumentException $e) {
        return 'recusado: ' . $e->getMessage();
    }
}

// Tenta ler um atributo direto, de fora da classe. O PHP bloqueia com um Error.
function acessoDireto(Usuario $usuario, string $atributo): string
{
    try {
        $valor = $usuario->$atributo;
        return 'acesso permitido (não deveria), valor: ' . (string) $valor;
    } catch (Error $e) {
        return $e->getMessage();
    }
}

$cenarios = [
    [
        'titulo' => 'Troca válida (usuário comum)',
        'tipo' => 'comum',
        'atual' => SENHA_ATUAL_DEMO,
        'nova' => 'NovaSenha#2026',
        'novaExibida' => 'NovaSenha#2026',
        'confirmacao' => 'NovaSenha#2026',
        'esperado' => 'sucesso',
    ],
    [
        'titulo' => 'Troca válida (administrador)',
        'tipo' => 'administrador',
        'atual' => SENHA_ATUAL_DEMO,
        'nova' => 'NovaSenha#2026',
        'novaExibida' => 'NovaSenha#2026',
        'confirmacao' => 'NovaSenha#2026',
        'esperado' => 'sucesso',
    ],
    [
        'titulo' => 'Senha atual incorreta',
        'tipo' => 'comum',
        'atual' => 'senhaerrada',
        'nova' => 'NovaSenha#2026',
        'novaExibida' => 'NovaSenha#2026',
        'confirmacao' => 'NovaSenha#2026',
        'esperado' => 'senha_incorreta',
    ],
    [
        'titulo' => 'Confirmação diferente',
        'tipo' => 'comum',
        'atual' => SENHA_ATUAL_DEMO,
        'nova' => 'NovaSenha#2026',
        'novaExibida' => 'NovaSenha#2026',
        'confirmacao' => 'OutraSenha#2026',
        'esperado' => 'erro_coincidencia',
    ],
    [
        'titulo' => 'Nova senha curta demais',
        'tipo' => 'comum',
        'atual' => SENHA_ATUAL_DEMO,
        'nova' => 'abc12',
        'novaExibida' => 'abc12',
        'confirmacao' => 'abc12',
        'esperado' => 'erro_senha_curta',
    ],
    [
        'titulo' => 'Nova senha longa demais',
        'tipo' => 'comum',
        'atual' => SENHA_ATUAL_DEMO,
        'nova' => str_repeat('a', 73),
        'novaExibida' => '(73 vezes a letra "a")',
        'confirmacao' => str_repeat('a', 73),
        'esperado' => 'erro_senha_longa',
    ],
    [
        'titulo' => 'Nova senha igual à atual',
        'tipo' => 'comum',
        'atual' => SENHA_ATUAL_DEMO,
        'nova' => SENHA_ATUAL_DEMO,
        'novaExibida' => SENHA_ATUAL_DEMO,
        'confirmacao' => SENHA_ATUAL_DEMO,
        'esperado' => 'erro_senha_igual',
    ],
];

$resultados = array_map('executarCenario', $cenarios);
$passaram = count(array_filter($resultados, function ($r) {
    return $r['passou'];
}));

$classesDeErro = [
    'ConfirmacaoDiferenteException',
    'SenhaAtualIncorretaException',
    'NovaSenhaIgualException',
    'SenhaFracaException',
];

// --- Seção: herança e polimorfismo ---
$hashDemo = password_hash(SENHA_ATUAL_DEMO, PASSWORD_DEFAULT);

$comum = new UsuarioComum(1, 'Ju Teste', 'ju@exemplo.com', $hashDemo, 120);
$comum->adicionarPontos(30);

$admin = new Administrador(2, 'Carlos Admin', 'carlos@exemplo.com', $hashDemo, Administrador::NIVEL_GERENTE);

$usuariosDemo = [$comum, $admin];

// --- Seção: getters, setters e visibilidade ---
$exemplo = new UsuarioComum(3, 'Ju Teste', 'ju@exemplo.com', $hashDemo, 120);
$nomeAntes = $exemplo->getNome();
$exemplo->setNome('  Júlia Souza  ');
$nomeDepois = $exemplo->getNome();

$respostaEmail = tentarAlterar(function () use ($exemplo) {
    $exemplo->setEmail('isso-nao-e-email');
});
$respostaPontos = tentarAlterar(function () use ($exemplo) {
    $exemplo->setPontosGamificacao(-5);
});

$acessos = [
    'nome' => ['visibilidade' => 'protected', 'mensagem' => acessoDireto($exemplo, 'nome')],
    'senhaHash' => ['visibilidade' => 'private', 'mensagem' => acessoDireto($exemplo, 'senhaHash')],
    'pontosGamificacao' => ['visibilidade' => 'private', 'mensagem' => acessoDireto($exemplo, 'pontosGamificacao')],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymUp - Demonstração: Alterar senha (POO)</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .cenario { margin-bottom: 14px; }
        .cenario p { margin: 6px 0; font-size: .88rem; color: var(--texto-2); }
        .cenario code { color: var(--acento); }
        .cenario .alerta-feedback { margin: 10px 0 0; text-align: left; }
        .cenario hr { border: none; border-top: 1px solid var(--borda); margin: 12px 0; }
    </style>
</head>

<body>
    <div class="app-container">
        <header class="app-header dashboard-header">
            <div class="logo-container">
                <h1>GymUp</h1>
            </div>
            <p>Demonstração do módulo Alterar senha (POO)</p>
        </header>

        <main class="app-content">

            <div class="search-card cenario">
                <h3>Resumo</h3>
                <p>Cenários de troca de senha com o resultado esperado: <strong><?= $passaram ?> de <?= count($cenarios) ?></strong></p>
                <p>Cada cenário usa objetos novos e o <code>UsuarioRepositorioMemoria</code>, então nada é gravado no banco.</p>
            </div>

            <div class="search-card cenario">
                <h3>Herança e polimorfismo</h3>
                <p><code>Usuario</code> é a classe base. <code>UsuarioComum</code> e <code>Administrador</code> herdam dela e têm atributos próprios.</p>
                <?php foreach ($usuariosDemo as $u): ?>
                    <hr>
                    <p><code><?= h(get_class($u)) ?></code> herda de <code><?= h(get_parent_class($u)) ?></code></p>
                    <p>É um <code>Usuario</code>? <strong><?= $u instanceof Usuario ? 'Sim' : 'Não' ?></strong></p>
                    <p><code>getTipo()</code> (sobrescrito): <strong><?= h($u->getTipo()) ?></strong></p>
                    <p><code>descrever()</code> (sobrescrito): <strong><?= h($u->descrever()) ?></strong></p>
                    <?php if ($u instanceof UsuarioComum): ?>
                        <p>Atributo específico <code>pontosGamificacao</code> (120 + 30 depois de <code>adicionarPontos(30)</code>): <strong><?= $u->getPontosGamificacao() ?></strong></p>
                    <?php elseif ($u instanceof Administrador): ?>
                        <p>Atributo específico <code>nivelAcesso</code>: <strong><?= $u->getNivelAcesso() ?></strong></p>
                        <p>Método próprio <code>podeGerenciarAcademias()</code>: <strong><?= $u->podeGerenciarAcademias() ? 'Sim' : 'Não' ?></strong></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="search-card cenario">
                <h3>Getters, setters e visibilidade</h3>
                <p>Nome antes de <code>setNome('  Júlia Souza  ')</code>: <strong><?= h($nomeAntes) ?></strong></p>
                <p>Nome depois (o setter limpa os espaços): <strong><?= h($nomeDepois) ?></strong></p>
                <p><code>setEmail('isso-nao-e-email')</code>: <strong><?= h($respostaEmail) ?></strong></p>
                <p><code>setPontosGamificacao(-5)</code>: <strong><?= h($respostaPontos) ?></strong></p>
                <hr>
                <p>Acesso direto aos atributos, de fora da classe, é bloqueado:</p>
                <?php foreach ($acessos as $atributo => $info): ?>
                    <p><code>$usuario-&gt;<?= h($atributo) ?></code> (<?= h($info['visibilidade']) ?>): <strong><?= h($info['mensagem']) ?></strong></p>
                <?php endforeach; ?>
            </div>

            <?php foreach ($cenarios as $i => $cenario): ?>
                <?php $r = $resultados[$i]; ?>
                <div class="search-card cenario">
                    <h3><?= h($cenario['titulo']) ?></h3>
                    <p>Objeto usado: <code><?= h($r['classe']) ?></code></p>
                    <p>Senha atual informada: <code><?= h($cenario['atual']) ?></code></p>
                    <p>Nova senha: <code><?= h($cenario['novaExibida']) ?></code></p>
                    <p>Resultado esperado: <code><?= h($cenario['esperado']) ?></code></p>

                    <div class="alerta-feedback <?= $r['status'] === 'sucesso' ? 'sucesso' : 'erro' ?>">
                        <?= h($r['mensagem']) ?>
                    </div>

                    <p>Status devolvido: <code><?= h($r['status']) ?></code></p>
                    <?php if ($r['excecao'] !== null): ?>
                        <p>Exceção lançada: <code><?= h($r['excecao']) ?></code></p>
                    <?php endif; ?>
                    <p>O hash da senha mudou? <strong><?= $r['hashMudou'] ? 'Sim' : 'Não' ?></strong></p>
                    <p>Teste: <strong><?= $r['passou'] ? '✔ passou' : '✘ falhou' ?></strong></p>
                </div>
            <?php endforeach; ?>

            <div class="search-card cenario">
                <h3>Herança: classes de erro</h3>
                <p>Todas as exceções do módulo herdam de <code>AlteracaoSenhaException</code>:</p>
                <?php foreach ($classesDeErro as $classe): ?>
                    <p><code><?= h($classe) ?></code> → <code><?= h(get_parent_class($classe)) ?></code></p>
                <?php endforeach; ?>
                <p><code>AlteracaoSenhaException</code> → <code><?= h(get_parent_class('AlteracaoSenhaException')) ?></code></p>
            </div>

        </main>
    </div>
</body>

</html>