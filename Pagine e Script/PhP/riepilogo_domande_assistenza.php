<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTE LE DOMANDE PER RIPRISTINARE LA PASSWORD DEI VARI CLIENTI, LE QUALI NON SONO ANCORA STATE CONSIDERATE DALL'AMMINISTRATORE

	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");

	// PER QUESTIONI DI SICUREZZA, È NECESSARIO L'INSERIMENTO DELLO SCRIPT "private_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA. QUESTO MECCANISMO NON PERMETTERÀ L'ACCESSO AGLI UTENTI CHE NON SI AUTENTICATI TRAMITE IL RELATIVO SERVIZIO
	require_once("./private_session_control.php");
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO ALL'AMMINISTRATORE DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="A") {
		header("Location: area_riservata.php");
	}
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_domande_assistenza.php");
	
	// PRIMA DI PROCEDERE CON LA COMPOSIZIONE DELLA PAGINA, È NECESSARIO VALUTARE IL NUMERO DI RICHIESTE NON ANCORA PROCESSATE. INFATTI, QUALORA SIANO GIÀ STATE CONSIDERATE, SI DOVRÀ STAMPARE UN MESSAGGIO CHE NOTIFICHERÀ L'ASSENZA DI ULTERIORI NOTIFICHE IN SOSPESO  
	require_once("./calcolo_domande_assistenza.php");
	
	// UNA VOLTA SELEZIONATA LA RICHIESTA DA GESTIRE, BISOGNERÀ PROCEDERE CON LA MODIFICA DELL'ATTRIBUTO "seen" IN ESSA CONTENUTO
	if(isset($_GET["id_Domanda"])) {
		
		// AL FINE DI ESEGUIRE LA PROCEDURA DI CUI SOPRA, SI DOVRÀ NUOVAMENTE CONSULTARE IL DOCUMENTO FINO AL PUNTO SELEZIONATO 
		for($i=0; $i<$domande->length; $i++){
			$domanda=$domande->item($i);
			
			if($domanda->getAttribute("id")==$_GET["id_Domanda"]) {
				$domanda->setAttribute("seen", "Si");
		
				// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
				if($docDomande->schemaValidate("../../XML/Schema/Domande_Assistenza.xsd")){
					
					// AL FINE DI GARANTIRE UNA STAMPA GODIBILE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
					$docDomande->preserveWhiteSpace = false;
					$docDomande->formatOutput = true;
					$docDomande->save("../../XML/Domande_Assistenza.xml");
					
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
									<img src="../../Immagini/screwdriver-wrench-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Contrassegna le domande gi&agrave; processate!</h2>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<?php
									// IN BASE AL NUMERO DI DOMANDE DA ESAMINARE, SARÀ POSSIBILE STABILIRE COSA PRESENTARE A SCHERMO
									if($num_domande==0) {
										echo "<span class=\"nessun_elemento\">Non &egrave; stato trovato nessun elemento con le specifiche di interesse.</span>\n";
									}
									else {
										// L'INTESTAZIONE DELLA TABELLA SARÀ UN FATTORE STATICO IN CUI VERRANNO PRESENTATI TUTTI GLI ELEMENTI, DEFINITI COME COLONNE, CHE LA COMPONGONO 
										echo "<table>\n";
										echo "\t\t\t\t\t\t\t\t<thead>\n";
										echo "\t\t\t\t\t\t\t\t\t<tr>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Domanda</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Username <strong style=\"color: rgb(217,118,64);\" title=\"del richiedente\">*</strong></th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Email <strong style=\"color: rgb(217,118,64);\" title=\"del richiedente\">*</strong></th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Recapito Telefonico <strong style=\"color: rgb(217,118,64);\" title=\"del richiedente\">*</strong></th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Data &amp; Ora</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Azione</th>\n";
										echo "\t\t\t\t\t\t\t\t\t</tr>\n";
										echo "\t\t\t\t\t\t\t\t</thead>\n";
										echo "\t\t\t\t\t\t\t\t<tbody>\n";
										
										// IL CORPO DELLA COMPONENTE DI CUI SOPRA VERRÀ COMPOSTO DINAMICAMENTE E SEGUENDO L'ORDINE INDOTTO DALLE PRECEDENTI STAMPE
										for($i=0; $i<$domande->length; $i++){
											$domanda=$domande->item($i);
											
											if($domanda->getAttribute("seen")=="No") {
												
												// PER CIASCUNA SEGNALAZIONE, VERRANNO RIPORTATI L'USERNAME, L'EMAIL E IL RECAPITO TELEFONICO DELL'UTENTE CHE L'HA INOLTRATA
												$sql="SELECT Username, Email, Num_Telefono FROM $tab WHERE ID=".$domanda->getAttribute("idRichiedente")." AND Tipo_Utente='C'";
												$result=mysqli_query($conn, $sql);
			
												while($row=mysqli_fetch_array($result)) {
													$username=$row["Username"];
													$email=$row["Email"];
													$num_telefono=$row["Num_Telefono"];
												}
												
												echo "\t\t\t\t\t\t\t\t\t<tr>\n"; 
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$domanda->getAttribute("id")."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$username."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$email."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$num_telefono."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".date_format(date_create($domanda->getAttribute("dataOraRichiesta")), "d/m/Y H:i:s")."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">\n";
												
												// ALLO SCOPO DI OTTENERE CORRETTAMENTE LE INFORMAZIONI DI UNA CERTA RICHIESTA, È STATO NECESSARIO FORZARE IL REINDIRIZZAMENTO ALLA PAGINA DI INTERESSE CON LA SPECIFICA "MANUALE" DELL'IDENTIFICATORE TRAMITE IL METODO GET 
												echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"pulsante_td\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"riepilogo_domande_assistenza.php?id_Domanda=".$domanda->getAttribute("id")."\" class=\"container_pulsante_td\" title=\"Conferma!\"><img src=\"../../Immagini/check-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
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