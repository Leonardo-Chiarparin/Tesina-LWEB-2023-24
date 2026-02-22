<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTE LE SEGNALAZIONI INVIATE DAI VARI CLIENTI, LE QUALI NON SONO ANCORA STATE CONSIDERATE DALLE FIGURE DI SPICCO DELLA PIATTAFORMA

	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");

	// PER QUESTIONI DI SICUREZZA, È NECESSARIO L'INSERIMENTO DELLO SCRIPT "private_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA. QUESTO MECCANISMO NON PERMETTERÀ L'ACCESSO AGLI UTENTI CHE NON SI AUTENTICATI TRAMITE IL RELATIVO SERVIZIO
	require_once("./private_session_control.php");
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO AI GESTORI E ALL'AMMINISTRATORE DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]=="C") {
		header("Location: area_riservata.php");
	}
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_segnalazioni.php");
	
	// PRIMA DI PROCEDERE CON LA COMPOSIZIONE DELLA PAGINA, È NECESSARIO VALUTARE IL NUMERO DI SEGNALAZIONI NON ANCORA PROCESSATE. INFATTI, QUALORA SIANO GIÀ STATE CONSIDERATE, SI DOVRÀ STAMPARE UN MESSAGGIO CHE NOTIFICHERÀ L'ASSENZA DI ULTERIORI NOTIFICHE IN SOSPESO  
	require_once("./calcolo_segnalazioni.php");
	
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
			// L'INTESTAZIONE DEL SITO, POICHÈ SI TRATTA DI UN DETTAGLIO RICORRENTE, È STATA DEFINITA ALL'INTERNO DI UN PROGRAMMA INDIPENDENTE CHE, DATE LE ESIGENZE, VERRÀ INCLUSO A PIÙ RIPRESE NEI VARI SCRIPT
			require_once ("./intestazione_sito.php");
		?>
		<div class="corpo_pagina">
			<div class="container_corpo_pagina">
				<div class="form">
					<div class="container_form">
						<div class="intestazione_form">
							<div class="container_intestazione_form">
								<span class="icona_form">
									<img src="../../Immagini/crosshairs-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Occupati delle segnalazioni ricevute!</h2>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<?php
									// IN BASE AL NUMERO DI SEGNALAZIONI DA ESAMINARE, SARÀ POSSIBILE STABILIRE COSA PRESENTARE A SCHERMO
									if($num_segnalazioni==0) {
										echo "<span class=\"nessun_elemento\">Non &egrave; stato trovato nessun elemento con le specifiche di interesse.</span>\n";
									}
									else {
										// L'INTESTAZIONE DELLA TABELLA SARÀ UN FATTORE STATICO IN CUI VERRANNO PRESENTATI TUTTI GLI ELEMENTI, DEFINITI COME COLONNE, CHE LA COMPONGONO 
										echo "<table>\n";
										echo "\t\t\t\t\t\t\t\t<thead>\n";
										echo "\t\t\t\t\t\t\t\t\t<tr>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Segnalazione</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Mittente</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Destinatario</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Contributo</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Categoria</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Azione</th>\n";
										echo "\t\t\t\t\t\t\t\t\t</tr>\n";
										echo "\t\t\t\t\t\t\t\t</thead>\n";
										echo "\t\t\t\t\t\t\t\t<tbody>\n";
										
										// IL CORPO DELLA COMPONENTE DI CUI SOPRA VERRÀ COMPOSTO DINAMICAMENTE E SEGUENDO L'ORDINE INDOTTO DALLE PRECEDENTI STAMPE
										for($i=0; $i<$segnalazioni->length; $i++){
											$segnalazione=$segnalazioni->item($i);
											
											if($segnalazione->getAttribute("seen")=="No") {
												
												// PER CIASCUNA SEGNALAZIONE, VERRANNO RIPORTATI L'USERNAME DELL'UTENTE CHE L'HA INOLTRATA E DI CHI È STATO SEGNALATO A CAUSA DEL CONTENUTO DI UN SUO CONTRIBUTO
												$sql="SELECT U1.Username AS Username_Segnalato, U2.Username AS Username_Segnalatore FROM $tab U1, $tab U2 WHERE U1.ID=".$segnalazione->getAttribute("idSegnalato")." AND U2.ID=".$segnalazione->getAttribute("idSegnalatore")." AND U1.Tipo_Utente='C' AND U2.Tipo_Utente='C'";
												$result=mysqli_query($conn, $sql);
			
												while($row=mysqli_fetch_array($result)) {
													$username_segnalato=$row["Username_Segnalato"];
													$username_segnalatore=$row["Username_Segnalatore"];
												}
												
												echo "\t\t\t\t\t\t\t\t\t<tr>\n"; 
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$segnalazione->getAttribute("id")."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$username_segnalatore."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$username_segnalato."</td>\n";
												
												// PER UNA STAMPA OTTIMALE E COMPLETA DEI VARI DETTAGLI D'INTERESSE, ABBIAMO DECISO DI EFFETTUARE UNA SERIE DI CONFRONTI PER DETERMINARE A QUALE TIPOLOGIA APPARTIENE IL CONTRIBUTO COINVOLTO NELLA SEGNALAZIONE SELEZIONATA
												if($segnalazione->getElementsByTagName("perRecensione")->length!=0) 
													echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">Recensione</td>\n";
												else {
													if($segnalazione->getElementsByTagName("perDiscussione")->length!=0)
														echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">Discussione</td>\n";
													else
														echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">Intervento</td>\n";
												}
												
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$segnalazione->getAttribute("categoria")."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">\n";
												
												// ALLO SCOPO DI OTTENERE CORRETTAMENTE LE INFORMAZIONI DI UNA CERTA SEGNALAZIONE, È STATO NECESSARIO FORZARE IL REINDIRIZZAMENTO AD UN'ALTRA PAGINA CON LA SPECIFICA "MANUALE" DELL'IDENTIFICATORE TRAMITE IL METODO GET 
												echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"pulsante_td\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"riepilogo_segnalazione.php?id_Segnalazione=".$segnalazione->getAttribute("id")."\" class=\"container_pulsante_td\" title=\"Visualizza!\"><img src=\"../../Immagini/info-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
												echo "\t\t\t\t\t\t\t\t\t\t</td>\n";
												echo "\t\t\t\t\t\t\t\t\t</tr>\n"; 
											}
										}
										
										echo "\t\t\t\t\t\t\t\t</tbody>\n";
										echo "\t\t\t\t\t\t\t</table>\n";
										
									}
								?>
								<div class="pulsante" style="justify-content: center; margin-top: 3.5%; margin-bottom: 0%;">
									<form action="area_riservata.php" method="post">
										<p>
											<button type="submit" class="container_pulsante back" style="margin-left: 1.5em; margin-right: 1.5em; padding: 0em;">Indietro!</button>
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
			require_once ("./footer_sito.php");
		?>
	</body>
</html>