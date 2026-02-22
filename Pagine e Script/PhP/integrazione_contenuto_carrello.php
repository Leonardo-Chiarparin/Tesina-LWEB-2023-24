<?php
	// LO SCRIPT PERMETTE DI INTEGRARE LE PROPOSTE DI VENDITA PRESENTI NEL FILE XML, RAFFIGURANTE IL CARRELLO DI UN CERTO CLIENTE, CON QUELLE MEMORIZZATE ALL'INTERNO DEL COOKIE CREATO PER ADEMPIERE AI MEDESIMI COMPITI MA NEL CASO DI UN SEMPLICE VISITATORE
	// L'IDEA DI BASE CONSISTE NEL SCANSIONARE IL CONTENUTO DEI DUE OGGETTI ALLA RICERCA DI EVENTUALI CORRISPONDENZE TRA LE SINGOLE VOCI. IN BASE ALL'ESITO DEL PRECEDENTE CONFRONTO, SI POTRÀ STABILIRE SE E QUALI MODIFICHE DOVRANNO ESSERE APPLICATE NEL DOCUMENTO, OVVERO SE IL QUANTITATIVO DI UN'OFFERTA COLLOCATA AL SUO INTERNO DOVRÀ ESSERE INCREMENTATO OPPURE SE SI DOVRÀ INSERIRE UNA NUOVA VOCE A SÈ STANTE 
	// IN AGGIUNTA, BISOGNERÀ VALUTARE LA CLASSE DI APPARTENENZA DEL SOGGETTO CHE HA INSERITO I PRODOTTI ALL'INTERNO DEL COOKIE, IL QUALE, QUALORA SI TRATTI DI UN GESTORE O DI UN AMMINISTRATORE, VERRÀ SEMPLICEMENTE RIMOSSO. D'ALTRO CANTO, E GRAZIE ANCHE ALLE VERIFICHE EFFETTUATE DURANTE LA CREAZIONE DEI SINGOLI ELEMENTI, SI PROCEDERÀ CON L'INSERIMENTO DEI VARI ELEMENTI ALL'INTERNO DEL FILE SENZA EFFETTUARE ULTERIORI CONTROLLI
	
	if(isset($_SESSION["id_Utente"]) && $_SESSION["tipo_Utente"]=="C" && isset($_COOKIE["carrello_Offerte"])) {
		
		// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
		require_once("./apertura_file_carrelli.php");
		require_once("./apertura_file_offerte.php");
		
		// DAL MOMENTO CHE I COOKIE SONO IN GRADO DI MEMORIZZARE SOLTANTO DELLE STRINGHE, RISULTA NECESSARIO RIPORTARE IL CONTENUTO DI QUES'ULTIMO AL SUO TIPO E ALLA SUA STRUTTURA ORIGINALE, OVVERA QUELLA DI UN VETTORE IN CUI OGNI CELLA CONTIENE LE INFORMAZIONI, DISPOSTE A LORO VOLTA SOTTO FORMA DI ARRAY, CHE SI RIFERISCONO AD UNA CERTA PROPOSTA DI VENDITA
		$carrello_Offerte=unserialize($_COOKIE["carrello_Offerte"]);
		
		for($i=0; $i<$carrelli->length; $i++) {
			$carrello=$carrelli->item($i);
		
			// PER VALUTARE LA SOMIGLIANZA DELLE SINGOLE OFFERTE, SI HA LA NECESSITÀ DI APPLICARE ALCUNI DEI RAGIONAMENTI GIÀ VISTI PER LA RICERCA DI UN'OFFERTA ALL'INTERNO DEL CARRELLO
			// CONTRARIAMENTE AI PRECEDENTI CASI, LA STRUTTURA CON CUI È STATO REALIZZATO L'ALGORITMO APPENA CITATO ("ricerca_offerta_nel_carrello.php") CI IMPEDISCE DI POTERLO RICHIAMARE ANCHE IN QUESTO CONTESTO, INFATTI, SE COSÌ FOSSE, RICADREBBE SEMPRE NELLA PRIMA DIRAMAZIONE
			// PER POTER OPERARE CON IL FILE XML, ABBIAMO INFATTI BISOGNO DELL'IDENTIFICATORE DELL'UTENTE, IL QUALE RISULTA ACCESSIBILE UNA VOLTA TERMINATA LA PROCEDURA DI AUTENTICAZIONE
			if($carrello->getAttribute("idCliente")==$_SESSION["id_Utente"]) {
				
				for($j=0; $j<$carrello->getElementsByTagName("offerta")->length; $j++) {
					$offerta_interessata=$carrello->getElementsByTagName("offerta")->item($j);
					
					$offerta_presente=false;
					
					foreach ($carrello_Offerte as $offerta_carrello_cookie) {
						if(!$offerta_presente) {
							if(($offerta_interessata->getAttribute("id")==$offerta_carrello_cookie["id"]) && ($offerta_interessata->getAttribute("idProdotto")==$offerta_carrello_cookie["idProdotto"]) && ($offerta_interessata->firstChild->textContent==$offerta_carrello_cookie["prezzoContabile"])) {
								// NEL CASO DI UN VETTORE ASSOCIATIVO, L'UNICO MODO CHE SI HA PER VALUTARE SE È PRESENTE UN ELEMENTO CON UNA CERTA CHIAVE CONSISTE NEL RICHIAMARE IL METODO array_key_exists(...)
								if($offerta_interessata->getElementsByTagName("sconto")->length!=0 && (array_key_exists("scontoATempo", $offerta_carrello_cookie) xor array_key_exists("scontoFuturo", $offerta_carrello_cookie))) {
									if($offerta_interessata->getElementsByTagName("scontoATempo")->length!=0 && array_key_exists("scontoATempo", $offerta_carrello_cookie)) {
										if(($offerta_interessata->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale") == $offerta_carrello_cookie["scontoATempo"]) && ($offerta_interessata->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione") == $offerta_carrello_cookie["inizioApplicazione"]) && ($offerta_interessata->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione") == $offerta_carrello_cookie["fineApplicazione"])) {
											if(($offerta_interessata->getElementsByTagName("bonus")->length!=0 && array_key_exists("numeroCrediti", $offerta_carrello_cookie)) && ($offerta_interessata->getElementsByTagName("numeroCrediti")->item(0)->textContent == $offerta_carrello_cookie["numeroCrediti"])) {
												$offerta_presente=true;
												
												// GIUNTI A QUESTO PUNTO, SI PROCEDERÀ CON LE OPERAZIONI PER ADEGUARE IL CONTENUTO DEL CARRELLO
												require("./sistemazione_contenuto_carrello.php");
												
											}
											else {
												if($offerta_interessata->getElementsByTagName("bonus")->length==0 && !array_key_exists("numeroCrediti", $offerta_carrello_cookie)) {
													$offerta_presente=true;
													
													// ***
													require("./sistemazione_contenuto_carrello.php");
												}
											}
										}
									}
									else {
										if($offerta_interessata->getElementsByTagName("scontoFuturo")->length!=0 && array_key_exists("scontoFuturo", $offerta_carrello_cookie)) {
											if(($offerta_interessata->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale") == $offerta_carrello_cookie["scontoFuturo"]) && ($offerta_interessata->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione") == $offerta_carrello_cookie["inizioApplicazione"]) && ($offerta_interessata->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione") == $offerta_carrello_cookie["fineApplicazione"])) {
												if(($offerta_interessata->getElementsByTagName("bonus")->length!=0 && array_key_exists("numeroCrediti", $offerta_carrello_cookie)) && ($offerta_interessata->getElementsByTagName("numeroCrediti")->item(0)->textContent == $offerta_carrello_cookie["numeroCrediti"])) {
													$offerta_presente=true;
													
													// ***
													require("./sistemazione_contenuto_carrello.php");
												}
												else {
													if($offerta_interessata->getElementsByTagName("bonus")->length==0 && !array_key_exists("numeroCrediti", $offerta_carrello_cookie)) {
														$offerta_presente=true;
														
														// ***
														require("./sistemazione_contenuto_carrello.php");
													}
												}
											}
										}
									}
								}
								else {
									if($offerta_interessata->getElementsByTagName("sconto")->length==0 && !(array_key_exists("scontoATempo", $offerta_carrello_cookie) && !array_key_exists("scontoFuturo", $offerta_carrello_cookie))) {
										if(($offerta_interessata->getElementsByTagName("bonus")->length!=0 && array_key_exists("numeroCrediti", $offerta_carrello_cookie)) && ($offerta_interessata->getElementsByTagName("numeroCrediti")->item(0)->textContent == $offerta_carrello_cookie["numeroCrediti"])) {
											$offerta_presente=true;
											
											// ***
											require("./sistemazione_contenuto_carrello.php");
										}
										else {
											if($offerta_interessata->getElementsByTagName("bonus")->length==0 && !array_key_exists("numeroCrediti", $offerta_carrello_cookie)) {
												$offerta_presente=true;
												
												// ***
												require("./sistemazione_contenuto_carrello.php");
											}
										}
									}
								}
							}
						}
						else
							break;
					}
				}
				
				// GIUNTI A QUESTO PUNTO, OLTRE A INSERIRE TUTTE QUELLE PROPOSTE DI VENDITA PER CUI NON SONO STATE RILEVATE DELLE SOMIGLIANZE, SARÀ POSSIBILE RIMUOVERE IL COOKIE DI INTERESSE
				$offerta_presente=false;
				
				foreach ($carrello_Offerte as $offerta_carrello_cookie) {
					// ***
					require("./sistemazione_contenuto_carrello.php");
				}
				
				setcookie("carrello_Offerte", "", time()-60);
				
			}
		}
	}
	else {
		// SE L'UTENTE CHE SI È AUTENTICATO RISULTA ESSERE UN GESTORE O UN AMMINISTRATORE, ALLORA SARÀ NECESSARIO ELIMINARE IL COOKIE DI CUI SOPRA SENZA CONSIDERARNE IL CONTENUTO
		if(isset($_COOKIE["carrello_Offerte"])) {
			setcookie("carrello_Offerte", "", time()-60);
		}
	}
?>