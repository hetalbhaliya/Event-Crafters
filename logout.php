<?php

session_start();

/* =========================
   DESTROY SESSION
========================= */

$_SESSION = array();

session_destroy();


/* =========================
   REDIRECT TO HOME
========================= */

header("Location: index.php");
exit;

?>