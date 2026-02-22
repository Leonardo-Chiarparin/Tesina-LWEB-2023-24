<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA COMPILARE PER MODIFICARE I VARI DATI ANAGRAFICI DELL'UTENTE COINVOLTO
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
	
	// COME PER I FILE XML, SI PROCEDE CON UN'INTERROGAZIONE ALLA BASE DI DATI SUDDETTA AL FINE DI REPERIRE I DATI ANAGRAFICI RELATIVI ALL'UTENTE PER AGEVOLARNE LA PROCEDURA DI MODIFICA  
	$sql="SELECT Nome, Cognome, Num_Telefono, Indirizzo, Citta, CAP FROM $tab WHERE ID=".$_SESSION["id_Utente"]; 
	$result=mysqli_query($conn, $sql);
	
	// NEL CASO IN CUI CI SIANO DELLE CORRISPONDENZE (IN REALTÀ UNA SOLTANTO), SI PROCEDE CON IL SALVATAGGIO DI TUTTI GLI ELEMENTI DI CUI SI È FATTA RICHIESTA
	while($row=mysqli_fetch_array($result)){
		$nome=$row["Nome"];
		$cognome=$row["Cognome"];
		$num_telefono=$row["Num_Telefono"];
		$indirizzo=$row["Indirizzo"];
		$citta=$row["Citta"];
		$cap=$row["CAP"];
	}
	
	// IL PULSANTE AVENTE LA DICITURA "INDIETRO" PERMETTERÀ ALL'UTENTE DI TORNARE ALLA SCHERMATA PRECEDENTE A QUELLA CORRENTE
	if(isset($_POST["back"])) {
		header("Location: profilo.php");
	}
	
	// UNA VOLTA CONFERMATE LE PROPRIE SCELTE, BISOGNA PROCEDERE CON I CONTROLLI PER VALUTARE LA CORRETTEZZA E L'INTEGRITÀ DEI VALORI INDICATI ALL'INTERNO DEI VARI CAMPI 
	if(isset($_POST["confirm"])) {
		
		// IL PRIMO PASSO CONSISTE NELLA RIMOZIONE DEI POSSIBILI SPAZI (BIANCHI) COLLOCATI ALL'INIZIO E ALLA FINE DEGLI ELEMENTI COINVOLTI
		$_POST["nome"]=trim($_POST["nome"]);
		$_POST["nome"]=rtrim($_POST["nome"]);
		
		$_POST["cognome"]=trim($_POST["cognome"]);
		$_POST["cognome"]=rtrim($_POST["cognome"]);
		
		$_POST["num_telefono"]=trim($_POST["num_telefono"]);
		$_POST["num_telefono"]=rtrim($_POST["num_telefono"]);
		
		$_POST["indirizzo"]=trim($_POST["indirizzo"]);
		$_POST["indirizzo"]=rtrim($_POST["indirizzo"]);
		
		$_POST["citta"]=trim($_POST["citta"]);
		$_POST["citta"]=rtrim($_POST["citta"]);
		
		$_POST["cap"]=trim($_POST["cap"]);
		$_POST["cap"]=rtrim($_POST["cap"]);
		
		// AL FINE DI GARANTIRE UN DISCRETO LIVELLO DI SICUREZZA, PER I CAMPI PRIVI DI CONTROLLI INERENTI AD ESPRESSIONI REGOLARI (ADEGUATE PER IL CONTESTO) VERRANNO APPLICATI DEI CONTROLLI INERENTI AI CARATTERI AL LORO INTERNO 
		$_POST["nome"]=stripslashes($_POST["nome"]); // UN ESEMPIO, POTREBBE ESSERE LA RIMOZIONE DEI BACKSLASH \ PER EVITARE LA MySQL Injection
		$_POST["cognome"]=stripslashes($_POST["cognome"]);    // ***
		$_POST["indirizzo"]=stripslashes($_POST["indirizzo"]); // ***
		$_POST["citta"]=stripslashes($_POST["citta"]); // ***
		
		// GIUNTI A QUESTO PUNTO, E SECONDO L'ORDINAMENTO INDOTTO DALLE PRECEDENTI OPERAZIONI, È NECESSARIO EFFETTUARE UN ULTERIORE CONTROLLO PER VALUTARE SE SI È EFFETTIVAMENTE INSERITO QUALCOSA ALL'INTERNO DEI VARI CAMPI 
		if((strlen($_POST["nome"])==0)||(strlen($_POST["cognome"])==0)||(strlen($_POST["num_telefono"])==0)||(strlen($_POST["indirizzo"])==0)||(strlen($_POST["citta"])==0)||(strlen($_POST["cap"])==0)) {
			
			// IN CASO DI ERRORE, SI DOVRÀ INFORMARE L'UTENTE COINVOLTO DELLE MANCANZE CHE HA AVUTO NEI CONFRONTI DEL MODULO PER L'INSERIMENTO DELLA PROPOSTA DI VENDITA. UN SIMILE MECCANISMO VERRÀ IMPLEMENTATO PER TUTTE LE CASISITCHE IN CUI SARÀ IMPOSSIBILE PROCEDERE  
			$campi_vuoti=true;
		}
		else {
			// PRIMA DI PROCEDERE OLTRE, BISOGNA EFFETTUARE DEI CONTROLLI PRELIMINARI PER VALUTARE SE UN DETERMINATO VALORE ECCEDE LA DIMENSIONE INDICATA
			if(strlen($_POST["nome"])>30) {
				
				// ***
				$superamento_nome=true;
			}
			
			if(strlen($_POST["cognome"])>35) {
				
				// ***
				$superamento_cognome=true;
			}
			
			if(strlen($_POST["num_telefono"])>10) {
				
				// ***
				$superamento_recapito=true;
			}
			
			if(strlen($_POST["indirizzo"])>60) {
				
				// ***
				$superamento_indirizzo=true;
			}
			
			if(strlen($_POST["citta"])>40) {
				
				// ***
				$superamento_citta=true;
			}
			
			if(strlen($_POST["cap"])>5) {
				
				// ***
				$superamento_cap=true;
			}
			
			// SE LE VERIFICHE DI CUI SOPRA NON HANNO INDIVIDUATO ALCUNA SORTA DI PROBLEMATICA, ALLORA È POSSIBILE PROCEDERE CON LE OPERAZIONI RESTANTI
			if(!(isset($superamento_nome) && $superamento_nome) && !(isset($superamento_cognome) && $superamento_cognome) && !(isset($superamento_recapito) && $superamento_recapito) && !(isset($superamento_indirizzo) && $superamento_indirizzo) && !(isset($superamento_citta) && $superamento_citta) && !(isset($superamento_cap) && $superamento_cap)) {
				// INOLTRE, PER QUESTIONI DI PRESENTAZIONE DELLE VARIE INFORMAZIONI, VERRÀ APPLICATA UN'OPPORTUNA FORMATTAZIONE PER IL NOMINATIVO DEL CLIENTE COIVNOLTO. NEL DETTAGLIO, SOLTANTO LA PRIMA LETTERA DEL NOME E DEL COGNOME DOVRÀ ESSERE MAIUSCOLA
				$_POST["nome"]=strtolower($_POST["nome"]);
				$_POST["cognome"]=strtolower($_POST["cognome"]);
				
				$_POST["nome"]=ucfirst($_POST["nome"]);
				$_POST["cognome"]=ucfirst($_POST["cognome"]);
				
				// UNA VOLTA TERMINATE LE VERIFICHE PRELIMINARI, BISOGNERÀ PROCEDERE CON DEI CONTROLLI DISPOSTI A CASCATA, DUNQUE ANNIDATI, PER LA VERIFICA DEL FORMATO DEI VARI ELEMENTI COINVOLTI 
				// STANDO A COME È STATA CARATTERIZZATA LA PAGINA IN QUESTIONE, I PRIMI VALORI A DOVER RIPORTARE UNA COMPOSIZIONE BEN DELINEATA SONO PROPRIO IL RECAPITO TELEFONICO E IL CODICE DI AVVIAMENTO POSTALE FORNITI DALL'UTENTE 
				if(preg_match("/([[:digit:]]{10,10})/",$_POST["num_telefono"],$matches_recapito)){
					if($matches_recapito[0]!=$_POST["num_telefono"]) {
						
						// ***
						$recapito_errato=true;
					}
					else {
						if(preg_match("/([[:digit:]]{5,5})/",$_POST["cap"],$matches_cap)) {
							if($matches_cap[0]!=$_POST["cap"]) {
								
								// ***
								$cap_errato=true;
							}
							else {
								// DATO L'INTENTO DI VOLER CONFRONTARE L'ESITO DI UNA DETERMINATA QUERY, SARÀ NECESSARIO PREDISPORRE IL TUTTO ALL'INTERNO DI UN COSTRUTTO try ... catch ... AL FINE DI CATTURARE L'EVENTUALE ECCEZIONE E NOTIFICARE L'ACCADUTO ALL'UTENTE IN OGGETTO
								// INFATTI, UN POSSIBILE FALLIMENTO POTREBBE DIPENDERE DAL SUPERAMENTO DEL LIMITE DI CARATTERI CHE POSSONO ESSERE INSERITI ALL'INTERNO DI UN CAMPO DELLA TABELLA RELAZIONE COINVOLTA. SIMILMENTE, QUALORA CI SIA LA DUPLICAZIONE DEL CONTENUTO DEI CAMPI DEFINITI COME unique 
								try {
									// SE NON È STATA EVIDENZIATA ALCUNA SORTA DI PROBLEMATICA, È POSSIBILE EFFETTUARE L'ADEGUAMENTO DEI DATI ALL'INTERNO DELLA BASE DI DATI
									$sql="UPDATE $tab SET Nome='".$_POST["nome"]."', Cognome='".$_POST["cognome"]."', Num_Telefono='".$_POST["num_telefono"]."', Indirizzo='".$_POST["indirizzo"]."', Citta='".$_POST["citta"]."', Cap='".$_POST["cap"]."' WHERE ID=".$_SESSION["id_Utente"];
									
									// COME ACCENNATO, PRIMA DI CONCLUDERE L'OPERAZIONE BISOGNERÀ VALUTARE L'ESITO DELL'ESECUZIONE INERENTE AL PRECEDENTE COMANDO SQL
									if(mysqli_query($conn,$sql)){ 
										
										// PRIMA DI ESSERE REIDERIZZATI, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
										$_SESSION["modifica_Effettuata"]=true;
										
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
						}
						else {
							// ***
							$cap_errato=true;
						}
					}
				}
				else {
					// ***
					$recapito_errato=true;
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
									<img src="../../Immagini/arrows-rotate-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Aggiorna i tuoi dati anagrafici!</h2>
							</div>
						</div>
						<form class="corpo_form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Profilo Personale (Obbligatorio) <strong style="color: rgb(217, 118, 64);" title="ripetere o lasciare invariati i valori che si intende preservare">*</strong>
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Nome (max. 30 caratteri)
											</p>
											<p>
												<input type="text" name="nome" value="<?php if(isset($_POST['nome'])) echo $_POST['nome']; else echo $nome;?>"  />
											</p>	
										</div>
										<div class="campo">
											<p>
												Cognome (max. 35 caratteri)
											</p>
											<p>
												<input type="text" name="cognome" value="<?php if(isset($_POST['cognome'])) echo $_POST['cognome']; else echo $cognome;?>"  />
											</p>	
										</div>
										<div class="campo">
											<p>
												Recapito Telefonico
											</p>
											<p>
												<input type="text" name="num_telefono" value="<?php if(isset($_POST['num_telefono'])) echo $_POST['num_telefono']; else echo $num_telefono;?>"  />
											</p>										
										</div>
										<p class="nota"><strong>N.B.</strong> Il numero di telefono deve essere formato da una sequenza di 10 cifre.</p>
										<div class="campo">
											<p>
												Indirizzo (max. 60 caratteri)
											</p>
											<p>
												<input type="text" name="indirizzo" value="<?php if(isset($_POST['indirizzo'])) echo $_POST['indirizzo']; else echo $indirizzo;?>"  />
											</p>	
										</div>
										<div class="campo">
											<p>
												Citt&agrave; (max. 40 caratteri)
											</p>
											<p>
												<input type="text" name="citta" value="<?php if(isset($_POST['citta'])) echo $_POST['citta']; else echo $citta;?>"  />
											</p>	
										</div>
										<div class="campo">
											<p>
												CAP
											</p>
											<p>
												<input type="text" name="cap" value="<?php if(isset($_POST['cap'])) echo $_POST['cap']; else echo $cap;?>"  />
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> Il codice di avviamento postale deve essere formato da una sequenza di 5 cifre.</p>		
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