<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO LE OFFERTE ATTUALMENTE PRESENTI NEL CARRELLO DI UN CERTO UTENTE (VISITATORE O CLIENTE)

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
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO AI CLIENTI DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="C") {
		header("Location: index.php");
	}
	
	// PRIMA DI PROCEDERE CON LA COMPOSIZIONE DELLA PAGINA, È NECESSARIO VALUTARE IL NUMERO DI VOCI CHE COMPONGONO IL CARRELLO DELL'UTENTE INTERESSATO. INFATTI, QUALORA SIANO GIÀ STATE CONSIDERATE, SI DOVRÀ STAMPARE UN MESSAGGIO CHE NOTIFICHERÀ L'ASSENZA DI ELEMENTI AL SUO INTERNO
	require_once("./calcolo_offerte_carrello.php");
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_carrelli.php");
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_piattaforme_videogiochi.php");
	
	// NEL CASO IN CUI CI SIANO DELLE OFFERTE ALL'INTERNO DEL CARRELLO, SARÀ NECESSARIO DETERMINARNE IL SUBTOTALE, OVVERO LA SOMMA DEI PREZZI DELLE SINGOLE PROPOSTE DI VENDITA A CUI SONO STATI APPLICATI I RELATIVI SCONTI "INTERNI"
	if($num_prodotti_carrello!=0) {
	
		// AL FINE DI RIPORTARE LE INFORMAZIONI INERENTI ALLE PROPOSTE DI VENDITA PRESENTI ALL'INTERNO DEL CARRELLO DI UN CERTO UTENTE, SI DOVRÀ DISCRIMINARE L'ENTITÀ DA CUI POTERLE EFFETTIVAMENTE REPERIRE
		$subtotale=0.00;
		
		if(isset($_SESSION["id_Utente"])) {
			
			for($i=0; $i<$carrelli->length; $i++) {
				$carrello=$carrelli->item($i);
				
				if($carrello->getAttribute("idCliente")==$_SESSION["id_Utente"])
					break;
			}
			
			for($i=0; $i<$carrello->getElementsByTagName("offerta")->length; $i++) {
				$offerta=$carrello->getElementsByTagName("offerta")->item($i);
				
				if($offerta->getElementsByTagName("scontoATempo")->length!=0)
					$subtotale=number_format(intval($offerta->getElementsByTagName("quantitativo")->item(0)->textContent)*(floatval($offerta->firstChild->textContent) - (floatval($offerta->firstChild->textContent)*(floatval($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"))/100))) + $subtotale, 2,".","");
				else
					$subtotale=number_format(intval($offerta->getElementsByTagName("quantitativo")->item(0)->textContent)*(floatval($offerta->firstChild->textContent)) + $subtotale, 2,".","");  
			}
		}
		else {
			if(isset($_COOKIE["carrello_Offerte"])) {
				
				// ***
				$carrello=unserialize($_COOKIE["carrello_Offerte"]);
				
				foreach($carrello as $offerta) {
					
					if(array_key_exists("scontoATempo", $offerta))
						$subtotale=number_format($offerta["quantitativo"]*($offerta["prezzoContabile"] - (($offerta["prezzoContabile"]*$offerta["scontoATempo"])/100)) + $subtotale, 2,".","");
					else
						$subtotale=number_format($offerta["quantitativo"]*$offerta["prezzoContabile"] + $subtotale, 2,".",""); 	
				}
			}
		}
	}
	
	// UNA VOLTA SELEZIONATA L'ELEMENTO DA GESTIRE, BISOGNERÀ IDENTIFICARE LA PROPOSTA DI VENDITA DA RIMUOVERE DAL DOCUMENTO 
	if(isset($_GET["id_Offerta"])) {
			
		if(isset($_SESSION["id_Utente"])) {
			
			// PER DI PIÙ, BISOGNA IMPEDIRE CHE VENGA INSERITO UN VALORE INERENTE AD UN'OFFERTA INESISTENTE
			if(in_array($_GET["id_Offerta"], range(1, $carrello->getElementsByTagName("offerta")->length))) {
				
				// QUALORA L'UTENTE ABBIA INTENZIONE DI RINUCIARE AD UNA DELLE PROPOSTE DI VENDITA PRESENTI ALL'INTERNO DEL PROPRIO CARRELLO, SI PROCEDERÀ CON IL REINSERIMENTO DEI SINGOLI PEZZI IN CORRISPONDENZA DELL'OFFERTA PIÙ RECENTE E AD ESSA RICONDUCIBILE TRAMITE IL PROPRIO IDENTIFICARE O QUELLO DEL PRODOTTO A CUI SI RIFERISCE. INOLTRE, NEL CASO IN CUI L'OFFERTA IN QUESTIONE NON SIA PIÙ PRESENTE ALL'INTERNO DEL CATALOGO, GLI ELEMENTI CHE LA COMPONGONO VERRANNO UTILIZZATI PER CREARLA NUOVAMENTE ED INSERIRLA ALL'INTERNO DEL CATALOGO, IN CORRISPONDENZA DELLA POSIZIONE OCCUPATA "IN PASSATO" MEDIANTE IL METODO insertBefore(...)
				// N.B.: LA SECONDA CASISTICA ILLUSTRATA RISULTA FATTIBILE POICHÈ, OLTRE A NON AVER INDIVIDUATO ALCUNA CORRISPONDENZA CON GLI INDICI DELLE PROPOSTE DI VENDITA NEL CATALOGO, IL MECCANISMO PER L'INSERIMENTO DI NUOVE OFFERTE NON PREVEDE L'UTILIZZO DEGLI IDENTIFICATORI ATTRIBUITI IN PRECEDENZA, PERTANTO NON SI POTRANNO MAI VERIFICARE DELLE PROBLEMATICHE INERENTI ALL'INCONGRUENZA DI QUALCHE ENTITÀ 
				$offerta_presente=false;
				
				for($i=0; $i<$offerte->length && !$offerta_presente; $i++) {
					$offerta=$offerte->item($i);
					
					if($offerta->getAttribute("id")==$carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getAttribute("id") || $offerta->getAttribute("idProdotto")==$carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getAttribute("idProdotto")) {
						$offerta->replaceChild($docOfferte->createElement("quantitativo", intval($offerta->getElementsByTagName("quantitativo")->item(0)->textContent)+intval($carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getElementsByTagName("quantitativo")->item(0)->textContent)), $offerta->getElementsByTagName("quantitativo")->item(0));
						$offerta_presente=true;
					}
				}
				
				if(!$offerta_presente) {
					$nuova_offerta=$docOfferte->createElement("offerta");
					$nuova_offerta->setAttribute("id", $carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getAttribute("id"));
					$nuova_offerta->setAttribute("idProdotto", $carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getAttribute("idProdotto"));
					
					$nuova_offerta->appendChild($docOfferte->createElement("prezzoContabile",$carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getElementsByTagName("prezzoContabile")->item(0)->textContent));
					
					if($carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getElementsByTagName("sconto")->length!=0) {
						
						$nuovo_sconto=$docOfferte->createElement("sconto");
						
						if($carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getElementsByTagName("scontoATempo")->length!=0) {
							$scontoATempo=$docOfferte->createElement("scontoATempo");
							$scontoATempo->setAttribute("percentuale", $carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"));
							$scontoATempo->setAttribute("inizioApplicazione", $carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione"));
							$scontoATempo->setAttribute("fineApplicazione", $carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione"));
							$nuovo_sconto->appendChild($scontoATempo);
						}
						else {
							$scontoFuturo=$docOfferte->createElement("scontoFuturo");
							$scontoFuturo->setAttribute("percentuale", $carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale"));
							$scontoFuturo->setAttribute("inizioApplicazione", $carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione"));
							$scontoFuturo->setAttribute("fineApplicazione", $carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione"));
							$nuovo_sconto->appendChild($scontoFuturo);
						}
						
						$nuova_offerta->appendChild($nuovo_sconto);
						
					}
					
					$nuova_offerta->appendChild($docOfferte->createElement("quantitativo", $carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getElementsByTagName("quantitativo")->item(0)->textContent));
					
					if($carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getElementsByTagName("bonus")->length!=0) {
						
						$nuovo_bonus=$docOfferte->createElement("bonus");
						
						$nuovo_bonus->appendChild($docOfferte->createElement("numeroCrediti", $carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1)->getElementsByTagName("bonus")->firstChild->textContent));
						
						$nuova_offerta->appendChild($nuovo_bonus);
						
					}
					
					$inserimento_effettuato=false;
					
					for($i=0; $i<$offerte->length && !$inserimento_effettuato; $i++) {
						$offerta=$offerte->item($i);
						
						if($offerta->getAttribute("id")>$nuova_offerta->getAttribute("id")) {
							$rootOfferte->insertBefore($nuova_offerta, $offerta);
							
							$inserimento_effettuato=true;
						}
					}
					
					// SE NON È STATA INDIVIDUATA ALCUNA COMPONENTE "SUCCESSIVA" PER INDICE A QUELLA D'INTERESSE, SARÀ SUFFICIENTE EFFETTUARE UN SEMPLICE INSERIMENTO IN CODA
					if(!$inserimento_effettuato) {
						$rootOfferte->appendChild($nuova_offerta);
					}
					
				}
				
				// IN OGNI CASO, SARÀ NECESSARIO RIMUOVERE DAL CARRELLO LA PROPOSTA DI VENDITA SELEZIONATA
				$carrello->removeChild($carrello->getElementsByTagName("offerta")->item($_GET["id_Offerta"]-1));
				
				// GIUNTI A QUESTO PUNTO, SI PROCEDE CON IL SALVATAGGIO DEL CONTENUTO DEI DOCUMENTI APPENA AGGIORNATI
				if($docOfferte->schemaValidate("../../XML/Schema/Offerte.xsd") && $docCarrelli->schemaValidate("../../XML/Schema/Carrelli_Clienti.xsd")){
					
					// ***
					$docCarrelli->preserveWhiteSpace = false;
					$docCarrelli->formatOutput = true;
					$docCarrelli->save("../../XML/Carrelli_Clienti.xml");
					
					$docOfferte->preserveWhiteSpace = false;
					$docOfferte->formatOutput = true;
					$docOfferte->save("../../XML/Offerte.xml");
					
					// PRIMA DI ESSERE REINDIRIZZATI, SI PREDISPONE UNA VARIABILE UN COOKIE CHE SARÀ USATO COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
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
		else {
			if(isset($_COOKIE["carrello_Offerte"])) {
		
				// ***
				if(in_array($_GET["id_Offerta"], range(1, count($carrello)))) {
					
					// NEL CASO IN CUI IL CARRELLO SIA RAPPRESENTATO DAL COOKIE, SI HA, A SEGUITO DELLA RIMOZIONE DI UNA CERTA COMPONENTE, LA NECESSITÀ DI RIORDINARE GLI ELEMENTI PRESENTI ALL'INTERNO DEL RELATIVO VETTORE
					// N.B.: POICHÈ SI TRATTA DI UN ELEMENTO TEMPORANEO E FITTIZIO, TUTTI I QUANTITATIVI INERENTI ALLE PROPOSTE DI VENDITA RIMOSSE NON DOVRANNO ESSERE REINSERITI ALL'INTERNO DI QUELLE DEL CATALOGO
					
					// PRIMA DI POTER INTERAGIRE CON IL VETTORE RAFFIGURANTE LE PROPOSTE DI VENDITA INSERITE NEL CARRELLO DA UN SEMPLICE VISITATORE, BISOGNA FARE IN MODO DA INDICIZZARLE NUMERICAMENTE, IN QUANTO, CONTRARIAMENTE AD ALTRI SCENARI, NON SI HA A DISPOSIZIONE UNA COMPONENTE CON CUI È POSSIBILE INTERAGIRE MEDIANTE IL METODO array_search(...)
					$carrello=array_values($carrello);
					
					// ***
					unset($carrello[$_GET["id_Offerta"]-1]);
					
					$carrello=array_values($carrello);
					
					// QUALORA IL CARRELLO RISULTI VUOTO, SARÀ POSSIBILE RIMUOVERE IL COOKIE D'INTERESSE. AL CONTRARIO, BISOGNERÀ AGGIORNARNE IL CONTENUTO
					if(count($carrello)!=0) {
						setcookie("carrello_Offerte", serialize($carrello));
					}
					else {
						setcookie("carrello_Offerte", "", time()-60);
					}
					
					// ***
					setcookie("modifica_Effettuata", true);
						
					header("Location: index.php");
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
			// DATA LA VARIETÀ DELLE CASISTICHE CHE POSSONO MANIFESTARE, ABBIAMO DEFINITO UNA GERARCHIA DI CONTROLLI PER GESTIRE AL MEGLIO LE STAMPE DEI VARI MESSAGGI DI POPUP, I QUALI, MEDIANTE UNA SIMILE IMPOSTAZIONE, SARANNO PRESENTATI SINGOLARMENTE AI SOGGETTI INTERESSATI
			if(isset($_COOKIE["modifica_Effettuata"]) && $_COOKIE["modifica_Effettuata"]) {
				
				// IN OCCASIONE DEL POSSIBILE RICARICAMENTO DELLA PAGINA, SI PROCEDE CON LA RIMOZIONE DEL FLAG AL SOLO SCOPO DI NON RIPROPORRE LA MEDESIMA NOTIFCA 
				setcookie("modifica_Effettuata","",time()-60);
				
				echo "<div class=\"confirm_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/check-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"con\">OTTIMO!</p>\n";
				echo "\t\t\t\t\t<p>INSERIMENTO AVVENUTO CON SUCCESSO!</p>\n";
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
									<img src="../../Immagini/bag-shopping-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Carrello!</h2>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<?php
									// IN BASE AL NUMERO DI PRODOTTI DA ESAMINARE, SARÀ POSSIBILE STABILIRE COSA PRESENTARE A SCHERMO
									if($num_prodotti_carrello==0) {
										echo "<span class=\"nessun_elemento\">Non &egrave; stato trovato nessun elemento con le specifiche di interesse.</span>\n";
									}
									else {		
										echo "<div class=\"elenco_campi\">\n";
										echo "\t\t\t\t\t\t\t\t\t<div class=\"container_elenco_campi\" style=\"border: 0em; padding-bottom: 0em; margin-bottom: 0em;\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<div class=\"container_campo\" style=\"margin-left: 0em; margin-right: 0em;\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"dettagli_ordine\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"container_dettagli_ordine\">\n";			
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"intestazione_dettagli_ordine\">\n";				
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<h3>Riferimenti Temporali</h3>\n";					
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"corpo_dettagli_ordine\" style=\"overflow: auto; text-overflow: unset; white-space: normal;\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<ul class=\"container_corpo_dettagli_ordine\">\n";										
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<li><span>La consegna dei singoli articoli &egrave; <span style=\"font-weight: bold;\">PREVISTA</span> per le <span style=\"font-weight: bold;\">24</span> o le <span style=\"font-weight: bold;\">48 ore</span> successive all'acquisto</span></li>\n";								
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</ul>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"intestazione_dettagli_ordine\">\n";				
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<h3>Informazioni Aggiuntive</h3>\n";					
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"corpo_dettagli_ordine\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<ul class=\"container_corpo_dettagli_ordine\">\n";										
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<li><span style=\"font-weight:bold;\">Pagamento</span> <span>Crediti</span></li>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<li><span style=\"font-weight:bold;\">Spedizione</span> <span>Corriere LEV</span></li>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<li><span style=\"font-weight:bold;\">Venditore</span> <span>LEV</span></li>\n";							
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</ul>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"intestazione_dettagli_ordine\">\n";				
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<h3>Sommario (Provvisorio)</h3>\n";					
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"corpo_dettagli_ordine\" style=\"overflow: auto; text-overflow: unset; white-space: normal;\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<ul class=\"container_corpo_dettagli_ordine\">\n";										
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<li><span>Contenuto</span> <span>".$num_prodotti_carrello." Art.</span></li>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<li style=\"font-weight:bold;\"><span>Subtotale</span> <span>".number_format($subtotale, 2,".","")." Cr</span></li>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<li style=\"font-weight:bold; color: rgb(119, 119, 119); font-size: 0.875em; margin-top: 0.875em;\"><span>Il risultato mostrato non tiene conto di Sconti, Promozioni o ulteriori agevolazioni personali</span></li>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</ul>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t<div class=\"intestazione_sezione\">\n";
										echo "\t\t\t\t\t\t\t\t\t<div class=\"container_intestazione_sezione\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t<span>Riepilogo Contenuto</span>\n"; 
										echo "\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t<div class=\"elenco_campi\">\n";
										echo "\t\t\t\t\t\t\t\t\t<div class=\"container_elenco_campi\" style=\"border: 0em; padding-bottom: 0em; margin-bottom: 0em;\" >\n";
										echo "\t\t\t\t\t\t\t\t\t\t<div class=\"campo\">\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t<div class=\"container_campo\">\n";

										// L'INTESTAZIONE DELLA TABELLA SARÀ UN FATTORE STATICO IN CUI VERRANNO PRESENTATI TUTTI GLI ELEMENTI, DEFINITI COME COLONNE, CHE LA COMPORRANNO 
										echo "\t\t\t\t\t\t\t\t\t\t\t<table>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<thead>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<tr>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Quantit&agrave;</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Articolo <strong style=\"color: rgb(217, 118, 64);\" title=\"ricerca le offerte che coinvolgono i vari prodotti premendo il loro nome\">*</strong></th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Info.</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Prezzo <strong style=\"color: rgb(217, 118, 64);\" title=\"(Cr.) &amp; al lordo di un'eventuale sconto\">*</strong></th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Sconto <strong style=\"color: rgb(217, 118, 64);\" title=\"(%)\">*</strong></th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Bonus <strong style=\"color: rgb(217, 118, 64);\" title=\"(Cr.)\">*</strong></th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Azione</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</tr>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t</thead>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t\t<tbody>\n";
										
										// IL CORPO DELLA COMPONENTE DI CUI SOPRA VERRÀ COMPOSTO DINAMICAMENTE E SEGUENDO L'ORDINE INDOTTO DALLE PRECEDENTI STAMPE
										if(isset($_SESSION["id_Utente"])) {
											
											for($i=0; $i<$carrello->getElementsByTagName("offerta")->length; $i++){
												$offerta=$carrello->getElementsByTagName("offerta")->item($i);
												
												// PRIMA DI OGNI ALTRO DETTAGLIO, BISOGNA RICERCARE IL NOMINATIVO DEL PRODOTTO PER CUI È STATA DEFINITA LA PROPOSTA DI VENDITA IN QUESTIONE
												for($j=0; $j<$prodotti->length; $j++) {
													$prodotto=$prodotti->item($j);
													
													if($prodotto->getAttribute("id")==$offerta->getAttribute("idProdotto"))
														break;
													
												}
												
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<tr>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta->getElementsByTagName("quantitativo")->item(0)->textContent." <small>x</small></td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\"><a href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$prodotto->firstChild->textContent."\" class=\"offerta\">".$prodotto->firstChild->textContent."</a></td>\n";
												
												// A SEGUITO DI CIÒ, È STATO PREDISPOSTO UN CONTROLLO CHE, IN BASE ALLA NATURA DELL'ARTICOLO, RIPORTERÀ DEI DETTAGLI LORO DEDICATI, QUALI PIATTAFORME E CASE DI PRODUZIONE O AUTORI  
												if($prodotto->getElementsByTagName("videogioco")->length!=0) {
													
													for($j=0; $j<$piattaforme->length; $j++) {
														$piattaforma=$piattaforme->item($j);
														
														if($piattaforma->getAttribute("id")==$prodotto->getElementsByTagName("videogioco")->item(0)->firstChild->getAttribute("idPiattaforma")) 
															break;
													}
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\"><span>".$piattaforma->firstChild->textContent."; <span style=\"font-weight: bold;\">".$prodotto->getElementsByTagName("casaProduzione")->item(0)->textContent."</span></span></td>\n";
												}
												else {
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">";
													
													for($j=0; $j<$prodotto->getElementsByTagName("autore")->length; $j++) {
														$autore=$prodotto->getElementsByTagName("autore")->item($j);
														
														if($j<$prodotto->getElementsByTagName("autore")->length-1)
															echo "<span style=\"font-weight: bold;\">".$autore->firstChild->textContent." ".$autore->lastChild->textContent."</span>; ";
														else
															echo "<span style=\"font-weight: bold;\">".$autore->firstChild->textContent." ".$autore->lastChild->textContent."</span> ";
													}
													
													echo "</td>";
												}
												
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent."</td>\n";
												
												// PER CONCLUDERE, È DOVEROSO CONSIDERARE LE RIDUZIONI DEL PREZZO E DEI CREDITI BONUS CHE CARATTERIZZANO L'OFFERTA IN QUESTIONE
												if($offerta->getElementsByTagName("sconto")->length!=0) {
													if($offerta->getElementsByTagName("scontoATempo")->length!=0) 
														echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale")."<strong style=\"color: rgb(217, 118, 64);\" title=\"sull'offerta corrente\">*</strong></td>\n";
													else
														echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale")."<strong style=\"color: rgb(217, 118, 64);\" title=\"sul prossimo acquisto\">*</strong></td>\n";
												}
												else
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">0.00</td>\n";
												
												if($offerta->getElementsByTagName("bonus")->length!=0) 
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta->getElementsByTagName("bonus")->item(0)->firstChild->textContent."</td>\n";
												else
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">0.00</td>\n";
												
												// PER PERMETTERE AGLI UTENTI DI RIMUOVERE DELLE PROPOSTE DI VENDITA, BISOGNERÀ INSERIRE UN PUSLANTE CHE, TRAMITE METODO GET, SARÀ IN GRADO DI RIPORTARE L'IDENTIFICATORE DELL'ELEMENTO D'INTERESSE, IL QUALE, DATA LA POSSIBILE DUPLICAZIONE DEGLI STESSI IDENTIFICATORI, COINCIDERÀ CON LA SUA POSIZIONE ALL'INTERNO DEL RELATIVO CARRELLO
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"pulsante_td\">\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"riepilogo_carrello.php?id_Offerta=".($i+1)."\" class=\"container_pulsante_td back\" title=\"Elimina...\"><img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t</td>\n";
												
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</tr>\n";
												
											}	
										}
										else {
											if(isset($_COOKIE["carrello_Offerte"])) {
												
												// ***
												$i=1;
												
												foreach($carrello as $offerta){
													
													// ***
													for($j=0; $j<$prodotti->length; $j++) {
														$prodotto=$prodotti->item($j);
														
														if($prodotto->getAttribute("id")==$offerta["idProdotto"])
															break;
														
													}
													
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<tr>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta["quantitativo"]." <small>x</small></td>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\"><a href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$prodotto->firstChild->textContent."\" class=\"offerta\">".$prodotto->firstChild->textContent."</a></td>\n";
													
													// ***
													if($prodotto->getElementsByTagName("videogioco")->length!=0) {
														
														for($j=0; $j<$piattaforme->length; $j++) {
															$piattaforma=$piattaforme->item($j);
															
															if($piattaforma->getAttribute("id")==$prodotto->getElementsByTagName("videogioco")->item(0)->firstChild->getAttribute("idPiattaforma")) 
																break;
														}
														echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\"><span>".$piattaforma->firstChild->textContent."; <span style=\"font-weight: bold;\">".$prodotto->getElementsByTagName("casaProduzione")->item(0)->textContent."</span></span></td>\n";
													}
													else {
														echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">";
														
														for($j=0; $j<$prodotto->getElementsByTagName("autore")->length; $j++) {
															$autore=$prodotto->getElementsByTagName("autore")->item($j);
															
															if($j<$prodotto->getElementsByTagName("autore")->length-1)
																echo "<span style=\"font-weight: bold;\">".$autore->firstChild->textContent." ".$autore->lastChild->textContent."</span>; ";
															else
																echo "<span style=\"font-weight: bold;\">".$autore->firstChild->textContent." ".$autore->lastChild->textContent."</span> ";
														}
														
														echo "</td>";
													}
													
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta["prezzoContabile"]."</td>\n";
													
													// *** 
													if(array_key_exists("scontoATempo", $offerta) xor array_key_exists("scontoFuturo", $offerta)) {
														if(array_key_exists("scontoATempo", $offerta)) 
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta["scontoATempo"]."<strong style=\"color: rgb(217, 118, 64);\" title=\"sull'offerta corrente\">*</strong></td>\n";
														else
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta["scontoFuturo"]."<strong style=\"color: rgb(217, 118, 64);\" title=\"sul prossimo acquisto\">*</strong></td>\n";
													}
													else
														echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">0.00</td>\n";
													
													if(array_key_exists("numeroCrediti", $offerta)) 
														echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta["numeroCrediti"]."</td>\n";
													else
														echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">0.00</td>\n";
													
													// ***
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"pulsante_td\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"riepilogo_carrello.php?id_Offerta=".$i."\" class=\"container_pulsante_td back\" title=\"Elimina...\"><img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t</td>\n";
													
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</tr>\n";
													
													$i++;
													
												}
												
											}
										}
										echo "\t\t\t\t\t\t\t\t\t\t\t\t</tbody>\n";
										echo "\t\t\t\t\t\t\t\t\t\t\t</table>\n";
										
										echo "\t\t\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t\t</div>\n";
										echo "\t\t\t\t\t\t\t\t</div>\n";
									}
									
									if($num_prodotti_carrello!=0)
										echo "\t\t\t\t\t\t\t\t<div class=\"pulsante\" style=\"justify-content: center; margin-bottom: 0%;\">\n";
									else
										echo "\t\t\t\t\t\t\t\t<div class=\"pulsante\" style=\"justify-content: center; margin-bottom: -0.5%; margin-top: 2.5%;\">\n";
								
								?>
									<form action="index.php" method="post">
										<p>
											<button type="submit" class="container_pulsante back" style="margin-left: 1.5em; margin-right: 1.5em; padding: 0em;">Indietro!</button>
										</p>
									</form>
									<?php
										if($num_prodotti_carrello!=0) {
											
											// STANDO A QUANTO RIPORTATO NELLA RELAZIONE DI ACCOMPAGNAMENTO AL PROGETTO, LA PAGINA CONTENENTE TUTTI I DETTAGLI DELL'ORDINE, COSÌ COME LA FUNZIONALITÀ PER CONFERMARNE L'ACQUISTO, SARÀ ACCESSIBILE SOLTANTO AI CLIENTI CHE SI SONO GIÀ AUTENTICATI
											if(isset($_SESSION["id_Utente"]) && $_SESSION["tipo_Utente"]=="C")
												echo "\t\t\t\t\t\t\t\t\t<form action=\"conferma_acquisto_ordine.php\" method=\"post\">\n";
											else
												echo "\t\t\t\t\t\t\t\t\t<form action=\"login.php\" method=\"post\">\n";
											
											echo "\t\t\t\t\t\t\t\t\t\t<p>\n";
											echo "\t\t\t\t\t\t\t\t\t\t\t<button type=\"submit\" class=\"container_pulsante\" style=\"margin-left: 1.5em; margin-right: 1.5em; padding: 0em;\">Continua!</button>\n";
											echo "\t\t\t\t\t\t\t\t\t\t</p>\n";
											echo "\t\t\t\t\t\t\t\t\t</form>\n";
										}
									?>
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