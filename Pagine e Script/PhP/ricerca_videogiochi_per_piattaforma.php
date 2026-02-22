<?php
	// LO SCRIPT PERMETTE DI RICERCARE GLI ARTICOLI RELATIVI A DELLE OFFERTE E APPARTENENTI ALLA PIATTAFORMA SELEZIONATA
	// IL PRIMO PASSO DELL'ALGORITMO CONSISTE NELL'INIZIALIZZARE UN VETTORE VUOTO, IL QUALE VERRÀ POI RIEMPITO IN BASE ALL'ORDINAMENTO IMPOSTO DALL'UTENTE
	// NEL DETTAGLIO, LE CASISTICHE DI INTERESSE PER LA PRESENTAZIONE DELLE SINGOLE PROPOSTE DI VENDITA RISULTANO ESSERE:
	// 1) NESSUN ORDINAMENTO => IL VETTORE DI CUI SOPRA, AL CONTRARIO DELLE ALTRE VOCI, NON SARÀ ASSOCIATIVO E, DI CONSEGUENZA, IL CONTENUTO DI CIASCUNA DELLE CELLE CHE LO COMPONGONO SI RIFERÀ ALL'IDENTIFICATORE DELL'OFFERTA i-ESIMA, IL CUI ARTICOLO DI RIFERIMENTO APPARTIENE ALLA PIATTAFORMA DI GIOCO SELEZIONATA IN PRECEDENZA
	// 2) ORDINAMENTO PER PREZZO (CRESCENTE O DECRESCENTE), PER DENOMINAZIONE (CRESCENTE O DESCRESCENTE) E ANNO DI USCITA => LE COPPIE CHE ANDRANNO A FORMARE L'ARRAY ASSOCIATIVO SARANNO: L'IDENTIFICATORE DELLA PROPOSTA DI VENDITA, FORMATTATO MEDIANTE L'INSERIMENTO DI ' ALL'INIZIO E ALLA FINE DI QUEST'ULTIMO, E DEL VALORE PER CUI SI VUOLE EFFETTUARE I CONFRONTI PER L'ORDINAMENTO. IN OGNI CASO, IL FATTO DI ESSERE CRESCENTE O MENO VERRÀ REALIZZATO TRAMITE I METODI asort(...) O arsort(...), I QUALI, SPOSTANDO OPPORTUNAMENTE ANCHE LE CHIAVI, CONSENTIRANNO DI PRESERVARE TUTTE LE COMBINAZIONI 
	// N.B.: L'ORDINAMENTO A SECONDA DELLA PIATTAFORMA È STATO IMPLEMENTATO ISOLANDO I VIDEOGIOCHI IN BASE ALLA LORO CATEGORIA DI APPARTENENZA, PERTANTO TRAMITE LA CREAZIONE DELLA PAGINA IN CUI VIENE RICHIAMATO TALE SCRIPT
	
	$videogiochi_per_piattaforma=array();

	// UNA VOLTA CONCLUSI I CONTROLLI PRELIMINARI, È POSSIBILE PRESENTARE LE PROPOSTE DI VENDITA CHE SI RIFERISCONO A DEGLI ARTICOLI INERENTI ALLA PIATTAFORMA SELEZIONATA. IN PARTICOLARE, POICHÈ L'INTENTO CONSISTE SEMPRE NEL GARANTIRE UN CERTO LIVELLO DI VISIBILITÀ, È STATO DECISO DI DISPORNE QUATTRO PER OGNI RIGA
	for($i=0; $i<$offerte->length; $i++)
	{
		$offerta=$offerte->item($i);

		for($j=0; $j<$prodotti->length; $j++)
		{
			$prodotto=$prodotti->item($j);
			
			if($offerta->getAttribute("idProdotto")==$prodotto->getAttribute("id"))
			{
				for($k=0; $k<$prodotto->getElementsByTagName("piattaforma")->length; $k++)
				{
					if($piattaforma->getAttribute("id")==$prodotto->getElementsByTagName("piattaforma")->item($k)->getAttribute("idPiattaforma"))
					{
						// COME ANTICIPATO, BISOGNERÀ INDIVIDUARE LA POSSIBILE PRESENZA DI UN ORDINAMENTO PER LE VARIE COMPONENTI
						if(isset($_GET["ordinamento"]))
						{
							// IN TAL CASO, OLTRE A DOVER CREARE UN ARRAY ASSOCIATIVO, SARÀ NECESSARIO DISCIMINARE IL TIPO DI QUEST'ULTIMO TRA QUELLI AMMESSI DALLA PIATTAFORMA. A PRESCINDERE, AL FINE DI REGISTRARE CORRETTAMENTE L'IDENTIFICATORE DELL'OFFERTA, È STATO NECESSARIO TRATTARLO COME SE FOSSE UNA STRINGA. IN PARTICOLARE, I DUE CARATTERI DELIMITATORI DEL VALORE EFFETTIVO SARANNO RAPPRESENTATI DA DEI SINGOLI APICI
							if($_GET["ordinamento"]=="prezzoCrescente" || $_GET["ordinamento"]=="prezzoDecrescente")
							{
								// IL PREZZO DA CONSIDERARE DOVRÀ ESSERE VALUTATO AL NETTO DI EVENTUALI RIDUZIONI INERENTI ALLA PROPOSTA DI VENDITA 
								if(!$offerta->getElementsByTagName("scontoATempo")->length)
									$videogiochi_per_piattaforma=array_merge($videogiochi_per_piattaforma, array("'".$offerta->getAttribute("id")."'" => $offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent));
								else
									$videogiochi_per_piattaforma=array_merge($videogiochi_per_piattaforma, array("'".$offerta->getAttribute("id")."'" => number_format(floatval($offerta->firstChild->textContent) - (floatval($offerta->firstChild->textContent)*(floatval($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"))/100)),2,".","")));
							}
							else {
								if($_GET["ordinamento"]=="nomeA-Z" || $_GET["ordinamento"]=="nomeZ-A")
								{
									$videogiochi_per_piattaforma=array_merge($videogiochi_per_piattaforma, array("'".$offerta->getAttribute("id")."'" => $prodotto->firstChild->textContent));
								}
								else {
									if($_GET["ordinamento"]=="annoDiUscita")
									{
										$videogiochi_per_piattaforma=array_merge($videogiochi_per_piattaforma, array("'".$offerta->getAttribute("id")."'" => intval($prodotto->getElementsByTagName("annoUscita")->item(0)->textContent)));
									}
									// QUALORA SIA STATO RICHIESTO UN ORDINAMENTO NON PREVISTO DALLA PAGINA DEL SITO, VERRÀ INTERPRETATO COME SE FOSSE QUELLO DI DEFAULT, OVVERO NESSUNO 
									else {
										array_push($videogiochi_per_piattaforma, $offerta->getAttribute("id"));
									}
								}
							}
						}
						else
							array_push($videogiochi_per_piattaforma, $offerta->getAttribute("id"));
					}
				}	
			}
		}
	}
	
	if(isset($_GET["ordinamento"]))
	{
		if($_GET["ordinamento"]=="prezzoCrescente" || $_GET["ordinamento"]=="nomeA-Z")
		{
			asort($videogiochi_per_piattaforma);
		}
		else {
			if($_GET["ordinamento"]=="prezzoDecrescente" || $_GET["ordinamento"]=="nomeZ-A" || $_GET["ordinamento"]=="annoDiUscita")
			{
				arsort($videogiochi_per_piattaforma);
			}
		}
	}
?>