<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA COMPILARE PER INSERIRE UNA NUOVA RECENSIONE IN CORRISPONDENZA DEL PRODOTTO A CUI SI RIFERISCE L'OFFERTA SELEZIONATA
	// N.B.: IN CASO DI ERRORE, LE INFORMAZIONI INSERITE, COSÌ COME LE SCELTE EFFETTUATE, VERRANNO PRESERVATE TRAMITE UNA SERIE DI CONTROLLI APPLICATI AL SOLO SCOPO DI AGEVOLARE L'OPERATO DEI VARI UTENTI D'INTERESSE

	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");

	// PER QUESTIONI DI SICUREZZA, È STATO NECESSARIO L'INSERIMENTO DELLO SCRIPT "private_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA. QUESTO MECCANISMO NON PERMETTERÀ L'ACCESSO AGLI UTENTI CHE NON SI AUTENTICATI TRAMITE IL RELATIVO SERVIZIO
	require_once("./private_session_control.php");
	
	// I CLIENTI DELLA PIATTAFORMA POSSONO SUBIRE UNA SOSPENSIONE DEL PROFILO A CAUSA DEL LORO COMPORTAMENTO. PROPRIO PER QUESTO, E CONSIDERANDO CHE CIÒ PUÒ AVVENIRE IN QUALUNQUE MOMENTO, BISOGNERÀ MONITORARE COSTANTEMENTE I LORO "PERMESSI" COSÌ DA IMPEDIRNE LA NAVIGAZIONE VERSO LE SEZIONI PIÙ SENSIBILI DEL SITO 
	require_once("./monitoraggio_stato_account.php");
	
	// LE PROPOSTE DI VENDITA, DATA LA LORO STRUTTURA, POTREBBERO ESSERE SOGGETTE A DELLE RIDUZIONI DI PREZZO, CARATTERIZZATE DA UN INTERVALLO DI VALIDITÀ BEN DEFINITO E TALVOLTA APPLICABILI SUL PROSSIMO ACQUISTO. PERTANTO, NELLE IPOTESI IN CUI NON SIA SEMPRE POSSIBILE RIMUOVERE MANUALMENTE LE VOCI INTERESSATE, ABBIAMO DECISO DI IMPLEMENTARE UN CODICE IN GRADO DI INTERVENIRE IN AUTOMATICO SU TUTTI QUELLI SCONTI NON PIÙ USUFRUIBILI DAI CLIENTI DELLA PIATTAFORMA
	require_once("./rimozione_sconti.php");
	
	// LA PAGINA, ESSENDO RAGGIUNGIBILE DOPO AVER PREMUTO IL PULSANTE PER PUBBLICARE UNA NUOVA RECENSIONE, NECESSITA DI UN ULTERIORE CONTROLLO PER APPURARE SE È STATA EFFETTIVAMENTE PRESA UNA DECISIONE IN MERITO O MENO 
	if(!isset($_GET["id_Offerta"]))
		header("Location: index.php");
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_recensioni.php");
	require_once("./apertura_file_piattaforme_videogiochi.php");
	
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
	
	if($offerta_individuata==false) {
		header("Location: index.php");
	}
	
	// DATA LA SUA COMPOSIZIONE, L'ELEMENTO CHE RAPPRESENTA UN CERTO PRODOTTO ALL'INTERNO DEL RELATIVO FILE XML CONTERRÀ IL RIFERIMENTO DELLA RECENSIONE APPENA PUBBLICATA. PROPRIO PER QUESTO, E PER IMPEDIRE CHE GLI UTENTI INSERISCANO PIÙ VOLTE LA PROPRIA OPINIONE, SI DOVRÀ INDIVIDUARE FARE RIFERIMENTO AI PRODOTTI CHE HANNO GIÀ RICEVUTO UN LORO GIUDIZIO  
	for($i=0; $i<$prodotti->length; $i++) {
		$prodotto=$prodotti->item($i);
		
		if($prodotto->getAttribute("id")==$offerta->getAttribute("idProdotto"))
			break;
	}
	
	// INOLTRE, PER LE RAGIONI ILLUSTRATE PIÙ IN BASSO, SI HA LA NECESSITÀ DI INDIVIDUARE E MOSTRARE LA PIATTAFORMA SU CUI PUÒ ESSERE MANDATO IN ESECUZIONE L'EVENTUALE VIDEOGIOCO 
	if($prodotto->getElementsByTagName("videogioco")->length!=0) {
		for($i=0; $i<$piattaforme->length; $i++) {
			$piattaforma=$piattaforme->item($i);
			
			if($piattaforma->getAttribute("id")==$prodotto->getElementsByTagName("piattaforma")->item(0)->getAttribute("idPiattaforma"))
				break;
		}
	}
	
	// CONTRARIAMENTE A MOLTE ALTRE SCHERMATE, UN CASO PARTICOLARE DI REINDERIZZAMENTO SI HA QUANDO L'UTENTE PREME SUL PULSANTE PER TORNARE ALLA PAGINA PRECEDENTE. INFATTI, POICHÈ FINORA SI È FATTO RIFERIMENTO AI VALORI PASSATI TRAMITE METODO GET, BISOGNERÀ PREDISPORRE IL NUOVO INDIRIZZO IN MODO TALE DA GARANTIRE NUOVAMENTE LA STAMPA DELLA PROPOSTA DI VENDITA SELEZIONATA IN PRECEDENZA DAL SOGGETTO D'INTERESSE
	if(isset($_POST["back"])) {
		// PER RIUSCIRCI, SARÀ SUFFICEINTE ASSEGNARE IL VALORE DI CUI SOPRA AD UNA VARIABILE "TEMPORANEA" E, IN SEGUITO, UTILIZZARE OPPORTUNAMENTE LA FUNZIONE header() 
		$id_Offerta=$_GET["id_Offerta"];
		
		// EVIDENTEMENTE, LA CONDIVISIONE DEL DATO IN QUESTIONE È STATA GESTITA COME SOPRA PER EVITARE LA CREAZIONE DI ULTERIORI VARIBILI DI SESSIONE, LE QUALI, DATA LA LIBERTÀ DI NAVIGAZIONE CONCESSA ALL'UTENTE, AVREBBERO DOVUTO ESSERE RIMOSSE IN OGNI ALTRO SCRIPT
		header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
	}
	
	// UNA VOLTA CONFERMATE LE PROPRIE SCELTE, BISOGNA PROCEDERE CON I CONTROLLI PER VALUTARE LA CORRETTEZZA E L'INTEGRITÀ DEI VALORI INDICATI ALL'INTERNO DEI VARI CAMPI
	if(isset($_POST["confirm"])) {
		
		// IL PRIMO PASSO CONSISTE NELLA RIMOZIONE DEI POSSIBILI SPAZI (BIANCHI) COLLOCATI ALL'INIZIO E ALLA FINE DEGLI ELEMENTI COINVOLTI
		if($prodotto->getElementsByTagName("libro")->length!=0) {
			$_POST["trama"]=trim($_POST["trama"]);
			$_POST["trama"]=rtrim($_POST["trama"]);
			
			$_POST["caratterizzazionePersonaggi"]=trim($_POST["caratterizzazionePersonaggi"]);
			$_POST["caratterizzazionePersonaggi"]=rtrim($_POST["caratterizzazionePersonaggi"]);
			
			$_POST["ambientazione"]=trim($_POST["ambientazione"]);
			$_POST["ambientazione"]=rtrim($_POST["ambientazione"]);
		}
		else {
			$_POST["sceneggiatura"]=trim($_POST["sceneggiatura"]);
			$_POST["sceneggiatura"]=rtrim($_POST["sceneggiatura"]);
			
			$_POST["tecnica"]=trim($_POST["tecnica"]);
			$_POST["tecnica"]=rtrim($_POST["tecnica"]);
			
			$_POST["giocabilita"]=trim($_POST["giocabilita"]);
			$_POST["giocabilita"]=rtrim($_POST["giocabilita"]);
		}
		
		$_POST["titolo"]=trim($_POST["titolo"]);
		$_POST["titolo"]=rtrim($_POST["titolo"]);

		$_POST["testo"]=trim($_POST["testo"]);
		$_POST["testo"]=rtrim($_POST["testo"]);
		
		// PER QUESTIONI DI FORMATTAZIONE DEL DOCUMENTO XML, È STATO NECESSARIO DISPORRE TUTTE LE COMPONENTI DELLA RECENSIONE ALL'INTERNO DI UN'UNICA RIGA. A TALE SCOPO, ABBIAMO USUFRUITO DEL METODO explode(...) SPECIFICANDO "\n" COME PARAMETRO DELIMITATORE PER EFFETTUARE LA SEPARAZIONE DELLA STRINGA
		$testo=explode("\n", $_POST["testo"]);
		$_POST["testo"]="";
		
		foreach($testo as $riga) {
			$_POST["testo"]=$_POST["testo"].$riga;
		}
		
		// AL FINE DI GARANTIRE UN DISCRETO LIVELLO DI SICUREZZA, PER I CAMPI PRIVI DI CONTROLLI INERENTI AD ESPRESSIONI REGOLARI (ADEGUATE PER IL CONTESTO) VERRANNO APPLICATI DEI CONTROLLI INERENTI AI CARATTERI AL LORO INTERNO 
		$_POST["titolo"]=stripslashes($_POST["titolo"]); // UN ESEMPIO, POTREBBE ESSERE LA RIMOZIONE DEI BACKSLASH \ PER EVITARE LA MySQL Injection
		$_POST["testo"]=stripslashes($_POST["testo"]); // ***
		
		// GIUNTI A QUESTO PUNTO, E SECONDO L'ORDINAMENTO INDOTTO DALLE PRECEDENTI OPERAZIONI, È NECESSARIO EFFETTUARE UN ULTERIORE CONTROLLO PER VALUTARE SE SI È EFFETTIVAMENTE INSERITO QUALCOSA ALL'INTERNO DEI VARI CAMPI 
		if($prodotto->getElementsByTagName("libro")->length!=0) {
			if(strlen($_POST["trama"])==0 || strlen($_POST["caratterizzazionePersonaggi"])==0 || strlen($_POST["ambientazione"])==0 || strlen($_POST["titolo"])==0 || strlen($_POST["testo"])==0) {
				// IN CASO DI ERRORE, SI DOVRÀ INFORMARE L'UTENTE COINVOLTO DELLE MANCANZE CHE HA AVUTO NEI CONFRONTI DEL MODULO PER L'INSERIMENTO DELLA PROPOSTA DI VENDITA. UN SIMILE MECCANISMO VERRÀ IMPLEMENTATO PER TUTTE LE CASISITCHE IN CUI SARÀ IMPOSSIBILE PROCEDERE  
				$campi_vuoti=true;
			}
		}
		else {
			if(strlen($_POST["sceneggiatura"])==0 || strlen($_POST["tecnica"])==0 || strlen($_POST["giocabilita"])==0 || strlen($_POST["titolo"])==0 || strlen($_POST["testo"])==0) {
				// ***  
				$campi_vuoti=true;
			}
		}
		
		// SE LE VERIFICHE DI CUI SOPRA NON HANNO INDIVIDUATO ALCUNA SORTA DI PROBLEMATICA, ALLORA È POSSIBILE PROCEDERE CON LE RESTANTI OPERAZIONI
		if(!(isset($campi_vuoti) && $campi_vuoti)) {
			// PRIMA DI PROCEDERE CON L'INSERIMENTO DELLA NUOVA RECENSIONE, BISOGNA EFFETTUARE DEI CONTROLLI PER VALUTARE SE UN DETERMINATO ELEMENTO ECCEDE LA DIMENSIONE O NON RISPETTA IL FORMATO INDICATO
			if($prodotto->getElementsByTagName("libro")->length!=0) {
				// I PARAMETRI DI VALUTAZIONE INERENTI AL TIPO DI PRODOTTO SELEZIONATO DOVRANNO ESSERE DEI VALORI INTERI COMPRESI NELL'INTERVALLO (1,5) CON I DUE ESTREMI INCLUSI. PER POTER IMPLEMENTARE UN SIMILE CONTROLLO, ABBIAMO DECISO DI UTILIZZARE, RISPETTIVAMENTE, I METODI range(...)  E in_array(...)     
				if(!(in_array($_POST["trama"], range(1,5))) || !(in_array($_POST["caratterizzazionePersonaggi"], range(1,5))) || !(in_array($_POST["ambientazione"], range(1,5)))) {
					// ***
					$parametri_errati=true;
				}
			}
			else {
				// ***
				if(!(in_array($_POST["sceneggiatura"], range(1,5))) || !(in_array($_POST["tecnica"], range(1,5))) || !(in_array($_POST["giocabilita"], range(1,5)))) {
					// ***
					$parametri_errati=true;
				}
			}
				
			// ***
			if(strlen($_POST["titolo"])>30) {
				// ***
				$superamento_titolo=true;
			}
			
			// ***
			if(strlen($_POST["testo"])>1989) {
				// ***
				$superamento_testo=true;
			}
			
			// GIUNTI A QUESTO PUNTO, È DOVEROSO VALUTARE SE L'UTENTE IN QUESTIONE HA GIÀ PUBBLICATO UNA RECENSIONE CHE SI RIFERISCE AL PRODOTTO D'INTERESSE 
			if(!(isset($parametri_errati) && $parametri_errati) && !(isset($superamento_titolo) && $superamento_titolo) && !(isset($superamento_testo) && $superamento_testo)) {
				$recensione_pubblicata=false;
				
				for($i=0; $i<$prodotto->getElementsByTagName("recensione")->length && !$recensione_pubblicata; $i++) {
					$recensione_prodotto=$prodotto->getElementsByTagName("recensione")->item($i);
					
					for($j=0; $j<$recensioni->length && !$recensione_pubblicata; $j++) {
						
						if($recensione_prodotto->getAttribute("idRecensione")==$recensioni->item($j)->getAttribute("id") && $recensioni->item($j)->getAttribute("idUtente")==$_SESSION["id_Utente"])
							$recensione_pubblicata=true;
					}
				}
				
				// PER CONCLUDERE, È POSSIBILE PROCEDERE CON L'EFFETIVO INSERIMENTO DELLA NUOVA OCCORRENZA ALL'INTERNO DEL FILE XML
				if(!$recensione_pubblicata) {
					
					// LA RAPPRESENTAZIONE DI UNA RECENSIONE È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUEST'ULTIMA. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI INDICATI DALL'UTENTE
					$nuova_recensione=$docRecensioni->createElement("recensione");
					
					// CONTESTUALMENTE ALLA DEFINIZIONE DI UN INDICE PER L'ELEMENTO SUDDETTO, È PREVISTO L'ADEGUAMENTO DELL'ATTRIBUTO RELATIVO ALLA RADICE DEL DOCUMENTO E INERENTE AL NUMERO DI RECENSIONI INSERITE FINORA  
					$rootRecensioni->setAttribute("ultimoId", $rootRecensioni->getAttribute("ultimoId")+1);
					$nuova_recensione->setAttribute("id", $rootRecensioni->getAttribute("ultimoId"));
					$nuova_recensione->setAttribute("idUtente", $_SESSION["id_Utente"]);
					$nuova_recensione->setAttribute("dataPubblicazione", date("Y-m-d"));
					$nuova_recensione->setAttribute("moderata", "No");
					
					$titolo=$docRecensioni->createElement("titolo", $_POST["titolo"]);
					
					$nuova_recensione->appendChild($titolo);
					
					$testo=$docRecensioni->createElement("testo", $_POST["testo"]);
					
					$nuova_recensione->appendChild($testo);
					
					$valutazione=$docRecensioni->createElement("valutazione");
					
					if($prodotto->getElementsByTagName("libro")->length!=0) {
						$perLibro=$docRecensioni->createElement("perLibro");
						$perLibro->setAttribute("trama", $_POST["trama"]);
						$perLibro->setAttribute("caratterizzazionePersonaggi", $_POST["caratterizzazionePersonaggi"]);
						$perLibro->setAttribute("ambientazione", $_POST["ambientazione"]);
						
						$valutazione->appendChild($perLibro);
					}
					else {
						$perVideogioco=$docRecensioni->createElement("perVideogioco");
						$perVideogioco->setAttribute("sceneggiatura", $_POST["sceneggiatura"]);
						$perVideogioco->setAttribute("tecnica", $_POST["tecnica"]);
						$perVideogioco->setAttribute("giocabilita", $_POST["giocabilita"]);
						
						$valutazione->appendChild($perVideogioco);
					}
					
					$nuova_recensione->appendChild($valutazione);
					
					$rootRecensioni->appendChild($nuova_recensione);
					
					// IN AGGIUNTA A QUANTO EFFETTUATO FINORA, SARÀ NECESSARIO ADEGUARE IL CONTENUTO DEL PRODOTTO ALL'INTERNO DEL CORRISPONDENTE FILE XML
					$recensioni_prodotto=$prodotto->getElementsByTagName("recensioni")->item(0);
					
					$nuovo_riferimento_recensione=$docProdotti->createElement("recensione");
					$nuovo_riferimento_recensione->setAttribute("idRecensione", $nuova_recensione->getAttribute("id"));
					
					$recensioni_prodotto->appendChild($nuovo_riferimento_recensione);
					
					// AL FINE DI GARANTIRE UNA STAMPA OTTIMALE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
					$docRecensioni->preserveWhiteSpace = false;
					$docRecensioni->formatOutput = true;
					$docRecensioni->save("../../XML/Recensioni_Prodotti.xml");
					
					// ***
					$docProdotti->preserveWhiteSpace = false;
					$docProdotti->formatOutput = true;
					$docProdotti->save("../../XML/Prodotti.xml");
					
					// INOLTRE, POICHÈ SI HA A CHE FARE CON UN FILE INERENTE AD UNA GRAMMATICA DTD, SARÀ NECESSARIO CARICARE NUOVAMENTE IL DOCUMENTO PER PROCEDERE CON IL RELATIVO CONTROLLO DI VALIDITÀ
					$dom_recensioni=new DOMDocument();
					$dom_recensioni->load("../../XML/Recensioni_Prodotti.xml");
					
					// ***
					$dom_prodotti=new DOMDocument();
					$dom_prodotti->load("../../XML/Prodotti.xml");
					
					if($dom_recensioni->validate() && $dom_prodotti->validate()){
						
						// PRIMA DI ESSERE REINDIRIZZATI, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
						$_SESSION["modifica_Effettuata"]=true;
						
						$id_Offerta=$_GET["id_Offerta"];
						
						header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
					}
					else {
						// ***
						setcookie("errore_Validazione", true);
						
						$id_Offerta=$_GET["id_Offerta"];
						
						header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
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
				if(isset($parametri_errati) && $parametri_errati) {
					// *** 
					$parametri_errati=false;
					
					echo "<div class=\"error_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
					echo "\t\t\t\t\t<p>LE VALUTAZIONI ATTRIBUITE NON APPARTENGONO ALL'INTERVALLO INDICATO...</p>\n";
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
							echo "\t\t\t\t\t<p>LA DIMENSIONE DELLE CONSIDERAZIONI ECCEDE IL NUMERO MASSIMO DI CARATTERI CONSENTITI...</p>\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t</div>\n";
							echo "\t\t</div>\n";
						}
						else {
							// ***
							if(isset($recensione_pubblicata) && $recensione_pubblicata) {
								// ***
								$recensione_pubblicata=false;
								
								echo "<div class=\"error_message\">\n";
								echo "\t\t\t<div class=\"container_message\">\n";
								echo "\t\t\t\t<div class=\"container_img\">\n";
								echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibilie...\" />\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t\t<div class=\"message\">\n";
								echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
								echo "\t\t\t\t\t<p>HAI GI&Agrave; PUBBLICATO UNA RECENSIONE PER IL PRODOTTO SELEZIONATO...</p>\n";
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
									<img src="../../Immagini/comment-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Aggiungi una nuova recensione!</h2>
							</div>
						</div>
						<form class="corpo_form" action="<?php echo $_SERVER['PHP_SELF']."?id_Offerta=".$_GET["id_Offerta"]; ?>" method="post">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Articolo (Informativo)
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
												<?php
													// POICHÈ L'INTENTO CONSISTE NEL PRESENTARE LE VARRIE INFORMAZIONI NEL MIGLIOR MODO POSSIBILE, ABBIAMO DECISO DI SUDDIVIDERE LE STAMPE DEL NOMINATIVO DEL PRODOTTO SULLA FALSA RIGA DI QUELLE PRESENTI NELLA RELATIVA SCHEDA DI RIEPILOGO E DUNQUE IN BASE ALLA NATURA STESSA DI QUEST'ULTIMO
													// IN PARTICOLARE, I LIBRI, OLTRE A PRESENTARE L'ANNO IN CUI SONO STATI DISTRIBUITI PER LA PRIMA VOLTA, SARANNO CARATTERIZZATI DALLA DICITURA "Copertina Flessibile". D'ALTRO CANTO, PER I VIDEOGIOCHI SARÀ RIPORTATA LA PIATTAFORMA SU CUI È POSSIBILE RIPRODURLI 
													if($prodotto->getElementsByTagName("libro")->length!=0)
														echo "<input type=\"text\" disabled=\"disabled\" value=\"".$prodotto->firstChild->textContent." Copertina Flessibile - ".$prodotto->getElementsByTagName("annoUscita")->item(0)->textContent."\" />\n";
													else 
														echo "<input type=\"text\" disabled=\"disabled\" value=\"".$prodotto->firstChild->textContent." - ".$piattaforma->firstChild->textContent."\" />\n";
												?>
											</p>		
										</div>
										<p class="nota"><strong>N.B.</strong> La precedente sezione si limita a riportare le informazioni principali inerenti al bene di interesse.</p>
									</div>
								</div>
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Valutazioni (Obbligatorio)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												<?php
													// COME DISCUSSO A PIÙ RIPRESE, I PARAMETRI INERENTI ALLA VALUTAZIONE DEI VARI UTENTI DIPENDONO DAL TIPO DI PRODOTTO CHE SI STA CONSIDERANDO AL MOMENTO
													if($prodotto->getElementsByTagName("libro")->length!=0)
														echo "Trama (int. da 1 a 5)\n";
													else
														echo "Sceneggiatura (int. da 1 a 5)\n"
												?>
											</p>
											<p style="margin-right: 0.55em; margin-left: -0.75em;"> 
												<?php
													if($prodotto->getElementsByTagName("libro")->length!=0) {
														echo "<select name=\"trama\" style=\"padding-left: 0.25em; padding-right: 0.25em;\">\n";
														
														// OGNI PARAMETRO DI VALUTAZIONE, OLTRE A POTER ASSUMERE UNO DEI VALORI INTERI COMPRESI TRA 1 E 5 (INCLUSI), SARÀ INIZIALIZZATO A 3, IN QUANTO QUEST'ULTIMO È STATO ETICHETTATO COME ELEMENTO INTERMEDIO DA CUI L'UTENTE POTRÀ ESORDIRE CON IL PROPRIO GIUDIZIO 
														// PER DI PIÙ, STANDO A QUANTO RIPORTATO IN PRECEDENZA, ABBIAMO DECISO DI TENERE TRACCIA, A SEGUITO DI EVENTUALI MANCANZE, DI TUTTE LE SCELTE COMPIUTE AL FINE DI RIPROPORLE DOPO IL RICARICAMENTO DELLA PAGINA
														for($i=1; $i<=5; $i++)
														{
															if(isset($_POST["trama"])) {
																if($_POST["trama"]==$i)
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
																else
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
															}
															else
															{
																if($i==3)
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
																else
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
															}
														}
														
														echo "\t\t\t\t\t\t\t\t\t\t\t\t</select>\n";
														
													}
													else {
														echo "<select name=\"sceneggiatura\" style=\"padding-left: 0.25em; padding-right: 0.25em;\">\n";
														
														// ***
														for($i=1; $i<=5; $i++)
														{
															if(isset($_POST["sceneggiatura"])) {
																if($_POST["sceneggiatura"]==$i)
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
																else
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
															}
															else
															{
																if($i==3)
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
																else
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
															}
														}
														
														echo "\t\t\t\t\t\t\t\t\t\t\t\t</select>\n";
													}
												?>
											</p>
										</div>
										<div class="campo">
											<p>
												<?php
													// ***
													if($prodotto->getElementsByTagName("libro")->length!=0)
														echo "Caratterizzazione dei Personaggi (int. da 1 a 5)\n";
													else
														echo "Tecnica (int. da 1 a 5)\n"
												?>
											</p>
											<p style="margin-right: 0.55em; margin-left: -0.75em;">  
												<?php
													if($prodotto->getElementsByTagName("libro")->length!=0) {
														echo "<select name=\"caratterizzazionePersonaggi\" style=\"padding-left: 0.25em; padding-right: 0.25em;\">\n";
														
														// ***
														for($i=1; $i<=5; $i++)
														{
															if(isset($_POST["caratterizzazionePersonaggi"])) {
																if($_POST["caratterizzazionePersonaggi"]==$i)
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
																else
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
															}
															else
															{
																if($i==3)
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
																else
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
															}
														}
														
														echo "\t\t\t\t\t\t\t\t\t\t\t\t</select>\n";
														
													}
													else {
														echo "<select name=\"tecnica\" style=\"padding-left: 0.25em; padding-right: 0.25em;\">\n";
														
														// ***
														for($i=1; $i<=5; $i++)
														{
															if(isset($_POST["tecnica"])) {
																if($_POST["tecnica"]==$i)
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
																else
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
															}
															else
															{
																if($i==3)
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
																else
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
															}
														}
														
														echo "\t\t\t\t\t\t\t\t\t\t\t\t</select>\n";
													}
												?>
											</p>	
										</div>
										<div class="campo">
											<p>
												<?php
													// ***
													if($prodotto->getElementsByTagName("libro")->length!=0)
														echo "Ambientazione (int. da 1 a 5)\n";
													else
														echo "Giocabilit&agrave; (int. da 1 a 5)\n"
												?>
											</p>
											<p style="margin-right: 0.55em; margin-left: -0.75em;">  
												<?php
													if($prodotto->getElementsByTagName("libro")->length!=0) {
														echo "<select name=\"ambientazione\" style=\"padding-left: 0.25em; padding-right: 0.25em;\">\n";
														
														// ***
														for($i=1; $i<=5; $i++)
														{
															if(isset($_POST["ambientazione"])) {
																if($_POST["ambientazione"]==$i)
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
																else
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
															}
															else
															{
																if($i==3)
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
																else
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
															}
														}
														
														echo "\t\t\t\t\t\t\t\t\t\t\t\t</select>\n";
														
													}
													else {
														echo "<select name=\"giocabilita\" style=\"padding-left: 0.25em; padding-right: 0.25em;\">\n";
														
														// ***
														for($i=1; $i<=5; $i++)
														{
															if(isset($_POST["giocabilita"])) {
																if($_POST["giocabilita"]==$i)
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
																else
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
															}
															else
															{
																if($i==3)
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
																else
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
															}
														}
														
														echo "\t\t\t\t\t\t\t\t\t\t\t\t</select>\n";
													}
												?>
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> Le voci proposte rappresentano l'insieme di tutti i possibili parametri, con annessi i punteggi, tramite cui &egrave; possibile valutare la qualit&agrave; del bene in esame.</p>		
									</div>
								</div>
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Recensione (Obbligatorio)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Titolo (max. 30 caratteri)
											</p>
											<p>
												<input type="text" name="titolo" value="<?php if(isset($_POST['titolo'])) echo $_POST['titolo']; else echo ''; ?>" />
											</p>		
										</div>
										<div class="campo_descrizione">
											<p>
												Considerazioni (max. 1989 caratteri)
											</p>
											<p>
												<textarea name="testo" rows="0" cols="0"><?php if(isset($_POST['testo'])) echo $_POST['testo']; else echo '';?></textarea>
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> Gli ultimi campi permettono di esprimere il proprio pensiero limitando soltanto la lunghezza del testo che &egrave; possibile produrre. Proprio per questo, si prega di condividere le proprie opinioni nel rispetto di quelle altrui.</p>		
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