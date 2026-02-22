<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php 
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA DOVER COMPILARE PER POTER INVIARE, IN CASO DI "SMARRIMENTO", LA RICHIESTA PER POTER AGGIORNARE LA PAROLA CHIAVE DEL PROPRIO PROFILO
	// N.B.: IN CASO DI ERRORE, LE INFORMAZIONI INSERITE, COSÌ COME LE SCELTE EFFETTUATE, VERRANNO PRESERVATE TRAMITE UNA SERIE DI CONTROLLI APPLICATI AL SOLO SCOPO DI AGEVOLARE L'OPERATO DEI VARI UTENTI D'INTERESSE
	
	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");
	
	// PER QUESTIONI DI SICUREZZA, È NECESSARIO UN CONTROLLO PER IMPENDIRE ACCESSI NON PREVISTI. NEL DETTAGLIO, SE UN UTENTE HA GIÀ EFFETTUATO L'AUTENTICAZIONE, NON DOVRÀ ESSERE IN GRADO DI VISUALIZZARE LA PAGINA IN ESAME FINO ALLA SUA DISCONNESSIONE
	session_start();
	if(isset($_SESSION["id_Utente"]))
		header("Location: area_riservata.php");
	else {
		$_SESSION=array();
		session_destroy();
	}
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");
	
	// LE PROPOSTE DI VENDITA, DATA LA LORO STRUTTURA, POTREBBERO ESSERE SOGGETTE A DELLE RIDUZIONI DI PREZZO, CARATTERIZZATE DA UN INTERVALLO DI VALIDITÀ BEN DEFINITO E TALVOLTA APPLICABILI SUL PROSSIMO ACQUISTO. PERTANTO, NELLE IPOTESI IN CUI NON SIA SEMPRE POSSIBILE RIMUOVERE MANUALMENTE LE VOCI INTERESSATE, ABBIAMO DECISO DI IMPLEMENTARE UN CODICE IN GRADO DI INTERVENIRE IN AUTOMATICO SU TUTTI QUELLI SCONTI NON PIÙ USUFRUIBILI DAI CLIENTI DELLA PIATTAFORMA
	require_once("./rimozione_sconti.php");
	
	// IL PULSANTE AVENTE LA DICITURA "INDIETRO" PERMETTERÀ ALL'UTENTE DI TORNARE ALLA SCHERMATA PRECEDENTE A QUELLA CORRENTE
	if(isset($_POST["back"]))
		header("Location: login.php");
	
	// UNA VOLTA CONFERMATE LE PROPRIE SCELTE, BISOGNA PROCEDERE CON I CONTROLLI PER VALUTARE LA CORRETTEZZA E L'INTEGRITÀ DEI VALORI INDICATI ALL'INTERNO DEI VARI CAMPI  
	if(isset($_POST["confirm"])){
		
		// IL PRIMO PASSO CONSISTE NELLA RIMOZIONE DEI POSSIBILI SPAZI (BIANCHI) COLLOCATI ALL'INIZIO E ALLA FINE DEGLI ELEMENTI COINVOLTI
		$_POST["username"]=trim($_POST["username"]);
		$_POST["username"]=rtrim($_POST["username"]);
		
		$_POST["email"]=trim($_POST["email"]);
		$_POST["email"]=rtrim($_POST["email"]);
		
		// AL FINE DI GARANTIRE UN DISCRETO LIVELLO DI SICUREZZA, PER I CAMPI PRIVI DI CONTROLLI INERENTI AD ESPRESSIONI REGOLARI (ADEGUATE PER IL CONTESTO) VERRANNO APPLICATI DEI CONTROLLI INERENTI AI CARATTERI AL LORO INTERNO 
		$_POST["username"]=stripslashes($_POST["username"]); // UN ESEMPIO, POTREBBE ESSERE LA RIMOZIONE DEI BACKSLASH \ PER EVITARE LA MySQL Injection
		$_POST["email"]=stripslashes($_POST["email"]); // ***
		
		// GIUNTI A QUESTO PUNTO, E SECONDO L'ORDINAMENTO INDOTTO DALLE PRECEDENTI OPERAZIONI, È NECESSARIO EFFETTUARE UN ULTERIORE CONTROLLO PER VALUTARE SE SI È EFFETTIVAMENTE INSERITO QUALCOSA ALL'INTERNO DEI VARI CAMPI 
		if((strlen($_POST["username"])==0) || (strlen($_POST["email"])==0))
		{
			// IN CASO DI ERRORE, SI DOVRÀ INFORMARE L'UTENTE COINVOLTO DELLE MANCANZE CHE HA AVUTO NEI CONFRONTI DEL MODULO PER L'INSERIMENTO DELLA PROPOSTA DI VENDITA. UN SIMILE MECCANISMO VERRÀ IMPLEMENTATO PER TUTTE LE CASISITCHE IN CUI SARÀ IMPOSSIBILE PROCEDERE  
			$campi_vuoti=true;
		}
		else
		{
			// PRIMA DI TERMINARE CON L'INOLTRO DELLA RICHIESTA, È NECESSARIO VERIFICARE CHE I RIFERIMENTI INSERITI, QUALI USERNAME ED EMAIL, SIANO ENTRAMBI ASSOCIATI AD UN DETERMINATO UTENTE
			$sql="SELECT ID, Tipo_Utente, Ban FROM $tab WHERE Username='".$_POST["username"]."' AND Email='".$_POST["email"]."'";  
			$result=mysqli_query($conn, $sql);
			
			// NEL CASO IN CUI CI SIANO DELLE CORRISPONDENZE CON QUANTO MEMORIZZATO ALL'INTERNO DELLA BASE DI DATI, SI PROCEDERÀ CON LA PREPARAZIONE DEL MESSAGGIO DA INVIARE 
			if(mysqli_num_rows($result)!=0) {
				// PRIMA DI POTER INTERAGIRE CON IL DOCUMENTO CONTENENTE LE DOMANDE DI CUI SOPRA, BISOGNERÀ VALUTARE SIA LA NATURA DELL'UTENTE CHE LO STATO ATTUALE DEL SUO PROFILO, IN QUANTO UNA SIMILE FUNZIONALITÀ POTRÀ ESSERE UTILIZZATA DAI SOLI CLIENTI NON ANCORA SOSPESI  
				while($row=mysqli_fetch_array($result)) {
					$id=$row["ID"];
					$tipo=$row["Tipo_Utente"];
					$ban=$row["Ban"];
				}
				
				if($tipo=="C") {
					
					if($ban=="N") {
						
						require_once("./apertura_file_domande_assistenza.php");
						
						// COME RIPORTATO IN CORRISPONDENZA DEI CAMPI DA COMPILARE, L'UTENTE SARÀ IN GRADO DI INVIARE LA RICHIESTA SOLTANTO SE QUELLE INOLTRATE IN PRECEDENZA SONO GIÀ STATE RISOLTE
						$duplicazione_domanda=false;
						
						for($i=0; $i<$domande->length && !$duplicazione_domanda; $i++) {
							$domanda=$domande->item($i);
							
							if($domanda->getAttribute("idRichiedente")==$id)
								$duplicazione_domanda=true;
							
						}
						
						if(!$duplicazione_domanda) {
							// LA RAPPRESENTAZIONE DI UNA DOMANDA È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUES'ULTIMA. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI INDICATI DAL CLIENTE 
							$nuova_domanda=$docDomande->createElement("domanda");
							
							// CONTESTUALMENTE ALLA DEFINIZIONE DI UN INDICE PER LA VOCE SUDDETTA, È PREVISTO L'ADEGUAMENTO DELL'ATTRIBUTO RELATIVO ALLA RADICE DEL DOCUMENTO E INERENTE AL NUMERO DI DOMANDE INSERITE FINORA  
							$rootDomande->setAttribute("ultimoId", $rootDomande->getAttribute("ultimoId")+1);
							$nuova_domanda->setAttribute("id", $rootDomande->getAttribute("ultimoId"));
							
							$nuova_domanda->setAttribute("idRichiedente", $id);
							$nuova_domanda->setAttribute("dataOraRichiesta", date("Y-m-d H:i:s"));
							$nuova_domanda->setAttribute("seen", "No");
							
							$rootDomande->appendChild($nuova_domanda);
							
							// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
							if($docDomande->schemaValidate("../../XML/Schema/Domande_Assistenza.xsd")){
								
								// AL FINE DI GARANTIRE UNA STAMPA GODIBILE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
								$docDomande->preserveWhiteSpace = false;
								$docDomande->formatOutput = true;
								$docDomande->save("../../XML/Domande_Assistenza.xml");
								
								// PRIMA DI ESSERE REINDIRIZZATI, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
								setcookie("modifica_Effettuata", true);
								
								header("Location: index.php");
							}
							else {
								
								// ***
								setcookie("errore_Validazione", true);
								
								
								header("Location: index.php");
							}
						}
					}
					else {
						// ***
						$permessi_negati=true;
					}
				}
				else {
					// ***
					$tipo_errato=true;
				}
			}
			else {
				// ***
				$riferimenti_errati=true;
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
				if(isset($riferimenti_errati) && $riferimenti_errati) {
					// ***
					$riferimenti_errati=false;
					
					echo "<div class=\"error_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
					echo "\t\t\t\t\t<p>L'USERNAME E/O L'EMAIL INSERITI NON SONO CORRETTI...</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
				}
				else {
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
						echo "\t\t\t\t\t<p>IL PROFILO INDICATO NON PREVEDE L'APPLICAZIONE DI UN SIMILE MECCANISMO...</p>\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t</div>\n";
						echo "\t\t</div>\n";
					}
					else {
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
							echo "\t\t\t\t\t<p>OPERAZIONE NEGATA PER SOSPENSIONE DEL PROFILO...</p>\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t</div>\n";
							echo "\t\t</div>\n";
						}
						else {
							if(isset($duplicazione_domanda) && $duplicazione_domanda) {
								// ***
								$duplicazione_domanda=false;
								
								echo "<div class=\"error_message\">\n";
								echo "\t\t\t<div class=\"container_message\">\n";
								echo "\t\t\t\t<div class=\"container_img\">\n";
								echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t\t<div class=\"message\">\n";
								echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
								echo "\t\t\t\t\t<p>&Egrave; GI&Agrave; STATA INOLTRATA UNA RICHIESTA PER RISOLVERE IL PROBLEMA...</p>\n";
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
									<img src="../../Immagini/key-solid.svg" alt="Icona Password" />
								</span>
								<h2>Effettua la richiesta per una nuova parola chiave!</h2>
							</div>
						</div>
						<form class="corpo_form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Profilo Utente (Obbligatorio) <strong style="color: rgb(217, 118, 64);" title="per questioni di sicurezza, &egrave; necessario l'inserimento del tuo username e del tuo indirizzo di posta elettronica">*</strong>
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Username
											</p>
											<p>
												<input type="text" name="username" value="<?php if(isset($_POST['username'])) echo $_POST['username']; else echo '';?>"  />
											</p>	
										</div>
										<div class="campo">
											<p>
												Email
											</p>
											<p>
												<input type="text" name="email" value="<?php if(isset($_POST['email'])) echo $_POST['email']; else echo '';?>"  />
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> La domanda in oggetto potr&agrave; essere sottomessa soltanto se l'amministratore ha gi&agrave; processato quelle relative al profilo indicato.</p>
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