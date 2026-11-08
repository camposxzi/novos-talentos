
<?php
session_start();

unset($_SESSION['admin_logado']);

session_regenerate_id(true);

header('Location: login.php');
exit;
