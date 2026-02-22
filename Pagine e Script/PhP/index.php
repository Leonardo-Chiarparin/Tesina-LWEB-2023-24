<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO GLI ELEMENTI CHE COMPONGO L'INTERFACCIA PRINCIPALE DEL SITO
	
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
		<link rel="stylesheet" href="../../Stili CSS/style_index.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_intestazione_sito.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_common.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_footer_sito.css" type="text/css" />
		<script type="text/javascript" src="../JavaScript/gestioneMenuTendina.js"></script>
	</head>
	<body>
		<?php 
			// DATA LA VARIETÀ DI CASISTICHE CHE SI POSSONO MANIFESTARE, ABBIAMO DECISO DI DEFINIRE UNA GERARCHIA DI CONTROLLI PER GESTIRE AL MEGLIO LE STAMPE DEI VARI MESSAGGI DI POPUP, I QUALI, MEDIANTE UNA SIMILE IMPOSTAZIONE, SARANNO PRESENTATI SINGOLARMENTE AI SOGGETTI INTERESSATI
			if(isset($_SESSION["accesso_Effettuato"])) {
					
					// IN OCCASIONE DEL POSSIBILE RICARICAMENTO DELLA PAGINA, SI PROCEDE CON LA RIMOZIONE DEL FLAG ALLO SCOPO DI NON RIPROPORRE LA MEDESIMA NOTIFCA 
					unset($_SESSION["accesso_Effettuato"]);
					
					echo "<div class=\"confirm_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/check-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"con\">BENVENUTO!</p>\n";
					echo "\t\t\t\t\t<p>APRI IL MEN&Ugrave; PER ACCEDERE AI VARI CONTENUTI RISERVATI!</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
			}
			
			// ***
			if(isset($_COOKIE["caricamento_Effettuato"]) && ($_COOKIE["caricamento_Effettuato"])){
				
				// ***
				setcookie("caricamento_Effettuato","",time()-60);
				
				echo "<div class=\"confirm_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/check-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"con\">OTTIMO!</p>\n";
				echo "\t\t\t\t\t<p>CARICAMENTO DELLE STRUTTURE DATI EFFETTUATO CON SUCCESSO!</p>\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t</div>\n";
				echo "\t\t</div>\n";
			}
			
			// ***
			if(isset($_COOKIE["nessuna_Offerta"]) && ($_COOKIE["nessuna_Offerta"])){
				
				// ***
				setcookie("nessuna_Offerta","",time()-60);
				
				echo "<div class=\"error_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"err\">ATTENZIONE!</p>\n";
				echo "\t\t\t\t\t<p>AL MOMENTO NON VI SONO DELLE OFFERTE DISPONIBILI PER I PRODOTTI SELEZIONATI...</p>\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t</div>\n";
				echo "\t\t</div>\n";
			}
			
			// ***
			if(isset($_COOKIE["errore_Validazione"]) && $_COOKIE["errore_Validazione"]) {
				// ***
				setcookie("errore_Validazione","",time()-60);
				unset($_COOKIE["errore_Validazione"]);
				
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
				if(isset($_COOKIE["modifica_Effettuata"]) && $_COOKIE["modifica_Effettuata"]) {
					// ***
					setcookie("modifica_Effettuata","",time()-60);
					
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
			}
			
			// L'INTESTAZIONE DEL SITO, POICHÈ SI TRATTA DI UN DETTAGLIO RICORRENTE, È STATA DEFINITA ALL'INTERNO DI UN PROGRAMMA INDIPENDENTE CHE, DATE LE ESIGENZE, VERRÀ INCLUSO A PIÙ RIPRESE NEI VARI SCRIPT
			require_once ("./intestazione_sito.php"); 
		?>
		<div class="corpo_pagina">
			<div class="container_corpo_pagina" style="background-color: rgb(56, 58, 58);">
				<div class="carosello_immagini">
					<div class="container_carosello_immagini">
						<div class="oggetti_carosello">
							<div class="oggetto_carosello">
								<div class="testo_oggetto_carosello">
									<h3>Elden Ring</h3>
									<p>L'avventura continua nelle Terre dell'Ombra!</p>
									<a href="elenco_risultati_ricerca.php?prodotto_Ricercato=Elden Ring">Scopri di pi&ugrave;!</a>
								</div>
								<div class="immagine_carosello">
									<img src="../../Immagini/Elden-Ring_SOTE.png" alt="Immagine Non Disponibile..." />
								</div>
							</div>
							<div class="oggetto_carosello">
								<div class="testo_oggetto_carosello">
									<h3>La Forma della Voce</h3>
									<p>Ascolta con il cuore e capirai!</p>
									<a href="elenco_risultati_ricerca.php?prodotto_Ricercato=La Forma della Voce">Scopri di pi&ugrave;!</a>
								</div>
								<div class="immagine_carosello">
									<img src="../../Immagini/A-Silent-Voice.png" alt="Immagine Non Disponibile..." />
								</div>
							</div>
							<div class="oggetto_carosello">
								<div class="testo_oggetto_carosello">
									<h3>The Last of Us</h3>
									<p>Riuscirai a far fronte all'epidemia?</p>
									<a href="elenco_risultati_ricerca.php?prodotto_Ricercato=The Last of Us">Scopri di pi&ugrave;!</a>
								</div>
								<div class="immagine_carosello">
									<img src="../../Immagini/The-Last-Of-Us_Parte-1.png" alt="Immagine Non Disponibile..." />
								</div>
							</div>
						</div>
						<ul class="indicatori_carosello">
							<li class="indicatore_carosello" id="indicatore_1"></li>
							<li class="indicatore_carosello" id="indicatore_2"></li>
							<li class="indicatore_carosello" id="indicatore_3"></li>
						</ul>
					</div>
				</div>
				<div class="presentazione">
					<div class="container_presentazione">
						<div class="intestazione_presentazione">
							<div class="container_intestazione_presentazione">
								<span class="icona_negozio">
									<img src="../../Immagini/store-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>LEV, il vostro punto di riferimento per libri e videgiochi!</h2>
							</div>
						</div>
						<div class="corpo_presentazione">
							<p>Da pi&ugrave; di 6 mesi, siamo esperti nella gestione e nella vendita dei migliori articoli rivolti al mondo dell'<strong>intrattenimento digitale</strong> e della <strong>lettura</strong>, offrendo un'ampia selezione di prodotti di alta qualit&agrave;. Che siate fan dei giochi d'azione, degli RPG, dei romanzi classici o delle ultime novit&agrave; letterarie, qui troverete tutto ci&ograve; che cercate. Esplorate il nostro <strong>catalogo</strong>, approfittate delle nostre <strong>offerte</strong> e lasciatevi ispirare dai nostri <strong>suggerimenti</strong>. <strong>LEV &egrave; pi&ugrave; di un semplice negozio</strong>: &egrave; una comunit&agrave; di appassionati che, prima di tutto, desiderano instaurare un ambiente in cui ci si può aiutare a vicenda. <strong>Buon divertimento e buona lettura!</strong></p>
						</div>
					</div>
				</div>
				<div class="elenco_prodotti">
					<div class="container_prodotti">
						<div class="intestazione_prodotti">
							<div class="container_intestazione_prodotti">
								<span class="icona_prodotti">
									<img src="../../Immagini/box-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Prodotti</h2>
							</div>
						</div>
						<div class="categorie_prodotti">
							<div class="categoria_prodotti">
								<div class="container_categoria_prodotti">
									<span class="anteprima" title="Slam Dunk">
										<img src="../../Immagini/Slam-Dunk.png" style="height: 150%; margin-top: 17.5%;" alt="Slam Dunk" />
									</span>
									<h3>Libri</h3>
									<a href="anteprima_libri.php">Vedi Tutto!</a>
								</div>
							</div>
							<div class="categoria_prodotti">
								<div class="container_categoria_prodotti">
									<span class="anteprima" title="Persona 5: Royal">
										<img src="../../Immagini/Persona-5_Royal.png" style="height: 125%; margin-top: -12.5%; " alt="Persona 5: Royal" />
									</span>
									<h3>Videogiochi</h3>
									<a href="anteprima_videogiochi.php">Vedi Tutto!</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="funzionamento">
					<div class="container_funzionamento">
						<div class="intestazione_funzionamento">
							<div class="container_intestazione_funzionamento">
								<span class="icona_funzionamento">
									<img src="../../Immagini/circle-info-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Come si utilizza LEV?</h2>
							</div>
						</div>
						<div class="illustrazione_funzionamento">
							<p>LEV, la piattaforma che permette l'acquisto di opere cartacee e videoludiche in maniera <strong>semplice</strong>, <strong>sicura</strong> e <strong>conveniente</strong>! <strong>Registrati</strong> e <strong>accedi</strong> a tutti i nostri servizi mediante le relative voci di men&ugrave; e le pagine loro riservate. Non perdere l'occasione di entrare in possesso degli articoli che preferisci! Per le tue scelte, sarai sempre indirizzato dalla disposizione dei vari elementi e da un <strong>supporto di esperti del settore</strong>.</p>
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