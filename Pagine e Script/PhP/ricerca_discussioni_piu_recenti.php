<?php
	// LO SCRIPT PREVEDE LA RICERCA DI TUTTE QUELLE DISCUSSIONI CHE SI RIFERISCONO ALL'ARTICOLO COINVOLTO IN UNA DETERMINATA PROPOSTA DI VENDITA, LE QUALI SARANNO POI ORDINATE A SECONDA DI QUANDO SONO STATE PUBBLICATE
	// IL PRIMO PASSO DELL'ALGORITMO DI RICERCA CONSISTE NELL'INIZIALIZZARE UN VETTORE VUOTO, IL QUALE VERRÀ POI RIEMPITO MEDIANTE LE COPPIE FORMATE DALL'IDENTIFICATORE DELLA DISCUSSIONE E L'ISTANTE DI TEMPO, ESPRESSO COME SECONDI PASSATI DAL 01/01/1970, IN CUI È STATA AVVIATA
	// COME RIPORTATO ANCHE NELLA PAGINA IN CUI IL CODICE VIENE RICHIAMATO, I GESTORI E L'AMMINISTRATORE, AL CONTRARIO DEGLI ALTRI UTENTI, POTRANNO CONSULTARE ANCHE GLI ELEMENTI MODERATI
	$discussioni_piu_recenti=array();
	
	if(isset($_SESSION["tipo_Utente"]) && ($_SESSION["tipo_Utente"]=="G" || $_SESSION["tipo_Utente"]=="A")) {
		
		// PER OGNI DISCUSSIONE (INERENTE A QUEL DETERMINATO PRODOTTO), VERRÀ CREATA UNA NUOVA COMBINAZIONE, LA QUALE, OLTRE AD ESSERE DESCRITTA COME SOPRA, ANDRÀ FORMARE UN NUOVO ELEMENTO DELL'ARRAY IN QUESTIONE   
		for($i=0; $i<$prodotto->getElementsByTagName("discussione")->length; $i++) {
			$discussione_prodotto=$prodotto->getElementsByTagName("discussione")->item($i);
			
			for($j=0; $j<$discussioni->length; $j++) {
				$discussione=$discussioni->item($j);
				
				if($discussione_prodotto->getAttribute("idDiscussione")==$discussione->getAttribute("id")) {
					$data_ora_pubblicazione=strtotime($discussione->getAttribute("dataOraIssue"));
					
					// AL FINE DI REGISTRARE CORRETTAMENTE L'IDENTIFICATORE DELLA DISCUSSIONE, È STATO NECESSARIO TRATTARLO COME SE FOSSE UNA STRINGA. IN PARTICOLARE, I DUE CARATTERI DELIMITATORI DEL VALORE EFFETTIVO SARANNO RAPPRESENTATI DA DEI SINGOLI APICI
					$discussioni_piu_recenti=array_merge($discussioni_piu_recenti, array("'".$discussione->getAttribute("id")."'" => $data_ora_pubblicazione));
				}
			}
		}
	}
	else {
		// ***
		for($i=0; $i<$prodotto->getElementsByTagName("discussione")->length; $i++) {
			$discussione_prodotto=$prodotto->getElementsByTagName("discussione")->item($i);
			
			for($j=0; $j<$discussioni->length; $j++) {
				$discussione=$discussioni->item($j);
				
				// INOLTRE, LE DISCUSSIONI CHE SI ANDRANNO A CONSIDERARE SARANNO SOLO ED ESCLUSIVAMENTE QUELLE CHE NON SONO STATE MODERATE
				if($discussione_prodotto->getAttribute("idDiscussione")==$discussione->getAttribute("id") && $discussione->getAttribute("moderata")=="No") {
					$data_ora_pubblicazione=strtotime($discussione->getAttribute("dataOraIssue"));
					
					// ***
					$discussioni_piu_recenti=array_merge($discussioni_piu_recenti, array("'".$discussione->getAttribute("id")."'" => $data_ora_pubblicazione));
				}
			}
		}
	}
	
	// UNA VOLTA INDIVIDUATE E MEMORIZZATE LE VARIE INFORMAZIONI DI INTERESSE, SI PROCEDE CON L'ORDINAMENTO DECRESCENTE IN FUNZIONE DEL CONTENUTO (ISTANTE DI TEMPO, ESPRESSO IN SECONDI TRAMITE IL METODO strtotime, IN CUI È STATA AVVIATA) DELLE VARIE COMPONENTI DEL VETTORE TRAMITE LA FUNZIONE arsort(...), LA QUALE, SPOSTANDO OPPORTUNAMENTE ANCHE LE CHIAVI, CONSENTIÀ DI PRESERVARE TUTTE LE COMBINAZIONI 
	arsort($discussioni_piu_recenti);	
?>