<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTE LE FUNZIONALITÀ OFFERTE AGLI UTENTI DEL SITO IN RELAZIONE ALLA LORO CLASSE DI APPARTENENZA (CLIENTI, GESTORI O AMMINISTRATORE)

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
	
	// NEL CASO IN CUI CI SIANO DELLE RICHIESTE DI CREDITO ANCORA PRIVE DI RISPOSTA, L'AMMINISTRATORE DEVE ESSERE INFORMATO DELLA SITUAZIONE CORRENTE MEDIANTE UN MESSAGGIO DI POPUP E LA PERSONALIZZAZIONE DEL "PULSANTE" INERENTE ALLA FUNZIONALITÀ CON CUI POTERLE GESTIRE  
	if($_SESSION["tipo_Utente"]=="A") {
		require_once("./calcolo_richieste_crediti.php");
	}
	
	// SIMILMENTE AL CONTESTO RIPORTATO SUBITO SOPRA, LE FIGURE DI SPICCO DELLA PIATTAFORMA (GESTORI E AMMINISTRATORE) DOVRANNO ESSERE RESI PARTECIPI DELLE SEGNALAZIONI CHE SONO STATE INOLTRATE DAI VARI CLIENTI MEDIANTE UN MESSAGGIO DI POP, DI PRIORITÀ MAGGIORE RISPETTO AL PRECEDENTE, E UNA "PERSONALIZZAZIONE" DEL PULSANTE CHE NE PERMETTE LA GESTIONE
	if($_SESSION["tipo_Utente"]=="G" || $_SESSION["tipo_Utente"]=="A") {
		require_once("./calcolo_segnalazioni.php");
	}
	
	// PER QUANTO CONCERNE L'AMMINISTRATORE, SARÀ NECESSARIO TENERE TRACCIA DELLE DOMANDE CHE, OLTRE AD ESSERE ANCORA PENDENTI, SI RIFERISCONO ALL'ASSISTENZA RICHIESTA DAI CLIENTI PER IL RIPRISTINO DELLE LORO PAROLE CHIAVE
	if($_SESSION["tipo_Utente"]=="A") {
		require_once("./calcolo_domande_assistenza.php");
	}
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>LEV: Libri &amp; Videogiochi</title>
		<link rel="icon" href="../../Immagini/Icona_LEV.png" />
		<link rel="stylesheet" href="../../Stili CSS/style_area_riservata.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_intestazione_sito.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_common.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_footer_sito.css" type="text/css" />
		<script type="text/javascript" src="../JavaScript/gestioneMenuTendina.js"></script>
	</head>
	<body>
		<?php
			// DATA LA VARIETÀ DI CASISTICHE CHE SI POSSONO MANIFESTARE, ABBIAMO DECISO DI DEFINIRE UNA GERARCHIA DI CONTROLLI PER GESTIRE AL MEGLIO LE STAMPE DEI VARI MESSAGGI DI POPUP, I QUALI, MEDIANTE UNA SIMILE IMPOSTAZIONE, SARANNO PRESENTATI SINGOLARMENTE AI SOGGETTI INTERESSATI
			if(isset($_SESSION["modifica_Effettuata"])) {
				
				// IN OCCASIONE DEL POSSIBILE RICARICAMENTO DELLA PAGINA, SI PROCEDE CON LA RIMOZIONE DEL FLAG AL SOLO SCOPO DI NON RIPROPORRE LA MEDESIMA NOTIFCA 
				unset($_SESSION["modifica_Effettuata"]);
				
				echo "<div class=\"confirm_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/check-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"con\">OTTIMO!</p>\n";
				echo "\t\t\t\t\t<p>OPERAZIONE EFFETTUATA CON SUCCESSO!</p>\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t</div>\n";
				echo "\t\t</div>\n";
			}
			else {
				// ***
				if(isset($_COOKIE["errore_Validazione"]) && $_COOKIE["errore_Validazione"]) {
					
					// ***
					setcookie("errore_Validazione","",time()-60);
					
					echo "<div class=\"error_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
					echo "\t\t\t\t\t<p>VALIDAZIONE DEL/I FILE NON RIUSCITA... SI CONSIGLIA DI RIPRISTINARE LE STRUTTURE DATI!</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
				}
				else {
					// ***
					if(isset($num_domande) && $num_domande!=0) {
						// ***
						echo "<div class=\"error_message\">\n";
						echo "\t\t\t<div class=\"container_message\">\n";
						echo "\t\t\t\t<div class=\"container_img\">\n";
						echo "\t\t\t\t\t<img src=\"../../Immagini/info-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t\t<div class=\"message\">\n";
						echo "\t\t\t\t\t<p class=\"err\">ATTENZIONE!</p>\n";
						echo "\t\t\t\t\t<p>RILEVATE DELLE DOMANDE NON ANCORA CONSIDERATE...</p>\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t</div>\n";
						echo "\t\t</div>\n";
					}
					else {
						// ***
						if(isset($num_segnalazioni) && $num_segnalazioni!=0) {
							// ***
							echo "<div class=\"error_message\">\n";
							echo "\t\t\t<div class=\"container_message\">\n";
							echo "\t\t\t\t<div class=\"container_img\">\n";
							echo "\t\t\t\t\t<img src=\"../../Immagini/info-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t\t<div class=\"message\">\n";
							echo "\t\t\t\t\t<p class=\"err\">ATTENZIONE!</p>\n";
							echo "\t\t\t\t\t<p>RILEVATE DELLE SEGNALAZIONI NON ANCORA CONSIDERATE...</p>\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t</div>\n";
							echo "\t\t</div>\n";
						}
						else {
							// ***
							if(isset($num_richieste) && $num_richieste!=0) {
								// ***
								echo "<div class=\"error_message\">\n";
								echo "\t\t\t<div class=\"container_message\">\n";
								echo "\t\t\t\t<div class=\"container_img\">\n";
								echo "\t\t\t\t\t<img src=\"../../Immagini/info-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t\t<div class=\"message\">\n";
								echo "\t\t\t\t\t<p class=\"err\">ATTENZIONE!</p>\n";
								echo "\t\t\t\t\t<p>RILEVATE DELLE RICHIESTE DI RICARICA ANCORA PRIVE DI RISPOSTA...</p>\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t</div>\n";
								echo "\t\t</div>\n";
							}
						}
					}
				}
			}
			
			// L'INTESTAZIONE DEL SITO, POICHÈ SI TRATTA DI UN DETTAGLIO RICORRENTE, È STATA DEFINITA ALL'INTERNO DI UN "PROGRAMMA" INDIPENDENTE CHE, DATE LE ESIGENZE, VERRÀ INCLUSO A PIÙ RIPRESE NELLE VARIE PAGINE DELL'APPLICATIVO
			require_once ("./intestazione_sito.php");
		?>
		<div class="corpo_pagina">
			<div class="container_corpo_pagina">
				<div class="area_riservata">
					<div class="container_area_riservata">
						<div class="intestazione_area_riservata">	
							<div class="container_intestazione_area_riservata">
								<span class="icona_area_riservata">
									<img src="../../Immagini/user-lock-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Area Riservata</h2>
							</div>
						</div>
						<div class="corpo_area_riservata">
							<div class="container_corpo_area_riservata">
								<?php
									// PER I SOLI CLIENTI, SARANNO DISPONIBILI: LO STORICO DEGLI ACQUISTI; LA MODIFICA E IL RIEPILOGO DELLE INFORMAZIONI ANAGRAFICHE E DI SICUREZZA; LA VISUALIZZAZIONE E LA GESTIONE DELLE TRANSAZIONI PER LA RICARICA DEL LORO PORTAFOGLIO 
									if($_SESSION["tipo_Utente"]=="C"){
										echo "<div class=\"riga_elenco_funzioni\">\n";
										echo "\t\t\t\t\t\t\t\t<a href=\"riepilogo_acquisti.php\" class=\"cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t<span class=\"container_cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/box-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"corpo_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>I miei Acquisti</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Visualizza il riepilogo degli ordini effettuati</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t</a>\n";
										echo "\t\t\t\t\t\t\t\t<a href=\"modifica_credenziali.php\" class=\"cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t<span class=\"container_cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/user-shield-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"corpo_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Accesso e Sicurezza</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Modifica il nome utente, l'email e la parola chiave</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t</a>\n";
										echo "\t\t\t\t\t\t\t\t<a href=\"saldo_clienti.php\" class=\"cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t<span class=\"container_cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/wallet-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"corpo_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Saldo e Ricarica</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Visualizza il totale dei crediti e richiedine degli altri</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t</a>\n";
										echo "\t\t\t\t\t\t\t</div>\n";
									}
									
									// PER I SOLI GESTORI, SARANNO DISPONIBILI: LE PROCEDURE PER LA GESTIONE (INSERIMENTO, MODIFICA E CANCELLAZIONE) DELLE SINGOLE OFFERTE; LA VISUALIZZAZIONE DEL PROFILO DEI CLIENTI E DELLA RICAPITOLAZIONE DEI LORO ACQUISTI; LA CONSULTAZIONE, CON CONSEGUENTE ED EVENTUALE MODERAZIONE, DELLE SEGNALAZIONI RICEVUTE DAI CLIENTI 									
									if($_SESSION["tipo_Utente"]=="G"){
										echo "<div class=\"riga_elenco_funzioni\">\n";
										echo "\t\t\t\t\t\t\t\t<a href=\"riepilogo_offerte.php\" class=\"cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t<span class=\"container_cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/tag-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"corpo_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Offerte</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Aggiungi, modifica o elimina delle proposte di vendita</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t</a>\n";
										echo "\t\t\t\t\t\t\t\t<a href=\"riepilogo_utenti.php\" class=\"cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t<span class=\"container_cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/users-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"corpo_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Riepilogo Utenti</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Visualizza il profilo dei clienti e lo storico dei loro acquisti</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t</a>\n";
										
										if($num_segnalazioni!=0)
											echo "\t\t\t\t\t\t\t\t<a href=\"riepilogo_segnalazioni.php\" class=\"cella_funzione_segnalazioni\">\n";
										else 
											echo "\t\t\t\t\t\t\t\t<a href=\"riepilogo_segnalazioni.php\" class=\"cella_funzione\">\n";
										
										echo "\t\t\t\t\t\t\t\t\t<span class=\"container_cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/satellite-dish-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"corpo_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Segnalazioni</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Visualizza le notifiche e intervieni sui contributi coinvolti</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t</a>\n";
										echo "\t\t\t\t\t\t\t</div>\n";
									}
									
									// PER IL SOLO AMMINISTRATORE, SARANNO DISPONIBILI: LA VISUALIZZAZIONE DEL PROFILO DEGLI UTENTI CON EVENTUALE AGGIORNAMENTO DI TUTTI I LORO DETTAGLI; IL MECCANISMO PER LA SOSPENSIONE DEI CLIENTI A CAUSA DEL LORO COMPORTAMENTO; LA GESTIONE (ACCETTAZIONE O RIFIUTO) DELLE DOMANDE PER LA RICARICA DEL SALDO DEI CLIENTI; LE PROCEDURE PER LA GESTIONE (INSERIMENTO, MODIFICA E CANCELLAZIONE) DEI SINGOLI ARTICOLI; IL MECCANISMO PER LA RICEZIONE DELLE DOMANDE INERENTI AL RIPRISTINO DELLE PAROLE CHIAVE DEI VARI CLIENTI 
									if($_SESSION["tipo_Utente"]=="A"){
										echo "<div class=\"riga_elenco_funzioni\">\n";
										echo "\t\t\t\t\t\t\t\t<a href=\"riepilogo_utenti.php\" class=\"cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t<span class=\"container_cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/users-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"corpo_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Riepilogo Utenti</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Visualizza il profilo degli utenti e modifica tutti i loro dati</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t</a>\n";
										echo "\t\t\t\t\t\t\t\t<a href=\"restrizioni_clienti.php\" class=\"cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t<span class=\"container_cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/ban-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"corpo_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Restrizioni</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Disattiva o riabilita il profilo dei clienti sospesi in passato</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t</a>\n";
										
										if($num_richieste!=0)
											echo "\t\t\t\t\t\t\t\t<a href=\"gestione_richieste_crediti.php\" class=\"cella_funzione_richieste\">\n";
										else 
											echo "\t\t\t\t\t\t\t\t<a href=\"gestione_richieste_crediti.php\" class=\"cella_funzione\">\n";
										
										echo "\t\t\t\t\t\t\t\t\t<span class=\"container_cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_icona_funzione\">\n"; 
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/coins-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"corpo_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Crediti</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Visualizza e rispondi alle richieste di ricarica inoltrate dai clienti</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t</a>\n";
										echo "\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t<div class=\"riga_elenco_funzioni\">\n";
										
										if($num_segnalazioni!=0)
											echo "\t\t\t\t\t\t\t\t<a href=\"riepilogo_segnalazioni.php\" class=\"cella_funzione_segnalazioni\">\n";
										else 
											echo "\t\t\t\t\t\t\t\t<a href=\"riepilogo_segnalazioni.php\" class=\"cella_funzione\">\n";
										
										echo "\t\t\t\t\t\t\t\t\t<span class=\"container_cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/satellite-dish-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"corpo_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Segnalazioni</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Visualizza le notifiche e intervieni sui contributi coinvolti</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t</a>\n";
										echo "\t\t\t\t\t\t\t\t<a href=\"riepilogo_prodotti.php\" class=\"cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t<span class=\"container_cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/dolly-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"corpo_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Magazzino</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Tieni traccia dei vari articoli e modifica tutti i loro dettagli</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t</a>\n";
										
										if($num_domande!=0)
											echo "\t\t\t\t\t\t\t\t<a href=\"riepilogo_domande_assistenza.php\" class=\"cella_funzione_richieste\">\n";
										else 
											echo "\t\t\t\t\t\t\t\t<a href=\"riepilogo_domande_assistenza.php\" class=\"cella_funzione\">\n";
										
										echo "\t\t\t\t\t\t\t\t\t<span class=\"container_cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_icona_funzione\">\n"; 
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/handshake-angle-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"corpo_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Assistenza</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Consulta le domande per ripristinare le password smarrite</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t</a>\n";
										
										echo "\t\t\t\t\t\t\t</div>\n";
									}
										
									// ESSENDO UN DETTAGLIO COMUNE AI GESTORI E AI CLIENTI CHE INTERAGISCONO CON IL SITO, SI È DECISO DI RENDERE LA SCHERMATA DI RIEPILOGO E MODIFICA DEI PROPRI DATI ANAGRAFICI UN FATTORE ACCESSIBILE AD AMBEDUE LE CLASSI DI UTENTI CITATE
									if($_SESSION["tipo_Utente"]=="G" || $_SESSION["tipo_Utente"]=="C") {
										echo "\t\t\t\t\t\t\t<div class=\"riga_elenco_funzioni\">\n";
										echo "\t\t\t\t\t\t\t\t<a href=\"profilo.php\" class=\"cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t<span class=\"container_cella_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_icona_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/user-solid.svg\" alt=\"Icona Profilo\" />\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span class=\"corpo_funzione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>I miei Dati</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span>Visualizza e aggiorna le informazioni personali</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t</a>\n";
										echo "\t\t\t\t\t\t\t</div>\n";
									}
								?>
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