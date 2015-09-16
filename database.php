<?php 
// configuration
$dbtype		= "mysql";
$dbhost 	= "localhost";
$dbname		= "atomicfl_opium";
$dbuser		= "atomicfl_opiumus";
$dbpass		= "3,_W.AG0I}Ey";

$conn = new PDO("mysql:host=$dbhost;dbname=$dbname",$dbuser,$dbpass);

// set the PDO error mode to exception
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
