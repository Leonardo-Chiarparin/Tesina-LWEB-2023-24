<?php
	// LO SCRIPT PERMETTE DI STABILIRE SE L'OFFERTA PER CUI L'UTENTE HA INDICATO IL NUMERO DI PEZZI È GIÀ STATA INSERITA ALL'INTERNO DEL CARRELLO A LUI ASSOCIATO
	// L'ALGORITMO DI RICERCA SI BASA SU UNA SERIE DI CONFRONTI, I QUALI, IN BASE AL CONTESTO E ALLA STRUTTURA DELLA PROPOSTA DI VENDITA, POTRANNO EVENTUALMENTE RIPETERSI
	// A PRESCINDERE DALLA DIRAMAZIONE CHE SI ANDRÀ A CONSIDERARE, IL PRINCIPIO DI BASE CHE CI HA PORTATO ALLA SUA DEFINIZIONE È STATO QUELLO DI COMPARARE I VARI ELEMENTI SEGUENDO IL SEGUENTE ORDINE:
	// 1) PRIMA DI OGNI ALTRO DETTAGLIO, BISOGNA ANDARE A VALUTARE SE IL CONTENUTO DI CIASCUN CAMPO "OBBLIGATORIO" DELL'OFFERTA (FATTA ECCEZIONE PER IL QUANTITATIVO) È UGUALE A QUELLO DELLA VOCE i-ESIMA ATTUALMENTE PRESENTE NEL CARRELLO. IN CASO DI ERRORE, SI POTRÀ FIN DA SUBITO CONCLUDERE LA SCANSIONE AFFERMANDO CHE LE DUE ENTITÀ SONO DIFFERENTI
	// 2) D'ALTRO CANTO, SI POTRÀ PROCEDERE CON LE VERIFICHE IN MERITO A QUEI FATTORI CHE POSSONO CARATTERIZZARE O MENO UNA CERTA OFFERTA, ESCLUDENDO TUTTE QUELLE CASISTICHE PER CUI SI È GIÀ CONSAPEVOLI DELLE POSSIBILI DIFFERENZE 
	// N.B.: IL CODICE È STATO STRUTTURATO IN MODO TALE CHE, IN BASE ALLA PROFILAZIONE DELL'UTENTE, SIA IN GRADO DI OPERARE CON I GIUSTI ELEMENTI (FILE O COOKIE), COSÌ DA PRODURRE IL RISULTATO DESIDERATO
	
	$offerta_presente=false;
	
	if(isset($_SESSION["id_Utente"]) && $_SESSION["tipo_Utente"]=="C") {
	
		for($i=0; $i<$carrello->getElementsByTagName("offerta")->length && !$offerta_presente; $i++) {
			
			// ***
			$offerta_carrello=$carrello->getElementsByTagName("offerta")->item($i);
			
			if(($offerta->getAttribute("id")==$offerta_carrello->getAttribute("id")) && ($offerta->getAttribute("idProdotto")==$offerta_carrello->getAttribute("idProdotto")) && ($offerta->firstChild->textContent==$offerta_carrello->firstChild->textContent)) {
				if($offerta->getElementsByTagName("sconto")->length!=0 && $offerta_carrello->getElementsByTagName("sconto")->length!=0) {
					if($offerta->getElementsByTagName("scontoATempo")->length!=0 && $offerta_carrello->getElementsByTagName("scontoATempo")->length!=0) {
						if(($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale") == $offerta_carrello->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale")) && ($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione") == $offerta_carrello->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione")) && ($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione") == $offerta_carrello->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione"))) {
							if(($offerta->getElementsByTagName("bonus")->length!=0 && $offerta_carrello->getElementsByTagName("bonus")->length!=0) && ($offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent == $offerta_carrello->getElementsByTagName("numeroCrediti")->item(0)->textContent)) {
								$offerta_presente=true;
							}
							else {
								if($offerta->getElementsByTagName("bonus")->length==0 && $offerta_carrello->getElementsByTagName("bonus")->length==0) {
									$offerta_presente=true;
								}
							}
						}
					}
					else {
						if($offerta->getElementsByTagName("scontoFuturo")->length!=0 && $offerta_carrello->getElementsByTagName("scontoFuturo")->length!=0) {
							if(($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale") == $offerta_carrello->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale")) && ($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione") == $offerta_carrello->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione")) && ($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione") == $offerta_carrello->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione"))) {
								if(($offerta->getElementsByTagName("bonus")->length!=0 && $offerta_carrello->getElementsByTagName("bonus")->length!=0) && ($offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent == $offerta_carrello->getElementsByTagName("numeroCrediti")->item(0)->textContent)) {
									$offerta_presente=true;
								}
								else {
									if($offerta->getElementsByTagName("bonus")->length==0 && $offerta_carrello->getElementsByTagName("bonus")->length==0) {
										$offerta_presente=true;
									}
								}
							}
						}
					}
				}
				else {
					if($offerta->getElementsByTagName("sconto")->length==0 && $offerta_carrello->getElementsByTagName("sconto")->length==0) {
						if(($offerta->getElementsByTagName("bonus")->length!=0 && $offerta_carrello->getElementsByTagName("bonus")->length!=0) && ($offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent == $offerta_carrello->getElementsByTagName("numeroCrediti")->item(0)->textContent)) {
							$offerta_presente=true;
						}
						else {
							if($offerta->getElementsByTagName("bonus")->length==0 && $offerta_carrello->getElementsByTagName("bonus")->length==0) {
								$offerta_presente=true;
							}
						}
					}
				}
			}
		}
	}
	else {
		if(isset($_COOKIE["carrello_Offerte"])) {
			
			// PER POTER ACCEDERE AL CONTENUTO DEL RIFERIMENTO CHE HA EVENTUALMENTE PORTATO ALLA TERMINAZIONE DELLA SCANSIONE, SI HA AVUTO LA NECESSITÀ DI DEFINIRE UN CONTATORE CHE, OLTRE AD ESSERE INIZIALIZZATO A ZERO, VERRÀ INCREMENTATO AD OGNI ITERAZIONE DEL CICLO
			$i=0;
			
			// DAL MOMENTO CHE I COOKIE SONO IN GRADO DI MEMORIZZARE SOLTANTO DELLE STRINGHE, RISULTA NECESSARIO RIPORTARE IL CONTENUTO DI QUES'ULTIMO AL SUO TIPO E ALLA SUA STRUTTURA ORIGINALE, OVVERA QUELLA DI UN VETTORE IN CUI OGNI CELLA CONTIENE LE INFORMAZIONI, DISPOSTE A LORO VOLTA SOTTO FORMA DI ARRAY, CHE SI RIFERISCONO AD UNA CERTA PROPOSTA DI VENDITA
			$carrello_Offerte=unserialize($_COOKIE["carrello_Offerte"]);
			
			foreach ($carrello_Offerte as $offerta_carrello) {
				if(!$offerta_presente) {
					if(($offerta->getAttribute("id")==$offerta_carrello["id"]) && ($offerta->getAttribute("idProdotto")==$offerta_carrello["idProdotto"]) && ($offerta->firstChild->textContent==$offerta_carrello["prezzoContabile"])) {
						
						// NEL CASO DI UN VETTORE ASSOCIATIVO, L'UNICO MODO CHE SI HA PER VALUTARE SE È PRESENTE UN ELEMENTO CON UNA CERTA CHIAVE CONSISTE NEL RICHIAMARE IL METODO array_key_exists(...)
						if($offerta->getElementsByTagName("sconto")->length!=0 && (array_key_exists("scontoATempo", $offerta_carrello) xor array_key_exists("scontoFuturo", $offerta_carrello))) {
							if($offerta->getElementsByTagName("scontoATempo")->length!=0 && array_key_exists("scontoATempo", $offerta_carrello)) {
								if(($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale") == $offerta_carrello["scontoATempo"]) && ($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione") == $offerta_carrello["inizioApplicazione"]) && ($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione") == $offerta_carrello["fineApplicazione"])) {
									if(($offerta->getElementsByTagName("bonus")->length!=0 && array_key_exists("numeroCrediti", $offerta_carrello)) && ($offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent == $offerta_carrello["numeroCrediti"])) {
										$offerta_presente=true;
									}
									else {
										if($offerta->getElementsByTagName("bonus")->length==0 && !array_key_exists("numeroCrediti", $offerta_carrello)) {
											$offerta_presente=true;
										}
									}
								}
							}
							else {
								if($offerta->getElementsByTagName("scontoFuturo")->length!=0 && array_key_exists("scontoFuturo", $offerta_carrello)) {
									if(($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale") == $offerta_carrello["scontoFuturo"]) && ($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione") == $offerta_carrello["inizioApplicazione"]) && ($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione") == $offerta_carrello["fineApplicazione"])) {
										if(($offerta->getElementsByTagName("bonus")->length!=0 && array_key_exists("numeroCrediti", $offerta_carrello)) && ($offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent == $offerta_carrello["numeroCrediti"])) {
											$offerta_presente=true;
										}
										else {
											if($offerta->getElementsByTagName("bonus")->length==0 && !array_key_exists("numeroCrediti", $offerta_carrello)) {
												$offerta_presente=true;
											}
										}
									}
								}
							}
						}
						else {
							if($offerta->getElementsByTagName("sconto")->length==0 && !(array_key_exists("scontoATempo", $offerta_carrello) && !array_key_exists("scontoFuturo", $offerta_carrello))) {
								if(($offerta->getElementsByTagName("bonus")->length!=0 && array_key_exists("numeroCrediti", $offerta_carrello)) && ($offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent == $offerta_carrello["numeroCrediti"])) {
									$offerta_presente=true;
								}
								else {
									if($offerta->getElementsByTagName("bonus")->length==0 && !array_key_exists("numeroCrediti", $offerta_carrello)) {
										$offerta_presente=true;
									}
								}
							}
						}
					}
					$i++;
				}
				else
					break;
			} 
		}
	}
?>