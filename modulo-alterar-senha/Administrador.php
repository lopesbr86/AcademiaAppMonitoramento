<?php
// CLASSE FILHA de Usuario.
// Atributo específico: nivelAcesso (1 = moderador, 2 = gerente, 3 = total).
// Esta classe existe só para modelar e demonstrar a herança: o banco ainda tem uma única tabela Usuario.
class Administrador extends Usuario
{
    public const NIVEL_MODERADOR = 1;
    public const NIVEL_GERENTE = 2;
    public const NIVEL_TOTAL = 3;

    private int $nivelAcesso;

    public function __construct(int $id, string $nome, string $email, string $senhaHash, int $nivelAcesso = self::NIVEL_MODERADOR)
    {
        parent::__construct($id, $nome, $email, $senhaHash);
        $this->nivelAcesso = $nivelAcesso;
    }

    public function getNivelAcesso(): int
    {
        return $this->nivelAcesso;
    }

    public function setNivelAcesso(int $nivel): void
    {
        if ($nivel < self::NIVEL_MODERADOR || $nivel > self::NIVEL_TOTAL) {
            throw new InvalidArgumentException('O nível de acesso deve ser de 1 a 3.');
        }

        $this->nivelAcesso = $nivel;
    }

    // Método próprio desta classe: só administradores com nível 2 ou mais gerenciam academias.
    public function podeGerenciarAcademias(): bool
    {
        return $this->nivelAcesso >= self::NIVEL_GERENTE;
    }

    public function getTipo(): string
    {
        return 'Administrador';
    }

    public function descrever(): string
    {
        return $this->nome . ' (' . $this->email . ') - ' . $this->getTipo() . ' - nível de acesso ' . $this->nivelAcesso;
    }
}