<?php
// CLASSE BASE. Representa uma linha da tabela Usuario.
//
// Visibilidade dos atributos:
//   protected -> id, nome, email: as classes filhas também enxergam.
//   private   -> senhaHash: só a própria classe Usuario mexe. A senha nunca fica em texto puro.
// Os métodos são public, e é por eles que o resto do sistema conversa com o objeto.
class Usuario
{
    protected int $id;
    protected string $nome;
    protected string $email;
    private string $senhaHash;

    public function __construct(int $id, string $nome, string $email, string $senhaHash)
    {
        // Os dados vêm do banco, então são atribuídos direto, sem validar de novo.
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->senhaHash = $senhaHash;
    }

    // O id não tem setter: a identidade do usuário não muda.
    public function getId(): int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $nome = trim($nome);

        if ($nome === '') {
            throw new InvalidArgumentException('O nome não pode ficar vazio.');
        }

        if (mb_strlen($nome) > 100) {
            throw new InvalidArgumentException('O nome pode ter no máximo 100 caracteres.');
        }

        $this->nome = $nome;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $email = trim($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
            throw new InvalidArgumentException('E-mail inválido.');
        }

        $this->email = $email;
    }

    // A senha também não tem setter comum: só definirNovaSenha() grava, e já como hash.
    // O getter existe para o repositório conseguir salvar o hash no banco.
    public function getSenhaHash(): string
    {
        return $this->senhaHash;
    }

    public function verificarSenha(string $senha): bool
    {
        return password_verify($senha, $this->senhaHash);
    }

    public function definirNovaSenha(string $senha): void
    {
        $this->senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    }

    // As classes filhas sobrescrevem (override) estes dois métodos.
    public function getTipo(): string
    {
        return 'Usuário';
    }

    public function descrever(): string
    {
        return $this->nome . ' (' . $this->email . ') - ' . $this->getTipo();
    }
}