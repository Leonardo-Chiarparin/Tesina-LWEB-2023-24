<?php
	// LO SCRIPT PREVEDE LA RICERCA DI TUTTE QUELLE PROPOSTE DI VENDITA CHE SI RIFERISCONO AI LIBRI CHE SONO STATI VENDUTI PIÙ VOLTE NEL CORSO DEL TEMPO
	// IL PRIMO PASSO DELL'ALGORITMO DI RICERCA CONSISTE NELL'INIZIALIZZARE UN VETTORE VUOTO, IL QUALE VERRÀ POI RIEMPITO MEDIANTE LE COPPIE FORMATE DALL'IDENTIFICATORE DEL PRODOTTO E IL QUANTITATIVO DI ACQUISTI CHE LO RIGUARDA
	$i_piu_venduti=array();
	
	// PER OGNI LIBRO CARATTERIZZATO DA UNA PROPOSTA DI VENDITA ANCORA VALIDA, VERRÀ CREATA UNA NUOVA COMBINAZIONE, LA QUALE, OLTRE AD ESSERE DESCRITTA COME SOPRA, ANDRÀ FORMARE UN NUOVO ELEMENTO DELL'ARRAY IN QUESTIONE   
	for($i=0; $i<$prodotti->length; $i++) {
		$prodotto=$prodotti->item($i);
		
		for($j=0; $j<$offerte->length; $j++) {
			$offerta=$offerte->item($j);
			
			if($offerta->getAttribute("idProdotto")==$prodotto->getAttribute("id") && $prodotto->getElementsByTagName("libro")->length!=0) {
				
				// IN MERITO AL CONTEGGIO DEGLI ACQUISTI INERENTI AD UN DETERMINATO PRODOTTO, SARÀ DOVEROSO SCANSIONARE LA STRUTTURA DATI INERENTE A QUEST'ULTIMI
				$num_acquisti=0;
				
				for($k=0; $k<$acquisti->length; $k++) {
					$acquistiPerCliente=$acquisti->item($k);
					
					// PER OGNI ACQUISTO, BISOGNERÀ CONSIDERARE IL QUANTITATIVO, OVVERO IL NUMERO DI PEZZI, DEL PRODOTTO VENDUTO, IL QUALE RISULTA CONTENUTO ALL'INTERNO DELL'ENTITÀ CHE DESCRIVE LA SINGOLA PROPOSTA DI VENDITA
					for($l=0; $l<$acquistiPerCliente->getElementsByTagName("offerta")->length; $l++) {
						
						if($acquistiPerCliente->getElementsByTagName("offerta")->item($l)->getAttribute("idProdotto")==$prodotto->getAttribute("id"))
							$num_acquisti+=$acquistiPerCliente->getElementsByTagName("offerta")->item($l)->getElementsByTagName("quantitativo")->item(0)->textContent;
					}
				}
				
				// AL FINE DI REGISTRARE CORRETTAMENTE L'IDENTIFICATORE DEL PRODOTTO, È STATO NECESSARIO TRATTARLO COME SE FOSSE UNA STRINGA. IN PARTICOLARE, I DUE CARATTERI DELIMITATORI DEL VALORE EFFETTIVO SARANNO RAPPRESENTATI DA DEI SINGOLI APICI
				$i_piu_venduti=array_merge($i_piu_venduti, array("'".$prodotto->getAttribute("id")."'" => $num_acquisti));
			}
		}
	}
	
	// UNA VOLTA INDIVIDUATE E MEMORIZZATE LE VARIE INFORMAZIONI DI INTERESSE, SI PROCEDE CON L'ORDINAMENTO DECRESCENTE IN FUNZIONE DEL CONTENUTO (NUMERO DEGLI ACQUISTI) DELLE VARIE COMPONENTI DEL VETTORE TRAMITE LA FUNZIONE arsort(...), LA QUALE, SPOSTANDO OPPORTUNAMENTE ANCHE LE CHIAVI, CONSENTIÀ DI PRESERVARE TUTTE LE COMBINAZIONI 
	arsort($i_piu_venduti);
	
	// PER PORTARE A TERMINE L'OPERAZIONE, È NECESSARIO ELIMINARE TUTTI GLI ELEMENTI AGGIUNTIVI PER PORTARE LA DIMENSIONE DEL VETTORE AD AL MASSIMO 4 POSIZIONI
	$i=0;
	
	foreach($i_piu_venduti as $chiave => $contenuto) {
		
		if($i>=4){
			unset($i_piu_venduti[$chiave]);
		}
		
		$i++;
		
	}
?>