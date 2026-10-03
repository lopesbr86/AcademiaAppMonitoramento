<?php
// CLASSE FILHA de Usuario.
// Atributo específico: pontosGamificacao (a coluna de mesmo nome da tabela Usuario).
class UsuarioComum extends Usuario
{
    private int $pontosGamificacao;

    public function __construct(int $id, string $nome, string $email, string $senhaHash, int $pontosGamificacao = 0)
    {
        parent::__construct($id, $nome, $email, $senhaHash);
        $this->pontosGamificacao = max(0, $pontosGamificacao);
    }

    public function getPontosGamificacao(): int
    {
        return $this->pontosGamificacao;
    }

    public function setPontosGamificacao(int $pontos): void
    {
        if ($pontos < 0) {
            throw new InvalidArgumentException('Os pontos não podem ser negativos.');
        }

        $this->pontosGamificacao = $pontos;
    }

    public function adicionarPontos(int $pontos): void
    {
        if ($pontos <= 0) {
            throw new InvalidArgumentException('Informe uma quantidade positiva de pontos.');
        }

        $this->pontosGamificacao += $pontos;
    }

    // Sobrescreve o método da classe base.
    public function getTipo(): string
    {
        return 'Usuário comum';
    }

    public function descrever(): string
    {
        // $this->nome e $this->email são protected: a classe filha consegue usar.
        return $this->nome . ' (' . $this->email . ') - ' . $this->getTipo() . ' - ' . $this->pontosGamificacao . ' pontos';
    }
}