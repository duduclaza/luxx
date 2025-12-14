<?php
/**
 * TOTEM LUXX - Logout
 */

if (isLoggedIn()) {
    registrarLog('info', 'auth', 'logout', 'Logout realizado');
}

// Limpar sessão
session_destroy();
session_start();

redirect('/login');
