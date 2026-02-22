<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTE LE INFORMAZIONI INERENTI ALL'UTENTE COINVOLTO
	
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
	
	// COME PER I FILE XML, SI PROCEDE CON UN'INTERROGAZIONE ALLA BASE DI DATI SUDDETTA AL FINE DI REPERIRE TUTTI I DATI RELATIVI ALL'UTENTE IN QUESTIONE  
	$sql="SELECT Nome, Cognome, Num_Telefono, Email, Username, Password, Indirizzo, Citta, CAP, Tipo_Utente, DATE_FORMAT(Data_Registrazione, '%d/%m/%Y') AS Data_Registrazione, Reputazione FROM $tab WHERE ID=".$_SESSION["id_Utente"]; 
	$result=mysqli_query($conn, $sql);
	
	// NEL CASO IN CUI CI SIANO DELLE CORRISPONDENZE (IN REALTÀ UNA SOLTANTO), SI PROCEDE CON IL SALVATAGGIO DI TUTTI GLI ELEMENTI DI CUI SI È FATTA RICHIESTA
	while($row=mysqli_fetch_array($result)){
		$nome=$row["Nome"];
		$cognome=$row["Cognome"];
		$num_telefono=$row["Num_Telefono"];
		$email=$row["Email"];
		$username=$row["Username"];
		$password=$row["Password"];
		$indirizzo=$row["Indirizzo"];
		$citta=$row["Citta"];
		$cap=$row["CAP"];
		
		// PER QUESTIONI DI GODIBILITÀ DELLA STAMPA, SI PROCEDE CON L'ESTENSIONE DELL'ACRONIMO INERENTE ALLA TIPOLOGIA DI UTENTE E PRELEVATO DALLA ENTRY INTERESSATA
		$tipo_utente=$row["Tipo_Utente"];
		
		if($tipo_utente=="C")
			$tipo_utente="Cliente";
		
		if($tipo_utente=="G")
			$tipo_utente="Gestore";
		
		if($tipo_utente=="A")
			$tipo_utente="Amministratore";
		
		$data_registrazione=$row["Data_Registrazione"];
		$reputazione=$row["Reputazione"];
		
		// PER DI PIÙ, SI EFFETTUA LA TRADUZIONE DELLA REPUTAZIONE DA PUNTI A TITOLO, RIPORTANDO COMUNQUE IL LORO AMMONTARE ATTUALE
		if($reputazione<33) {
			$reputazione="Pessima (".$reputazione." punti)";
		}		
		else {
			if($reputazione<66 && $reputazione>=33) {
				$reputazione="Rispettabile (".$reputazione." punti)";
			}
			else {
				$reputazione="Virtuosa (".$reputazione." punti)";
			}
		}
	}
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>LEV: Libri &amp; Videogiochi</title>
		<link rel="icon" href="../../Immagini/Icona_LEV.png" />
		<link rel="stylesheet" href="../../Stili CSS/style_profilo.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_intestazione_sito.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_common.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_footer_sito.css" type="text/css" />
		<script type="text/javascript" src="../JavaScript/gestioneMenuTendina.js"></script>
	</head>
	<body>
		<?php
			// L'INTESTAZIONE DEL SITO, POICHÈ SI TRATTA DI UN DETTAGLIO RICORRENTE, È STATA DEFINITA ALL'INTERNO DI UN PROGRAMMA INDIPENDENTE CHE, DATE LE ESIGENZE, VERRÀ INCLUSO A PIÙ RIPRESE NEI VARI SCRIPT
			require_once("./intestazione_sito.php");
		?>
		<div class="corpo_pagina">
			<div class="container_corpo_pagina">
				<div class="profilo">
					<div class="container_profilo">
						<div class="intestazione_profilo">
							<div class="container_intestazione_profilo">
								<span class="icona_profilo">
									<img src="../../Immagini/address-card-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Il mio profilo!</h2>
							</div>
						</div>
						<div class="corpo_profilo">
							<div class="container_corpo_profilo">
								<div class="intestazione_sezione">
									<div class="container_intestazione_sezione">
										Scheda <?php echo $tipo_utente."\n"; ?>
									</div>
								</div>
								<div class="campo">
									<div class="container_campo">
										<div class="oggetto_campo">
											<div class="container_oggetto_campo">
												<div class="intestazione_oggetto_campo">
													<div class="container_intestazione_oggetto_campo">
														<img alt="Immagine Non Disponibile..." src="../../Immagini/user-solid.svg" />
													</div>
												</div>
												<div class="corpo_oggetto_campo">
													<span>
														<strong><?php echo $nome." ".$cognome; ?></strong>
													</span>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="intestazione_sezione">
									<div class="container_intestazione_sezione">
										<span>
											Profilo Personale <strong style="color: rgb(217, 118, 64);" title="modificabile tramite il pulsante di cui sotto">*</strong>
										</span>
									</div>
								</div>
								<div class="campo">
									<div class="container_campo">
										<div class="oggetto_campo" style="margin-bottom: 2.5%;">
											<p>
												Nome
											</p>
											<p>
												<input type="text" value="<?php echo $nome; ?>" disabled="disabled" />
											</p>	
										</div>
										<div class="oggetto_campo" style="margin-bottom: 2.5%;">
											<p>
												Cognome
											</p>
											<p>
												<input type="text" value="<?php echo $cognome; ?>" disabled="disabled" />
											</p>	
										</div>
										<div class="oggetto_campo" style="margin-bottom: 2.5%;">
											<p>
												Recapito Telefonico
											</p>
											<p>
												<input type="text" value="<?php echo $num_telefono; ?>" disabled="disabled" />
											</p>	
										</div>
										<div class="oggetto_campo" style="margin-bottom: 2.5%;">
											<p>
												Indirizzo
											</p>
											<p>
												<input type="text" value="<?php echo $indirizzo; ?>" disabled="disabled" />
											</p>	
										</div>
										<div class="oggetto_campo" style="margin-bottom: 2.5%;">
											<p>
												Citt&agrave;
											</p>
											<p>
												<input type="text" value="<?php echo $citta; ?>" disabled="disabled" />
											</p>	
										</div>
										<div class="oggetto_campo" style="margin-bottom: 2.5%;">
											<p>
												CAP
											</p>
											<p>
												<input type="text" value="<?php echo $cap; ?>" disabled="disabled" />
											</p>	
										</div>
									</div>
								</div>
								<div class="intestazione_sezione">
									<div class="container_intestazione_sezione">
										<span>
											Profilo Utente <strong style="color: rgb(119, 119, 119);" title="NON modificabile tramite il pulsante di cui sotto">*</strong>
										</span>
									</div>
								</div>
								<div class="campo">
									<div class="container_campo">
										<div class="oggetto_campo" style="margin-bottom: 2.5%;">
											<p>
												Username
											</p>
											<p>
												<input type="text" value="<?php echo $username; ?>" disabled="disabled" />
											</p>	
										</div>
										<div class="oggetto_campo" style="margin-bottom: 2.5%;">
											<p>
												Email
											</p>
											<p>
												<input type="text" value="<?php echo $email; ?>" disabled="disabled" />
											</p>	
										</div>
										<div class="oggetto_campo" style="margin-bottom: 2.5%;">
											<p>
												Password
											</p>
											<p>
												<input type="password" value="<?php echo $password; ?>" disabled="disabled" />
											</p>	
										</div>
										<div class="oggetto_campo" style="margin-bottom: 2.5%;">
											<p>
												Reputazione
											</p>
											<p>
												<input type="text" value="<?php echo $reputazione; ?>" disabled="disabled" />
											</p>	
										</div>
										<div class="oggetto_campo" style="margin-bottom: 2.5%;">
											<p>
												Data di Registrazione
											</p>
											<p>
												<input type="text" value="<?php echo $data_registrazione; ?>" disabled="disabled" />
											</p>	
										</div>
									</div>
								</div>
								<div class="pulsante">
									<form action="area_riservata.php" method="post">
										<p>
											<button type="submit" class="container_pulsante back">Indietro!</button>
										</p>
									</form>
									<form action="modifica_dati_anagrafici.php" method="post">
										<p>
											<button type="submit" class="container_pulsante">Modifica!</button>
										</p>
									</form>
								</div>
							</div>
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