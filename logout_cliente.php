<?php

/**
 * Logout para clientes
 */

session_start();

// Clear client session
unset($_SESSION['client_id']);
unset($_SESSION['client_name']);
unset($_SESSION['client_email']);

// Destroy session if no other data
if (empty($_SESSION)) {
    session_destroy();
}

header('Location: buscar.php');
exit;
