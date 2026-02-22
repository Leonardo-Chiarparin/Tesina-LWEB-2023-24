<?php
	// LO SCRIPT PREVEDE LA RICERCA DI TUTTI GLI INTERVENTI CHE SI RIFERISCONO AD UNA DISCUSSIONE, I QUALI SARANNO POI ORDINATI A SECONDA DI QUANDO SONO STATI PUBBLICATI
	// IL PRIMO PASSO DELL'ALGORITMO DI RICERCA CONSISTE NELL'INIZIALIZZARE UN VETTORE VUOTO, IL QUALE VERRÀ POI RIEMPITO MEDIANTE LE COPPIE FORMATE DALL'IDENTIFICATORE DELLA DISCUSSIONE E L'ISTANTE DI TEMPO, ESPRESSO COME SECONDI PASSATI DAL 01/01/1970, IN CUI È STATA AVVIATA
	// COME RIPORTATO ANCHE NELLA PAGINA IN CUI IL CODICE VIENE RICHIAMATO, I GESTORI E L'AMMINISTRATORE, AL CONTRARIO DEGLI ALTRI UTENTI, POTRANNO CONSULTARE ANCHE GLI ELEMENTI MODERATI
	$interventi_piu_recenti=array();
	
	if(isset($_SESSION["tipo_Utente"]) && ($_SESSION["tipo_Utente"]=="G" || $_SESSION["tipo_Utente"]=="A")) {
		
		// PER OGNI DISCUSSIONE (INERENTE AD UN CERTO PRODOTTO), VERRÀ CREATA UNA NUOVA COMBINAZIONE, LA QUALE, OLTRE AD ESSERE DESCRITTA COME SOPRA, ANDRÀ FORMARE UN NUOVO ELEMENTO DELL'ARRAY IN QUESTIONE   
		for($n=0; $n<$discussione->getElementsByTagName("intervento")->length; $n++) {
			$intervento_discussione=$discussione->getElementsByTagName("intervento")->item($n);
				
			$data_ora_pubblicazione=strtotime($intervento_discussione->getAttribute("dataOraIssue"));
			
			// AL FINE DI REGISTRARE CORRETTAMENTE L'IDENTIFICATORE DELL'INTERVENTO, È STATO NECESSARIO TRATTARLO COME SE FOSSE UNA STRINGA. IN PARTICOLARE, I DUE CARATTERI DELIMITATORI DEL VALORE EFFETTIVO SARANNO RAPPRESENTATI DA DEI SINGOLI APICI
			$interventi_piu_recenti=array_merge($interventi_piu_recenti, array("'".$intervento_discussione->getAttribute("id")."'" => $data_ora_pubblicazione));
		
		}
	}
	else {
		// ***
		for($n=0; $n<$discussione->getElementsByTagName("intervento")->length; $n++) {
			$intervento_discussione=$discussione->getElementsByTagName("intervento")->item($n);
			
			// INOLTRE, GLI INTERVENTI CHE SI ANDRANNO A CONSIDERARE SARANNO SOLO ED ESCLUSIVAMENTE QUELLI CHE NON SONO STATI MODERATI
			if($intervento_discussione->getAttribute("moderato")=="No") {
				$data_ora_pubblicazione=strtotime($intervento_discussione->getAttribute("dataOraIssue"));
				
				// ***
				$interventi_piu_recenti=array_merge($interventi_piu_recenti, array("'".$intervento_discussione->getAttribute("id")."'" => $data_ora_pubblicazione));
			}
		}
	}
	
	// UNA VOLTA INDIVIDUATE E MEMORIZZATE LE VARIE INFORMAZIONI DI INTERESSE, SI PROCEDE CON L'ORDINAMENTO DECRESCENTE IN FUNZIONE DEL CONTENUTO (ISTANTE DI TEMPO, ESPRESSO IN SECONDI TRAMITE IL METODO strtotime, IN CUI È STATA AVVIATA) DELLE VARIE COMPONENTI DEL VETTORE TRAMITE LA FUNZIONE arsort(...), LA QUALE, SPOSTANDO OPPORTUNAMENTE ANCHE LE CHIAVI, CONSENTIÀ DI PRESERVARE TUTTE LE COMBINAZIONI 
	arsort($interventi_piu_recenti);	
?>