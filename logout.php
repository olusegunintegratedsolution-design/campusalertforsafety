<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * User Logout Handler
 */

require_once __DIR__ . '/includes/auth.php';

logout_user();
set_flash('info', 'You have been securely signed out of the Campus Safety System.');
header('Location: ' . BASE_URL . '/login.php');
exit;
