<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php 
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTI I DETTAGLI DETTAGLI INERENTI ALLA PROPOSTA DI VENDITA SELEZIONATA 

	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");

	// PER QUESTIONI DI SICUREZZA, È STATO NECESSARIO L'INSERIMENTO DELLO SCRIPT "public_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA
	// CONTRARIAMENTE ALLA SUA CONTROPARTE, OVVERO QUELLA COLLOCATA IN TUTTE LE PAGINE CHE COMPONGONO L'AREA RISERVATA, IL CONTROLLO, IN CASO DI FALLIMENTO, NON REINDERIZZERÀ VERSO UN'ALTRA PAGINA DELLA PIATTAFORMA. INFATTI, LA SCHERMATA IN QUESTIONE DOVRÀ ESSERE VISIBILE A PRESCINDERE DAL FATTO CHE L'UTENTE SI SIA AUTENTICATO O MENO	
	require_once("./public_session_control.php");
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");
	
	// I CLIENTI DELLA PIATTAFORMA POSSONO SUBIRE UNA SOSPENSIONE DEL PROFILO A CAUSA DEL LORO COMPORTAMENTO. PROPRIO PER QUESTO, E CONSIDERANDO CHE CIÒ PUÒ AVVENIRE IN QUALUNQUE MOMENTO, BISOGNERÀ MONITORARE COSTANTEMENTE I LORO "PERMESSI" COSÌ DA IMPEDIRNE LA NAVIGAZIONE VERSO LE SEZIONI PIÙ SENSIBILI DEL SITO 
	require_once("./monitoraggio_stato_account.php");
	
	// LE PROPOSTE DI VENDITA, DATA LA LORO STRUTTURA, POTREBBERO ESSERE SOGGETTE A DELLE RIDUZIONI DI PREZZO, CARATTERIZZATE DA UN INTERVALLO DI VALIDITÀ BEN DEFINITO E TALVOLTA APPLICABILI SUL PROSSIMO ACQUISTO. PERTANTO, NELLE IPOTESI IN CUI NON SIA SEMPRE POSSIBILE RIMUOVERE MANUALMENTE LE VOCI INTERESSATE, ABBIAMO DECISO DI IMPLEMENTARE UN CODICE IN GRADO DI INTERVENIRE IN AUTOMATICO SU TUTTI QUELLI SCONTI NON PIÙ USUFRUIBILI DAI CLIENTI DELLA PIATTAFORMA
	require_once("./rimozione_sconti.php");
	
	// LA PAGINA, ESSENDO RAGGIUNGIBILE DOPO AVER SELEZIONATO UNA DETERMINATA PROPOSTA DI VENDITA, NECESSITA DI UN ULTERIORE CONTROLLO PER APPURARE SE È STATA EFFETTIVAMENTE PRESA UNA DECISIONE IN MERITO O MENO 
	if(!isset($_GET["id_Offerta"])) 
		header("Location: index.php");
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_piattaforme_videogiochi.php");
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_recensioni.php");
	require_once("./apertura_file_discussioni.php");
	require_once("./apertura_file_categorie_libri.php");
	require_once("./apertura_file_generi_videogiochi.php");
	require_once("./apertura_file_acquisti.php");
	require_once("./apertura_file_carrelli.php");
	
	// NELL'OTTICA DI VOLER MANTENERE UN CERTO LIVELLO DI ROBUSTEZZA, ABBIAMO DECISO DI INTRODURRE DEI CONTROLLI PER VALUTARE SE L'OFFERTA A CUI SI RIFERISCE L'IDENTIFICATORE ESISTE REALMENTE O MENO
	$offerta_individuata=false;
	
	for($i=0; $i<$offerte->length; $i++) {
		$offerta=$offerte->item($i);
		
		// UNA VOLTA INDIVIDUATA LA PROPOSTA DI INTERESSE, SI POTRÀ INTERROMPERE LA RICERCA, IN QUANTO L'ENTITÀ CHE LA RAPPRESENTA SARÀ IMPIEGATA ALL'INTERNO DI SUCCESSIVE OPERAZIONI
		if($offerta->getAttribute("id")==$_GET["id_Offerta"]) {
			$offerta_individuata=true;
			break;
		}
	}
	
	if($offerta_individuata==false)
		header("Location: index.php");
	
	// UNA VOLTA PORTATI A TERMINE I CONTROLLI PRELIMINARI, SARÀ NECESSARIO REPERIRE TUTTE LE INFORMAZIONI INERENTI AL PRODOTTO COINVOLTO NELLA PROPOSTA DI VENDITA DI INTERESSE
	for($i=0; $i<$prodotti->length; $i++) {
		$prodotto=$prodotti->item($i);
		
		if($prodotto->getAttribute("id")==$offerta->getAttribute("idProdotto"))
			break;
	}
	
	// UNA VOLTA CONFERMATE LE PROPRIE SCELTE, BISOGNA PROCEDERE CON I CONTROLLI PER VALUTARE LA CORRETTEZZA E L'INTEGRITÀ DEI VALORI INDICATI ALL'INTERNO DEI VARI CAMPI
	if(isset($_POST["confirm"])) {
		
		// IL PRIMO PASSO CONSISTE NEL VALUTARE IL CONTENUTO DEL CAMPO INERENTE AL NUMERO DI PEZZI SELEZIONATI, IL QUALE, OLTRE A DOVER ESSERE UN VALORE INTERO POSITIVO, NON POTRÀ ECCEDERE IL LIMITE IMPOSTO PER QUELLA DETERMINATA PROPOSTA DI VENDITA
		if($offerta->getElementsByTagName("quantitativo")->item(0)->textContent>0) {
			if(in_array($_POST["quantitativo"], range(1, $offerta->getElementsByTagName("quantitativo")->item(0)->textContent))) {
				
				// PER DI PIÙ, SARÀ NECESSARIO DISTINGUERE LE MODALITÀ CON CUI INSERIRE LA PROPOSTA DI VENDITA ALL'INTERNO DEL CARRELLO, IN QUANTO SIA I CLIENTI CHE I SEMPLICI VISITATORI POSSONO FARNE RICHIESTA
				if(isset($_SESSION["id_Utente"]) && $_SESSION["tipo_Utente"]=="C") {
					
					// PRIMA DI INSERIRE L'OFFERTA ALL'INTERNO DEL CARRELLO DI INTERESSE, BISOGNERÀ DETERMINARE SE SI TRATTA, O MENO, DI UN ELEMENTO GIÀ PRESENTE AL SUO INTERNO
					for($i=0; $i<$carrelli->length; $i++) {
						$carrello=$carrelli->item($i);
						
						if($carrello->getAttribute("idCliente")==$_SESSION["id_Utente"])
							break;
						
					}
					
					// AL FINE DI POTER EFFETTUARE CORRETTAMENTE LE SUCCESSIVE OPERAZIONI, E STANDO A QUANTO DETTO FINORA, È STATO NECESSARIO DEFINIRE UNO SCRIPT IN GRADO DI STABILIRE SE UNA DETERMINATA PROPOSTA DI VENDITA APPARTIENE GIÀ AL CARRELLO DELL'UTENTE INTERESSATO 
					require("./ricerca_offerta_nel_carrello.php");
					
					
					// IN BASE ALL'ESITO DEI PRECEDENTI CONFRONTI, SAREMO IN GRADO DI STABILIRE SE È NECESSARIO INCREMENTARE IL QUANTITATIVO DI UN'OFFERTA ALL'INTERNO DEL CARRELLO OPPURE SE INSERIRNE UNA NUOVA 
					if($offerta_presente) {
						// CONTESTUALMENTE ALLA PRECEDENTE OPERAZIONE, BISOGNERÀ DIMINUIRE DELLO STESSO AMMONTARE IL NUMERO DI PEZZI L'ENTITÀ CHE RAPPRESENTA L'OFFERTA ALL'INTERNO DEL CATALOGO
						$nuovo_quantitativo=$docOfferte->createElement("quantitativo", intval($offerta->getElementsByTagName("quantitativo")->item(0)->textContent)-$_POST["quantitativo"]);
						
						// QUALORA IL NUOVO QUANTITATIVO DOVESSE ESSERE PARI MINORE DI ZERO, SARÀ NECESSARIO RIPORTARE A SCHERMO UN MESSAGGO INERENTE ALL'ESAURIMENTO DELLE SCORTE
						// N.B.: TALE CONTROLLO È STATO INSERITO PER LIMITARE IL NUMERO DI PROBLEMATICHE DERVIANTI DALLA CONCORRENZA DELLE VARIE RICHIESTE
						if($nuovo_quantitativo->textContent<0) {
							// ***
							$scorte_esaurite=true;
						} 
						else {
							$offerta->replaceChild($nuovo_quantitativo, $offerta->getElementsByTagName("quantitativo")->item(0));
							
							$nuovo_quantitativo=$docCarrelli->createElement("quantitativo", $_POST["quantitativo"]+intval($offerta_carrello->getElementsByTagName("quantitativo")->item(0)->textContent));
						
							$offerta_carrello->replaceChild($nuovo_quantitativo, $offerta_carrello->getElementsByTagName("quantitativo")->item(0));
					
							// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEI DOCUMENTI APPENA AGGIORNATI IN RELAZIONE A QUANTO ESPOSTO NEI RELATIVI SCHEMA
							if($docCarrelli->schemaValidate("../../XML/Schema/Carrelli_Clienti.xsd") && $docOfferte->schemaValidate("../../XML/Schema/Offerte.xsd")){
								
								// AL FINE DI GARANTIRE UNA STAMPA GODIBILE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
								$docCarrelli->preserveWhiteSpace = false;
								$docCarrelli->formatOutput = true;
								$docCarrelli->save("../../XML/Carrelli_Clienti.xml");
								
								// ***
								$docOfferte->preserveWhiteSpace = false;
								$docOfferte->formatOutput = true;
								$docOfferte->save("../../XML/Offerte.xml");
								
								// PRIMA DI ESSERE REINDIRIZZATI, SI PREDISPONE UN COOKIE CHE SARÀ USATO COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
								setcookie("modifica_Effettuata", true);
								
								header("Location: riepilogo_carrello.php");
							}
							else {
								
								// ***
								setcookie("errore_Validazione", true);
								
								header("Location: index.php");
							}
						}
					}
					else {
						// NEL CASO IN CUI LE PRECEDENTI NON SIANO ESATTAMENTE LE STESSE, SI PROCEDERÀ CON L'INSERIMENTO DELLA NUOVA OFFERTA, LE CUI INFORMAZIONI, A MENO DEL QUANTITATIVO CHE SARÀ PARI AL NUMERO DI PEZZI FORNITI DAL CLIENTE, VERRANNO PRELEVATE DA QUELLA DI PARTENZA 
						$nuova_offerta_carrello=$docCarrelli->createElement("offerta");
						$nuova_offerta_carrello->setAttribute("id", $offerta->getAttribute("id"));
						$nuova_offerta_carrello->setAttribute("idProdotto", $offerta->getAttribute("idProdotto"));
						
						$nuova_offerta_carrello->appendChild($docCarrelli->createElement("prezzoContabile", $offerta->firstChild->textContent));
						
						if($offerta->getElementsByTagName("sconto")->length!=0) {
							$sconto=$docCarrelli->createElement("sconto");
							
							if($offerta->getElementsByTagName("scontoATempo")->length!=0) {
								$scontoATempo=$docCarrelli->createElement("scontoATempo");
								$scontoATempo->setAttribute("percentuale", $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"));
								$scontoATempo->setAttribute("inizioApplicazione", $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione"));
								$scontoATempo->setAttribute("fineApplicazione", $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione"));
								
								$sconto->appendChild($scontoATempo);
							}
							else {
								$scontoFuturo=$docCarrelli->createElement("scontoFuturo");
								$scontoFuturo->setAttribute("percentuale", $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale"));
								$scontoFuturo->setAttribute("inizioApplicazione", $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione"));
								$scontoFuturo->setAttribute("fineApplicazione", $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione"));
								
								$sconto->appendChild($scontoFuturo);
							}
							
							$nuova_offerta_carrello->appendChild($sconto);
							
						}

						$nuova_offerta_carrello->appendChild($docCarrelli->createElement("quantitativo", $_POST["quantitativo"]));
						
						if($offerta->getElementsByTagName("bonus")->length!=0) {
							$bonus=$docCarrelli->createElement("bonus");
							$bonus->appendChild($docCarrelli->createElement("numeroCrediti", $offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent));
							$nuova_offerta_carrello->appendChild($bonus);
						}
						
						$carrello->appendChild($nuova_offerta_carrello);
						
						// ***
						$nuovo_quantitativo=$docOfferte->createElement("quantitativo", intval($offerta->getElementsByTagName("quantitativo")->item(0)->textContent)-$_POST["quantitativo"]);
						
						// ***
						if($nuovo_quantitativo->textContent<0) {
							// ***
							$scorte_esaurite=true;
						} 
						else {
							$offerta->replaceChild($nuovo_quantitativo, $offerta->getElementsByTagName("quantitativo")->item(0));
							
							// ***
							if($docCarrelli->schemaValidate("../../XML/Schema/Carrelli_Clienti.xsd") && $docOfferte->schemaValidate("../../XML/Schema/Offerte.xsd")){
								
								// ***
								$docCarrelli->preserveWhiteSpace = false;
								$docCarrelli->formatOutput = true;
								$docCarrelli->save("../../XML/Carrelli_Clienti.xml");
								
								// ***
								$docOfferte->preserveWhiteSpace = false;
								$docOfferte->formatOutput = true;
								$docOfferte->save("../../XML/Offerte.xml");
								
								// ***
								setcookie("modifica_Effettuata", true);
								
								header("Location: riepilogo_carrello.php");
							}
							else {
								
								// ***
								setcookie("errore_Validazione", true);
								
								header("Location: index.php");
							}
						}
					}
				}
				// STANDO AI CONTROLLI RIPORTATI PIÙ IN BASSO, I GESTORI E L'AMMINISTRATORE DEL SITO SARANNO ESCLUSI DALLA SEGUENTE CASISTICA, POICHÈ SIA IL PULSANTE CHE IL SELETTORE DA CUI POTER SPUNTARE UNA CERTA VOCE NON SARANNO PRESENTI NELLA LORO PAGINA. PROPRIO PER QUESTO, SARÀ POSSIBILE CONCENTRARSI ESCLUSIVAMENTE SUI VISITATORI DEL SITO
				else {
					// PER POTER GESTIRE AL MEGLIO UNA SIMILE EVENIENZA, SI HA AVUTO IL BISOGNO DI DEFINIRE UN COOKIE, ALL'INTERNO DEL QUALE ANDARE A MEMORIZZARE TUTTE LE INFORMAZIONI INERENTI ALLE SINGOLE OFFERTE. NEL DETTAGLIO, È STATO RITENUTO OPPORTUNO COMPORRE DELLE COPPIE RAFFIGURANTI: L'IDENTIFICATORE DELLA CELLA D'INTERESSE E IL CONTENUTO DI UNA CERTA PROPOSTA DI VENDITA, LE CUI COMPONENTI SONO STATE ORGANIZZATE A LORO VOLTA ALL'INTERNO DI UN VETTORE ASSOCIATIVO 
					// ALLO SCOPO DI MANTENERE E CARATTERIZZARE UN ELEMENTO DEL GENERE, ABBIAMO DOVUTO UTILIZZARE IL METODO serialize(...) CHE, OLTRE A PERMETTERNE LA CONVERSIONE IN STRINGA, È IN GRADO ANCHE DI MANTENERNE I RIFERIMENTI IN RELAZIONE AL TIPO E ALLA STRUTTURA, COSÌ DA POTERLO RIPRISTINARE IN OGNI MOMENTO TRAMITE LA FUNZIONE unserialize(...)
					if(isset($_COOKIE["carrello_Offerte"])) {
						
						// SE IL VISITATORE HA GIÀ INSERITO QUALCOSA ALL'INTERNO DEL PROPRIO CARRELLO, SARÀ NECESSARIO AGGIORNARE IL CONTENUTO DEL COOKIE CREATO IN PRECEDENZA
						$carrello_Offerte=unserialize($_COOKIE["carrello_Offerte"]);
						
						// POICHÈ SI HA A CHE FARE CON UN VETTORE, L'INSERIMENTO DI UN NUOVO ELEMENTO DOVRÀ ESSERE REALIZZATO MEDIANTE IL METODO array_push(...), A CUI SEGUIRANNO DEI PASSI MOLTO SIMILI A QUELLI ELENCATI PIÙ IN BASSO. IN OGNI CASO, L'INTEGRAZIONE DEL RELATIVO FILE XML CON QUANTO PRESENTE ALL'INTERNO DEL COOKIE VERRÀ EFFETTUATO DOPO L'AUTENTICAZIONE DA PARTE DELL'UTENTE (CLIENTE)
						// N.B.: CONTRARIAMENTE AL CASO PRECEDENTE, ABBIAMO DECISO DI NON DIMINUIRE FIN DA SUBITO IL QUANTITATIVO DELL'OFFERTA SELEZIONATA ALL'INTERNO DEL DOCUMENTO CHE LA CONTIENE. INFATTI, NON SI È IN GRADO DI STABILIRE CON ESATTEZZA SE L'UTENTE RIUSCIRÀ O MENO A PROFILARSI PRIMA DELLA RIMOZIONE DEL COOKIE. L'UNICO CONTROLLO CHE È STATO POSSIBILE APPLICARE CONSISTE NEL VALUTARE, VOLTA PER VOLTA, IL NUMERO DI PEZZI INERENTI ALLE PROPOSTE DI VENDITA PRESENTI NEL COOKIE E IMPEDIRE CHE QUESTI, A SEGUITO DI UN CERTO INCREMENTO, SUPERINO IL QUANTITATIVO DELL'OFFERTA DI PARTENZA      
						if($offerta->getElementsByTagName("sconto")->length!=0) {
							if($offerta->getElementsByTagName("scontoATempo")->length!=0) {
								if($offerta->getElementsByTagName("bonus")->length!=0) {
									
									// ***
									require("./ricerca_offerta_nel_carrello.php");
									
									if(!$offerta_presente) {
										array_push($carrello_Offerte, array("id" => $offerta->getAttribute("id"), "idProdotto" => $offerta->getAttribute("idProdotto"), "prezzoContabile" => $offerta->firstChild->textContent, "scontoATempo" => $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"), "inizioApplicazione" => $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione"), "fineApplicazione" => $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione"), "quantitativo" => $_POST["quantitativo"], "numeroCrediti" => $offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent));
										
										setcookie("carrello_Offerte", serialize($carrello_Offerte));
									
										// ***
										setcookie("modifica_Effettuata", true);
										
										header("Location: riepilogo_carrello.php");
									}
									else {
										if(!($carrello_Offerte[$i]["quantitativo"]+$_POST["quantitativo"]>$offerta->getElementsByTagName("quantitativo")->item(0)->textContent)) {
											$carrello_Offerte[$i]["quantitativo"]+=$_POST["quantitativo"];
											
											setcookie("carrello_Offerte", serialize($carrello_Offerte));
									
											// ***
											setcookie("modifica_Effettuata", true);
											
											header("Location: riepilogo_carrello.php");
											
										}
										else {
											// ***
											$quantitativo_errato=true;
										} 
									}
								}
								else {
									// ***
									require("./ricerca_offerta_nel_carrello.php");
									
									if(!$offerta_presente) {
										array_push($carrello_Offerte, array("id" => $offerta->getAttribute("id"), "idProdotto" => $offerta->getAttribute("idProdotto"), "prezzoContabile" => $offerta->firstChild->textContent, "scontoATempo" => $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"), "inizioApplicazione" => $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione"), "fineApplicazione" => $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione"), "quantitativo" => $_POST["quantitativo"]));
										
										setcookie("carrello_Offerte", serialize($carrello_Offerte));
									
										// ***
										setcookie("modifica_Effettuata", true);
										
										header("Location: riepilogo_carrello.php");
									}
									else {
										if(!($carrello_Offerte[$i]["quantitativo"]+$_POST["quantitativo"]>$offerta->getElementsByTagName("quantitativo")->item(0)->textContent)) {
											$carrello_Offerte[$i]["quantitativo"]+=$_POST["quantitativo"];
											
											setcookie("carrello_Offerte", serialize($carrello_Offerte));
									
											// ***
											setcookie("modifica_Effettuata", true);
											
											header("Location: riepilogo_carrello.php");
											
										}
										else {
											// ***
											$quantitativo_errato=true;
										} 
									}
								}
							}
							else {
								if($offerta->getElementsByTagName("bonus")->length!=0) {
									// ***
									require("./ricerca_offerta_nel_carrello.php");
									
									if(!$offerta_presente) {
										array_push($carrello_Offerte, array("id" => $offerta->getAttribute("id"), "idProdotto" => $offerta->getAttribute("idProdotto"), "prezzoContabile" => $offerta->firstChild->textContent, "scontoFuturo" => $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale"), "inizioApplicazione" => $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione"), "fineApplicazione" => $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione"), "quantitativo" => $_POST["quantitativo"], "numeroCrediti" => $offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent));
									
										setcookie("carrello_Offerte", serialize($carrello_Offerte));
									
										// ***
										setcookie("modifica_Effettuata", true);
										
										header("Location: riepilogo_carrello.php");
									}
									else {
										if(!($carrello_Offerte[$i]["quantitativo"]+$_POST["quantitativo"]>$offerta->getElementsByTagName("quantitativo")->item(0)->textContent)) {
											$carrello_Offerte[$i]["quantitativo"]+=$_POST["quantitativo"];
											
											setcookie("carrello_Offerte", serialize($carrello_Offerte));
									
											// ***
											setcookie("modifica_Effettuata", true);
											
											header("Location: riepilogo_carrello.php");
											
										}
										else {
											// ***
											$quantitativo_errato=true;
										} 
									}
								}
								else {
									// ***
									require("./ricerca_offerta_nel_carrello.php");
									
									if(!$offerta_presente) {
										array_push($carrello_Offerte, array("id" => $offerta->getAttribute("id"), "idProdotto" => $offerta->getAttribute("idProdotto"), "prezzoContabile" => $offerta->firstChild->textContent, "scontoFuturo" => $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale"), "inizioApplicazione" => $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione"), "fineApplicazione" => $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione"), "quantitativo" => $_POST["quantitativo"]));
									
										setcookie("carrello_Offerte", serialize($carrello_Offerte));
									
										// ***
										setcookie("modifica_Effettuata", true);
										
										header("Location: riepilogo_carrello.php");
									}
									else {
										if(!($carrello_Offerte[$i]["quantitativo"]+$_POST["quantitativo"]>$offerta->getElementsByTagName("quantitativo")->item(0)->textContent)) {
											$carrello_Offerte[$i]["quantitativo"]+=$_POST["quantitativo"];	
											
											setcookie("carrello_Offerte", serialize($carrello_Offerte));
									
											// ***
											setcookie("modifica_Effettuata", true);
											
											header("Location: riepilogo_carrello.php");
											
										}
										else {
											// ***
											$quantitativo_errato=true;
										} 
									}
								}
							}
						}
						else {
							if($offerta->getElementsByTagName("bonus")->length!=0) {
								// ***
								require("./ricerca_offerta_nel_carrello.php");
								
								if(!$offerta_presente) {
									array_push($carrello_Offerte, array("id" => $offerta->getAttribute("id"), "idProdotto" => $offerta->getAttribute("idProdotto"), "prezzoContabile" => $offerta->firstChild->textContent, "quantitativo" => $_POST["quantitativo"], "numeroCrediti" => $offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent));
								
									setcookie("carrello_Offerte", serialize($carrello_Offerte));
									
									// ***
									setcookie("modifica_Effettuata", true);
									
									header("Location: riepilogo_carrello.php");
								}
								else {
									if(!($carrello_Offerte[$i]["quantitativo"]+$_POST["quantitativo"]>$offerta->getElementsByTagName("quantitativo")->item(0)->textContent)) {
										$carrello_Offerte[$i]["quantitativo"]+=$_POST["quantitativo"];	
										
										setcookie("carrello_Offerte", serialize($carrello_Offerte));
								
										// ***
										setcookie("modifica_Effettuata", true);
										
										header("Location: riepilogo_carrello.php");
										
									}
									else {
										// ***
										$quantitativo_errato=true;
									} 
								}
							}
							else {
								// ***
								require("./ricerca_offerta_nel_carrello.php");
								
								if(!$offerta_presente) {
									array_push($carrello_Offerte, array("id" => $offerta->getAttribute("id"), "idProdotto" => $offerta->getAttribute("idProdotto"), "prezzoContabile" => $offerta->firstChild->textContent, "quantitativo" => $_POST["quantitativo"]));
								
									setcookie("carrello_Offerte", serialize($carrello_Offerte));
									
									// ***
									setcookie("modifica_Effettuata", true);
									
									header("Location: riepilogo_carrello.php");
								}
								else {
									if(!($carrello_Offerte[$i]["quantitativo"]+$_POST["quantitativo"]>$offerta->getElementsByTagName("quantitativo")->item(0)->textContent)) {
										$carrello_Offerte[$i]["quantitativo"]+=$_POST["quantitativo"];
										
										setcookie("carrello_Offerte", serialize($carrello_Offerte));
								
										// ***
										setcookie("modifica_Effettuata", true);
										
										header("Location: riepilogo_carrello.php");
										
									}
									else {
										// ***
										$quantitativo_errato=true;
									} 
								}
							}
						}
					}
					else {
						// PRIMA DI PROCEDERE CON LA CREAZIONE DEL COOKIE, BISOGNA DETERMINARE QUALE SONO LE COMPONENTI CHE DESCRIVONO L'OFFERTA DI RIFERIMENTO
						if($offerta->getElementsByTagName("sconto")->length!=0) {
							if($offerta->getElementsByTagName("scontoATempo")->length!=0) {
								if($offerta->getElementsByTagName("bonus")->length!=0) {
									setcookie("carrello_Offerte", serialize(array(1 => array("id" => $offerta->getAttribute("id"), "idProdotto" => $offerta->getAttribute("idProdotto"), "prezzoContabile" => $offerta->firstChild->textContent, "scontoATempo" => $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"), "inizioApplicazione" => $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione"), "fineApplicazione" => $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione"), "quantitativo" => $_POST["quantitativo"], "numeroCrediti" => $offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent))));
									
									// ***
									setcookie("modifica_Effettuata", true);
									
									header("Location: riepilogo_carrello.php");
								}
								else {
									setcookie("carrello_Offerte", serialize(array(1 => array("id" => $offerta->getAttribute("id"), "idProdotto" => $offerta->getAttribute("idProdotto"), "prezzoContabile" => $offerta->firstChild->textContent, "scontoATempo" => $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"), "inizioApplicazione" => $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione"), "fineApplicazione" => $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione"), "quantitativo" => $_POST["quantitativo"]))));
								
									// ***
									setcookie("modifica_Effettuata", true);
									
									header("Location: riepilogo_carrello.php");
								}
							}
							else {
								if($offerta->getElementsByTagName("bonus")->length!=0) {
									setcookie("carrello_Offerte", serialize(array(1 => array("id" => $offerta->getAttribute("id"), "idProdotto" => $offerta->getAttribute("idProdotto"), "prezzoContabile" => $offerta->firstChild->textContent, "scontoATempo" => $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale"), "inizioApplicazione" => $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione"), "fineApplicazione" => $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione"), "quantitativo" => $_POST["quantitativo"], "numeroCrediti" => $offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent))));
								
									// ***
									setcookie("modifica_Effettuata", true);
									
									header("Location: riepilogo_carrello.php");
								}
								else {
									setcookie("carrello_Offerte", serialize(array(1 => array("id" => $offerta->getAttribute("id"), "idProdotto" => $offerta->getAttribute("idProdotto"), "prezzoContabile" => $offerta->firstChild->textContent, "scontoFuturo" => $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale"), "inizioApplicazione" => $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione"), "fineApplicazione" => $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione"), "quantitativo" => $_POST["quantitativo"]))));
								
									// ***
									setcookie("modifica_Effettuata", true);
									
									header("Location: riepilogo_carrello.php");
								}
							}
						}
						else {
							if($offerta->getElementsByTagName("bonus")->length!=0) {
								setcookie("carrello_Offerte", serialize(array(1 => array("id" => $offerta->getAttribute("id"), "idProdotto" => $offerta->getAttribute("idProdotto"), "prezzoContabile" => $offerta->firstChild->textContent, "quantitativo" => $_POST["quantitativo"], "numeroCrediti" => $offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent))));
							
								// ***
								setcookie("modifica_Effettuata", true);
								
								header("Location: riepilogo_carrello.php");
							
							}
							else {
								setcookie("carrello_Offerte", serialize(array(1 => array("id" => $offerta->getAttribute("id"), "idProdotto" => $offerta->getAttribute("idProdotto"), "prezzoContabile" => $offerta->firstChild->textContent, "quantitativo" => $_POST["quantitativo"]))));
								
								// ***
								setcookie("modifica_Effettuata", true);
								
								header("Location: riepilogo_carrello.php");
							}
						}
					}
				}
			}
			else {
				// ***
				$quantitativo_errato=true;
			}
		}
		else {
			// ***
			$scorte_esaurite=true;
		}
	}
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>LEV: Libri &amp; Videogiochi</title>
		<link rel="icon" href="../../Immagini/Icona_LEV.png" />
		<link rel="stylesheet" href="../../Stili CSS/style_schede_offerte.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_form.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_intestazione_sito.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_common.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_footer_sito.css" type="text/css" />
		<script type="text/javascript" src="../JavaScript/gestioneMenuTendina.js"></script>
	</head>
	<body>
		<?php 
			// DATA LA VARIETÀ DELLE CASISTICHE CHE POSSONO MANIFESTARE, ABBIAMO DEFINITO UNA GERARCHIA DI CONTROLLI PER GESTIRE AL MEGLIO LE STAMPE DEI VARI MESSAGGI DI POPUP, I QUALI, MEDIANTE UNA SIMILE IMPOSTAZIONE, SARANNO PRESENTATI SINGOLARMENTE AI SOGGETTI INTERESSATI
			if(isset($_SESSION["modifica_Effettuata"])) {
				
				// IN OCCASIONE DEL POSSIBILE RICARICAMENTO DELLA PAGINA, SI PROCEDE CON LA RIMOZIONE DEL FLAG AL SOLO SCOPO DI NON RIPROPORRE LA MEDESIMA NOTIFCA 
				unset($_SESSION["modifica_Effettuata"]);
				
				echo "<div class=\"confirm_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/check-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"con\">OTTIMO!</p>\n";
				echo "\t\t\t\t\t<p>OPERAZIONE EFFETTUATA CON SUCCESSO!</p>\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t</div>\n";
				echo "\t\t</div>\n";
			}
			else {
				// ***
				if(isset($_COOKIE["errore_Validazione"]) && $_COOKIE["errore_Validazione"]) {
					// ***
					setcookie("errore_Validazione","",time()-60);
					
					echo "<div class=\"error_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
					echo "\t\t\t\t\t<p>VALIDAZIONE DEL/I FILE NON RIUSCITA... SI CONSIGLIA DI RIPRISTINARE LE STRUTTURE DATI!</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
				}
				else {
					// ***
					if(isset($scorte_esaurite) && $scorte_esaurite) {
						// ***
						$scorte_esaurite=false;
						
						echo "<div class=\"error_message\">\n";
						echo "\t\t\t<div class=\"container_message\">\n";
						echo "\t\t\t\t<div class=\"container_img\">\n";
						echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t\t<div class=\"message\">\n";
						echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
						echo "\t\t\t\t\t<p>LE SCORTE DEL PRODOTTO SONO TERMINATE...</p>\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t</div>\n";
						echo "\t\t</div>\n";
					}
					else {
						// ***
						if(isset($quantitativo_errato) && $quantitativo_errato) {
							// ***
							$quantitativo_errato=false;
							
							echo "<div class=\"error_message\">\n";
							echo "\t\t\t<div class=\"container_message\">\n";
							echo "\t\t\t\t<div class=\"container_img\">\n";
							echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t\t<div class=\"message\">\n";
							echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
							echo "\t\t\t\t\t<p>I PEZZI INDICATI NON RAPPRESENTANO UN'OPZIONE VALIDA...</p>\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t</div>\n";
							echo "\t\t</div>\n";
						}
					}
				}
			}
		
			// L'INTESTAZIONE DEL SITO, POICHÈ SI TRATTA DI UN DETTAGLIO RICORRENTE, È STATA DEFINITA ALL'INTERNO DI UN PROGRAMMA INDIPENDENTE CHE, DATE LE ESIGENZE, VERRÀ INCLUSO A PIÙ RIPRESE NEI VARI SCRIPT
			require_once("./intestazione_sito.php"); 
		?>
		<div class="corpo_pagina">
			<div class="container_corpo_pagina">
				<div class="schede_offerte">
					<div class="container_schede_offerte">
						<div class="intestazione_schede_offerte">
							<div class="container_intestazione_schede_offerte">
								<span class="icona_schede_offerte">
									<img src="../../Immagini/file-invoice-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Visualizza i dettagli dell'offerta selezionata!</h2>
							</div>
						</div>
						<div class="contenuto_schede_offerte">
							<div class="elenco_schede_offerte">
								<div class="elenco_campi">
									<div class="container_elenco_campi" style="border: 0em; padding-bottom: 0em; margin-bottom: 0em;" >
										<div class="campo">
											<div class="container_campo" style="margin-left: 0em; margin-right: 0em;">
												<div class="dettagli_scheda_offerta">
													<div class="container_dettagli_scheda_offerta">
														<div class="intestazione_dettagli_scheda_offerta">
															<?php
																// IL PRIMO ELEMENTO CHE COMPONE LA SCHEDA DI UNA CERTA PROPOSTA DI VENDITA CONSISTE NELL'IMMAGINE DEL PRODOTTO COINVOLTO, LA QUALE PUÒ ESSRE REPERITA MEDIANTE LA RELATIVA STRUTTURA DATI 
																echo "<img src=\"".$prodotto->getElementsByTagName("immagine")->item(0)->textContent."\" alt=\"Immagine Non Disponibile...\" />\n";
															?>
														</div>
														<div class="intestazione_dettagli_scheda_offerta">
															<div class="dettagli_principali_scheda_offerta">
																<h1>
																	<span class="titolo_prodotto"><?php echo $prodotto->firstChild->textContent; ?></span>
																	<span class="sottotitolo_prodotto">
																		<?php
																			// IN BASE AL TIPO DI PRODOTTO, SARANNO PRESENTATE DELLE DICITURE DIFFERENTI, RELATIVE AGLI ASPETTI CARDINI IN GRADO DI DISTINGUERLI GLI UNI DAGLI ALTRI
																			// PER OGNI OPERA CARTACEA, SARÀ PRESENTATO A SCHERMO L'ANNO DI PRODUZIONE DELLE COPIE COINVOLTE. D'ALTRO CANTO, QUELLE VIDEOLUDICHE RIPORTERANNO LA PIATTAFORMA DI GIOCO SU CUI È POSSIBILE MANDARLE IN ESECUZIONE, LA QUALE, SE PREMUTA, PERMETTERÀ DI REINDIRIZZARE L'UTENTE VERSO LA PAGINA COMPOSTA DALLE SOLE OFFERTE CHE COINVOLGONO TUTTI QUEI I PRODOTTI CHE CONDIVIDONO UNA SIMILE SPECIFICA  
																			if($prodotto->getElementsByTagName("libro")->length!=0)
																				echo "<span style=\"margin-left: 0.25em;\">Copertina Flessibile</span> - ".$prodotto->getElementsByTagName("annoUscita")->item(0)->textContent."\n";
																			else {
																				for($i=0; $i<$piattaforme->length; $i++) {
																					$piattaforma=$piattaforme->item($i);
																					
																					if($prodotto->getElementsByTagName("piattaforma")->item(0)->getAttribute("idPiattaforma")==$piattaforma->getAttribute("id")) {
																						echo " - <a href=\"videogiochi_per_piattaforma.php?id_Piattaforma=".$piattaforma->getAttribute("id")."\">".$piattaforma->firstChild->textContent."</a>\n";
																						break;
																					}
																				}
																			}
																		?>
																	</span>
																</h1>
																<div class="dettagli_aggiuntivi_scheda_offerta">
																	<?php
																		// INOLTRE, MOLTO SIMILMENTE ALLE ALTRE SCHERMATE DI INTERESSE, VERRANNO STAMPATI GLI AUTORI CHE, OLTRE AD ESSERE SUDDIVISI IN BASE AL LORO CONTRIBUTO, HANNO PORTATO ALLA STESURA DI LIBRO COINVOLTO O LA CASA DI PRODUZIONE CHE SI È OCCUPATA DELLO SVILUPPO DEL SOFTWARE IN ESAME
																		if($prodotto->getElementsByTagName("libro")->length)
																		{
																			echo "<span>di <span class=\"autori\">";
																			
																			for($i=0; $i<$prodotto->getElementsByTagName("autore")->length; $i++) {
																				
																				// LA DISTINZIONE CITATA IN PRECEDENZA PREVEDE CHE TUTTI COLORO CHE SONO STATI REGISTRATI PER PRIMI SIANO GLI AUTORI EFFETTIVI DELL'OPERA, PERTANTO I RESTANTI VERRANNO CONSIDERATI COME COAUTORI DI QUEST'ULTIMA
																				if($i==0)
																					$ruolo="<span style=\"color: rgb(119, 119, 119);\">(Autore)</span>";
																				else
																					$ruolo="<span style=\"color: rgb(119, 119, 119);\">(Coautore)</span>";
																				
																				if($i<$prodotto->getElementsByTagName("autore")->length-1)
																					echo $prodotto->getElementsByTagName("autore")->item($i)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($i)->lastChild->textContent." ".$ruolo.", ";
																				else
																					echo $prodotto->getElementsByTagName("autore")->item($i)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($i)->lastChild->textContent." ".$ruolo;
																			
																			}
																			
																			echo "</span></span>\n";
																		}
																		else
																		{
																			echo "<span>di <span style=\"font-weight: bold;\">".$prodotto->getElementsByTagName("casaProduzione")->item(0)->textContent." <span style=\"color: rgb(119, 119, 119);\">(Casa di produzione)</span></span></span>\n";
																		}
																	?>
																</div>
																<div class="dettagli_aggiuntivi_scheda_offerta">
																	<?php
																		// IN OGNI CASO, ABBIAMO RITENUTO OPPORTUNO ELENCARE TUTTI I GENERI LETTERARI O VIDELUDICI A CUI APPARTIENE IL PRODOTTO D'INTERESSE
																		// PER I PRIMI, È STATO IMPLEMENTATO UN MECCANISMO DI REINDIRIZZAMENTO MOLTO SIMILE A QUANTO DETTO PER LE PIATTAFORME DEI VIDEOGIOCHI 
																		if($prodotto->getElementsByTagName("libro")->length)
																		{
																			echo "<span>Generi: <span class=\"generi\">";
																			
																			for($i=0; $i<$prodotto->getElementsByTagName("categoria")->length; $i++) {
																				for($j=0; $j<$categorie->length; $j++) {
																					$categoria=$categorie->item($j);
																					
																					if($categoria->getAttribute("id")==$prodotto->getElementsByTagName("categoria")->item($i)->getAttribute("idCategoria")) {																						
																						if($i<$prodotto->getElementsByTagName("categoria")->length-1)
																							echo "<a style=\"color: rgb(255, 255, 255);\" href=\"libri_per_categoria.php?id_Categoria=".$categoria->getAttribute("id")."\">".$categoria->firstChild->textContent."</a>, ";
																						else
																							echo "<a style=\"color: rgb(255, 255, 255);\" href=\"libri_per_categoria.php?id_Categoria=".$categoria->getAttribute("id")."\">".$categoria->firstChild->textContent."</a>";
																					}
																				}
																			}
																			
																			echo "</span></span>\n";
																		}
																		else
																		{
																			echo "<span>Generi: <span class=\"generi\">";
																			
																			for($i=0; $i<$prodotto->getElementsByTagName("genere")->length; $i++) {
																				for($j=0; $j<$generi->length; $j++) {
																					$genere=$generi->item($j);
																					
																					if($genere->getAttribute("id")==$prodotto->getElementsByTagName("genere")->item($i)->getAttribute("idGenere")) {																						
																						if($i<$prodotto->getElementsByTagName("genere")->length-1)
																							echo $genere->firstChild->textContent.", ";
																						else
																							echo $genere->firstChild->textContent;
																					}
																				}
																			}
																			
																			echo "</span></span>\n";
																		}
																	?>
																</div>
																<div class="dettagli_aggiuntivi_scheda_offerta">
																	<?php
																		// LA QUALITÀ DI UN CERTO PRODOTTO PUÒ ESSERE DESCRITTA TENENDO CONTO DELLA MEDIA COMPLESSIVA CALCOLATA A PARTIRE DA QUELLE DEI SINGOLI PARAMETRI DI VALUTAZIONE, I QUALI VARIERANNO A SECONDA DEL TIPO DI PRODOTTO
																		// LE RECENSIONI TENUTE IN CONSIDERAZIONE SARANNO SOLO ED ESCLUSIVAMENTE QUELLE CHE NON RISULTANO ESSERE STATE MODERATE DAI GESTORI DEL SITO 
																		$num_recensioni=0;
																		
																		for($i=0; $i<$prodotto->getElementsByTagName("recensione")->length; $i++) {
																			$recensione_prodotto=$prodotto->getElementsByTagName("recensione")->item($i);
																			
																			for($j=0; $j<$recensioni->length; $j++) {
																				if($recensioni->item($j)->getAttribute("id")==$recensione_prodotto->getAttribute("idRecensione") && $recensioni->item($j)->getAttribute("moderata")=="No") {
																					$num_recensioni++;
																					break;
																				}
																			}
																		}
																		
																		require_once("./calcolo_media_recensioni_prodotto.php");
																		
																		if($prodotto->getElementsByTagName("libro")->length)
																		{
																			$media_recensioni=number_format((number_format($media_trama,1)+number_format($media_personaggi,1)+number_format($media_ambientazione,1))/3, 1,".","");
																		}
																		else {
																			$media_recensioni=number_format((number_format($media_sceneggiatura,1)+number_format($media_tecnica,1)+number_format($media_giocabilita,1))/3, 1,".","");
																		}
																		
																		echo "<span class=\"recensioni\">Recensioni degli utenti: <span class=\"valutazione\">".$media_recensioni."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\"/></span><span class=\"voti\" style=\"color: rgb(255, 255, 255);\">".$num_recensioni." voti</span></span>\n";
																	?>
																</div>
															</div>
															<div class="descrizione_prodotto_scheda_offerta">
																<div class="container_descrizione_prodotto_scheda_offerta">
																	<?php
																		// PER INTRODURRE LE VICENDE NARRATE DALL'OPERA IN QUESTIONE, ABBIAMO DECISO DI INSERIRE LA DESCRIZIONE COME DETTAGLIO A SUPPORTO DELLA VENDITA DEL PRODOTTO 
																		echo $prodotto->getElementsByTagName("descrizione")->item(0)->textContent."\n";
																	?>
																</div>
															</div>
														</div>
														<div class="intestazione_dettagli_scheda_offerta">
															<form class="anteprima_acquisto_scheda_offerta" action="<?php echo $_SERVER["PHP_SELF"]."?id_Offerta=".$_GET["id_Offerta"]; ?>" method="post">
																<div class="container_anteprima_acquisto_scheda_offerta">
																	<div class="contenuto_anteprima_acquisto_scheda_offerta">
																		<span class="elemento_anteprima_acquisto_scheda_offerta">
																			<?php
																				// PER OGNI PROPOSTA DI VENDITA, VERRANNO RIPORTATI TUTTI I DETTAGLI INERENTI: AL PREZZO (EVENTUALMENTE RIDOTTO TRAMITE SCONTI), ALLE POSSIBILI PROMOZIONI CONFERITE DAL SUO ACQUISTO, ALLE TEMPISTICHE DI CONSEGNA E IL NUMERO DI PEZZI A DISPOSIZIONE DEI CLIENTI 
																				if($offerta->getElementsByTagName("scontoATempo")->length==0)
																					echo "<span style=\"font-size: 1.5em; font-weight: bold;\">".$offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent." Cr.</span>\n";			
																				else {
																					echo "<span style=\"font-size: 1.25em; font-weight: bold; color: rgb(217, 118, 64);\">-".$offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale")."%</span>\n";		
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"font-size: 1.5em; font-weight: bold; margin-left: 0.25em;\">".number_format(floatval($offerta->firstChild->textContent) - (floatval($offerta->firstChild->textContent)*(floatval($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"))/100)),2,".","")." Cr.</span>\n";		
																				}
																			?>
																		</span>
																		<?php
																			if($offerta->getElementsByTagName("scontoATempo")->length!=0)
																				echo "<span class=\"elemento_anteprima_acquisto_scheda_offerta\"><span style=\"font-size: 0.875em\">Prezzo originale: <span style=\"text-decoration: line-through; text-decoration-color: rgb(217, 118, 64);\">".$offerta->firstChild->textContent." Cr.</span> <span style=\"color: rgb(217, 118, 64); cursor: pointer;\" title=\"valido dal ".date_format(date_create($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione")),"d/m/Y")." al ".date_format(date_create($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione")),"d/m/Y")."\">*</span></span></span>\n";
																			
																			if($offerta->getElementsByTagName("scontoFuturo")->length!=0)
																				echo "<span class=\"elemento_anteprima_acquisto_scheda_offerta\"><span style=\"font-size: 0.875em;\">Dal <span style=\"font-weight: bold;\">".date_format(date_create($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione")),"d/m/Y")."</span> al <span style=\"font-weight: bold;\">".date_format(date_create($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione")),"d/m/Y")."</span>, &egrave; prevista una promozione del <span style=\"color: rgb(217, 118, 64); font-weight: bold;\">".$offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale")." %</span> di sconto sul prossimo acquisto.</span></span>\n";			
																		
																			if($offerta->getElementsByTagName("bonus")->length!=0)
																				echo "<span class=\"elemento_anteprima_acquisto_scheda_offerta\"><span style=\"font-size: 0.875em;\">Non farti sfuggire il <span style=\"font-weight: bold;\">rimborso LEV</span>: acquista l'articolo e riceverai <span style=\"color: rgb(217, 118, 64); font-weight: bold;\">".$offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent." Cr.</span><span style=\"font-weight: normal;\"> come ringraziamento per aver partecipato all'iniziativa.</span></span></span>\n";			
																		?>
																		<span class="elemento_anteprima_acquisto_scheda_offerta">
																			<span style="font-size: 0.875em;"><span style="font-weight: bold;">Consegna GRATUITA</span> prevista per le <span style="font-weight: bold; color: rgb(217, 118, 64);">24</span> o le <span style="font-weight: bold; color: rgb(217, 118, 64);">48 ore</span> successive all'acquisto.</span>
																		</span>
																		<span class="elemento_anteprima_acquisto_scheda_offerta">
																			<span style="font-size: 1.125em; font-weight: bold;">
																			<?php 
																				// PER DARE L'IMPRESSIONE DI AVER COMUNQUE RIDOTTO IL NUMERO DI PEZZI A DISPOSIZIONE DEI VARI UTENTI, SARÀ POSSIBILE ADOTTARE UN RAGIONAMENTO SIMILE A QUELLO APPLICATO PER L'INSERIMENTO DI UNA DETERMINATA OFFERTA ALL'INTERNO DEL DOCUMENTO O DEL COOKIE CHE NE IDENTIFICA IL CARRELLO
																				// INFATTI, È POSSIBILE CONSIDERARE LE SEGUENTI DUE CASISTICHE:
																				// 1) NELL'EVENTUALITÀ IN CUI L'UTENTE NON SI SIA ANCORA PROFILATO E NEL COOKIE SIA PRESENTE UN RIFERIMENTO ALL'OFFERTA INTERESSATA, SARÀ POSSIBILE CONSIDERARE LA DIFFERENZA TRA IL QUANTITATIVO EFFETTIVO DI QUEST'ULTIMA, PRESENTE NEL FILE XML, E QUELLO MEMORIZZATO TEMPORANEMANTE ALL'INTERNO DEL COOKIE
																				// 2) D'ALTRO CANTO, E QUALORA L'UTENTE COINVOLTO RISULTI ESSERE UN CLIENTE, SI POTRÀ FARE RIFERIMENTO AL QUANTITATIVO PRESENTE IN CORRISPONDENZA DELL'ELEMENTO CHE RAPPRESENTA L'OFFERTA D'INTERESSE NEL RELATIVO DOCUMENTO
																				// N.B.: IN OGNI CASO, E LADDOVE RISULTI NECESSARIO, DOVRANNO ESSERE INTRODOTTI DEI CONFRONTI IN GRADO DI CONSIDERARE AMBEDUE LE IPOTESI ILLUSTRATE
																				
																				require ("./ricerca_offerta_nel_carrello.php");
																				
																				if(isset($_COOKIE["carrello_Offerte"]) && $offerta_presente) {
																					if(intval($offerta->getElementsByTagName("quantitativo")->item(0)->textContent)-$offerta_carrello["quantitativo"]>0)
																						echo "\t<span style=\"color: rgb(217, 118, 64);\">Disponibilit&agrave; immediata</span>\n";
																					else
																						echo "\t<span style=\"color: rgb(119, 119, 119);\">Al momento non disponibile...</span>\n";
																				}
																				else {
																					if($offerta->getElementsByTagName("quantitativo")->item(0)->textContent>0)
																						echo "\t<span style=\"color: rgb(217, 118, 64);\">Disponibilit&agrave; immediata</span>\n";
																					else
																						echo "\t<span style=\"color: rgb(119, 119, 119);\">Al momento non disponibile...</span>\n";
																				}
																			?>
																			</span>
																		</span>
																		<?php
																			// ***
																			if(isset($_COOKIE["carrello_Offerte"]) && $offerta_presente) {
																				if(intval($offerta->getElementsByTagName("quantitativo")->item(0)->textContent)-$offerta_carrello["quantitativo"]>0) {
																					echo "<span class=\"elemento_anteprima_acquisto_scheda_offerta quantitativo\">\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Quantit&agrave;</span>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<select name=\"quantitativo\">\n";
																					
																					// *** 
																					for($i=1; $i<=intval($offerta->getElementsByTagName("quantitativo")->item(0)->textContent)-$offerta_carrello["quantitativo"]; $i++)
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
																						
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</select>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"hidden\" name=\"id_Offerta\" value=\"".$_GET["id_Offerta"]."\"/>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<button class=\"pulsante_acquisto_scheda_offerta\" name=\"confirm\"><span style=\"font-size: 1em; width: 100%\">Aggiungi al carrello!</span></button>\n";
																				}
																			}
																			else {
																				if($offerta->getElementsByTagName("quantitativo")->item(0)->textContent>0 && ((isset($_SESSION["id_Utente"]) && $_SESSION["tipo_Utente"]=="C") || !isset($_SESSION["id_Utente"]))) {
																					echo "<span class=\"elemento_anteprima_acquisto_scheda_offerta quantitativo\">\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Quantit&agrave;</span>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<select name=\"quantitativo\">\n";
																					
																					// *** 
																					for($i=1; $i<=$offerta->getElementsByTagName("quantitativo")->item(0)->textContent; $i++)
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
																					
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</select>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"hidden\" name=\"id_Offerta\" value=\"".$_GET["id_Offerta"]."\"/>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<button class=\"pulsante_acquisto_scheda_offerta\" name=\"confirm\"><span style=\"font-size: 1em; width: 100%\">Aggiungi al carrello!</span></button>\n";
																				}
																			}
																		?>
																		<span class="elemento_anteprima_acquisto_scheda_offerta">
																			<span style="font-size: 0.875em;">Spedizione: <span style="font-weight: bold;">Corriere LEV</span></span>
																		</span>
																		<span class="elemento_anteprima_acquisto_scheda_offerta">
																			<span style="font-size: 0.875em;">Venditore: <span style="font-weight: bold;">LEV</span></span>
																		</span>
																	</div>
																</div>
															</form>
														</div>
													</div>
													<div class="container_recensioni_scheda_offerta">
														<div class="elemento_container_recensioni_scheda_offerta">
															<div class="pannello_recensioni_scheda_offerta">
																<div class="container_pannello_recensioni_scheda_offerta">
																	<span class="elemento_pannello_recensioni_scheda_offerta">
																		<span style="font-size:1.25em; font-weight:bold;">Recensisci questo prodotto</span>
																	</span>
																	<span class="elemento_pannello_recensioni_scheda_offerta">
																		<span style="font-size:0.875em;">Condividi i tuoi pensieri con gli altri utenti</span>
																	</span>
																	<a href="pubblicazione_recensione.php?id_Offerta=<?php echo $offerta->getAttribute("id"); ?>" class="pulsante_recensioni_scheda_offerta"><span style="font-size: 0.75em; width: 100%">Scrivi una recensione!</span></a>
																</div>
															</div>
														</div>
														<div class="elemento_container_recensioni_scheda_offerta">
															<div class="pannello_recensioni_scheda_offerta" style="border-radius:0.25em; border: 0.1em solid rgb(119,119,119); width: 100%;">
																<div class="container_pannello_recensioni_scheda_offerta" style="margin-bottom: 0.875em; overflow: hidden;">
																	<span class="elemento_pannello_recensioni_scheda_offerta">
																		<span style="font-size:1.25em; font-weight:bold;">Recensioni pi&ugrave; recenti</span>
																	</span>
																	<?php
																		// LE RECENSIONI CHE SI RIFERISCONO AL PRODOTTO COINVOLTO NELLA PROPOSTA DI VENDITA SELEZIONATA SARANNO PRESENTATE A PARTIRE DA QUELLE PIÙ RECENTI 
																		// A TALE SCOPO, È STATO NECESSARIO INTRODURRE UNO SCRIPT DI RICERCA IN GRADO DI DISPORRE LE VARIE OPINIONI IN FUNZIONE DI QUANTO INDICATO IN PRECEDENZA
																		// IL RAGIONAMENTO APPENA ILLUSTRATO, COSÌ COME IL SEGUENTE ALGORITMO, POTRANNO ESSERE ESTESI ANCHE ALLE ALTRE SEZIONI DELLA SCHERMATA
																		// N.B.: AL FINE DI TENERE TRACCIA DEI CONTRIBUTI INSERITI DAGLI UTENTI NEL CORSO DEL TEMPO, I GESTORI, COSÌ COME L'AMMINISTRATORE, POTRANNO VEDERE ANCHE I VARI ELEMENTI MODERATI, OVVERO NASCOSTI AGLI OCCHI DEI VISITATORI E DEI CLIENTI
																		require_once("./ricerca_recensioni_piu_recenti.php");
																		
																		// SE LA RICERCA NON HA PORTATO AD ALCUN RISULTATO, SARÀ NECESSARIO RIPORTARE A SCHERMO UN MESSAGGIO INERENTE ALLA MANCANTA PRESENZA DI RECENSIONI PER IL PRODOTTO D'INTERESSE
																		if(!count($recensioni_piu_recenti)) {
																			echo "<span class=\"elemento_pannello_recensioni_scheda_offerta\"><span class=\"nessun_elemento\" style=\"padding-left:0em; padding-right:0em; font-size:0.875em;\">Non &egrave; stato trovato nessun elemento con le specifiche di interesse.</span></span>\n";
																		}
																		
																		// DOPO AVER CREATO IL VETTORE CONTENENTE I VARI RIFERIMENTI D'INTERESSE, BISOGNERÀ PROCEDERE CON LA RICAPITOLAZIONE DELLE LORO INFORMAZIONI SFRUTTANDO, PER SEMPLICITÀ, IL COSTRUTTO foreach(...). INOLTRE, PER TENERE TRACCIA DEL NUMERO DI ELEMENTI FINORA CONSIDERATI, È STATO INTRODOTTO UN CONTATORE CHE CI HA PERMESSO DI TABULARE CORRETTAMENTE IL DOCUMENTO  
																		$i=0;
																		
																		foreach($recensioni_piu_recenti as $idRecensione => $dataPubblicazione )
																		{
																			// LE CHIAVI DEL VETTORE SONO STATE DEFINITE COME DELLE STRINGHE DELIMITATE DA DUE APPOSITI CARATTERI '...' IN MODO TALE DA PRESERVARE L'IDENTIFICATORE DI UNA CERTA PROPOSTA DI VENDITA. DUNQUE, PRIMA DI POTER ESSERE CONFRONTATE CON GLI INDICI DEI PRODOTTI, NECESSATINO DI UN'ULTERIORE FORMATTAZIONE     
																			$idRecensione=substr($idRecensione, 1, strlen($idRecensione)-2);
																			
																			// DATE LE POCHE INFORMAZIONI A DISPOSIZIONE, BISOGNERÀ NUOVAMENTE INTERAGIRE CON IL DOCUMENTO INERENTE ALLE RECENSIONI PUBBLICATE NEL CORSO DEL TEMPO
																			for($j=0; $j<$recensioni->length; $j++)
																			{
																				$recensione=$recensioni->item($j);
																				
																				if($recensione->getAttribute("id")==$idRecensione)
																				{
																					// PER OGNI RECENSIONE CHE SI RIFERISCE AL PRODOTTO INTERESSATO, BISOGNERÀ REPERIRE TUTTI I DETTAGLI UTILI AL FINE DI IDENTIFICARE L'UTENTE CHE HA DECISO DI PUBBLICARLA
																					$sql="SELECT Username, Reputazione, Tipo_Utente FROM $tab WHERE ID=".$recensione->getAttribute("idUtente");
																					$result=mysqli_query($conn, $sql);
																					
																					while($row=mysqli_fetch_array($result)) {
																						$username=$row["Username"];
																						$reputazione=$row["Reputazione"];
																						$tipoUtente=$row["Tipo_Utente"];
																					}
																					
																					// PER UNA STAMPA OTTIMALE DELLE VARIE INFORMAZIONI, SI PROCEDE CON L'ESTENSIONE DELL'ACRONIMO INERENTE ALLA TIPOLOGIA DI UTENTE E PRELEVATO DALLA ENTRY INTERESSATA
																					if($tipoUtente=="C")
																						$tipoUtente="Cliente";
																					
																					if($tipoUtente=="G")
																						$tipoUtente="Gestore";
																					
																					if($tipoUtente=="A")
																						$tipoUtente="Amministratore";
																					
																					// IN AGGIUNTA, SI EFFETTUA LA TRADUZIONE DELLA REPUTAZIONE DA PUNTI A TITOLO, RIPORTANDO COMUNQUE IL LORO AMMONTARE ATTUALE
																					if($reputazione<33) {
																						$reputazione="<span class=\"titolo_reputazione\" title=\"".$reputazione." punti\" style=\"color: rgb(119, 119, 119);\">Pessimo</span>";
																					}		
																					else {
																						if($reputazione<66 && $reputazione>=33) {
																							$reputazione="<span class=\"titolo_reputazione\" title=\"".$reputazione." punti\">Rispettabile</span>";
																						}
																						else {
																							$reputazione="<span class=\"titolo_reputazione\" title=\"".$reputazione." punti\" style=\"color: rgb(217, 118, 64);\">Virtuoso</span>";
																						}
																					}
																					
																					// PER QUESTIONI DI TABULAZIONE, È STATO NECESSARIO DISCRIMINARE L'ELEMENTO A CUI SI STA FACENDO ATTUALMENTE RIFERIMENTO. INFATTI, A PARTIRE DALLA "SECONDA RIGA", SARÀ NECESSARIO ATTRIBUIRE UNA DETERMINATA FORMATTAZIONE ANCHE ALL'OGGETTO DI PARTENZA CHE LA COMPONE
																					if($i==0)
																						echo "<span class=\"elemento_pannello_recensioni_scheda_offerta\" style=\"margin-top: 0.5em;\" id=\"recensione_".$recensione->getAttribute("id")."\">\n";
																					else
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_recensioni_scheda_offerta\" style=\"margin-top: 1.25em;\" id=\"recensione_".$recensione->getAttribute("id")."\">\n";
																					
																					// AL FINE DI DISTINGUERE OPPORTUNAMENTE LE SINGOLE RECENSIONI, I PRIMI DETTAGLI CHE SI È DECISO DI STAMPARE CONSISTONO NELLO USERNAME, NEL RUOLO E NELLA REPUTAZIONE DELL'UTENTE CHE HA CONDIVISO LA SUA OPINIONE
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"anteprima_utente\"><span class=\"immagine\"><img src=\"../../Immagini/user-solid.svg\" alt=\"Immagine Non Disponibile..\" /></span><span style=\"font-weight: bold; margin-left: 0.3125em;\">".$username." (".$tipoUtente." ".$reputazione.")</span></span>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					
																					// A SEGUITO DEI PRECEDENTI, VERRANNO RIPORTATI I PUNTEGGI ATTRIBUITI AI PARAMETRI PER LA VALUTAZIONE DELLA QUALITÀ DEL PRODOTTO E IL TITOLO DELLA RECENSIONE CHE, IN BASE ALLE CIRCOSTANZE, POTRÀ PRESENTARE LA DICITURA "Moderata" PER INDICARE CHE QUEL COMMENTO APPARTIENE ALL'INSIEME DI QUELLI NON VISIBILI AGLI UTENTI AL DI FUORI DEI GESTORI E DELL'AMMINISTRATORE DEL SITO 
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_recensioni_scheda_offerta\" style=\"margin-top: 0.5em;\">\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"display: flex; align-items: center; width: 100%;\">\n";
																					
																					if($prodotto->getElementsByTagName("libro")->length!=0) {
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"font-size: 0.875em;\" class=\"recensioni\">Trama: <span class=\"valutazione\">".number_format($recensione->getElementsByTagName("perLibro")->item(0)->getAttribute("trama"), 1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\"/></span></span>";
																						echo "<span style=\"font-size: 0.875em; padding-left: 0.5em;\" class=\"recensioni\">Personaggi: <span class=\"valutazione\">".number_format($recensione->getElementsByTagName("perLibro")->item(0)->getAttribute("caratterizzazionePersonaggi"), 1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\"/></span></span>";
																						echo "<span style=\"font-size: 0.875em; padding-left: 0.5em;\" class=\"recensioni\">Ambientazione: <span class=\"valutazione\">".number_format($recensione->getElementsByTagName("perLibro")->item(0)->getAttribute("ambientazione"), 1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\"/></span></span>\n";					
																					}
																					else {
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"font-size: 0.875em;\" class=\"recensioni\">Sceneggiatura: <span class=\"valutazione\">".number_format($recensione->getElementsByTagName("perVideogioco")->item(0)->getAttribute("sceneggiatura"), 1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\"/></span></span>";
																						echo "<span style=\"font-size: 0.875em; padding-left: 0.5em;\" class=\"recensioni\">Tecnica: <span class=\"valutazione\">".number_format($recensione->getElementsByTagName("perVideogioco")->item(0)->getAttribute("tecnica"), 1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\"/></span></span>";
																						echo "<span style=\"font-size: 0.875em; padding-left: 0.5em;\" class=\"recensioni\">Giocabilit&agrave;: <span class=\"valutazione\">".number_format($recensione->getElementsByTagName("perVideogioco")->item(0)->getAttribute("giocabilita"), 1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\"/></span></span>\n";					
																					}
																					
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_recensioni_scheda_offerta\" style=\"margin-top: 0.35em;\">\n";
																					
																					if($recensione->getAttribute("moderata")=="Si")
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"display: flex; align-items: center;\"><span style=\"font-weight: bold; color: rgb(217, 118, 64);\">".$recensione->getElementsByTagName("titolo")->item(0)->textContent."</span><span class=\"contributo_moderato\" style=\"font-size: 0.75em;\">Moderata</span></span>\n";
																					else
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"font-weight: bold; color: rgb(217, 118, 64);\">".$recensione->getElementsByTagName("titolo")->item(0)->textContent."</span>\n";
																					
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					
																					// LE ULTIME INFORMAZIONI D'INTERESSE PER LA GENERICA RECENSIONE SARANNO: LA DATA IN CUI È STATA PUBBLICATA, UNA DICITURA IN GRADO DI AFFERMARE SE L'UTENTE HA EFFETTIVAMENTE ACQUISTATO QUEL DETERMINATO PRODOTTO E UN PULSANTE, IL QUALE TORNERÀ UTILE PER SEGNALARE (O PER MODERARE NEL CASO DEI GESTORI) IL CONTRIBUTO IN ESAME A CAUSA DEL SUO CONTENUTO
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_recensioni_scheda_offerta\" style=\"margin-top: 0.125em;\">\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"font-size: 0.875em;\">Pubblicata il <span style=\"font-weight: bold;\">".date_format(date_create($recensione->getAttribute("dataPubblicazione")), "d/m/Y")."</span></span>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					
																					// PER AFFERMARE SE L'UTENTE COINVOLTO (CLIENTE) HA EFFETTIVAMENTE ACQUISTATO IL BENE IN ESAME, È STATO NECESSARIO IMPLEMENTARE UN ALGORITMO DI RICERCA CHE SI OCCUPERÀ DI SCANSIONARE TUTTI I FILE XML INTERESSATI E RIPORTARE IL RISULTATO SOTTO FORMA DI VARIABILE BOOLEANA
																					require("./ricerca_prodotto_tra_acquisti_recensione.php");
																					
																					if($prodotto_acquistato) {
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_recensioni_scheda_offerta\" style=\"margin-top: 0.175em;\">\n";
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"acquisto_verificato\" style=\"font-size: 0.75em; margin-left: 0em; margin-top: 0.175em;\">Acquisto verificato</span>\n";
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					}
																					
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"elemento_pannello_recensioni_scheda_offerta\" style=\"margin-top: 0.175em;\">\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div style=\"font-size: 0.875em; text-align: justify;\">".$recensione->getElementsByTagName("testo")->item(0)->textContent."</div>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
																					
																					// I CONTRIBUTI DEI GESTORI NON PRESENTERANNO I PULSANTI O I RIFERIMENTI IN MERITO AD UNA LORO POSSIBILE SEGNALAZIONE E/O MODERAZIONE
																					// LO STESSO RAGIONAMENTO VALE PER LE RECENSIONI PUBBLICATE DAI CLIENTI STESSI. INFATTI, IL SOGGETTO CHE HA CONDIVISO LE PROPRIE CONSIDERAZIONI CON GLI ALTRI NON POTRÀ SEGNALARE IL PROPRIO INTERVENTO
																					// PER DI PIÙ, L'AMMINISTRATORE DEL SITO SARÀ IN GRADO ESSO STESSO DI MODERARE LE RECENSIONI DEI VARI CLIENTI. IN OGNI CASO, ONDE EVITARE ERRORI DI DISTRAZIONE, UNA VOCE POTRÀ ESSERE RIPRISTINATA IN QUALSIASI MOMENTO
																					if(isset($_SESSION["id_Utente"])) {
																						if($_SESSION["tipo_Utente"]=="G" || $_SESSION["tipo_Utente"]=="A") {
																							if($tipoUtente!="Gestore" && $tipoUtente!="Amministratore") {
																								if($recensione->getAttribute("moderata")=="No") {
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_recensioni_scheda_offerta\" style=\"padding-bottom: 0em; margin-top: 0.5em; font-size: 0.875em;\">\n";
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"display: flex; align-items: center; font-size: 0.875em;\">\n";
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"moderazione_contributo.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Recensione=".$recensione->getAttribute("id")."\" class=\"pulsante_violazione_scheda_offerta\">Modera!</a>\n";
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																								}
																								else {
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_recensioni_scheda_offerta\" style=\"padding-bottom: 0em; margin-top: 0.5em; font-size: 0.875em;\">\n";
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"display: flex; align-items: center; font-size: 0.875em;\">\n";
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"ripristino_contributo_moderato.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Recensione=".$recensione->getAttribute("id")."\" class=\"pulsante_interazione_scheda_offerta\">Attiva!</a>\n";
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																								}
																							}
																						}
																						else {
																							if($_SESSION["tipo_Utente"]=="C") {
																								if(!($_SESSION["id_Utente"]==$recensione->getAttribute("idUtente")) && $tipoUtente!="Gestore" && $tipoUtente!="Amministratore") {
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_recensioni_scheda_offerta\" style=\"padding-bottom: 0em; margin-top: 0.5em; font-size: 0.875em;\">\n";
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"display: flex; align-items: center; font-size: 0.875em;\">\n";
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"invio_segnalazione.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Recensione=".$recensione->getAttribute("id")."\" class=\"pulsante_violazione_scheda_offerta\">Segnala!</a>\n";
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																								}
																							}
																						}
																					}
																					else {
																						if($tipoUtente!="Gestore" && $tipoUtente!="Amministratore") {
																							echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_recensioni_scheda_offerta\" style=\"padding-bottom: 0em; margin-top: 0.5em; font-size: 0.875em;\">\n";
																							echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"display: flex; align-items: center; font-size: 0.875em;\">\n";
																							echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"invio_segnalazione.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Recensione=".$recensione->getAttribute("id")."\" class=\"pulsante_violazione_scheda_offerta\">Segnala!</a>\n";
																							echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																							echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																						}
																					}
																					
																					$i++;
																					break;
																					
																				}
																			}
																		}
																	?>
																</div>
															</div>	
														</div>
													</div>
													<div class="container_discussioni_scheda_offerta">
														<div class="elemento_container_discussioni_scheda_offerta">
															<div class="pannello_discussioni_scheda_offerta">
																<div class="container_pannello_discussioni_scheda_offerta">
																	<span class="elemento_pannello_discussioni_scheda_offerta">
																		<span style="font-size:1.25em; font-weight:bold;">Discussioni dei clienti</span>
																	</span>
																	<span class="elemento_pannello_discussioni_scheda_offerta" style="margin-top: 0.325em;">
																		<span style="font-size: 0.875em; display: flex; justify-content: space-between; align-items: center;">Avvia una nuova conversione oppure aiuta chi &egrave; in cerca di risposte<a href="creazione_discussione.php?id_Offerta=<?php echo $offerta->getAttribute("id"); ?>" style="font-size: 0.875em; margin-right: 0em;" class="pulsante_violazione_scheda_offerta">Pubblica la tua domanda!</a></span>
																	</span>
																	<?php
																		// ***
																		require_once("./ricerca_discussioni_piu_recenti.php");
																		
																		// ***
																		if(!count($discussioni_piu_recenti)) {
																			echo "<span class=\"elemento_pannello_discussioni_scheda_offerta\" style=\"margin-top: 1.25em;\"><span class=\"nessun_elemento\" style=\"padding-left:0em; padding-right:0em; font-size:0.875em;\">Non &egrave; stato trovato nessun elemento con le specifiche di interesse.</span></span>\n";
																		}
																		else {
																			echo "<span class=\"elemento_pannello_discussioni_scheda_offerta\" style=\"margin-top: 0.375em; padding-bottom: 0.5em; font-size: 0.875em;\">\n";
																			echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"font-weight: bold;\">Interazioni pi&ugrave; recenti</span>\n";
																			echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																		}
																		
																		// ***
																		$i=0;
																		
																		foreach($discussioni_piu_recenti as $idDiscussione => $dataOraIssue )
																		{
																			// ***
																			$idDiscussione=substr($idDiscussione, 1, strlen($idDiscussione)-2);
																			
																			// ***
																			for($j=0; $j<$discussioni->length; $j++)
																			{
																				$discussione=$discussioni->item($j);
																				
																				if($discussione->getAttribute("id")==$idDiscussione)
																				{
																					// ***
																					$sql="SELECT Username, Reputazione, Tipo_Utente FROM $tab WHERE ID=".$discussione->getAttribute("idAutore");
																					$result=mysqli_query($conn, $sql);
																					
																					while($row=mysqli_fetch_array($result)) {
																						$username=$row["Username"];
																						$reputazione=$row["Reputazione"];
																						$tipoAutore=$row["Tipo_Utente"];
																					}
																					
																					// ***
																					if($tipoAutore=="C")
																						$tipoAutore="Cliente";
																					
																					if($tipoAutore=="G")
																						$tipoAutore="Gestore";
																					
																					if($tipoAutore=="A")
																						$tipoAutore="Amministratore";
																					
																					// ***
																					if($reputazione<33) {
																						$reputazione="<span class=\"titolo_reputazione\" title=\"".$reputazione." punti\" style=\"color: rgb(119, 119, 119);\">Pessimo</span>";
																					}		
																					else {
																						if($reputazione<66 && $reputazione>=33) {
																							$reputazione="<span class=\"titolo_reputazione\" title=\"".$reputazione." punti\">Rispettabile</span>";
																						}
																						else {
																							$reputazione="<span class=\"titolo_reputazione\" title=\"".$reputazione." punti\" style=\"color: rgb(217, 118, 64);\">Virtuoso</span>";
																						}
																					}
																					
																					// PER QUESTIONI PURAMENTE GRAFICHE, È STATO NECESSARIO CARATTERIZZARE UN ULTERIORE DIVISORE CHE POSSA COMPREDENDERE TUTTE LE COMPONENTI INERENTI AD UNA CERTA DISCUSSIONE, IL QUALE, IN BASE AL CONTESTO, DOVRÀ ESSERE OPPORTUNAMENTE FORMATTATO
																					if($discussione->getAttribute("risolta")=="Si") {
																						if(isset($_SESSION["id_Utente"])) {
																							if($_SESSION["tipo_Utente"]=="G" || $_SESSION["tipo_Utente"]=="A")
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"container_discussione\">\n";
																							else {
																								if($_SESSION["id_Utente"]==$discussione->getAttribute("idAutore"))
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"container_discussione\" style=\"padding-bottom: 0.35em;\">\n";
																								else {
																									if($discussione->getAttribute("moderata")=="No")
																										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"container_discussione\">\n";
																									else
																										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"container_discussione\" style=\"padding-bottom: 0.35em;\">\n";
																								}
																							}
																						}
																						else {
																							if($discussione->getAttribute("moderata")=="No")
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"container_discussione\">\n";
																							else
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"container_discussione\" style=\"padding-bottom: 0.35em;\">\n";
																						}
																					}
																					else 
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"container_discussione\">\n";
																					
																					// SIMILMENTE ALLE RECENSIONI, LE INFORMAZIONI CHE MEGLIO SI PRESTANO A RICONOSCERE ALL'ISTANTE UN DETERMINATO ELEMENTO RISULTANO ESSERE: L'AUTORE DELLA DISCUSSIONE E I RIFERIMENTI TEMPORALI NONCHÈ OPERATIVI (TITOLO, "Risolta", "Moderata", ...) INERENTI A QUEST'ULTIMA
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_discussioni_scheda_offerta\" style=\"display: flex; justify-content: space-between; align-items: center; font-size: 0.875em;\">\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Rivolta da<span style=\"font-weight: bold; margin-left: 0.3125em;\">".$username." (".$tipoAutore." ".$reputazione.")</span> il <span style=\"font-weight: bold;\">".date_format(date_create($discussione->getAttribute("dataOraIssue")), "d/m/Y")."</span> alle <span style=\"font-weight: bold;\">".date_format(date_create($discussione->getAttribute("dataOraIssue")), "H:i")."</span></span>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					
																					if($discussione->getAttribute("risolta")=="Si") 
																						$risolta="<span class=\"discussione_risolta\" style=\"font-size: 0.75em;\">Risolta</span>";
																					else
																						$risolta="";
																					
																					if($discussione->getAttribute("moderata")=="Si")
																						$moderata="<span class=\"contributo_moderato\" style=\"font-size: 0.75em;\">Moderata</span>";
																					else
																						$moderata="";
																					
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"display: flex; align-items: center; font-weight: bold; margin-top: 0.5em;\">Argomento: <span style=\"margin-left: 0.25em; color: rgb(217, 118, 64);\">".$discussione->firstChild->textContent."</span>".$risolta." ".$moderata."</span>\n";
																					
																					// A SEGUITO DEI PRECEDENTI, BISOGNERÀ PRESENTARE IL CONTENUTO DELLA DISCUSSIONE D'INTERESSE E L'ELENCO DI TUTTI I POSSIBILI INTERVENTI CHE L'HANNO COINVOLTA NEL CORSO DEL TEMPO
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"elemento_pannello_recensioni_scheda_offerta\" style=\"margin-top: 0.425em; margin-bottom: 1.275em; font-size: 0.875em;\">\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div style=\"text-align: justify; font-weight: bold;\">Descrizione: ".$discussione->getElementsByTagName("descrizione")->item(0)->textContent."</div>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
																					
																					if($discussione->getElementsByTagName("intervento")->length!=0) {
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_discussioni_scheda_offerta\" style=\"margin-bottom: 0.5em; font-size: 0.875em;\">\n";
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"font-weight: bold;\">Interventi correlati:</span>\n";
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					}
																					
																					// ***
																					require("./ricerca_interventi_piu_recenti.php");
																					
																					// ***
																					$m=0;
																					
																					foreach($interventi_piu_recenti as $idIntervento => $dataOraIssue )
																					{
																						// ***
																						$idIntervento=substr($idIntervento, 1, strlen($idIntervento)-2);
																						
																						// ***
																						for($m=0; $m<$discussione->getElementsByTagName("intervento")->length; $m++) {
																							$intervento=$discussione->getElementsByTagName("intervento")->item($m);
																							
																							if($intervento->getAttribute("id")==$idIntervento) {
																								// ***
																								$sql="SELECT Username, Reputazione, Tipo_Utente FROM $tab WHERE ID=".$intervento->getAttribute("idPartecipante");
																								$result=mysqli_query($conn, $sql);
																								
																								while($row=mysqli_fetch_array($result)) {
																									$username=$row["Username"];
																									$reputazione=$row["Reputazione"];
																									$tipoUtente=$row["Tipo_Utente"];
																								}
																								
																								// ***
																								if($tipoUtente=="C")
																									$tipoUtente="Cliente";
																								
																								if($tipoUtente=="G")
																									$tipoUtente="Gestore";
																								
																								if($tipoUtente=="A")
																									$tipoUtente="Amministratore";
																								
																								// ***
																								if($reputazione<33) {
																									$reputazione="<span class=\"titolo_reputazione\" title=\"".$reputazione." punti\" style=\"color: rgb(119, 119, 119);\">Pessimo</span>";
																								}		
																								else {
																									if($reputazione<66 && $reputazione>=33) {
																										$reputazione="<span class=\"titolo_reputazione\" title=\"".$reputazione." punti\">Rispettabile</span>";
																									}
																									else {
																										$reputazione="<span class=\"titolo_reputazione\" title=\"".$reputazione." punti\" style=\"color: rgb(217, 118, 64);\">Virtuoso</span>";
																									}
																								}
																								
																								// ***
																								require("./ricerca_prodotto_tra_acquisti_intervento.php");
																								
																								if($prodotto_acquistato)
																									$acquisto_verificato="<span class=\"acquisto_verificato\" style=\"font-size: 0.875em; margin-left: 0em; margin-right: 0.3125em; margin-bottom: 0em;\">Acquisto verificato</span>";
																								else
																									$acquisto_verificato="";
																								
																								if($intervento->getAttribute("moderato")=="Si")
																									$intervento_moderato="<span class=\"contributo_moderato\" style=\"font-size: 0.875em; margin-left: 0em; margin-bottom: 0em;\">Moderato</span>";
																								else
																									$intervento_moderato="";
																								
																								// ***
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"container_intervento\" style=\"margin-top: 0.5em;\">\n";
																								
																								// ***
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_discussioni_scheda_offerta\" style=\"display: flex; justify-content: space-between; align-items: center; font-size: 0.875em; margin-bottom: 0.375em;\">\n";
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"display: flex; align-items: center;\">Pubblicato da<span style=\"font-weight: bold; margin-left: 0.3125em; margin-right: 0.3125em;\">".$username." (".$tipoUtente." ".$reputazione.")</span> il <span style=\"font-weight: bold; margin-right: 0.3125em; margin-left: 0.3125em;\">".date_format(date_create($intervento->getAttribute("dataOraIssue")), "d/m/Y")."</span> alle <span style=\"font-weight: bold; margin-right: 0.3125em; margin-left: 0.3125em;\">".date_format(date_create($intervento->getAttribute("dataOraIssue")), "H:i")."</span>".$acquisto_verificato."".$intervento_moderato."</span>\n";
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																								
																								// UNA DELLE INFORMAZIONI PIÙ IMPORTANTI INERENTI AI SINGOLI CONTRIBUTI, SONO SICURAMENTE LE VALUTAZIONI CHE HANNO RICEVUTO IN RELAZIONE AL LORO LIVELLO DI SUPPORTO E DI UTILITÀ PER L'INTERA COMUNITÀ
																								// PROPRIO PER QUESTO, ABBIAMO DECISO DI IMPLEMENTARE UNO SCRIPT, MOLTO SIMILE AGLI ALTRI DEL SUO GENERE, CHE, PER OGNI INTERVENTO, PERMETTERÀ DI RIPORTARE A SCHERMO LE MEDIE E IL NUMERO DI GIUDIZI PUBBLICATI
																								require("./calcolo_media_valutazioni_intervento.php");
																								
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_discussioni_scheda_offerta\">\n";
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"display: flex; align-items: center; width: 100%;\">\n";
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"font-size: 0.875em;\" class=\"recensioni\">Supporto: <span class=\"valutazione\">".number_format($media_supporto, 1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\"/></span></span>";
																								echo "<span style=\"font-size: 0.875em; padding-left: 0.5em;\" class=\"recensioni\">Utilit&agrave;: <span class=\"valutazione\">".number_format($media_utilita, 1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\"/></span></span>";
																								echo "<span style=\"font-size: 0.875em;\" class=\"voti\">".$num_valutazioni." voti</span>\n";
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																								
																								// PER CIASCUN INTERVENTO, SARÀ NECESSARIO MOSTRARE LA RISPOSTA CHE L'UTENTE HA VOLUTO FORNIRE A SOSTEGNO DI CHI HA AVVIATO LA DISCUSSIONE
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"elemento_pannello_recensioni_scheda_offerta\" style=\"margin-top: 0.325em; font-size: 0.875em;\">\n";
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div style=\"text-align: justify; font-weight: bold;\">Messaggio: ".$intervento->getElementsByTagName("testo")->item(0)->textContent."</div>\n";
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
																								
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_discussioni_scheda_offerta\" style=\"display: flex; justify-content: space-between; align-items: center; font-size: 0.875em; margin-bottom: 0em;\">\n";
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"display: flex; align-items: center; font-size: 0.875em;\">\n";
																								
																								// GLI ULTIMI ELEMENTI CHE COMPORRANNO LE SINGOLE RISPOSTE SARANNO DEI PULSANTI CHE, OLTRE A PERMETTERE LA LORO SEGNALAZIONE O LA LORO MODERAZIONE, CONSENTIRANNO A VI VARI UTENTI DI APPLICARE LE VALUTAZIONI CITATE IN PRECEDENZA NEL CASO IN CUI NON SIA STATO MODERATO
																								if($intervento->getAttribute("moderato")=="No")
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"valutazione_intervento.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Discussione=".$discussione->getAttribute("id")."&amp;id_Intervento=".$intervento->getAttribute("id")."\" class=\"pulsante_interazione_scheda_offerta\">Valuta!</a>\n";
																								
																								// ***
																								if(isset($_SESSION["id_Utente"])) {
																									if($_SESSION["tipo_Utente"]=="G" || $_SESSION["tipo_Utente"]=="A") {
																										if($tipoUtente!="Gestore" && $tipoUtente!="Amministratore") {
																											if($intervento->getAttribute("moderato")=="No") {
																												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"moderazione_contributo.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Discussione=".$discussione->getAttribute("id")."&amp;id_Intervento=".$intervento->getAttribute("id")."\" class=\"pulsante_violazione_scheda_offerta\">Modera!</a>\n";
																												
																												// QUALORA L'UTENTE RISULTI ESSERE UN AMMINISTRATORE, DOVRÀ ESSERE IN GRADO DI PUBBLICARE L'INTERVENTO, NON ANCORA MODERATO, ANCHE NELLA SEZIONE RISERVATA ALLE FAQ
																												if($_SESSION["tipo_Utente"]=="A" && $discussione->getAttribute("moderata")=="No")
																													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"pubblicazione_faq.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Discussione=".$discussione->getAttribute("id")."&amp;id_Intervento=".$intervento->getAttribute("id")."\" class=\"pulsante_interazione_scheda_offerta\">Eleva!</a>\n";
																											}
																											else {
																												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"ripristino_contributo_moderato.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Discussione=".$discussione->getAttribute("id")."&amp;id_Intervento=".$intervento->getAttribute("id")."\" class=\"pulsante_interazione_scheda_offerta\">Attiva!</a>\n";
																											}
																										}
																									}
																									else {
																										if($_SESSION["tipo_Utente"]=="C") {
																											if(!($_SESSION["id_Utente"]==$intervento->getAttribute("idPartecipante")) && $tipoUtente!="Gestore" && $tipoUtente!="Amministratore") {
																												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"invio_segnalazione.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Discussione=".$discussione->getAttribute("id")."&amp;id_Intervento=".$intervento->getAttribute("id")."\" class=\"pulsante_violazione_scheda_offerta\">Segnala!</a>\n";
																											}
																										}
																									}
																								}
																								else {
																									if($tipoUtente!="Gestore" && $tipoUtente!="Amministratore") {
																										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"invio_segnalazione.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Discussione=".$discussione->getAttribute("id")."&amp;id_Intervento=".$intervento->getAttribute("id")."\" class=\"pulsante_violazione_scheda_offerta\">Segnala!</a>\n";
																									}
																								}
																								
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																								
																								echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
																								
																								$m++;
																								break;
																								
																							}
																						}
																					}
																					
																					// IN MERITO ALLE DISCUSSIONI, BISOGNERÀ FARE IN MODO CHE POSSANO SUBIRE LO STESSO TRATTAMENTO DEI SINGOLI INTERVENTI. L'UNICA DIFFERENZA RIGUARDERÀ IL FATTO CHE, ANZICHÈ DELLE VALUTAZIONI, POTRANNO RICEVERE DELLE RISPOSTE NEL CASO IN CUI NON SIANO STATE MODERATE O RISOLTE 
																					if($discussione->getElementsByTagName("intervento")->length!=0)
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_discussioni_scheda_offerta\" style=\"display: flex; justify-content: space-between; align-items: center; font-size: 0.875em; margin-bottom: 0.175em;\">\n";
																					else
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"elemento_pannello_discussioni_scheda_offerta\" style=\"display: flex; justify-content: space-between; align-items: center; font-size: 0.875em; margin-bottom: 0.25em; margin-top: -0.3875em;\">\n";
																					
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"display: flex; align-items: center; font-size: 0.875em; margin-top: 0.925em;\">\n";
																					
																					if($discussione->getAttribute("risolta")=="No" && $discussione->getAttribute("moderata")=="No") 
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"pubblicazione_intervento_discussione.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Discussione=".$discussione->getAttribute("id")."\" class=\"pulsante_interazione_scheda_offerta\">Replica!</a>\n";
																					
																					// ***
																					if(isset($_SESSION["id_Utente"])) {
																						if($_SESSION["tipo_Utente"]=="G" || $_SESSION["tipo_Utente"]=="A") {
																							if($tipoAutore!="Gestore" && $tipoAutore!="Amministratore") {
																								if($discussione->getAttribute("moderata")=="No") {
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"moderazione_contributo.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Discussione=".$discussione->getAttribute("id")."\" class=\"pulsante_violazione_scheda_offerta\">Modera!</a>\n";
																									
																									// QUALORA L'UTENTE RISULTI ESSERE UN AMMINISTRATORE, DOVRÀ ESSERE IN GRADO DI PUBBLICARE LA DOMANDA, NON ANCORA MODERATA, ANCHE NELLA SEZIONE RISERVATA ALLE FAQ
																									if($_SESSION["tipo_Utente"]=="A")
																										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"pubblicazione_faq.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Discussione=".$discussione->getAttribute("id")."\" class=\"pulsante_interazione_scheda_offerta\">Eleva!</a>\n";
																								}
																								else {
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"ripristino_contributo_moderato.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Discussione=".$discussione->getAttribute("id")."\" class=\"pulsante_interazione_scheda_offerta\">Attiva!</a>\n";
																								}
																							}
																						}
																						else {
																							if($_SESSION["tipo_Utente"]=="C") {
																								if(!($_SESSION["id_Utente"]==$discussione->getAttribute("idAutore")) && $tipoAutore!="Gestore" && $tipoAutore!="Amministratore") {
																									echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"invio_segnalazione.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Discussione=".$discussione->getAttribute("id")."\" class=\"pulsante_violazione_scheda_offerta\">Segnala!</a>\n";
																								}
																							}
																						}
																					}
																					else {
																						if($tipoAutore!="Gestore" && $tipoAutore!="Amministratore") {
																							echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"invio_segnalazione.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Discussione=".$discussione->getAttribute("id")."\" class=\"pulsante_violazione_scheda_offerta\">Segnala!</a>\n";
																						}
																					}
																					
																					// IN AGGIUNTA A QUANTO DETTO FINORA, L'UTENTE CHE HA AVVIATO LA CONVERSAZIONE DOVRÀ ESSERE IN GRADO DI CHIUDERLA NON APPENA LO RITERRÀ NECESSARIO
																					if($discussione->getAttribute("risolta")=="No" && $discussione->getAttribute("moderata")=="No" && isset($_SESSION["id_Utente"]) && $discussione->getAttribute("idAutore")==$_SESSION["id_Utente"])
																						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"chiusura_discussione.php?id_Offerta=".$offerta->getAttribute("id")."&amp;id_Discussione=".$discussione->getAttribute("id")."\" class=\"pulsante_violazione_scheda_offerta\">Chiudi!</a>\n";

																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
																					
																					echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
																					
																					$i++;
																					break;
																					
																				}
																			}
																		}
																	?>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="pulsante" style="justify-content: center; margin-bottom: 2.425%; margin-top: -2%;">
											<form action="elenco_risultati_ricerca.php" method="get">
												<p>
													<input type="hidden" name="prodotto_Ricercato" value="<?php echo $prodotto->firstChild->textContent; ?>" />
													<button type="submit" class="container_pulsante back" style="margin-left: 1.5em; margin-right: 1.5em; padding: 0em;">Indietro!</button>
												</p>
											</form>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
			// IN AGGIUNTA, SEGUENDO GLI STESSI RAGIONAMENTI APPLICATI PER L'INTESTAZIONE, È STATO RITENUTO UTILE RICHIAMARE IL PIÈ DI PAGINA ALL'INTERNO DI TUTTE QUELLE SCHERMATE IN CUI SE NE MANIFESTA IL BISOGNO
			require_once ("./footer_sito.php");
		?>
	</body>
</html>