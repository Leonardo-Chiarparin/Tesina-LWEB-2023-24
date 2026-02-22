<?php
	// LO SCRIPT PERMETTE DI EFFETTUARE LA CONNESSIONE CON LA BASE DI DATI SU CUI SI POGGIA L'APPLICATIVO PER INSERIRE, MODIFICARE O REPERIRE TUTTE LE INFORMAZIONI INERENTI AGLI UTENTI CHE INTERAGISCONO CON I VARI SERVIZI MESSI A LORO DISPOSIZIONE
    require_once("./variabili_connessioni.php");
    
	$conn = new mysqli($host,$user,$pass,$db);
    if(mysqli_connect_errno()){
		printf("ERRORE DI CONNESSIONE CON IL DATABASE: %s\n", mysqli_connect_error());
		exit();
    }
?>