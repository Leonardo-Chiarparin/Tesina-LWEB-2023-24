<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTE LE FAQ ATTUALMENTE PRESENTI SULLA PIATTAFORMA

	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");

	// PER QUESTIONI DI SICUREZZA, È STATO NECESSARIO L'INSERIMENTO DELLO SCRIPT "public_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA
	// CONTRARIAMENTE ALLA SUA CONTROPARTE, OVVERO QUELLA COLLOCATA IN TUTTE LE PAGINE CHE COMPONGONO L'AREA RISERVATA, IL CONTROLLO, IN CASO DI FALLIMENTO, NON REINDERIZZERÀ VERSO UN'ALTRA PAGINA DELLA PIATTAFORMA. INFATTI, LA SCHERMATA IN QUESTIONE DOVRÀ ESSERE VISIBILE A PRESCINDERE DAL FATTO CHE L'UTENTE SI SIA AUTENTICATO O MENO	
	require_once("./public_session_control.php");
	
	// I CLIENTI DELLA PIATTAFORMA POSSONO SUBIRE UNA SOSPENSIONE DEL PROFILO A CAUSA DEL LORO COMPORTAMENTO. PROPRIO PER QUESTO, E CONSIDERANDO CHE CIÒ PUÒ AVVENIRE IN QUALUNQUE MOMENTO, BISOGNERÀ MONITORARE COSTANTEMENTE I LORO "PERMESSI" COSÌ DA IMPEDIRNE LA NAVIGAZIONE VERSO LE SEZIONI PIÙ SENSIBILI DEL SITO 
	require_once("./monitoraggio_stato_account.php");
	
	// LE PROPOSTE DI VENDITA, DATA LA LORO STRUTTURA, POTREBBERO ESSERE SOGGETTE A DELLE RIDUZIONI DI PREZZO, CARATTERIZZATE DA UN INTERVALLO DI VALIDITÀ BEN DEFINITO E TALVOLTA APPLICABILI SUL PROSSIMO ACQUISTO. PERTANTO, NELLE IPOTESI IN CUI NON SIA SEMPRE POSSIBILE RIMUOVERE MANUALMENTE LE VOCI INTERESSATE, ABBIAMO DECISO DI IMPLEMENTARE UN CODICE IN GRADO DI INTERVENIRE IN AUTOMATICO SU TUTTI QUELLI SCONTI NON PIÙ USUFRUIBILI DAI CLIENTI DELLA PIATTAFORMA
	require_once("./rimozione_sconti.php");
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_faq.php");
	require_once("./apertura_file_discussioni.php");
	
	// PRIMA DI PROCEDERE CON LA COMPOSIZIONE DELLA PAGINA, È NECESSARIO VALUTARE IL NUMERO DI FAQ PUBBLICATE NEL CORSO DEL TEMPO. INFATTI, QUALORA NON CE SIANO, SI DOVRÀ STAMPARE, PER TUTTI GLI UTENTI AD ECCEZIONE DELL'AMMINISTRATORE DEL SITO, UN MESSAGGIO CHE NOTIFICHERÀ LA LORO ASSENZA  
	require_once("./calcolo_faq.php");
	
	// UNA VOLTA SELEZIONATO L'ELEMENTO DA GESTIRE, BISOGNERÀ IDENTIFICARE LA FAQ DA RIMUOVERE DAL DOCUMENTO 
	if(isset($_GET["id_Faq"])) {
		
		// PER DI PIÙ, BISOGNA IMPEDIRE CHE VENGA INSERITO UN VALORE INERENTE AD UNA DOMANDA INESISTENTE
		$faq_individuata=false;
		
		for($i=0; $i<$faq->length && !$faq_individuata; $i++) {
			$singola_faq=$faq->item($i);
			
			if($singola_faq->getAttribute("id")==$_GET["id_Faq"]) {
				$faq_individuata=true;
			}
		}
		
		if($faq_individuata) {
			$rootFaq->removeChild($singola_faq);
			
			// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
			if($docFaq->schemaValidate("../../XML/Schema/FAQ.xsd")){
				
				// AL FINE DI GARANTIRE UNA STAMPA GODIBILE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
				$docFaq->preserveWhiteSpace = false;
				$docFaq->formatOutput = true;
				$docFaq->save("../../XML/FAQ.xml");
				
				// PRIMA DI ESSERE REINDIRIZZATI, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
				setcookie("modifica_Effettuata", true);
				
				header("Location: index.php");
				
			}
			else {
				
				// ***
				setcookie("errore_Validazione", true);
				
				header("Location: index.php");
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
									<img src="../../Immagini/question-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<?php if(isset($_SESSION["id_Utente"]) && $_SESSION["tipo_Utente"]=="A") echo "<h2>Aggiungi, visualizza o elimina le domande pi&ugrave; frequenti!</h2>\n"; else echo "<h2>Visualizza le domande pi&ugrave; frequenti!</h2>\n"; ?>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<?php
									// IN BASE AL NUMERO DI RICHIESTE DA ESAMINARE, SARÀ POSSIBILE STABILIRE COSA PRESENTARE A SCHERMO
									if(!(isset($_SESSION["id_Utente"]) && $_SESSION["tipo_Utente"]=="A") && $num_faq==0) {
										echo "<span class=\"nessun_elemento\">Non &egrave; stato trovato nessun elemento con le specifiche di interesse.</span>\n";
									}
									else {
										// L'INTESTAZIONE DELLA TABELLA SARÀ UN FATTORE STATICO IN CUI VERRANNO PRESENTATI TUTTI GLI ELEMENTI, DEFINITI COME COLONNE, CHE LA COMPORRANNO
										echo "<table>\n";
										echo "\t\t\t\t\t\t\t\t<thead>\n";
										echo "\t\t\t\t\t\t\t\t\t<tr>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">FAQ</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Titolo</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Descrizione</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Intervento</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Azione</th>\n";
										echo "\t\t\t\t\t\t\t\t\t</tr>\n";
										echo "\t\t\t\t\t\t\t\t</thead>\n";
										echo "\t\t\t\t\t\t\t\t<tbody>\n";
										
										// QUALORA L'UTENTE RISULTI ESSERE L'AMMINISTRATORE DEL SITO, ALLORA LA PRIMA RIGA DELLA TABELLA CONTERRÀ UN PULSANTE PER PERMETTERE L'INSERIMENTO DI NUOVE FAQ
										if(isset($_SESSION["id_Utente"]) && $_SESSION["tipo_Utente"]=="A") {
											echo "\t\t\t\t\t\t\t\t\t<tr>\n"; 
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">\n";
											echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"pulsante_td\">\n";
											echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"pubblicazione_faq.php\" class=\"container_pulsante_td\" title=\"Aggiungi!\"><img src=\"../../Immagini/plus-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
											echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
											echo "\t\t\t\t\t\t\t\t\t\t</td>\n";
											echo "\t\t\t\t\t\t\t\t\t</tr>\n";
										}
										
										// IL CORPO DELLA COMPONENTE DI CUI SOPRA VERRÀ COMPOSTO DINAMICAMENTE E SEGUENDO L'ORDINE INDOTTO DALLE PRECEDENTI STAMPE
										for($i=0; $i<$faq->length; $i++) {
											
											$singola_faq=$faq->item($i);
											
											echo "\t\t\t\t\t\t\t\t\t<tr>\n"; 
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$singola_faq->getAttribute("id")."</td>\n";
											
											// ALLO SCOPO DI PRESENTARE LE SINGOLE INFORMAZIONI NEL MIGLIOR MODO POSSIBILE, SI DOVRÀ INDIVIDUARE LA NATURA DELLE COMPONENTI CHE CARATTERIZZANO LA FAQ DI INTERESSE. INFATTI, QUALORA PRESENTI DEI RIFERIMENTI A DELLE DISCUSSIONI O A DEGLI INTERVENTI "ESTERNI", BISOGNERÀ RICERCARNE IL CONTENUTO REPERENDOLO DAL RELATIVO FILE XML
											if($singola_faq->getElementsByTagName("discussioneDaZero")->length!=0) {
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$singola_faq->getElementsByTagName("titolo")->item(0)->textContent."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$singola_faq->getElementsByTagName("descrizione")->item(0)->textContent."</td>\n"; 
												
												if($singola_faq->getElementsByTagName("interventoDaZero")->length!=0) {
													echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$singola_faq->getElementsByTagName("testo")->item(0)->textContent."</td>\n";
												}
												else {
													$intervento_individuato=false;
													
													for($j=0; $j<$discussioni->length && !$intervento_individuato; $j++) {
														$discussione=$discussioni->item($j);
														
														if($singola_faq->getElementsByTagName("interventoEsistente")->item(0)->getAttribute("idDiscussione")==$discussione->getAttribute("id")) {
															
															for($k=0; $k<$discussione->getElementsByTagName("intervento")->length && !$intervento_individuato; $k++) {
																$intervento=$discussione->getElementsByTagName("intervento")->item($k);
																
																if($singola_faq->getElementsByTagName("interventoEsistente")->item(0)->getAttribute("idIntervento")==$intervento->getAttribute("id")) {
																	$intervento_individuato=true;
																}
															}
														}
													}
													
													echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$intervento->getElementsByTagName("testo")->item(0)->textContent."</td>\n";
												}
											}
											else {
												for($j=0; $j<$discussioni->length; $j++) {
													$discussione=$discussioni->item($j);
													
													if($singola_faq->getElementsByTagName("discussioneEsistente")->item(0)->getAttribute("idDiscussione")==$discussione->getAttribute("id"))
														break;
												}
												
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$discussione->getElementsByTagName("titolo")->item(0)->textContent."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$discussione->getElementsByTagName("descrizione")->item(0)->textContent."</td>\n"; 
												
												if($singola_faq->getElementsByTagName("interventoDaZero")->length!=0) {
													echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$singola_faq->getElementsByTagName("testo")->item(0)->textContent."</td>\n";
												}
												else {
													
													for($j=0; $j<$discussione->getElementsByTagName("intervento")->length; $j++) {
														$intervento=$discussione->getElementsByTagName("intervento")->item($j);
														
														if($singola_faq->getElementsByTagName("interventoEsistente")->item(0)->getAttribute("idIntervento")==$intervento->getAttribute("id")) {
															break;
														}
													}
													
													echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$intervento->getElementsByTagName("testo")->item(0)->textContent."</td>\n";
													
												}
											}
											
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">\n";
											echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"pulsante_td\">\n";
											
											echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"riepilogo_singola_faq.php?id_Faq=".$singola_faq->getAttribute("id")."\" class=\"container_pulsante_td\" title=\"Visualizza!\"><img src=\"../../Immagini/info-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
											
											// STANDO A QUANTO RIPORTATO IN PRECEDENZA, L'AMMINISTRATORE SARÀ IN GRADO DI VISUALIZZARE A SCHERMO UN ULTERIORE PULSANTE TRAMITE IL QUALE POTRÀ DECIDERE SE ELIMINARE UNA CERTA FAQ O MENO
											if(isset($_SESSION["id_Utente"]) && $_SESSION["tipo_Utente"]=="A")
												echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"riepilogo_faq.php?id_Faq=".$singola_faq->getAttribute("id")."\" class=\"container_pulsante_td back\" title=\"Elimina...\"><img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
											
											echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
											echo "\t\t\t\t\t\t\t\t\t\t</td>\n";
											
											echo "\t\t\t\t\t\t\t\t\t</tr>\n";
										
										}
										echo "\t\t\t\t\t\t\t\t</tbody>\n";
										echo "\t\t\t\t\t\t\t</table>\n";
									}
								?>
								<div class="pulsante" style="justify-content: center; margin-top: 3.5%; margin-bottom: 0%;">
									<form action="index.php" method="post">
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
			require_once("./footer_sito.php");
		?>
	</body>
</html>