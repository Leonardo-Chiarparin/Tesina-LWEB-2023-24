<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTE LE INFORMAZIONI CONTENUTE ALL'INTERNO DELLA FAQ SELEZIONATA
	
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
	
	// LA PAGINA, ESSENDO RAGGIUNGIBILE DOPO AVER PREMUTO IL PULSANTE PER VISUALIZZARE UNA CERTA DOMANDA, NECESSITA DI UN ULTERIORE CONTROLLO PER APPURARE SE È STATA EFFETTIVAMENTE PRESA UNA DECISIONE IN MERITO O MENO 
	if(!isset($_GET["id_Faq"]))
		header("Location: riepilogo_faq.php");
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_faq.php");
	require_once("./apertura_file_discussioni.php");
	
	// NELL'OTTICA DI VOLER MANTENERE UN CERTO LIVELLO DI ROBUSTEZZA, ABBIAMO DECISO DI INTRODURRE DEI CONTROLLI PER VALUTARE SE LA FAQ A CUI SI RIFERISCE L'IDENTIFICATORE ESISTE REALMENTE O MENO
	$faq_individuata=false;
	
	for($i=0; $i<$faq->length && !$faq_individuata; $i++) {
		$singola_faq=$faq->item($i);
		
		// UNA VOLTA INDIVIDUATA LA DOMANDA DI INTERESSE, SI POTRÀ INTERROMPERE LA RICERCA, IN QUANTO L'ENTITÀ CHE LA RAPPRESENTA SARÀ IMPIEGATA ALL'INTERNO DI SUCCESSIVE OPERAZIONI
		if($singola_faq->getAttribute("id")==$_GET["id_Faq"]) {
			$faq_individuata=true;
		}
	}
	
	if($faq_individuata==false) {
		header("Location: riepilogo_faq.php");
	}
	
	// PRIMA DI PROCEDERE CON LA COMPOSIZIONE DELLA PAGINA, RISULTERÀ UTILE CONSIDERARE FIN DA SUBITO GLI ELEMENTI CHE CARATTERIZZANO LA FAQ COINVOLTA. INFATTI, QUALORA FACCIA RIFERIMENTO A DEGLI ELEMENTI "ESTERNI", BISOGNERÀ REPERIRNE IL CONTENUTO
	if($singola_faq->getElementsByTagName("discussioneEsistente")->length!=0 || $singola_faq->getElementsByTagName("interventoEsistente")->length!=0) {
		
		for($i=0; $i<$discussioni->length; $i++) {
			$discussione=$discussioni->item($i);
			
			if($singola_faq->getElementsByTagName("discussioneEsistente")->length!=0 && $singola_faq->getElementsByTagName("discussioneEsistente")->item(0)->getAttribute("idDiscussione")==$discussione->getAttribute("id")) {
				break;
			}
			else {
				if($singola_faq->getElementsByTagName("interventoEsistente")->length!=0 && $singola_faq->getElementsByTagName("interventoEsistente")->item(0)->getAttribute("idDiscussione")==$discussione->getAttribute("id")) {
					break;
				}
			}
		}
		
		if($singola_faq->getElementsByTagName("interventoEsistente")->length!=0) {
			
			for($i=0; $i<$discussione->getElementsByTagName("intervento")->length; $i++) {
				$intervento=$discussione->getElementsByTagName("intervento")->item($i);
				
				if($singola_faq->getElementsByTagName("interventoEsistente")->item(0)->getAttribute("idIntervento")==$intervento->getAttribute("id")) {
					break;
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
			require_once("./intestazione_sito.php");
		?>
		<div class="corpo_pagina">
			<div class="container_corpo_pagina">
				<div class="form">
					<div class="container_form">
						<div class="intestazione_form">
							<div class="container_intestazione_form">
								<span class="icona_form">
									<img src="../../Immagini/hand-holding-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Esamina il contenuto della FAQ selezionata!</h2>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Discussione (Informativo)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Titolo
											</p>
											<p>
												<?php
													if($singola_faq->getElementsByTagName("discussioneDaZero")->length!=0)
														echo "<input type=\"text\" disabled=\"disabled\" value=\"".$singola_faq->getElementsByTagName("titolo")->item(0)->textContent."\" />\n";
													else
														echo "<input type=\"text\" disabled=\"disabled\" value=\"".$discussione->getElementsByTagName("titolo")->item(0)->textContent."\" />\n";
												?>
											</p>		
										</div>
										<div class="campo_descrizione">
											<p>
												Descrizione
											</p>
											<p>
												<?php
													if($singola_faq->getElementsByTagName("discussioneDaZero")->length!=0)
														echo "<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$singola_faq->getElementsByTagName("descrizione")->item(0)->textContent."</textarea>\n";
													else
														echo "<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$discussione->getElementsByTagName("descrizione")->item(0)->textContent."</textarea>\n";
												?>
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> La precedente sezione si limita riportare le informazioni inerenti alla discussione che compone la FAQ di interesse.</p>		
									</div>
								</div>
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Intervento (Informativo)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo_descrizione">
											<p>
												Testo
											</p>
											<p>
												<?php
													if($singola_faq->getElementsByTagName("interventoDaZero")->length!=0)
														echo "<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$singola_faq->getElementsByTagName("testo")->item(0)->textContent."</textarea>\n";
													else
														echo "<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$intervento->getElementsByTagName("testo")->item(0)->textContent."</textarea>\n";
												?>
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> L'ultimo campo rappresenta la risposta fornita o indicata dall'amministratore per fare luce sulle tematiche trattate in precedenza.</p>		
									</div>
								</div>
								<div class="pulsante" style="justify-content: center;">
									<form action="riepilogo_faq.php" method="post">
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