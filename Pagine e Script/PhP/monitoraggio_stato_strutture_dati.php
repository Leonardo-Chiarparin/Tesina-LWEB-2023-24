<?php
	// LO SCRIPT PERMETTE DI DETERMINARE SE LE STRUTTURE DATI D'INTERESSE ESISTONO E PRESENTANO I VALORI DI BASE INDICATI ALL'INTERNO DEL FILE D'INSTALLAZIONE
	if(file_exists("../../XML/Acquisti_Clienti.xml") && file_exists("../../XML/Carrelli_Clienti.xml") && file_exists("../../XML/Categorie_Libri.xml") && file_exists("../../XML/Discussioni.xml") && file_exists("../../XML/Domande_Assistenza.xml") && file_exists("../../XML/FAQ.xml") && file_exists("../../XML/Generi_Videogiochi.xml") && file_exists("../../XML/Offerte.xml") && file_exists("../../XML/Piattaforme_Videogiochi.xml") && file_exists("../../XML/Prodotti.xml") && file_exists("../../XML/Recensioni_Prodotti.xml") && file_exists("../../XML/Richieste_Crediti.xml") && file_exists("../../XML/Riduzioni_Prezzi.xml") && file_exists("../../XML/Segnalazioni.xml") && file_exists("../../XML/Tariffe_Sconti.xml")) {
		
		// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
		require_once("./apertura_file_acquisti.php");
		require_once("./apertura_file_carrelli.php");
		require_once("./apertura_file_categorie_libri.php");
		require_once("./apertura_file_discussioni.php");
		require_once("./apertura_file_domande_assistenza.php");
		require_once("./apertura_file_faq.php");
		require_once("./apertura_file_generi_videogiochi.php");
		require_once("./apertura_file_offerte.php");
		require_once("./apertura_file_piattaforme_videogiochi.php");
		require_once("./apertura_file_prodotti.php");
		require_once("./apertura_file_recensioni.php");
		require_once("./apertura_file_richieste_crediti.php");
		require_once("./apertura_file_riduzioni.php");
		require_once("./apertura_file_segnalazioni.php");
		require_once("./apertura_file_tariffe.php");
		
		if(!($acquisti->length>=0 && $carrelli->length>=0 && $categorie->length>=8 && $discussioni->length>=0 && $domande->length>=0 && $faq->length>=0 && $generi->length>=11 && $offerte->length>=0 && $piattaforme->length>=4 && $prodotti->length>=1 && $recensioni->length>=0 && $richieste->length>=0 && $riduzioni->length>=0 && $segnalazioni->length>=0 && $tariffe->length==4))
			header("Location: install.php");
	}
	else
		header("Location: install.php");
?>