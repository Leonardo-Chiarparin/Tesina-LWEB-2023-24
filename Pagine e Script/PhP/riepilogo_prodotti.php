<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTI PRODOTTI ATTUALMENTE PRESENTI ALL'INTERNO DEL MAGAZZINO

	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");

	// PER QUESTIONI DI SICUREZZA, È STATO NECESSARIO L'INSERIMENTO DELLO SCRIPT "private_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA. QUESTO MECCANISMO NON PERMETTERÀ L'ACCESSO AGLI UTENTI CHE NON SI AUTENTICATI TRAMITE IL RELATIVO SERVIZIO
	require_once("./private_session_control.php");
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");
	
	// LE PROPOSTE DI VENDITA, DATA LA LORO STRUTTURA, POTREBBERO ESSERE SOGGETTE A DELLE RIDUZIONI DI PREZZO, CARATTERIZZATE DA UN INTERVALLO DI VALIDITÀ BEN DEFINITO E TALVOLTA APPLICABILI SUL PROSSIMO ACQUISTO. PERTANTO, NELLE IPOTESI IN CUI NON SIA SEMPRE POSSIBILE RIMUOVERE MANUALMENTE LE VOCI INTERESSATE, ABBIAMO DECISO DI IMPLEMENTARE UN CODICE IN GRADO DI INTERVENIRE IN AUTOMATICO SU TUTTI QUELLI SCONTI NON PIÙ USUFRUIBILI DAI CLIENTI DELLA PIATTAFORMA
	require_once("./rimozione_sconti.php");
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO ALL'AMMINISTRATORE DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="A") {
		header("Location: area_riservata.php");
	}
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_prodotti.php");
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
									<img src="../../Immagini/boxes-stacked-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Aggiungi o modifica le voci dei prodotti in magazzino!</h2>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<?php
									// L'INTESTAZIONE DELLA TABELLA SARÀ UN FATTORE STATICO IN CUI VERRANNO PRESENTATI TUTTI GLI ELEMENTI, DEFINITI COME COLONNE, CHE LA COMPORRANNO
									echo "<table>\n";
									echo "\t\t\t\t\t\t\t\t<thead>\n";
									echo "\t\t\t\t\t\t\t\t\t<tr>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Articolo</th>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Nome</th>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Prezzo <strong style=\"color: rgb(217, 118, 64);\" title=\"(Cr.) &amp; di listino\">*</strong></th>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Anno <strong style=\"color: rgb(217, 118, 64);\" title=\"di uscita\">*</strong></th>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Tipo <strong style=\"color: rgb(217, 118, 64);\" title=\"libro (L) o videogioco (V)\">*</strong></th>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Azione</th>\n";
									echo "\t\t\t\t\t\t\t\t\t</tr>\n";
									echo "\t\t\t\t\t\t\t\t</thead>\n";
									echo "\t\t\t\t\t\t\t\t<tbody>\n";
									
									// LA PRIMA RIGA DELLA TABELLA CONTERRÀ UN PULSANTE PER PERMETTERE L'INSERIMENTO DI NUOVI PRODOTTI
									echo "\t\t\t\t\t\t\t\t\t<tr>\n"; 
									echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">\n";
									echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"pulsante_td\">\n";
									echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"inserimento_prodotto.php\" class=\"container_pulsante_td\" title=\"Aggiungi!\"><img src=\"../../Immagini/plus-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
									echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
									echo "\t\t\t\t\t\t\t\t\t\t</td>\n";
									echo "\t\t\t\t\t\t\t\t\t</tr>\n";
									
									// IL CORPO DELLA COMPONENTE DI CUI SOPRA VERRÀ COMPOSTO DINAMICAMENTE E SEGUENDO L'ORDINE INDOTTO DALLE PRECEDENTI STAMPE
									for($i=0; $i<$prodotti->length; $i++){
										$prodotto=$prodotti->item($i);
										
										echo "\t\t\t\t\t\t\t\t\t<tr>\n"; 
										echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$prodotto->getAttribute("id")."</td>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$prodotto->firstChild->textContent."</td>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$prodotto->getElementsByTagName("prezzoListino")->item(0)->textContent."</td>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$prodotto->getElementsByTagName("annoUscita")->item(0)->textContent."</td>\n";
										
										// POICHÈ LA PIATTAFORMA SI OCCUPA DI LIBRI E DI VIDEOGIOCHI, È NECESSARIO DISCRIMINARE A QUALE DELLE DUE PRECEDENTI TIPOLOGIE DI ARTICOLO SI STA FACENDO RIFERIMENTO 
										if($prodotto->getElementsByTagName("libro")->length!=0)
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">L</td>\n";
										else
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">V</td>\n";
										
										echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"pulsante_td\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"modifica_prodotto.php?id_Prodotto=".$prodotto->getAttribute("id")."\" class=\"container_pulsante_td\" title=\"Modifica!\"><img src=\"../../Immagini/gear-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</td>\n";
										echo "\t\t\t\t\t\t\t\t\t</tr>\n"; 
									}
									
									echo "\t\t\t\t\t\t\t\t</tbody>\n";
									echo "\t\t\t\t\t\t\t</table>\n";
										
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