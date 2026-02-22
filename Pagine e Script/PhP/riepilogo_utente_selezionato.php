<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTE LE INFORMAZIONI INERENTI ALL'UTENTE SELEZIONATO

	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");

	// PER QUESTIONI DI SICUREZZA, È STATO NECESSARIO L'INSERIMENTO DELLO SCRIPT "private_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA. QUESTO MECCANISMO NON PERMETTERÀ L'ACCESSO AGLI UTENTI CHE NON SI AUTENTICATI TRAMITE IL RELATIVO SERVIZIO
	require_once("./private_session_control.php");
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO AI GESTORI E ALL'AMMINISTRATORE DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]=="C") {
		header("Location: area_riservata.php");
	}
	
	// LA PAGINA, ESSENDO RAGGIUNGIBILE DOPO AVER SELEZIONATO UN DETERMINATO UTENTE DALLA RELATIVA PAGINA DI RIEPILOGO, NECESSITA DI UN ULTERIORE CONTROLLO PER APPURARE SE È STATA EFFETTIVAMENTE PRESA UNA DECISIONE IN MERITO O MENO 
	if(!isset($_GET["id_Utente_Selezionato"]))
		header("Location: riepilogo_utenti.php");
	
	// COME PER I FILE XML, SI PROCEDE CON UN'INTERROGAZIONE ALLA BASE DI DATI SUDDETTA AL FINE DI REPERIRE I DATI RELATIVI ALL'UTENTE SELEZIONATO PER PERMETTERNE LA VISUALIZZAZIONE DELLE VARIE INFORMAZIONI 
	$sql="SELECT Nome, Cognome, Num_Telefono, Email, Username, Password, Indirizzo, Citta, CAP, Tipo_Utente, DATE_FORMAT(Data_Registrazione, '%d/%m/%Y') AS Data_Registrazione, Reputazione FROM $tab WHERE ID=".$_GET["id_Utente_Selezionato"]; 
	$result=mysqli_query($conn, $sql);
	
	// QUALORA CI SIA STATA UN'ALTERAZIONE DELL'IDENTIFICATORE RELATIVO ALL'UTENTE SELEZIONATO, SI PROCEDE AL REINDIRIZZAMENTE VERSO LA SCHERMATA DI RIEPILOGO DEI VARI UTENTI
	if(mysqli_num_rows($result)==0)
		header("Location: riepilogo_utenti.php");
	
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
		$data_registrazione=$row["Data_Registrazione"];
		$reputazione=$row["Reputazione"];
		
		// PER QUESTIONI DI GODIBILITÀ DELLA STAMPA, SI PROCEDE CON L'ESTENSIONE DELL'ACRONIMO INERENTE ALLA TIPOLOGIA DI UTENTE E PRELEVATO DALLA ENTRY INTERESSATA
		$tipo_utente=$row["Tipo_Utente"];
		
		if($tipo_utente=="C") {
			$tipo_utente="Cliente";
			
			// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
			require_once("./apertura_file_segnalazioni.php");
			require_once("./apertura_file_acquisti.php");
			require_once("./apertura_file_riduzioni.php");
			require_once("./apertura_file_tariffe.php");
			
			// SE L'UTENTE SELEZIONATO RISULTA ESSERE UN CLIENTE, SARÀ POSSIBILE STAMPARNE ANCHE IL CREDITO RESIDUO E IL NUMERO DI ANNI PASSATI DA QUANDO HA EFFETTUATO LA REGISTRAZIONE AL SITO
			$sql="SELECT Portafoglio_Crediti, YEAR(CURDATE())-YEAR(Data_Registrazione) AS Num_Anni FROM $tab WHERE ID=".$_GET["id_Utente_Selezionato"];
			$result=mysqli_query($conn, $sql);
			
			while($row=mysqli_fetch_array($result)) {
				$saldo_portafoglio=$row["Portafoglio_Crediti"]." Cr.";
				$num_anni=$row["Num_Anni"];
			}
			
			// GIUNTI A QUESTO PUNTO, SI PROCEDE CON IL CALCOLO DEL NUMERO DI SEGNALAZIONI E ACQUISTI CHE SI RIFERISCONO AL SOGGETTO IN ESAME
			$num_segnalazioni = $num_acquisti = 0;
			
			for($i=0; $i<$segnalazioni->length; $i++) {
				$segnalazione=$segnalazioni->item($i);
				
				if($segnalazione->getAttribute("idSegnalato")==$_GET["id_Utente_Selezionato"])
					$num_segnalazioni=$num_segnalazioni+1;
			}
			
			for($i=0; $i<$acquisti->length; $i++) {
				$acquistiPerCliente=$acquisti->item($i);
				
				if($acquistiPerCliente->getAttribute("idCliente")==$_GET["id_Utente_Selezionato"]){
					$num_acquisti=$acquistiPerCliente->getAttribute("ultimoIdPerAcquisto");
					break;
				}
			}
			
			// INOLTRE, ABBIAMO DECISO DI MOSTRARE UN BREVE RIEPILOGO INERENTE ALLO STATO DELLE SINGOLE RIDUZIONI DI PREZZO A CUI IL CLIENTE HA ACCESSO O MENO
			for($i=0; $i<$riduzioni->length; $i++) {
				$riduzione=$riduzioni->item($i);
				
				if($riduzione->getAttribute("idCliente")==$_GET["id_Utente_Selezionato"])
					break;
			}
			
			// 1) SCONTO A SOGLIA
			if($riduzione->getElementsByTagName("aSoglia")->item(0)->getAttribute("fruibile")==1)
				$aSoglia=number_format($rootTariffe->getElementsByTagName("tariffaASoglia")->item(0)->getAttribute("basePercentuale"), 2,".","")." % (".number_format($riduzione->getElementsByTagName("aSoglia")->item(0)->getAttribute("creditiSpesi")-(intval($rootTariffe->getElementsByTagName("tariffaASoglia")->item(0)->getAttribute("soglia"))*(intval($riduzione->getElementsByTagName("aSoglia")->item(0)->getAttribute("superamenti"))-1)), 2,".","")."/".number_format(intval($rootTariffe->getElementsByTagName("tariffaASoglia")->item(0)->getAttribute("soglia")), 2,".","")." Cr. spesi)";
			else
				$aSoglia="No... (".number_format($riduzione->getElementsByTagName("aSoglia")->item(0)->getAttribute("creditiSpesi")-(intval($rootTariffe->getElementsByTagName("tariffaASoglia")->item(0)->getAttribute("soglia"))*intval($riduzione->getElementsByTagName("aSoglia")->item(0)->getAttribute("superamenti"))), 2,".","")."/".number_format(intval($rootTariffe->getElementsByTagName("tariffaASoglia")->item(0)->getAttribute("soglia")), 2,".","")." Cr. spesi)";
			
			// 2) SCONTO FEDELTÀ ELITÈ
			if($riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("fruibile")==1 && $riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("esercitabile")==1) 
				$fedeltaElite=number_format($rootTariffe->getElementsByTagName("tariffaFedeltaElite")->item(0)->getAttribute("basePercentuale"), 2,".","")." % (".number_format($riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("creditiSpesi"), 2,".","")."/".number_format(intval($rootTariffe->getElementsByTagName("tariffaFedeltaElite")->item(0)->getAttribute("soglia")), 2,".","")." Cr. spesi dal ".date_format(date_create($rootTariffe->getElementsByTagName("tariffaFedeltaElite")->item(0)->getAttribute("dataPerControllo")), "d/m/Y").")";
			else {
				if($riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("fruibile")==0 && $riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("esercitabile")==1) {
					$fedeltaElite="No... (".number_format($riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("creditiSpesi"), 2,".","")."/".number_format(intval($rootTariffe->getElementsByTagName("tariffaFedeltaElite")->item(0)->getAttribute("soglia")), 2,".","")." Cr. spesi dal ".date_format(date_create($rootTariffe->getElementsByTagName("tariffaFedeltaElite")->item(0)->getAttribute("dataPerControllo")), "d/m/Y").")";
				}
				else {
					$fedeltaElite="No, poich&egrave; &egrave; gi&agrave; stato applicato...";
				}
			}
			
			// 3) PROMOZIONI
			$acquistoPromozionale=number_format(floatval($riduzione->getElementsByTagName("acquistoPromozionale")->item(0)->firstChild->textContent), 2,".","")." %";
			
			// 4) SCONTO PER VIP
			if($riduzione->getElementsByTagName("perVIP")->item(0)->getAttribute("fruibile")==1)
				$scontoPerVip=number_format($rootTariffe->getElementsByTagName("tariffaPerVIP")->item(0)->getAttribute("basePercentuale"), 2,".","")." % (".$reputazione."/".$rootTariffe->getElementsByTagName("tariffaPerVIP")->item(0)->getAttribute("sogliaReputazione")." Punti Reputazione)";
			else
				$scontoPerVip="No... (".$reputazione."/".$rootTariffe->getElementsByTagName("tariffaPerVIP")->item(0)->getAttribute("sogliaReputazione")." Punti Reputazione)";
			
			// 5) SCONTO DI ANZIANITÀ
			$scontoDiAnzianita=number_format(floatval($riduzione->getElementsByTagName("diAnzianita")->item(0)->firstChild->textContent), 2,".","")." % (Cliente da ".$num_anni." anni)";
			
		}
		
		if($tipo_utente=="G")
			$tipo_utente="Gestore";
		
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
								<?php 
									if($_GET["id_Utente_Selezionato"]==$_SESSION["id_Utente"])
										echo "<h2>Il mio profilo!</h2>";
									else 
										echo "<h2>Profilo selezionato!</h2>";
								?>
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
														<img alt="Logo Utente" src="../../Immagini/user-solid.svg" />
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
											<?php
												// NEL CASO IN CUI L'UTENTE AUTENTICATO RISULTI ESSERE UN GESTORE, NON DOVRÀ VISUALIZZARE IL MESSAGGIO INERENTE ALLA POSSIBILE MODIFICA DEI DATI ANAGRAFICI DELL'UTENTE SELEZIONATO
												if($_SESSION["tipo_Utente"]=="G")
													echo "Profilo Personale\n";
												else
													echo "Profilo Personale <strong style=\"color: rgb(217, 118, 64);\" title=\"modificabile tramite il pulsante di cui sotto\">*</strong>\n";
											?>
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
											<?php
												// ***
												if($_SESSION["tipo_Utente"]=="G")
													echo "Profilo Utente\n";
												else
													echo "Profilo Utente <strong style=\"color: rgb(217, 118, 64);\" title=\"modificabile tramite il pulsante di cui sotto\">*</strong>\n";
											?>
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
										<?php
											// SE L'UTENTE SELEZIONATO CORRISPONDE AD UN CLIENTE DELLA PIATTAFORMA, SARÀ NECESSARIO RIPORTARE TUTTI I DETTAGLI INERENTI AL NUMERO DI SEGNALAZIONI RICEVUTE E ACQUISTI EFFETTUATI
											if($tipo_utente=="Cliente") {
												echo "<div class=\"oggetto_campo\" style=\"margin-bottom: 2.5%;\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\tAcquisti Effettuati\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" value=\"$num_acquisti\" disabled=\"disabled\" />\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t\t<div class=\"oggetto_campo\" style=\"margin-bottom: 2.5%;\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\tSegnalazioni Ricevute\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" value=\"$num_segnalazioni\" disabled=\"disabled\" />\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t</div>\n";
												
												echo "\t\t\t\t\t\t\t<div class=\"intestazione_sezione\">\n";
												echo "\t\t\t\t\t\t\t\t<div class=\"container_intestazione_sezione\">\n";
												echo "\t\t\t\t\t\t\t\t\t<span>\n";
												echo "\t\t\t\t\t\t\t\t\t\tProfilo Economico\n";
												echo "\t\t\t\t\t\t\t\t\t</span>\n";
												echo "\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t<div class=\"campo\">\n";
												echo "\t\t\t\t\t\t\t\t<div class=\"container_campo\">\n";
												echo "\t\t\t\t\t\t\t\t\t<div class=\"oggetto_campo\" style=\"margin-bottom: 2.5%;\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\tSaldo Portafoglio\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" value=\"$saldo_portafoglio\" disabled=\"disabled\" />\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t\t<div class=\"oggetto_campo\" style=\"margin-bottom: 2.5%;\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\tSconto a Soglia\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" value=\"$aSoglia\" disabled=\"disabled\" />\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t\t<div class=\"oggetto_campo\" style=\"margin-bottom: 2.5%;\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\tSconto Fedelt&agrave; Elit&egrave;\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" value=\"$fedeltaElite\" disabled=\"disabled\" />\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t\t<div class=\"oggetto_campo\" style=\"margin-bottom: 2.5%;\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\tPromozioni\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" value=\"$acquistoPromozionale\" disabled=\"disabled\" />\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t\t<div class=\"oggetto_campo\" style=\"margin-bottom: 2.5%;\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\tSconto per VIP\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" value=\"$scontoPerVip\" disabled=\"disabled\" />\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t\t<div class=\"oggetto_campo\" style=\"margin-bottom: 2.5%;\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\tSconto di Anzianit&agrave;\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" value=\"$scontoDiAnzianita\" disabled=\"disabled\" />\n";
												echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
												echo "\t\t\t\t\t\t\t\t\t</div>\n";
											}
										?>
									</div>
								</div>
								<div class="pulsante">
									<form action="riepilogo_utenti.php" method="post">
										<p>
											<button type="submit" class="container_pulsante back">Indietro!</button>
										</p>
									</form>
									<?php
										// POICHÈ LA PAGINA RISULTA COMUNE AI GESTORI E ALL'AMMINISTRATORE, È STATO NECESSARIO FILTRARE IL CONTENUTO DA FAR VISUALIZZARE A SCHERMO IN RELAZIONE ALLA FUNZIONALITÀ OFFERTA LORO
										if($_SESSION["tipo_Utente"]=="A") {
											echo "<form action=\"modifica_dati_utente_selezionato.php\" method=\"get\">\n";
											echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
											echo "\t\t\t\t\t\t\t\t\t\t\t<input type=\"hidden\" name=\"id_Utente_Selezionato\" value=\"".$_GET["id_Utente_Selezionato"]."\" />\n";
											echo "\t\t\t\t\t\t\t\t\t\t\t<button type=\"submit\" class=\"container_pulsante\">Modifica!</button>\n";
											echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
											echo "\t\t\t\t\t\t\t\t\t</form>\n";
										}
										else {
											echo "<form action=\"riepilogo_acquisti_utente_selezionato.php\" method=\"get\">\n";
											echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
											echo "\t\t\t\t\t\t\t\t\t\t\t<input type=\"hidden\" name=\"id_Utente_Selezionato\" value=\"".$_GET["id_Utente_Selezionato"]."\" />\n";
											echo "\t\t\t\t\t\t\t\t\t\t\t<button type=\"submit\" class=\"container_pulsante\">Storico!</button>\n";
											echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
											echo "\t\t\t\t\t\t\t\t\t</form>\n";
										}
									?>
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