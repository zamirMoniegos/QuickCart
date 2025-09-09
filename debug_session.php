<?php
// File: debug_session.php

// Start the session to read its data
session_start();

echo '<h1>Session Contents</h1>';
echo '<p>This shows what is currently stored in your session. After logging in, you should see "admin_id" listed below.</p>';
echo '<pre>'; // The <pre> tag makes the output readable

// Print the entire $_SESSION array
print_r($_SESSION);

echo '</pre>';
?>