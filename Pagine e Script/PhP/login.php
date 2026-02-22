<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA DOVER COMPILARE PER POTER ACCEDERE A TUTTE LE FUNZIONALITÀ CONTENUTE ALL'INTERNO DELL'AREA RISERVATA DELLA PIATTAFORMA
	
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
	
	// UNA VOLTA CONFERMATE LE PROPRIE SCELTE, BISOGNA PROCEDERE CON I CONTROLLI PER VALUTARE LA CORRETTEZZA E L'INTEGRITÀ DEI VALORI INDICATI ALL'INTERNO DEI VARI CAMPI 
	if(isset($_POST["confirm"])) {
		
		// IL PRIMO PASSO CONSISTE NELLA RIMOZIONE DEI POSSIBILI SPAZI (BIANCHI) COLLOCATI ALL'INIZIO E ALLA FINE DEGLI ELEMENTI COINVOLTI
		$_POST["email_username"]=trim($_POST["email_username"]);
		$_POST["email_username"]=rtrim($_POST["email_username"]);
		
		$_POST["password"]=trim($_POST["password"]);
		$_POST["password"]=rtrim($_POST["password"]);
		
		// AL FINE DI GARANTIRE UN DISCRETO LIVELLO DI SICUREZZA, VERRANNO APPLICATI DEI CONTROLLI INERENTI AI CARATTERI CONTENUTI ALL'INTERNO DEI VARI CAMPI 
		$email_username=stripslashes($_POST["email_username"]); // RIMOZIONE DEI BACKSLASH \ ONDE EVITARE LA MySQL Injection
		$password=stripslashes($_POST["password"]);    // ***
		
		$email_username=mysqli_real_escape_string($conn, $email_username); // AGGIUNTA DELLA SEQUENZA DI ESCAPE AI CARATTERI SPECIALI COSÌ CHE LA STRINGA SIA USATA IN MODO SICURO NEI COMANDI mysqli_query 
		$password=mysqli_real_escape_string($conn, $password); // ***
		
		// PER PERMETTERE LA PROFILAZIONE DELL'UTENTE, BISOGNERÀ INDIVIDUARNE L'IDENTIFICATORE, IL QUALE TORNERÀ UTILE PER LA DISCRIMINAZIONE ED ESECUZIONE DELLE FUTURE OPERAZIONI, DELL'USERNAME, DEL TIPO, IN MODO TALE DA REINDIRIZZARLO CORRETTAMENTE TRA LE PAGINE, E DELLO STATO DEL SUO PROFILO
		$sql="SELECT ID, Username, Tipo_Utente, Ban FROM $tab WHERE (Email='$email_username' OR Username='$email_username') AND Password='$password'";
		$result=mysqli_query($conn, $sql);
		$conta=mysqli_num_rows($result);
		
		// NEL CASO IN CUI CI SIA UNA SINGOLA CORRISPONDENZA, SI PROCEDERÀ CON L'OTTENIMENTO DELLE VARIE INFORMAZIONI COINVOLTE, GRAZIE ALLE QUALI DETERMINARE SE L'UTENTE HA PERSO IL DIRITTO DI ACCESSO A CAUSA DEL SUO COMPORTAMENTO 
		if($conta==1){
		   while($row = mysqli_fetch_array($result)){
				$id=$row["ID"];
				$username=$row["Username"];
				$tipo=$row["Tipo_Utente"];
				$ban=$row["Ban"];
			}
			
			// SE L'UTENTE È LIBERO DI ACCEDERE ALLE FUNZIONALITÀ OFFERTE DALLA PIATTAFORMA, VERRÀ AVVIATA LA RELATIVA SESSIONE DI LAVORO PREDISPONENDO DELLE VARIABILI (INIZIALI) CHE TORNERANNO UTILI PER GLI SCOPI DI CUI SOPRA
			if($ban=="N") {
				
				session_start();
				$_SESSION["id_Utente"]=$id;
				$_SESSION["username_Utente"]=$username;
				$_SESSION["tipo_Utente"]=$tipo;
				
				// PRIMA DI ESSERE REINDIRIZZATI, BISOGNA VALUTARE SE L'UTENTE COINVOLTO, PRIMA DI AUTENTICARSI, HA INSERITO QUALCHE OFFERTA ALL'INTERNO DEL PROPRIO CARRELLO. INFATTI, SI HA LA NECESSITÀ DI INTEGRARE LE INFORMAZIONI ATTUALMENTE PRESENTI NEL RELATIVO FILE XML CON QUELLE CONTENUTE ALL'INTERNO DEL COOKIE DI INTERESSE 
				require_once("./integrazione_contenuto_carrello.php");
				
				// INOLTRE, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
				$_SESSION["accesso_Effettuato"]=true;
				
				header("Location: index.php");
			}
		}
	}
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>LEV: Libri &amp; Videogiochi</title>
		<link rel="icon" href="../../Immagini/Icona_LEV.png" />
		<link rel="stylesheet" href="../../Stili CSS/style_login.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_intestazione_sito.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_common.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_footer_sito.css" type="text/css" />
		<script type="text/javascript" src="../JavaScript/gestioneMenuTendina.js"></script>
	</head>
	<body>
		<?php
			// DATA LA VARIETÀ DI CASISTICHE CHE SI POSSONO MANIFESTARE, ABBIAMO DECISO DI DEFINIRE UNA GERARCHIA DI CONTROLLI PER GESTIRE AL MEGLIO LE STAMPE DEI VARI MESSAGGI DI POPUP, I QUALI, MEDIANTE UNA SIMILE IMPOSTAZIONE, SARANNO PRESENTATI SINGOLARMENTE AI SOGGETTI INTERESSATI
			if(isset($conta)) {
				// *** 
				if($conta==0) {
					
					echo "<div class=\"error_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
					echo "\t\t\t\t\t<p>LE CREDENZIALI INSERITE NON SONO CORRETTE...</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
				}
				else {
					// ***
					if(isset($ban) && $ban=="Y") {
						
						echo "<div class=\"error_message\">\n";
						echo "\t\t\t<div class=\"container_message\">\n";
						echo "\t\t\t\t<div class=\"container_img\">\n";
						echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t\t<div class=\"message\">\n";
						echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
						echo "\t\t\t\t\t<p>ACCESSO NON CONSENTITO PER SOSPENSIONE DEL PROFILO...</p>\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t</div>\n";
						echo "\t\t</div>\n";
					}
				}				
			}
			
			// ***
			if(isset($_COOKIE["profilo_Sospeso"]) && ($_COOKIE["profilo_Sospeso"])){
				
				// ***
				setcookie("profilo_Sospeso","",time()-60);
				
				echo "<div class=\"error_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
				echo "\t\t\t\t\t<p>IL TUO PROFILO &Egrave; STATO SOSPESO DALL'AMMINISTRATORE DEL SITO...</p>\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t</div>\n";
				echo "\t\t</div>\n";
			}
			
			// L'INTESTAZIONE DEL SITO, POICHÈ SI TRATTA DI UN DETTAGLIO RICORRENTE, È STATA DEFINITA ALL'INTERNO DI UN PROGRAMMA INDIPENDENTE CHE, DATE LE ESIGENZE, VERRÀ INCLUSO A PIÙ RIPRESE NEI VARI SCRIPT
			require_once ("./intestazione_sito.php");
		?>
		<div class="corpo_pagina">
			<div class="container_corpo_pagina">
				<div class="login">
					<div class="container_login">
						<div class="intestazione_login">
							<div class="container_intestazione_login">
								<span class="icona_login">
									<img src="../../Immagini/right-to-bracket-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Accedi al tuo account!</h2>
							</div>
						</div>
						<div class="corpo_login">
							<form class="container_corpo_login" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
								<div class="riga_form">
									<h3>Email o Username</h3>
									<div class="container_riga_form">
										<div class="container_intestazione_riga_form" title="Email o Username">
											<img src="../../Immagini/envelope-solid.svg" alt="Immagine Non Disponibile..." />
										</div>
										<input type="text" name="email_username" value="<?php if(isset($_POST['email_username'])) echo $_POST['email_username']; else echo '';?>" />
									</div>
								</div>
								<div class="riga_form">
									<h3>Password</h3>
									<div class="container_riga_form">
										<div class="container_intestazione_riga_form" title="Password">
											<img src="../../Immagini/lock-solid.svg" alt="Immagine Non Disponibile..." />
										</div>
										<input type="password" name="password" value="<?php if(isset($_POST['password'])) echo $_POST['password']; else echo '';?>" />
									</div>
								</div>
								<div class="riga_form" style="margin-bottom: 0.5em; margin-top: 0%;">
									<div class="container_riga_form" style="justify-content: center; border: none;">
										<p>
											<a href="resume_password.php">
												Hai dimenticato la password?
											</a>
										</p>
									</div>
								</div>
								<div class="pulsante_form">
									<div class="container_pulsante_form">
										<button type="submit" name="confirm">Accedi!</button>
									</div>
								</div>
								<div class="footer_form">
									<div class="container_footer_form">
										<p>
											Non hai un account?
											<a href="registrazione.php">Registrati!</a>
										</p>
									</div>
								</div>
							</form>
						</div>
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