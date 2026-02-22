<?php
	// LO SCRIPT PERMETTE DI STABILIRE SE IL PRODOTTO COINVOLTO IN UNA DETERMINATA DISCUSSIONE È STATO ACQUISTATO O MENO DAL CLIENTE CHE VI HA PRESO PARTE
	// IL PRIMO PASSO DELL'ALGORITMO DI RICERCA CONSISTE NELL'INIZIALIZZARE UNA VARIABILE FLAG A FALSE, LA QUALE VERRÀ POI IMPOSTATA A TRUE NEL CASO IN CUI CI SIA EFFETTIVAMENTE UN ACQUISTO, DA PARTE DEL CLIENTE, CHE COINVOLGE QUEL DETERMINATO ARTICOLO 
	$prodotto_acquistato=false;
	
	for($j=0; $j<$acquisti->length && !$prodotto_acquistato; $j++) {
		$acquistiPerCliente=$acquisti->item($j);
		
		// UNA VOLTA INDIVIDUATI GLI ACQUISTI DELL'UTENTE (CLIENTE) CHE HA PUBBLICATO LA RECENSIONE, SI PROCEDE CON LA SCANSIONE DEL CONTENUTO DI TUTTI I SUOI ORDINI ALLA RICERCA DEL PRODOTTO IN ESAME
		if($acquistiPerCliente->getAttribute("idCliente")==$intervento->getAttribute("idPartecipante")) {
			
			for($k=0; $k<$acquistiPerCliente->getElementsByTagName("acquistoPerCliente")->length && !$prodotto_acquistato; $k++) {
				$acquistoPerCliente=$acquistiPerCliente->getElementsByTagName("acquistoPerCliente")->item($k);
				
				for($l=0; $l<$acquistoPerCliente->getElementsByTagName("offerta")->length && !$prodotto_acquistato; $l++) {
					$offerta_acquistata=$acquistoPerCliente->getElementsByTagName("offerta")->item($l);
					
					if($offerta_acquistata->getAttribute("idProdotto")==$prodotto->getAttribute("id")) {
						$prodotto_acquistato=true;
					}
				}
			}
		}
	}
?>