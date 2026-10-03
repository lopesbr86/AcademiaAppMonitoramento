<?php
// Implementação que conversa com o MySQL usando PDO.
// O banco tem uma única tabela Usuario, então o repositório devolve sempre um UsuarioComum.
class UsuarioRepositorio implements UsuarioRepositorioInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function buscarPorId(int $id): ?Usuario
    {
        $stmt = $this->pdo->prepare('SELECT idUsuario, nome, email, senha, pontosGamificacao FROM Usuario WHERE idUsuario = ?');
        $stmt->execute([$id]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($linha === false) {
            return null;
        }

        return new UsuarioComum(
            (int) $linha['idUsuario'],
            $linha['nome'],
            $linha['email'],
            $linha['senha'],
            (int) ($linha['pontosGamificacao'] ?? 0)
        );
    }

    public function salvarSenha(Usuario $usuario): void
    {
        $stmt = $this->pdo->prepare('UPDATE Usuario SET senha = ? WHERE idUsuario = ?');
        $stmt->execute([$usuario->getSenhaHash(), $usuario->getId()]);
    }
}