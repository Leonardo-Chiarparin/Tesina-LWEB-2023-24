<?php
	// LO SCRIPT PERMETTE DI RIMUOVERE AUTOMATICAMENTE GLI SCONTI DALLE OFFERTE, PRESENTI NEL CATALOGO E/O ALL'INTERNO DEI VARI CARRELLI, UNA VOLTA TERMINATO IL PERIODO DI TEMPO UTILE PER LA LORO APPLICAZIONE
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_carrelli.php");
	
	// PER EVITARE PROBLEMATICHE LEGATE AL MECCANISMO DI CUI SOTTO, BISOGNA VALUTARE SE SONO EFFETTIVAMENTE PRESENTI DELLE OFFERTE ALL'INTERNO DELLA RELATIVA STRUTTURA DATI, COSÌ DA POTER EVENTUALMENTE RETTIFICARE IL LORO CONTENUTO
	require_once("./calcolo_offerte.php");
	
	if($num_offerte!=0) {
		
		for($i=0; $i<$offerte->length; $i++){
			$offerta=$offerte->item($i);
		
			if($offerta->getElementsByTagName("sconto")->length!=0) {
				if(strtotime(date("Y-m-d"))>strtotime($offerta->getElementsByTagName("sconto")->item(0)->firstChild->getAttribute("fineApplicazione")))
					$offerta->removeChild($offerta->getElementsByTagName("sconto")->item(0));
			}
		}
		
		// IN CONCLUSIONE, BISOGNA VALIDARE LA COMPOSIZIONE DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
		if($docOfferte->schemaValidate("../../XML/Schema/Offerte.xsd")){
			
			// PER UNA STAMPA OTTIMALE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
			$docOfferte->preserveWhiteSpace = false;
			$docOfferte->formatOutput = true;
			$docOfferte->save("../../XML/Offerte.xml");
			
		}
		else {
			// LA MANCATA VALIDAZIONE DEL FILE XML PORTERÀ ALLA CREAZIONE DI UNA VARIABILE FLAG AL SOLO SCOPO DI NOTIFICARE AI GESTORI E ALL'AMMINISTRATORE DEL SITO QUANTO ACCADUTO
			if(isset($_SESSION["id_Utente"]) && ($_SESSION["tipo_Utente"]=="A" || $_SESSION["tipo_Utente"]=="G")) {
				// ***
				setcookie("errore_Validazione", true);
				
				header("Location: index.php");
			}
		}
	}
	
	// ***
	if($carrelli->length!=0) {
		
		for($i=0; $i<$carrelli->length; $i++){
			
			$carrello=$carrelli->item($i);
			
			for($j=0; $j<$carrello->getElementsByTagName("offerta")->length; $j++) {
				
				$offerta=$carrello->getElementsByTagName("offerta")->item($j);
				
				if($offerta->getElementsByTagName("sconto")->length!=0) {
					if(strtotime(date("Y-m-d"))>strtotime($offerta->getElementsByTagName("sconto")->item(0)->firstChild->getAttribute("fineApplicazione")))
						$offerta->removeChild($offerta->getElementsByTagName("sconto")->item(0));
				}
			
			}
		}
		
		// IN CONCLUSIONE, BISOGNA VALIDARE LA COMPOSIZIONE DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
		if($docCarrelli->schemaValidate("../../XML/Schema/Carrelli_Clienti.xsd")){
			
			// PER UNA STAMPA OTTIMALE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
			$docCarrelli->preserveWhiteSpace = false;
			$docCarrelli->formatOutput = true;
			$docCarrelli->save("../../XML/Carrelli_Clienti.xml");
			
		}
		else {
			// LA MANCATA VALIDAZIONE DEL FILE XML PORTERÀ ALLA CREAZIONE DI UNA VARIABILE FLAG AL SOLO SCOPO DI NOTIFICARE AI GESTORI E ALL'AMMINISTRATORE DEL SITO QUANTO ACCADUTO
			if(isset($_SESSION["id_Utente"]) && ($_SESSION["tipo_Utente"]=="A" || $_SESSION["tipo_Utente"]=="G")) {
				// ***
				setcookie("errore_Validazione", true);
				
				header("Location: index.php");
			}
		}
	}
?>