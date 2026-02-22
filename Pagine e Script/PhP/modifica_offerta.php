<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA COMPILARE PER MODIFICARE IL CONTENUTO DELLA PROPOSTA DI VENDITA SELEZIONATA
	// N.B.: IN CASO DI ERRORE, LE INFORMAZIONI INSERITE, COSÌ COME LE SCELTE EFFETTUATE, VERRANNO PRESERVATE TRAMITE UNA SERIE DI CONTROLLI APPLICATI AL SOLO SCOPO DI AGEVOLARE L'OPERATO DEI GESTORI. PER DI PIÙ, ABBIAMO CERCATO DI ATTRIBUIRE UNA MAGGIORE PRIORITÀ AI DETTAGLI FORNITI PIÙ DI RECENTE E, AL CONTEMPO, DI TENERE SEMPRE TRACCIA DEGLI ORIGINALI TRAMITE DELLE APPOSITE STAMPE
	
	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");

	// PER QUESTIONI DI SICUREZZA, È STATO NECESSARIO L'INSERIMENTO DELLO SCRIPT "private_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA. QUESTO MECCANISMO NON PERMETTERÀ L'ACCESSO AGLI UTENTI CHE NON SI AUTENTICATI TRAMITE IL RELATIVO SERVIZIO
	require_once("./private_session_control.php");
	
	// LE PROPOSTE DI VENDITA, DATA LA LORO STRUTTURA, POTREBBERO ESSERE SOGGETTE A DELLE RIDUZIONI DI PREZZO, CARATTERIZZATE DA UN INTERVALLO DI VALIDITÀ BEN DEFINITO E TALVOLTA APPLICABILI SUL PROSSIMO ACQUISTO. PERTANTO, NELLE IPOTESI IN CUI NON SIA SEMPRE POSSIBILE RIMUOVERE MANUALMENTE LE VOCI INTERESSATE, ABBIAMO DECISO DI IMPLEMENTARE UN CODICE IN GRADO DI INTERVENIRE IN AUTOMATICO SU TUTTI QUELLI SCONTI NON PIÙ USUFRUIBILI DAI CLIENTI DELLA PIATTAFORMA
	require_once("./rimozione_sconti.php");
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO AI GESTORI DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="G") {
		header("Location: area_riservata.php");
	}
	
	// LA PAGINA, ESSENDO RAGGIUNGIBILE DOPO AVER SELEZIONATO UNA DETERMINATA PROPOSTA DI VENDITA DALLA RELATIVA PAGINA DI RIEPILOGO, NECESSITA DI UN ULTERIORE CONTROLLO PER APPURARE SE È STATA EFFETTIVAMENTE PRESA UNA DECISIONE IN MERITO O MENO 
	if(!isset($_GET["id_Offerta"])) 
		header("Location: riepilogo_offerte.php");
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_prodotti.php");
	
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
		header("Location: riepilogo_offerte.php");
	
	// POICHÈ NELL'ELEMENTO CHE RAPPRESENTA UNA DETERMINATA OFFERTA È MEMORIZZATO L'IDENTIFICATIVO DELL'ARTICOLO COINVOLTO, SARÀ NECESSARIO CONSULTARE IL RELATIVO FILE PER OTTENERNE IL NOMINATIVO E IL PREZZO DI LISTINO
	for($i=0; $prodotti->length; $i++) {
		$prodotto=$prodotti->item($i);
		
		// ***
		if($offerta->getAttribute("idProdotto")==$prodotto->getAttribute("id")) {
			break;
		}
	}
	
	// IL PULSANTE AVENTE LA DICITURA "INDIETRO" PERMETTERÀ ALL'UTENTE DI TORNARE ALLA SCHERMATA PRECEDENTE A QUELLA CORRENTE
	if(isset($_POST["back"])) {
		header("Location: riepilogo_offerte.php");
	}
	
	// UNA VOLTA CONFERMATE LE PROPRIE SCELTE, BISOGNA PROCEDERE CON I CONTROLLI PER VALUTARE LA CORRETTEZZA E L'INTEGRITÀ DEI VALORI INDICATI ALL'INTERNO DEI VARI CAMPI  
	if(isset($_POST["confirm"])) {
		// IL PRIMO PASSO CONSISTE NELLA RIMOZIONE DEI POSSIBILI SPAZI (BIANCHI) COLLOCATI ALL'INIZIO E ALLA FINE DEGLI ELEMENTI COINVOLTI
		// NEL DETTAGLIO, POICHÈ UN'OFFERTA PUÒ ANCHE NON PRESENTARE DELLE RIDUZIONI DI PREZZO O DEI CREDITI ASSEGNATI COME BONUS, CI SI ASSICURA RISPETTO AGLI UNICI DETTAGLI OBBLIGATORI, OVVERO IL PREZZO E IL QUANTITATIVO, IN TERMINI DI PEZZI, DI QUEST'ULTIMA   
		$_POST["prezzoContabile"]=trim($_POST["prezzoContabile"]);
		$_POST["prezzoContabile"]=rtrim($_POST["prezzoContabile"]);
		
		$_POST["quantitativo"]=trim($_POST["quantitativo"]);
		$_POST["quantitativo"]=rtrim($_POST["quantitativo"]);
		
		//  QUALORA CI SIA ALMENO UNO DEGLI ASPETTI APPENA CITATI, E DUNQUE BASANDOSI SUL CONTENUTO DELLA PROPOSTA DI VENDITA IN QUESTIONE, SI PROCEDERÀ CON L'APPLICAZIONE DELL'OPERAZIONE DI CUI SOPRA. PROPRIO PER QUESTO, SARÀ FONDAMENTALE INDIVIDUARNE, SE PRESENTI, LE COMPONENTI RESTANTI
		// IN PRESENZA DI SCONTI, I PARAMETRI DA SOTTOPORRE AD UNA SERIE DI CONTROLLI DOVRANNO ESSERE LA PERCENTUALE E L'INTERVALLO DI TEMPO ENTRO CUI SI POTRÀ GODERE DEI BENIFICI OFFERTI DALLA PROPOSTA DI VENDITA
		if(isset($_POST["sconto"])) {
			
			// LA SEZIONE INERENTE ALLE RIDUZIONI DI PREZZO PRESENTA, PER CIASCUNA TIPOLOGIA DI SCONTO, UN CAMPO IN CUI SARÀ NECESSARIO SPECIFICARE LA RELATIVA PERCENTUALE. PER QUESTO MOTIVO, BASTERÀ VALUTARE L'ELEMENTO CHE INTERESSA ALLA CATEGORIA DI APPARTENENZA DETTATA DAL GESTORE
			if($_POST["sconto"]=="a_tempo") {
				$_POST["sconto_a_tempo"]=trim($_POST["sconto_a_tempo"]);
				$_POST["sconto_a_tempo"]=rtrim($_POST["sconto_a_tempo"]);
			}
			else {
				$_POST["sconto_futuro"]=trim($_POST["sconto_futuro"]);
				$_POST["sconto_futuro"]=rtrim($_POST["sconto_futuro"]);
			}
			
			$_POST["inizio_Applicazione"]=trim($_POST["inizio_Applicazione"]);
			$_POST["inizio_Applicazione"]=rtrim($_POST["inizio_Applicazione"]);
			
			$_POST["fine_Applicazione"]=trim($_POST["fine_Applicazione"]);
			$_POST["fine_Applicazione"]=rtrim($_POST["fine_Applicazione"]);
			
		}
		
		// I RAGIONAMENTI ADOTTATI FINORA SONO STATI ESTESI ANCHE PER IL POSSIBILE INCENTIVO DERIVANTE DALL'ACQUISTO DEL BENE COINVOLTO. PER COMPRENDERE SE SI È INSERITO UNO O PIÙ DETTAGLI TRA QUELLI DISCUSSI FINORA, SI FARÀ RIFERIMENTO A DEI radio CHE, IN BASE AL CONTESTO, PRESENTERANNO DEI VALORI PER DISCRIMINARE LA DECISIONE PRESA DAL GESTORE  
		if(isset($_POST["radio_bonus"])) {
			$_POST["bonus"]=trim($_POST["bonus"]);
			$_POST["bonus"]=rtrim($_POST["bonus"]);
		}
		
		// GIUNTI A QUESTO PUNTO, E SECONDO L'ORDINAMENTO INDOTTO DALLE PRECEDENTI OPERAZIONI, È NECESSARIO EFFETTUARE UN ULTERIORE CONTROLLO PER VALUTARE SE SI È EFFETTIVAMENTE INSERITO QUALCOSA ALL'INTERNO DEI VARI CAMPI 
		if((strlen($_POST["prezzoContabile"])==0)||(strlen($_POST["quantitativo"])==0)) {
			
			// IN CASO DI ERRORE, SI DOVRÀ INFORMARE L'UTENTE COINVOLTO DELLE MANCANZE CHE HA AVUTO NEI CONFRONTI DEL MODULO PER L'INSERIMENTO DELLA PROPOSTA DI VENDITA. UN SIMILE MECCANISMO VERRÀ IMPLEMENTATO PER TUTTE LE CASISITCHE IN CUI SARÀ IMPOSSIBILE PROCEDERE  
			$campi_vuoti=true;
		}
		else
		{
			// IN PRESENZA DI SCONTI, SARÀ NECESSARIO CONTROLLARE IL CONTENUTO DELLE PARTI LORO DEDICATE. LA SEQUENZA CON CUI I SOGGETTI SARANNO COINVOLTI NELLE VARIE ANALISI SARÀ SEMPRE LA STESSA 
			if(isset($_POST["sconto"])) {
				if($_POST["sconto"]=="a_tempo" && strlen($_POST["sconto_a_tempo"])==0) {
					
					// ***
					$campi_vuoti=true;
				}
				else {
					// ***
					if($_POST["sconto"]=="futuro" && strlen($_POST["sconto_futuro"])==0) {
						
						// ***
						$campi_vuoti=true;
					}
				}
				
				// ***
				if(strlen($_POST["inizio_Applicazione"])==0 || strlen($_POST["fine_Applicazione"])==0) {
					
					// ***
					$campi_vuoti=true;
				}
			}
			
			// COME PRIMA, QUANTO DEFINITO PER LE RIDUZIONI DI PREZZO VIENE RIPETUTO PER I POSSIBILI CREDITI BONUS
			if(isset($_POST["radio_bonus"])) {
				if(strlen($_POST["bonus"])==0) {
					
					// ***
					$campi_vuoti=true;
				}
			}
			
			// CONTRARIAMENTE ALLE MODALITÀ CON CUI SI È POTUTO GESTIRE L'INSERIMENTO DELLE ENTITÀ DESCRITTE PER MEZZO DI SOLI CAMPI OBBLIGATORI, NON SARÀ PIÙ POSSIBILE LA GERARCHIA DI CONTROLLI INDOTTA DA UN POSSIBILE ANNIDAMENTO DELLE STRUTTURE DI CONTROLLO
			// PROPRIO PER QUESTO, ABBIAMO OPTATO PER UNA VERIFICA PROGRESSIVA DELLA COMPONENTI, LA QUALE, OLTRE AD ESSERE CARATTERIZZATA DA UNA SERIE DI CONTROLLI BASATI SU DELLE ESPRESSIONI REGOLARI, È STATA GESTITA MEDIANTE DELLE CONDIZIONI CHE PERMETTERANNO DI PROSEGUIRE CON I VARI MECCANISMI SOLTANTO SE GLI ESITI DI QUES'ULTIME NON HANNO RISCONTRATO ALCUN ERRORE
			if(!(isset($campi_vuoti) && $campi_vuoti)) {
				
				// IL PREZZO E IL QUANTITATIVO DOVRANNO ESSERE, RISPETTIVAMENTE, UN NUMERO CON QUATTRO CIFRE INTERE (AL PIÙ) E DUE DECIMALI E UN NUMERO INTERO CARATTERIZZATO DA AL MASSIMO DUE CIFRE  
				if (preg_match("/([[:digit:]]{1,4}\.[[:digit:]]{2,2})/",$_POST["prezzoContabile"],$matches_prezzo)) {
					if($matches_prezzo[0]!=$_POST["prezzoContabile"]) {
						
						// ***
						$prezzo_errato=true;
					}
					else {
						if(preg_match("/([[:digit:]]{1,2})/",$_POST["quantitativo"],$matches_quantitativo)) {
							if($matches_quantitativo[0]!=$_POST["quantitativo"]) {
								// ***
								$quantitativo_errato=true;
							}
							else {
								// PER QUESTIONI DI CONFORMITÀ CON IL RELATIVO FILE XML, SI PROCEDE CON LA RIMOZIONE DELLE EVENTUALI CIFRE DECIMALI PRESENTI NEL NUMERO DI PEZZI SPECIFICATI DAL GESTORE. INFATTI, TRATTANDOSI DI INTERI, LA SEQUENZA CHE TERMINA CON .00 POTREBBE CREARE DEI PROBLEMI PER LA VALIDAZIONE DEL DOCUMENTO 
								$_POST["quantitativo"]=round($_POST["quantitativo"], 0);
								
								if($_POST["prezzoContabile"]>0 && $_POST["prezzoContabile"]<=$prodotto->getElementsByTagName("prezzoListino")->item(0)->textContent) {
									
									// PER LE PRECEDENTI RAGIONI, SARÀ NECESSARIO CONTROLLARE SE IL QUANTATIVO SPECIFICATO RISULTI COMPRESO NELL'INTERVALLO DI VALORI RIPORTATO A SCHERMO 
									if($_POST["quantitativo"]>0 && $_POST["quantitativo"]<=30) {
										
										// SIMILMENTE A QUANTO APPLICATO FINORA, GLI EVENTUALI SCONTI, O MEGLIO LE LORO BASI ESPRESSE SOTTO FORMA DI PERCENTUALI, DOVRANNO RISPETTARE UN FORMATO RICONDUCIBILE, A MENO DELL'ESTREMO SUPERIORE INERENTE ALLA PARTE INTERA, A QUELLO DEL PREZZO 
										if(isset($_POST["sconto"])) {
											if($_POST["sconto"]=="a_tempo") {
												if(preg_match("/([[:digit:]]{1,3}\.[[:digit:]]{2,2})/",$_POST["sconto_a_tempo"],$matches_sconto)) {
													if($matches_sconto[0]!=$_POST["sconto_a_tempo"]) {
														
														// ***
														$percentuale_errata=true;
													}
													else {
														
														// I VALORI DI CUI SOPRA, PER ESSERE TALI, DEVONO ESSERE CONTENUTI MAGGIORI STRETTAMENTE DI ZERO E MINORI, O UGUALI, A CENTO
														if(!($_POST["sconto_a_tempo"]>0 && $_POST["sconto_a_tempo"]<=100)) {
															
															// ***
															$percentuale_errata=true;
														}
													}
												}
												else {
													
													// ***
													$percentuale_errata=true;
												}
											}
											else {
												if($_POST["sconto"]=="futuro") {
													if(preg_match("/([[:digit:]]{1,3}\.[[:digit:]]{2,2})/",$_POST["sconto_futuro"],$matches_sconto)) {
														if($matches_sconto[0]!=$_POST["sconto_futuro"]) {
															
															// ***
															$percentuale_errata=true;
														}
														else {
															
															// ***
															if(!($_POST["sconto_futuro"]>0 && $_POST["sconto_futuro"]<=100)) {
																// ***
																$percentuale_errata=true;
															}
														}
													}
													else {
														
														// ***
														$percentuale_errata=true;
													}
												}
												else {
													// ***
													$riduzione_errata=true;
												}
											}
											
											// SE I CONTROLLI SUL FORMATO DELLO SCONTO NON HA PORTATO AD ALCUNA SORTA DI ERRORE, SI PROCEDE CON LA VERIFICA DELLA VALIDITÀ RELATIVA AI DUE FATTORI TEMPORALI FORNITI
											if(!(isset($riduzione_errata) && $riduzione_errata) && !(isset($percentuale_errata) && $percentuale_errata)) {
												
												// LE SINGOLE DATE DOVRANNO RISPETTARE IL FORMATO yyyy-mm-dd
												if(preg_match("/(\d{4,4}-([[:digit:]][[:digit:]])-([[:digit:]][[:digit:]]))/", $_POST["inizio_Applicazione"], $matches_inizio) && preg_match("/(\d{4,4}-([[:digit:]][[:digit:]])-([[:digit:]][[:digit:]]))/", $_POST["fine_Applicazione"], $matches_fine)) {
													if($matches_inizio[0]!=$_POST["inizio_Applicazione"] || $matches_fine[0]!=$_POST["fine_Applicazione"]) {
														
														// ***
														$date_errate=true;
													}
													else {
														
														// ALLO SCOPO DI APPURARE SE SI TRATTA DI DATE REALMENTE ESISTENTI, SI È FATTO USO DEL METODO checkdate(...), IL QUALE PREVEDE IL PASSAGGIO DI BEN TRE PARAMETRI: ANNO, MESE E GIORNO. PER OTTENERLI, È STATO NECESSARIO UTILIZZARE IL METODO substr(...) E SCOMPORRE ADEGUATAMENTE LE SINGOLE STRINGHE 
														if(checkdate(substr($_POST["inizio_Applicazione"],5,2),substr($_POST["inizio_Applicazione"],8,2),substr($_POST["inizio_Applicazione"],0,4)) && checkdate(substr($_POST["fine_Applicazione"],5,2),substr($_POST["fine_Applicazione"],8,2),substr($_POST["fine_Applicazione"],0,4))){
															
															// SE I RIFERIMENTI TEMPORALI FINALE E INIZIALE NON SONO, RISPETTIVAMENTE, SUCCESSIVI TRA LORO E UGUALI A QUELLO ODIERNO, SARANNO RITENUTI ERRATI
															if(!(strtotime($_POST["fine_Applicazione"])>=strtotime($_POST["inizio_Applicazione"]) && strtotime($_POST["inizio_Applicazione"])==strtotime(date("Y-m-d")))) {
																
																// ***
																$date_errate=true;
															}
														}
														else {
															
															// ***
															$date_errate=true;
														}
													}
												}
												else {
													
													// ***
													$date_errate=true;
												}
											}
										}
										
										// COME DISCUSSO A PIÙ RIPRESE, L' EVENTUALE INCENTIVO VIENE CONSIDERATO QUALORA NON CI SIANO STATI DEI PROBLEMI LEGATI ALLE RIDUZIONI DI PREZZO O AI LORO INTERVALLI DI VALIDITÀ
										if(!(isset($riduzione_errata) && $riduzione_errata) && !(isset($percentuale_errata) && $percentuale_errata) && !(isset($date_errate) && $date_errate)) {
											if(isset($_POST["radio_bonus"])) {
												
												// I BONUS DOVRANNO ESSERE CARATTERIZZATI DA UNA COMPOSIZIONE IDENTICA AL PREZZO DI VENDITA DELL'OFFERTA
												if(preg_match("/([[:digit:]]{1,4}\.[[:digit:]]{2,2})/",$_POST["bonus"],$matches)) {
													if($matches[0]!=$_POST["bonus"]) {
														
														// ***
														$bonus_errato=true;
													}
													else {
														
														// INOLTRE, SE PRESENTI, AVRANNO UN VALORE MAGGIORE STRETTO DI ZERO
														if(!($_POST["bonus"]>0)) {
															
															// ***
															$bonus_errato=true;
														}
													}
												}
												else {
													
													// ***
													$bonus_errato=true;
												}
											}
										}
									}
									else {
										
										// ***
										$superamento_quantitativo=true;
									}
								}
								else {
									
									// ***
									$superamento_prezzo=true;
								}
							}
						}
						else {
							// ***
							$quantitativo_errato=true;
						}
					}
				}
				else {
					
					// ***
					$prezzo_errato=true;
				}
			}
		}
		
		// SE NON CI SONO STATE DELLE VIOLAZIONI INERENTI AL FORMATO DEI VARI ELEMENTI, SI PROCEDE CON L'EFFETTIVO INSERIMENTO DELL'OFFERTA ALL'INTERNO DELLA STRUTTURA DATI LEI DEDICATA
		if(!(isset($campi_vuoti) && $campi_vuoti) && !(isset($prezzo_errato) && $prezzo_errato) && !(isset($superamento_prezzo) && $superamento_prezzo) && !(isset($quantitativo_errato) && $quantitativo_errato) && !(isset($superamento_quantitativo) && $superamento_quantitativo) && !(isset($riduzione_errata) && $riduzione_errata) && !(isset($percentuale_errata) && $percentuale_errata) && !(isset($date_errate) && $date_errate) && !(isset($bonus_errato) && $bonus_errato)) {
			
			// LA RAPPRESENTAZIONE DI UN'OFFERTA È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUEST'ULTIMA. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI INDICATI DAL GESTORE 
			$nuova_offerta=$docOfferte->createElement("offerta");
			
			// IN PARTICOLARE, LA NUOVA PROPOSTA DI VENDITA AVRÀ GLI STESSI IDENTIFICATORI DI QUELLA SELEZIONATA
			$nuova_offerta->setAttribute("id", $offerta->getAttribute("id"));
			$nuova_offerta->setAttribute("idProdotto", $offerta->getAttribute("idProdotto"));
			
			$prezzo=$docOfferte->createElement("prezzoContabile", $_POST["prezzoContabile"]);
			$nuova_offerta->appendChild($prezzo);
			
			if(isset($_POST["sconto"])) {
				$sconto=$docOfferte->createElement("sconto");
				if($_POST["sconto"]=="a_tempo") {
					$scontoATempo=$docOfferte->createElement("scontoATempo");
					$scontoATempo->setAttribute("percentuale", $_POST["sconto_a_tempo"]);
					$scontoATempo->setAttribute("inizioApplicazione", $_POST["inizio_Applicazione"]);
					$scontoATempo->setAttribute("fineApplicazione", $_POST["fine_Applicazione"]);
					$sconto->appendChild($scontoATempo);
				}
				else {
					$scontoFuturo=$docOfferte->createElement("scontoFuturo");
					$scontoFuturo->setAttribute("percentuale", $_POST["sconto_futuro"]);
					$scontoFuturo->setAttribute("inizioApplicazione", $_POST["inizio_Applicazione"]);
					$scontoFuturo->setAttribute("fineApplicazione", $_POST["fine_Applicazione"]);
					$sconto->appendChild($scontoFuturo);
				}
				$nuova_offerta->appendChild($sconto);
			}
			
			$quantitativo=$docOfferte->createElement("quantitativo", $_POST["quantitativo"]);
			$nuova_offerta->appendChild($quantitativo);
			
			if(isset($_POST["radio_bonus"])) {
				$bonus=$docOfferte->createElement("bonus");
				$numeroCrediti=$docOfferte->createElement("numeroCrediti", $_POST["bonus"]);
				$bonus->appendChild($numeroCrediti);
				$nuova_offerta->appendChild($bonus);
			}
			
			$rootOfferte->replaceChild($nuova_offerta, $offerta);
			
			// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
			if($docOfferte->schemaValidate("../../XML/Schema/Offerte.xsd")){
				
				// AL FINE DI GARANTIRE UNA STAMPA GODIBILE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
				$docOfferte->preserveWhiteSpace = false;
				$docOfferte->formatOutput = true;
				$docOfferte->save("../../XML/Offerte.xml");
				
				// PRIMA DI ESSERE REINDIRIZZATI, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
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
		<script type="text/javascript" src="../JavaScript/gestioneSelezioneScontiBonus.js"></script>
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
			else
			{
				// ***
				if(isset($prezzo_errato) && $prezzo_errato) 
				{ 
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
					if(isset($prodotto_individuato) && !$prodotto_individuato) {
						// ***
						$prodotto_individuato=false;
						
						echo "<div class=\"error_message\">\n";
						echo "\t\t\t<div class=\"container_message\">\n";
						echo "\t\t\t\t<div class=\"container_img\">\n";
						echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t\t<div class=\"message\">\n";
						echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
						echo "\t\t\t\t\t<p>IL PRODOTTO INDICATO NON &Egrave; TRA QUELLI PRESENTI IN MAGAZZINO...</p>\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t</div>\n";
						echo "\t\t</div>\n";
					}
					else {
						// ***
						if(isset($superamento_prezzo) && $superamento_prezzo) 
						{ 
							// ***
							$superamento_prezzo=false;
							
							echo "<div class=\"error_message\">\n";
							echo "\t\t\t<div class=\"container_message\">\n";
							echo "\t\t\t\t<div class=\"container_img\">\n";
							echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t\t<div class=\"message\">\n";
							echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
							echo "\t\t\t\t\t<p>IL PREZZO SPECIFICATO RISULTA NULLO O ECCEDE QUELLO MASSIMO PREVISTO...</p>\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t</div>\n";
							echo "\t\t</div>\n";
						}
						else {
							// *** 
							if(isset($quantitativo_errato) && $quantitativo_errato) 
							{ 
								// ***
								$quantitativo_errato=false;
								
								echo "<div class=\"error_message\">\n";
								echo "\t\t\t<div class=\"container_message\">\n";
								echo "\t\t\t\t<div class=\"container_img\">\n";
								echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t\t<div class=\"message\">\n";
								echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
								echo "\t\t\t\t\t<p>IL QUANTITATIVO NON RISPETTA IL FORMATO INDICATO...</p>\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t</div>\n";
								echo "\t\t</div>\n";
							}
							else {
								// *** 
								if(isset($superamento_quantitativo) && $superamento_quantitativo) 
								{ 
									// ***
									$superamento_quantitativo=false;
									
									echo "<div class=\"error_message\">\n";
									echo "\t\t\t<div class=\"container_message\">\n";
									echo "\t\t\t\t<div class=\"container_img\">\n";
									echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
									echo "\t\t\t\t</div>\n";	  
									echo "\t\t\t\t<div class=\"message\">\n";
									echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
									echo "\t\t\t\t\t<p>IL QUANTITATIVO SPECIFICATO RISULTA NULLO O ECCEDE QUELLO MASSIMO PREVISTO...</p>\n";
									echo "\t\t\t\t</div>\n";	  
									echo "\t\t\t</div>\n";
									echo "\t\t</div>\n";
								}
								else {
									// *** 
									if(isset($superamento_quantitativo) && $superamento_quantitativo) 
									{ 
										// ***
										$superamento_quantitativo=false;
										
										echo "<div class=\"error_message\">\n";
										echo "\t\t\t<div class=\"container_message\">\n";
										echo "\t\t\t\t<div class=\"container_img\">\n";
										echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t</div>\n";	  
										echo "\t\t\t\t<div class=\"message\">\n";
										echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
										echo "\t\t\t\t\t<p>LA TIPOLOGIA DELLA RIDUZIONE CHE SI VUOLE REGISTRARE NON&Egrave; AMMESSA...</p>\n";
										echo "\t\t\t\t</div>\n";	  
										echo "\t\t\t</div>\n";
										echo "\t\t</div>\n";
									}
									else {
										// ***
										if(isset($percentuale_errata) && $percentuale_errata) {
											// ***
											$percentuale_errata=false;
											
											echo "<div class=\"error_message\">\n";
											echo "\t\t\t<div class=\"container_message\">\n";
											echo "\t\t\t\t<div class=\"container_img\">\n";
											echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
											echo "\t\t\t\t</div>\n";	  
											echo "\t\t\t\t<div class=\"message\">\n";
											echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
											echo "\t\t\t\t\t<p>LA RIDUZIONE DEL PREZZO RISULTA NULLA E/O NON RISPETTA LE SPECIFICHE INDICATE...</p>\n";
											echo "\t\t\t\t</div>\n";	  
											echo "\t\t\t</div>\n";
											echo "\t\t</div>\n";
										}
										else {
											// ***
											if(isset($date_errate) && $date_errate) {
												// ***
												$date_errate=false;
													
												echo "<div class=\"error_message\">\n";
												echo "\t\t\t<div class=\"container_message\">\n";
												echo "\t\t\t\t<div class=\"container_img\">\n";
												echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
												echo "\t\t\t\t</div>\n";	  
												echo "\t\t\t\t<div class=\"message\">\n";
												echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
												echo "\t\t\t\t\t<p>LA/E DATE INSERITE NON ESISTONO E/O NON RISPETTANO LE SPECIFICHE INDICATE...</p>\n";
												echo "\t\t\t\t</div>\n";	  
												echo "\t\t\t</div>\n";
												echo "\t\t</div>\n";
											}
										}
									}
								}
							}
						}
						// ***					
						if(!(isset($riduzione_errata) && $riduzione_errata) && !(isset($percentuale_errata) && $percentuale_errata) && !(isset($date_errate) && $date_errate)) {
							// ***
							if(isset($bonus_errato) && $bonus_errato) {
								// ***
								$bonus_errato=false;
								
								echo "<div class=\"error_message\">\n";
								echo "\t\t\t<div class=\"container_message\">\n";
								echo "\t\t\t\t<div class=\"container_img\">\n";
								echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t\t<div class=\"message\">\n";
								echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
								echo "\t\t\t\t\t<p>L'AMMONTARE DEI CREDITI (BONUS) RISULTA NULLO O NON RISPETTA IL FORMATO INDICATO...</p>\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t</div>\n";
								echo "\t\t</div>\n";
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
									<img src="../../Immagini/sack-dollar-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Modifica l'offerta selezionata!</h2>
							</div>
						</div>
						<?php // IL MODULO CHE CARATTERIZZA LA SCHERMATA IN OGGETTO SARÀ COMPOSTO DA UN BEN TRE SEZIONI, DUE DELLE QUALI, POICHÈ NON OBBLIGATORIE AI FINI DELLA CREAZIONE DI UNA PROPOSTA DI VENDITA, NON SARANNO COMPLETAMENTE VISIBILI. INFATTI, SI È DECISO DI IMPLEMENTARE UN MECCANISMO DI COMPARSA E SCOMPARSA MOLTO SIMILE A QUELLO DEI MENÙ CONTENUTI NELL'INTESTAZIONE DEL SITO  ?>
						<form class="corpo_form" action="<?php echo $_SERVER['PHP_SELF']."?id_Offerta=".$_GET["id_Offerta"]; ?>" method="post">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Profilo Gestionale <strong style="color: rgb(119, 119, 119);" title="il prodotto coinvolto, al contrario delle altre voci, NON si pu&ograve; modificare">*</strong> 
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Prodotto
											</p>
											<p>  
												<input type="text" disabled="disabled" value="<?php echo $prodotto->firstChild->textContent." (".$prodotto->getElementsByTagName("prezzoListino")->item(0)->textContent." Crediti)"; ?>"  />  
											</p>		
										</div>	
										<div class="campo">
											<p>
												Prezzo (Cr.)
											</p>
											<p>
												<input type="text" name="prezzoContabile" value="<?php if(isset($_POST['prezzoContabile'])) echo $_POST['prezzoContabile']; else echo $offerta->firstChild->textContent; ?>"  />
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> Il valore richiesto per la vendita del bene, oltre ad essere rappresentato da un numero con quattro cifre intere (al pi&ugrave;) e due decimali separate tramite un punto, non potr&agrave; eccedere il relativo valore di listino.</p>		
										<div class="campo">
											<p>
												Quantitativo (max. 30 unit&agrave;)
											</p>
											<p>
												<input type="text" name="quantitativo" value="<?php if(isset($_POST['quantitativo'])) echo $_POST['quantitativo']; else echo $offerta->getElementsByTagName("quantitativo")->item(0)->textContent;?>"  />
											</p>										
										</div>
										<p class="nota"><strong>N.B.</strong> I pezzi rappresentano, in termini di disponibilit&agrave;, quanti prodotti si possono vendere al momento.</p>		
									</div>
								</div>
								<?php
									if(isset($_POST["sconto"]))
										echo "<div class=\"intestazione_sezione\" style=\"margin-bottom: 1.5em;\" id=\"intestazione_sezione_sconti\">\n"; 
									else {
										if($offerta->getElementsByTagName("sconto")->length!=0)
											echo "<div class=\"intestazione_sezione\" style=\"margin-bottom: 1.5em;\" id=\"intestazione_sezione_sconti\">\n"; 
										else
											echo "<div class=\"intestazione_sezione\" style=\"margin-bottom: 0.5em;\" id=\"intestazione_sezione_sconti\">\n"; 
									}
								?> 
									<div class="container_intestazione_sezione">
										<span>
											Riduzioni di Prezzo (Opzionale) <strong style="color: rgb(217, 118, 64);" title="&egrave; possibile selezionare soltanto una tipologia di sconto. Per eliminare le proprie scelte, basta chiudere il relativo men&ugrave;">*</strong> 
										</span>
										<?php 
											// COME ACCENNATO, L'APPARIZIONE DELLA SEZIONE INERENTE AGLI SCONTI VIENE GESTITA MEDIANTE DELLE FUNZIONI DEFINITE IN JavaScript, LE QUALI, OLTRE A SVOLGERE UN SIMILE COMPITO, PERMETTERANNO DI ATTIVARE I CAMPI, INIZIALMENTE DISABILITATI, IN BASE ALLA TIPOLOGIA DI RIDUZIONE DEL PREZZO SELEZIONATA 
											// NELLA PRATICA, TUTO QUESTO È STATO RESO POSSIBILE DAGLI "ATTRIBUTI" onclick="..." E id="..." E DALLA DEFINIZIONE DI OPPORTUNE CLASSI IN CSS, QUALI nascondi (display: none;) E mostra (display: block;)
											if(isset($_POST["sconto"]))
												echo "<span onclick=\"gestioneMenuSconti()\" style=\"width: auto; right: 0%; margin: 0%; cursor: pointer;\" id=\"freccia_menu_sconti\" class=\"freccia_tendina giu\"></span>\n";
											else {
												if($offerta->getElementsByTagName("sconto")->length!=0)
													echo "<span onclick=\"gestioneMenuSconti()\" style=\"width: auto; right: 0%; margin: 0%; cursor: pointer;\" id=\"freccia_menu_sconti\" class=\"freccia_tendina giu\"></span>\n";
												else
													echo "<span onclick=\"gestioneMenuSconti()\" style=\"width: auto; right: 0%; margin: 0%; cursor: pointer;\" id=\"freccia_menu_sconti\" class=\"freccia_tendina destra\"></span>\n";
											}
										?>
									</div>
								</div>
								<?php 
									// POICHÈ SI HA LA NECESSITÀ DI CREARE DINAMICAMENTE LA PAGINA IN QUESTIONE, SI PROCEDE CON UNA SERIE DI CONTROLLI E CONSIDERAZIONI ATTI ALLA DEFINIZIONE E CARATTERIZZAZIONE DELLE SINGOLE PARTI A SECONDA DEL CONTENUTO ORIGINALE DELL'OFFERTA 
									if(isset($_POST["sconto"]))
										echo "<div class=\"elenco_campi mostra\" id=\"elenco_sconti\">\n";
									else {
										if($offerta->getElementsByTagName("sconto")->length!=0)
											echo "<div class=\"elenco_campi mostra\" id=\"elenco_sconti\">\n";
										else
											echo "<div class=\"elenco_campi nascondi\" id=\"elenco_sconti\">\n";
									}
								?>
									<div class="container_elenco_campi">
										<div class="campo">
											<p style="display: flex; align-items: center;">
												<?php
													// SE LA PROPOSTA DI VENDITA È CARATTERIZZATA DA UNO SCONTO A TEMPO, IL RELATIVO PULSANTE VERRÀ SEGNATO IN MODO TALE DA GARANTIRE CONGRUENZA CON IL CONTENUTO ORIGINALE DI QUEST'ULTIMA  
													if(isset($_POST["sconto"]) && $_POST["sconto"]=="a_tempo")
														echo "<input type=\"radio\" checked=\"checked\" id=\"radio_sconto_a_tempo\" onclick=\"gestioneSelezioneScontiBonus()\"  name=\"sconto\" value=\"a_tempo\" />\n";
													else {
														echo "<input type=\"radio\" id=\"radio_sconto_a_tempo\" onclick=\"gestioneSelezioneScontiBonus()\"  name=\"sconto\" value=\"a_tempo\" />\n";
													}
												?>
												<span>
													Sconto a Tempo (max. 100%)
													<?php
														if($offerta->getElementsByTagName("scontoATempo")->length!=0)
															echo "<strong style=\"color: rgb(119, 119, 119); cursor: pointer; padding-left: 0.25em; \" title=\"presente nel contenuto originale dell'offerta\">&#10003;</strong>\n";
													?>
												</span>
											</p>
											<p>
												<?php
													if(isset($_POST["sconto"])) {
														if($_POST["sconto"]=="a_tempo")
															echo "<input id=\"sconto_a_tempo\" type=\"text\" name=\"sconto_a_tempo\" value=\"".$_POST['sconto_a_tempo']."\" />\n";
														else
															echo "<input id=\"sconto_a_tempo\" type=\"text\" name=\"sconto_a_tempo\" disabled=\"disabled\" value=\"\" />\n";
													}
													else {
														if($offerta->getElementsByTagName("scontoATempo")->length!=0)
															echo "<input id=\"sconto_a_tempo\" type=\"text\" name=\"sconto_a_tempo\" disabled=\"disabled\" value=\"".$offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale")."\" />\n";
														else
															echo "<input id=\"sconto_a_tempo\" type=\"text\" name=\"sconto_a_tempo\" disabled=\"disabled\" value=\"\" />\n";
													}
												?>
											</p>	
										</div>
										<div class="campo">
											<p style="display: flex; align-items: center;">
												<?php
													// COME PRIMA, SOLTANTO CHE LE VARIE CONSIDERAZIONI SI RIFERISCONO ALLA PROMOZIONE SUL PROSSIMO ACQUISTO
													if(isset($_POST["sconto"]) && $_POST["sconto"]=="futuro")
														echo "<input type=\"radio\" id=\"radio_sconto_futuro\" onclick=\"gestioneSelezioneScontiBonus()\"  checked=\"checked\" name=\"sconto\" value=\"futuro\" />\n";
													else {
														echo "<input type=\"radio\" id=\"radio_sconto_futuro\" onclick=\"gestioneSelezioneScontiBonus()\"  name=\"sconto\" value=\"futuro\" />\n";
													}
												?>
												<span>
													Promozionale (max. 100%)
													<?php
														if($offerta->getElementsByTagName("scontoFuturo")->length!=0)
															echo "<strong style=\"color: rgb(119, 119, 119); cursor: pointer; padding-left: 0.25em; \" title=\"presente nel contenuto originale dell'offerta\">&#10003;</strong>\n";
													?>
												</span>
											</p>
											<p>
												<?php
													if(isset($_POST["sconto"])) {
														if($_POST["sconto"]=="futuro")
															echo "<input id=\"sconto_futuro\" type=\"text\" name=\"sconto_futuro\" value=\"".$_POST['sconto_futuro']."\" />\n";
														else
															echo "<input id=\"sconto_futuro\" type=\"text\" name=\"sconto_futuro\" disabled=\"disabled\" value=\"\" />\n";
													}
													else {
														if($offerta->getElementsByTagName("scontoFuturo")->length!=0)
															echo "<input id=\"sconto_futuro\" type=\"text\" name=\"sconto_futuro\" disabled=\"disabled\" value=\"".$offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale")."\" />\n";
														else
															echo "<input id=\"sconto_futuro\" type=\"text\" name=\"sconto_futuro\" disabled=\"disabled\" value=\"\" />\n";
													}
												?>
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> Le riduzioni di cui sopra, definite come percentuali con due cifre decimali, verranno applicate, rispettivamente, sull'offerta in questione o sul prossimo acquisto del cliente.</p>		
										<div class="campo">
											<p>
												Data di Inizio (quella corrente)
												<?php
													if($offerta->getElementsByTagName("sconto")->length!=0)
														echo "<strong style=\"color: rgb(119, 119, 119); cursor: pointer; padding-left: 0.25em; \" title=\"presente nel contenuto originale dell'offerta\">&#10003;</strong>\n";
												?>
											</p>
											<p>
												<?php
													if(isset($_POST["sconto"]))
														echo "<input id=\"inizio_Applicazione\" type=\"text\" name=\"inizio_Applicazione\" value=\"".$_POST["inizio_Applicazione"]."\"  />\n";
													else {
														if($offerta->getElementsByTagName("sconto")->length!=0) {
															if($offerta->getElementsByTagName("scontoATempo")->length!=0)
																echo "<input id=\"inizio_Applicazione\" type=\"text\" name=\"inizio_Applicazione\" disabled=\"disabled\" value=\"".$offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione")."\"  />\n";
															else
																echo "<input id=\"inizio_Applicazione\" type=\"text\" name=\"inizio_Applicazione\" disabled=\"disabled\" value=\"".$offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione")."\"  />\n";
														}
														else
															echo "<input id=\"inizio_Applicazione\" type=\"text\" name=\"inizio_Applicazione\" disabled=\"disabled\" value=\"\"  />\n";
													}
												?>
											</p>	
										</div>
										<div class="campo">
											<p>
												Data di Fine
												<?php
													if($offerta->getElementsByTagName("sconto")->length!=0)
														echo "<strong style=\"color: rgb(119, 119, 119); cursor: pointer; padding-left: 0.25em; \" title=\"presente nel contenuto originale dell'offerta\">&#10003;</strong>\n";
												?>
											</p>
											<p>
												<?php
													if(isset($_POST["sconto"]))
														echo "<input id=\"fine_Applicazione\" type=\"text\" name=\"fine_Applicazione\" value=\"".$_POST["fine_Applicazione"]."\"  />\n";
													else {
														if($offerta->getElementsByTagName("sconto")->length!=0) {
															if($offerta->getElementsByTagName("scontoATempo")->length!=0)
																echo "<input id=\"fine_Applicazione\" type=\"text\" name=\"fine_Applicazione\" disabled=\"disabled\" value=\"".$offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione")."\"  />\n";
															else
																echo "<input id=\"fine_Applicazione\" type=\"text\" name=\"fine_Applicazione\" disabled=\"disabled\" value=\"".$offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione")."\"  />\n";
														}
														else
															echo "<input id=\"fine_Applicazione\" type=\"text\" name=\"fine_Applicazione\" disabled=\"disabled\" value=\"\"  />\n";
													}
												?>
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> I riferimenti temporali dovranno rispettare il formato anno-mese-giorno, con gli ultimi due caratterizzati da ben due cifre ciascuno.</p>		
									</div>
								</div>
								<?php
									if(isset($_POST["radio_bonus"]))
										echo "<div class=\"intestazione_sezione\" style=\"margin-bottom: 1.5em;\" id=\"intestazione_sezione_bonus\">\n"; 
									else {
										if($offerta->getElementsByTagName("bonus")->length!=0)
											echo "<div class=\"intestazione_sezione\" style=\"margin-bottom: 1.5em;\" id=\"intestazione_sezione_bonus\">\n"; 
										else
											echo "<div class=\"intestazione_sezione\" style=\"margin-bottom: 0.5em;\" id=\"intestazione_sezione_bonus\">\n"; 
									}
								?>
									<div class="container_intestazione_sezione">
										<span>
											Crediti Bonus <strong style="color: rgb(217, 118, 64);" title="per eliminare le proprie scelte, basta chiudere il relativo men&ugrave;">*</strong> 
										</span>
										<?php 
											// I BONUS AVRANNO UNA GESTIONE E UN FUNZIONAMENTO DEL TUTTO SIMILE A QUELLA DEGLI SCONTI 
											if(isset($_POST["radio_bonus"]))
												echo "<span onclick=\"gestioneMenuBonus()\" style=\"width: auto; right: 0%; margin: 0%; cursor: pointer;\" id=\"freccia_menu_bonus\" class=\"freccia_tendina giu\"></span>\n";
											else {
												if($offerta->getElementsByTagName("bonus")->length!=0)
													echo "<span onclick=\"gestioneMenuBonus()\" style=\"width: auto; right: 0%; margin: 0%; cursor: pointer;\" id=\"freccia_menu_bonus\" class=\"freccia_tendina giu\"></span>\n";
												else
													echo "<span onclick=\"gestioneMenuBonus()\" style=\"width: auto; right: 0%; margin: 0%; cursor: pointer;\" id=\"freccia_menu_bonus\" class=\"freccia_tendina destra\"></span>\n";
											}
										?>
									</div>
								</div>
								<?php
									if(isset($_POST["radio_bonus"]))
										echo "<div class=\"elenco_campi mostra\" id=\"elenco_bonus\">\n";
									else {
										if($offerta->getElementsByTagName("bonus")->length!=0)
											echo "<div class=\"elenco_campi mostra\" id=\"elenco_bonus\">\n";
										else
											echo "<div class=\"elenco_campi nascondi\" id=\"elenco_bonus\">\n";
									}
								?>
									<div class="container_elenco_campi">
										<div class="campo">
											<p style="display: flex; align-items: center;">
												<?php
													// I BONUS AVRANNO UNA GESTIONE E UN FUNZIONAMENTO DEL TUTTO SIMILE A QUELLA DEGLI SCONTI
													if(isset($_POST["radio_bonus"]))
														echo "<input type=\"radio\" checked=\"checked\" id=\"radio_bonus\" name=\"radio_bonus\" onclick=\"gestioneSelezioneScontiBonus()\" />\n";
													else {
														if($offerta->getElementsByTagName("bonus")->length!=0)
															echo "<input type=\"radio\" id=\"radio_bonus\" name=\"radio_bonus\" onclick=\"gestioneSelezioneScontiBonus()\" />\n";
														else
															echo "<input type=\"radio\" id=\"radio_bonus\" name=\"radio_bonus\" onclick=\"gestioneSelezioneScontiBonus()\" />\n";
													}
												?>
												<span>
													Importo (Cr.)
													<?php
														if($offerta->getElementsByTagName("bonus")->length!=0)
															echo "<strong style=\"color: rgb(119, 119, 119); cursor: pointer; padding-left: 0.25em; \" title=\"presente nel contenuto originale dell'offerta\">&#10003;</strong>\n";
													?>
												</span>
											</p>
											<p>
												<?php
													// I RAGIONAMENTI DI CUI SOPRA SI RIFLETTONO ANCHE SUI POSSIBILI CREDITI BONUS
													if(isset($_POST["radio_bonus"]))
														echo "<input id=\"bonus\" type=\"text\" name=\"bonus\" value=\"".$_POST["bonus"]."\"  />\n";
													else {
														if($offerta->getElementsByTagName("bonus")->length!=0)
															echo "<input id=\"bonus\" type=\"text\" name=\"bonus\" disabled=\"disabled\" value=\"".$offerta->getElementsByTagName("bonus")->item(0)->firstChild->textContent."\"  />\n";
														else
															echo "<input id=\"bonus\" type=\"text\" name=\"bonus\" disabled=\"disabled\" value=\"\"  />\n";
													}
												?>
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> Il campo di cui sopra, definito come un valore con al pi&ugrave; quattro cifre intere e due decimali, rappresenta il numero di crediti che verranno assegnati ai clienti che approfitteranno della proposta di vendita.</p>		
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
			// IN AGGIUNTA, SEGUENDO GLI STESSI RAGIONAMENTI APPLICATI PER L'INTESTAZIONE, È STATO RITENUTO UTILE RICHIAMARE IL PIÈ DI PAGINA ALL'INTERNO DI TUTTE QUELLE SCHERMATE IN CUI SE NE MANIFESTA IL BISOGNO
			require_once("./footer_sito.php");
		?>
	</body>
</html>