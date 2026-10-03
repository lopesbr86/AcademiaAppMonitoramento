<?php
// Classe base de todos os erros da alteração de senha.
// Guarda o "status" que vai na URL do redirecionamento (ex.: erro_coincidencia).
class AlteracaoSenhaException extends Exception
{
    private string $status;

    public function __construct(string $mensagem, string $status)
    {
        parent::__construct($mensagem);
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
