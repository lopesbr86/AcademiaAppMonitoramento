<?php
// A senha atual informada não confere com a gravada no banco.
class SenhaAtualIncorretaException extends AlteracaoSenhaException
{
    public function __construct()
    {
        parent::__construct('A senha atual está incorreta.', 'senha_incorreta');
    }
}
