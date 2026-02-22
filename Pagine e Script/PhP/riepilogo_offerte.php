<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTE LE OFFERTE ATTUALMENTE PRESENTI ALL'INTERNO DEL CATALOGO

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
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO AI GESTORI DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="G") {
		header("Location: area_riservata.php");
	}
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_prodotti.php");
	
	// UNA VOLTA SELEZIONATO L'ELEMENTO DA GESTIRE, BISOGNERÀ IDENTIFICARE LA PROPOSTA DI VENDITA DA RIMUOVERE DAL DOCUMENTO 
	if(isset($_GET["id_Offerta"])) {
		
		// PER DI PIÙ, BISOGNA IMPEDIRE CHE VENGA INSERITO UN VALORE INERENTE AD UN'OFFERTA INESISTENTE
		$offerta_individuata=false;
		
		for($i=0; $i<$offerte->length; $i++) {
			$offerta=$offerte->item($i);
			
			if($offerta->getAttribute("id")==$_GET["id_Offerta"]) {
				$offerta_individuata=true;
				break;
			}
		}
		
		if($offerta_individuata) {
			$rootOfferte->removeChild($offerta);
			
			// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
			if($docOfferte->schemaValidate("../../XML/Schema/Offerte.xsd")){
				
				// AL FINE DI GARANTIRE UNA STAMPA GODIBILE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
				$docOfferte->preserveWhiteSpace = false;
				$docOfferte->formatOutput = true;
				$docOfferte->save("../../XML/Offerte.xml");
				
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
			if(isset($_SESSION["nessun_Prodotto"])) {
				// IN OCCASIONE DEL POSSIBILE RICARICAMENTO DELLA PAGINA, SI PROCEDE CON LA RIMOZIONE DEL FLAG ALLO SCOPO DI NON RIPROPORRE LA MEDESIMA NOTIFCA 
				unset($_SESSION["nessun_Prodotto"]);
				
				echo "<div class=\"error_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
				echo "\t\t\t\t\t<p>PER OGNI ARTICOLO ESISTE GI&Agrave; UN'OFFERTA DI RIFERIMENTO...</p>\n";
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
									<img src="../../Immagini/hand-holding-dollar-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Aggiungi, modifica o elimina le proposte di vendita!</h2>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<?php
									// L'INTESTAZIONE DELLA TABELLA SARÀ UN FATTORE STATICO IN CUI VERRANNO PRESENTATI TUTTI GLI ELEMENTI, DEFINITI COME COLONNE, CHE LA COMPORRANNO
									echo "<table>\n";
									echo "\t\t\t\t\t\t\t\t<thead>\n";
									echo "\t\t\t\t\t\t\t\t\t<tr>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Offerta</th>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Prodotto</th>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Prezzo <strong style=\"color: rgb(217, 118, 64);\" title=\"(Cr.) &amp; calcolato tenendo conto della possibile riduzione di prezzo inerente all'offerta in questione\">*</strong></th>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Sconto <strong style=\"color: rgb(217, 118, 64);\" title=\"(%)\">*</strong></th>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Pezzi</th>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Bonus <strong style=\"color: rgb(217, 118, 64);\" title=\"(Cr.)\">*</strong></th>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Azione</th>\n";
									echo "\t\t\t\t\t\t\t\t\t</tr>\n";
									echo "\t\t\t\t\t\t\t\t</thead>\n";
									echo "\t\t\t\t\t\t\t\t<tbody>\n";
									
									// LA PRIMA RIGA DELLA TABELLA CONTERRÀ UN PULSANTE PER PERMETTERE L'INSERIMENTO DI NUOVE OFFERTE
									echo "\t\t\t\t\t\t\t\t\t<tr>\n"; 
									echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">-</td>\n";
									echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">\n";
									echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"pulsante_td\">\n";
									echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"creazione_offerta.php\" class=\"container_pulsante_td\" title=\"Aggiungi!\"><img src=\"../../Immagini/plus-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
									echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
									echo "\t\t\t\t\t\t\t\t\t\t</td>\n";
									echo "\t\t\t\t\t\t\t\t\t</tr>\n";
									
									// IL CORPO DELLA COMPONENTE DI CUI SOPRA VERRÀ COMPOSTO DINAMICAMENTE E SEGUENDO L'ORDINE INDOTTO DALLE PRECEDENTI STAMPE
									for($i=0; $i<$offerte->length; $i++){
										$offerta=$offerte->item($i);
										
										// PER LA CARATTERIZZAZIONE DEL BENE A CUI SI RIFERISCE, È NECESSARIO SCANSIONARE IL RELATIVO FILE XML 
										for($j=0; $j<$prodotti->length; $j++){
											$prodotto=$prodotti->item($j);
											
											if($prodotto->getAttribute("id")==$offerta->getAttribute("idProdotto"))
												break;
										}
										
										echo "\t\t\t\t\t\t\t\t\t<tr>\n"; 
										echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta->getAttribute("id")."</td>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$prodotto->firstChild->textContent."</td>\n";
										
										// IN VIRTÙ DEL CONTENUTO DELLE SINGOLE PROPOSTE DI VENDITA, SI PROCEDE AL CALCOLO DELL'EVENTUALE PREZZO SCONTATO (SCONTO A TEMPO). LA FUNZIONE floatval SERVE PER CONVERTIRE LA STRINGA RESTITUITA DA ->textContent IN UN VALORE DECIMALE
										if($offerta->getElementsByTagName("scontoATempo")->length!=0) {
											$prezzoScontato=number_format(floatval($offerta->firstChild->textContent) - (floatval($offerta->firstChild->textContent)*(floatval($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"))/100)),2,".","");
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$prezzoScontato."</td>\n"; 
										}
										else
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta->firstChild->textContent."</td>\n"; 
										
										// PER DI PIÙ, SARÀ NECESSARIO VALUTARE SE L'OFFERTA IN QUESTIONE PRESENTA UNA CERTA PERCENTUALE DI SCONTO O MENO. LA DURATA TEMPORALE, GRAZIE ALLO SCRIPT CHE SI OCCUPA DI RIMUOVERE GLI SCONTI NON PIÙ VALIDI, NON VIENE MOSTRATA IN QUESTA PAGINA DI RIEPILOGO
										if($offerta->getElementsByTagName("sconto")->length!=0){
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta->getElementsByTagName("sconto")->item(0)->firstChild->getAttribute("percentuale");
										
											if($offerta->getElementsByTagName("sconto")->item(0)->getElementsByTagName("scontoFuturo")->length!=0) {
												echo " <strong style=\"color: rgb(217, 118, 64)\" title=\"sul prossimo acquisto\">*</strong>";
											}
											else {
												echo " <strong style=\"color: rgb(217, 118, 64)\" title=\"sull'offerta in oggetto\">*</strong>";
											}
											
											echo "</td>\n";
										}
										else{
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">0.00</td>\n";
										}
										
										// LA RICERCA DEI RESTANTI ASPETTI DEVE ESSERE REALIZZATA TRAMITE IL METODO "getElementsByTagName(...)" POICHÈ NON SI HA ALCUNA CERTEZZA IN MERITO ALLA LORO COLLOCAZIONE NEL FILE
										echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta->getElementsByTagName("quantitativo")->item(0)->textContent."</td>\n"; 
										
										// I RAGIONAMENTI ADOTTATI PER GLI SCONTI VENGONO RIPROPOSTI ANCHE PER I CREDITI BONUS
										if($offerta->getElementsByTagName("bonus")->length!=0)
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta->getElementsByTagName("bonus")->item(0)->firstChild->textContent."</td>\n";
										else
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">0.00</td>\n";
										
										echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"pulsante_td\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"modifica_offerta.php?id_Offerta=".$offerta->getAttribute("id")."\" class=\"container_pulsante_td\" title=\"Modifica!\"><img src=\"../../Immagini/gear-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"riepilogo_offerte.php?id_Offerta=".$offerta->getAttribute("id")."\" class=\"container_pulsante_td back\" title=\"Elimina...\"><img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
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
			require_once("./footer_sito.php");
		?>
	</body>
</html>