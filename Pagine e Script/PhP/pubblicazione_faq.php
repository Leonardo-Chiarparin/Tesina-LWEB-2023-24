<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA COMPILARE PER INSERIRE UNA NUOVA FAQ ALL'INTERNO DELLA SEZIONE LORO DEDICATA. IN PARTICOLARE, QUALORA L'AMMINISTRATORE ABBIA SELEZIONATO UN CERTO CONTRIBUTO (DISCUSSIONE O INTERVENTO), SARÀ CONSTRETTO A TENERLO IN CONSIDERAZIONE PER LA PROPRIO PUBBLICAZIONE. D'ALTRO CANTO, E DUNQUE IN ASSENZA DEI PRECEDENTI RIFERIMENTI, SARÀ TENUTO A SCRIVERE DI SANA PIANTA LE VARIE COMPONENTI DI INTERESSE 
	// N.B.: IN CASO DI ERRORE, LE INFORMAZIONI INSERITE, COSÌ COME LE SCELTE EFFETTUATE, VERRANNO PRESERVATE TRAMITE UNA SERIE DI CONTROLLI APPLICATI AL SOLO SCOPO DI AGEVOLARE L'OPERATO DEI VARI UTENTI D'INTERESSE
	
	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");

	// PER QUESTIONI DI SICUREZZA, È STATO NECESSARIO L'INSERIMENTO DELLO SCRIPT "private_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA. QUESTO MECCANISMO NON PERMETTERÀ L'ACCESSO AGLI UTENTI CHE NON SI AUTENTICATI TRAMITE IL RELATIVO SERVIZIO
	require_once("./private_session_control.php");
	
	// LE PROPOSTE DI VENDITA, DATA LA LORO STRUTTURA, POTREBBERO ESSERE SOGGETTE A DELLE RIDUZIONI DI PREZZO, CARATTERIZZATE DA UN INTERVALLO DI VALIDITÀ BEN DEFINITO E TALVOLTA APPLICABILI SUL PROSSIMO ACQUISTO. PERTANTO, NELLE IPOTESI IN CUI NON SIA SEMPRE POSSIBILE RIMUOVERE MANUALMENTE LE VOCI INTERESSATE, ABBIAMO DECISO DI IMPLEMENTARE UN CODICE IN GRADO DI INTERVENIRE IN AUTOMATICO SU TUTTI QUELLI SCONTI NON PIÙ USUFRUIBILI DAI CLIENTI DELLA PIATTAFORMA
	require_once("./rimozione_sconti.php");
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO ALL'AMMINISTRATORE DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="A") {
		header("Location: area_riservata.php");
	}
	
	// LA PAGINA IN QUESTIONE SARÀ RAGGIUNGIBILE SOLTANTO DOPO AVER PREMUTO IL PULSANTE PER LA PUBBLICAZIONE DI UNA NUOVA FAQ SENZA ALCUN TIPO DI RIFERIMENTO OPPURE QUELLI PER CREARLA A PARTIRE DA UN CERTO CONTRIBUTO (DISCUSSIONE O INTERVENTO) 
	if(!(empty($_GET) xor (isset($_GET["id_Offerta"]) && isset($_GET["id_Discussione"]))))
		header("Location: index.php");
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_faq.php");
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_discussioni.php");
	
	// NELL'OTTICA DI VOLER MANTENERE UN CERTO LIVELLO DI ROBUSTEZZA, ABBIAMO DECISO DI INTRODURRE DEI CONTROLLI PER VALUTARE SE L'OFFERTA A CUI SI RIFERISCE L'IDENTIFICATORE ESISTE REALMENTE O MENO
	if(isset($_GET["id_Offerta"])) {
		$offerta_individuata=false;
		
		for($i=0; $i<$offerte->length; $i++) {
			$offerta=$offerte->item($i);
			
			// UNA VOLTA INDIVIDUATA LA PROPOSTA DI INTERESSE, SI POTRÀ INTERROMPERE LA RICERCA, IN QUANTO L'ENTITÀ CHE LA RAPPRESENTA SARÀ IMPIEGATA ALL'INTERNO DI SUCCESSIVE OPERAZIONI
			if($offerta->getAttribute("id")==$_GET["id_Offerta"]) {
				$offerta_individuata=true;
				break;
			}
		}
		
		if($offerta_individuata==false) {
			header("Location: index.php");
		}
		
		// PER ULTERIORI RAGIONI DI SICUREZZA, SI HA LA NECESSITÀ DI INDIVIDUARE IL PRODOTTO A CUI SI RIFERISCE L'EVENTUALE OFFERTA DI PARTENZA 
		for($i=0; $i<$prodotti->length; $i++) {
			$prodotto=$prodotti->item($i);
			
			if($prodotto->getAttribute("id")==$offerta->getAttribute("idProdotto"))
				break;
		}
		
	}
	
	// DATA LA LORO SOMIGLIANZA, IL MECCANISMO APPLICATO IN PRECEDENZA PER LE OFFERTE POTRÀ ESSERE ESTESO ANCHE PER I SINGOLI ELEMENTI DA ELEVARE
	if(isset($_GET["id_Discussione"])) {
		$discussione_individuata=false;
		
		for($i=0; $i<$discussioni->length; $i++) {
			$discussione=$discussioni->item($i);
			
			// ***
			if($discussione->getAttribute("id")==$_GET["id_Discussione"]) {
				$discussione_individuata=true;
				break;
			}
		}
		
		// NEL CASO IN CUI IL CONTRIBUTO INDICATO NON SIA PRESENTE ALL'INTERNO DEL RELATIVO FILE XML O SE È GIÀ STATO MODERATO, SI PROVVEDERÀ A REINDIRIZZARE L'UTENTE VERSO LA SCHERMATA DI RIEPILOGO DELLA PROPOSTA DI VENDITA
		if($discussione_individuata==false || $discussione->getAttribute("moderata")=="Si") {
			$id_Offerta=$_GET["id_Offerta"];
		
			header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
		}
		
		// INOLTRE, BISOGNERÀ FARE IN MODO CHE L'ELEMENTO DI INTERESSE SIA EFFETTIVAMENTE RICONDUCIBILE AL PRODOTTO A CUI SI RIFERISCE L'OFFERTA SELEZIONATA
		$contributo_esistente=false;
		
		for($i=0; $i<$prodotto->getElementsByTagName("discussione")->length && !$contributo_esistente; $i++) {
			if($prodotto->getElementsByTagName("discussione")->item($i)->getAttribute("idDiscussione")==$discussione->getAttribute("id"))
				$contributo_esistente=true;
		}
		
		if(!$contributo_esistente) {
			$id_Offerta=$_GET["id_Offerta"];
			
			header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
		}
		
		// ***
		if(isset($_GET["id_Intervento"])) {
			$intervento_individuato=false;
		
			for($i=0; $i<$discussione->getElementsByTagName("intervento")->length; $i++) {
				$intervento=$discussione->getElementsByTagName("intervento")->item($i);
				
				// ***
				if($intervento->getAttribute("id")==$_GET["id_Intervento"]) {
					$intervento_individuato=true;
					break;
				}
			}
			
			if($intervento_individuato==false || $intervento->getAttribute("moderato")=="Si") {
				$id_Offerta=$_GET["id_Offerta"];
			
				header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
			}
		}
	}
	
	// A SEGUITO DELL'INDIVIDUAZIONE DI UN DETERMINATO ERRORE, LA PAGINA VERRÀ RICARICATA MOSTRANDO IL RELATIVO MESSAGGIO DI POPUP. PROPRIO PER QUESTO, LE INFORMAZIONI PRECEDENTEMENTE CONTENUTE ALL'INTERNO DEL VETTORE RELATIVO AI PARAMETRI PASSATI TRAMITE METODO GET VERREBBERO PERDUTI
	// IN QUEST'OTTICA, ABBIAMO DECISO DI AGGIUNGERE UNA VARIABILE CHE, ALL'OCCORRENZA, MANTERRÀ GLI IDENTIFICATORI DEI CAMPI SELEZIONATI AL SOLO SCOPO DI RIPRISTINARNE, PREVIA COSTANTE ASSEGNAMENTO, IL VALORE ALL'INTERNO DELL'ARRAY DI CUI SOPRA 
	// PER DI PIÙ, UN SIMILE RAGIONAMENTO VERRÀ ADOTTATO IN TUTTE QUELLE PAGINE CHE POSSONO ESSERE RAGGIUNTE SOLTANTO DOPO AVER SCELTO UNA DETERMINATA VOCE DALLA CORRISPONDENTE PAGINA DI RIEPILOGO E CHE PRESENTANO UN MODULO CHE PREVEDE L'UTILIZZO DEL METODO POST
	if(empty($_GET)) {
		$parametri="";
	}
	else {
		if(isset($_GET["id_Discussione"])) {
			if(!isset($_GET["id_Intervento"]))
				$parametri="?id_Offerta=".$_GET["id_Offerta"]."&amp;id_Discussione=".$_GET["id_Discussione"];
			else
				$parametri="?id_Offerta=".$_GET["id_Offerta"]."&amp;id_Discussione=".$_GET["id_Discussione"]."&amp;id_Intervento=".$_GET["id_Intervento"];
		}
	}
	
	// IL PULSANTE AVENTE LA DICITURA "INDIETRO" PERMETTERÀ ALL'UTENTE DI TORNARE ALLA SCHERMATA PRECEDENTE A QUELLA CORRENTE. IN PARTICOLARE, SI POTRANNO APRIRE DUE POSSIBILI SCENARI:
	// 1) L'AMMINISTRATORE DEL SITO HA EFFETTUATO L'ACCESSO ALLA PAGINA TRAMITE IL PULSANTE DI INSERIMENTO DI UNA NUOVA FAQ NELLA SEZIONE LORO DEDICATA, PERTANTO DOVRÀ ESSERE REINDIRIZZATO VERSO LA PAGINA INIZIALE DEL SITO
	if(isset($_POST["back"])) {
		if(empty($_GET))
			header("Location: riepilogo_faq.php");
		else {
			// 2) L'AMMINISTRATORE DEL SITO HA EFFETTUATO L'ACCESSO ALLA PAGINA TRAMITE IL PULSANTE DI ELEVAMENTO DI UN CERTO ELEMENTO (DISCUSSIONE O INTERVENTO) COME COMPONENTE DI UNA NUOVA FAQ, PERTANTO DOVRÀ ESSERE REINDIRIZZATO VERSO LA SCHERMATA DI RIEPILOGO INERENTE ALL'OFFERTA D'INTERESSE
			// PER RIUSCIRCI, SARÀ SUFFICEINTE ASSEGNARE IL VALORE DI CUI SOPRA AD UNA VARIABILE "TEMPORANEA" E, IN SEGUITO, UTILIZZARE OPPORTUNAMENTE LA FUNZIONE header() 
			$id_Offerta=$_GET["id_Offerta"];
			
			// EVIDENTEMENTE, LA CONDIVISIONE DEL DATO IN QUESTIONE È STATA GESTITA COME SOPRA PER EVITARE LA CREAZIONE DI ULTERIORI VARIBILI DI SESSIONE, LE QUALI, DATA LA LIBERTÀ DI NAVIGAZIONE CONCESSA ALL'UTENTE, AVREBBERO DOVUTO ESSERE RIMOSSE IN OGNI ALTRO SCRIPT
			header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
		}
	}
	
	// UNA VOLTA CONFERMATE LE PROPRIE SCELTE, BISOGNA PROCEDERE CON I CONTROLLI PER VALUTARE LA CORRETTEZZA E L'INTEGRITÀ DEI VALORI INDICATI ALL'INTERNO DEI VARI CAMPI
	if(isset($_POST["confirm"])) {
		
		// IL PRIMO PASSO CONSISTE NELL'INDIVIDUARE LA CASISTICA A CUI SI STA FACENDO ATTUALMENTE RIFERIMENTO
		if(empty($_GET)) {
			
			// AL TERMINE DELLA PRECEDENTE OPERAZIONE, SARÀ NECESSARIO EFFETTUARE LA RIMOZIONE DEI POSSIBILI SPAZI (BIANCHI) COLLOCATI ALL'INIZIO E ALLA FINE DEGLI ELEMENTI COINVOLTI
			// IL PRIMO PASSO CONSISTE NELLA RIMOZIONE DEI POSSIBILI SPAZI (BIANCHI) COLLOCATI ALL'INIZIO E ALLA FINE DEGLI ELEMENTI COINVOLTI
			$_POST["titolo"]=trim($_POST["titolo"]);
			$_POST["titolo"]=rtrim($_POST["titolo"]);

			$_POST["descrizione"]=trim($_POST["descrizione"]);
			$_POST["descrizione"]=rtrim($_POST["descrizione"]);
			
			$_POST["testo"]=trim($_POST["testo"]);
			$_POST["testo"]=rtrim($_POST["testo"]);
			
			// PER QUESTIONI DI FORMATTAZIONE DEL DOCUMENTO XML, È STATO NECESSARIO DISPORRE TUTTE LE COMPONENTI DELLA DESCRIZIONE DI UNA CERTA DISCUSSIONE ALL'INTERNO DI UN'UNICA RIGA. A TALE SCOPO, ABBIAMO USUFRUITO DEL METODO explode(...) SPECIFICANDO "\n" COME PARAMETRO DELIMITATORE PER EFFETTUARE LA SEPARAZIONE DELLA STRINGA
			$descrizione=explode("\n", $_POST["descrizione"]);
			$_POST["descrizione"]="";
			
			foreach($descrizione as $riga_descrizione) {
				$_POST["descrizione"]=$_POST["descrizione"].$riga_descrizione;
			}
			
			// ***
			$testo=explode("\n", $_POST["testo"]);
			$_POST["testo"]="";
			
			foreach($testo as $riga_testo) {
				$_POST["testo"]=$_POST["testo"].$riga_testo;
			}
			
			// AL FINE DI GARANTIRE UN DISCRETO LIVELLO DI SICUREZZA, PER I CAMPI PRIVI DI CONTROLLI INERENTI AD ESPRESSIONI REGOLARI (ADEGUATE PER IL CONTESTO) VERRANNO APPLICATI DEI CONTROLLI INERENTI AI CARATTERI AL LORO INTERNO 
			$_POST["titolo"]=stripslashes($_POST["titolo"]); // UN ESEMPIO, POTREBBE ESSERE LA RIMOZIONE DEI BACKSLASH \ PER EVITARE LA MySQL Injection
			$_POST["descrizione"]=stripslashes($_POST["descrizione"]); // ***
			$_POST["testo"]=stripslashes($_POST["testo"]); // ***	

			// GIUNTI A QUESTO PUNTO, E SECONDO L'ORDINAMENTO INDOTTO DALLE PRECEDENTI OPERAZIONI, È NECESSARIO EFFETTUARE UN ULTERIORE CONTROLLO PER VALUTARE SE SI È EFFETTIVAMENTE INSERITO QUALCOSA ALL'INTERNO DEI VARI CAMPI 
			if(strlen($_POST["titolo"])==0 || strlen($_POST["descrizione"])==0 || strlen($_POST["testo"])==0) {
				// IN CASO DI ERRORE, SI DOVRÀ INFORMARE L'UTENTE COINVOLTO DELLE MANCANZE CHE HA AVUTO NEI CONFRONTI DEL MODULO PER L'INSERIMENTO DELLA PROPOSTA DI VENDITA. UN SIMILE MECCANISMO VERRÀ IMPLEMENTATO PER TUTTE LE CASISITCHE IN CUI SARÀ IMPOSSIBILE PROCEDERE  
				$campi_vuoti=true;
			}
			else {
				// PRIMA DI PROCEDERE CON L'INSERIMENTO DELLA NUOVA DISCUSSIONE, BISOGNA EFFETTUARE DEI CONTROLLI PER VALUTARE SE UN DETERMINATO ELEMENTO ECCEDE LA DIMENSIONE O NON RISPETTA IL FORMATO INDICATO
				if(strlen($_POST["titolo"])>30) {
					// ***
					$superamento_titolo=true;
				}
				
				// ***
				if(strlen($_POST["descrizione"])>1989) {
					// ***
					$superamento_descrizione=true;
				}
				
				// ***
				if(strlen($_POST["testo"])>1989) {
					// ***
					$superamento_testo=true;
				}
				
				// PER CONCLUDERE, È POSSIBILE PROCEDERE CON L'EFFETIVO INSERIMENTO DELLA NUOVA OCCORRENZA ALL'INTERNO DEL FILE XML
				if(!(isset($superamento_titolo) && $superamento_titolo) && !(isset($superamento_descrizione) && $superamento_descrizione) && !(isset($superamento_testo) && $superamento_testo)) {
					
					// PRIMA DI PROCEDERE CON L'EFFETTIVO INSERIMENTO, BISOGNERÀ FARE IN MODO CHE IL TITOLO O LA DESCRIZIONE INSERITI NON SIANO GIÀ STATI ATTRIBUITI AD UN'ALTRA FAQ
					$duplicazione_discussione=false;
					
					for($i=0; $i<$faq->length && !$duplicazione_discussione; $i++) {
						$singola_faq=$faq->item($i);
						
						if($singola_faq->getElementsByTagName("titolo")->length!=0) {
							if($singola_faq->getElementsByTagName("titolo")->item(0)->textContent==$_POST["titolo"] || $singola_faq->getElementsByTagName("descrizione")->item(0)->textContent==$_POST["descrizione"])
								$duplicazione_discussione=true;
						}
						else {
							for($j=0; $j<$discussioni->length && !$duplicazione_discussione; $j++) {
								if($singola_faq->getElementsByTagName("discussioneEsistente")->item(0)->getAttribute("idDiscussione")==$discussioni->item($j)->getAttribute("id") && $discussioni->item($j)->firstChild->textContent==$_POST["titolo"])
									$duplicazione_discussione=true;
							}
						}
					}
					
					if(!$duplicazione_discussione) {
						
						// LA RAPPRESENTAZIONE DI UNA FAQ È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUEST'ULTIMA. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI INDICATI DALL'UTENTE
						$nuova_faq=$docFaq->createElement("singolaFaq");
					
						// CONTESTUALMENTE ALLA DEFINIZIONE DI UN INDICE PER L'ELEMENTO SUDDETTO, È PREVISTO L'ADEGUAMENTO DELL'ATTRIBUTO RELATIVO ALLA RADICE DEL DOCUMENTO E INERENTE AL NUMERO DI DISCUSSIONI INSERITE FINORA  
						$rootFaq->setAttribute("ultimoId", $rootFaq->getAttribute("ultimoId")+1);
						$nuova_faq->setAttribute("id", $rootFaq->getAttribute("ultimoId"));
						
						$nuovo_resoconto_discussione=$docFaq->createElement("resocontoDiscussione");
						
						$nuova_discussione_da_zero=$docFaq->createElement("discussioneDaZero");
						
						$nuova_discussione_da_zero->appendChild($docFaq->createElement("titolo", $_POST["titolo"]));
						
						$nuova_discussione_da_zero->appendChild($docFaq->createElement("descrizione", $_POST["descrizione"]));
						
						$nuovo_resoconto_discussione->appendChild($nuova_discussione_da_zero);
						
						$nuova_faq->appendChild($nuovo_resoconto_discussione);
						
						$nuovo_intervento_discussione=$docFaq->createElement("interventoDiscussione");
						
						$nuovo_intervento_da_zero=$docFaq->createElement("interventoDaZero");
						
						$nuovo_intervento_da_zero->appendChild($docFaq->createElement("testo", $_POST["testo"]));
						
						$nuovo_intervento_discussione->appendChild($nuovo_intervento_da_zero);
						
						$nuova_faq->appendChild($nuovo_intervento_discussione);
						
						$rootFaq->appendChild($nuova_faq);
						
						// GIUNTI A QUESTO PUNTO, SI PROCEDE CON IL SALVATAGGIO DEL CONTENUTO DEL DOCUMENTO APPENA MODIFICATO
						if($docFaq->schemaValidate("../../XML/Schema/FAQ.xsd")){
							
							// PER UNA STAMPA OTTIMALE, SONO STATI APPLICATI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
							$docFaq->preserveWhiteSpace = false;
							$docFaq->formatOutput = true;
							$docFaq->save("../../XML/FAQ.xml");
							
							// INOLTRE, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
							$_SESSION["modifica_Effettuata"]=true;
							
							header("Location: riepilogo_faq.php");
						}
						else {
							
							// ***
							setcookie("errore_Validazione", true);
							
							header("Location: index.php");
						}
					}
				}
			}
			
		}
		else {
			if(isset($_GET["id_Discussione"])) {
				if(!isset($_GET["id_Intervento"])) {
						
					// INOLTRE, SI DOVRÀ IDENTIFICARE LA SCELTA OPERATA DALL'AMMINISTRATORE IN MERITO ALL'INTERVENTO DA ASSEGNARE COME RISPOSTA ALLA DISCUSSIONE CHE HA INTENZIONE DI ELEVARE A FAQ
					if(isset($_POST["intervento"])) {
						if($_POST["intervento"]=="nuovo") {
							// ***
							$_POST["testo"]=trim($_POST["testo"]);
							$_POST["testo"]=rtrim($_POST["testo"]);
						
							// ***
							$testo=explode("\n", $_POST["testo"]);
							$_POST["testo"]="";
							
							foreach($testo as $riga_testo) {
								$_POST["testo"]=$_POST["testo"].$riga_testo;
							}
							
							// ***
							$_POST["testo"]=stripslashes($_POST["testo"]); // ***
							
							// ***
							if(strlen($_POST["testo"])==0) {
								// ***
								$campi_vuoti=true;
							}
							else {
								// ***
								if(strlen($_POST["testo"])>1989) {
									// ***
									$superamento_testo=true;
								}
								
								// ***
								if(!(isset($superamento_testo) && $superamento_testo)) {
									
									// PRIMA DI PROCEDERE CON L'EFFETTIVO INSERIMENTO, BISOGNERÀ VALUTARE SE L'INTERA DISCUSSIONE DI UNA CERTA FAQ COINCIDE CON QUELLA CHE L'AMMINISTRATORE INTENDE ELEVARE
									$duplicazione_discussione=false;
									
									for($i=0; $i<$faq->length && !$duplicazione_discussione; $i++) {
										$singola_faq=$faq->item($i);
										
										if($singola_faq->getElementsByTagName("discussioneDaZero")->length!=0) {
											if($singola_faq->getElementsByTagName("titolo")->item(0)->textContent==$discussione->firstChild->textContent || $singola_faq->getElementsByTagName("descrizione")->item(0)->textContent==$discussione->getElementsByTagName("descrizione")->item(0)->textContent) {
												$duplicazione_discussione=true;
											}
										}
										else {
											if($singola_faq->getElementsByTagName("discussioneEsistente")->item(0)->getAttribute("idDiscussione")==$discussione->getAttribute("id")) {
												$duplicazione_discussione=true;
											}
										}
									}
									
									if(!$duplicazione_discussione) {
										// ***
										$nuova_faq=$docFaq->createElement("singolaFaq");
									
										// ***
										$rootFaq->setAttribute("ultimoId", $rootFaq->getAttribute("ultimoId")+1);
										$nuova_faq->setAttribute("id", $rootFaq->getAttribute("ultimoId"));
										
										$nuovo_resoconto_discussione=$docFaq->createElement("resocontoDiscussione");
										
										$nuova_discussione_esistente=$docFaq->createElement("discussioneEsistente");
										
										$nuova_discussione_esistente->setAttribute("idDiscussione", $discussione->getAttribute("id"));
										
										$nuovo_resoconto_discussione->appendChild($nuova_discussione_esistente);
										
										$nuova_faq->appendChild($nuovo_resoconto_discussione);
										
										$nuovo_intervento_discussione=$docFaq->createElement("interventoDiscussione");
										
										$nuovo_intervento_da_zero=$docFaq->createElement("interventoDaZero");
										
										$nuovo_intervento_da_zero->appendChild($docFaq->createElement("testo", $_POST["testo"]));
										
										$nuovo_intervento_discussione->appendChild($nuovo_intervento_da_zero);
										
										$nuova_faq->appendChild($nuovo_intervento_discussione);
										
										$rootFaq->appendChild($nuova_faq);
										
										// ***
										if($docFaq->schemaValidate("../../XML/Schema/FAQ.xsd")){
											
											// ***
											$docFaq->preserveWhiteSpace = false;
											$docFaq->formatOutput = true;
											$docFaq->save("../../XML/FAQ.xml");
											
											// ***
											$_SESSION["modifica_Effettuata"]=true;
											
											header("Location: riepilogo_faq.php");
										}
										else {
											
											// ***
											setcookie("errore_Validazione", true);
											
											header("Location: index.php");
										}
									}
								}
							}
						}
						else {
							// SE L'AMMINISTRATORE HA SELEZIONATO UN CONTRIBUTO TRA QUELLI CHE SI RIFERISCONO ALLA DISCUSSIONE DI PARTENZA, BISOGNERÀ FARE IN MODO CHE IL LORO CONTENUTO RISULTI CORRETTO
							// N.B.: UN CERTO INTERVENTO NON NECESSITA DI ALCUN TIPO DI CONTROLLO IN MERITO AL SUO POSSIBILE RIUTILIZZO. INFATTI, POTRÀ ESSERE CONSIDERATO COME PERTINENTE PER UNA SVARIATE SITUAZIONI 
							$intervento_presente=false;
							
							for($i=0; $i<$discussione->getElementsByTagName("intervento")->length && !$intervento_presente; $i++) {
								$intervento=$discussione->getElementsByTagName("intervento")->item($i);
								
								if($intervento->getAttribute("id")==$_POST["intervento"] && $intervento->getAttribute("moderato")=="No") {
									$intervento_presente=true;
								}
							}
							
							if($intervento_presente) {
								// ***
								$duplicazione_discussione=false;
									
								for($i=0; $i<$faq->length && !$duplicazione_discussione; $i++) {
									$singola_faq=$faq->item($i);
									
									if($singola_faq->getElementsByTagName("discussioneDaZero")->length!=0) {
										if($singola_faq->getElementsByTagName("titolo")->item(0)->textContent==$discussione->firstChild->textContent || $singola_faq->getElementsByTagName("descrizione")->item(0)->textContent==$discussione->getElementsByTagName("descrizione")->item(0)->textContent) {
											$duplicazione_discussione=true;
										}
									}
									else {
										if($singola_faq->getElementsByTagName("discussioneEsistente")->item(0)->getAttribute("idDiscussione")==$discussione->getAttribute("id")) {
											$duplicazione_discussione=true;
										}
									}
								}
								
								if(!$duplicazione_discussione) {
									// ***
									$nuova_faq=$docFaq->createElement("singolaFaq");
								
									// ***
									$rootFaq->setAttribute("ultimoId", $rootFaq->getAttribute("ultimoId")+1);
									$nuova_faq->setAttribute("id", $rootFaq->getAttribute("ultimoId"));
									
									$nuovo_resoconto_discussione=$docFaq->createElement("resocontoDiscussione");
									
									$nuova_discussione_esistente=$docFaq->createElement("discussioneEsistente");
									
									$nuova_discussione_esistente->setAttribute("idDiscussione", $discussione->getAttribute("id"));
									
									$nuovo_resoconto_discussione->appendChild($nuova_discussione_esistente);
									
									$nuova_faq->appendChild($nuovo_resoconto_discussione);
									
									$nuovo_intervento_discussione=$docFaq->createElement("interventoDiscussione");
									
									$nuovo_intervento_esistente=$docFaq->createElement("interventoEsistente");
									
									$nuovo_intervento_esistente->setAttribute("idDiscussione", $discussione->getAttribute("id"));
									
									$nuovo_intervento_esistente->setAttribute("idIntervento", $_POST["intervento"]);
									
									$nuovo_intervento_discussione->appendChild($nuovo_intervento_esistente);
									
									$nuova_faq->appendChild($nuovo_intervento_discussione);
									
									$rootFaq->appendChild($nuova_faq);
									
									// ***
									if($docFaq->schemaValidate("../../XML/Schema/FAQ.xsd")){
										
										// ***
										$docFaq->preserveWhiteSpace = false;
										$docFaq->formatOutput = true;
										$docFaq->save("../../XML/FAQ.xml");
										
										// ***
										$_SESSION["modifica_Effettuata"]=true;
										
										header("Location: riepilogo_faq.php");
									}
									else {
										
										// ***
										setcookie("errore_Validazione", true);
										
										header("Location: index.php");
									}
								}
							}
						}
					}
					else {
						// ***
						$nessun_intervento=true;
					}
				}
				else {
					// L'ULTIMA CASISTICA POSSIBILE PREVEDE L'INTENTO DA PARTE DELL'AMMINISTRATORE NEL VOLER ELEVARE UN CERTO INTERVENTO E, A PARTIRE DA QUEST'ULTIMO, SELEZIONARE O RIFORMULARE LA DISCUSSIONE DI RIFERIMENTO. PROPRIO PER QUESTO, I RAGIONAMENTI DA APPLICARE SARANNO DEL TUTTO SIMILI A QUELLI PRECEDENTI
					if(isset($_POST["discussione"])) {
						if($_POST["discussione"]=="nuova") {
							// ***
							$_POST["titolo"]=trim($_POST["titolo"]);
							$_POST["titolo"]=rtrim($_POST["titolo"]);

							$_POST["descrizione"]=trim($_POST["descrizione"]);
							$_POST["descrizione"]=rtrim($_POST["descrizione"]);
							
							// ***
							$descrizione=explode("\n", $_POST["descrizione"]);
							$_POST["descrizione"]="";
							
							foreach($descrizione as $riga_descrizione) {
								$_POST["descrizione"]=$_POST["descrizione"].$riga_descrizione;
							}
							
							// ***
							$_POST["titolo"]=stripslashes($_POST["titolo"]); // ***
							$_POST["descrizione"]=stripslashes($_POST["descrizione"]); // ***
			
							// ***
							if(strlen($_POST["titolo"])==0 || strlen($_POST["descrizione"])==0 ) {
								// ***
								$campi_vuoti=true;
							}
							else {
								// ***
								if(strlen($_POST["titolo"])>30) {
									// ***
									$superamento_titolo=true;
								}
								
								// ***
								if(strlen($_POST["descrizione"])>1989) {
									// ***
									$superamento_descrizione=true;
								}
								
								// ***
								if(!(isset($superamento_titolo) && $superamento_titolo) && !(isset($superamento_descrizione) && $superamento_descrizione)) {
									// ***
									$duplicazione_discussione=false;
					
									for($i=0; $i<$faq->length && !$duplicazione_discussione; $i++) {
										$singola_faq=$faq->item($i);
										
										if($singola_faq->getElementsByTagName("titolo")->length!=0) {
											if($singola_faq->getElementsByTagName("titolo")->item(0)->textContent==$_POST["titolo"] || $singola_faq->getElementsByTagName("descrizione")->item(0)->textContent==$_POST["descrizione"])
												$duplicazione_discussione=true;
										}
										else {
											for($j=0; $j<$discussioni->length && !$duplicazione_discussione; $j++) {
												if($singola_faq->getElementsByTagName("discussioneEsistente")->item(0)->getAttribute("idDiscussione")==$discussioni->item($j)->getAttribute("id") && $discussioni->item($j)->firstChild->textContent==$_POST["titolo"])
													$duplicazione_discussione=true;
											}
										}
									}
									
									if(!$duplicazione_discussione) {
										
										// ***
										$nuova_faq=$docFaq->createElement("singolaFaq");
									
										// ***
										$rootFaq->setAttribute("ultimoId", $rootFaq->getAttribute("ultimoId")+1);
										$nuova_faq->setAttribute("id", $rootFaq->getAttribute("ultimoId"));
										
										$nuovo_resoconto_discussione=$docFaq->createElement("resocontoDiscussione");
										
										$nuova_discussione_da_zero=$docFaq->createElement("discussioneDaZero");
										
										$nuova_discussione_da_zero->appendChild($docFaq->createElement("titolo", $_POST["titolo"]));
										
										$nuova_discussione_da_zero->appendChild($docFaq->createElement("descrizione", $_POST["descrizione"]));
										
										$nuovo_resoconto_discussione->appendChild($nuova_discussione_da_zero);
										
										$nuova_faq->appendChild($nuovo_resoconto_discussione);
										
										$nuovo_intervento_discussione=$docFaq->createElement("interventoDiscussione");
										
										$nuovo_intervento_esistente=$docFaq->createElement("interventoEsistente");
										
										$nuovo_intervento_esistente->setAttribute("idDiscussione", $discussione->getAttribute("id"));
									
										$nuovo_intervento_esistente->setAttribute("idIntervento", $intervento->getAttribute("id"));
										
										$nuovo_intervento_discussione->appendChild($nuovo_intervento_esistente);
										
										$nuova_faq->appendChild($nuovo_intervento_discussione);
										
										$rootFaq->appendChild($nuova_faq);
										
										// ***
										if($docFaq->schemaValidate("../../XML/Schema/FAQ.xsd")){
											
											// ***
											$docFaq->preserveWhiteSpace = false;
											$docFaq->formatOutput = true;
											$docFaq->save("../../XML/FAQ.xml");
											
											// ***
											$_SESSION["modifica_Effettuata"]=true;
											
											header("Location: riepilogo_faq.php");
										}
										else {
											
											// ***
											setcookie("errore_Validazione", true);
											
											header("Location: index.php");
										}
									}
								}
							}
						}
						else {
							// PRIMA DI PROCEDERE CON L'EFFETTIVO INSERIMENTO, BISOGNERÀ VERIFICARE SE LA DISCUSSIONE SELEZIONATA È EFFETTIVAMENTE QUELLA A CUI SI RIFERISCE L'INTERVENTO CHE SI VUOLE ELEVARE A FAQ
							
							// ***
							$duplicazione_discussione=false;
								
							for($i=0; $i<$faq->length && !$duplicazione_discussione; $i++) {
								$singola_faq=$faq->item($i);
								
								if($singola_faq->getElementsByTagName("discussioneDaZero")->length!=0) {
									if($singola_faq->getElementsByTagName("titolo")->item(0)->textContent==$discussione->firstChild->textContent) {
										$duplicazione_discussione=true;
									}
								}
								else {
									if($singola_faq->getElementsByTagName("discussioneEsistente")->item(0)->getAttribute("idDiscussione")==$discussione->getAttribute("id")) {
										$duplicazione_discussione=true;
									}
								}
							}
							
							if(!$duplicazione_discussione) {
								// ***
								$nuova_faq=$docFaq->createElement("singolaFaq");
							
								// ***
								$rootFaq->setAttribute("ultimoId", $rootFaq->getAttribute("ultimoId")+1);
								$nuova_faq->setAttribute("id", $rootFaq->getAttribute("ultimoId"));
								
								$nuovo_resoconto_discussione=$docFaq->createElement("resocontoDiscussione");
								
								$nuova_discussione_esistente=$docFaq->createElement("discussioneEsistente");
								
								$nuova_discussione_esistente->setAttribute("idDiscussione", $discussione->getAttribute("id"));
								
								$nuovo_resoconto_discussione->appendChild($nuova_discussione_esistente);
								
								$nuova_faq->appendChild($nuovo_resoconto_discussione);
								
								$nuovo_intervento_discussione=$docFaq->createElement("interventoDiscussione");
								
								$nuovo_intervento_esistente=$docFaq->createElement("interventoEsistente");
								
								$nuovo_intervento_esistente->setAttribute("idDiscussione", $discussione->getAttribute("id"));
								
								$nuovo_intervento_esistente->setAttribute("idIntervento", $intervento->getAttribute("id"));
								
								$nuovo_intervento_discussione->appendChild($nuovo_intervento_esistente);
								
								$nuova_faq->appendChild($nuovo_intervento_discussione);
								
								$rootFaq->appendChild($nuova_faq);
								
								// ***
								if($docFaq->schemaValidate("../../XML/Schema/FAQ.xsd")){
									
									// ***
									$docFaq->preserveWhiteSpace = false;
									$docFaq->formatOutput = true;
									$docFaq->save("../../XML/FAQ.xml");
									
									// ***
									$_SESSION["modifica_Effettuata"]=true;
									
									header("Location: riepilogo_faq.php");
								}
								else {
									
									// ***
									setcookie("errore_Validazione", true);
									
									header("Location: index.php");
								}
							}
						}
					}
					else {
						// ***
						$nessuna_discussione=true;
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
		<script type="text/javascript" src="../JavaScript/gestioneSelezioneContributo.js"></script>
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
				if(isset($superamento_titolo) && $superamento_titolo) {
					// *** 
					$superamento_titolo=false;
					
					echo "<div class=\"error_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
					echo "\t\t\t\t\t<p>LA DIMENSIONE DEL TITOLO ECCEDE IL NUMERO MASSIMO DI CARATTERI CONSENTITI...</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
				}
				else {
					// ***
					if(isset($discussione_presente) && !$discussione_presente) {
						// ***
						$discussione_presente=true;
						
						echo "<div class=\"error_message\">\n";
						echo "\t\t\t<div class=\"container_message\">\n";
						echo "\t\t\t\t<div class=\"container_img\">\n";
						echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t\t<div class=\"message\">\n";
						echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
						echo "\t\t\t\t\t<p>LA DISCUSSIONE CHE SI INTENDE ELEVARE NON ESISTE...</p>\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t</div>\n";
						echo "\t\t</div>\n";
					}
					else {
						// ***
						if(isset($duplicazione_discussione) && $duplicazione_discussione) {
							// ***
							$duplicazione_discussione=false;
							
							echo "<div class=\"error_message\">\n";
							echo "\t\t\t<div class=\"container_message\">\n";
							echo "\t\t\t\t<div class=\"container_img\">\n";
							echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t\t<div class=\"message\">\n";
							echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
							echo "\t\t\t\t\t<p>LA DISCUSSIONE FORNITA &Egrave; GI&Agrave; STATA ATTRIBUITA AD UN'ALTRA FAQ...</p>\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t</div>\n";
							echo "\t\t</div>\n";
						}
						else {
							// ***
							if(isset($superamento_titolo) && $superamento_titolo) {
								// *** 
								$superamento_titolo=false;
								
								echo "<div class=\"error_message\">\n";
								echo "\t\t\t<div class=\"container_message\">\n";
								echo "\t\t\t\t<div class=\"container_img\">\n";
								echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t\t<div class=\"message\">\n";
								echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
								echo "\t\t\t\t\t<p>LA DIMENSIONE DEL TITOLO ECCEDE IL NUMERO MASSIMO DI CARATTERI CONSENTITI...</p>\n";
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
									echo "\t\t\t\t\t<p>LA DIMENSIONE DELLA DESCRIZIONE ECCEDE IL NUMERO MASSIMO DI CARATTERI CONSENTITI...</p>\n";
									echo "\t\t\t\t</div>\n";	  
									echo "\t\t\t</div>\n";
									echo "\t\t</div>\n";
								}
								else {
									if(isset($nessun_intervento) && $nessun_intervento) {
										// *** 
										$nessun_intervento=false;
										
										echo "<div class=\"error_message\">\n";
										echo "\t\t\t<div class=\"container_message\">\n";
										echo "\t\t\t\t<div class=\"container_img\">\n";
										echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t</div>\n";	  
										echo "\t\t\t\t<div class=\"message\">\n";
										echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
										echo "\t\t\t\t\t<p>SI PREGA DI SPECIFICARE L'INTERVENTO DA ASSEGNARE ALLA NUOVA FAQ...</p>\n";
										echo "\t\t\t\t</div>\n";	  
										echo "\t\t\t</div>\n";
										echo "\t\t</div>\n";
									}
									else {
										// ***
										if(isset($intervento_presente) && !$intervento_presente) {
											// *** 
											$intervento_presente=true;
											
											echo "<div class=\"error_message\">\n";
											echo "\t\t\t<div class=\"container_message\">\n";
											echo "\t\t\t\t<div class=\"container_img\">\n";
											echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
											echo "\t\t\t\t</div>\n";	  
											echo "\t\t\t\t<div class=\"message\">\n";
											echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
											echo "\t\t\t\t\t<p>L'INTERVENTO DA ELEVARE NON ESISTE O RISULTA MODERATO...</p>\n";
											echo "\t\t\t\t</div>\n";	  
											echo "\t\t\t</div>\n";
											echo "\t\t</div>\n";
										}
										else {
											// ***
											if(isset($superamento_testo) && $superamento_testo) {
												// *** 
												$superamento_testo=false;
												
												echo "<div class=\"error_message\">\n";
												echo "\t\t\t<div class=\"container_message\">\n";
												echo "\t\t\t\t<div class=\"container_img\">\n";
												echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
												echo "\t\t\t\t</div>\n";	  
												echo "\t\t\t\t<div class=\"message\">\n";
												echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
												echo "\t\t\t\t\t<p>LA DIMENSIONE DEL TESTO ECCEDE IL NUMERO MASSIMO DI CARATTERI CONSENTITI...</p>\n";
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
									<img src="../../Immagini/comment-medical-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Pubblica una nuova FAQ!</h2>
							</div>
						</div>
						<form class="corpo_form" action="<?php echo $_SERVER['PHP_SELF']."".$parametri; ?>" method="post">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											<?php
												// POICHÈ SI VUOLE RENDERE LA PAGINA SPENDIBILE PER OGNUNO DEGLI ELEMENTI CHE SI PUÒ SELEZIONARE, È STATO RITENUTO OPPORTUNO EFFETTUARE UNA SERIE DI CONTROLLI PER INDIVIDUARE A QUALE DELLE TRE ENTITÀ (DOMANDE, RISPOSTE O NESSUNA DELLE PRECEDENTI) SI STA FACENDO RIFERIMENTO
												if(empty($_GET)) 
													echo "Discussione (Obbligatorio)\n";
												else {
													if(isset($_GET["id_Discussione"])) {
														if(!isset($_GET["id_Intervento"]))
															echo "Discussione (Informativo)\n";
														else
															echo "Discussione (Obbligatorio)\n";
													}
												}
											?>
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<?php
											// SE L'AMMINISTRATORE HA INTENZIONE DI ILLUSTRARE UNA NUOVA FAQ SENZA ALCUN TIPO DI RIFERIMENTO ESTERNO, BISOGNERÀ MOSTRARE A SCHERMO SOLTANTO LE DUE SEZIONI (PROBLEMATICA E SOLUZIONE) DA COMPILARE PER POTERLA PUBBLICARE
											if(empty($_GET)) {
												echo "<div class=\"campo\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\tTitolo (max. 30 caratteri)\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" name=\"titolo\" value=\"";
												
												if(isset($_POST["titolo"]))
													echo $_POST["titolo"];
												else
													echo "";
												
												echo "\" />\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
												
												echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo_descrizione\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\tDescrizione (max. 1989 caratteri)\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t<textarea rows=\"0\" cols=\"0\" name=\"descrizione\">";
												
												if(isset($_POST["descrizione"]))
													echo $_POST["descrizione"];
												else
													echo "";
												
												echo "</textarea>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p class=\"nota\"><strong>N.B.</strong> I campi di cui sopra permettono di esprimere i dubbi pi&ugrave; comuni limitando soltanto la lunghezza del testo che &egrave; possibile produrre. Proprio per questo, si prega di essere quanto pi&ugrave; chiari possibile.</p>\n";
												echo "\t\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t</div>\n";
												
												echo "\t\t\t\t\t\t\t\t<div class=\"intestazione_sezione\">\n";
												echo "\t\t\t\t\t\t\t\t\t<div class=\"container_intestazione_sezione\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t<span>Intervento (Obbligatorio)</span>\n";
												echo "\t\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t<div class=\"elenco_campi\">\n";
												echo "\t\t\t\t\t\t\t\t\t<div class=\"container_elenco_campi\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo_descrizione\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\tTesto (max. 1989 caratteri)\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t<textarea rows=\"0\" cols=\"0\" name=\"testo\">";
												
												if(isset($_POST["testo"]))
													echo $_POST["testo"];
												else
													echo "";
												
												echo "</textarea>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p class=\"nota\"><strong>N.B.</strong> La precedente sezione dovr&agrave; contenere la risposta che meglio si presta a fare luce sulle tematiche trattate.</p>\n";
												echo "\t\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t</div>\n";
												
											}
											else {
												// AL CONTRARIO, L'AMMINISTRATORE SARÀ TENUTO A CONSIDERARE IL CONTRIBUTO SELEZIONATO SENZA POTER APPORTARE ALCUNA MODIFICA. INFATTI, GLI UNICI DETTAGLI CHE POTRANNO VARIARE SARANNO QUELLI INERENTI ALLA PARTE RESTANTE DELLA DOMANDA 
												if(isset($_GET["id_Discussione"])) {
													if(!isset($_GET["id_Intervento"])) {
														echo "<div class=\"campo\">\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\tTitolo\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" disabled=\"disabled\" value=\"".$discussione->getElementsByTagName("titolo")->item(0)->textContent."\" />\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
														echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo_descrizione\">\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\tDescrizione\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$discussione->getElementsByTagName("descrizione")->item(0)->textContent."</textarea>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
														echo "\t\t\t\t\t\t\t\t\t\t<p class=\"nota\"><strong>N.B.</strong> La precedente sezione si limita a riportare le informazioni principali inerenti alla discussione di interesse.</p>\n";
														echo "\t\t\t\t\t\t\t\t\t</div>\n";
														echo "\t\t\t\t\t\t\t\t</div>\n";
														
														echo "\t\t\t\t\t\t\t\t<div class=\"intestazione_sezione\">\n";
														echo "\t\t\t\t\t\t\t\t\t<div class=\"container_intestazione_sezione\">\n";
														echo "\t\t\t\t\t\t\t\t\t\t<span>Intervento (Obbligatorio)</span>\n";
														echo "\t\t\t\t\t\t\t\t\t</div>\n";
														echo "\t\t\t\t\t\t\t\t</div>\n";
														
														echo "\t\t\t\t\t\t\t\t<div class=\"elenco_campi\">\n";
														echo "\t\t\t\t\t\t\t\t\t<div class=\"container_elenco_campi\">\n";
														
														// GIUNTI A QUESTO PUNTO, E NEL CASO IN CUI LA DISCUSSIONE NE PRESENTI ALMENO 1, SARÀ POSSIBILE ELENCARE TUTTI GLI INTERVENTI NON ANCORA MODERATI CHE SI RIFERISCONO A QUEST'ULTIMA E TRA CUI L'AMMINISTRATORE POTRÀ SCEGLIERE QUELLO DI MAGGIORE PERTINENZA SECONDO IL PROPRIO PARERE 
														$num_interventi=0;
														
														for($i=0; $i<$discussione->getElementsByTagName("intervento")->length; $i++) {
															
															$intervento=$discussione->getElementsByTagName("intervento")->item($i);
															
															if($intervento->getAttribute("moderato")=="No")
																$num_interventi++;
														
														}
														
														if($num_interventi!=0) {
															
															// PER UNA STAMPA OTTIMALE DEGLI INTERVENTI CHE SI RIFERISCONO ALLA DISCUSSIONE SELEZIONATA, ABBIAMO DECISO DI INIZIALIZZARE UN CONTATORE CHE, OLTRE AD ESSERE INCREMENTATO PER OGNI RISPOSTA NON ANCORA MODERATA, NE INDICHERÀ L'OCCORRENZA
															$num_intervento=1;
															
															for($i=0; $i<$discussione->getElementsByTagName("intervento")->length; $i++) {
																
																$intervento=$discussione->getElementsByTagName("intervento")->item($i);
																
																if($intervento->getAttribute("moderato")=="No") {
																	
																	echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo_descrizione\">\n";
																	echo "\t\t\t\t\t\t\t\t\t\t\t<p style=\"display: flex; align-items: center;\">\n";
																	
																	if(isset($_POST["intervento"]) && $_POST["intervento"]==$intervento->getAttribute("id"))
																		echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"radio\" checked=\"checked\" onclick=\"gestioneSelezioneContributo()\" name=\"intervento\" value=\"".$intervento->getAttribute("id")."\" />\n";
																	else
																		echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"radio\" name=\"intervento\" onclick=\"gestioneSelezioneContributo()\" value=\"".$intervento->getAttribute("id")."\" />\n";
																	
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t<span>Testo ".$num_intervento." (Originale)</span>\n";
																	echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
																	echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$intervento->getElementsByTagName("testo")->item(0)->textContent."</textarea>\n";
																	echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
																	echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
																	
																	$num_intervento++;
																	
																}
															}
															
															echo "\t\t\t\t\t\t\t\t\t\t<p class=\"nota\"><strong>N.B.</strong> I campi di cui sopra permettono di selezionare l'intervento che, tra quelli pubblicati, chiarisce meglio i dubbi in merito alle tematiche trattate.</p>\n";
															
															// PIUTTOSTO CHE SELEZIONARE UNA DELLE RISPOSTE ELENCATE, L'AMMINISTRATORE SARÀ ANCHE IN GRADO DI DECIDERE SE PUBBLICARNE DIRETTAMENTE UNA NUOVA  
															echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo_descrizione\">\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t<p style=\"display: flex; align-items: center;\">\n";
															
															if(isset($_POST["intervento"]) && $_POST["intervento"]=="nuovo")
																echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"radio\" id=\"radio_nuovo_intervento\" onclick=\"gestioneSelezioneContributo()\" checked=\"checked\" name=\"intervento\" value=\"nuovo\" />\n";
															else
																echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"radio\" id=\"radio_nuovo_intervento\" onclick=\"gestioneSelezioneContributo()\" name=\"intervento\" value=\"nuovo\" />\n";
															
															echo "\t\t\t\t\t\t\t\t\t\t\t\tTesto (max. 1989 caratteri)\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
															
															if(isset($_POST["intervento"]) && $_POST["intervento"]=="nuovo")
																echo "\t\t\t\t\t\t\t\t\t\t\t\t<textarea id=\"testo_nuovo_intervento\" rows=\"0\" cols=\"0\" name=\"testo\">";
															else
																echo "\t\t\t\t\t\t\t\t\t\t\t\t<textarea id=\"testo_nuovo_intervento\" rows=\"0\" cols=\"0\" disabled=\"disabled\" name=\"testo\">";
															
															if(isset($_POST["testo"]))
																echo $_POST["testo"];
															else
																echo "";
															
															echo "</textarea>\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
															echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
															echo "\t\t\t\t\t\t\t\t\t\t<p class=\"nota\"><strong>N.B.</strong> Piuttosto che selezionare una delle risposte elencate, sar&agrave; possibile pubblicare una nuova soluzione, sputando e compilando l'ultima voce mostrata a schermo.</p>\n";
															
														}
														else {
															// NEL CASO IN CUI LA DISCUSSIONE SELEZIONATA RISULTI PRIVA DI RISPOSTE RITENUTE VALIDE PER IL SISTEMA, SARÀ SUFFICIENTE RIPORTARE LA COMPONENTE RISERVATA AL NUOVO CONTRIBUTO DA PUBBLICARE
															echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo_descrizione\">\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t<p style=\"display: flex; align-items: center;\">\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t\tTesto (max. 1989 caratteri)\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
															
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<textarea rows=\"0\" cols=\"0\" name=\"testo\">";
															
															if(isset($_POST["testo"]))
																echo $_POST["testo"];
															else
																echo "";
															
															echo "</textarea>\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
															echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
															echo "\t\t\t\t\t\t\t\t\t\t<p class=\"nota\"><strong>N.B.</strong> L'ultimo campo permette di illustrare la propria soluzione limitando soltanto la lunghezza del testo che &egrave; possibile produrre. Proprio per questo, si prega di essere quanto pi&ugrave; chiari possibile.</p>\n";
														}
														
														echo "\t\t\t\t\t\t\t\t\t</div>\n";
														echo "\t\t\t\t\t\t\t\t</div>\n";
														
													}
													else {
														// L'ULTIMO SCENARIO È RAPPRESENTATO DALLA CASISTICA OPPOSTA ALLA PRECEDENTE. INFATTI, L'AMMINISTRATORE SARÀ IN GRADO DI ELEVARE DIRETTAMENTE UN CONTRIBUTO INERENTE AD UNA CERTA DISCUSSIONE, LA CUI ESPOSIZIONE POTRÀ ESSERE INTERAMENTE RIPROPOSTA O SUBIRE TUTTI I MIGLIORAMENTI RITENUTI NECESSARI
														echo "<div class=\"campo\">\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p style=\"display: flex; align-items: center;\">\n";
														
														if(isset($_POST["discussione"]) && $_POST["discussione"]=="originale")
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"radio\" checked=\"checked\" onclick=\"gestioneSelezioneDiscussione()\" name=\"discussione\" value=\"originale\" />\n";
														else
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"radio\" onclick=\"gestioneSelezioneDiscussione()\" name=\"discussione\" value=\"originale\" />\n";
														
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span>Titolo (Originale)</span>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" disabled=\"disabled\" value=\"".$discussione->getElementsByTagName("titolo")->item(0)->textContent."\" />\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
														echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo_descrizione\">\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\tDescrizione (Originale)\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$discussione->getElementsByTagName("descrizione")->item(0)->textContent."</textarea>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
														echo "\t\t\t\t\t\t\t\t\t\t<p class=\"nota\"><strong>N.B.</strong> La precedente sezione si limita a riportare le informazioni principali inerenti alla discussione a cui si riferisce il contributo selezionato.</p>\n";
														
														echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo\">\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p style=\"display: flex; align-items: center;\">\n";
														
														if(isset($_POST["discussione"]) && $_POST["discussione"]=="nuova")
															echo "\t\t\t\t\t\t\t\t\t\t\t<input type=\"radio\" id=\"radio_nuova_discussione\" checked=\"checked\" onclick=\"gestioneSelezioneDiscussione()\" name=\"discussione\" value=\"nuova\" />\n";
														else
															echo "\t\t\t\t\t\t\t\t\t\t\t<input type=\"radio\" id=\"radio_nuova_discussione\" onclick=\"gestioneSelezioneDiscussione()\" name=\"discussione\" value=\"nuova\" />\n";
														
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span>Titolo (max. 30 caratteri)</span>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
														
														if(isset($_POST["discussione"]) && $_POST["discussione"]=="nuova")
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" id=\"titolo_nuova_discussione\" name=\"titolo\" value=\"";
														else
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" id=\"titolo_nuova_discussione\" name=\"titolo\" disabled=\"disabled\" value=\"";
												
														if(isset($_POST["titolo"]))
															echo $_POST["titolo"];
														else
															echo "";
														
														echo "\" />\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
														echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo_descrizione\">\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\tDescrizione (max. 1989 caratteri)\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
														
														if(isset($_POST["discussione"]) && $_POST["discussione"]=="nuova")
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<textarea rows=\"0\" cols=\"0\" id=\"descrizione_nuova_discussione\" name=\"descrizione\">";
														else
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<textarea rows=\"0\" cols=\"0\" id=\"descrizione_nuova_discussione\" disabled=\"disabled\" name=\"descrizione\">";
														
														if(isset($_POST["descrizione"]))
															echo $_POST["descrizione"];
														else
															echo "";
														
														echo "</textarea>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
														echo "\t\t\t\t\t\t\t\t\t\t<p class=\"nota\"><strong>N.B.</strong> Piuttosto che selezionare la discussione di partenza, sarà possibile riformulare le varie tematiche, sputando e compilando le precedenti due voci.</p>\n";
														echo "\t\t\t\t\t\t\t\t\t</div>\n";
														echo "\t\t\t\t\t\t\t\t</div>\n";
														
														echo "\t\t\t\t\t\t\t\t<div class=\"intestazione_sezione\">\n";
														echo "\t\t\t\t\t\t\t\t\t<div class=\"container_intestazione_sezione\">\n";
														echo "\t\t\t\t\t\t\t\t\t\t<span>Intervento (Informativo)</span>\n";
														echo "\t\t\t\t\t\t\t\t\t</div>\n";
														echo "\t\t\t\t\t\t\t\t</div>\n";
														
														echo "\t\t\t\t\t\t\t\t<div class=\"elenco_campi\">\n";
														echo "\t\t\t\t\t\t\t\t\t<div class=\"container_elenco_campi\">\n";
														echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo_descrizione\">\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\tTesto\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$intervento->getElementsByTagName("testo")->item(0)->textContent."</textarea>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</p>\n";
														echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
														echo "\t\t\t\t\t\t\t\t\t\t<p class=\"nota\"><strong>N.B.</strong> L'ultimo campo permette di illustrare le informazioni inerenti all'intervento di interesse.</p>\n";
														echo "\t\t\t\t\t\t\t\t\t</div>\n";
														echo "\t\t\t\t\t\t\t\t</div>\n";
													}
												}
											}
										?>
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