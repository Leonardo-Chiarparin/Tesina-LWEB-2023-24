<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA COMPILARE PER AVVIARE UNA NUOVA CONVERSAZIONE IN RELAZIONE AL PRODOTTO COINVOLTO ALL'INTERNO DELLA PROPOSTA DI VENDITA DI INTERESSE
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
	
	// LA PAGINA, ESSENDO RAGGIUNGIBILE DOPO AVER PREMUTO IL PULSANTE PER CREARE UNA NUOVA DISCUSSIONE, NECESSITA DI UN ULTERIORE CONTROLLO PER APPURARE SE È STATA EFFETTIVAMENTE PRESA UNA DECISIONE IN MERITO O MENO 
	if(!isset($_GET["id_Offerta"]))
		header("Location: index.php");
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_piattaforme_videogiochi.php");
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_discussioni.php");
	
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
	
	// INOLTRE, PER LE RAGIONI ILLUSTRATE PIÙ IN BASSO, SI HA LA NECESSITÀ DI INDIVIDUARE E MOSTRARE IL PRODOTTO E LA PIATTAFORMA SU CUI PUÒ ESSERE MANDATO IN ESECUZIONE L'EVENTUALE VIDEOGIOCO 
	for($i=0; $i<$prodotti->length; $i++) {
		$prodotto=$prodotti->item($i);
		
		if($prodotto->getAttribute("id")==$offerta->getAttribute("idProdotto"))
			break;
	}
	
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
		$_POST["titolo"]=trim($_POST["titolo"]);
		$_POST["titolo"]=rtrim($_POST["titolo"]);

		$_POST["descrizione"]=trim($_POST["descrizione"]);
		$_POST["descrizione"]=rtrim($_POST["descrizione"]);
		
		// PER QUESTIONI DI FORMATTAZIONE DEL DOCUMENTO XML, È STATO NECESSARIO DISPORRE TUTTE LE COMPONENTI DELLA DESCRIZIONE ALL'INTERNO DI UN'UNICA RIGA. A TALE SCOPO, ABBIAMO USUFRUITO DEL METODO explode(...) SPECIFICANDO "\n" COME PARAMETRO DELIMITATORE PER EFFETTUARE LA SEPARAZIONE DELLA STRINGA
		$descrizione=explode("\n", $_POST["descrizione"]);
		$_POST["descrizione"]="";
		
		foreach($descrizione as $riga) {
			$_POST["descrizione"]=$_POST["descrizione"].$riga;
		}
		
		// AL FINE DI GARANTIRE UN DISCRETO LIVELLO DI SICUREZZA, PER I CAMPI PRIVI DI CONTROLLI INERENTI AD ESPRESSIONI REGOLARI (ADEGUATE PER IL CONTESTO) VERRANNO APPLICATI DEI CONTROLLI INERENTI AI CARATTERI AL LORO INTERNO 
		$_POST["titolo"]=stripslashes($_POST["titolo"]); // UN ESEMPIO, POTREBBE ESSERE LA RIMOZIONE DEI BACKSLASH \ PER EVITARE LA MySQL Injection
		$_POST["descrizione"]=stripslashes($_POST["descrizione"]); // ***
		
		// GIUNTI A QUESTO PUNTO, E SECONDO L'ORDINAMENTO INDOTTO DALLE PRECEDENTI OPERAZIONI, È NECESSARIO EFFETTUARE UN ULTERIORE CONTROLLO PER VALUTARE SE SI È EFFETTIVAMENTE INSERITO QUALCOSA ALL'INTERNO DEI VARI CAMPI 
		if(strlen($_POST["titolo"])==0 || strlen($_POST["descrizione"])==0) {
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
			
			// PER CONCLUDERE, È POSSIBILE PROCEDERE CON L'EFFETIVO INSERIMENTO DELLA NUOVA OCCORRENZA ALL'INTERNO DEL FILE XML
			if(!(isset($superamento_titolo) && $superamento_titolo) && !(isset($superamento_descrizione) && $superamento_descrizione)) {
				
				// LA RAPPRESENTAZIONE DI UNA DISCUSSIONE È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUEST'ULTIMA. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI INDICATI DALL'UTENTE
				$nuova_discussione=$docDiscussioni->createElement("discussione");
				
				// CONTESTUALMENTE ALLA DEFINIZIONE DI UN INDICE PER L'ELEMENTO SUDDETTO, È PREVISTO L'ADEGUAMENTO DELL'ATTRIBUTO RELATIVO ALLA RADICE DEL DOCUMENTO E INERENTE AL NUMERO DI DISCUSSIONI INSERITE FINORA  
				$rootDiscussioni->setAttribute("ultimoId", $rootDiscussioni->getAttribute("ultimoId")+1);
				$nuova_discussione->setAttribute("id", $rootDiscussioni->getAttribute("ultimoId"));
				$nuova_discussione->setAttribute("idAutore", $_SESSION["id_Utente"]);
				$nuova_discussione->setAttribute("dataOraIssue", date("Y-m-d H:i:s"));
				$nuova_discussione->setAttribute("moderata", "No");
				$nuova_discussione->setAttribute("risolta", "No");
				
				$titolo=$docDiscussioni->createElement("titolo", $_POST["titolo"]);
				
				$nuova_discussione->appendChild($titolo);
				
				$descrizione=$docDiscussioni->createElement("descrizione", $_POST["descrizione"]);
				
				$nuova_discussione->appendChild($descrizione);
				
				$interventi=$docDiscussioni->createElement("interventi");
				$interventi->setAttribute("ultimoId", 0);
				
				$nuova_discussione->appendChild($interventi);
				
				$rootDiscussioni->appendChild($nuova_discussione);
				
				// CONTESTUALMENTE ALLA PRECEDENTE OPERAZIONE, SARÀ NECESSARIO INSERIRE IL RIFERIMENTO DELLA NUOVA DISCUSSIONE ALL'INTERNO DELL'ENTITÀ CHE RAPPRESENTA IL PRODOTTO DI INTERESSE
				$discussioni_prodotto=$prodotto->getElementsByTagName("discussioni")->item(0);
				
				$nuova_discussione_prodotto=$docProdotti->createElement("discussione");
				$nuova_discussione_prodotto->setAttribute("idDiscussione", $nuova_discussione->getAttribute("id"));
				
				$discussioni_prodotto->appendChild($nuova_discussione_prodotto);
				
				$docProdotti->preserveWhiteSpace = false;
				$docProdotti->formatOutput = true;
				$docProdotti->save("../../XML/Prodotti.xml");
					
				// INOLTRE, POICHÈ SI HA A CHE FARE CON UN FILE INERENTE AD UNA GRAMMATICA DTD, SARÀ NECESSARIO CARICARE NUOVAMENTE IL DOCUMENTO PER PROCEDERE CON IL RELATIVO CONTROLLO DI VALIDITÀ
				$dom_prodotti=new DOMDocument();
				$dom_prodotti->load("../../XML/Prodotti.xml");
				
				// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
				if($docDiscussioni->schemaValidate("../../XML/Schema/Discussioni.xsd") && $dom_prodotti->validate()){
					
					// ***
					$docDiscussioni->preserveWhiteSpace = false;
					$docDiscussioni->formatOutput = true;
					$docDiscussioni->save("../../XML/Discussioni.xml");
					
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
									<img src="../../Immagini/question-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Aggiungi una nuova discussione!</h2>
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
											Discussione (Obbligatorio)
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
												Descrizione (max. 1989 caratteri)
											</p>
											<p>
												<textarea name="descrizione" rows="0" cols="0"><?php if(isset($_POST['descrizione'])) echo $_POST['descrizione']; else echo '';?></textarea>
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> Gli ultimi campi permettono di esprimere i propri dubbi limitando soltanto la lunghezza del testo che &egrave; possibile produrre. Proprio per questo, si prega di essere quanto pi&ugrave; chiari possibile.</p>		
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