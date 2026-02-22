<?php
	// LO SCRIPT PERMETTE DI CALCOLARE LA MEDIA DI OGNUNA DELLE VALUTAZIONI RICEVUTE DAI SINGOLI PRODOTTI AL SOLO SCOPO DI GIUDICARNE LA QUALITÀ
	// AL FINE DI REALIZZARE UN CODICE CHE SIA SPENDIBILE PER AMBEDUE LE TIPOLOGIE D'INTERESSE (LIBRI E VIDEOGIOCHI), SI È DOVUTO IMPLEMENTARE UN CONTROLLO PRELIMINARE CHE, IN BASE ALL'ESITO, SARÀ IN GRADO DI DISCRIMINARE I PARAMETRI DI GIUDIZIO DA CONSIDERARE PER LA PRODUZIONE DEL RISULTATO DESIDERATO
	if($prodotto->getElementsByTagName("libro")->length!=0) {
		$media_trama=$media_personaggi=$media_ambientazione=0;
	
		if($num_recensioni!=0){
			
			// PER OGNUNA DELLE RECENSIONI RELATIVE AD UN CERTO LIBRO, SI DOVRÀ EFFETTUARE UNA SOMMA PARZIALE DEI VOTI RELATIVI ALLA TRAMA, ALLA CARATTERIZZAZIONE DEI PERSONAGGI E ALL'AMBIENTAZIONE DELL'OPERA IN QUESTIONE
			for($k=0; $k<$prodotto->getElementsByTagName("recensione")->length; $k++) {
				$recensione=$prodotto->getElementsByTagName("recensione")->item($k);
				
				for($l=0; $l<$recensioni->length; $l++) {
					
					// A SEGUITO DEL "PASSAGGIO" DA XML A PHP, IL CONTENUTO DEI VARI ELEMENTI, COSÌ COME QUELLO DEI LORO STESSI ATTRIBUTI, VIENE CONSIDERATO COME SE FOSSE UNA STRINGA. PROPRIO PER QUESTO, È STATO NECESSARIO CONVERTIRE IN INTERI, MEDIANTE IL METODO intval(...), TUTTI I RIFERIMENTI INTERESSATI IN MODO TALE DA POTERLI METTERE IN RELAZIONE TRA LORO 
					// PER DI PIÙ, I PUNTEGGI DA TENERE IN CONSIDERAZIONE PER IL CALCOLO SARANNO SOLO ED ESCLUSIVAMENTE QUELLI INERENTI AD UNA RECENSIONE CHE NON È STATA MODERATA
					if($recensione->getAttribute("idRecensione")==$recensioni->item($l)->getAttribute("id") && $recensioni->item($l)->getAttribute("moderata")=="No") {
						$media_trama=$media_trama+intval($recensioni->item($l)->getElementsByTagName("perLibro")->item(0)->getAttribute("trama"));
						$media_personaggi=$media_personaggi+intval($recensioni->item($l)->getElementsByTagName("perLibro")->item(0)->getAttribute("caratterizzazionePersonaggi"));
						$media_ambientazione=$media_ambientazione+intval($recensioni->item($l)->getElementsByTagName("perLibro")->item(0)->getAttribute("ambientazione"));
					}
				}
			}
			
			// UNA VOLTA PORTATI A TERMINE I PASSI DI CUI SOPRA, BISOGNERÀ DIVIDERE OGNI VALORE OTTENUTO DALLA PRECEDENTE SCANSIONE CON L'AMMONTARE DELLE RECENSIONI E, IN SEGUITO, FORMATTARE I VARI RISULTATI IN MODO TALE CHE POSSANO PRESENTARE UNA SOLA CIFRA DECIMALE
			$media_trama=number_format($media_trama/$num_recensioni, 1,".","");
			$media_personaggi=number_format($media_personaggi/$num_recensioni, 1,".","");
			$media_ambientazione=number_format($media_ambientazione/$num_recensioni, 1,".","");
		}
	}
	else {
		$media_sceneggiatura=$media_tecnica=$media_giocabilita=0;
	
		if($num_recensioni!=0){
			
			// ***
			for($k=0; $k<$prodotto->getElementsByTagName("recensione")->length; $k++) {
				$recensione=$prodotto->getElementsByTagName("recensione")->item($k);
				
				// ***
				for($l=0; $l<$recensioni->length; $l++) {
					if($recensione->getAttribute("idRecensione")==$recensioni->item($l)->getAttribute("id") && $recensioni->item($l)->getAttribute("moderata")=="No") {
						$media_sceneggiatura=$media_sceneggiatura+intval($recensioni->item($l)->getElementsByTagName("perVideogioco")->item(0)->getAttribute("sceneggiatura"));
						$media_tecnica=$media_tecnica+intval($recensioni->item($l)->getElementsByTagName("perVideogioco")->item(0)->getAttribute("tecnica"));
						$media_giocabilita=$media_giocabilita+intval($recensioni->item($l)->getElementsByTagName("perVideogioco")->item(0)->getAttribute("giocabilita"));
					}
				}
			}
			
			// ***
			$media_sceneggiatura=number_format($media_sceneggiatura/$num_recensioni, 1,".","");
			$media_tecnica=number_format($media_tecnica/$num_recensioni, 1,".","");
			$media_giocabilita=number_format($media_giocabilita/$num_recensioni, 1,".","");
		}
	}
?>