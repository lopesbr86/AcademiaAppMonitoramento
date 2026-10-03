<?php
// Carrega automaticamente cada classe do módulo a partir do nome do arquivo.
spl_autoload_register(function (string $classe): void {
    $arquivo = __DIR__ . '/' . $classe . '.php';

    if (is_file($arquivo)) {
        require_once $arquivo;
    }
});
