<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA COMPILARE PER CONFERMARE LA VISIONE DELLA SEGNALAZIONE SELEZIONATA NELLA PAGINA DI RIEPILOGO LORO DEDICATA
	
	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");

	// PER QUESTIONI DI SICUREZZA, È STATO NECESSARIO L'INSERIMENTO DELLO SCRIPT "private_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA. QUESTO MECCANISMO NON PERMETTERÀ L'ACCESSO AGLI UTENTI CHE NON SI AUTENTICATI TRAMITE IL RELATIVO SERVIZIO
	require_once("./private_session_control.php");
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO AI GESTORI E ALL'AMMINISTRATORE DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]=="C") {
		header("Location: area_riservata.php");
	}
	
	// LA PAGINA, ESSENDO RAGGIUNGIBILE DOPO AVER SELEZIONATO UNA DETERMINATA NOTIFICA, NECESSITA DI UN ULTERIORE CONTROLLO PER APPURARE SE È STATA EFFETTIVAMENTE PRESA UNA DECISIONE IN MERITO O MENO 
	if(!isset($_GET["id_Segnalazione"]))
		header("Location: gestione_segnalazioni.php");
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_piattaforme_videogiochi.php");
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_segnalazioni.php");
	require_once("./apertura_file_recensioni.php");
	require_once("./apertura_file_discussioni.php");
	
	// NELL'OTTICA DI VOLER MANTENERE UN CERTO LIVELLO DI ROBUSTEZZA, ABBIAMO DECISO DI INTRODURRE DEI CONTROLLI PER VALUTARE SE LA SEGNALAZIONE A CUI SI RIFERISCE L'IDENTIFICATORE ESISTE REALMENTE O MENO
	$segnalazione_individuata=false;
	
	for($i=0; $i<$segnalazioni->length; $i++) {
		$segnalazione=$segnalazioni->item($i);
		
		// UNA VOLTA INDIVIDUATA LA NOTIFICA DI CUI SOPRA, SI POTRÀ INTERROMPERE LA RICERCA, IN QUANTO L'ENTITÀ CHE LA RAPPRESENTA SARÀ IMPIEGATA ALL'INTERNO DI SUCCESSIVE OPERAZIONI
		if($segnalazione->getAttribute("id")==$_GET["id_Segnalazione"] && $segnalazione->getAttribute("seen")=="No") {
			$segnalazione_individuata=true;
			break;
		}
	}
	
	if($segnalazione_individuata==false) {
		header("Location: riepilogo_segnalazioni.php");
	}
	
	// A SEGUITO DEI PRECEDENTI CONTROLLI, BISOGNERÀ REPERIRE TUTTE LE INFORMAZIONI CHE COINVOLGONO GLI ELEMENTI A CUI SI RIFERISCE LA SEGNALAZIONE SELEZIONATA. PROPRIO PER QUESTO, SI AVRÀ BISOGNO DI DISCRIMINARE LE VARIE COMPONENTI IN BASE AL TIPO DEL CONTRIBUTO D'INTERESSE
	// PER DI PIÙ, È STATO RITENUTO OPPORTUNO ESTENDERE IL PRECEDENTE MECCANISMO DI RICERCA ANCHE PER I RESTANTI SCENARI
	if($segnalazione->getElementsByTagName("perRecensione")->length!=0) {
		$recensione_individuata=false;
		
		$riferimento_recensione=$segnalazione->getElementsByTagName("perRecensione")->item(0)->getAttribute("idRecensione");
		
		for($i=0; $i<$recensioni->length && !$recensione_individuata; $i++) {
			$recensione=$recensioni->item($i);
			
			if($recensione->getAttribute("id")==$riferimento_recensione) 
				$recensione_individuata=true;
			
		}
		
		if(!$recensione_individuata)
			header("Location: riepilogo_segnalazioni.php");
	}
	else {
		if($segnalazione->getElementsByTagName("perDiscussione")->length!=0 xor $segnalazione->getElementsByTagName("perIntervento")->length!=0) {
			$discussione_individuata=false;
			
			if($segnalazione->getElementsByTagName("perDiscussione")->length!=0)
				$riferimento_discussione=$segnalazione->getElementsByTagName("perDiscussione")->item(0)->getAttribute("idDiscussione");
			else
				$riferimento_discussione=$segnalazione->getElementsByTagName("perIntervento")->item(0)->getAttribute("idDiscussione");
			
			for($i=0; $i<$discussioni->length && !$discussione_individuata; $i++) {
				$discussione=$discussioni->item($i);
				
				if($discussione->getAttribute("id")==$riferimento_discussione) 
					$discussione_individuata=true;
				
			}
			
			if(!$discussione_individuata)
				header("Location: riepilogo_segnalazioni.php");
			
			if($segnalazione->getElementsByTagName("perIntervento")->length!=0) {
				$intervento_individuato=false;
				
				$riferimento_intervento=$segnalazione->getElementsByTagName("perIntervento")->item(0)->getAttribute("idIntervento");
			
				for($i=0; $i<$discussione->getElementsByTagName("intervento")->length && !$intervento_individuato; $i++) {
					$intervento=$discussioni=$discussione->getElementsByTagName("intervento")->item($i);
					
					if($intervento->getAttribute("id")==$riferimento_intervento) 
						$intervento_individuato=true;
					
				}
				
				if(!$intervento_individuato)
					header("Location: riepilogo_segnalazioni.php");
			}
		}
	}
	
	// AL FINE DI GARANTIRE UN'ADEGUATA PRESENTAZIONE DELLE VARIE INFORMAZIONI DI INTERESSE, SI HA LA NECESSITÀ DI RICERCARE IL PRODOTTO (E L'EVENTUALE PIATTAFORMA DI GIOCO), E DUNQUE LA PROPOSTA DI VENDITA, A CUI È DESTINATO IL COMMENTO SEGNALATO
	$prodotto_individuato=false;
	
	if($segnalazione->getElementsByTagName("perRecensione")->length!=0) {
		for($i=0; $i<$prodotti->length && !$prodotto_individuato; $i++) {
			$prodotto=$prodotti->item($i);
			
			for($j=0; $j<$prodotto->getElementsByTagName("recensione")->length && !$prodotto_individuato; $j++) {
				
				if($recensione->getAttribute("id")==$prodotto->getElementsByTagName("recensione")->item($j)->getAttribute("idRecensione"))
					$prodotto_individuato=true;
			}
		}
	}
	else {
		for($i=0; $i<$prodotti->length && !$prodotto_individuato; $i++) {
			$prodotto=$prodotti->item($i);
			
			for($j=0; $j<$prodotto->getElementsByTagName("discussione")->length && !$prodotto_individuato; $j++) {
				
				if($discussione->getAttribute("id")==$prodotto->getElementsByTagName("discussione")->item($j)->getAttribute("idDiscussione"))
					$prodotto_individuato=true;
			}
		}
	}
	
	if(!$prodotto_individuato)
		header("Location: riepilogo_segnalazioni.php");
	
	if($prodotto->getElementsByTagName("videogioco")->length!=0) {
		for($i=0; $i<$piattaforme->length; $i++) {
			$piattaforma=$piattaforme->item($i);
			
			if($piattaforma->getAttribute("id")==$prodotto->getElementsByTagName("piattaforma")->item(0)->getAttribute("idPiattaforma"))
				break;
		}
	}
	
	// A PARTIRE DAL PRODOTTO, SARÀ POSSIBILE INDIVIDUARE L'EVENTUALE PROPOSTA DI VENDITA CHE LO COINVOLGE E A CUI REINDIRIZZARE I GESTORI O L'AMMINISTRATORE PER AVERE UNA CHIARA VISIONE DI QUANTO ACCADUTO
	// CONTRARIAMENTE AL CASO PRECEDENTE, NON RISULTERÀ NECESSARIO EFFFETTUARE UN CONTROLLO IN MERITO AL CONTENUTO DEL FLAG CHE SI ANDRÀ CREARE, IN QUANTO QUEST'ULTIMO VERRÀ UTILIZZATO PER INDICARE SE È POSSIBILE REINDIRIZZARE IL SOGGETTO D'INTERESSE VERSO LA PAGINA DI RIEPILOGO DELL'OFFERTA
	$offerta_individuata=false;
	
	for($i=0; $i<$offerte->length && !$offerta_individuata; $i++) {
		$offerta=$offerte->item($i);
		
		if($offerta->getAttribute("idProdotto")==$prodotto->getAttribute("id"))
			$offerta_individuata=true;
	}
	
	// PER DI PIÙ, COME ANCHE RIPORTATO ALL'INTERNO DELLA SCHERMATA DI RIEPILOGO DELLE SEGNALAZIONI, BISOGNERÀ MOSTRARE A SCHERMO I SOGGETTI COINVOLTI NELLA SEGNALAZIONE SELEZIONATA
	$sql="SELECT U1.Username AS Username_Segnalato, U2.Username AS Username_Segnalatore FROM $tab U1, $tab U2 WHERE U1.ID=".$segnalazione->getAttribute("idSegnalato")." AND U2.ID=".$segnalazione->getAttribute("idSegnalatore")." AND U1.Tipo_Utente='C' AND U2.Tipo_Utente='C'";
	$result=mysqli_query($conn, $sql);

	while($row=mysqli_fetch_array($result)) {
		$username_segnalato=$row["Username_Segnalato"];
		$username_segnalatore=$row["Username_Segnalatore"];
	}
	
	// IL PULSANTE AVENTE LA DICITURA "INDIETRO" PERMETTERÀ ALL'UTENTE DI TORNARE ALLA SCHERMATA PRECEDENTE A QUELLA CORRENTE
	if(isset($_POST["back"])) {
		header("Location: riepilogo_segnalazioni.php");
	}
	
	// UNA VOLTA CONFERMATE LE PROPRIE SCELTE, BISOGNA PROCEDERE CON L'ADEGUAMENTO DEL CONTENUTO INERENTE ALLA SEGNALAZIONE SELEZIONATA
	if(isset($_POST["confirm"])) {
		$segnalazione->setAttribute("seen", "Si");
		
		// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
		if($docSegnalazioni->schemaValidate("../../XML/Schema/Segnalazioni.xsd")){
			
			// AL FINE DI GARANTIRE UNA STAMPA GODIBILE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
			$docSegnalazioni->preserveWhiteSpace = false;
			$docSegnalazioni->formatOutput = true;
			$docSegnalazioni->save("../../XML/Segnalazioni.xml");
			
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
									<img src="../../Immagini/glasses-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Consulta e conferma i dettagli della notifica selezionata!</h2>
							</div>
						</div>
						<form class="corpo_form" action="<?php echo $_SERVER['PHP_SELF']."?id_Segnalazione=".$_GET["id_Segnalazione"]; ?>" method="post">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											<?php
												// POICHÈ SI VUOLE RENDERE LA PAGINA SPENDIBILE PER OGNUNO DEGLI ELEMENTI CHE SI PUÒ SEGNALARE, È STATO RITENUTO OPPORTUNO EFFETTUARE UNA SERIE DI CONTROLLI PER INDIVIDUARE A QUALE DELLE TRE ENTITÀ (RECENSIONI, DOMANDE E DISCUSSIONI) SI STA FACENDO RIFERIMENTO
												if($segnalazione->getElementsByTagName("perRecensione")->length!=0)
													echo "Recensione (Informativo)\n";
												else {
													if($segnalazione->getElementsByTagName("perDiscussione")->length!=0)
														echo "Discussione (Informativo)\n";
													else
														echo "Intervento (Informativo)\n";
												}
											?>
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Articolo <?php if($offerta_individuata) echo "<a href=\"riepilogo_scheda_offerta.php?id_Offerta=".$offerta->getAttribute("id")."\" style=\"text-decoration: none;\"><strong style=\"color: rgb(217, 118, 64); cursor: pointer;\" title=\"clicca qui per visualizzare l'offerta\">*</strong></a>\n"; else echo "<a href=\"elenco_risultati_ricerca.php?prodotto_Ricercato=".$prodotto->firstChild->textContent."\" style=\"text-decoration: none;\"><strong style=\"color: rgb(217, 118, 64); cursor: pointer;\" title=\"clicca qui per ricercare il prodotto nel catalogo\">*</strong></a>\n";?>
											</p>
											<p>
												<?php
													// POICHÈ L'INTENTO CONSISTE NEL PRESENTARE LE VARIE INFORMAZIONI NEL MIGLIOR MODO POSSIBILE, ABBIAMO DECISO DI SUDDIVIDERE LE STAMPE DEL NOME DEL PRODOTTO IN BASE ALLA NATURA STESSA DI QUES'ULTIMA
													// IN PARTICOLARE, I LIBRI, OLTRE A PRESENTARE L'ANNO IN CUI SONO STATI DISTRIBUITI PER LA PRIMA VOLTA, SARANNO CARATTERIZZATI DALLA DICITURA "Copertina Flessibile". D'ALTRO CANTO, PER I VIDEOGIOCHI SARÀ RIPORTATA LA PIATTAFORMA SU CUI È POSSIBILE MANDARLI IN ESECUZIONE 
													if($prodotto->getElementsByTagName("libro")->length!=0)
														echo "<input type=\"text\" disabled=\"disabled\" value=\"".$prodotto->firstChild->textContent." Copertina Flessibile - ".$prodotto->getElementsByTagName("annoUscita")->item(0)->textContent."\" />\n";
													else 
														echo "<input type=\"text\" disabled=\"disabled\" value=\"".$prodotto->firstChild->textContent." - ".$piattaforma->firstChild->textContent."\" />\n";
												?>
											</p>		
										</div>
										<div class="campo">
											<p>
												<?php 
													// ***
													if($segnalazione->getElementsByTagName("perRecensione")->length!=0) 
														echo "Condivisa da\n";
													else {
														if($segnalazione->getElementsByTagName("perDiscussione")->length!=0)
															echo "Avviata da\n";
														else
															echo "Pubblicato da\n";
													}
												?>
											</p>
											<p>
												<?php
													echo "<input type=\"text\" disabled=\"disabled\" value=\"".$username_segnalato."\" />\n";
												?>
											</p>		
										</div>
										<div class="campo">
											<p>
												Titolo <?php if($segnalazione->getElementsByTagName("perIntervento")->length!=0) echo "della Discussione\n"; else echo "\n"; ?>
											</p>
											<p>
												<?php
													// ***
													if($segnalazione->getElementsByTagName("perRecensione")->length!=0)
														echo "<input type=\"text\" disabled=\"disabled\" value=\"".$recensione->getElementsByTagName("titolo")->item(0)->textContent."\" />\n";
													else 
														echo "<input type=\"text\" disabled=\"disabled\" value=\"".$discussione->getElementsByTagName("titolo")->item(0)->textContent."\" />\n";
												?>
											</p>		
										</div>
										<div class="campo_descrizione">
											<p>
												Contenuto
											</p>
											<p>
												<?php
													// ***
													if($segnalazione->getElementsByTagName("perRecensione")->length!=0)
														echo "<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$recensione->getElementsByTagName("testo")->item(0)->textContent."</textarea>\n";
													else {
														if($segnalazione->getElementsByTagName("perDiscussione")->length!=0)
															echo "<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$discussione->getElementsByTagName("descrizione")->item(0)->textContent."</textarea>\n";
														else
															echo "<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$intervento->getElementsByTagName("testo")->item(0)->textContent."</textarea>\n";
													}
												?>
											</p>		
										</div>
										<?php
											// ***
											if($segnalazione->getElementsByTagName("perRecensione")->length!=0)
												echo "<p class=\"nota\"><strong>N.B.</strong> La precedente sezione si limita a riportare le informazioni principali inerenti alla recensione di interesse.</p>\n";
											else {
												if($segnalazione->getElementsByTagName("perDiscussione")->length!=0)
													echo "<p class=\"nota\"><strong>N.B.</strong> La precedente sezione si limita a riportare le informazioni principali inerenti alla discussione di interesse.</p>\n";
												else
													echo "<p class=\"nota\"><strong>N.B.</strong> La precedente sezione si limita a riportare le informazioni principali inerenti all'intervento di interesse.</p>\n";
											}
										?>
									</div>
								</div>
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Segnalazione (Informativo)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Inoltrata il <?php echo date_format(date_create($segnalazione->getAttribute("dataOraSegnalazione")), "d/m/Y"); ?> alle <?php echo date_format(date_create($segnalazione->getAttribute("dataOraSegnalazione")), "H:i"); ?> da
											</p>
											<p>
												<?php
													echo "<input type=\"text\" disabled=\"disabled\" value=\"".$username_segnalatore."\" />\n";
												?>
											</p>		
										</div>
										<div class="campo">
											<p>
												Categoria
											</p>
											<p>
												<?php
													echo "<input type=\"text\" disabled=\"disabled\" value=\"".$segnalazione->getAttribute("categoria")."\" />\n";
												?>
											</p>		
										</div>
										<div class="campo_descrizione">
											<p>
												Motivazioni
											</p>
											<p>
												<textarea disabled="disabled" rows="0" cols="0"><?php echo $segnalazione->getElementsByTagName("testo")->item(0)->textContent;?></textarea>
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> Le voci proposte rappresentano le informazioni contenute all'interno della segnalazione in esame.</p>		
									</div>
								</div>
								<div class="pulsante">
									<button type="submit" name="back" class="container_pulsante back">Annulla!</button>
									<button type="submit" name="confirm" class="container_pulsante">Conferma!</button>
								</div>  
							</div>
						</form>
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