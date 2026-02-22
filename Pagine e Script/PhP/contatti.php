<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php 
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTI I CONTATTI DELL'APPLICATIVO CHE POTRANNO ESSERE UTILI PER I VARI VISITATORI

	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");

	// PER QUESTIONI DI SICUREZZA, È STATO NECESSARIO L'INSERIMENTO DELLO SCRIPT "public_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA
	// CONTRARIAMENTE ALLA SUA CONTROPARTE, OVVERO QUELLA COLLOCATA IN TUTTE LE PAGINE CHE COMPONGONO L'AREA RISERVATA, IL CONTROLLO, IN CASO DI FALLIMENTO, NON REINDERIZZERÀ VERSO UN'ALTRA PAGINA DELLA PIATTAFORMA. INFATTI, LA SCHERMATA IN QUESTIONE DOVRÀ ESSERE VISIBILE A PRESCINDERE DAL FATTO CHE L'UTENTE SI SIA AUTENTICATO O MENO	
	require_once("./public_session_control.php");
	
	// I CLIENTI DELLA PIATTAFORMA POSSONO SUBIRE UNA SOSPENSIONE DEL PROFILO A CAUSA DEL LORO COMPORTAMENTO. PROPRIO PER QUESTO, E CONSIDERANDO CHE CIÒ PUÒ AVVENIRE IN QUALUNQUE MOMENTO, BISOGNERÀ MONITORARE COSTANTEMENTE I LORO "PERMESSI" COSÌ DA IMPEDIRNE LA NAVIGAZIONE VERSO LE SEZIONI PIÙ SENSIBILI DEL SITO 
	require_once("./monitoraggio_stato_account.php");
	
	// LE PROPOSTE DI VENDITA, DATA LA LORO STRUTTURA, POTREBBERO ESSERE SOGGETTE A DELLE RIDUZIONI DI PREZZO, CARATTERIZZATE DA UN INTERVALLO DI VALIDITÀ BEN DEFINITO E TALVOLTA APPLICABILI SUL PROSSIMO ACQUISTO. PERTANTO, NELLE IPOTESI IN CUI NON SIA SEMPRE POSSIBILE RIMUOVERE MANUALMENTE LE VOCI INTERESSATE, ABBIAMO DECISO DI IMPLEMENTARE UN CODICE IN GRADO DI INTERVENIRE IN AUTOMATICO SU TUTTI QUELLI SCONTI NON PIÙ USUFRUIBILI DAI CLIENTI DELLA PIATTAFORMA
	require_once("./rimozione_sconti.php");
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>LEV: Libri &amp; Videogiochi</title>
		<link rel="icon" href="../../Immagini/Icona_LEV.png" />
		<link rel="stylesheet" href="../../Stili CSS/style_contatti.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_intestazione_sito.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_common.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_footer_sito.css" type="text/css" />
		<script type="text/javascript" src="../JavaScript/gestioneMenuTendina.js"></script>
	</head>
	<body>
		<?php 
			// L'INTESTAZIONE DEL SITO, POICHÈ SI TRATTA DI UN DETTAGLIO RICORRENTE, È STATA DEFINITA ALL'INTERNO DI UN "PROGRAMMA" INDIPENDENTE CHE, DATE LE ESIGENZE, VERRÀ INCLUSO A PIÙ RIPRESE NELLE VARIE PAGINE DELL'APPLICATIVO
			require_once("./intestazione_sito.php");  
		?>
		<div class="corpo_pagina">
			<div class="container_corpo_pagina">
				<div class="contatti">
					<div class="container_contatti">
						<div class="intestazione_contatti">
							<div class="container_intestazione_contatti">
								<span class="icona_contatti">
									<img src="../../Immagini/phone-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Contatti!</h2>
							</div>
						</div>
						<div class="contenuto_contatti">
							<div class="elenco_contatti">
								<div class="container_elenco_contatti">
									<h3>Informazioni</h3>
									<p>
										Potete trovarci tutti i giorni in <strong>Viale Le Corbiuser, n.39 - 04100 Latina</strong>, indicativamente dalle 09:00 alle 19:00.
									</p>
									<h3>Recapiti e Social</h3>
									<p>
										Che tu abbia bisogno di assistenza con un ordine, di maggiori informazioni sui prodotti, o di condividere un feedback, siamo a tua disposizione. Ecco come puoi metterti in contatto con noi:
									</p>
									<ul style="list-style: none;">
										<li>
											<div class="info">
												<img src="../../Immagini/user-gear-solid.svg" alt="Immagine Non Disponibile..." />
												<p>
													<strong>Ettore: 3451772123</strong>
												</p>
											</div>
										</li>
										<li>
											<div class="info" style="margin-top: -0.5em;">
												<img src="../../Immagini/envelope-solid.svg" alt="Immagine Non Disponibile..." />
												<p>
													<strong>cantile.2026562@studenti.uniroma1.it</strong> 
												</p>
											</div>
										</li>
										<li>
											<div class="info" style="margin-top: -0.5em;">
												<img src="../../Immagini/instagram.svg" alt="Immagine Non Disponibile..." />
												<p>
													<strong>ettorecantile</strong> 
												</p>
											</div>
										</li>
										<li>
											<div class="info">
												<img src="../../Immagini/user-gear-solid.svg" alt="Immagine Non Disponibile..." />
												<p>
													<strong>Leonardo: 3337279141</strong>
												</p>
											</div>
										</li>
										<li>
											<div class="info" style="margin-top: -0.5em;">
												<img src="../../Immagini/envelope-solid.svg" alt="Immagine Non Disponibile..." />
												<p>
													<strong>chiarparin.2016363@studenti.uniroma1.it</strong> 
												</p>
											</div>
										</li>
										<li>
											<div class="info" style="margin-top: -0.5em;">
												<img src="../../Immagini/instagram.svg" alt="Immagine Non Disponibile..." />
												<p>
													<strong>leonardochiarparin</strong> 
												</p>
											</div>
										</li>
									</ul>
								</div>
							</div>
							<div class="mappa">		
								<a class="container_mappa" title="LEV: Libri &amp; Videogiochi" href="https://www.google.it/maps/place/V.le+Le+Corbusier,+39,+04100+Latina+LT/@41.4650687,12.8856968,17z/data=!3m1!4b1!4m6!3m5!1s0x13250b848366c6d1:0x2baff641d9c5954b!8m2!3d41.4650647!4d12.8882717!16s%2Fg%2F11gjwwcr_d?entry=ttu">
									<img src="../../Immagini/Sede-Fisica.jpg" alt="Immagine Non Disponibile..." />
								</a>
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