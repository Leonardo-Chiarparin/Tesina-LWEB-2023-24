<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php 
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO I RISULTATI PRODOTTI DALLA RICERCA EFFETTUATA A PARTIRE DAI CARATTERI DIGITATI NELLA RELATIVA BARRA 

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
	require_once("./apertura_file_piattaforme_videogiochi.php");
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_recensioni.php");
	
	// AL FINE DI GARANTIRE UNA DISPOSIZIONE ADEGUATA AI VARI ELEMENTI DELLA PAGINA, BISOGNERÀ DETERMINARE IL NUMERO DI OFFERTE INERENTI AGLI ARTICOLI D'INTERESSE. INFATTI, QUALORA SIANO TUTTE ESAURITE, SI DOVRÀ STAMPARE UN MESSAGGIO CHE NOTIFICHERÀ L'ASSENZA DI OFFERTE PER QUEL DETERMINATO BENE
	require_once("./calcolo_offerte.php");
	
	if($num_offerte==0) {
		// PER POTER PRESENTARE IL MESSAGGIO, SI PREDISPONE UN COOKIE CHE SARÀ UTILIZZATO COME FLAG COSÌ DA RIPORTARE L'ACCADUTO AL CLIENTE COINVOLTO
		setcookie("nessuna_Offerta", true);
		
		header("Location: index.php");
	}
	
	// LA PAGINA, ESSENDO RAGGIUNGIBILE DOPO AVER DIGITATO UNA DETERMINATA STRINGA ALL'INTERNO DELLA BARRA DI RICERCA, NECESSITA DI UN ULTERIORE CONTROLLO PER APPURARE SE È STATA EFFETTIVAMENTE PRESA UNA DECISIONE IN MERITO O MENO
	if(isset($_GET["prodotto_Ricercato"]))
	{	
		// PRIMA DI OGNI ALTRA OPERAZIONE, SI PROCEDERÀ CON LA RIMOZIONE DEGLI EVENTUALI SPAZI BIANCHI AGLI ESTREMI DELLA STRINGA, PER POI PROCEDERE CON LA TRADUZIONE IN MINUSCOLO DI QUEST'ULTIMI. PER DI PIÙ, AL FINE DI PRESERVARE IL FORMATO DELL'INPUT FORNITO DALL'UTENTE, IL SALVATAGGIO DEL RISULTATO VERRÀ EFFETTUATO ALL'INTERNO DI UN'ALTRA VARIABILE IMPIEGATA SUCCESSIVAMENTE
		$_GET["prodotto_Ricercato"]=trim($_GET["prodotto_Ricercato"]);
		$_GET["prodotto_Ricercato"]=rtrim($_GET["prodotto_Ricercato"]);
		
		// QUALORA L'UTENTE NON ABBIA INSERITO NULLA (O SOLTANTO DEGLI SPAZI BIANCHI), SARÀ REINDIRIZZATO VERSO LA PAGINA INIZIALE DEL SITO. AL CONTRARIO, SI PROCEDERÀ SIA CON LA RIMOZIONE DI EVENTUALI "\" (PER EVITARE PROBLEMATICHE LEGATE ALLA MySQL Injection) CHE ALLA FORMATAZIONE DEI CARATTERI CHE LO COMPONGONO
		if(strlen($_GET["prodotto_Ricercato"])==0)
			header("Location: index.php");
		else {
			$prodotto_ricercato=stripslashes($_GET["prodotto_Ricercato"]);
			$prodotto_ricercato=strtolower($prodotto_ricercato);
		}
	}
	else
	{
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
		<script type="text/javascript" src="../JavaScript/gestioneMenuOrdinamento.js"></script>
	</head>
	<body>
		<?php 
			// L'INTESTAZIONE DEL SITO, POICHÈ SI TRATTA DI UN DETTAGLIO RICORRENTE, È STATA DEFINITA ALL'INTERNO DI UN PROGRAMMA INDIPENDENTE CHE, DATE LE ESIGENZE, VERRÀ INCLUSO A PIÙ RIPRESE NEI VARI SCRIPT
			require_once("./intestazione_sito.php"); 
		?>
		<div class="corpo_pagina">
			<div class="container_corpo_pagina">
				<div class="articoli">
					<div class="container_articoli">
						<div class="intestazione_articoli">
							<div class="container_intestazione_articoli">
								<span class="icona_articoli">
									<img src="../../Immagini/box-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Articoli</h2>
							</div>
						</div>
						<div class="contenuto_articoli">
							<div class="elenco_articoli">
								<div class="intestazione_elenco_articoli" style="display:flex; justify-content:space-between; align-items:center;">
									<h3 style="color: rgb(255,255,255);">Risultati per <span style="color: rgb(217,118,64);">"<?php echo $_GET["prodotto_Ricercato"]; ?>"</span></h3>
									<div class="selettore_ordinamenti" onclick="gestioneMenuOrdinamento()">
										<div class="container_selettore_ordinamenti">
											<div class="intestazione_selettore_ordinamenti">
												<span>Ordinamento:</span>
												<span>
													<?php
														// IN BASE ALL'ORDINAMENTO SELEZIONATO, SARÀ DOVEROSO ADEGUARE IL CONTENUTO DEL RELATIVO MENÙ. QUALORA VENISSE INSERITA UN'OPZIONE AL DI FUORI DI QUELLE IMPLEMENTATE, QUEST'ULTIMA VERRÀ CONSIDERATA COME SE FOSSE QUELLA DI DEFAULT    
														if(isset($_GET["ordinamento"])) {
															if($_GET["ordinamento"]=="prezzoCrescente")
																echo "Prezzo Crescente";
															else {
																if($_GET["ordinamento"]=="prezzoDecrescente")
																	echo "Prezzo Decrescente";
																else {
																	if($_GET["ordinamento"]=="nomeA-Z")
																		echo "Nome A-Z";
																	else {
																		if($_GET["ordinamento"]=="nomeZ-A")
																			echo "Nome Z-A";
																		else {
																			if($_GET["ordinamento"]=="annoDiUscita")
																				echo "Anno di Uscita";
																			else
																				echo "Nessuno";
																		}
																	}
																}
															}
														}
														else
															echo "Nessuno";
													?>
													<span class="freccia_tendina giu" style="margin-right: 0em;" id="freccia_tendina_ordinamento"></span>
												</span>
												<ul class="menu_tendina_ordinamento nascondi" style="" id="menu_ordinamento">
													<li>
														<?php
															if(!(isset($_GET["ordinamento"])))
																echo "<a style=\"background-color: rgb(119, 119, 119);\" href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$_GET["prodotto_Ricercato"]."\">Nessuno</a>\n";
															else
																echo "<a href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$_GET["prodotto_Ricercato"]."\">Nessuno</a>\n";
														?>
													</li>
													<li>
														<?php
															if(isset($_GET["ordinamento"]) && $_GET["ordinamento"]=="prezzoCrescente")
																echo "<a style=\"background-color: rgb(119, 119, 119);\" href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$_GET["prodotto_Ricercato"]."&amp;ordinamento=prezzoCrescente\">Prezzo Crescente</a>\n";
															else
																echo "<a href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$_GET["prodotto_Ricercato"]."&amp;ordinamento=prezzoCrescente\">Prezzo Crescente</a>\n";
														?>
													</li>
													<li>
														<?php
															if(isset($_GET["ordinamento"]) && $_GET["ordinamento"]=="prezzoDecrescente")
																echo "<a style=\"background-color: rgb(119, 119, 119);\" href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$_GET["prodotto_Ricercato"]."&amp;ordinamento=prezzoDecrescente\">Prezzo Decrescente</a>\n";
															else
																echo "<a href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$_GET["prodotto_Ricercato"]."&amp;ordinamento=prezzoDecrescente\">Prezzo Decrescente</a>\n";
														?>
													</li>
													<li>
														<?php
															if(isset($_GET["ordinamento"]) && $_GET["ordinamento"]=="nomeA-Z")
																echo "<a style=\"background-color: rgb(119, 119, 119);\" href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$_GET["prodotto_Ricercato"]."&amp;ordinamento=nomeA-Z\">Nome A-Z</a>\n";
															else
																echo "<a href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$_GET["prodotto_Ricercato"]."&amp;ordinamento=nomeA-Z\">Nome A-Z</a>\n";
														?>
													</li>
													<li>
														<?php
															if(isset($_GET["ordinamento"]) && $_GET["ordinamento"]=="nomeZ-A")
																echo "<a style=\"background-color: rgb(119, 119, 119);\" href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$_GET["prodotto_Ricercato"]."&amp;ordinamento=nomeZ-A\">Nome Z-A</a>\n";
															else
																echo "<a href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$_GET["prodotto_Ricercato"]."&amp;ordinamento=nomeZ-A\">Nome Z-A</a>\n";
														?>
													</li>
													<li>
														<?php
															if(isset($_GET["ordinamento"]) && $_GET["ordinamento"]=="annoDiUscita")
																echo "<a style=\"background-color: rgb(119, 119, 119);\" href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$_GET["prodotto_Ricercato"]."&amp;ordinamento=annoDiUscita\">Anno di Uscita</a>\n";
															else
																echo "<a href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$_GET["prodotto_Ricercato"]."&amp;ordinamento=annoDiUscita\">Anno di Uscita</a>\n";
														?>
													</li>
												</ul>
											</div>
										</div>
									</div>
								</div>
									<?php
										// PER PRESENTARE LE PROPOSTE DI VENDITA CHE SI "RIFERISCONO" AL BENE INDICATO, SARÀ NECESSARIO TENERE TRACCIA DI TUTTE GLI ARTICOLI CHE SODDISFANO TALE REQUISITO
										require_once("./ricerca_articoli_per_nome.php");
										
										// SE LA RICERCA NON HA PORTATO AD ALCUN RISULTATO, SARÀ NECESSARIO RIPORTARE A SCHERMO UN MESSAGGIO INERENTE ALLA MANCANTA PRESENZA DI PROPOSTE DI VENDITA PER IL PRODOTTO SPECIFICATO
										if(!count($articoli_per_nome)) {
											echo "<span class=\"nessun_elemento\" style=\"margin-top: 1.125em; margin-bottom: 1.625em;\">Non &egrave; stato trovato nessun elemento con le specifiche di interesse.</span>\n";
										}
										
										// UNA VOLTA INDIVIDUATI I VARI ELEMENTI D'INTERESSE, BISOGNERÀ CONSIDERARE IL CONTENUTO DELLE CELLE INERENTI AL VETTORE CREATO IN PRECEDENZA
										for($i=0; $i<count($articoli_per_nome); $i++)
										{
											// AD OGNI ITERAZIONE CHE INIZIA CON UNA COMPONENTE DIVISIBILE PER 4, VIENE AVVIATO IL NUOVO CICLO DI STAMPE INERENTI ALLE OFFERTE DI INTERESSE
											if($i%4==0) {
												
												// PER QUESTIONI DI TABULAZIONE, È STATO NECESSARIO DISCRIMINARE L'ELEMENTO A CUI SI STA FACENDO ATTUALMENTE RIFERIMENTO. INFATTI, A PARTIRE DALLA SECONDA RIGA SARÀ NECESSARIO ATTRIBUIRE UNA DETERMINATA FORMATTAZIONE ANCHE ALL'OGGETTO DI PARTENZA CHE LA COMPONE
												if($i==0)
													echo "<div class=\"selezione_articoli\">\n";
												else
													echo "\t\t\t\t\t\t\t\t<div class=\"selezione_articoli\">\n";
												
												// DAL MOMENTO CHE NON SI HA LA TOTALE CERTEZZA IN MERITO AL NUMERO DI OFFERTE CON CUI SI AVRÀ A CHE FARE, BISOGNA GESTIRE IN MODO PARAMETRICO LE VARIE SEZIONI DEL COSTRUTTO PER EFFETTUARE CORRETTAMENTE LA SCANSIONE 
												for($j=$i; $j<$i+4; $j++) {
													
													// PER DI PIÙ, SARÀ NECESSARIO EFFETTUARE UN COSTANTE CONTROLLO IN RELAZIONE AL NUMERO EFFETTIVO DI OFFERTE, IN QUANTO POTREBBERO NON ESSERE PARI AD UN MULTIPLO DI 4 
													if($j<count($articoli_per_nome))
													{
														for($k=0; $k<$offerte->length; $k++)
														{
															$offerta=$offerte->item($k);
															
															// POICHÈ È POSSIBILE ORDINARE I VARI RISULTATI SECONDO CERTI CRITERI, SI DOVRÀ DISCRIMINARE LE MODALITÀ CON CUI È STATO CARATTERIZZATO IL RELATIVO VETTORE 
															if(isset($_GET["ordinamento"]) && ($_GET["ordinamento"]=="prezzoCrescente" || $_GET["ordinamento"]=="prezzoDecrescente" || $_GET["ordinamento"]=="nomeA-Z" || $_GET["ordinamento"]=="nomeZ-A" || $_GET["ordinamento"]=="annoDiUscita"))
															{
																// NELL'EVENTUALITÀ IN CUI SIA STATO INDICATO UN CERTO ORDINAMENTO, IL CONFRONTO PER VALUTARE SE PRESENTARE O MENO UNA CERTA PROPOSTA DI VENDITA SI BASERÀ SULLA COMPONENTE j-ESIMA DELL'ARRAY INERENTE ALLE CHIAVI DEL VETTORE ASSOCIATIVO, LE QUALI, DATA LA LORO CARATTERIZZAZIONE, SARANNO DECURTATATE DEI DUE CARATTERI DELIMITATORI COLLOCATI, RISPETTIVAMENTE, ALL'INIZIO E ALLA FINE DELLA STRINGA     
																if($offerta->getAttribute("id")==substr(array_keys($articoli_per_nome)[$j], 1, strlen(array_keys($articoli_per_nome)[$j])-2 ))
																	break;
															}
															else
															{
																// NEL CASO IN CUI CI SIA UNA CORRISPONDENZA TRA GLI IDENTIFICATORI DELL'OFFERTA IN ESAME E DI QUELLO MEMORIZZATO ALL'INTERNO DELLA POSIZIONE j-ESIMA DELL'ARRAY, SARÀ POSSBIILE INTERROMPERE LA RICERCA   
																if($offerta->getAttribute("id")==$articoli_per_nome[$j])
																	break;
															}
														}
														
														// INOLTRE, DATA L'ANTEPRIMA CHE SI VUOLE PRESENTARE A SCHERMO, BISOGNERÀ RECUPERARE TUTTE LE INFORMAZIONI, DUNQUE LE ENTITÀ, CHE RAPPRESENTATO L'ARTICOLO COINVOLTO NELLA RELATIVA PROPOSTA DI VENDITA
														for($l=0; $l<$prodotti->length; $l++)
														{
															$prodotto=$prodotti->item($l);
															
															if($offerta->getAttribute("idProdotto")==$prodotto->getAttribute("id"))
																break;
														}
														
														if($prodotto->getElementsByTagName("videogioco")->length) {
															$tipo="Videogioco";
														}
														else
															$tipo="Libro";
														
														// GIUNTI A QUESTO PUNTO, SI PROCEDE CON LA STAMPA DELLE SEZIONI CHE CONCORRONO ALLA CARATTERIZZAZIONE DELL'OFFERTA IN ESAME
														echo "\t\t\t\t\t\t\t\t\t<a class=\"articolo\" href=\"riepilogo_scheda_offerta.php?id_Offerta=".$offerta->getAttribute("id")."\" title=\"".$tipo." - Anno (".$prodotto->getElementsByTagName("annoUscita")->item(0)->textContent.")\">\n";
														echo "\t\t\t\t\t\t\t\t\t\t<span class=\"container_articolo\">\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"anteprima_articolo\">\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t\t<img src=\"".$prodotto->getElementsByTagName("immagine")->item(0)->textContent."\" alt=\"Immagine Non Disponibile...\" />\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
														echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"dettagli_articolo\">\n";
														
														// PER DI PIÙ, DATA LA TIPOLOGIA (VARIABILE) DEL PRODOTTO, SARÀ NECESSARIO EFFETTUARE UNA SERIE DI CONTROLLI PER DISCRIMINARE LE PROPRIETÀ CHE LO CARATTERIZZANO
														if($prodotto->getElementsByTagName("videogioco")->length)
														{
															for($l=0; $l<$piattaforme->length; $l++)
															{
																$piattaforma=$piattaforme->item($l);
																
																if($piattaforma->getAttribute("id")==$prodotto->getElementsByTagName("piattaforma")->item(0)->getAttribute("idPiattaforma")) {
																	echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"nome\"><span title=\"".$prodotto->firstChild->textContent."\">".$prodotto->firstChild->textContent." <span style=\"color: rgb(217, 118, 64);\" title=\"(".$piattaforma->firstChild->textContent.")\">*</span></span></span>\n";			
																}
															}
														}
														else
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"nome\"><span title=\"".$prodotto->firstChild->textContent."\">".$prodotto->firstChild->textContent."</span></span>\n";			
														
														if($prodotto->getElementsByTagName("libro")->length)
														{
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
														}
														else
														{
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"casa_produzione\"><span title=\"di ".$prodotto->getElementsByTagName("casaProduzione")->item(0)->textContent."\">di ".$prodotto->getElementsByTagName("casaProduzione")->item(0)->textContent."</span></span>\n";
														}
														
														// PER OGNI PROPOSTA DI VENDITA, SI DOVRÀ CONSIDERARE LE CASISTICHE IN CUI QUESTE SIANO CARATTERIZZATE DA DELLE RIDUZIONI DI PREZZO (SCONTI A TEMPO O PROMOZIONALI) O DA DEGLI INCENTIVI, QUALI CREDITI BONUS
														if($offerta->getElementsByTagName("scontoATempo")->length==0)
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"prezzo\"><span>Prezzo</span> <span>".$offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent." Cr.</span></span>\n";			
														else {
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"prezzo\"><span>Prezzo</span> <span style=\"text-decoration: line-through; text-decoration-color: rgb(217, 118, 64); font-weight: normal;\">".$offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent." Cr.</span></span>\n";			
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"prezzo\"><span>Offerta <span style=\"color: rgb(217, 118, 64);\" title=\"valida dal ".date_format(date_create($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione")), "d/m/Y")." al ".date_format(date_create($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione")), "d/m/Y")."\">*</span></span> <span style=\"color: rgb(217, 118, 64);\">".number_format(floatval($offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent)-(floatval($offerta->getElementsByTagName("prezzoContabile")->item(0)->textContent)*(floatval($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale")/100))),2,".","")." Cr.</span></span>\n";
														}
														
														if($offerta->getElementsByTagName("scontoFuturo")->length!=0)
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"sconto\"><span>Promozione <span style=\"color: rgb(217, 118, 64);\" title=\"applicabile sul prossimo acquisto e valida dal ".date_format(date_create($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione")), "d/m/Y")." al ".date_format(date_create($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione")), "d/m/Y")."\">*</span></span> <span style=\"color: rgb(217, 118, 64);\">".$offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale")." %</span></span>\n";			
														
														if($offerta->getElementsByTagName("bonus")->length!=0)
															echo "\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"bonus\"><span>Bonus</span> <span style=\"color: rgb(217, 118, 64);\">".$offerta->getElementsByTagName("bonus")->item(0)->firstChild->textContent." Cr.</span></span>\n";
											
														// L'ULTIMA SEZIONE CHE ANDRÀ A COMPORRE LA SINGOLA OFFERTA SARÀ CARATTERIZZATA DA UN RIEPILOGO GENERALE DELLE RECENSIONI CHE IL BENE HA RICEVUTO NEL CORSO DEL TEMPO. PROPRIO PER QUESTO, SARÀ NECESSARIO ANDARE A DETERMINARE LA MEDIA INERENTE A CIASCUNO DEI SUOI PARAMETRI DI VALUTAZIONE
														// INOLTRE, LE VOCI DA TENERE IN CONSIDERAZIONE SARANNO SOLO ED ESCLUSIVAMENTE QUELLE CHE NON SONO ANCORA STATE MODERATE DAI GESTORI E DALL'AMMINISTRATORE DEL SITO 
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

														if($prodotto->getElementsByTagName("libro")->length)
														{
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

															// PER GARANTIRE CHE LA PROSSIMA ITERAZIONE VENGA ESEGUITA CORRETTAMENTE, BISOGNA AZZERARE LE VARIBILI UTILIZZATE PER LE CALCOLARE LE VARIE MEDIE
															$media_trama=$media_personaggi=$media_ambientazione=0;
																
															echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";			
															echo "\t\t\t\t\t\t\t\t\t\t</span>\n";			
															echo "\t\t\t\t\t\t\t\t\t</a>\n";
														}
														else
														{
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazioni\">\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazione\">\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Sceneggiatura</span> <span>".number_format($media_sceneggiatura,1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\" /></span>\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazione\">\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Tecnica</span> <span>".number_format($media_tecnica,1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\" /></span>\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"valutazione\">\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>Giocabilit&agrave;</span> <span>".number_format($media_giocabilita,1,".","")."<img src=\"../../Immagini/star-solid.svg\" alt=\"Immagine Non Disponibile...\" /></span>\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";
															echo "\t\t\t\t\t\t\t\t\t\t\t\t</span>\n";

															// PER GARANTIRE CHE LA PROSSIMA ITERAZIONE VENGA ESEGUITA CORRETTAMENTE, BISOGNA AZZERARE LE VARIBILI UTILIZZATE PER LE CALCOLARE LE VARIE MEDIE
															$media_sceneggiatura=$media_tecnica=$media_giocabilita=0;
																
															echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";			
															echo "\t\t\t\t\t\t\t\t\t\t</span>\n";			
															echo "\t\t\t\t\t\t\t\t\t</a>\n";
														}
													}
												}
												
												echo "\t\t\t\t\t\t\t\t</div>\n";
												
												// AL TERMINE DELLA PRIMA SEQUENZA DI STAMPE, È NECESSARIO ADEGUARE IL CONTENUTO DEL CONTATORE PIÙ ESTERNO PONENDOLO, IN VISTA DEL SUCCESSIVO INCREMENTO, AL VALORE DI QUELLO PIÙ INTERNO DECREMENTATO UNO
												$i=$j-1;
												
											}
										}
									?>
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
			require_once ("./footer_sito.php");
		?>
	</body>
</html>