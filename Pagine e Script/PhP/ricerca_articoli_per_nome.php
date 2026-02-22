<?php
	// LO SCRIPT PERMETTE DI RICERCARE GLI ARTICOLI RELATIVI A DELLE OFFERTE E AVENTI UN NOMINATIVO SIMILE O IDENTICO A QUELLO SPECIFICATO DALL'UTENTE ALL'INTERNO DELLA BARRA DI RICERCA
	// IL PRIMO PASSO DELL'ALGORITMO CONSISTE NELL'INIZIALIZZARE UN VETTORE VUOTO, IL QUALE, UNA VOLTA RIEMPITO, VERRÀ POI ORGANIZZATO IN BASE ALL'ORDINAMENTO IMPOSTO
	// NEL DETTAGLIO, LE CASISTICHE DI INTERESSE PER LA PRESENTAZIONE DELLE SINGOLE PROPOSTE DI VENDITA RISULTANO ESSERE:
	// 1) NESSUN ORDINAMENTO => IL VETTORE DI CUI SOPRA, AL CONTRARIO DELLE ALTRE VOCI, NON SARÀ ASSOCIATIVO E, DI CONSEGUENZA, IL CONTENUTO DI CIASCUNA DELLE CELLE CHE LO COMPONGONO SI RIFERÀ ALL'IDENTIFICATORE DELL'OFFERTA i-ESIMA, IL CUI ARTICOLO DI RIFERIMENTO PRESENTA UN NOMINATIVO RICONDUCIBILE A QUANTO INSERITO DALL'UTENTE
	// 2) ORDINAMENTO PER PREZZO (CRESCENTE O DECRESCENTE), PER DENOMINAZIONE (CRESCENTE O DESCRESCENTE) E ANNO DI USCITA => LE COPPIE CHE ANDRANNO A FORMARE L'ARRAY ASSOCIATIVO SARANNO: L'IDENTIFICATORE DELLA PROPOSTA DI VENDITA, FORMATTATO MEDIANTE L'INSERIMENTO DI ' ALL'INIZIO E ALLA FINE DI QUEST'ULTIMO, E DEL VALORE PER CUI SI VUOLE EFFETTUARE I CONFRONTI PER L'ORDINAMENTO. IN OGNI CASO, IL FATTO DI ESSERE CRESCENTE O MENO VERRÀ REALIZZATO TRAMITE I METODI asort(...) O arsort(...), I QUALI, SPOSTANDO OPPORTUNAMENTE ANCHE LE CHIAVI, CONSENTIRANNO DI PRESERVARE TUTTE LE COMBINAZIONI 
	// N.B.: L'ORDINAMENTO PER CATEGORIA È STATO GESTITO MEDIANTE LA DEFINIZIONE DI ALTRE PAGINE IN CUI SONO STATI ISOLATI I VARI ARTICOLI A SECONDA DI QUELLE DI APPARTENENZA
	
	// ALLO SCOPO DI PRESENTARE CORRETTAMENTE LE VARIE PROPOSTE DI VENDITA, È STATO NECESSARIO INTRODURRE DUE FASI PRELIMINARI, LE QUALI SI OCCUPERANNO DI:
	// 1) COMPRENDERE SE IL NOMINATIVO DI UN ARTICOLO PRESENTA AL SUO INTERNO, E DUNQUE COME SUE SOTTOSTRINGHE, TUTTE LE PAROLE (EVENTUALMENTE SEPARATE DA DEGLI SPAZI BIANCHI) CHE COMPONGONO L'INPUT INDICATO ALL'INTERNO DELLA RELATIVA BARRA DI RICERCA. IN CASO POSITIVO, CI SI LIMITERÀ A RIPORTARE ESCLUSIVAMENTE L'OFFERTA DI QUEL DETERMINATO PRODOTTO, TERMINANDO ISTANTANEMENTE CON LA SCANSIONE DELLE VARIE PROPOSTE DI VENDITA 
	// 2) IN CASO CONTRARIO, VERRANNO ANALIZZATE LE CORRISPONDENZE TRA LE SINGOLE PARTI DELLA STRINGA CON IL NOME DEL PRODOTTO MEDIANTE L'UTILIZZO DELLA FUNZIONE explode(...), LA QUALE, SPECIFICANDO UN OPPORTUNO DELIMITATORE (NEL NOSTRO CASO " "), CREERÀ UN VETTORE FORMATO DA TUTTE LE PARTI DELL'INPUT CHE RISULTANO SEPARATE DAL PRECEDENTE FATTORE  
	$articoli_per_nome=array();
	
	for($i=0; $i<$offerte->length; $i++)
	{
		$offerta=$offerte->item($i);

		for($j=0; $j<$prodotti->length; $j++)
		{
			$prodotto=$prodotti->item($j);
			
			if($offerta->getAttribute("idProdotto")==$prodotto->getAttribute("id"))
			{
				// PER SEMPLICITÀ, SI INIZIALIZZA UNA VARIABILE FLAG CHE, IN BASE ALL'ESITO DEI CONFRONTI TRA NOME E PARTI DELLA STRINGA D'INPUT, VERRÀ IMPOSTATO O MENO A FALSE  
				$corrispondenze_individuate=true;
				$componente_prodotto_ricercato=explode(" ", $prodotto_ricercato);
				
				for($k=0; $k<count($componente_prodotto_ricercato) && $corrispondenze_individuate; $k++)
				{
					if(!(strpos(strtolower($prodotto->firstChild->textContent), $componente_prodotto_ricercato[$k])!==false))
					{
						$corrispondenze_individuate=false;
					}
				}
				
				if($corrispondenze_individuate) {
					// COME ANTICIPATO, BISOGNERÀ INDIVIDUARE LA POSSIBILE PRESENZA DI UN ORDINAMENTO PER LE VARIE COMPONENTI
					if(isset($_GET["ordinamento"]))
					{
						// IN TAL CASO, OLTRE A DOVER CREARE UN ARRAY ASSOCIATIVO, SARÀ NECESSARIO DISCIMINARE IL TIPO DI QUEST'ULTIMO TRA QUELLI AMMESSI DALLA PIATTAFORMA. A PRESCINDERE, AL FINE DI REGISTRARE CORRETTAMENTE L'IDENTIFICATORE DELL'OFFERTA , È STATO NECESSARIO TRATTARLO COME SE FOSSE UNA STRINGA. IN PARTICOLARE, I DUE CARATTERI DELIMITATORI DEL VALORE EFFETTIVO SARANNO RAPPRESENTATI DA DEI SINGOLI APICI
						if($_GET["ordinamento"]=="prezzoCrescente" || $_GET["ordinamento"]=="prezzoDecrescente")
						{
							// IL PREZZO DA CONSIDERARE DOVRÀ ESSERE VALUTATO AL NETTO DI EVENTUALI RIDUZIONI INERENTI ALLA PROPOSTA DI VENDITA 
							if(!$offerta->getElementsByTagName("scontoATempo")->length)
							{
								$articoli_per_nome=array_merge($articoli_per_nome, array("'".$offerta->getAttribute("id")."'" => $offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent));	
							}
							else
							{
								$articoli_per_nome=array_merge($articoli_per_nome, array("'".$offerta->getAttribute("id")."'" => number_format(floatval($offerta->firstChild->textContent) - (floatval($offerta->firstChild->textContent)*(floatval($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"))/100)),2,".","")));
							}
						}
						else {
							if($_GET["ordinamento"]=="nomeA-Z" || $_GET["ordinamento"]=="nomeZ-A")
							{
								$articoli_per_nome=array_merge($articoli_per_nome, array("'".$offerta->getAttribute("id")."'" => $prodotto->firstChild->textContent));
							}
							else {
								if($_GET["ordinamento"]=="annoDiUscita")
								{
									$articoli_per_nome=array_merge($articoli_per_nome, array("'".$offerta->getAttribute("id")."'" => intval($prodotto->getElementsByTagName("annoUscita")->item(0)->textContent)));
								}
								// QUALORA SIA STATO RICHIESTO UN ORDINAMENTO NON PREVISTO DALLA PAGINA DEL SITO, VERRÀ INTERPRETATO COME SE FOSSE QUELLO DI DEFAULT, OVVERO NESSUNO 
								else {
									array_push($articoli_per_nome, $offerta->getAttribute("id"));
								}
							}
						}
					}
					else
					{
						array_push($articoli_per_nome, $offerta->getAttribute("id"));
					}
				}
			}
		}
	}
	
	// SE I PRECEDENTI RIFERIMENTI NON SONO TUTTI CONTENUTI ALL'INTERNO DEL NOME DI UN SINGOLO PRODOTTO (E QUINDI LA DIMENSIONE DEL VETTORE RISULTA NULLA), VERRANNO EFFETTUATI DEI CONFRONTI TENENDO CONTO DI QUEI BENI CHE NE INCLUDONO ALMENO UNO COME SOTTOSTRINGA
	if(!$corrispondenze_individuate && count($articoli_per_nome)==0) {
		for($i=0; $i<$offerte->length; $i++)
		{
			$offerta=$offerte->item($i);

			for($j=0; $j<$prodotti->length; $j++)
			{
				$prodotto=$prodotti->item($j);
				
				if($offerta->getAttribute("idProdotto")==$prodotto->getAttribute("id"))
				{
					for($k=0; $k<count($componente_prodotto_ricercato); $k++)
					{
						if(strpos(strtolower($prodotto->firstChild->textContent), $componente_prodotto_ricercato[$k])!==false)
						{
							if(isset($_GET["ordinamento"]))
							{
								if($_GET["ordinamento"]=="prezzoCrescente" || $_GET["ordinamento"]=="prezzoDecrescente")
								{
									if(!$offerta->getElementsByTagName("scontoATempo")->length)
									{
										$articoli_per_nome=array_merge($articoli_per_nome, array("'".$offerta->getAttribute("id")."'" => $offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent));
										break;
									}
									else
									{
										$articoli_per_nome=array_merge($articoli_per_nome, array("'".$offerta->getAttribute("id")."'" => number_format(floatval($offerta->firstChild->textContent) - (floatval($offerta->firstChild->textContent)*(floatval($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"))/100)),2,".","")));
										break;
									}
								}
								else {
									if($_GET["ordinamento"]=="nomeA-Z" || $_GET["ordinamento"]=="nomeZ-A")
									{
										$articoli_per_nome=array_merge($articoli_per_nome, array("'".$offerta->getAttribute("id")."'" => $prodotto->firstChild->textContent));
										break;
									}
									else {
										if($_GET["ordinamento"]=="annoDiUscita")
										{
											$articoli_per_nome=array_merge($articoli_per_nome, array("'".$offerta->getAttribute("id")."'" => intval($prodotto->getElementsByTagName("annoUscita")->item(0)->textContent)));
											break;
										}
										else {
											array_push($articoli_per_nome, $offerta->getAttribute("id"));
											break;
										}
									}
								}
							}
							else
							{
								array_push($articoli_per_nome, $offerta->getAttribute("id"));
								break;
							}
						}
					}	
				}
			}
		}
	}
	
	if(isset($_GET["ordinamento"]))
	{
		if($_GET["ordinamento"]=="prezzoCrescente" || $_GET["ordinamento"]=="nomeA-Z")
		{
			asort($articoli_per_nome);
		}
		else {
			if($_GET["ordinamento"]=="prezzoDecrescente" || $_GET["ordinamento"]=="nomeZ-A" || $_GET["ordinamento"]=="annoDiUscita")
			{
				arsort($articoli_per_nome);
			}
		}
	}
?>