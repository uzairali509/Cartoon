<?php
/**
 * logout.php — Logout handler.
 */

require_once __DIR__ . '/includes/config.php';

// Clear session
logoutUser();

// Redirect to home
header('Location: index.php');
exit;