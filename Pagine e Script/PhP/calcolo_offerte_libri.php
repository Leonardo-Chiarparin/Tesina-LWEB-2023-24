<?php
	// LO SCRIPT PERMETTE DI TENERE TRACCIA DEL NUMERO DELLE PROPOSTE DI VENDITA CHE SI RIFERISCONO A DELLE OPERE CARTACEE
	// AL FINE DI POTERLE QUANTIFICARE CON ESATTEZZA, È STATO NECESSARIO INTRODURRE UN CONTATORE CHE, OLTRE AD ESSERE INIZIALIZZATO A ZERO, VERRÀ INCREMENTATO AD OGNI NUOVA OFFERTA D'INTERESSE
	$num_offerte=0;
	
	for($i=0; $i<$offerte->length; $i++){
		$offerta=$offerte->item($i);
		
		for($j=0; $j<$prodotti->length; $j++) {
			
			if($prodotti->item($j)->getAttribute("id")==$offerta->getAttribute("idProdotto") && $prodotti->item($j)->getElementsByTagName("libro")->length!=0) {
				$num_offerte++;
			}
		}
	}
?>