<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA DOVER COMPILARE PER POTERSI REGISTRARE ALLA PIATTAFORMA
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
	if(isset($_POST["confirm"])) {
		require_once("./gestione_meccanismo_registrazione.php");
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
									<img src="../../Immagini/signature-solid.svg" alt="Icona Registrazione" />
								</span>
								<h2>Crea un account!</h2>
							</div>
						</div>
						<form class="corpo_form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">Profilo Personale (Obbligatorio)</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Nome (max. 30 caratteri)
											</p>
											<p>
												<input type="text" name="nome" value="<?php if(isset($_POST['nome'])) echo $_POST['nome']; else echo '';?>"  />
											</p>	
										</div>
										<div class="campo">
											<p>
												Cognome (max. 35 caratteri)
											</p>
											<p>
												<input type="text" name="cognome" value="<?php if(isset($_POST['cognome'])) echo $_POST['cognome']; else echo '';?>"  />
											</p>	
										</div>
										<div class="campo">
											<p>
												Recapito Telefonico
											</p>
											<p>
												<input type="text" name="num_telefono" value="<?php if(isset($_POST['num_telefono'])) echo $_POST['num_telefono']; else echo '';?>"  />
											</p>										
										</div>
										<p class="nota"><strong>N.B.</strong> Il numero di telefono deve essere formato da una sequenza di 10 cifre.</p>
										<div class="campo">
											<p>
												Indirizzo (max. 60 caratteri)
											</p>
											<p>
												<input type="text" name="indirizzo" value="<?php if(isset($_POST['indirizzo'])) echo $_POST['indirizzo']; else echo '';?>"  />
											</p>	
										</div>
										<div class="campo">
											<p>
												Citt&agrave; (max. 40 caratteri)
											</p>
											<p>
												<input type="text" name="citta" value="<?php if(isset($_POST['citta'])) echo $_POST['citta']; else echo '';?>"  />
											</p>	
										</div>
										<div class="campo">
											<p>
												CAP
											</p>
											<p>
												<input type="text" name="cap" value="<?php if(isset($_POST['cap'])) echo $_POST['cap']; else echo '';?>"  />
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> Il codice di avviamento postale deve essere formato da una sequenza di 5 cifre.</p>		
									</div>
								</div>
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">Profilo Utente (Obbligatorio)</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Username (max. 30 caratteri)
											</p>
											<p>
												<input type="text" name="username" value="<?php if(isset($_POST['username'])) echo $_POST['username']; else echo '';?>"  />
											</p>	
										</div>
										<div class="campo">
											<p>
												Email (max. 35 caratteri)
											</p>
											<p>
												<input type="text" name="email" value="<?php if(isset($_POST['email'])) echo $_POST['email']; else echo '';?>"  />
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> L'indirizzo di posta elettronica dovr&agrave; rispettare il formato classico: example@example.dom, con la lunghezza del dominio pari a 2 o a 3.</p>
										<div class="campo">
											<p>
												Password
											</p>
											<p>
												<input type="password" name="password" value="<?php if(isset($_POST['password'])) echo $_POST['password']; else echo '';?>"  />
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