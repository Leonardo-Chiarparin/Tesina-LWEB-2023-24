<?php
	// LO SCRIPT PREVEDE LA RICERCA DI TUTTE QUELLE RECENSIONI CHE SI RIFERISCONO ALL'ARTICOLO COINVOLTO IN UNA DETERMINATA PROPOSTA DI VENDITA, LE QUALI SARANNO POI ORDINATE A SECONDA DI QUANDO SONO STATE PUBBLICATE
	// IL PRIMO PASSO DELL'ALGORITMO DI RICERCA CONSISTE NELL'INIZIALIZZARE UN VETTORE VUOTO, IL QUALE VERRÀ POI RIEMPITO MEDIANTE LE COPPIE FORMATE DALL'IDENTIFICATORE DELLA RECENSIONE E LA DATA DI PUBBLICAZIONE, ESPRESSA COME SECONDI PASSATI DAL 01/01/1970, DI QUEST'ULTIMA
	// COME RIPORTATO ANCHE NELLA PAGINA IN CUI IL CODICE VIENE RICHIAMATO, I GESTORI E L'AMMINISTRATORE, AL CONTRARIO DEGLI ALTRI UTENTI, POTRANNO CONSULTARE ANCHE GLI ELEMENTI MODERATI
	$recensioni_piu_recenti=array();
	
	if(isset($_SESSION["tipo_Utente"]) && ($_SESSION["tipo_Utente"]=="G" || $_SESSION["tipo_Utente"]=="A")) {
		
		// PER OGNI RECENSIONE (INERENTE AD UN CERTO PRODOTTO), VERRÀ CREATA UNA NUOVA COMBINAZIONE, LA QUALE, OLTRE AD ESSERE DESCRITTA COME SOPRA, ANDRÀ FORMARE UN NUOVO ELEMENTO DELL'ARRAY IN QUESTIONE   
		for($i=0; $i<$prodotto->getElementsByTagName("recensione")->length; $i++) {
			$recensione_prodotto=$prodotto->getElementsByTagName("recensione")->item($i);
			
			for($j=0; $j<$recensioni->length; $j++) {
				$recensione=$recensioni->item($j);
				
				if($recensione_prodotto->getAttribute("idRecensione")==$recensione->getAttribute("id")) {
					$data_pubblicazione=strtotime($recensione->getAttribute("dataPubblicazione"));
					
					// AL FINE DI REGISTRARE CORRETTAMENTE L'IDENTIFICATORE DELLA RECENSIONE, È STATO NECESSARIO TRATTARLO COME SE FOSSE UNA STRINGA. IN PARTICOLARE, I DUE CARATTERI DELIMITATORI DEL VALORE EFFETTIVO SARANNO RAPPRESENTATI DA DEI SINGOLI APICI
					$recensioni_piu_recenti=array_merge($recensioni_piu_recenti, array("'".$recensione->getAttribute("id")."'" => $data_pubblicazione));
				}
			}
		}
	}
	else {
		// ***
		for($i=0; $i<$prodotto->getElementsByTagName("recensione")->length; $i++) {
			$recensione_prodotto=$prodotto->getElementsByTagName("recensione")->item($i);
			
			for($j=0; $j<$recensioni->length; $j++) {
				$recensione=$recensioni->item($j);
				
				// INOLTRE, LE RECENSIONI CHE SI ANDRANNO A CONSIDERARE SARANNO SOLO ED ESCLUSIVAMENTE QUELLE CHE NON SONO STATE MODERATE
				if($recensione_prodotto->getAttribute("idRecensione")==$recensione->getAttribute("id") && $recensione->getAttribute("moderata")=="No") {
					$data_pubblicazione=strtotime($recensione->getAttribute("dataPubblicazione"));
					
					// ***
					$recensioni_piu_recenti=array_merge($recensioni_piu_recenti, array("'".$recensione->getAttribute("id")."'" => $data_pubblicazione));
				}
			}
		}
	}
	
	// UNA VOLTA INDIVIDUATE E MEMORIZZATE LE VARIE INFORMAZIONI DI INTERESSE, SI PROCEDE CON L'ORDINAMENTO DECRESCENTE IN FUNZIONE DEL CONTENUTO (DATA DI PUBBLICAZIONE ESPRESSA IN SECONDI TRAMITE IL METODO strtotime) DELLE VARIE COMPONENTI DEL VETTORE TRAMITE LA FUNZIONE arsort(...), LA QUALE, SPOSTANDO OPPORTUNAMENTE ANCHE LE CHIAVI, CONSENTIÀ DI PRESERVARE TUTTE LE COMBINAZIONI 
	arsort($recensioni_piu_recenti);	
?>