<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA COMPILARE PER INSERIRE UN INTERVENTO ALL'INTERNO DI UNA DISCUSSIONE CHE SI RIFERISCE AD UN DETERMINATO PRODOTTO 
	// N.B.: IN CASO DI ERRORE, LE INFORMAZIONI INSERITE, COSÌ COME LE SCELTE EFFETTUATE, VERRANNO PRESERVATE TRAMITE UNA SERIE DI CONTROLLI APPLICATI AL SOLO SCOPO DI AGEVOLARE L'OPERATO DEI VARI UTENTI D'INTERESSE
	
	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");

	// PER QUESTIONI DI SICUREZZA, È STATO NECESSARIO L'INSERIMENTO DELLO SCRIPT "private_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA. QUESTO MECCANISMO NON PERMETTERÀ L'ACCESSO AGLI UTENTI CHE NON SI AUTENTICATI TRAMITE IL RELATIVO SERVIZIO
	require_once("./private_session_control.php");
	
	// I CLIENTI DELLA PIATTAFORMA POSSONO SUBIRE UNA SOSPENSIONE DEL PROFILO A CAUSA DEL LORO COMPORTAMENTO. PROPRIO PER QUESTO, E CONSIDERANDO CHE CIÒ PUÒ AVVENIRE IN QUALUNQUE MOMENTO, BISOGNERÀ MONITORARE COSTANTEMENTE I LORO "PERMESSI" COSÌ DA IMPEDIRNE LA NAVIGAZIONE VERSO LE SEZIONI PIÙ SENSIBILI DEL SITO 
	require_once("./monitoraggio_stato_account.php");
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");
	
	// LE PROPOSTE DI VENDITA, DATA LA LORO STRUTTURA, POTREBBERO ESSERE SOGGETTE A DELLE RIDUZIONI DI PREZZO, CARATTERIZZATE DA UN INTERVALLO DI VALIDITÀ BEN DEFINITO E TALVOLTA APPLICABILI SUL PROSSIMO ACQUISTO. PERTANTO, NELLE IPOTESI IN CUI NON SIA SEMPRE POSSIBILE RIMUOVERE MANUALMENTE LE VOCI INTERESSATE, ABBIAMO DECISO DI IMPLEMENTARE UN CODICE IN GRADO DI INTERVENIRE IN AUTOMATICO SU TUTTI QUELLI SCONTI NON PIÙ USUFRUIBILI DAI CLIENTI DELLA PIATTAFORMA
	require_once("./rimozione_sconti.php");
	
	// LA PAGINA, ESSENDO RAGGIUNGIBILE DOPO AVER PREMUTO IL PULSANTE PER PUBBLICARE UN CERTO INTERVENTO, NECESSITA DI UN ULTERIORE CONTROLLO PER APPURARE SE È STATA EFFETTIVAMENTE PRESA UNA DECISIONE IN MERITO O MENO 
	if(!isset($_GET["id_Offerta"]))
		header("Location: index.php");
	
	// ***
	if(!(isset($_GET["id_Discussione"]))) {
		$id_Offerta=$_GET["id_Offerta"];
		
		header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
	}
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_discussioni.php");
	require_once("./apertura_file_acquisti.php");
	
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
	
	// DATA LA LORO SOMIGLIANZA, IL MECCANISMO APPLICATO IN PRECEDENZA PER LE OFFERTE POTRÀ ESSERE ESTESO ANCHE PER I SINGOLI ELEMENTI DA VALUTARE
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
	
	// PER I CONTROLLI CHE VERRANNO APPLICATI PIÙ IN BASSO, SI HA LA NECESSITÀ DI INDIVIDUARE IL PRODOTTO A CUI SI RIFERISCE LA PROPOSTA DI VENDITA SELEZIONATA  
	for($i=0; $i<$prodotti->length; $i++) {
		$prodotto=$prodotti->item($i);
		
		if($prodotto->getAttribute("id")==$offerta->getAttribute("idProdotto"))
			break;
	}
	
	// INOLTRE, BISOGNERÀ FARE IN MODO CHE L'ELEMENTO DI INTERESSE SIA EFFETTIVAMENTE RICONDUCIBILE AL PRODOTTO A CUI SI RIFERISCE L'OFFERTA SELEZIONATA
	$contributo_esistente=false;
	
	if(isset($_GET["id_Discussione"])) {
		for($i=0; $i<$prodotto->getElementsByTagName("discussione")->length && !$contributo_esistente; $i++) {
			if($prodotto->getElementsByTagName("discussione")->item($i)->getAttribute("idDiscussione")==$discussione->getAttribute("id"))
				$contributo_esistente=true;
		}
	}

	
	if(!$contributo_esistente) {
		$id_Offerta=$_GET["id_Offerta"];
		
		header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
	}
	
	// TRA I DETTAGLI CHE MEGLIO SI PRESTANO A RAPPRESENTARE UN DETERMINATO CONTRIBUTO, VI È SICURAMENTE L'USERNAME DELL'UTENTE CHE LO HA PUBBLICATO
	if(isset($_GET["id_Discussione"])) {
		$sql="SELECT Username FROM $tab WHERE ID=".$discussione->getAttribute("idAutore");
		$result=mysqli_query($conn, $sql);
		
		// NEL CASO IN CUI SI SIA VERIFICATA UN'ALTERAZIONE DI UNO DEI CAMPI INERENTI ALLA PRECEDENTE INTERROGAZIONE, BISOGNERÀ IMPEDIRE CHE L'UTENTE POSSA CONTINUARE A VISUALIZZARE LA PAGINA IN ESAME
		if(mysqli_num_rows($result)==0) {
			$id_Offerta=$_GET["id_Offerta"];
			
			header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
		}
		
		while($row=mysqli_fetch_array($result)) 
			$username=$row["Username"];
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
		$_POST["testo"]=trim($_POST["testo"]);
		$_POST["testo"]=rtrim($_POST["testo"]);
		
		// PER QUESTIONI DI FORMATTAZIONE DEL DOCUMENTO XML, È STATO NECESSARIO DISPORRE TUTTE LE COMPONENTI DELLA RECENSIONE DELL'ARTICOLO ALL'INTERNO DI UN'UNICA RIGA. A TALE SCOPO, ABBIAMO USUFRUITO DEL METODO explode(...) SPECIFICANDO "\n" COME PARAMETRO DELIMITATORE PER EFFETTUARE LA SEPARAZIONE DELLA STRINGA
		$testo=explode("\n", $_POST["testo"]);
		$_POST["testo"]="";
		
		foreach($testo as $riga) {
			$_POST["testo"]=$_POST["testo"].$riga;
		}
		
		// AL FINE DI GARANTIRE UN DISCRETO LIVELLO DI SICUREZZA, PER I CAMPI PRIVI DI CONTROLLI INERENTI AD ESPRESSIONI REGOLARI (ADEGUATE PER IL CONTESTO) VERRANNO APPLICATI DEI CONTROLLI INERENTI AI CARATTERI AL LORO INTERNO 
		$_POST["testo"]=stripslashes($_POST["testo"]); // UN ESEMPIO, POTREBBE ESSERE LA RIMOZIONE DEI BACKSLASH \ PER EVITARE LA MySQL Injection
		
		// GIUNTI A QUESTO PUNTO, E SECONDO L'ORDINAMENTO INDOTTO DALLE PRECEDENTI OPERAZIONI, È NECESSARIO EFFETTUARE UN ULTERIORE CONTROLLO PER VALUTARE SE SI È EFFETTIVAMENTE INSERITO QUALCOSA ALL'INTERNO DEI VARI CAMPI 
		if(strlen($_POST["testo"])==0) {
			// IN CASO DI ERRORE, SI DOVRÀ INFORMARE L'UTENTE COINVOLTO DELLE MANCANZE CHE HA AVUTO NEI CONFRONTI DEL MODULO PER L'INSERIMENTO DELLA PROPOSTA DI VENDITA. UN SIMILE MECCANISMO VERRÀ IMPLEMENTATO PER TUTTE LE CASISITCHE IN CUI SARÀ IMPOSSIBILE PROCEDERE  
			$campi_vuoti=true;
		}
		else {
			// PRIMA DI PROCEDERE CON L'INSERIMENTO DELLA NUOVA RISPOSTA, BISOGNA EFFETTUARE DEI CONTROLLI PER VALUTARE SE UN DETERMINATO ELEMENTO ECCEDE LA DIMENSIONE MASSIMA INDICATA
			if(strlen($_POST["testo"])>1989) {
				// ***
				$superamento_testo=true;
			}
			else {
				// SE LE VERIFICHE DI CUI SOPRA NON HANNO INDIVIDUATO ALCUNA SORTA DI PROBLEMATICA, ALLORA È POSSIBILE PROCEDERE CON L'EFFETTIVO INSERIMENTO DELLA RISPOSTA
				// LA RAPPRESENTAZIONE DI UN INTERVENTO È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUEST'ULTIMO. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI INDICATI DALL'UTENTE
				$nuovo_intervento=$docDiscussioni->createElement("intervento");
				
				// CONTESTUALMENTE ALLA DEFINIZIONE DI UN INDICE PER L'ELEMENTO SUDDETTO, È PREVISTO L'ADEGUAMENTO DELL'ATTRIBUTO RELATIVO ALLA RADICE DEGLI INTERVENTI E INERENTE AL NUMERO DI RISPOSTE INSERITE FINORA PER QUELLA PARTICOLARE DISCUSSIONE  
				$interventi=$discussione->getElementsByTagName("interventi")->item(0);
				$interventi->setAttribute("ultimoId", $interventi->getAttribute("ultimoId")+1);
				$nuovo_intervento->setAttribute("id", $interventi->getAttribute("ultimoId"));
				$nuovo_intervento->setAttribute("idPartecipante", $_SESSION["id_Utente"]);
				$nuovo_intervento->setAttribute("dataOraIssue", date("Y-m-d H:i:s"));
				$nuovo_intervento->setAttribute("moderato", "No");
				
				$testo=$docDiscussioni->createElement("testo", $_POST["testo"]);
				
				$nuovo_intervento->appendChild($testo);
				
				$valutazioni=$docDiscussioni->createElement("valutazioni");
				
				$nuovo_intervento->appendChild($valutazioni);
				
				$interventi->appendChild($nuovo_intervento);
				
				// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
				if($docDiscussioni->schemaValidate("../../XML/Schema/Discussioni.xsd")){
					
					// AL FINE DI GARANTIRE UNA STAMPA GODIBILE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
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
					echo "\t\t\t\t\t<p>LA DIMENSIONE DELLA RISPOSTA ECCEDE IL NUMERO MASSIMO DI CARATTERI CONSENTITI...</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
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
									<img src="../../Immagini/comments-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Aggiungi un nuovo intervento!</h2>
							</div>
						</div>
						<form class="corpo_form" action="<?php echo $_SERVER['PHP_SELF']."?id_Offerta=".$_GET["id_Offerta"]."&amp;id_Discussione=".$_GET["id_Discussione"]; ?>" method="post">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Discussione (Informativo)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Avviata da
											</p>
											<p>
												<?php
													echo "<input type=\"text\" disabled=\"disabled\" value=\"".$username."\" />\n";
												?>
											</p>		
										</div>
										<div class="campo">
											<p>
												Titolo
											</p>
											<p>
												<?php
													echo "<input type=\"text\" disabled=\"disabled\" value=\"".$discussione->getElementsByTagName("titolo")->item(0)->textContent."\" />\n";
												?>
											</p>		
										</div>
										<div class="campo_descrizione">
											<p>
												Contenuto
											</p>
											<p>
												<?php
													echo "<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\" >".$discussione->getElementsByTagName("descrizione")->item(0)->textContent."</textarea>\n";
												?>
											</p>		
										</div>
										<p class="nota"><strong>N.B.</strong> La precedente sezione si limita a riportare le informazioni principali inerenti alla discussione di interesse.</p>
									</div>
								</div>
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Intervento (Obbligatorio)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo_descrizione">
											<p>
												Testo (max. 1989 caratteri)
											</p>
											<p>
												<textarea name="testo" rows="0" cols="0"><?php if(isset($_POST['testo'])) echo $_POST['testo']; else echo '';?></textarea>
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> L'ultimo campo permette di esprimere il proprio pensiero limitando soltanto la lunghezza del testo che &egrave; possibile produrre. Proprio per questo, si prega di essere quanto pi&ugrave; chiari possibile.</p>		
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
			require_once ("./footer_sito.php");
		?>
	</body>
</html>