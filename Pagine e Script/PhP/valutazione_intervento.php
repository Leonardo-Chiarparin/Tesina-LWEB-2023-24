<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA COMPILARE PER GIUDICARE IL GRADO DI SUPPORTO E DI UTILITÀ DI UN INTERVENTO INERENTE AD UNA CERTA DISCUSSIONE
	// N.B.: IN CASO DI ERRORE, LE INFORMAZIONI INSERITE, COSÌ COME LE SCELTE EFFETTUATE, VERRANNO PRESERVATE TRAMITE UNA SERIE DI CONTROLLI APPLICATI AL SOLO SCOPO DI AGEVOLARE L'OPERATO DEI VARI UTENTI D'INTERESSE
	
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
	
	// LA PAGINA, ESSENDO RAGGIUNGIBILE DOPO AVER PREMUTO IL PULSANTE PER INSERIRE VALUTARE UN DETERMINATO INTERVENTO, NECESSITA DI UN ULTERIORE CONTROLLO PER APPURARE SE È STATA EFFETTIVAMENTE PRESA UNA DECISIONE IN MERITO O MENO 
	if(!isset($_GET["id_Offerta"]))
		header("Location: index.php");
	
	// ***
	if(!(isset($_GET["id_Discussione"]) && isset($_GET["id_Intervento"]))) {
		$id_Offerta=$_GET["id_Offerta"];
		
		header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
	}
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_discussioni.php");
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_acquisti.php");
	require_once("./apertura_file_riduzioni.php");
	require_once("./apertura_file_tariffe.php");
	
	
	// NELL'OTTICA DI VOLER MANTENERE UN CERTO LIVELLO DI ROBUSTEZZA, ABBIAMO DECISO DI INTRODURRE DEI CONTROLLI PER VALUTARE SE L'OFFERTA A CUI SI RIFERISCE L'IDENTIFICATORE ESISTE REALMENTE O MENO
	$offerta_individuata=false;
	
	for($i=0; $i<$offerte->length; $i++) {
		$offerta=$offerte->item($i);
		
		// UNA VOLTA INDIVIDUATA LA PROPOSTA DI INTERESSE, SI POTRÀ INTERROMPERE LA RICERCA, IN QUANTO L'ENTITÀ CHE LA RAPPRESENTA SARÀ IMPIEGATA ALL'INTERNO DI SUCCESSIVE OPERAZIONI
		if($offerta->getAttribute("id")==$_GET["id_Offerta"]) {
			$offerta_individuata=true;
			break;
		}
	}
	
	if($offerta_individuata==false) {
		header("Location: index.php");
	}
	
	// DATA LA LORO SOMIGLIANZA, IL MECCANISMO APPLICATO IN PRECEDENZA PER LE OFFERTE POTRÀ ESSERE ESTESO ANCHE PER I SINGOLI ELEMENTI DA VALUTARE
	$discussione_individuata=false;
		
	for($i=0; $i<$discussioni->length; $i++) {
		$discussione=$discussioni->item($i);
		
		// ***
		if($discussione->getAttribute("id")==$_GET["id_Discussione"]) {
			$discussione_individuata=true;
			break;
		}
	}
	
	// NEL CASO IN CUI IL CONTRIBUTO INDICATO NON SIA PRESENTE ALL'INTERNO DEL RELATIVO FILE XML O SE È GIÀ STATO MODERATO, SI PROVVEDERÀ A REINDIRIZZARE L'UTENTE VERSO LA SCHERMATA DI RIEPILOGO DELLA PROPOSTA DI VENDITA
	if($discussione_individuata==false || $discussione->getAttribute("moderata")=="Si") {
		$id_Offerta=$_GET["id_Offerta"];
	
		header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
	}
	
	// ***
	$intervento_individuato=false;

	for($i=0; $i<$discussione->getElementsByTagName("intervento")->length; $i++) {
		$intervento=$discussione->getElementsByTagName("intervento")->item($i);
		
		// ***
		if($intervento->getAttribute("id")==$_GET["id_Intervento"]) {
			$intervento_individuato=true;
			break;
		}
	}
	
	if($intervento_individuato==false || $intervento->getAttribute("moderato")=="Si") {
		$id_Offerta=$_GET["id_Offerta"];
	
		header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
	}
	
	// PER I CONTROLLI CHE VERRANNO APPLICATI PIÙ IN BASSO, SI HA LA NECESSITÀ DI INDIVIDUARE IL PRODOTTO A CUI SI RIFERISCE LA PROPOSTA DI VENDITA SELEZIONATA  
	for($i=0; $i<$prodotti->length; $i++) {
		$prodotto=$prodotti->item($i);
		
		if($prodotto->getAttribute("id")==$offerta->getAttribute("idProdotto"))
			break;
	}
	
	// INOLTRE, BISOGNERÀ FARE IN MODO CHE L'ELEMENTO DI INTERESSE SIA RICONDUCIBILE AL PRODOTTO A CUI SI RIFERISCE L'OFFERTA SELEZIONATA. COME ANTICIPATO, NEL CASO DI UN SEMPLICE INTERVENTO BASTERÀ EFFETTUARE IL CONFRONTO SULLA DISCUSSIONE A CUI APPARTIENE
	$contributo_esistente=false;
	
	if(isset($_GET["id_Discussione"])) {
		for($i=0; $i<$prodotto->getElementsByTagName("discussione")->length && !$contributo_esistente; $i++) {
			if($prodotto->getElementsByTagName("discussione")->item($i)->getAttribute("idDiscussione")==$discussione->getAttribute("id"))
				$contributo_esistente=true;
		}
	}
	
	if(!$contributo_esistente) {
		$id_Offerta=$_GET["id_Offerta"];
		
		header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
	}
	
	// TRA I DETTAGLI CHE MEGLIO SI PRESTANO A RAPPRESENTARE UN DETERMINATO CONTRIBUTO, VI È SICURAMENTE L'USERNAME DELL'UTENTE CHE LO HA PUBBLICATO
	if(isset($_GET["id_Intervento"])) {
		$sql="SELECT Username FROM $tab WHERE ID=".$intervento->getAttribute("idPartecipante");
		$result=mysqli_query($conn, $sql);
		
		while($row=mysqli_fetch_array($result)) 
			$username=$row["Username"];
	}
	
	// CONTRARIAMENTE A MOLTE ALTRE SCHERMATE, UN CASO PARTICOLARE DI REINDERIZZAMENTO SI HA QUANDO L'UTENTE PREME SUL PULSANTE PER TORNARE ALLA PAGINA PRECEDENTE. INFATTI, POICHÈ FINORA SI È FATTO RIFERIMENTO AI VALORI PASSATI TRAMITE METODO GET, BISOGNERÀ PREDISPORRE IL NUOVO INDIRIZZO IN MODO TALE DA GARANTIRE NUOVAMENTE LA STAMPA DELLA PROPOSTA DI VENDITA SELEZIONATA IN PRECEDENZA DAL SOGGETTO D'INTERESSE
	if(isset($_POST["back"])) {
		// PER RIUSCIRCI, SARÀ SUFFICEINTE ASSEGNARE IL VALORE DI CUI SOPRA AD UNA VARIABILE "TEMPORANEA" E, IN SEGUITO, UTILIZZARE OPPORTUNAMENTE LA FUNZIONE header() 
		$id_Offerta=$_GET["id_Offerta"];
		
		// EVIDENTEMENTE, LA CONDIVISIONE DEL DATO IN QUESTIONE È STATA GESTITA COME SOPRA PER EVITARE LA CREAZIONE DI ULTERIORI VARIBILI DI SESSIONE, LE QUALI, DATA LA LIBERTÀ DI NAVIGAZIONE CONCESSA ALL'UTENTE, AVREBBERO DOVUTO ESSERE RIMOSSE IN OGNI ALTRO SCRIPT
		header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
	}
	
	// UNA VOLTA CONFERMATE LE PROPRIE SCELTE, BISOGNA PROCEDERE CON I CONTROLLI PER VALUTARE LA CORRETTEZZA E L'INTEGRITÀ DEI VALORI INDICATI ALL'INTERNO DEI VARI CAMPI
	if(isset($_POST["confirm"])) {
		// IL PRIMO PASSO CONSISTE NELLA RIMOZIONE DEI POSSIBILI SPAZI (BIANCHI) COLLOCATI ALL'INIZIO E ALLA FINE DEGLI ELEMENTI COINVOLTI
		$_POST["supporto"]=trim($_POST["supporto"]);
		$_POST["supporto"]=rtrim($_POST["supporto"]);
		
		$_POST["utilita"]=trim($_POST["utilita"]);
		$_POST["utilita"]=rtrim($_POST["utilita"]);
		
		// GIUNTI A QUESTO PUNTO, E SECONDO L'ORDINAMENTO INDOTTO DALLE PRECEDENTI OPERAZIONI, È NECESSARIO EFFETTUARE UN ULTERIORE CONTROLLO PER VALUTARE SE SI È EFFETTIVAMENTE INSERITO QUALCOSA ALL'INTERNO DEI VARI CAMPI 
		if(strlen($_POST["supporto"])==0 || strlen($_POST["utilita"])==0) {
			// IN CASO DI ERRORE, SI DOVRÀ INFORMARE L'UTENTE COINVOLTO DELLE MANCANZE CHE HA AVUTO NEI CONFRONTI DEL MODULO PER L'INSERIMENTO DELLA PROPOSTA DI VENDITA. UN SIMILE MECCANISMO VERRÀ IMPLEMENTATO PER TUTTE LE CASISITCHE IN CUI SARÀ IMPOSSIBILE PROCEDERE  
			$campi_vuoti=true;
		}
		else {
			// PRIMA DI PROCEDERE CON L'INSERIMENTO DELLA NUOVA RECENSIONE, BISOGNA EFFETTUARE DEI CONTROLLI PER VALUTARE SE UN DETERMINATO ELEMENTO ECCEDE LA DIMENSIONE O NON RISPETTA IL FORMATO INDICATO
			// NEL DETTAGLIO, I PARAMETRI DI VALUTAZIONE INERENTI ALL'INTERVENTO DOVRANNO ESSERE DEI VALORI INTERI COMPRESI NELL'INTERVALLO (1,5) CON I DUE ESTREMI INCLUSI. PER POTER IMPLEMENTARE UN SIMILE CONTROLLO, ABBIAMO DECISO DI UTILIZZARE, RISPETTIVAMENTE, I METODI range(...)  E in_array(...)     
			if(!(in_array($_POST["supporto"], range(1,5))) || !(in_array($_POST["utilita"], range(1,5)))) {
				// ***
				$parametri_errati=true;
			}
			else {
				// GIUNTI A QUESTO PUNTO, È DOVEROSO VALUTARE SE L'UTENTE IN QUESTIONE HA GIÀ VALUTATO L'INTERVENTO D'INTERESSE
				$intervento_valutato=false;
				
				for($i=0; $i<$intervento->getElementsByTagName("valutazione")->length && !$intervento_valutato; $i++) {
					$valutazione_intervento=$intervento->getElementsByTagName("valutazione")->item($i);
					
					if($valutazione_intervento->getAttribute("idVotante")==$_SESSION["id_Utente"]) {
						$intervento_valutato=true;
					}
				}
				
				// PER CONCLUDERE, È POSSIBILE PROCEDERE CON L'EFFETIVO INSERIMENTO DELLA NUOVA OCCORRENZA ALL'INTERNO DEL FILE XML
				if(!$intervento_valutato) {
					
					// LA RAPPRESENTAZIONE DI UNA VALUTAZIONE È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUEST'ULTIMA. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI INDICATI DALL'UTENTE
					$nuova_valutazione=$docDiscussioni->createElement("valutazione");
					$nuova_valutazione->setAttribute("idVotante", $_SESSION["id_Utente"]);
					$nuova_valutazione->setAttribute("supporto", $_POST["supporto"]);
					$nuova_valutazione->setAttribute("utilita", $_POST["utilita"]);
					
					$valutazioni=$intervento->getElementsByTagName("valutazioni")->item(0);
					
					$valutazioni->appendChild($nuova_valutazione);
					
					// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
					if($docDiscussioni->schemaValidate("../../XML/Schema/Discussioni.xsd")){
						
						// AL FINE DI GARANTIRE UNA STAMPA GODIBILE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
						$docDiscussioni->preserveWhiteSpace = false;
						$docDiscussioni->formatOutput = true;
						$docDiscussioni->save("../../XML/Discussioni.xml");
						
						// UNA VOLTA MEMORIZZATA LA NUOVA VALUTAZIONE, SARÀ NECESSARIO ANDARE AD AGGIORNARE LA REPUTAZIONE DELL'UTENTE CHE L'HA RICEVUTA IN BASE AI PUNTEGGI INDICATI
						// PER DI PIÙ, SE IL SOGGETTO COINVOLTO HA ACQUISTATO L'ARTICOLO COINVOLTO, IL PESO DI OGNUNO DEI PARAMETRI RISULTERÀ DOPPIO
						require("./ricerca_prodotto_tra_acquisti_intervento.php");
						
						if($prodotto_acquistato) {
							$peso_valutazioni=2;
						}
						else {
							$peso_valutazioni=1;
						}
						
						$punti_rettifica_reputazione=0;
						
						// AL FINE DI INDIVIDUARE CORRETTAMENTE IL CONTESTO DI INTERESSE, ABBIAMO DECISO DI UTILIZZARE IL COTRUTTO switch(...). INOLTRE, IN FUNZIONE DEI PRECEDENTI CONTROLLI, È POSSIBILE OMETTERE LA CASISTICA DI DEFAULT, IN QUANTO NON PREVISTA
						// IN PARTICOLARE, LE VALUTAZIONI PARI AD 1 O 2 PORTERANNO AD UNA DIMINUZIONE DELLA REPUTAZIONE. D'ALTRO CANTO, LE ULTIME DUE VOCI CONSENTIRANNO DI AUMETARLA 
						switch($_POST["supporto"]) {
							case 1:
								$punti_rettifica_reputazione=$punti_rettifica_reputazione+($peso_valutazioni*(-3));
							break;
							
							case 2:
								$punti_rettifica_reputazione=$punti_rettifica_reputazione+($peso_valutazioni*(-1));
							break;
							
							case 3:
								$punti_rettifica_reputazione=$punti_rettifica_reputazione;
							break;
							
							case 4:
								$punti_rettifica_reputazione=$punti_rettifica_reputazione+($peso_valutazioni*(1));
							break;
							
							case 5:
								$punti_rettifica_reputazione=$punti_rettifica_reputazione+($peso_valutazioni*(3));
							break;
						}
						
						// LA MEDESIMA POTRÀ ESSERE REPLICATA ANCHE PER L'UTILITÀ DEL COMMENTO
						switch($_POST["utilita"]) {
							case 1:
								$punti_rettifica_reputazione=$punti_rettifica_reputazione+($peso_valutazioni*(-3));
							break;
							
							case 2:
								$punti_rettifica_reputazione=$punti_rettifica_reputazione+($peso_valutazioni*(-1));
							break;
							
							case 3:
								$punti_rettifica_reputazione=$punti_rettifica_reputazione;
							break;
							
							case 4:
								$punti_rettifica_reputazione=$punti_rettifica_reputazione+($peso_valutazioni*(1));
							break;
							
							case 5:
								$punti_rettifica_reputazione=$punti_rettifica_reputazione+($peso_valutazioni*(3));
							break;
						}
						
						// PRIMA DI PROCEDERE CON LA MODIFICA INERENTE ALLA REPUTAZIONE DI UN CERTO UTENTE, BISOGNERÀ FARE IN MODO CHE CONTINUI AD ESSERE UN VALORE COMPRESO NELL'INTERVALLO TRA 0 E 100 (INCLUSI), I QUALI, IN CASO DI SUPERAMENTO, VERRANNO DESIGNATI COME I NUOVI PUNTEGGI DA ATTRIBUIRE AI SOGGETTI COINVOLTI
						$sql="SELECT Reputazione FROM $tab WHERE ID=".$intervento->getAttribute("idPartecipante");
						$result=mysqli_query($conn, $sql);
						
						while($row=mysqli_fetch_array($result)) 
							$reputazione=$row["Reputazione"];
						
						if($reputazione+$punti_rettifica_reputazione>100)
							$reputazione=100;
						else {
							if($reputazione+$punti_rettifica_reputazione<0)
								$reputazione=0;
							else
								$reputazione+=$punti_rettifica_reputazione;
						}
						
						// DATO L'INTENTO DI VOLER CONFRONTARE L'ESITO DI UNA DETERMINATA QUERY, SARÀ NECESSARIO PREDISPORRE IL TUTTO ALL'INTERNO DI UN COSTRUTTO try ... catch ... AL FINE DI CATTURARE L'EVENTUALE ECCEZIONE E NOTIFICARE L'ACCADUTO ALL'UTENTE IN OGGETTO
						// INFATTI, UN POSSIBILE FALLIMENTO POTREBBE DIPENDERE DAL SUPERAMENTO DEL LIMITE DI CARATTERI CHE POSSONO ESSERE INSERITI ALL'INTERNO DI UN CAMPO DELLA TABELLA RELAZIONALE COINVOLTA 
						try {
							// SE NON È STATA EVIDENZIATA ALCUNA SORTA DI PROBLEMATICA, È POSSIBILE EFFETTUARE L'ADEGUAMENTO DEI DATI ALL'INTERNO DELLA BASE DI DATI
							$sql="UPDATE $tab SET Reputazione=".$reputazione." WHERE ID=".$intervento->getAttribute("idPartecipante");
							
							// COME ACCENNATO, PRIMA DI CONCLUDERE L'OPERAZIONE BISOGNERÀ VALUTARE L'ESITO DELL'ESECUZIONE INERENTE AL PRECEDENTE COMANDO SQL
							if(mysqli_query($conn,$sql)){
								
								// L'ULTIMO PASSO DA APPLICARE CONSISTE NEL VALUTARE SE, A SEGUITO DI UN POSSIBILE INCREMENTO DEI PUNTI INERENTI ALLA REPUTAZIONE (E DUNQUE AL RAGGIUNGIMENTO DEL LIVELLO PIÙ ALTO), L'UTENTE (CLIENTE) HA ACQUISITO LA POSSIBILITÀ DI USUFRUIRE DELLO SCONTO PER VIP
								$sql="SELECT Username FROM $tab WHERE ID=".$intervento->getAttribute("idPartecipante")." AND Tipo_Utente='C' AND Reputazione>=".$rootTariffe->getElementsByTagName("tariffaPerVIP")->item(0)->getAttribute("sogliaReputazione");
								$result=mysqli_query($conn, $sql);
								
								if(mysqli_num_rows($result)!=0) {
									
									// GIUNTI A QUESTO PUNTO, SI EFFETTUA LA SCANSIONE DEL FILE RELATIVO ALLO STATO DELLE RIDUZIONI DEI CLIENTI ALLA RICERCA DI QUELLE CHE SI RIFERISCONO AL SOGGETTO DI CUI SOPRA									
									for($i=0; $i<$riduzioni->length; $i++) {
										$riduzione=$riduzioni->item($i);
										
										if($riduzione->getAttribute("idCliente")==$intervento->getAttribute("idPartecipante")) {
											$riduzione->getElementsByTagName("perVIP")->item(0)->setAttribute("fruibile", 1);
											
											// DAL MOMENTO CHE QUEST'ULTIMO DOCUMENTO SI RIFERISCE AD UNA STRUTTURA DESCRITTA TRAMITE DTD, È NECESSARIO SALVARNE PREVENTIVAMENTE IL CONTENUTO E IN SEGUITO VALUTARNE LA CORRETTEZZA TRAMITE IL METODO validate() 
											$docRiduzioni->preserveWhiteSpace = false;
											$docRiduzioni->formatOutput = true;
											$docRiduzioni->save("../../XML/Riduzioni_Prezzi.xml");
											
											$dom = new DOMDocument;
											$dom->load("../../XML/Riduzioni_Prezzi.xml");
											
											if(!($dom->validate())) {
												// ***
												setcookie("errore_Validazione", true);
												
												$id_Offerta=$_GET["id_Offerta"];
												
												header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
											}
										}
									}
								}
								
								// PRIMA DI ESSERE REINDIRIZZATI, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
								$_SESSION["modifica_Effettuata"]=true;
								
								$id_Offerta=$_GET["id_Offerta"];
								
								header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
								
							}
							else
							{ 
								throw new mysqli_sql_exception;	   
							}
						}
						catch (mysqli_sql_exception $e){
							
							// *** 
							$errore_query=true;
						}
					}
					else {
						
						// ***
						setcookie("errore_Validazione", true);
						
						$id_Offerta=$_GET["id_Offerta"];
						
						header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
					}
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
			// DATA LA VARIETÀ DI CASISTICHE CHE SI POSSONO MANIFESTARE, ABBIAMO DECISO DI DEFINIRE UNA GERARCHIA DI CONTROLLI PER GESTIRE AL MEGLIO LE STAMPE DEI VARI MESSAGGI DI POPUP, I QUALI, MEDIANTE UNA SIMILE IMPOSTAZIONE, SARANNO PRESENTATI SINGOLARMENTE AI SOGGETTI INTERESSATI
			if(isset($campi_vuoti) && $campi_vuoti) { 
				
				// IN OCCASIONE DEL POSSIBILE RICARICAMENTO DELLA PAGINA, SI PROCEDE CON LA RIMOZIONE DEL FLAG ALLO SCOPO DI NON RIPROPORRE LA MEDESIMA NOTIFCA 
				$campi_vuoti=false;
				
				echo "<div class=\"error_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
				echo "\t\t\t\t\t<p>COMPILARE TUTTI I CAMPI...</p>\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t</div>\n";
				echo "\t\t</div>\n";
			}
			else {
				// ***
				if(isset($parametri_errati) && $parametri_errati) {
					// *** 
					$parametri_errati=false;
					
					echo "<div class=\"error_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
					echo "\t\t\t\t\t<p>LE VALUTAZIONI ATTRIBUITE NON APPARTENGONO ALL'INTERVALLO INDICATO...</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
				}
				else {
					// ***
					if(isset($intervento_valutato) && $intervento_valutato) {
						// ***
						$intervento_valutato=false;
						
						echo "<div class=\"error_message\">\n";
						echo "\t\t\t<div class=\"container_message\">\n";
						echo "\t\t\t\t<div class=\"container_img\">\n";
						echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibilie...\" />\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t\t<div class=\"message\">\n";
						echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
						echo "\t\t\t\t\t<p>HAI GI&Agrave; VALUTATO IL CONTRIBUTO SELEZIONATO...</p>\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t</div>\n";
						echo "\t\t</div>\n";
					}
					else {
						// ***
						if(isset($errore_query) && $errore_query) {
							// ***
							$errore_query=false;
							
							echo "<div class=\"error_message\">\n";
							echo "\t\t\t<div class=\"container_message\">\n";
							echo "\t\t\t\t<div class=\"container_img\">\n";
							echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibilie...\" />\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t\t<div class=\"message\">\n";
							echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
							echo "\t\t\t\t\t<p>LA REPUTAZIONE DELL'UTENTE COINVOLTO NON PU&Ograve; PI&Ugrave; AUMENTARE O DIMINUIRE...</p>\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t</div>\n";
							echo "\t\t</div>\n";
						}
					}
				}
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
									<img src="../../Immagini/handshake-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Aggiungi una nuova valutazione!</h2>
							</div>
						</div>
						<form class="corpo_form" action="<?php echo $_SERVER['PHP_SELF']."?id_Offerta=".$_GET["id_Offerta"]."&amp;id_Discussione=".$_GET["id_Discussione"]."&amp;id_Intervento=".$_GET["id_Intervento"]; ?>" method="post">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Intervento (Informativo)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Pubblicato da
											</p>
											<p>
												<?php
													echo "<input type=\"text\" disabled=\"disabled\" value=\"".$username."\" />\n";
												?>
											</p>		
										</div>
										<div class="campo">
											<p>
												Titolo della Discussione
											</p>
											<p>
												<?php
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
													echo "<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$intervento->getElementsByTagName("testo")->item(0)->textContent."</textarea>\n";
												?>
											</p>		
										</div>
										<p class="nota"><strong>N.B.</strong> La precedente sezione si limita a riportare le informazioni principali inerenti all'intervento di interesse.</p>
									</div>
								</div>
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Valutazione (Obbligatorio)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Supporto (int. da 1 a 5)
											</p>
											<p style="margin-right: 0.55em; margin-left: -0.75em;">  
												<?php
													echo "<select name=\"supporto\" style=\"padding-left: 0.25em; padding-right: 0.25em;\">\n";
													
													// OGNI PARAMETRO DI VALUTAZIONE, OLTRE A POTER ASSUMERE UNO DEI VALORI INTERI COMPRESI TRA 1 E 5 (INCLUSI), SARÀ INIZIALIZZATO A 3, IN QUANTO QUEST'ULTIMO È STATO ETICHETTATO COME ELEMENTO INTERMEDIO DA CUI L'UTENTE POTRÀ ESORDIRE CON IL PROPRIO GIUDIZIO 
													// PER DI PIÙ, STANDO A QUANTO RIPORTATO IN PRECEDENZA, ABBIAMO DECISO DI TENERE TRACCIA, A SEGUITO DI EVENTUALI MANCANZE, DI TUTTE LE SCELTE COMPIUTE AL FINE DI RIPROPORLE DOPO IL RICARICAMENTO DELLA PAGINA
													for($i=1; $i<=5; $i++)
													{
														if(isset($_POST["supporto"])) {
															if($_POST["supporto"]==$i)
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
															else
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
														}
														else
														{
															if($i==3)
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
															else
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
														}
													}
													
													echo "\t\t\t\t\t\t\t\t\t\t\t\t</select>\n";
												?>
											</p>
										</div>
										<div class="campo">
											<p>
												Utilit&agrave; (int. da 1 a 5)
											</p>
											<p style="margin-right: 0.55em; margin-left: -0.75em;">  
												<?php
													echo "<select name=\"utilita\" style=\"padding-left: 0.25em; padding-right: 0.25em;\">\n";
													
													// ***
													for($i=1; $i<=5; $i++)
													{
														if(isset($_POST["utilita"])) {
															if($_POST["utilita"]==$i)
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
															else
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
														}
														else
														{
															if($i==3)
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option selected=\"selected\">".$i."</option>\n";
															else
																echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<option>".$i."</option>\n";
														}
													}
													
													echo "\t\t\t\t\t\t\t\t\t\t\t\t</select>\n";
												?>
											</p>
										</div>
										<p class="nota"><strong>N.B.</strong> Le voci proposte rappresentano l'insieme di tutti i possibili parametri, con annessi i punteggi, tramite cui &egrave; possibile valutare l'importanza dell'intervento in esame.</p>		
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
			// IN AGGIUNTA, SEGUENDO GLI STESSI RAGIONAMENTI APPLICATI PER L'INTESTAZIONE, È STATO RITENUTO UTILE RICHIAMARE IL PIÈ DI PAGINA ALL'INTERNO DI TUTTE QUELLE SCHERMATE IN CUI SE NE MANIFESTA IL BISOGNO
			require_once ("./footer_sito.php");
		?>
	</body>
</html>