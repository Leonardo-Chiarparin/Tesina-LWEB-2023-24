<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA COMPILARE PER MODIFICARE I VARI PARAMETRI INERENTI ALLA PROFILAZIONE DEL CLIENTE
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
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO AI CLIENTI DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="C")
		header ("Location: area_riservata.php");
	
	// COME PER I FILE XML, SI PROCEDE CON UN'INTERROGAZIONE ALLA BASE DI DATI SUDDETTA AL FINE DI REPERIRE LE CREDENZIALI RELATIVE ALL'UTENTE PER AGEVOLARNE LA PROCEDURA DI MODIFICA  
	$sql="SELECT Username, Email, Password FROM $tab WHERE ID=".$_SESSION["id_Utente"]; 
	$result=mysqli_query($conn, $sql);
	
	// NEL CASO IN CUI CI SIANO DELLE CORRISPONDENZE (IN REALTÀ UNA SOLTANTO), SI PROCEDE CON IL SALVATAGGIO DI TUTTI GLI ELEMENTI DI CUI SI È FATTA RICHIESTA
	while($row=mysqli_fetch_array($result)){
		$username=$row["Username"];
		$email=$row["Email"];
		$old_password=$row["Password"];
	}
	
	// IL PULSANTE AVENTE LA DICITURA "INDIETRO" PERMETTERÀ ALL'UTENTE DI TORNARE ALLA SCHERMATA PRECEDENTE A QUELLA CORRENTE
	if(isset($_POST["back"])) {
		header("Location: area_riservata.php");
	}
	
	// UNA VOLTA CONFERMATE LE PROPRIE SCELTE, BISOGNA PROCEDERE CON I CONTROLLI PER VALUTARE LA CORRETTEZZA E L'INTEGRITÀ DEI VALORI INDICATI ALL'INTERNO DEI VARI CAMPI 
	if(isset($_POST["confirm"])) {
		
		// IL PRIMO PASSO CONSISTE NELLA RIMOZIONE DEI POSSIBILI SPAZI (BIANCHI) COLLOCATI ALL'INIZIO E ALLA FINE DEGLI ELEMENTI COINVOLTI
		$_POST["username"]=trim($_POST["username"]);
		$_POST["username"]=rtrim($_POST["username"]);
		
		$_POST["email"]=trim($_POST["email"]);
		$_POST["email"]=rtrim($_POST["email"]);
		
		$_POST["old_password"]=trim($_POST["old_password"]);
		$_POST["old_password"]=rtrim($_POST["old_password"]);
		
		$_POST["new_password"]=trim($_POST["new_password"]);
		$_POST["new_password"]=rtrim($_POST["new_password"]);
		
		// AL FINE DI GARANTIRE UN DISCRETO LIVELLO DI SICUREZZA, PER I CAMPI PRIVI DI CONTROLLI INERENTI AD ESPRESSIONI REGOLARI (ADEGUATE PER IL CONTESTO) VERRANNO APPLICATI DEI CONTROLLI INERENTI AI CARATTERI AL LORO INTERNO 
		$_POST["username"]=stripslashes($_POST["username"]); // UN ESEMPIO, POTREBBE ESSERE LA RIMOZIONE DEI BACKSLASH \ PER EVITARE LA MySQL Injection
		
		// GIUNTI A QUESTO PUNTO, E SECONDO L'ORDINAMENTO INDOTTO DALLE PRECEDENTI OPERAZIONI, È NECESSARIO EFFETTUARE UN ULTERIORE CONTROLLO PER VALUTARE SE SI È EFFETTIVAMENTE INSERITO QUALCOSA ALL'INTERNO DEI VARI CAMPI 
		if((strlen($_POST["username"])==0)||(strlen($_POST["email"])==0)||(strlen($_POST["old_password"])==0)||(strlen($_POST["new_password"])==0)) {
			
			// IN CASO DI ERRORE, SI DOVRÀ INFORMARE L'UTENTE COINVOLTO DELLE MANCANZE CHE HA AVUTO NEI CONFRONTI DEL MODULO PER L'INSERIMENTO DELLA PROPOSTA DI VENDITA. UN SIMILE MECCANISMO VERRÀ IMPLEMENTATO PER TUTTE LE CASISITCHE IN CUI SARÀ IMPOSSIBILE PROCEDERE  
			$campi_vuoti=true;
		}
		else {
			// PRIMA DI PROCEDERE OLTRE, BISOGNA EFFETTUARE DEI CONTROLLI PRELIMINARI PER VALUTARE SE UN DETERMINATO VALORE ECCEDE LA DIMENSIONE INDICATA
			if(strlen($_POST["username"])>30) {
			
				// ***
				$superamento_username=true;
			}
			
			if(strlen($_POST["email"])>35) {
				
				// ***
				$superamento_email=true;
			}
			
			if(strlen($_POST["new_password"])>16) {
				
				// ***
				$superamento_password=true;
			}
			
			// SE LE VERIFICHE DI CUI SOPRA NON HANNO INDIVIDUATO ALCUNA SORTA DI PROBLEMATICA, ALLORA È POSSIBILE PROCEDERE CON LE OPERAZIONI RESTANTI
			if(!(isset($superamento_username) && $superamento_username) && !(isset($superamento_email) && $superamento_email) && !(isset($superamento_password) && $superamento_password)) {
				// UNA VOLTA TERMINATE LE VERIFICHE PRELIMINARI, BISOGNERÀ PROCEDERE CON DEI CONTROLLI DISPOSTI A CASCATA, DUNQUE ANNIDATI, PER LA VERIFICA DEL FORMATO DEI VARI ELEMENTI COINVOLTI 
				// STANDO A COME È STATA CARATTERIZZATA LA PAGINA IN QUESTIONE, I PRIMI VALORI A DOVER RIPORTARE UNA COMPOSIZIONE BEN DELINEATA SONO PROPRIO L'INDIRIZZO DI POSTA ELETTRONICA (ESEMPIO: example@example.DOM, CON IL DOMINIO CARATTERIZZATO DA DUE O TRE CARATTERI) E LA NUOVA PAROLA CHIAVE (DI LUNGHEZZA PARI AL PIÙ A SEDICI ELEMENTI, TRA CUI DOVRANNO ESSERCI ALMENO UN NUMERO, UNA LETTERA MINUSCOLA E UN CARATTERE MAIUSCOLO) FORNITI DALL'UTENTE 
				if (preg_match("/((([[:alpha:]]|(\d))+)@([[:alpha:]]+)(\.[[:alpha:]]{2,3}))/",$_POST["email"],$matches_email)) {
					if($matches_email[0]!=$_POST["email"]) {
						
						// ***
						$email_errata=true;
					}
					else {
						if(preg_match("/(((?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])).{3,16}$)/",$_POST["new_password"],$matches_new_password)) {
							if($matches_new_password[0]!=$_POST["new_password"]) {
						
								// ***
								$password_errata=true;
							}
							else {
								// PER CONSENTIRE LA MODIFICA DELLA PASSWORD, L'UTENTE DOVRÀ RIPORTARE ANCHE QUELLA DA SOSTITUIRE. PROPRIO PER QUESTO, È NECESSARIO CHE CI SIA UNA CORRISPONDENZA TRA QUANTO INSERITO E CIÒ CHE È PRESENTE ALL'INTERNO DEL DATABASE
								if($_POST["old_password"]==$old_password){
									// DATO L'INTENTO DI VOLER CONFRONTARE L'ESITO DI UNA DETERMINATA QUERY, SARÀ NECESSARIO PREDISPORRE IL TUTTO ALL'INTERNO DI UN COSTRUTTO try ... catch ... AL FINE DI CATTURARE L'EVENTUALE ECCEZIONE E NOTIFICARE L'ACCADUTO ALL'UTENTE IN OGGETTO
									// INFATTI, UN ESEMPIO POTREBBE ESSERE LA DUPLICAZIONE DEL CONTENUTO DEI CAMPI DEFINITI COME unique, OVVERO L'USERNAME E L'INDIRIZZO DI POSTA ELETTRONICA 
									// PER QUANTO CONCERNE L'EVENTUALITÀ PRESENTATA, SARÀ SUFFICIENTE VERIFICARE IL NUMERO DI RIGHE OTTENUTE COME RISULTATO A SEGUITO DELLA SEGUENTE INTERROGAZIONE ALLA BASE DI DATI
									$sql="SELECT * FROM $tab WHERE Username='".$_POST["username"]."' AND ID<>".$_SESSION["id_Utente"];
									$result=mysqli_query($conn, $sql);
									
									if(mysqli_num_rows($result)==0) {
										$sql="SELECT * FROM $tab WHERE Email='".$_POST["email"]."' AND ID<>".$_SESSION["id_Utente"];
										$result=mysqli_query($conn, $sql);
										
										if(mysqli_num_rows($result)==0) { 
											try {
												// SE NON È STATA EVIDENZIATA ALCUNA SORTA DI PROBLEMATICA, È POSSIBILE EFFETTUARE L'ADEGUAMENTO DEI DATI ALL'INTERNO DELLA BASE DI DATI
												$sql="UPDATE $tab SET Username='".$_POST["username"]."', Email='".$_POST["email"]."', Password='".$_POST["new_password"]."' WHERE ID=".$_SESSION["id_Utente"];
												
												// COME ACCENNATO, PRIMA DI CONCLUDERE L'OPERAZIONE BISOGNERÀ VALUTARE L'ESITO DELL'ESECUZIONE INERENTE AL PRECEDENTE COMANDO SQL
												if(mysqli_query($conn,$sql)){ 
													
													// PRIMA DI ESSERE REIDERIZZATI, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
													$_SESSION["modifica_Effettuata"]=true;
													
													// INOLTRE, BISOGNERÀ ADEGUARE IL CONTENUTO DELLA VARIABILE DI SESSIONE INERENTE ALL'USERNAME
													$_SESSION["username_Utente"]=$_POST["username"];
													
													header("Location: area_riservata.php");
													
												}
												else {
													throw new mysqli_sql_exception;
												}
											}
											catch(mysqli_sql_exception $e){
												
												// *** 
												$errore_query=true;							
											}
										}
										else {
											// ***
											$duplicazione_email=true;
										}
									}
									else {
										// ***
										$duplicazione_username=true;
									}
								}
								else {
									
									// ***
									$password_differenti=true;
								}
							}
						}
						else {
							// ***
							$password_errata=true;
						}
					}
				}
				else {
					// ***
					$email_errata=true;
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
			// POICHÈ DURANTE IL CONTROLLO DEI VARI CAMPI SI POTREBBERO MANIFESTARE DELLE PROBLEMATICHE, RISULTA NECESSARIO INCLUDERE L'INSIEME DEI POSSIBILI MESSAGGI DI POPUP CHE POSSONO ESSERE MOSTRATI ALL'UTENTE, COSÌ DA POTERLO RENDERE PARTECIPE DELL'EVENTUALE ERRORE COMMESSO
			require_once("./messaggistica_operazioni_sui_dati.php");
			
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
									<img src="../../Immagini/right-to-bracket-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Aggiorna i dettagli per l'autenticazione!</h2>
							</div>
						</div>
						<form class="corpo_form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Profilo Utente (Obbligatorio) <strong style="color: rgb(217, 118, 64);" title="ripetere o lasciare invariati i valori che si intende preservare">*</strong>
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Username (max. 30 caratteri)
											</p>
											<p>
												<input type="text" name="username" value="<?php if(isset($_POST['username'])) echo $_POST['username']; else echo $username;?>"  />
											</p>	
										</div>
										<div class="campo">
											<p>
												Email
											</p>
											<p>
												<input type="text" name="email" value="<?php if(isset($_POST['email'])) echo $_POST['email']; else echo $email;?>"  />
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> La lunghezza complessiva dell'indirizzo di posta elettronica non pu&ograve; essere superiore a 35 caratteri.</p>
										<div class="campo">
											<p>
												Vecchia Password
											</p>
											<p>
												<input type="password" name="old_password" value="<?php if(isset($_POST['old_password'])) echo $_POST['old_password']; else echo '';?>"  />
											</p>	
										</div>
										<div class="campo">
											<p>
												Nuova Password
											</p>
											<p>
												<input type="password" name="new_password" value="<?php if(isset($_POST['new_password'])) echo $_POST['new_password']; else echo '';?>"  />
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> La parola chiave dovr&agrave; contenere al pi&ugrave; 16 elementi, di cui (almeno): un numero, una lettera minuscola e una lettera maiuscola.</p>
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