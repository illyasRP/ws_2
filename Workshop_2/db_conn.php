<?php
$con = mysqli_connect("localhost", "root", "root", "ws_2");
if (mysqli_connect_errno()) {
    die("Échec connexion MySQL: " . mysqli_connect_error());
}

$test = mysqli_query($con, "SELECT 1");
if (!$test) die("Erreur requête test: " . mysqli_error($con));