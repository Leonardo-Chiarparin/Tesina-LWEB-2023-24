<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTE LE RICHIESTE PER LA RICARICA DEL SALDO DEI VARI CLIENTI A CUI L'AMMINISTRATORE NON HA ANCORA RISPOSTO
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
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_richieste_crediti.php");
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO AI CLIENTI DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="C")
		header ("Location: area_riservata.php");
	
	// IL PULSANTE AVENTE LA DICITURA "INDIETRO" PERMETTERÀ ALL'UTENTE DI TORNARE ALLA SCHERMATA PRECEDENTE A QUELLA CORRENTE
	if(isset($_POST["back"])) {
		header("Location: saldo_clienti.php");
	}
	
	// UNA VOLTA CONFERMATE LE PROPRIE SCELTE, BISOGNA PROCEDERE CON I CONTROLLI PER VALUTARE LA CORRETTEZZA E L'INTEGRITÀ DEI VALORI INDICATI ALL'INTERNO DEI VARI CAMPI 
	if(isset($_POST["confirm"])) {
		
		// IL PRIMO PASSO CONSISTE NELLA RIMOZIONE DEI POSSIBILI SPAZI (BIANCHI) COLLOCATI ALL'INIZIO E ALLA FINE DEGLI ELEMENTI COINVOLTI
		$_POST["importo"]=trim($_POST["importo"]);
		$_POST["importo"]=rtrim($_POST["importo"]);
		
		$_POST["importo_confermato"]=trim($_POST["importo_confermato"]);
		$_POST["importo_confermato"]=rtrim($_POST["importo_confermato"]);
		
		// GIUNTI A QUESTO PUNTO, E SECONDO L'ORDINAMENTO INDOTTO DALLE PRECEDENTI OPERAZIONI, È NECESSARIO EFFETTUARE UN ULTERIORE CONTROLLO PER VALUTARE SE SI È EFFETTIVAMENTE INSERITO QUALCOSA ALL'INTERNO DEI VARI CAMPI 
		if((strlen($_POST["importo"])==0)||(strlen($_POST["importo_confermato"])==0)) {
			
			// IN CASO DI ERRORE, SI DOVRÀ INFORMARE L'UTENTE COINVOLTO DELLE MANCANZE CHE HA AVUTO NEI CONFRONTI DEL MODULO PER L'INSERIMENTO DELLA PROPOSTA DI VENDITA. UN SIMILE MECCANISMO VERRÀ IMPLEMENTATO PER TUTTE LE CASISITCHE IN CUI SARÀ IMPOSSIBILE PROCEDERE  
			$campi_vuoti=true;
		}
		else
		{
			// PRIMA DI PROCEDERE OLTRE, BISOGNA EFFETTUARE DEI CONTROLLI PRELIMINARI PER VALUTARE SE UN DETERMINATO VALORE ECCEDE LA DIMENSIONE INDICATA
			if(($_POST["importo"]<=0 || $_POST["importo"]>2500) || ($_POST["importo_confermato"]<=0 || $_POST["importo_confermato"]>2500)) {
				
				// ***
				$superamento_importo=true;
			}
			
			// SE LE VERIFICHE DI CUI SOPRA NON HANNO INDIVIDUATO ALCUNA SORTA DI PROBLEMATICA, ALLORA È POSSIBILE PROCEDERE CON LE OPERAZIONI RESTANTI
			if(!(isset($superamento_importo) && $superamento_importo)) {
				// UNA VOLTA TERMINATE LE VERIFICHE PRELIMINARI, BISOGNERÀ PROCEDERE CON DEI CONTROLLI DISPOSTI A CASCATA, DUNQUE ANNIDATI, PER LA VERIFICA DEL FORMATO DEI VARI ELEMENTI COINVOLTI 
				// STANDO A COME È STATA CARATTERIZZATA LA PAGINA IN QUESTIONE, L'UNICO VALORE A DOVER RIPORTARE UNA COMPOSIZIONE BEN DELINEATA È PROPRIO L'IMPORTO DI RICARICA FORNITO DALL'UTENTE, IL QUALE DOVRÀ ESSERE UN NUMERO CARATTERIZZATO DA DA QUATTRO CIFRE INTERE (AL PIÙ) E DUE DECIMALI 
				if (preg_match("/([[:digit:]]{1,4}\.[[:digit:]]{2,2})/",$_POST["importo"],$matches)) {
					if($matches[0]!=$_POST["importo"]) {
						// ***
						$importo_errato=true;
					}
					else
					{
						// PER DI PIÙ, È NECESSARIO EFFETTUARE UN CONTROLLO INERENTE ALLA CORRISPONDENZA TRA GLI IMPORTI INSERITI DALL'UTENTE E ALL'AMMISSIBILITÀ DEL NUMERO DI CREDITI RICHIESTI
						if($_POST["importo"]==$_POST["importo_confermato"]){
							
							// PRIMA DI PROCEDERE CON L'INSERIMENTO DELLA NUOVA DOMANDA, BISOGNA VALUTARE L'AMMONTARE DEI CREDITI A CUI SI TROVA L'UTENTE IN QUESTIONE, IN QUANTO POTREBBE AVER GIÀ RAGGIUNTO LA SOGLIA MASSIMA PREVISTA (10000 UNITÀ)
							$sql="SELECT Portafoglio_Crediti FROM $tab WHERE ID=".$_SESSION["id_Utente"];
							$result=mysqli_query($conn, $sql);
							
							while($row=mysqli_fetch_array($result))
								$portafoglio_crediti=$row["Portafoglio_Crediti"];
							
							// NEL CASO IN CUI IL RISULTATO DELL'INCREMENTO SIA MINORE O UGUALE AL VALORE LIMITE INDICATO, SI PROCEDERÀ CON L'EFFETTIVO INOLTRO DELLA RICHIESTA
							if($portafoglio_crediti+$_POST["importo"]<=10000) {
							
								// LA RAPPRESENTAZIONE DI UNA DOMANDA È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUES'ULTIMA. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI INDICATI DAL CLIENTE 
								$nuova_richiesta=$docRichieste->createElement("richiesta");
								
								// CONTESTUALMENTE ALLA DEFINIZIONE DI UN INDICE PER LA DOMANDA SUDDETTA, È PREVISTO L'ADEGUAMENTO DELL'ATTRIBUTO RELATIVO ALLA RADICE DEL DOCUMENTO E INERENTE AL NUMERO DI RICHIESTE INSERITE FINORA  
								$rootRichieste->setAttribute("ultimoId", $rootRichieste->getAttribute("ultimoId")+1);
								$nuova_richiesta->setAttribute("id", $rootRichieste->getAttribute("ultimoId"));
								
								$nuova_richiesta->setAttribute("idRichiedente", $_SESSION["id_Utente"]);
								$nuova_richiesta->setAttribute("dataOraRichiesta", date("Y-m-d H:i:s"));
								$nuova_richiesta->setAttribute("numeroCrediti", $_POST["importo"]);
								$nuova_richiesta->setAttribute("stato", "In Corso");
								
								$rootRichieste->appendChild($nuova_richiesta);
								
								// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
								if($docRichieste->schemaValidate("../../XML/Schema/Richieste_Crediti.xsd")){
									
									// AL FINE DI GARANTIRE UNA STAMPA GODIBILE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
									$docRichieste->preserveWhiteSpace = false;
									$docRichieste->formatOutput = true;
									$docRichieste->save("../../XML/Richieste_Crediti.xml");
									
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
							else {
								// ***
								$limite_superato=true;
							}
						}
						else {
							
							// ***
							$importi_differenti=true;
						}
					}
				}
				else
				{
					// ***
					$importo_errato=true;
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
			else
			{
				// ***
				if(isset($superamento_importo) && $superamento_importo) 
				{ // ***
					$superamento_importo=false;
					
					echo "<div class=\"error_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
					echo "\t\t\t\t\t<p>GLI IMPORTI NON DEVONO ESSERE NULLI O SUPERIORI AL LIMITE INDICATO...</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
				}
				else {
					// ***
					if(isset($importo_errato) && $importo_errato) 
					{ // ***
						$importo_errato=false;
						
						echo "<div class=\"error_message\">\n";
						echo "\t\t\t<div class=\"container_message\">\n";
						echo "\t\t\t\t<div class=\"container_img\">\n";
						echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t\t<div class=\"message\">\n";
						echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
						echo "\t\t\t\t\t<p>IL FORMATO DEL (PRIMO) IMPORTO NON È VALIDO...</p>\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t</div>\n";
						echo "\t\t</div>\n";
					}
					else {
						// ***
						if(isset($importi_differenti) && $importi_differenti) {
							// ***
							$importi_differenti=false;
						
							echo "<div class=\"error_message\">\n";
							echo "\t\t\t<div class=\"container_message\">\n";
							echo "\t\t\t\t<div class=\"container_img\">\n";
							echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t\t<div class=\"message\">\n";
							echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
							echo "\t\t\t\t\t<p>GLI IMPORTI INDICATI NON CORRISPONDONO...</p>\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t</div>\n";
							echo "\t\t</div>\n";
						}
						else {
							// ***
							if(isset($limite_superato) && $limite_superato) {
								// ***
								$limite_superato=false;
							
								echo "<div class=\"error_message\">\n";
								echo "\t\t\t<div class=\"container_message\">\n";
								echo "\t\t\t\t<div class=\"container_img\">\n";
								echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t\t<div class=\"message\">\n";
								echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
								echo "\t\t\t\t\t<p>IMPORTO RIFIUTATO POICHÈ SI POTREBBE SUPERARE LA SOGLIA DEI 10000 Cr....</p>\n";
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
									<img src="../../Immagini/comments-dollar-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Richiesta per i crediti!</h2>
							</div>
						</div>
						<form class="corpo_form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Profilo Economico (Obbligatorio)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Importo (max. 2500 Cr.)
											</p>
											<p>
												<input type="text" name="importo" value="<?php if(isset($_POST['importo'])) echo $_POST['importo']; else echo '';?>"  />
											</p>	
										</div>
										<div class="campo">
											<p>
												Conferma Importo (Cr.)
											</p>
											<p>
												<input type="text" name="importo_confermato" value="<?php if(isset($_POST['importo_confermato'])) echo $_POST['importo_confermato']; else echo '';?>"  />
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> L'ammontare dei crediti dovr&agrave; essere formato da due cifre decimali separate da quelle intere tramite un punto. La richiesta verr&agrave; inoltrata se non &egrave; ancora stata raggiunta la soglia delle 10000 unit&agrave;.</p>		
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
			require_once("./footer_sito.php");
		?>
	</body>
</html>