<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php 
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO UN'ANTEPRIMA DELLE PROPOSTE DI VENDITA INERENTI AI LIBRI. NEL DETTAGLIO, SARANNO PRESENTATI TUTTI I GENERI LETTERARI A DISPOSIZIONE DEI CLIENTI E UNA SELEZIONE DI OPERE CARTACEE ORGANIZZATE IN BASE ALLE VOTAZIONI RICEVUTE, AL NUMERO DELLE LORO VENDITE E ALL'ANNO DI USCITA DEGLI ARTICOLI DI INTERESSE 
	
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
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_categorie_libri.php");
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_recensioni.php");
	require_once("./apertura_file_acquisti.php");
	
	// AL FINE DI GARANTIRE UNA DISPOSIZIONE ADEGUATA AI VARI ELEMENTI DELLA PAGINA, BISOGNERÀ DETERMINARE IL NUMERO DI OFFERTE INERENTI AI LIBRI E ATTUALMENTE COLLOCATE NEL CATALOGO. INFATTI, QUALORA SIANO TUTTE ESAURITE, SI DOVRÀ STAMPARE UN MESSAGGIO CHE, DOPO ESSERE STATI REINDERIZZATI ALLA PAGINA INIZIALE DEL SITO, NOTIFICHERÀ L'INTERVENTO IN "TEMPI BREVI" DELLE VARIE FIGURE DI SPICCO (GESTORI E/O AMMINISTRATORE) PER OVVIARE AD UNA SIMILE MANCANZA
	require_once("./calcolo_offerte_libri.php");
	
	if($num_offerte==0) {
		// PER POTER PRESENTARE IL MESSAGGIO, SI PREDISPONE UN COOKIE CHE SARÀ UTILIZZATO COME FLAG COSÌ DA RIPORTARE L'ACCADUTO AL CLIENTE COINVOLTO
		setcookie("nessuna_Offerta", true);
		
		header("Location: index.php");
	}
	
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>LEV: Libri &amp; Videogiochi</title>
		<link rel="icon" href="../../Immagini/Icona_LEV.png" />
		<link rel="stylesheet" href="../../Stili CSS/style_articoli.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_intestazione_sito.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_common.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_footer_sito.css" type="text/css" />
		<script type="text/javascript" src="../JavaScript/gestioneMenuTendina.js"></script>
	</head>
	<body>
		<?php 
			// L'INTESTAZIONE DEL SITO, POICHÈ SI TRATTA DI UN DETTAGLIO RICORRENTE, È STATA DEFINITA ALL'INTERNO DI UN PROGRAMMA INDIPENDENTE CHE, IN BASE ALLE ESIGENZE, VERRÀ INCLUSO A PIÙ RIPRESE NEI VARI SCRIPT
			require_once("./intestazione_sito.php"); 
		?>
		<div class="corpo_pagina">
			<div class="container_corpo_pagina">
				<div class="articoli">
					<div class="container_articoli">
						<div class="intestazione_articoli">
							<div class="container_intestazione_articoli">
								<span class="icona_articoli">
									<img src="../../Immagini/book-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Libri</h2>
							</div>
						</div>
						<div class="contenuto_articoli">
							<div class="elenco_articoli">
								<div class="intestazione_elenco_articoli">
									<h3>Categorie in evidenza</h3>
								</div>
								<?php
									// UNA VOLTA CONCLUSI I CONTROLLI PRELIMINARI, È POSSIBILE PRESENTARE I GENERI LETTERARI IN CUI SONO ORGANIZZATE LE OPERE TRATTATE DAI SOGGETTI CHIAVE DEL SITO, LE QUALI, PER POTER GARANTIRE UN CERTO LIVELLO DI VISIBILITÀ, SARANNO DISPOSTE A GRUPPI DI 4 SU OGNI RIGA
									for($i=0; $i<$categorie->length; $i++) {
										
										// AD OGNI ITERAZIONE CHE INIZIA CON UNA COMPONENTE DIVISIBILE PER 4, VIENE AVVIATO IL NUOVO CICLO DI STAMPE INERENTI ALLE CATEGORIE DI INTERESSE
										if($i%4==0) {
											
											// PER QUESTIONI DI TABULAZIONE, È STATO NECESSARIO DISCRIMINARE L'ELEMENTO A CUI SI STA FACENDO ATTUALMENTE RIFERIMENTO. INFATTI, A PARTIRE DALLA SECONDA RIGA, SARÀ NECESSARIO ATTRIBUIRE UNA DETERMINATA FORMATTAZIONE ANCHE ALL'OGGETTO DI PARTENZA CHE LA COMPONE
											if($i==0)
												echo "<div class=\"categorie_libri\">\n";
											else
												echo "\t\t\t\t\t\t\t\t<div class=\"categorie_libri\">\n";
											
											// DAL MOMENTO CHE NON SI HA LA TOTALE CERTEZZA IN MERITO AL NUMERO DI CATEGORIE CON CUI SI AVRÀ A CHE FARE, ABBIAMO DOVUTO GESTIRE LE VARIE SEZIONI DEL COSTRUTTO ITERATIVO IN MODO PARAMETRICO 
											for($j=$i; $j<$i+4; $j++) {
												
												// PER DI PIÙ, SARÀ NECESSARIO EFFETTUARE UN COSTANTE CONTROLLO IN RELAZIONE AL NUMERO EFFETTIVO DI GENERI LETTERARI, IN QUANTO POTREBBERO NON ESSERE PARI AD UN MULTIPLO DI 4
												if($j<$categorie->length) {
													$categoria=$categorie->item($j);
													
													// OGNUNA DELLE VOCI INERENTI AI GENERI ELENCATI, OLTRE AD ESSERE COMPOSTA A PARTIRE DAI RIFERIMENTI PRESENTI NEL RELATIVO FILE XML, DOVRÀ ESSERE IN GRADO DI REINDERIZZARE IL SOGGETTO D'INTERESSE VERSO LA PAGINA CONTENENTE QUELLE PROPOSTE DI VENDITA I CUI ARTICOLI SI RIFERISCONO ALLA CATEGORIA INDICATA 
													echo "\t\t\t\t\t\t\t\t\t<a class=\"categoria_libri\" href=\"libri_per_categoria.php?id_Categoria=".$categoria->getAttribute("id")."\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t<span class=\"container_categoria_libri\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"anteprima_categoria\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"".$categoria->lastChild->textContent."\" alt=\"Immagine Non Disponibile...\" />\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"titolo_categoria\">".$categoria->firstChild->textContent."</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t</a>\n";
												}
											}
											
											echo "\t\t\t\t\t\t\t\t</div>\n";
											
											// AL TERMINE DELLA PRIMA SEQUENZA DI STAMPE, È NECESSARIO ADEGUARE IL CONTENUTO DEL CONTATORE PIÙ ESTERNO, PONENDOLO, IN VISTA DEL SUCCESSIVO INCREMENTO, AL VALORE DI QUELLO PIÙ INTERNO DECREMENTATO DI UNO
											$i=$j-1;
											
										}
									}
								?>
							</div>
							<div class="elenco_articoli">
								<div class="intestazione_elenco_articoli">
									<h3>I pi&ugrave; votati</h3>
								</div>
								<div class="selezione_articoli">
									<?php
										// PER PRESENTARE I TITOLI CHE HANNO RICEVUTO PIÙ VOTAZIONI NEL CORSO DEL TEMPO, SARÀ NECESSARIO TENERE TRACCIA DI TUTTE QUELLE OPERE CARTACEE CHE AL MOMENTO SONO CARATTERIZZATE DA UNA PROPOSTA DI VENDITA ALL'INTERNO DEL CATALOGO. SIMILMENTE AL CASO PRECEDENTE, È STATO RITENUTO OPPORTUNO RIPORTARE SOLTANTO I PRIMI 4 PRODOTTI CHE SODDISFANO TALE REQUISITO
										// IL RAGIONAMENTO APPENA ILLUSTRATO, COSÌ COME IL SEGUENTE ALGORITMO, POTRANNO ESSERE ESTESI ANCHE ALLE ALTRE SEZIONI DELLA SCHERMATA
										require_once("./ricerca_libri_piu_votati.php");
										
										// DOPO AVER CREATO IL VETTORE CONTENENTE I VARI RIFERIMENTI D'INTERESSE, BISOGNERÀ PROCEDERE CON LA RICAPITOLAZIONE DELLE LORO INFORMAZIONI SFRUTTANDO, PER SEMPLICITÀ, IL COSTRUTTO foreach(...). INOLTRE, PER TENERE TRACCIA DEL NUMERO DI ELEMENTI FINORA CONSIDERATI, È STATO INTRODOTTO UN CONTATORE CHE CI HA PERMESSO DI TABULARE CORRETTAMENTE IL DOCUMENTO  
										$i=0;
										
										foreach($i_piu_votati as $idProdotto => $num_recensioni) {
											
											// LE CHIAVI DEL VETTORE SONO STATE DEFINITE COME DELLE STRINGHE DELIMITATE DA DUE APPOSITI CARATTERI '...' IN MODO TALE DA PRESERVARE L'IDENTIFICATORE DI UNA CERTA PROPOSTA DI VENDITA. DUNQUE, PRIMA DI POTER ESSERE CONFRONTATE CON GLI INDICI DEI PRODOTTI, NECESSATINO DI UN'ULTERIORE FORMATTAZIONE     
											$idProdotto=substr($idProdotto, 1, strlen($idProdotto)-2);
											
											// DATE LE POCHE INFORMAZIONI A DISPOSIZIONE, BISOGNERÀ NUOVAMENTE INTERAGIRE CON IL DOCUMENTO INERENTE AGLI ARTICOLI IN MAGAZZINO
											for($j=0; $j<$prodotti->length; $j++) {
												$prodotto=$prodotti->item($j);
												
												if($prodotto->getAttribute("id")==$idProdotto) {
													
													for($k=0; $k<$offerte->length; $k++) {
														$offerta=$offerte->item($k);
														
														if($offerta->getAttribute("idProdotto")==$idProdotto) {
															break;
														}
													}
													
													// ***
													if($i==0)
														echo "<a class=\"articolo\" href=\"riepilogo_scheda_offerta.php?id_Offerta=".$offerta->getAttribute("id")."\">\n";
													else
														echo "\t\t\t\t\t\t\t\t\t<a class=\"articolo\" href=\"riepilogo_scheda_offerta.php?id_Offerta=".$offerta->getAttribute("id")."\">\n";
													
													echo "\t\t\t\t\t\t\t\t\t\t<span class=\"container_articolo\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"anteprima_articolo\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"".$prodotto->getElementsByTagName("immagine")->item(0)->textContent."\" alt=\"Immagine Non Disponibile...\" />\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"dettagli_articolo\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"nome\"><span title=\"".$prodotto->firstChild->textContent."\">".$prodotto->firstChild->textContent."</span></span>\n";			
													
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"autori\"><span title=\"di ";
													
													for($k=0; $k<$prodotto->getElementsByTagName("autore")->length; $k++) {
														
														if($k<$prodotto->getElementsByTagName("autore")->length-1)
															echo $prodotto->getElementsByTagName("autore")->item($k)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($k)->lastChild->textContent.", ";
														else
															echo $prodotto->getElementsByTagName("autore")->item($k)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($k)->lastChild->textContent;
													}
													
													echo "\">di ";
													
													for($k=0; $k<$prodotto->getElementsByTagName("autore")->length; $k++) {
														
														if($k<$prodotto->getElementsByTagName("autore")->length-1)
															echo $prodotto->getElementsByTagName("autore")->item($k)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($k)->lastChild->textContent.", ";
														else
															echo $prodotto->getElementsByTagName("autore")->item($k)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($k)->lastChild->textContent;
														
													}
													
													echo "</span></span>\n";
													
													// PER OGNI PROPOSTA DI VENDITA, SI DOVRÀ CONSIDERARE LE CASISTICHE IN CUI QUESTE SIANO CARATTERIZZATE DA DELLE RIDUZIONI DI PREZZO (SCONTI A TEMPO O PROMOZIONALI) O DA DEGLI INCENTIVI, QUALI CREDITI BONUS
													if($offerta->getElementsByTagName("scontoATempo")->length==0)
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"prezzo\"><span>Prezzo</span> <span>".$offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent." Cr.</span></span>\n";			
													else {
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"prezzo\"><span>Prezzo</span> <span style=\"text-decoration: line-through; text-decoration-color: rgb(217, 118, 64); font-weight: normal;\">".$offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent." Cr.</span></span>\n";			
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"prezzo\"><span>Offerta <span style=\"color: rgb(217, 118, 64);\" title=\"valida dal ".date_format(date_create($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione")), "d/m/Y")." al ".date_format(date_create($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione")), "d/m/Y")."\">*</span></span> <span style=\"color: rgb(217, 118, 64);\">".number_format(floatval($offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent)-(floatval($offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent)*(floatval($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale")/100))),2, ".", "")." Cr.</span></span>\n";
													}
													
													if($offerta->getElementsByTagName("scontoFuturo")->length!=0)
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"sconto\"><span>Promozione <span style=\"color: rgb(217, 118, 64);\" title=\"applicabile sul prossimo acquisto e valida dal ".date_format(date_create($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione")), "d/m/Y")." al ".date_format(date_create($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione")), "d/m/Y")."\">*</span></span> <span style=\"color: rgb(217, 118, 64);\">".$offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale")." %</span></span>\n";			
														
													if($offerta->getElementsByTagName("bonus")->length!=0)
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"bonus\"><span>Bonus</span> <span style=\"color: rgb(217, 118, 64);\">".$offerta->getElementsByTagName("bonus")->item(0)->firstChild->textContent." Cr.</span></span>\n";
										
													// L'ULTIMA SEZIONE CHE ANDRÀ A COMPORRE LA SINGOLA OFFERTA SARÀ CARATTERIZZATA DA UN RIEPILOGO GENERALE DELLE RECENSIONI CHE IL BENE HA RICEVUTO NEL CORSO DEL TEMPO. PROPRIO PER QUESTO, SARÀ NECESSARIO ANDARE A DETERMINARE LA MEDIA INERENTE A CIASCUNO DEI SUOI PARAMETRI DI VALUTAZIONE
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"recensioni\">\n";			
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_recensioni\" style=\"font-weight: normal;\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\tRecensioni (".$num_recensioni.")\n";
													
													require("./calcolo_media_recensioni_prodotto.php");
													
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazioni\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazione\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Trama</span> <span>".number_format($media_trama,1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\" /></span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazione\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Personaggi</span> <span>".number_format($media_personaggi,1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\" /></span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazione\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Ambientazione</span> <span>".number_format($media_ambientazione,1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\" /></span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";

													// PER GARANTIRE CHE LA PROSSIMA ITERAZIONE NON PRODUCA DEI DATI INESATTI, BISOGNA AZZERARE LE VARIBILI UTILIZZATE PER CALCOLARE I PRECEDENTI RISULTATI
													$media_trama=$media_personaggi=$media_ambientazione=0;
														
													echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";			
													echo "\t\t\t\t\t\t\t\t\t\t</span>\n";			
													echo "\t\t\t\t\t\t\t\t\t</a>\n";
													
													$i++;
													break;
												}
											}
										}
									?>
								</div>
							</div>
							<div class="elenco_articoli">
								<div class="intestazione_elenco_articoli">
									<h3>I pi&ugrave; venduti</h3>
								</div>
								<div class="selezione_articoli">
									<?php
										// ***
										require_once("./ricerca_libri_piu_venduti.php");
										
										// *** 
										$i=0;
										
										foreach($i_piu_venduti as $idProdotto => $num_acquisti) {
											
											// ***
											$idProdotto=substr($idProdotto, 1, strlen($idProdotto)-2);
											
											// ***
											for($j=0; $j<$prodotti->length; $j++) {
												$prodotto=$prodotti->item($j);
												
												if($prodotto->getAttribute("id")==$idProdotto) {
													
													for($k=0; $k<$offerte->length; $k++) {
														$offerta=$offerte->item($k);
														
														if($offerta->getAttribute("idProdotto")==$idProdotto) {
															break;
														}
													}
													
													// ***
													if($i==0)
														echo "<a class=\"articolo\" href=\"riepilogo_scheda_offerta.php?id_Offerta=".$offerta->getAttribute("id")."\" title=\"Vendite (".$num_acquisti.") \">\n";
													else
														echo "\t\t\t\t\t\t\t\t\t<a class=\"articolo\" href=\"riepilogo_scheda_offerta.php?id_Offerta=".$offerta->getAttribute("id")."\" title=\"Vendite (".$num_acquisti.") \">\n";
													
													echo "\t\t\t\t\t\t\t\t\t\t<span class=\"container_articolo\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"anteprima_articolo\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"".$prodotto->getElementsByTagName("immagine")->item(0)->textContent."\" alt=\"Immagine Non Disponibile...\" />\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"dettagli_articolo\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"nome\"><span title=\"".$prodotto->firstChild->textContent."\">".$prodotto->firstChild->textContent."</span></span>\n";			
													
													for($k=0; $k<$prodotto->getElementsByTagName("autore")->length; $k++) {
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"autori\"><span title=\"di ";
														
														if($k<$prodotto->getElementsByTagName("autore")->length-1)
															echo $prodotto->getElementsByTagName("autore")->item($k)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($k)->lastChild->textContent.", ";
														else
															echo $prodotto->getElementsByTagName("autore")->item($k)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($k)->lastChild->textContent;
														
														echo "\">di ";
														
														if($k<$prodotto->getElementsByTagName("autore")->length-1)
															echo $prodotto->getElementsByTagName("autore")->item($k)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($k)->lastChild->textContent.", ";
														else
															echo $prodotto->getElementsByTagName("autore")->item($k)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($k)->lastChild->textContent;
														
														echo "</span></span>\n";
													}
													
													// ***
													if($offerta->getElementsByTagName("scontoATempo")->length==0)
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"prezzo\"><span>Prezzo</span> <span>".$offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent." Cr.</span></span>\n";			
													else {
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"prezzo\"><span>Prezzo</span> <span style=\"text-decoration: line-through; text-decoration-color: rgb(217, 118, 64); font-weight: normal;\">".$offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent." Cr.</span></span>\n";			
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"prezzo\"><span>Offerta <span style=\"color: rgb(217, 118, 64);\" title=\"valida dal ".date_format(date_create($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione")), "d/m/Y")." al ".date_format(date_create($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione")), "d/m/Y")."\">*</span></span> <span style=\"color: rgb(217, 118, 64);\">".number_format(floatval($offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent)-(floatval($offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent)*(floatval($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale")/100))),2, ".", "")." Cr.</span></span>\n";
													}
													
													if($offerta->getElementsByTagName("scontoFuturo")->length!=0)
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"sconto\"><span>Promozione <span style=\"color: rgb(217, 118, 64);\" title=\"applicabile sul prossimo acquisto e valida dal ".date_format(date_create($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione")), "d/m/Y")." al ".date_format(date_create($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione")), "d/m/Y")."\">*</span></span> <span style=\"color: rgb(217, 118, 64);\">".$offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale")." %</span></span>\n";			
														
													if($offerta->getElementsByTagName("bonus")->length!=0)
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"bonus\"><span>Bonus</span> <span style=\"color: rgb(217, 118, 64);\">".$offerta->getElementsByTagName("bonus")->item(0)->firstChild->textContent." Cr.</span></span>\n";
										
													// ***
													// CONTRARIAMENTE AL CASO PRECEDENTE, OVVERO QUELLO INERENTE AI PRODOTTI PIÙ VOTATI, SARÀ POSSIBILE INDIVIDUARE LE RECENSIONI CHE LO COINVOLGONO SOLTANTO IN UN SECONDO MOMENTO. INOLTRE, LE VOCI DA TENERE IN CONSIDERAZIONE SARANNO SOLO ED ESCLUSIVAMENTE QUELLE CHE NON SONO ANCORA STATE MODERATE DAI GESTORI E DALL'AMMINISTRATORE DEL SITO 
													$num_recensioni=0;
														
													for($k=0; $k<$prodotto->getElementsByTagName("recensione")->length; $k++) {
														$recensione_prodotto=$prodotto->getElementsByTagName("recensione")->item($k);
														
														for($l=0; $l<$recensioni->length; $l++) {
															if($recensioni->item($l)->getAttribute("id")==$recensione_prodotto->getAttribute("idRecensione") && $recensioni->item($l)->getAttribute("moderata")=="No") {
																$num_recensioni++;
																break;
															}
														}
													}
													
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"recensioni\">\n";			
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_recensioni\" style=\"font-weight: normal;\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\tRecensioni (".$num_recensioni.")\n";
													
													require("./calcolo_media_recensioni_prodotto.php");
													
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazioni\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazione\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Trama</span> <span>".number_format($media_trama,1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\" /></span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazione\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Personaggi</span> <span>".number_format($media_personaggi,1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\" /></span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazione\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Ambientazione</span> <span>".number_format($media_ambientazione,1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\" /></span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";

													// ***
													$media_trama=$media_personaggi=$media_ambientazione=0;
														
													echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";			
													echo "\t\t\t\t\t\t\t\t\t\t</span>\n";			
													echo "\t\t\t\t\t\t\t\t\t</a>\n";			
														
													$i++;
													break;
												}
											}
										}
									?>
								</div>
							</div>
							<div class="elenco_articoli">
								<div class="intestazione_elenco_articoli">
									<h3>I pi&ugrave; recenti</h3>
								</div>
								<div class="selezione_articoli">
									<?php
										// ***
										require_once("./ricerca_libri_piu_recenti.php");
										
										
										// *** 
										$i=0;
										
										foreach($i_piu_recenti as $idProdotto => $anno_pubblicazione) {
											
											// ***
											$idProdotto=substr($idProdotto, 1, strlen($idProdotto)-2);
											
											// ***
											for($j=0; $j<$prodotti->length; $j++) {
												$prodotto=$prodotti->item($j);
												
												if($prodotto->getAttribute("id")==$idProdotto) {
													
													for($k=0; $k<$offerte->length; $k++) {
														$offerta=$offerte->item($k);
														
														if($offerta->getAttribute("idProdotto")==$idProdotto) {
															break;
														}
													}
													
													// ***
													if($i==0)
														echo "<a class=\"articolo\" href=\"riepilogo_scheda_offerta.php?id_Offerta=".$offerta->getAttribute("id")."\" title=\"Anno (".$anno_pubblicazione.") \">\n";
													else
														echo "\t\t\t\t\t\t\t\t\t<a class=\"articolo\" href=\"riepilogo_scheda_offerta.php?id_Offerta=".$offerta->getAttribute("id")."\" title=\"Anno (".$anno_pubblicazione.") \">\n";
													
													echo "\t\t\t\t\t\t\t\t\t\t<span class=\"container_articolo\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"anteprima_articolo\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"".$prodotto->getElementsByTagName("immagine")->item(0)->textContent."\" alt=\"Immagine Non Disponibile...\" />\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"dettagli_articolo\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"nome\"><span title=\"".$prodotto->firstChild->textContent."\">".$prodotto->firstChild->textContent."</span></span>\n";			
													
													for($k=0; $k<$prodotto->getElementsByTagName("autore")->length; $k++) {
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"autori\"><span title=\"di ";
														
														if($k<$prodotto->getElementsByTagName("autore")->length-1)
															echo $prodotto->getElementsByTagName("autore")->item($k)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($k)->lastChild->textContent.", ";
														else
															echo $prodotto->getElementsByTagName("autore")->item($k)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($k)->lastChild->textContent;
														
														echo "\">di ";
														
														if($k<$prodotto->getElementsByTagName("autore")->length-1)
															echo $prodotto->getElementsByTagName("autore")->item($k)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($k)->lastChild->textContent.", ";
														else
															echo $prodotto->getElementsByTagName("autore")->item($k)->firstChild->textContent." ".$prodotto->getElementsByTagName("autore")->item($k)->lastChild->textContent;
														
														echo "</span></span>\n";
													}
													
													// ***
													if($offerta->getElementsByTagName("scontoATempo")->length==0)
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"prezzo\"><span>Prezzo</span> <span>".$offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent." Cr.</span></span>\n";			
													else {
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"prezzo\"><span>Prezzo</span> <span style=\"text-decoration: line-through; text-decoration-color: rgb(217, 118, 64); font-weight: normal;\">".$offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent." Cr.</span></span>\n";			
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"prezzo\"><span>Offerta <span style=\"color: rgb(217, 118, 64);\" title=\"valida dal ".date_format(date_create($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione")), "d/m/Y")." al ".date_format(date_create($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione")), "d/m/Y")."\">*</span></span> <span style=\"color: rgb(217, 118, 64);\">".number_format(floatval($offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent)-(floatval($offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent)*(floatval($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale")/100))),2, ".", "")." Cr.</span></span>\n";
													}
													
													if($offerta->getElementsByTagName("scontoFuturo")->length!=0)
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"sconto\"><span>Promozione <span style=\"color: rgb(217, 118, 64);\" title=\"applicabile sul prossimo acquisto e valida dal ".date_format(date_create($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione")), "d/m/Y")." al ".date_format(date_create($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione")), "d/m/Y")."\">*</span></span> <span style=\"color: rgb(217, 118, 64);\">".$offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale")." %</span></span>\n";			
														
													if($offerta->getElementsByTagName("bonus")->length!=0)
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"bonus\"><span>Bonus</span> <span style=\"color: rgb(217, 118, 64);\">".$offerta->getElementsByTagName("bonus")->item(0)->firstChild->textContent." Cr.</span></span>\n";
										
													// ***
													$num_recensioni=0;
														
													for($k=0; $k<$prodotto->getElementsByTagName("recensione")->length; $k++) {
														$recensione_prodotto=$prodotto->getElementsByTagName("recensione")->item($k);
														
														for($l=0; $l<$recensioni->length; $l++) {
															if($recensioni->item($l)->getAttribute("id")==$recensione_prodotto->getAttribute("idRecensione") && $recensioni->item($l)->getAttribute("moderata")=="No") {
																$num_recensioni++;
																break;
															}
														}
													}
													
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"recensioni\">\n";			
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"container_recensioni\" style=\"font-weight: normal;\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\tRecensioni (".$num_recensioni.")\n";
													
													require("./calcolo_media_recensioni_prodotto.php");
													
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazioni\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazione\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Trama</span> <span>".number_format($media_trama,1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\" /></span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazione\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Personaggi</span> <span>".number_format($media_personaggi,1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\" /></span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazione\">\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Ambientazione</span> <span>".number_format($media_ambientazione,1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\" /></span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";

													// ***
													$media_trama=$media_personaggi=$media_ambientazione=0;
														
													echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";			
													echo "\t\t\t\t\t\t\t\t\t\t</span>\n";			
													echo "\t\t\t\t\t\t\t\t\t</a>\n";			
														
													$i++;
													break;
												}
											}
										}
									?>
								</div>
							</div>
							<div class="pulsante" style="justify-content: center;">
								<form action="index.php" method="post">
									<p>
										<button type="submit" class="container_pulsante back" style="margin-left: 1.5em; margin-right: 1.5em; padding: 0em; width: 9.35em; font-size: 1em;">Indietro!</button>
									</p>
								</form>
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