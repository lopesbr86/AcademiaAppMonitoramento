<?php
// A nova senha e a confirmação digitadas são diferentes.
class ConfirmacaoDiferenteException extends AlteracaoSenhaException
{
    public function __construct()
    {
        parent::__construct('A nova senha e a confirmação não coincidem.', 'erro_coincidencia');
    }
}
