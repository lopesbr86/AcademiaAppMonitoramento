<?php
// A nova senha não atende às regras de tamanho.
// Use SenhaFracaException::curta(6) ou SenhaFracaException::longa(72).
class SenhaFracaException extends AlteracaoSenhaException
{
    public static function curta(int $minimo): self
    {
        return new self("A nova senha deve ter pelo menos $minimo caracteres.", 'erro_senha_curta');
    }

    public static function longa(int $maximo): self
    {
        return new self("A nova senha é longa demais (máximo de $maximo caracteres).", 'erro_senha_longa');
    }
}
