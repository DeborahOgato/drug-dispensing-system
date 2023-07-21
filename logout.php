<?php
session_start();
session_destroy();
// Redirect the user to index.html
header('Location: index.html');
exit;
?>
