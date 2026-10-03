<?php
// Contrato de quem busca e salva usuários.
// Permite trocar o banco real por outra implementação (ex.: uma em memória, para demonstração).
interface UsuarioRepositorioInterface
{
    public function buscarPorId(int $id): ?Usuario;

    public function salvarSenha(Usuario $usuario): void;
}
