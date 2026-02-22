<?php
	// LO SCRIPT PREVEDE LA RICERCA DI TUTTE QUELLE PROPOSTE DI VENDITA CHE SI RIFERISCONO AI VIDEOGIOCHI CHE SONO STATI VOTATI IL MAGGIOR NUMERO DI VOLTE NEL CORSO DEL TEMPO
	// IL PRIMO PASSO DELL'ALGORITMO DI RICERCA CONSISTE NELL'INIZIALIZZARE UN VETTORE VUOTO, IL QUALE VERRÀ POI RIEMPITO MEDIANTE LE COPPIE FORMATE DALL'IDENTIFICATORE DEL PRODOTTO E IL QUANTITATIVO DI RECENSIONI A LUI DESTINATE
	$i_piu_votati=array();
	
	// PER OGNI VIDEOGIOCO CARATTERIZZATO DA UNA PROPOSTA DI VENDITA ANCORA VALIDA, VERRÀ CREATA UNA NUOVA COMBINAZIONE, LA QUALE, OLTRE AD ESSERE DESCRITTA COME SOPRA, ANDRÀ FORMARE UN NUOVO ELEMENTO DELL'ARRAY IN QUESTIONE   
	for($i=0; $i<$prodotti->length; $i++) {
		$prodotto=$prodotti->item($i);
		
		for($j=0; $j<$offerte->length; $j++) {
			$offerta=$offerte->item($j);
			
			if($offerta->getAttribute("idProdotto")==$prodotto->getAttribute("id") && $prodotto->getElementsByTagName("videogioco")->length!=0) {
				// IL NUMERO DI RECENSIONI VERRÀ DETERMINATO IN FUNZIONE DI TUTTE QUELLE CHE NON SONO STATE ANCORA MODERATE
				$num_recensioni=0;
				
				for($k=0; $k<$prodotto->getElementsByTagName("recensione")->length; $k++) {
					$recensione_prodotto=$prodotto->getElementsByTagName("recensione")->item($k);
					
					for($l=0; $l<$recensioni->length; $l++) {
						if($recensioni->item($l)->getAttribute("id")==$recensione_prodotto->getAttribute("idRecensione") && $recensioni->item($l)->getAttribute("moderata")=="No") {
							$num_recensioni++;
							break;
						}
					}
				}
				
				// AL FINE DI REGISTRARE CORRETTAMENTE L'IDENTIFICATORE DEL PRODOTTO, È STATO NECESSARIO TRATTARLO COME SE FOSSE UNA STRINGA. IN PARTICOLARE, I DUE CARATTERI DELIMITATORI DEL VALORE EFFETTIVO SARANNO RAPPRESENTATI DA DEI SINGOLI APICI
				$i_piu_votati=array_merge($i_piu_votati, array("'".$prodotto->getAttribute("id")."'" => $num_recensioni));
			}
		}
	}
	
	// UNA VOLTA INDIVIDUATE E MEMORIZZATE LE VARIE INFORMAZIONI DI INTERESSE, SI PROCEDE CON L'ORDINAMENTO DECRESCENTE IN FUNZIONE DEL CONTENUTO (NUMERO DELLE RECENSIONI) DELLE VARIE COMPONENTI DEL VETTORE TRAMITE LA FUNZIONE arsort(...), LA QUALE, SPOSTANDO OPPORTUNAMENTE ANCHE LE CHIAVI, CONSENTIÀ DI PRESERVARE TUTTE LE COMBINAZIONI 
	arsort($i_piu_votati);
	
	// PER PORTARE A TERMINE L'OPERAZIONE, È NECESSARIO ELIMINARE TUTTI GLI ELEMENTI AGGIUNTIVI PER PORTARE LA DIMENSIONE DEL VETTORE AD AL MASSIMO 4 POSIZIONI
	$i=0;
	
	foreach($i_piu_votati as $chiave => $contenuto) {
		
		if($i>=4){
			unset($i_piu_votati[$chiave]);
		}
		
		$i++;
		
	}
?>