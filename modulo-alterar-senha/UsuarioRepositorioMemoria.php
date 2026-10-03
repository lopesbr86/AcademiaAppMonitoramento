<?php
// Repositório "de mentira": guarda os usuários em um array, dentro da memória do PHP.
// Serve para demonstrar o módulo sem tocar no banco real.
// Funciona no lugar do UsuarioRepositorio porque os dois seguem o mesmo contrato (a interface).
class UsuarioRepositorioMemoria implements UsuarioRepositorioInterface
{
    private array $usuarios = [];

    public function adicionar(Usuario $usuario): void
    {
        $this->usuarios[$usuario->getId()] = $usuario;
    }

    public function buscarPorId(int $id): ?Usuario
    {
        return $this->usuarios[$id] ?? null;
    }

    public function salvarSenha(Usuario $usuario): void
    {
        $this->usuarios[$usuario->getId()] = $usuario;
    }
}