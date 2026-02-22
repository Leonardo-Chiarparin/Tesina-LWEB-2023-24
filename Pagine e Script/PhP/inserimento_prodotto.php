<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA COMPILARE PER INSERIRE UN NUOVO PRODOTTO ALL'INTERNO DEL MAGAZZINO
	// N.B.: IN CASO DI ERRORE, LE INFORMAZIONI INSERITE, COSÌ COME LE SCELTE EFFETTUATE, VERRANNO PRESERVATE TRAMITE UNA SERIE DI CONTROLLI APPLICATI AL SOLO SCOPO DI AGEVOLARE L'OPERATO DELL'AMMINISTRATORE

	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");

	// PER QUESTIONI DI SICUREZZA, È STATO NECESSARIO L'INSERIMENTO DELLO SCRIPT "private_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA. QUESTO MECCANISMO NON PERMETTERÀ L'ACCESSO AGLI UTENTI CHE NON SI AUTENTICATI TRAMITE IL RELATIVO SERVIZIO
	require_once("./private_session_control.php");
	
	// LE PROPOSTE DI VENDITA, DATA LA LORO STRUTTURA, POTREBBERO ESSERE SOGGETTE A DELLE RIDUZIONI DI PREZZO, CARATTERIZZATE DA UN INTERVALLO DI VALIDITÀ BEN DEFINITO E TALVOLTA APPLICABILI SUL PROSSIMO ACQUISTO. PERTANTO, NELLE IPOTESI IN CUI NON SIA SEMPRE POSSIBILE RIMUOVERE MANUALMENTE LE VOCI INTERESSATE, ABBIAMO DECISO DI IMPLEMENTARE UN CODICE IN GRADO DI INTERVENIRE IN AUTOMATICO SU TUTTI QUELLI SCONTI NON PIÙ USUFRUIBILI DAI CLIENTI DELLA PIATTAFORMA
	require_once("./rimozione_sconti.php");
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO ALL'AMMINISTRATORE DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="A") {
		header("Location: area_riservata.php");
	}
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_categorie_libri.php");
	require_once("./apertura_file_piattaforme_videogiochi.php");
	require_once("./apertura_file_generi_videogiochi.php");
	
	// IL PULSANTE AVENTE LA DICITURA "INDIETRO" PERMETTERÀ ALL'UTENTE DI TORNARE ALLA SCHERMATA PRECEDENTE A QUELLA CORRENTE
	if(isset($_POST["back"])) {
		header("Location: riepilogo_prodotti.php");
	}
	
	// UNA VOLTA CONFERMATE LE PROPRIE SCELTE, BISOGNA PROCEDERE CON I CONTROLLI PER VALUTARE LA CORRETTEZZA E L'INTEGRITÀ DEI VALORI INDICATI ALL'INTERNO DEI VARI CAMPI
	if(isset($_POST["confirm"])) {
		
		// IL PRIMO PASSO CONSISTE NELLA RIMOZIONE DEI POSSIBILI SPAZI (BIANCHI) COLLOCATI ALL'INIZIO E ALLA FINE DEGLI ELEMENTI COINVOLTI
		// NEL DETTAGLIO, POICHÈ IL PRODOTTO PUÒ PRESENTARE DELLE COMPONENTI DIFFERENTI IN BASE ALLA PROPRIA CI SI ASSICURA RISPETTO AGLI UNICI DETTAGLI OBBLIGATORI, OVVERO LA DENOMINAZIONE, LA DESCRIZIONE, IL PREZZO E L'ANNO DI USCITA DI QUEST'ULTIMO  
		$_POST["nome"]=trim($_POST["nome"]);
		$_POST["nome"]=rtrim($_POST["nome"]);

		$_POST["descrizione"]=trim($_POST["descrizione"]);
		$_POST["descrizione"]=rtrim($_POST["descrizione"]);
		
		// PER QUESTIONI DI FORMATTAZIONE DEL DOCUMENTO XML, È STATO NECESSARIO DISPORRE TUTTE LE COMPONENTI DELLA DESCRIZIONE DELL'ARTICOLO ALL'INTERNO DI UN'UNICA RIGA. A TALE SCOPO, ABBIAMO USUFRUITO DEL METODO explode(...) SPECIFICANDO "\n" COME PARAMETRO DELIMITATORE PER EFFETTUARE LA SEPARAZIONE DELLA STRINGA
		$descrizione=explode("\n", $_POST["descrizione"]);
		$_POST["descrizione"]="";
		
		foreach($descrizione as $riga) {
			$_POST["descrizione"]=$_POST["descrizione"].$riga;
		}
		
		$_POST["prezzoListino"]=trim($_POST["prezzoListino"]);
		$_POST["prezzoListino"]=rtrim($_POST["prezzoListino"]);

		$_POST["annoUscita"]=trim($_POST["annoUscita"]);
		$_POST["annoUscita"]=rtrim($_POST["annoUscita"]);
		
		// AL FINE DI GARANTIRE UN DISCRETO LIVELLO DI SICUREZZA, PER I CAMPI PRIVI DI CONTROLLI INERENTI AD ESPRESSIONI REGOLARI (ADEGUATE PER IL CONTESTO) VERRANNO APPLICATI DEI CONTROLLI INERENTI AI CARATTERI AL LORO INTERNO 
		$_POST["nome"]=stripslashes($_POST["nome"]); // UN ESEMPIO, POTREBBE ESSERE LA RIMOZIONE DEI BACKSLASH \ PER EVITARE LA MySQL Injection
		$_POST["descrizione"]=stripslashes($_POST["descrizione"]); // ***
		
		// GIUNTI A QUESTO PUNTO, E SECONDO L'ORDINAMENTO INDOTTO DALLE PRECEDENTI OPERAZIONI, È NECESSARIO EFFETTUARE UN ULTERIORE CONTROLLO PER VALUTARE SE SI È EFFETTIVAMENTE INSERITO QUALCOSA ALL'INTERNO DEI VARI CAMPI 
		if(strlen($_POST["nome"])==0 || strlen($_POST["descrizione"])==0 || strlen($_POST["prezzoListino"])==0 || strlen($_POST["annoUscita"])==0) {
			// IN CASO DI ERRORE, SI DOVRÀ INFORMARE L'UTENTE COINVOLTO DELLE MANCANZE CHE HA AVUTO NEI CONFRONTI DEL MODULO PER L'INSERIMENTO DELLA PROPOSTA DI VENDITA. UN SIMILE MECCANISMO VERRÀ IMPLEMENTATO PER TUTTE LE CASISITCHE IN CUI SARÀ IMPOSSIBILE PROCEDERE  
			$campi_vuoti=true;
		}
		else {
			// IN PRESENZA DI UN FILE CARICATO, SARÀ NECESSARIO EFFETTUARE UNA SERIE DI CONTROLLI PER VALUTARNE LA DIMENSIONE (AL PIÙ DI 2 MB) COSÌ DA EVITARE PROBLEMATICHE RELATIVE ALLA LORO GESTIONE  
			if(!empty($_FILES["immagine"]["name"])) {
				
				$dimensione_massima_immagine = "2097152";
				
				if($_FILES["immagine"]["size"]>$dimensione_massima_immagine) {
					// ***
					$superamento_immagine=true;
				}
				else {
					//	A SEGUITO DEI PRECEDENTI CONTROLLI, BISOGNERÀ INDIVIDUARE IL TIPO DI PRODOTTO (LIBRO O VIDEOGIOCO) DA REGISTRARE ALL'INTERNO DELLA RELATIVA STRUTTURA DATI. PER DI PIÙ, SARÀ NECESSARIO EFFETTUARE DELLE VERIFICHE "DINAMICHE" (E DI CONSISTENZA) INERENTI AL CONTENUTO DEGLI ASPETTI NON ANCORA TRATTATI   
					if(isset($_POST["tipo"])) {
						
						if($_POST["tipo"]=="libro") {
							
							// DATA LA COSTRUZIONE DELLE CASELLE DI SPUNTA (BASATA INTERAMENTE SUL LORO RICONOSCIMENTO PREVIA NOMI DERIVANTI DAI LORO IDENTIFICATORI) SI È RISCONTRATA LA NECESSITÀ DI SCANSIONARE TUTTE LE POSSIBILI PER VALUTARE SE L'AMMINISTRATORE NE HA SELEZIONATA ALMENO UNA, PARTENDO DA UN FLAG PARI INIZIALMENTE A "false" 
							$categoria_selezionata=false;
							
							for($i=0; $i<$categorie->length && !$categoria_selezionata; $i++) {
								$categoria=$categorie->item($i);
								
								if(isset($_POST["categoria_".$categoria->getAttribute("id")])) 
									$categoria_selezionata=true;
								
							}
							
							// NEL CASO IN CUI LE OPERAZIONI DI CUI SOPRA NON ABBIANO PORTATO A DEGLI ERRORI, SI PROCEDE CON L'ANALISI DEI CAMPI INERENTI AI NOMINATIVI DEGLI AUTORI DEL LIBRO, I QUALI, COME ANCHE SPECIFICATO NELLA RELAZIONE DI ACCOMPAGNAMENTO AL PROGETTO, POTRANNO ANCHE ESSERE CONSIDERATI COME D'ARTE E DUNQUE PRIVI DI UN COGNOME LORO ASSOCIATO
							if($categoria_selezionata) {
								// ***
								$_POST["nomeAutore"]=trim($_POST["nomeAutore"]);
								$_POST["nomeAutore"]=rtrim($_POST["nomeAutore"]);
								
								// ***
								$_POST["nomeAutore"]=stripslashes($_POST["nomeAutore"]);
								
								// ***
								if(strlen($_POST["nomeAutore"])==0) {
									// ***
									$nessun_nome_autore=true;
								}
								else {
									// ***
									$_POST["cognomeAutore"]=trim($_POST["cognomeAutore"]);
									$_POST["cognomeAutore"]=rtrim($_POST["cognomeAutore"]);
									
									// ***
									$_POST["cognomeAutore"]=stripslashes($_POST["cognomeAutore"]);
									
									// ***
									$_POST["nomeCoautore"]=trim($_POST["nomeCoautore"]);
									$_POST["nomeCoautore"]=rtrim($_POST["nomeCoautore"]);
									
									// ***
									$_POST["nomeCoautore"]=stripslashes($_POST["nomeCoautore"]);
									
									// ***
									if(strlen($_POST["nomeCoautore"])==0) {
										// ***
										$_POST["cognomeCoautore"]=trim($_POST["cognomeCoautore"]);
										$_POST["cognomeCoautore"]=rtrim($_POST["cognomeCoautore"]);
										
										// ***
										$_POST["cognomeCoautore"]=stripslashes($_POST["cognomeCoautore"]);
										
										// ***
										if(strlen($_POST["cognomeCoautore"])!=0) {
											// ***
											$nessun_nome_coautore=true;
										}
									}
									else {
										// ***
										$_POST["cognomeCoautore"]=trim($_POST["cognomeCoautore"]);
										$_POST["cognomeCoautore"]=rtrim($_POST["cognomeCoautore"]);
										
										// ***
										$_POST["cognomeCoautore"]=stripslashes($_POST["cognomeCoautore"]);
									}
								}
							}
							else {
								// ***
								$nessuna_categoria=true;
							}
						}
						else {
							if($_POST["tipo"]=="videogioco") {
								// LA STRATEGIA ADOTTATA PER IL CONTROLLO DEI CAMPI INERENTI ALLE OPERE CARTACEE PUÒ ESSERE ESTESA ANCHE PER QUELLE VIDEOLUDICHE
								// ***
								if(isset($_POST["piattaforma"])) {
									// ***
									$genere_selezionato=false;
								
									for($i=0; $i<$generi->length && !$genere_selezionato; $i++) {
										$genere=$generi->item($i);
										
										if(isset($_POST["genere_".$genere->getAttribute("id")])) 
											$genere_selezionato=true;
										
									}
									
									// ***
									if($genere_selezionato) {
										// ***
										$_POST["casaProduzione"]=trim($_POST["casaProduzione"]);
										$_POST["casaProduzione"]=rtrim($_POST["casaProduzione"]);
										
										// ***
										$_POST["casaProduzione"]=stripslashes($_POST["casaProduzione"]);
										
										// ***
										if(strlen($_POST["casaProduzione"])==0) {
											// ***
											$nessuna_casa_produzione=true;
										}
									}
									else {
										// ***
										$nessun_genere=true;
									}
									
									
								}
								else {
									// ***
									$nessuna_piattaforma=true;
								}
							}
							else {
								// ***
								$tipo_errato=true;
							}
						}
					}
					else {
						// ***
						$nessun_tipo=true;
					}
				}
			}
			else {
				// ***
				$nessuna_immagine=true;
			}
		}
		
		// SE NON CI SONO STATE DELLE VIOLAZIONI INERENTI AL MANCATO RIEMPIMENTO DEI VARI CAMPI INTERESSATI, SI PROCEDE CON L'EFFETTIVO CONTROLLO, IN TERMINI DI FORMATO DA RISPETTARE, DEL LORO CONTENUTO
		if(!(isset($campi_vuoti) && $campi_vuoti) && !(isset($nessuna_immagine) && $nessuna_immagine) && !(isset($nessun_tipo) && $nessun_tipo) && !(isset($tipo_errato) && $tipo_errato) && !(isset($nessuna_piattaforma) && $nessuna_piattaforma) && !(isset($nessun_genere) && $nessun_genere) && !(isset($nessuna_casa_produzione) && $nessuna_casa_produzione) && !(isset($nessuna_categoria) && $nessuna_categoria) && !(isset($nessun_nome_autore) && $nessun_nome_autore) && !(isset($nessun_nome_coautore) && $nessun_nome_coautore)) {
			// AL FINE DI PREVENIRE EVENTUALI DUPLICAZIONI, ABBIAMO DECISO DI EFFETTUARE UN CONTROLLO PER VALUTARE SE IL NOMINATIVO INSERITO DALL'AMMINISTRATORE RISULTA GIÀ PRESENTE ALL'INTERNO DEL FILE XML
			$duplicazione_nome=false;
			
			for($i=0; $i<$prodotti->length; $i++) {
				$prodotto=$prodotti->item($i);
				
				if($prodotto->firstChild->textContent==$_POST["nome"])
					$duplicazione_nome=true;
			}
			
			if(!$duplicazione_nome) {
				// PRIMA DI PROCEDERE OLTRE, BISOGNA EFFETTUARE DELLE VERIFICHE PRELIMINARI PER VALUTARE SE UN DETERMINATO VALORE ECCEDE LA DIMENSIONE INDICATA
				if(strlen($_POST["descrizione"])>1989) {
					// ***
					$superamento_descrizione=true;
				}
				else {
					// IL PREZZO E L'ANNO DI USCITA DEL PRODOTTO DOVRANNO ESSERE, RISPETTIVAMENTE, UN NUMERO CON QUATTRO CIFRE INTERE (AL PIÙ) E DUE DECIMALI E UN NUMERO INTERO CARATTERIZZATO DA QUATTRO CIFRE  
					if (preg_match("/([[:digit:]]{1,4}\.[[:digit:]]{2,2})/",$_POST["prezzoListino"], $matches_prezzo)) {
						if($matches_prezzo[0]!=$_POST["prezzoListino"]) {
							// ***
							$prezzo_errato=true;
						}
						else {
							if(preg_match("/([[:digit:]]{4,4})/",$_POST["annoUscita"], $matches_anno_uscita)) {
								if($matches_anno_uscita[0]!=$_POST["annoUscita"]) {
									// ***
									$anno_uscita_errato=true;
								}
								else {
									// PER DI PIÙ, È NECESSARIO CONSIDERARE LA VALIDITÀ DEL RIFERIMENTO TEMPORALE DI CUI SOPRA. INFATTI, SARANNO AMMESSI SOLTANTO DEGLI ANNI PRECEDENTI O UGUALI A QUELLO CORRENTE
									if(strtotime($_POST["annoUscita"])>strtotime(date("Y"))) {
										// ***
										$superamento_anno_uscita=true;
									}
									else {
										// ***
										if($_POST["tipo"]=="libro") {
											// ***
											if(strlen($_POST["nomeAutore"])>30) {
												// ***
												$superamento_nome_autore=true;
											}
											
											// ***
											if(strlen($_POST["cognomeAutore"])!=0 && strlen($_POST["cognomeAutore"])>35) {
												// ***
												$superamento_cognome_autore=true;
											}
											
											// ***
											if(strlen($_POST["nomeCoautore"])!=0 && strlen($_POST["nomeCoautore"])>30) {
												// ***
												$superamento_nome_coautore=true;
											}
											
											// ***
											if(strlen($_POST["cognomeCoautore"])!=0 && strlen($_POST["cognomeCoautore"])>35) {
												// ***
												$superamento_cognome_coautore=true;
											}
											
											// GIUNTI A QUESTO PUNTO, SE NESSUNO DEI PRECEDENTI CONTROLLI HA FATTO EMERGERE DELLE PROBLEMATICHE, SARÀ POSSIBILE PROCEDERE, UNA VOLTA CARICATO L'IMMAGINE SPECIFICATA ALL'INTERNO DELLA DIRECTORY DI INTERESSE,  CON L'EFFETTIVO INSERIMENTO DEL PRODOTTO ALL'INTERNO DELLA STRUTTURA DATI LUI DEDICATA
											if(!(isset($duplicazione_nome) && $duplicazione_nome) && !(isset($superamento_descrizione) && $superamento_descrizione) && !(isset($prezzo_errato) && $prezzo_errato) && !(isset($anno_uscita_errato) && $anno_uscita_errato) && !(isset($superamento_anno_uscita) && $superamento_anno_uscita) && !(isset($superamento_nome_autore) && $superamento_nome_autore) && !(isset($superamento_cognome_autore) && $superamento_cognome_autore) && !(isset($superamento_nome_coautore) && $superamento_nome_coautore) && !(isset($superamento_cognome_coautore) && $superamento_cognome_coautore)) {
												
												// COME ANTICIPATO, SI PROCEDE CON IL SALVATAGGIO DELL'IMMAGINE (.jpg) ALL'INTERNO DELLA CARTELLA AVENTE IL PERCORSO "../../Immagini/Catalogo/" TRAMITE IL METODO move_uploaded_file(...), IL QUALE CONSENTE DI GESTIRE ANCHE POSSIBILI DUPLICAZIONI DEL DOCUMENTO PREVIA SOVRASCRITTURA DEL PRECEDENTE
												move_uploaded_file($_FILES["immagine"]["tmp_name"], "../../Immagini/Catalogo/".$_FILES["immagine"]["name"]);
												
												// LA RAPPRESENTAZIONE DI UN PRODOTTO È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUEST'ULTIMO. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI INDICATI DALL'AMMINISTRATORE 
												$nuovo_prodotto=$docProdotti->createElement("prodotto");
												
												// CONTESTUALMENTE ALLA DEFINIZIONE DI UN INDICE PER L'ARTICOLO SUDDETTO, È PREVISTO L'ADEGUAMENTO DELL'ATTRIBUTO RELATIVO ALLA RADICE DEL DOCUMENTO E INERENTE AL NUMERO DI PRODOTTI INSERITI FINORA  
												$rootProdotti->setAttribute("ultimoId", $rootProdotti->getAttribute("ultimoId")+1);
												$nuovo_prodotto->setAttribute("id", $rootProdotti->getAttribute("ultimoId"));
												
												$nome=$docProdotti->createElement("nome", $_POST["nome"]);
												$nuovo_prodotto->appendChild($nome);
												
												$tipologia=$docProdotti->createElement("tipologia");
												
												$libro=$docProdotti->createElement("libro");
												
												$categorie_libro=$docProdotti->createElement("categorie");
												
												for($i=0; $i<$categorie->length; $i++) {
													$categoria=$categorie->item($i);
													
													if(isset($_POST["categoria_".$categoria->getAttribute("id")])) {
														$categoria_libro=$docProdotti->createElement("categoria");
														$categoria_libro->setAttribute("idCategoria", $categoria->getAttribute("id"));
														$categorie_libro->appendChild($categoria_libro);
													}
												}
												
												$libro->appendChild($categorie_libro);
												
												$autori=$docProdotti->createElement("autori");
												
												$autore=$docProdotti->createElement("autore");
												
												$nome_autore=$docProdotti->createElement("nome", $_POST["nomeAutore"]);
												
												$autore->appendChild($nome_autore);
												
												if(strlen($_POST["cognomeAutore"])!=0) {
													$cognome_autore=$docProdotti->createElement("cognome", $_POST["cognomeAutore"]);
													$autore->appendChild($cognome_autore);
												}
												
												$autori->appendChild($autore);
												
												if(strlen($_POST["nomeCoautore"])!=0) {
													$coautore=$docProdotti->createElement("autore");
												
													$nome_coautore=$docProdotti->createElement("nome", $_POST["nomeCoautore"]);
													
													$coautore->appendChild($nome_coautore);
													
													if(strlen($_POST["cognomeCoautore"])!=0) {
														$cognome_coautore=$docProdotti->createElement("cognome", $_POST["cognomeCoautore"]);
														$coautore->appendChild($cognome_coautore);
													}
													
													$autori->appendChild($coautore);
													
												}
												
												$libro->appendChild($autori);
												
												$tipologia->appendChild($libro);
												
												$nuovo_prodotto->appendChild($tipologia);
												
												$descrizione=$docProdotti->createElement("descrizione", $_POST["descrizione"]);
												
												$nuovo_prodotto->appendChild($descrizione);
												
												$immagine=$docProdotti->createElement("immagine", "../../Immagini/Catalogo/".$_FILES["immagine"]["name"]);
												
												$nuovo_prodotto->appendChild($immagine);
												
												$prezzoListino=$docProdotti->createElement("prezzoListino", $_POST["prezzoListino"]);
												
												$nuovo_prodotto->appendChild($prezzoListino);
												
												$annoUscita=$docProdotti->createElement("annoUscita", $_POST["annoUscita"]);
												
												$nuovo_prodotto->appendChild($annoUscita);
												
												$recensioni=$docProdotti->createElement("recensioni");
												
												$nuovo_prodotto->appendChild($recensioni);
												
												$discussioni=$docProdotti->createElement("discussioni");
												
												$nuovo_prodotto->appendChild($discussioni);
												
												$rootProdotti->appendChild($nuovo_prodotto);
												
												// AL FINE DI GARANTIRE UNA STAMPA OTTIMALE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
												$docProdotti->preserveWhiteSpace = false;
												$docProdotti->formatOutput = true;
												$docProdotti->save("../../XML/Prodotti.xml");
												
												// PER CONCLUDERE, POICHÈ SI HA A CHE FARE CON FILE INERENTE AD UNA GRAMMATICA DTD, SARÀ NECESSARIO CARICARE NUOVAMENTE IL DOCUMENTO PER PROCEDERE CON IL RELATIVO CONTROLLO DI VALIDITÀ
												$dom=new DOMDocument();
												$dom->load("../../XML/Prodotti.xml");
												
												if($dom->validate()){
													// PRIMA DI ESSERE REIDERIZZATI, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
													$_SESSION["modifica_Effettuata"]=true;
													
													header("Location: area_riservata.php");
												}
												else {
													// ***
													setcookie("errore_Validazione", true);
													
													header("Location: area_riservata.php");
												}
											}
										}
										else {
											// GIUNTI A QUESTO PUNTO, SE NESSUNO DEI PRECEDENTI CONTROLLI HA FATTO EMERGERE DELLE PROBLEMATICHE, SARÀ POSSIBILE PROCEDERE, UNA VOLTA CARICATO L'IMMAGINE SPECIFICATA ALL'INTERNO DELLA DIRECTORY DI INTERESSE,  CON L'EFFETTIVO INSERIMENTO DEL PRODOTTO ALL'INTERNO DELLA STRUTTURA DATI LUI DEDICATA
											if(!(isset($duplicazione_nome) && $duplicazione_nome) && !(isset($superamento_descrizione) && $superamento_descrizione) && !(isset($prezzo_errato) && $prezzo_errato) && !(isset($anno_uscita_errato) && $anno_uscita_errato) && !(isset($superamento_anno_uscita) && $superamento_anno_uscita)) {
												
												// COME ANTICIPATO, SI PROCEDE CON IL SALVATAGGIO DELL'IMMAGINE (.jpg) ALL'INTERNO DELLA CARTELLA AVENTE IL PERCORSO "../../Immagini/Catalogo/" TRAMITE IL METODO move_uploaded_file(...), IL QUALE CONSENTE DI GESTIRE ANCHE POSSIBILI DUPLICAZIONI DEL DOCUMENTO PREVIA SOVRASCRITTURA DEL PRECEDENTE
												move_uploaded_file($_FILES["immagine"]["tmp_name"], "../../Immagini/Catalogo/".$_FILES["immagine"]["name"]);
												
												// LA RAPPRESENTAZIONE DI UN PRODOTTO È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUEST'ULTIMA. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI INDICATI DALL'AMMINISTRATORE 
												$nuovo_prodotto=$docProdotti->createElement("prodotto");
												
												// CONTESTUALMENTE ALLA DEFINIZIONE DI UN INDICE PER L'ARTICOLO SUDDETTO, È PREVISTO L'ADEGUAMENTO DELL'ATTRIBUTO RELATIVO ALLA RADICE DEL DOCUMENTO E INERENTE AL NUMERO DI PRODOTTI INSERITI FINORA  
												$rootProdotti->setAttribute("ultimoId", $rootProdotti->getAttribute("ultimoId")+1);
												$nuovo_prodotto->setAttribute("id", $rootProdotti->getAttribute("ultimoId"));
												
												$nome=$docProdotti->createElement("nome", $_POST["nome"]);
												$nuovo_prodotto->appendChild($nome);
												
												$tipologia=$docProdotti->createElement("tipologia");
												
												$videogioco=$docProdotti->createElement("videogioco");
												
												for($i=0; $i<$piattaforme->length; $i++) {
													$piattaforma=$piattaforme->item($i);
													
													if($_POST["piattaforma"]==$piattaforma->getAttribute("id")) {
														$piattaforma_videogioco=$docProdotti->createElement("piattaforma");
														$piattaforma_videogioco->setAttribute("idPiattaforma", $piattaforma->getAttribute("id"));
														$videogioco->appendChild($piattaforma_videogioco);
														break;
													}
												}
												
												$generi_videogioco=$docProdotti->createElement("generi");
												
												for($i=0; $i<$generi->length; $i++) {
													$genere=$generi->item($i);
													
													if(isset($_POST["genere_".$genere->getAttribute("id")])) {
														$genere_videogioco=$docProdotti->createElement("genere");
														$genere_videogioco->setAttribute("idGenere", $genere->getAttribute("id"));
														$generi_videogioco->appendChild($genere_videogioco);
													}
												}
												
												$videogioco->appendChild($generi_videogioco);
												
												$casa_produzione=$docProdotti->createElement("casaProduzione", $_POST["casaProduzione"]);
												
												$videogioco->appendChild($casa_produzione);
												
												$tipologia->appendChild($videogioco);
												
												$nuovo_prodotto->appendChild($tipologia);
												
												$descrizione=$docProdotti->createElement("descrizione", $_POST["descrizione"]);
												
												$nuovo_prodotto->appendChild($descrizione);
												
												$immagine=$docProdotti->createElement("immagine", "../../Immagini/Catalogo/".$_FILES["immagine"]["name"]);
												
												$nuovo_prodotto->appendChild($immagine);
												
												$prezzoListino=$docProdotti->createElement("prezzoListino", $_POST["prezzoListino"]);
												
												$nuovo_prodotto->appendChild($prezzoListino);
												
												$annoUscita=$docProdotti->createElement("annoUscita", $_POST["annoUscita"]);
												
												$nuovo_prodotto->appendChild($annoUscita);
												
												$recensioni=$docProdotti->createElement("recensioni");
												
												$nuovo_prodotto->appendChild($recensioni);
												
												$discussioni=$docProdotti->createElement("discussioni");
												
												$nuovo_prodotto->appendChild($discussioni);
												
												$rootProdotti->appendChild($nuovo_prodotto);
												
												// AL FINE DI GARANTIRE UNA STAMPA OTTIMALE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
												$docProdotti->preserveWhiteSpace = false;
												$docProdotti->formatOutput = true;
												$docProdotti->save("../../XML/Prodotti.xml");
												
												// PER CONCLUDERE, POICHÈ SI HA A CHE FARE CON FILE INERENTE AD UNA GRAMMATICA DTD, SARÀ NECESSARIO CARICARE NUOVAMENTE IL DOCUMENTO PER PROCEDERE CON IL RELATIVO CONTROLLO DI VALIDITÀ
												$dom=new DOMDocument();
												$dom->load("../../XML/Prodotti.xml");
												
												if($dom->validate()){
													// PRIMA DI ESSERE REIDERIZZATI, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
													$_SESSION["modifica_Effettuata"]=true;
													
													header("Location: area_riservata.php");
												}
												else {
													// ***
													setcookie("errore_Validazione", true);
													
													header("Location: area_riservata.php");
												}
											}
										}
									}
								}
							}
							else {
								// ***
								$anno_uscita_errato=true;
							}
						}
					}
					else {
						// ***
						$prezzo_errato=true;
					}
				}
			}				
		}
	}
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>LEV: Libri &amp; Videogiochi</title>
		<link rel="icon" href="../../Immagini/Icona_LEV.png" />
		<link rel="stylesheet" href="../../Stili CSS/style_form.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_intestazione_sito.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_common.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_footer_sito.css" type="text/css" />
		<script type="text/javascript" src="../JavaScript/gestioneMenuTendina.js"></script>
		<script type="text/javascript" src="../JavaScript/gestioneSelezioneTipologiaProdotto.js"></script>
	</head>
	<body>
		<?php
			// DATA LA VARIETÀ DI CASISTICHE CHE SI POSSONO MANIFESTARE, ABBIAMO DECISO DI DEFINIRE UNA GERARCHIA DI CONTROLLI PER GESTIRE AL MEGLIO LE STAMPE DEI VARI MESSAGGI DI POPUP, I QUALI, MEDIANTE UNA SIMILE IMPOSTAZIONE, SARANNO PRESENTATI SINGOLARMENTE AI SOGGETTI INTERESSATI
			if(isset($campi_vuoti) && $campi_vuoti) { 
				
				// IN OCCASIONE DEL POSSIBILE RICARICAMENTO DELLA PAGINA, SI PROCEDE CON LA RIMOZIONE DEL FLAG ALLO SCOPO DI NON RIPROPORRE LA MEDESIMA NOTIFCA 
				$campi_vuoti=false;
				
				echo "<div class=\"error_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
				echo "\t\t\t\t\t<p>COMPILARE TUTTI I CAMPI...</p>\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t</div>\n";
				echo "\t\t</div>\n";
			}
			else {
				// ***
				if(isset($nessuna_immagine) && $nessuna_immagine) { 
					// *** 
					$nessuna_immagine=false;
					
					echo "<div class=\"error_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
					echo "\t\t\t\t\t<p>L'IMMAGINE DEL PRODOTTO NON &Egrave; STATA CARICATA CORRETTAMENTE...</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
				}
				else {
					// ***
					if(isset($nessun_tipo) && $nessun_tipo) { 
						// *** 
						$nessun_tipo=false;
						
						echo "<div class=\"error_message\">\n";
						echo "\t\t\t<div class=\"container_message\">\n";
						echo "\t\t\t\t<div class=\"container_img\">\n";
						echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t\t<div class=\"message\">\n";
						echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
						echo "\t\t\t\t\t<p>SPECIFICARE LA NATURA DEL PRODOTTO CHE SI INTENDE REGISTRARE...</p>\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t</div>\n";
						echo "\t\t</div>\n";
					}
					else {
						// ***
						if(isset($nessun_tipo) && $nessun_tipo) { 
							// *** 
							$nessun_tipo=false;
							
							echo "<div class=\"error_message\">\n";
							echo "\t\t\t<div class=\"container_message\">\n";
							echo "\t\t\t\t<div class=\"container_img\">\n";
							echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t\t<div class=\"message\">\n";
							echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
							echo "\t\t\t\t\t<p>SPECIFICARE LA NATURA DEL PRODOTTO CHE SI INTENDE REGISTRARE...</p>\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t</div>\n";
							echo "\t\t</div>\n";
						}
						else {
							// ***
							if(isset($tipo_errato) && $tipo_errato) { 
								// *** 
								$tipo_errato=false;
								
								echo "<div class=\"error_message\">\n";
								echo "\t\t\t<div class=\"container_message\">\n";
								echo "\t\t\t\t<div class=\"container_img\">\n";
								echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t\t<div class=\"message\">\n";
								echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
								echo "\t\t\t\t\t<p>LA TIPOLOGIA DEL PRODOTTO CHE SI STA CERCANDO DI REGISTRARE NON &Egrave; AMMESSA...</p>\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t</div>\n";
								echo "\t\t</div>\n";
							}
							else {
								// ***
								if(isset($nessuna_piattaforma) && $nessuna_piattaforma) { 
									// *** 
									$nessuna_piattaforma=false;
									
									echo "<div class=\"error_message\">\n";
									echo "\t\t\t<div class=\"container_message\">\n";
									echo "\t\t\t\t<div class=\"container_img\">\n";
									echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
									echo "\t\t\t\t</div>\n";	  
									echo "\t\t\t\t<div class=\"message\">\n";
									echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
									echo "\t\t\t\t\t<p>IL CAMPO INERENTE ALLA PIATTAFORMA DI GIOCO NON &Egrave; STATO COMPILATO...</p>\n";
									echo "\t\t\t\t</div>\n";	  
									echo "\t\t\t</div>\n";
									echo "\t\t</div>\n";
								}
								else {
									// ***
									if(isset($nessun_genere) && $nessun_genere) { 
										// *** 
										$nessun_genere=false;
										
										echo "<div class=\"error_message\">\n";
										echo "\t\t\t<div class=\"container_message\">\n";
										echo "\t\t\t\t<div class=\"container_img\">\n";
										echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t</div>\n";	  
										echo "\t\t\t\t<div class=\"message\">\n";
										echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
										echo "\t\t\t\t\t<p>SPECIFICARE I GENERI A CUI APPARTIENE IL PRODOTTO CHE SI INTENDE REGISTRARE...</p>\n";
										echo "\t\t\t\t</div>\n";	  
										echo "\t\t\t</div>\n";
										echo "\t\t</div>\n";
									}
									else {
										// ***
										if(isset($nessuna_casa_produzione) && $nessuna_casa_produzione) { 
											// *** 
											$nessuna_casa_produzione=false;
											
											echo "<div class=\"error_message\">\n";
											echo "\t\t\t<div class=\"container_message\">\n";
											echo "\t\t\t\t<div class=\"container_img\">\n";
											echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
											echo "\t\t\t\t</div>\n";	  
											echo "\t\t\t\t<div class=\"message\">\n";
											echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
											echo "\t\t\t\t\t<p>IL CAMPO INERENTE ALLA CASA DI PRODUZIONE DEL VIDEOGIOCO NON &Egrave; STATO COMPILATO...</p>\n";
											echo "\t\t\t\t</div>\n";	  
											echo "\t\t\t</div>\n";
											echo "\t\t</div>\n";
										}
										else {
											// ***
											if(isset($nessuna_categoria) && $nessuna_categoria) { 
												// *** 
												$nessuna_categoria=false;
												
												echo "<div class=\"error_message\">\n";
												echo "\t\t\t<div class=\"container_message\">\n";
												echo "\t\t\t\t<div class=\"container_img\">\n";
												echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
												echo "\t\t\t\t</div>\n";	  
												echo "\t\t\t\t<div class=\"message\">\n";
												echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
												echo "\t\t\t\t\t<p>SPECIFICARE LE CATEGORIE LETTERARIE A CUI APPARTIENE IL PRODOTTO CHE SI INTENDE REGISTRARE...</p>\n";
												echo "\t\t\t\t</div>\n";	  
												echo "\t\t\t</div>\n";
												echo "\t\t</div>\n";
											}
											else {
												// ***
												if(isset($nessun_nome_autore) && $nessun_nome_autore) { 
													// *** 
													$nessun_nome_autore=false;
													
													echo "<div class=\"error_message\">\n";
													echo "\t\t\t<div class=\"container_message\">\n";
													echo "\t\t\t\t<div class=\"container_img\">\n";
													echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
													echo "\t\t\t\t</div>\n";	  
													echo "\t\t\t\t<div class=\"message\">\n";
													echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
													echo "\t\t\t\t\t<p>IL CAMPO INERENTE AL NOME DELL'AUTORE DELL'OPERA NON &Egrave; STATO COMPILATO...</p>\n";
													echo "\t\t\t\t</div>\n";	  
													echo "\t\t\t</div>\n";
													echo "\t\t</div>\n";
												}
												else {
													// ***
													if(isset($nessun_nome_coautore) && $nessun_nome_coautore) { 
														// *** 
														$nessun_nome_coautore=false;
														
														echo "<div class=\"error_message\">\n";
														echo "\t\t\t<div class=\"container_message\">\n";
														echo "\t\t\t\t<div class=\"container_img\">\n";
														echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
														echo "\t\t\t\t</div>\n";	  
														echo "\t\t\t\t<div class=\"message\">\n";
														echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
														echo "\t\t\t\t\t<p>IL CAMPO INERENTE AL NOME DEL COAUTORE DELL'OPERA NON &Egrave; STATO COMPILATO...</p>\n";
														echo "\t\t\t\t</div>\n";	  
														echo "\t\t\t</div>\n";
														echo "\t\t</div>\n";
													}
													else {
														// ***
														if(isset($duplicazione_nome) && $duplicazione_nome) { 
															// *** 
															$duplicazione_nome=false;
															
															echo "<div class=\"error_message\">\n";
															echo "\t\t\t<div class=\"container_message\">\n";
															echo "\t\t\t\t<div class=\"container_img\">\n";
															echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
															echo "\t\t\t\t</div>\n";	  
															echo "\t\t\t\t<div class=\"message\">\n";
															echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
															echo "\t\t\t\t\t<p>IL PRODOTTO INDICATO &Egrave; STATO INSERITO PRECEDENTEMENTE...</p>\n";
															echo "\t\t\t\t</div>\n";	  
															echo "\t\t\t</div>\n";
															echo "\t\t</div>\n";
														}
														else {
															// ***
															if(isset($superamento_descrizione) && $superamento_descrizione) { 
																// *** 
																$superamento_descrizione=false;
																
																echo "<div class=\"error_message\">\n";
																echo "\t\t\t<div class=\"container_message\">\n";
																echo "\t\t\t\t<div class=\"container_img\">\n";
																echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
																echo "\t\t\t\t</div>\n";	  
																echo "\t\t\t\t<div class=\"message\">\n";
																echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
																echo "\t\t\t\t\t<p>LA DIMENSIONE DELLA DESCRIZIONE DEL PRODOTTO ECCEDE IL NUMERO MASSIMO DI CARATTERI CONSENTITI...</p>\n";
																echo "\t\t\t\t</div>\n";	  
																echo "\t\t\t</div>\n";
																echo "\t\t</div>\n";
															}
															else {
																// ***
																if(isset($prezzo_errato) && $prezzo_errato) { 
																	// *** 
																	$prezzo_errato=false;
																	
																	echo "<div class=\"error_message\">\n";
																	echo "\t\t\t<div class=\"container_message\">\n";
																	echo "\t\t\t\t<div class=\"container_img\">\n";
																	echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
																	echo "\t\t\t\t</div>\n";	  
																	echo "\t\t\t\t<div class=\"message\">\n";
																	echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
																	echo "\t\t\t\t\t<p>IL PREZZO NON RISPETTA IL FORMATO INDICATO...</p>\n";
																	echo "\t\t\t\t</div>\n";	  
																	echo "\t\t\t</div>\n";
																	echo "\t\t</div>\n";
																}
																else {
																	// ***
																	if(isset($anno_uscita_errato) && $anno_uscita_errato) { 
																		// *** 
																		$anno_uscita_errato=false;
																		
																		echo "<div class=\"error_message\">\n";
																		echo "\t\t\t<div class=\"container_message\">\n";
																		echo "\t\t\t\t<div class=\"container_img\">\n";
																		echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
																		echo "\t\t\t\t</div>\n";	  
																		echo "\t\t\t\t<div class=\"message\">\n";
																		echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
																		echo "\t\t\t\t\t<p>L'ANNO DI USCITA NON RISPETTA IL FORMATO INDICATO...</p>\n";
																		echo "\t\t\t\t</div>\n";	  
																		echo "\t\t\t</div>\n";
																		echo "\t\t</div>\n";
																	}
																	else {
																		// ***
																		if(isset($superamento_anno_uscita) && $superamento_anno_uscita) { 
																			// *** 
																			$superamento_anno_uscita=false;
																			
																			echo "<div class=\"error_message\">\n";
																			echo "\t\t\t<div class=\"container_message\">\n";
																			echo "\t\t\t\t<div class=\"container_img\">\n";
																			echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
																			echo "\t\t\t\t</div>\n";	  
																			echo "\t\t\t\t<div class=\"message\">\n";
																			echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
																			echo "\t\t\t\t\t<p>L'ANNO DI USCITA NON RISULTA VALIDO POICH&Egrave; ECCEDE QUELLO CORRENTE...</p>\n";
																			echo "\t\t\t\t</div>\n";	  
																			echo "\t\t\t</div>\n";
																			echo "\t\t</div>\n";
																		}
																		else {
																			// ***
																			if(isset($superamento_nome_autore) && $superamento_nome_autore) { 
																				// *** 
																				$superamento_nome_autore=false;
																				
																				echo "<div class=\"error_message\">\n";
																				echo "\t\t\t<div class=\"container_message\">\n";
																				echo "\t\t\t\t<div class=\"container_img\">\n";
																				echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
																				echo "\t\t\t\t</div>\n";	  
																				echo "\t\t\t\t<div class=\"message\">\n";
																				echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
																				echo "\t\t\t\t\t<p>LA DIMENSIONE DEL NOME DELL'AUTORE ECCEDE IL NUMERO MASSIMO DI CARATTERI CONSENTITI...</p>\n";
																				echo "\t\t\t\t</div>\n";	  
																				echo "\t\t\t</div>\n";
																				echo "\t\t</div>\n";
																			}
																			else {
																				// ***
																				if(isset($superamento_cognome_autore) && $superamento_cognome_autore) { 
																					// *** 
																					$superamento_cognome_autore=false;
																					
																					echo "<div class=\"error_message\">\n";
																					echo "\t\t\t<div class=\"container_message\">\n";
																					echo "\t\t\t\t<div class=\"container_img\">\n";
																					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
																					echo "\t\t\t\t</div>\n";	  
																					echo "\t\t\t\t<div class=\"message\">\n";
																					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
																					echo "\t\t\t\t\t<p>LA DIMENSIONE DEL COGNOME DELL'AUTORE ECCEDE IL NUMERO MASSIMO DI CARATTERI CONSENTITI...</p>\n";
																					echo "\t\t\t\t</div>\n";	  
																					echo "\t\t\t</div>\n";
																					echo "\t\t</div>\n";
																				}
																				else {
																					// ***
																					if(isset($superamento_nome_coautore) && $superamento_nome_coautore) { 
																						// *** 
																						$superamento_nome_coautore=false;
																						
																						echo "<div class=\"error_message\">\n";
																						echo "\t\t\t<div class=\"container_message\">\n";
																						echo "\t\t\t\t<div class=\"container_img\">\n";
																						echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
																						echo "\t\t\t\t</div>\n";	  
																						echo "\t\t\t\t<div class=\"message\">\n";
																						echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
																						echo "\t\t\t\t\t<p>LA DIMENSIONE DEL NOME DEL COAUTORE ECCEDE IL NUMERO MASSIMO DI CARATTERI CONSENTITI...</p>\n";
																						echo "\t\t\t\t</div>\n";	  
																						echo "\t\t\t</div>\n";
																						echo "\t\t</div>\n";
																					}
																					else {
																						// ***
																						if(isset($superamento_cognome_coautore) && $superamento_cognome_coautore) { 
																							// *** 
																							$superamento_cognome_coautore=false;
																							
																							echo "<div class=\"error_message\">\n";
																							echo "\t\t\t<div class=\"container_message\">\n";
																							echo "\t\t\t\t<div class=\"container_img\">\n";
																							echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
																							echo "\t\t\t\t</div>\n";	  
																							echo "\t\t\t\t<div class=\"message\">\n";
																							echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
																							echo "\t\t\t\t\t<p>LA DIMENSIONE DEL COGNOME DEL COAUTORE ECCEDE IL NUMERO MASSIMO DI CARATTERI CONSENTITI...</p>\n";
																							echo "\t\t\t\t</div>\n";	  
																							echo "\t\t\t</div>\n";
																							echo "\t\t</div>\n";
																						}
																					}
																				}
																			}
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
			
			// L'INTESTAZIONE DEL SITO, POICHÈ SI TRATTA DI UN DETTAGLIO RICORRENTE, È STATA DEFINITA ALL'INTERNO DI UN PROGRAMMA INDIPENDENTE CHE, DATE LE ESIGENZE, VERRÀ INCLUSO A PIÙ RIPRESE NEI VARI SCRIPT
			require_once("./intestazione_sito.php");
		?>
		<div class="corpo_pagina">
			<div class="container_corpo_pagina">
				<div class="form">
					<div class="container_form">
						<div class="intestazione_form">
							<div class="container_intestazione_form">
								<span class="icona_form">
									<img src="../../Immagini/box-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Aggiungi un nuovo prodotto!</h2>
							</div>
						</div>
						<?php // PER POTER INTERAGIRE CORRETTAMENTE CON I FILE DA CARICARE, È NECESSARIO INTRODURRE L'ATTRIBUTO "enctype" COSÌ CHE POSSANO ESSERE INTERPRETATI COME TALI E NON COME DELLE STRINGHE ?>
						<form class="corpo_form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Profilo Gestionale (Obbligatorio)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Denominazione
											</p>
											<p>
												<input type="text" name="nome" value="<?php if(isset($_POST['nome'])) echo $_POST['nome']; else echo '';?>"  />
											</p>		
										</div>
										<div class="campo_descrizione">
											<p>
												Descrizione (max. 1989 caratteri)
											</p>
											<p>
												<textarea name="descrizione" rows="0" cols="0"><?php if(isset($_POST['descrizione'])) echo $_POST['descrizione']; else echo '';?></textarea>
											</p>	
										</div>
										<div class="campo">
											<p>
												Immagine (.jpg)
											</p>
											<p>
												<input type="file" name="immagine" accept=".jpg" />
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> L'immagine, oltre a riportare l'estensione indicata, non potr&agrave; avere una dimensione superiore ai 2 MB.</p>		
										<div class="campo">
											<p>
												Prezzo di Listino
											</p>
											<p>
												<input type="text" name="prezzoListino" value="<?php if(isset($_POST['prezzoListino'])) echo $_POST['prezzoListino']; else echo '';?>"  />
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> Il valore di listino del bene dovr&agrave; essere rappresentato da un numero con quattro cifre intere (al pi&ugrave;) e due decimali, quest'ultime separate dalle prime tramite un punto.</p>		
										<div class="campo">
											<p>
												Anno (max. quello corrente)
											</p>
											<p>
												<input type="text" name="annoUscita" value="<?php if(isset($_POST['annoUscita'])) echo $_POST['annoUscita']; else echo '';?>"  />
											</p>	
										</div>	
										<p class="nota"><strong>N.B.</strong> Il campo di cui sopra, oltre ad essere una sequenza numerica di quattro cifre, indica l'anno in cui l'articolo d'interesse &egrave; stato distribuito per la prima volta.</p>		
									</div>
								</div>
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Tipologia (Obbligatorio)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p style="display: flex; align-items: center; width: 100%;">
												<?php
													if(isset($_POST["tipo"]) && $_POST["tipo"]=="libro")
														echo "<input type=\"radio\" name=\"tipo\" checked=\"checked\" value=\"libro\" id=\"radio_libro\" onclick=\"gestioneSelezioneTipologiaProdotto()\" />\n";
													else
														echo "<input type=\"radio\" name=\"tipo\" value=\"libro\" id=\"radio_libro\" onclick=\"gestioneSelezioneTipologiaProdotto()\" />\n";
												?>
												Libro
											</p>
										</div>
										<div class="campo">
											<p style="display: flex; align-items: center; width: 100%;">
												<?php
													if(isset($_POST["tipo"]) && $_POST["tipo"]=="videogioco")
														echo "<input type=\"radio\" name=\"tipo\" checked=\"checked\" value=\"videogioco\" id=\"radio_videogioco\" onclick=\"gestioneSelezioneTipologiaProdotto()\" />\n";
													else
														echo "<input type=\"radio\" name=\"tipo\" value=\"videogioco\" id=\"radio_videogioco\" onclick=\"gestioneSelezioneTipologiaProdotto()\" />\n";
												?>
												Videogioco
											</p>
										</div>
										<p class="nota"><strong>N.B.</strong> Gli ultimi dettagli per completare la registrazione dell'articolo saranno resi accessibili non appena verr&agrave; selezionata una delle voci presentate.</p>		
									</div>
								</div>
								<?php
									if(isset($_POST["tipo"]) && $_POST["tipo"]=="libro")
										echo "<div class=\"intestazione_sezione\" id=\"intestazione_categorie_libri\">\n";
									else
										echo "<div class=\"intestazione_sezione nascondi\" id=\"intestazione_categorie_libri\">\n";
								?> 
									<div class="container_intestazione_sezione">
										<span>
											Categorie (Obbligatorio)
										</span>
									</div>
								</div>
								<?php
									if(isset($_POST["tipo"]) && $_POST["tipo"]=="libro")
										echo "<div class=\"elenco_campi mostra\" id=\"elenco_categorie_libri\">\n";
									else
										echo "<div class=\"elenco_campi nascondi\" id=\"elenco_categorie_libri\">\n";
								?>
									<div class="container_elenco_campi">
										<?php
											for($i=0; $i<$categorie->length; $i++) {
												$categoria=$categorie->item($i);
												
												// PER QUESTIONI DI TABULAZIONE, È STATO NECESSARIO DISCRIMINARE L'ELEMENTO A CUI SI STA FACENDO ATTUALMENTE RIFERIMENTO. INFATTI, A PARTIRE DALLA SECONDA VOCE SARÀ NECESSARIO ATTRIBUIRE UNA DETERMINATA FORMATTAZIONE ANCHE ALL'OGGETTO DI PARTENZA
												if($i==0)
													echo "<div class=\"campo\">\n";
												else {
													echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo\">\n";
												}
												
												echo "\t\t\t\t\t\t\t\t\t\t\t<p style=\"display: flex; align-items: center; width: 100%;\">\n";
												
												if(isset($_POST["categoria_".$categoria->getAttribute("id")]))
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" name=\"categoria_".$categoria->getAttribute("id")."\" checked=\"checked\" value=\"".$categoria->getAttribute("id")."\" />\n";
												else
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" name=\"categoria_".$categoria->getAttribute("id")."\" value=\"".$categoria->getAttribute("id")."\" />\n";
												
												echo "\t\t\t\t\t\t\t\t\t\t\t\t".$categoria->firstChild->textContent."\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
											}
										?>
										<p class="nota"><strong>N.B.</strong> Le categorie esposte rappresentano l'insieme di tutti i possibili generi letterari tra cui &egrave; possibile scegliere per poter catalogare correttamente il testo in esame.</p>		
									</div>
								</div>
								<?php
									if(isset($_POST["tipo"]) && $_POST["tipo"]=="libro")
										echo "<div class=\"intestazione_sezione mostra\" id=\"intestazione_autore_libro\">\n";
									else
										echo "<div class=\"intestazione_sezione nascondi\" id=\"intestazione_autore_libro\">\n";
								?>
									<div class="container_intestazione_sezione">
										<span>
											Autore (Obbligatorio)
										</span>
									</div>
								</div>
								<?php
									if(isset($_POST["tipo"]) && $_POST["tipo"]=="libro")
										echo "<div class=\"elenco_campi mostra\" id=\"campi_autore_libro\">\n";
									else
										echo "<div class=\"elenco_campi nascondi\" id=\"campi_autore_libro\">\n";
								?>
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Nome (max. 30 caratteri)
											</p>
											<p>
												<input type="text" name="nomeAutore" value="<?php if(isset($_POST['nomeAutore'])) echo $_POST['nomeAutore']; else echo '';?>" id="nome_autore_libro" />
											</p>	
										</div>
										<div class="campo">
											<p>
												Cognome (max. 35 caratteri)
											</p>
											<p>
												<input type="text" name="cognomeAutore" value="<?php if(isset($_POST['cognomeAutore'])) echo $_POST['cognomeAutore']; else echo '';?>" id="cognome_autore_libro" />
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> L'autore dell'opera, cos&igrave; come il suo eventuale coautore, pu&ograve; essere talvolta noto ai pi&ugrave; tramite uno pseudonimo d'arte. In questi casi, sar&agrave; possibile ometterne il cognome.</p>		
									</div>
								</div>
								<?php
									if(isset($_POST["tipo"]) && $_POST["tipo"]=="libro")
										echo "<div class=\"intestazione_sezione mostra\" id=\"intestazione_coautore_libro\">\n";
									else
										echo "<div class=\"intestazione_sezione nascondi\" id=\"intestazione_coautore_libro\">\n";
								?>
									<div class="container_intestazione_sezione">
										<span>
											Coautore (Facoltativo)
										</span>
									</div>
								</div>
								<?php
									if(isset($_POST["tipo"]) && $_POST["tipo"]=="libro")
										echo "<div class=\"elenco_campi mostra\" id=\"campi_coautore_libro\">\n";
									else
										echo "<div class=\"elenco_campi nascondi\" id=\"campi_coautore_libro\">\n";
								?>
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Nome (max. 30 caratteri)
											</p>
											<p>
												<input type="text" name="nomeCoautore" value="<?php if(isset($_POST['nomeCoautore'])) echo $_POST['nomeCoautore']; else echo '';?>"  id="nome_coautore_libro" />
											</p>	
										</div>
										<div class="campo">
											<p>
												Cognome (max. 35 caratteri)
											</p>
											<p>
												<input type="text" name="cognomeCoautore" value="<?php if(isset($_POST['cognomeCoautore'])) echo $_POST['cognomeCoautore']; else echo '';?>"  id="cognome_coautore_libro" />
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> L'eventuale coautore dell'opera, cos&igrave; come il suo autore, pu&ograve; essere talvolta noto ai pi&ugrave; tramite uno pseudonimo d'arte. In questi casi, sar&agrave; possibile ometterne il cognome.</p>		
									</div>
								</div>
								<?php
									if(isset($_POST["tipo"]) && $_POST["tipo"]=="videogioco")
										echo "<div class=\"intestazione_sezione mostra\" id=\"intestazione_piattaforme_videogiochi\">\n";
									else
										echo "<div class=\"intestazione_sezione nascondi\" id=\"intestazione_piattaforme_videogiochi\">\n";
								?>
									<div class="container_intestazione_sezione">
										<span>
											Piattaforma (Obbligatorio)
										</span>
									</div>
								</div>
								<?php
									if(isset($_POST["tipo"]) && $_POST["tipo"]=="videogioco")
										echo "<div class=\"elenco_campi mostra\" id=\"elenco_piattaforme_videogiochi\">\n";
									else
										echo "<div class=\"elenco_campi nascondi\" id=\"elenco_piattaforme_videogiochi\">\n";
								?>
									<div class="container_elenco_campi">
										<?php
											for($i=0; $i<$piattaforme->length; $i++) {
												$piattaforma=$piattaforme->item($i);
												
												// ***
												if($i==0)
													echo "<div class=\"campo\">\n";
												else {
													echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo\">\n";
												}
												
												echo "\t\t\t\t\t\t\t\t\t\t\t<p style=\"display: flex; align-items: center; width: 100%;\">\n";
												
												if(isset($_POST["piattaforma"]) && $_POST["piattaforma"]==$piattaforma->getAttribute("id"))
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"radio\" name=\"piattaforma\" checked=\"checked\" value=\"".$piattaforma->getAttribute("id")."\" />\n";
												else
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"radio\" name=\"piattaforma\" value=\"".$piattaforma->getAttribute("id")."\" />\n";
												
												echo "\t\t\t\t\t\t\t\t\t\t\t\t".$piattaforma->firstChild->textContent."\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
											}
										?>
										<p class="nota"><strong>N.B.</strong> Le piattaforme esposte rappresentano l'insieme di tutte le possibili console tra cui &egrave; possibile scegliere per poter catalogare correttamente il videogioco in esame.</p>		
									</div>
								</div>
								<?php
									if(isset($_POST["tipo"]) && $_POST["tipo"]=="videogioco")
										echo "<div class=\"intestazione_sezione mostra\" id=\"intestazione_generi_videogiochi\">\n";
									else
										echo "<div class=\"intestazione_sezione nascondi\" id=\"intestazione_generi_videogiochi\">\n";
								?>
									<div class="container_intestazione_sezione">
										<span>
											Generi (Obbligatorio)
										</span>
									</div>
								</div>
								<?php
									if(isset($_POST["tipo"]) && $_POST["tipo"]=="videogioco")
										echo "<div class=\"elenco_campi mostra\" id=\"elenco_generi_videogiochi\">\n";
									else
										echo "<div class=\"elenco_campi nascondi\" id=\"elenco_generi_videogiochi\">\n";
								?>
									<div class="container_elenco_campi">
										<?php
											for($i=0; $i<$generi->length; $i++) {
												$genere=$generi->item($i);
												
												// ***
												if($i==0)
													echo "<div class=\"campo\">\n";
												else {
													echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo\">\n";
												}
												
												echo "\t\t\t\t\t\t\t\t\t\t\t<p style=\"display: flex; align-items: center; width: 100%;\">\n";
												
												if(isset($_POST["genere_".$genere->getAttribute("id")]))
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" name=\"genere_".$genere->getAttribute("id")."\" checked=\"checked\" value=\"".$genere->getAttribute("id")."\" />\n";
												else
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" name=\"genere_".$genere->getAttribute("id")."\" value=\"".$genere->getAttribute("id")."\" />\n";
												
												echo "\t\t\t\t\t\t\t\t\t\t\t\t".$genere->firstChild->textContent."\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
											}
										?>
										<p class="nota"><strong>N.B.</strong> I generi esposti rappresentano l'insieme di tutte le possibili tematiche tra cui &egrave; possibile scegliere per poter catalogare correttamente il videogioco in esame.</p>		
									</div>
								</div>
								<?php
									if(isset($_POST["tipo"]) && $_POST["tipo"]=="videogioco")
										echo "<div class=\"intestazione_sezione mostra\" id=\"intestazione_casa_produzione_videogioco\">\n";
									else
										echo "<div class=\"intestazione_sezione nascondi\" id=\"intestazione_casa_produzione_videogioco\">\n";
								?>
									<div class="container_intestazione_sezione">
										<span>
											Casa di Produzione (Obbligatorio)
										</span>
									</div>
								</div>
								<?php
									if(isset($_POST["tipo"]) && $_POST["tipo"]=="videogioco")
										echo "<div class=\"elenco_campi mostra\" id=\"campi_casa_produzione_videogioco\">\n";
									else
										echo "<div class=\"elenco_campi nascondi\" id=\"campi_casa_produzione_videogioco\">\n";
								?>
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Denominazione
											</p>
											<p>
												<input type="text" name="casaProduzione" value="<?php if(isset($_POST['casaProduzione'])) echo $_POST['casaProduzione']; else echo '';?>"  id="casa_produzione_videogioco" />
											</p>		
										</div>
										<p class="nota"><strong>N.B.</strong> La casa di produzione identifica la ditta informatica che si occupa dello sviluppo e a cui appartengono tutti i diritti dell'opera videoludica che si intende registrare.</p>		
									</div>
								</div>
								<div class="pulsante">
									<button type="submit" name="back" class="container_pulsante back">Annulla!</button>
									<button type="submit" name="confirm" class="container_pulsante">Conferma!</button>
								</div>  
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
			// IN AGGIUNTA, SEGUENDO GLI STESSI RAGIONAMENTI APPLICATI PER L'INTESTAZIONE, È STATO RITENUTO OPPORTUNO RICHIAMARE IL PIÈ DI PAGINA ALL'INTERNO DI TUTTE QUELLE SCHERMATE IN CUI SE NE MANIFESTA IL BISOGNO
			require_once ("./footer_sito.php");
		?>
	</body>
</html>