<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTI I DETTAGLI INERENTI ALL'ACQUISTO SELEZIONATO DA UN CERTO CLIENTE

	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");

	// PER QUESTIONI DI SICUREZZA, È STATO NECESSARIO L'INSERIMENTO DELLO SCRIPT "private_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA. QUESTO MECCANISMO NON PERMETTERÀ L'ACCESSO AGLI UTENTI CHE NON SI AUTENTICATI TRAMITE IL RELATIVO SERVIZIO
	require_once("./private_session_control.php");
	
	// I CLIENTI DELLA PIATTAFORMA POSSONO SUBIRE UNA SOSPENSIONE DEL PROFILO A CAUSA DEL LORO COMPORTAMENTO. PROPRIO PER QUESTO, E CONSIDERANDO CHE CIÒ PUÒ AVVENIRE IN QUALUNQUE MOMENTO, BISOGNERÀ MONITORARE COSTANTEMENTE I LORO "PERMESSI" COSÌ DA IMPEDIRNE LA NAVIGAZIONE VERSO LE SEZIONI PIÙ SENSIBILI DEL SITO 
	require_once("./monitoraggio_stato_account.php");
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");
	
	// LE PROPOSTE DI VENDITA, DATA LA LORO STRUTTURA, POTREBBERO ESSERE SOGGETTE A DELLE RIDUZIONI DI PREZZO, CARATTERIZZATE DA UN INTERVALLO DI VALIDITÀ BEN DEFINITO E TALVOLTA APPLICABILI SUL PROSSIMO ACQUISTO. PERTANTO, NELLE IPOTESI IN CUI NON SIA SEMPRE POSSIBILE RIMUOVERE MANUALMENTE LE VOCI INTERESSATE, ABBIAMO DECISO DI IMPLEMENTARE UN CODICE IN GRADO DI INTERVENIRE IN AUTOMATICO SU TUTTI QUELLI SCONTI NON PIÙ USUFRUIBILI DAI CLIENTI DELLA PIATTAFORMA
	require_once("./rimozione_sconti.php");
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO AI CLIENTI DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="C") {
		header("Location: area_riservata.php");
	}
	
	// LA PAGINA, ESSENDO RAGGIUNGIBILE DOPO AVER SELEZIONATO UN DETERMINATO ACQUISTO DALLA RELATIVA PAGINA DI RIEPILOGO, NECESSITA DI UN ULTERIORE CONTROLLO PER APPURARE SE È STATA EFFETTIVAMENTE PRESA UNA DECISIONE IN MERITO O MENO 
	if(!isset($_GET["id_Acquisto"]))
		header("Location: riepilogo_acquisti.php");
	
	// COME PER I FILE XML, SI PROCEDE CON UN'INTERROGAZIONE ALLA BASE DI DATI SUDDETTA AL FINE DI REPERIRE I DATI RELATIVI ALL'UTENTE COINVOLTO  
	$sql="SELECT Nome, Cognome, Num_Telefono FROM $tab WHERE ID=".$_SESSION["id_Utente"];
	$result=mysqli_query($conn, $sql);
	
	// NEL CASO IN CUI CI SIANO DELLE CORRISPONDENZE (IN REALTÀ UNA SOLTANTO), SI PROCEDE CON IL SALVATAGGIO DI TUTTI GLI ELEMENTI DI CUI SI È FATTA RICHIESTA
	while($row=mysqli_fetch_array($result))
	{
		$nome=$row["Nome"];
		$cognome=$row["Cognome"];
		$num_telefono=$row["Num_Telefono"];
	}
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_acquisti.php");
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_piattaforme_videogiochi.php");
	
	// NELL'OTTICA DI VOLER MANTENERE UN CERTO LIVELLO DI ROBUSTEZZA, ABBIAMO DECISO DI INTRODURRE DEI CONTROLLI PER VALUTARE SE L'ACQUISTO A CUI SI RIFERISCE L'IDENTIFICATORE ESISTE REALMENTE O MENO
	$acquisto_individuato=false;
	
	for($i=0; $i<$acquisti->length; $i++)
	{
		$acquistiPerCliente=$acquisti->item($i);
		
		if($acquistiPerCliente->getAttribute("idCliente")==$_SESSION["id_Utente"])
		{
			for($j=0; $j<$acquistiPerCliente->getElementsByTagName("acquistoPerCliente")->length; $j++)
			{
				$acquistoPerCliente=$acquistiPerCliente->getElementsByTagName("acquistoPerCliente")->item($j);
				
				// UNA VOLTA INDIVIDUATO L'ACQUISTO DI INTERESSE, SI POTRÀ INTERROMPERE LA RICERCA, IN QUANTO L'ENTITÀ CHE LO RAPPRESENTA SARÀ IMPIEGATA ALL'INTERNO DI SUCCESSIVE OPERAZIONI
				if($acquistoPerCliente->getAttribute("id")==$_GET["id_Acquisto"]){
					$acquisto_individuato=true;
					break;
				}
			}
		}
	}
	
	if($acquisto_individuato==false)
		header("Location: riepilogo_acquisti.php");
	
	// PER EFFETTUARE UNA CORRETTA PRESENTAZIONE DEL CONTENUTO DELL'ORDINE, SI PROCEDE CON IL CALCOLO DEL SUBTOTALE E DEL TOTALE INERENTI ALLE OFFERTE CONTENUTE ALL'INTERNO DI QUEST'ULTIMO
	$subtotale=0.00;
	$totale=0.00;
	
	for($i=0; $i<$acquistoPerCliente->getElementsByTagName("offerta")->length; $i++) {
		$offerta=$acquistoPerCliente->getElementsByTagName("offerta")->item($i);
		
		if($offerta->getElementsByTagName("scontoATempo")->length!=0)
			$subtotale=number_format(intval($offerta->getElementsByTagName("quantitativo")->item(0)->textContent)*(floatval($offerta->firstChild->textContent) - (floatval($offerta->firstChild->textContent)*(floatval($offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"))/100))) + $subtotale, 2,".","");
		else
			$subtotale=number_format(intval($offerta->getElementsByTagName("quantitativo")->item(0)->textContent)*(floatval($offerta->firstChild->textContent)) + $subtotale, 2,".","");  
	}
	
	$totale=number_format($subtotale - ($subtotale*(floatval($acquistoPerCliente->getAttribute("scontoGenerale"))/100)), 2,".","");
	
	// INOLTRE, SARÀ NECESSARIO RIPORTARE IL NUMERO DEGLI ARTICOLI CHE COMPONGONO L'ACQUISTO IN QUESTIONE
	$num_articoli=0;
	
	for($j=0; $j<$acquistoPerCliente->getElementsByTagName("offerta")->length; $j++)
		$num_articoli=$num_articoli + $acquistoPerCliente->getElementsByTagName("offerta")->item($j)->getElementsByTagName("quantitativo")->item(0)->textContent;
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
									<img src="../../Immagini/file-invoice-dollar-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Visualizza il tuo acquisto!</h2>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<div class="campo_riferimenti_temporali">
									<div class="container_campo_riferimenti_temporali">
										<span class="riferimento_temporale">
											Pagato: <?php  echo date_format(date_create($acquistoPerCliente->getAttribute("dataAcquisto")),"d/m/Y")."\n"; ?>
										</span>
										<span class="riferimento_temporale">
											<?php 
												// PER GARANTIRE UNA CERTA DINAMICITÀ IN RELAZIONE ALLE TEMPISTICHE DI ACQUISTO E DI CONSEGNA DELL'ORDINE, È STATA EFFETTUATA UNA STAMPA CHE, IN BASE ALL'ESITO DEL RELATIVO CONTROLLO, RIPORTERÀ L'ISTANTE IN CUI È STATO RICHIESTO E RECAPITATO IL PACCO
												if(strtotime(date("Y-m-d"))>strtotime($acquistoPerCliente->getAttribute("dataConsegna")))
													echo "Consegnato: ".date_format(date_create($acquistoPerCliente->getAttribute("dataConsegna")),"d/m/Y")."\n";
												else
													echo "In Consegna: ".date_format(date_create($acquistoPerCliente->getAttribute("dataConsegna")),"d/m/Y")."\n";
											?>
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi" style="border: 0em; padding-bottom: 0em; margin-bottom: 0em;" >
										<div class="campo">
											<div class="container_campo" style="margin-left: 0em; margin-right: 0em;">
												<div class="dettagli_ordine">
													<div class="container_dettagli_ordine">
														<div class="intestazione_dettagli_ordine">
															<h3>Indirizzo di Spedizione</h3>
															<div class="corpo_dettagli_ordine">
																<ul class="container_corpo_dettagli_ordine">
																	<li><span><?php echo $nome." ".$cognome; ?></span></li>
																	<li><span><?php echo $acquistoPerCliente->getAttribute("indirizzoConsegna"); ?></span></li>
																	<li><span><?php echo $acquistoPerCliente->getAttribute("cittaConsegna").", ".$acquistoPerCliente->getAttribute("capConsegna"); ?></span></li>
																	<li style="margin-top: 0.875em;"><span><?php echo $num_telefono; ?></span></li>
																</ul>
															</div>
														</div>
														<div class="intestazione_dettagli_ordine">
															<h3>Informazioni Aggiuntive</h3>
															<div class="corpo_dettagli_ordine">
																<ul class="container_corpo_dettagli_ordine">
																	<li><span style="font-weight:bold;">Pagamento</span> <span>Crediti</span></li>
																	<li><span style="font-weight:bold;">Spedizione</span> <span>Corriere LEV</span></li>
																	<li><span style="font-weight:bold;">Venditore</span> <span>LEV</span></li>
																</ul>
															</div>
														</div>
														<div class="intestazione_dettagli_ordine">
															<h3>Sommario</h3>
															<div class="corpo_dettagli_ordine">
																<ul class="container_corpo_dettagli_ordine">
																	<li><span>Contenuto</span> <span><?php echo $num_articoli." Art."; ?></span></li>
																	<li><span>Subtotale</span> <span><?php echo number_format($subtotale, 2,".","")." Cr."; ?></span></li>
																	<li><span>Sconto</span> <span><?php echo number_format($acquistoPerCliente->getAttribute("scontoGenerale"), 2,".","")." %"; ?></span></li>
																	<li style="font-weight:bold; margin-top: 0.875em;"><span>Totale</span> <span><?php echo number_format($totale, 2,".","")." Cr."; ?></span></li>	
																</ul>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Riepilogo Contenuto
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi" style="border: 0em; padding-bottom: 0em; margin-bottom: 0em;" >
										<div class="campo">
											<div class="container_campo">
												<?php
													// L'INTESTAZIONE DELLA TABELLA SARÀ UN FATTORE STATICO IN CUI VERRANNO PRESENTATI TUTTI GLI ELEMENTI, DEFINITI COME COLONNE, CHE LA COMPORRANNO 
													echo "<table>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<thead>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<tr>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Quantit&agrave;</th>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Articolo <strong style=\"color: rgb(217, 118, 64);\" title=\"ricerca le offerte che coinvolgono i vari prodotti premendo il loro nome\">*</strong></th>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Info.</th>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Prezzo <strong style=\"color: rgb(217, 118, 64);\" title=\"(Cr.) &amp; al lordo di un'eventuale sconto\">*</strong></th>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Sconto <strong style=\"color: rgb(217, 118, 64);\" title=\"(%)\">*</strong></th>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\" style=\"text-align: left;\">Bonus <strong style=\"color: rgb(217, 118, 64);\" title=\"(Cr.)\">*</strong></th>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</tr>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t</thead>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t\t<tbody>\n";
													
													// IL CORPO DELLA COMPONENTE DI CUI SOPRA VERRÀ COMPOSTO DINAMICAMENTE E SEGUENDO L'ORDINE INDOTTO DALLE PRECEDENTI STAMPE
													for($i=0; $i<$acquistoPerCliente->getElementsByTagName("offerta")->length; $i++){
														$offerta=$acquistoPerCliente->getElementsByTagName("offerta")->item($i);
														
														// ALLO SCOPO DI PRESENTARE TUTTE LE INFORMAZIONI SOPRA RIPORTATE, È NECESSARIO FARE RIFERIMENTO ALLE VARIE STRUTTURE DATI COINVOLTE
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
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$piattaforma->firstChild->textContent."; <span style=\"font-weight: bold;\">".$prodotto->getElementsByTagName("casaProduzione")->item(0)->textContent."</span></td>\n";
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
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale")."<strong style=\"color: rgb(217, 118, 64);\" title=\"inerente all'offerta corrente\">*</strong></td>\n";
															else
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale")."<strong style=\"color: rgb(217, 118, 64);\" title=\"inerente ad un acquisto futuro\">*</strong></td>\n";
														}
														else
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">0.00</td>\n";
														
														if($offerta->getElementsByTagName("bonus")->length!=0) 
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\" style=\"text-align: left;\">".$offerta->getElementsByTagName("bonus")->item(0)->firstChild->textContent."</td>\n";
														else
															echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\" style=\"text-align: left;\">0.00</td>\n";
														
														echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</tr>\n";
														
													}
													echo "\t\t\t\t\t\t\t\t\t\t\t\t</tbody>\n";
													echo "\t\t\t\t\t\t\t\t\t\t\t</table>\n";
												?>
											</div>	
										</div>
									</div>
								</div>
								<div class="pulsante" style="justify-content: center; margin-bottom: 0%;">
									<form action="riepilogo_acquisti.php" method="post">
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