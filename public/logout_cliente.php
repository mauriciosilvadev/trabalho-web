<?php

/**
 * Logout para clientes
 */

session_start();

// Clear client session
unset($_SESSION['client_id']);
unset($_SESSION['client_name']);
unset($_SESSION['client_email']);

// Clear cart data
unset($_SESSION['cart']);
unset($_SESSION['checkout_client_id']);

// Destroy session if no other data
if (empty($_SESSION)) {
    session_destroy();
}

// Redirect with a script to clear localStorage cart too
?>
<!DOCTYPE html>
<html>
<head>
    <script>
        // Clear cart from localStorage
        localStorage.removeItem('service_cart');
        // Redirect to search page
        window.location.href = 'buscar.php';
    </script>
</head>
<body>
    <p>Redirecionando...</p>
</body>
</html>
