<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTI GLI ACQUISTI EFFETTUATI DAL CLIENTE INTERESSATO

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
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO AI CLIENTI DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="C")
		header ("Location: area_riservata.php");
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_acquisti.php");
	
	// PER DETERMINARE L'AMMONTARE DEGLI ACQUISTI DA RIPORTARE A SCHERMO, BISOGNERÀ SCANSIONARE IL DOCUMENTO IN CUI SONO CONTENUTI   
	// A TAL FINE, SARÀ NECESSARIO PREDISPORRE UN FLAG CHE, OLTRE AD ESSERE INIZIALIZZATO A FALSE, VERRÀ IMPOSTATO A TRUE UNA VOLTA INDIVIDUATI GLI EVENTUALI ACQUISTI EFFETTUATI DAL SOGGETTO IN QUESTIONE 
	$acquisti_individuati=false;
	
	for($i=0; $i<$acquisti->length; $i++) {
		$acquistiPerCliente=$acquisti->item($i);	
		
		if($acquistiPerCliente->getAttribute("idCliente")==$_SESSION["id_Utente"]) {
			if($acquistiPerCliente->getElementsByTagName("acquistoPerCliente")->length!=0) {
				$acquisti_individuati=true;
				break;
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
									<img src="../../Immagini/basket-shopping-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Tieni traccia di tutti gli ordini effettuati!</h2>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<?php
									// IN BASE AL NUMERO DI ACQUISTI DA ESAMINARE, SARÀ POSSIBILE STABILIRE COSA PRESENTARE A SCHERMO
									if($acquisti_individuati==false) {
										echo "<span class=\"nessun_elemento\">Non &egrave; stato trovato nessun elemento con le specifiche di interesse.</span>\n";
									}
									else {
										// L'INTESTAZIONE DELLA TABELLA SARÀ UN FATTORE STATICO IN CUI VERRANNO PRESENTATI TUTTI GLI ELEMENTI, DEFINITI COME COLONNE, CHE LA COMPORRANNO 
										echo "<table>\n";
										echo "\t\t\t\t\t\t\t\t<thead>\n";
										echo "\t\t\t\t\t\t\t\t\t<tr>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Acquisto</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Quantit&agrave; <strong style=\"color: rgb(217, 118, 64);\" title=\"il numero degli articoli che compongono l'ordine\">*</strong></th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Totale <strong style=\"color: rgb(217, 118, 64);\" title=\"(Cr.)\">*</strong></th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Consegna</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Azione</th>\n";
										echo "\t\t\t\t\t\t\t\t\t</tr>\n";
										echo "\t\t\t\t\t\t\t\t</thead>\n";
										echo "\t\t\t\t\t\t\t\t<tbody>\n";
										
										// IL CORPO DELLA COMPONENTE DI CUI SOPRA VERRÀ COMPOSTO DINAMICAMENTE E SEGUENDO L'ORDINE INDOTTO DALLE PRECEDENTI STAMPE
										for($i=0; $i<$acquistiPerCliente->getElementsByTagName("acquistoPerCliente")->length; $i++)
										{
											$acquistoPerCliente=$acquistiPerCliente->getElementsByTagName("acquistoPerCliente")->item($i);
											
											// PER OGNUNO DEGLI ACQUISTI, SARÀ NECESSARIO CALCOLARE IL QUANTITATIVO, OVVERO IL NUMERO DI PEZZI, DEGLI ARTICOLI INERENTI ALLE OFFERTE COINVOLTE
											$num_articoli=0;
											
											for($j=0; $j<$acquistoPerCliente->getElementsByTagName("offerta")->length; $j++)
												$num_articoli=$num_articoli + $acquistoPerCliente->getElementsByTagName("offerta")->item($j)->getElementsByTagName("quantitativo")->item(0)->textContent;
											
											
											echo "\t\t\t\t\t\t\t\t\t<tr>\n"; 
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$acquistoPerCliente->getAttribute("id")."</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$num_articoli."</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".number_format(floatval($acquistoPerCliente->getAttribute("prezzoTotale")),2,".","")."</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".date_format(date_create($acquistoPerCliente->getAttribute("dataConsegna")), "d/m/Y")."</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\"><span class=\"pulsante_td\"><a href=\"riepilogo_acquisto.php?id_Acquisto=".$acquistoPerCliente->getAttribute("id")."\" class=\"container_pulsante_td\" title=\"Visualizza!\"><img src=\"../../Immagini/info-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a></span></td>\n";
											echo "\t\t\t\t\t\t\t\t\t</tr>\n"; 
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
			require_once("./footer_sito.php");
		?>
	</body>
</html>