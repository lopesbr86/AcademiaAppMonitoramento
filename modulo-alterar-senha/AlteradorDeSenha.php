<?php
// Regra de negócio da troca de senha.
// Se algo estiver errado, lança uma exceção filha de AlteracaoSenhaException.
class AlteradorDeSenha
{
    private const TAMANHO_MINIMO = 6;
    private const TAMANHO_MAXIMO_BYTES = 72; // limite do bcrypt usado pelo password_hash

    private UsuarioRepositorioInterface $repositorio;

    public function __construct(UsuarioRepositorioInterface $repositorio)
    {
        $this->repositorio = $repositorio;
    }

    public function alterar(int $idUsuario, string $senhaAtual, string $novaSenha, string $confirmacao): void
    {
        if ($novaSenha !== $confirmacao) {
            throw new ConfirmacaoDiferenteException();
        }

        if (mb_strlen($novaSenha) < self::TAMANHO_MINIMO) {
            throw SenhaFracaException::curta(self::TAMANHO_MINIMO);
        }

        if (strlen($novaSenha) > self::TAMANHO_MAXIMO_BYTES) {
            throw SenhaFracaException::longa(self::TAMANHO_MAXIMO_BYTES);
        }

        $usuario = $this->repositorio->buscarPorId($idUsuario);

        // Usuário inexistente e senha errada geram o mesmo erro, para não revelar nada a quem tenta adivinhar.
        if ($usuario === null || !$usuario->verificarSenha($senhaAtual)) {
            throw new SenhaAtualIncorretaException();
        }

        if ($usuario->verificarSenha($novaSenha)) {
            throw new NovaSenhaIgualException();
        }

        $usuario->definirNovaSenha($novaSenha);
        $this->repositorio->salvarSenha($usuario);
    }
}
