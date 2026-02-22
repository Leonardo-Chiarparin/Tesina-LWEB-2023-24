<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTE LE INFORMAZIONI INERENTI AL SALDO DEL PORTAFOGLIO DEI CLIENTI E DELLE TRANSAZIONI EFFETTUATE NEL CORSO DEL TEMPO

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
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO AI CLIENTI DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="C")
		header("Location: area_riservata.php");
	
	// COME PER I FILE XML, SI PROCEDE CON UN'INTERROGAZIONE ALLA BASE DI DATI SUDDETTA AL FINE DI REPERIRE I DATI RELATIVI AL PORTAFOGLIO CREDITI DELL'UTENTE SELEZIONATO PER AGEVOLARNE LA PROCEDURA DI MODIFICA  
	$sql="SELECT Portafoglio_Crediti FROM $tab WHERE ID=".$_SESSION["id_Utente"]; 
	$result=mysqli_query($conn, $sql);
	
	// NEL CASO IN CUI CI SIANO DELLE CORRISPONDENZE (IN REALTÀ UNA SOLTANTO), SI PROCEDE CON IL SALVATAGGIO DI TUTTI GLI ELEMENTI DI CUI SI È FATTA RICHIESTA
	while($row=mysqli_fetch_array($result))
		$portafoglio_crediti=$row["Portafoglio_Crediti"];
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE ALLE RICHIESTE DI RICARICA, È NECESSARIO FARE RIFERIMENTO AI CODICI PER APRIRE E DUNQUE INTERAGIRE CON I RELATIVI FILE XML 
	require_once("./apertura_file_richieste_crediti.php");
	
	// PER VALUTARE IL NUMERO DELLE DOMANDE A CUI L'AMMINISTRATORE NON HA ANCORA FORNITO UNA RISPOSTA, BISOGNERÀ SCANSIONARE IL DOCUMENTO IN CUI SONO CONTENUTE   
	// A TAL FINE, SARÀ NECESSARIO PREDISPORRE UN CONTATORE CHE, OLTRE AD ESSERE INIZIALIZZATO A ZERO, VERRÀ INCREMENTATO PER OGNI OFFERTA CHE SI RIFERISCE ALL'UTENTE IN QUEESTIONE
	$num_richieste=0;
	
	for($i=0; $i<$richieste->length; $i++){
		$richiesta=$richieste->item($i);
		
		if($richiesta->getAttribute("idRichiedente")==$_SESSION["id_Utente"])
			$num_richieste++;
	}
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>LEV: Libri &amp; Videogiochi</title>
		<link rel="icon" href="../../Immagini/Icona_LEV.png" />
		<link rel="stylesheet" href="../../Stili CSS/style_saldo_clienti.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_intestazione_sito.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_common.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_footer_sito.css" type="text/css" />
		<script type="text/javascript" src="../JavaScript/gestioneMenuTendina.js"></script>
	</head>
	<body>
		<?php
			// DATA LA VARIETÀ DI CASISTICHE CHE SI POSSONO MANIFESTARE, ABBIAMO DECISO DI DEFINIRE UNA GERARCHIA DI CONTROLLI PER GESTIRE AL MEGLIO LE STAMPE DEI VARI MESSAGGI DI POPUP, I QUALI, MEDIANTE UNA SIMILE IMPOSTAZIONE, SARANNO PRESENTATI SINGOLARMENTE AI SOGGETTI INTERESSATI
			if(isset($_SESSION["credito_Insufficiente"]) && $_SESSION["credito_Insufficiente"]) { 
				
				// IN OCCASIONE DEL POSSIBILE RICARICAMENTO DELLA PAGINA, SI PROCEDE CON LA RIMOZIONE DEL FLAG ALLO SCOPO DI NON RIPROPORRE LA MEDESIMA NOTIFCA 
				unset($_SESSION["credito_Insufficiente"]);
				
				setcookie("indenta_Intestazione", "", time()-60);
				
				echo "<div class=\"error_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
				echo "\t\t\t\t\t<p>CREDITO INSUFFICIENTE PER COMPLETARE L'ACQUISTO...</p>\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t</div>\n";
				echo "\t\t</div>\n";
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
									<img src="../../Immagini/vault-solid.svg" alt="Icona Saldo" />
								</span>
								<h2>Il mio saldo!</h2>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<div class="container_campo">
												<div class="credito">
													<div class="container_credito">
														<h3>Crediti:</h3>
														<h3><?php echo $portafoglio_crediti."<span style=\"color: rgb(255, 255, 255); padding-left: 0em; padding-right: 0em;\">/10000.00</span>"; ?></h3>
													</div>
												</div>
											</div>
										</div>
										<div class="campo">
											<div class="container_campo">
												<div class="pulsante">
													<form action="richiesta_crediti.php" method="post">
														<p>
															<button type="submit" class="container_pulsante" style="padding-left: 1.25em; padding-right: 1.25em;">Ricarica il saldo!</button>
														</p>
													</form>
												</div>
											</div>	
										</div>									
									</div>
								</div>
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Riepilogo Transazioni
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<div class="container_campo">
												<?php
													// IN BASE AL NUMERO DI RICHIESTE DA ESAMINARE, SARÀ POSSIBILE STABILIRE COSA PRESENTARE A SCHERMO
													if($num_richieste==0) {
														echo "<span>Non &egrave; stata effettuata nessuna richiesta per aumentare il proprio saldo.</span>\n";
													}
													else {
														// L'INTESTAZIONE DELLA TABELLA SARÀ UN FATTORE STATICO IN CUI VERRANNO PRESENTATI TUTTI GLI ELEMENTI, DEFINITI COME COLONNE, CHE LA COMPORRANNO 
														echo "<table>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<thead>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<tr>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Data &amp; Ora</th>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Importo</th>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Stato</th>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</tr>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t</thead>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<tbody>\n";
														
														// IL CORPO DELLA COMPONENTE DI CUI SOPRA VERRÀ COMPOSTO DINAMICAMENTE E SEGUENDO L'ORDINE INDOTTO DALLE PRECEDENTI STAMPE
														for($i=0; $i<$richieste->length; $i++){
															$richiesta=$richieste->item($i);
															
															if($richiesta->getAttribute("idRichiedente")==$_SESSION["id_Utente"]) {
																
																if($richiesta->getAttribute("stato")=="Rifiutata")
																	$stato="\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\" style=\"color: rgb(119, 119, 119);\">Rifiutata</td>\n";
																
																if($richiesta->getAttribute("stato")=="In Corso")
																	$stato="\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">In Corso</td>\n";
																
																if($richiesta->getAttribute("stato")=="Accettata")
																	$stato="\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\" style=\"color: rgb(217, 118, 64);\">Accettata</td>\n";
															
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<tr>\n";
																
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".date_format(date_create($richiesta->getAttribute("dataOraRichiesta")), "d/m/Y H:i:s")."</td>\n";
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$richiesta->getAttribute("numeroCrediti")."</td>\n";
																echo $stato;
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</tr>\n";
															}
														}
														echo "\t\t\t\t\t\t\t\t\t\t\t\t</tbody>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</table>\n";
													}
												?>
											</div>	
										</div>
									</div>
								</div>
								<div class="pulsante" style="justify-content: center;">
									<form action="area_riservata.php" method="post">
										<p>
											<button type="submit" class="container_pulsante back" style="margin-left: 1.5em; margin-right: 1.5em; padding: 0em; width: 9.35em; font-size: 1em;">Indietro!</button>
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