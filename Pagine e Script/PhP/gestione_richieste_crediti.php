<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTE LE RICHIESTE PER LA RICARICA DEL SALDO DEI VARI CLIENTI A CUI L'AMMINISTRATORE NON HA ANCORA RISPOSTO

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
	require_once("./apertura_file_richieste_crediti.php");
	
	// PRIMA DI PROCEDERE CON LA COMPOSIZIONE DELLA PAGINA, È NECESSARIO VALUTARE IL NUMERO DI RICHIESTE ANCORA IN CORSO. INFATTI, QUALORA SIANO GIÀ STATE CONSIDERATE, SI DOVRÀ STAMPARE UN MESSAGGIO CHE NOTIFICHERÀ L'ASSENZA DI ULTERIORI DOMANDE A CUI RISPONDERE 
	require_once("./calcolo_richieste_crediti.php");
	
	// UNA VOLTA SELEZIONATA LA RICHIESTA DA GESTIRE, BISOGNERÀ IDENTIFICARE L'OPERAZIONE DA SVOLGERE, LA QUALE POTRÀ PORTARE ESCLUSIVAMENTE ALL'ACCETTAZIONE O AL RIFIUTO DELLA RICARICA DA PARTE DELL'AMMINISTRATORE
	if(isset($_GET["id_Richiesta"]) && isset($_GET["azione"]) && ($_GET["azione"]=="rifiuta" || $_GET["azione"]=="conferma")) {
		
		// PER UNA QUESTIONE PURAMENTE PRATICA, SARÀ UTILE DEFINIRE UNA VARIABILE DI STATO CON CUI AGGIORNARE LA DOMANDA IN ESAME 
		if($_GET["azione"]=="rifiuta")
			$stato="Rifiutata";
		
		if($_GET["azione"]=="conferma")
			$stato="Accettata";
		
		// AL FINE DI ESEGUIRE LA PROCEDURA DI CUI SOPRA, SI DOVRÀ NUOVAMENTE CONSULTARE IL DOCUMENTO FINO AL PUNTO SELEZIONATO 
		for($i=0; $i<$richieste->length; $i++){
			$richiesta=$richieste->item($i);
			
			if($richiesta->getAttribute("id")==$_GET["id_Richiesta"]) {
				$richiesta->setAttribute("stato", $stato);
				
				// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
				if($docRichieste->schemaValidate("../../XML/Schema/Richieste_Crediti.xsd")){
					
					// PER UNA STAMPA OTTIMALE, SONO STATI APPLICATI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
					$docRichieste->preserveWhiteSpace = false;
					$docRichieste->formatOutput = true;
					$docRichieste->save("../../XML/Richieste_Crediti.xml");
					
					// SE SI TRATTA DI UN'ACCETTAZIONE, PROCEDIAMO AD AUMENTARE IL SALDO DELL'UTENTE COINVOLTO
					if($stato=="Accettata") {
						// DATO L'INTENTO DI VOLER CONFRONTARE L'ESITO DI UNA DETERMINATA QUERY, SARÀ NECESSARIO PREDISPORRE IL TUTTO ALL'INTERNO DI UN COSTRUTTO try ... catch ... AL FINE DI CATTURARE L'EVENTUALE ECCEZIONE E NOTIFICARE L'ACCADUTO ALL'UTENTE IN OGGETTO
						// INFATTI, UN POSSIBILE FALLIMENTO POTREBBE DIPENDERE DAL SUPERAMENTO DEL LIMITE DI CARATTERI CHE POSSONO ESSERE INSERITI ALL'INTERNO DI UN CAMPO DELLA TABELLA RELAZIONALE COINVOLTA 
						try {
							// SE NON È STATA EVIDENZIATA ALCUNA SORTA DI PROBLEMATICA, È POSSIBILE EFFETTUARE L'ADEGUAMENTO DEI DATI ALL'INTERNO DELLA BASE DI DATI
							$sql="UPDATE $tab SET Portafoglio_Crediti=Portafoglio_Crediti+".$richiesta->getAttribute("numeroCrediti")." WHERE ID=".$richiesta->getAttribute("idRichiedente");
							
							echo $sql;
							
							// COME ACCENNATO, PRIMA DI CONCLUDERE L'OPERAZIONE BISOGNERÀ VALUTARE L'ESITO DELL'ESECUZIONE INERENTE AL PRECEDENTE COMANDO SQL
							if(mysqli_query($conn,$sql)){
				
								// PRIMA DI ESSERE REIDERIZZATI, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
								$_SESSION["modifica_Effettuata"]=true;
								
								header("Location: area_riservata.php");
								
							}
							else
							{ 
								throw new mysqli_sql_exception;	   
							}
						}
						catch (mysqli_sql_exception $e){
							
							// *** 
							$errore_query=true;
						}
					}
					else {
						if($stato=="Rifiutata") {
							// ***
							$_SESSION["modifica_Effettuata"]=true;
							
							header("Location: area_riservata.php");
						}
					}
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
			// DATA LA VARIETÀ DI CASISTICHE CHE SI POSSONO MANIFESTARE, ABBIAMO DECISO DI DEFINIRE UNA GERARCHIA DI CONTROLLI PER GESTIRE AL MEGLIO LE STAMPE DEI VARI MESSAGGI DI POPUP, I QUALI, MEDIANTE UNA SIMILE IMPOSTAZIONE, SARANNO PRESENTATI SINGOLARMENTE AI SOGGETTI INTERESSATI
			if(isset($errore_query) && $errore_query) {
				
				// IN OCCASIONE DEL POSSIBILE RICARICAMENTO DELLA PAGINA, SI PROCEDE CON LA RIMOZIONE DEL FLAG ALLO SCOPO DI NON RIPROPORRE LA MEDESIMA NOTIFCA 
				$errore_query=false;
				
				echo "<div class=\"error_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
				echo "\t\t\t\t\t<p>NON &Egrave; STATO POSSIBILE AGGIORNARE IL SALDO DEL CLIENTE INTERESSATO...</p>\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t</div>\n";
				echo "\t\t</div>\n";
			}
			
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
									<img src="../../Immagini/comment-dollar-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Approva o respingi le richieste di credito ricevute!</h2>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<?php
									// IN BASE AL NUMERO DI RICHIESTE DA ESAMINARE, SARÀ POSSIBILE STABILIRE COSA PRESENTARE A SCHERMO
									if($num_richieste==0) {
										echo "<span class=\"nessun_elemento\">Non &egrave; stato trovato nessun elemento con le specifiche di interesse.</span>\n";
									}
									else {
										// L'INTESTAZIONE DELLA TABELLA SARÀ UN FATTORE STATICO IN CUI VERRANNO PRESENTATI TUTTI GLI ELEMENTI, DEFINITI COME COLONNE, CHE LA COMPONGONO 
										echo "<table>\n";
										echo "\t\t\t\t\t\t\t\t<thead>\n";
										echo "\t\t\t\t\t\t\t\t\t<tr>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Richiesta</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Username</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Importo</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Data &amp; Ora</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Stato</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Azione</th>\n";
										echo "\t\t\t\t\t\t\t\t\t</tr>\n";
										echo "\t\t\t\t\t\t\t\t</thead>\n";
										echo "\t\t\t\t\t\t\t\t<tbody>\n";
										
										// IL CORPO DELLA COMPONENTE DI CUI SOPRA VERRÀ COMPOSTO DINAMICAMENTE E SEGUENDO L'ORDINE INDOTTO DALLE PRECEDENTI STAMPE
										for($i=0; $i<$richieste->length; $i++){
											$richiesta=$richieste->item($i);
											
											if($richiesta->getAttribute("stato")=="In Corso") {
												
												// PER CIASCUNA RICHIESTA, VERRÀ RIPORTATO L'USERNAME DELL'UTENTE CHE L'HA INOLTRATA, IN QUANTO, A LIVELLO DI TABELLA RELAZIONALE, RAPPRESENTA UN FATTORE unique E, PERTANTO, RISERVATO AD UNA SOLA ENTITÀ
												$sql="SELECT Username FROM $tab WHERE ID=".$richiesta->getAttribute("idRichiedente")." AND Tipo_Utente='C'";
												$result=mysqli_query($conn, $sql);
			
												while($row=mysqli_fetch_array($result))
													$username=$row["Username"];
												
												echo "\t\t\t\t\t\t\t\t\t<tr>\n"; 
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$richiesta->getAttribute("id")."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$username."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$richiesta->getAttribute("numeroCrediti")."</td>\n"; 
												
												// INOLTRE, SI È DECISO DI PRESENTARE I VARI RIFERIMENTI TEMPORALI SECONDO IL FORMATO LOCALE, OVVERO QUELLO ITALIANO. PER RIUSCIRCI, SONO STATE UTILIZZATE LE FUNZIONI date_create(...) E date_format(...), LA QUALE, DATA IN INPUT UNA DATA (O Datetime), CONSENTE DI DARLE UNA CERTA STRUTTURA  
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".date_format(date_create($richiesta->getAttribute("dataOraRichiesta")), "d/m/Y H:i:s")."</td>\n";
												
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$richiesta->getAttribute("stato")."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">\n";
												
												// ALLO SCOPO DI OTTENERE CORRETTAMENTE LE INFORMAZIONI DI UNA CERTA RICHIESTA, È STATO NECESSARIO FORZARE IL REINDIRIZZAMENTO ALLA STESSA PAGINA CON LA SPECIFICA "MANUALE" DEI VARI ELEMENTI (IDENTIFICATORE E AZIONE) TRAMITE IL METODO GET 
												echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"pulsante_td\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"gestione_richieste_crediti.php?id_Richiesta=".$richiesta->getAttribute("id")."&amp;azione=conferma\" class=\"container_pulsante_td\" title=\"Accetta!\"><img src=\"../../Immagini/check-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"gestione_richieste_crediti.php?id_Richiesta=".$richiesta->getAttribute("id")."&amp;azione=rifiuta\" class=\"container_pulsante_td back\" title=\"Rifiuta...\"><img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
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