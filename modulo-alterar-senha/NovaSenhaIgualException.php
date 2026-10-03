<?php
// A nova senha é igual à senha atual (trocar por ela mesma não faz sentido).
class NovaSenhaIgualException extends AlteracaoSenhaException
{
    public function __construct()
    {
        parent::__construct('A nova senha deve ser diferente da senha atual.', 'erro_senha_igual');
    }
}
