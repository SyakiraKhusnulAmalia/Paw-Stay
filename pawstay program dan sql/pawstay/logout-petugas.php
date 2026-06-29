<?php
session_start();
unset($_SESSION['id_petugas'], $_SESSION['nama_petugas']);
header("Location: login-petugas.php");
exit;
