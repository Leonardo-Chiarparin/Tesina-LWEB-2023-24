<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTE LE INFORMAZIONI UTILI AL FINE DI RENDERE PARTECIPI I VARI VISITATORI DELLA STORIA DELL'APPLICATIVO

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
		<link rel="stylesheet" href="../../Stili CSS/style_chi_siamo.css" type="text/css" />
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
				<div class="chi_siamo">
					<div class="container_chi_siamo">
						<div class="intestazione_chi_siamo">
							<div class="container_intestazione_chi_siamo">
								<span class="icona_chi_siamo">
									<img src="../../Immagini/clock-rotate-left-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Chi Siamo?</h2>
							</div>
						</div>
						<div class="storia">
							<div class="container_storia">
								<p>
									Benvenuti nella pagina dedicata alla storia del nostro e-commerce, <strong>un'avventura che ha preso vita solo sei mesi fa ma che ha gi&agrave; raggiunto traguardi significativi!</strong>
								</p>
								<h3>L'inizio di tutto</h3>
								<p>Abbiamo iniziato questa avventura con una visione chiara: creare un <strong>punto di riferimento</strong> per gli amanti dei videogiochi e dei libri, offrendo una selezione accurata e un'esperienza d'acquisto senza pari. Partendo da un'idea ambiziosa, abbiamo lanciato il nostro sito con <strong>determinazione</strong> e <strong>passione</strong>.</p>
								<div class="foto">
									<div class="container_foto" title="LEV all'inizio del suo viaggio">
										<img src="../../Immagini/Inizio-Di-Tutto.jpg" alt="Immagine Non Disponibile..." />
										<div class="memories">
											<p><strong>LEV</strong> all'inizio del suo viaggio</p>
										</div>
									</div>
								</div>
								<h3>Una crescita esponenziale</h3>
								<p>Nell'ultimo periodo, la nostra attivi&agrave; ha subito una notevole espansione. <strong>Grazie al costante supporto dei nostri clienti</strong>, abbiamo visto aumentare il numero di <strong>visitatori</strong> e <strong>acquirenti</strong>. Le <strong>recensioni positive</strong> e il <strong>passaparola</strong> ci hanno aiutato a raggiungere nuovi utenti e a consolidare la nostra reputazione nel settore. Proprio per questo, ci impegniamo ad offrire solo <strong>prodotti di alta qualit&agrave;</strong> e un <strong>servizio clienti impeccabile</strong>. Ogni giorno lavoriamo per <strong>migliorare la nostra piattaforma</strong>, aggiungere nuove funzionalit&agrave; e ampliare il nostro assortimento di offerte, sempre con l'obiettivo di <strong>soddisfare al meglio le esigenze dei nostri clienti</strong>.</p>
								<div class="foto">
									<div class="container_foto" title="LEV in costante miglioramento">
										<img src="../../Immagini/Una-Crescita-Esponenziale.jpg" alt="Immagine Non Disponibile..." />
										<div class="memories">
											<p><strong>LEV</strong> in costante miglioramento</p>
										</div>
									</div>
								</div>
								<h3>Prossimi Passi</h3>
								<p>Oltre a essere un negozio online, siamo una <strong>comunità di appassionati</strong>. Sosteniamo e incoraggiamo lo <strong>scambio di idee</strong> e la <strong>condivisione di esperienze</strong> tra i nostri clienti. Le vostre opinioni e suggerimenti sono fondamentali per <strong>guidare il nostro futuro</strong>. Infatti, sin dagli albori abbiamo avuto l'intenzione di crescere e innovarci volta per volta. Proprio per questo, stiamo gi&agrave; pianificando nuove iniziative per offrire un'<strong>esperienza ancora più gratificante</strong> ai nostri clienti.</p>
								<div class="foto">
									<div class="container_foto" title="LEV rivolta al futuro">
										<img src="../../Immagini/Prossimi-Passi.jpg" alt="Immagine Non Disponibile..." />
										<div class="memories">
											<p><strong>LEV</strong> rivolta al futuro</p>
										</div>
									</div>
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