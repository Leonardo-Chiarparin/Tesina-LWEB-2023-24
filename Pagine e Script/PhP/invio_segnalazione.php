<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL MODULO DA COMPILARE PER INSERIRE UNA NUOVA SEGNALAZIONE INERENTE AD UN CONTRIBUTO (RECENSIONE, DISCUSSIONE O INTERVENTO) RICEVUTO DA UN ALTRO CLIENTE ALL'INTERNO DELLA SCHERMATA DEDICATA ALLA PRESENTAZIONE DI UNA CERTA PROPOSTA DI VENDITA
	// N.B.: IN CASO DI ERRORE, LE INFORMAZIONI INSERITE, COSÌ COME LE SCELTE EFFETTUATE, VERRANNO PRESERVATE TRAMITE UNA SERIE DI CONTROLLI APPLICATI AL SOLO SCOPO DI AGEVOLARE L'OPERATO DEI VARI UTENTI D'INTERESSE
	
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
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO AI CLIENTI DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="C") {
		$id_Offerta=$_GET["id_Offerta"];
		
		header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
	}
	
	// LA PAGINA, ESSENDO RAGGIUNGIBILE DOPO AVER PREMUTO IL PULSANTE PER INOLTRARE UNA NUOVA SEGNALAZIONE, NECESSITA DI UN ULTERIORE CONTROLLO PER APPURARE SE È STATA EFFETTIVAMENTE PRESA UNA DECISIONE IN MERITO O MENO 
	if(!isset($_GET["id_Offerta"]))
		header("Location: index.php");
	
	// ***
	if(!(isset($_GET["id_Recensione"]) xor isset($_GET["id_Discussione"]))) {
	
		$id_Offerta=$_GET["id_Offerta"];
		
		header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
	}
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_piattaforme_videogiochi.php");
	require_once("./apertura_file_offerte.php");
	require_once("./apertura_file_segnalazioni.php");
	require_once("./apertura_file_recensioni.php");
	require_once("./apertura_file_discussioni.php");
	
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
	
	// DATA LA LORO SOMIGLIANZA, IL MECCANISMO APPLICATO IN PRECEDENZA PER LE OFFERTE POTRÀ ESSERE ESTESO ANCHE PER I SINGOLI ELEMENTI DA SEGNALARE
	if(isset($_GET["id_Recensione"])) {
		$recensione_individuata=false;
		
		for($i=0; $i<$recensioni->length; $i++) {
			$recensione=$recensioni->item($i);
			
			// ***
			if($recensione->getAttribute("id")==$_GET["id_Recensione"]) {
				$recensione_individuata=true;
				break;
			}
		}
		
		// NEL CASO IN CUI IL CONTRIBUTO INDICATO NON SIA PRESENTE ALL'INTERNO DEL RELATIVO FILE XML O SE È GIÀ STATO MODERATO, SI PROVVEDERÀ A REINDIRIZZARE L'UTENTE VERSO LA SCHERMATA DI RIEPILOGO DELLA PROPOSTA DI VENDITA
		if($recensione_individuata==false || $recensione->getAttribute("moderata")=="Si") {
			$id_Offerta=$_GET["id_Offerta"];
		
			header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
		}
	}
	else {
		// ***
		if(isset($_GET["id_Discussione"])) {
			$discussione_individuata=false;
			
			for($i=0; $i<$discussioni->length; $i++) {
				$discussione=$discussioni->item($i);
				
				// ***
				if($discussione->getAttribute("id")==$_GET["id_Discussione"]) {
					$discussione_individuata=true;
					break;
				}
			}
			
			// ***
			if($discussione_individuata==false || $discussione->getAttribute("moderata")=="Si") {
				$id_Offerta=$_GET["id_Offerta"];
			
				header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
			}
			
			// ***
			if(isset($_GET["id_Intervento"])) {
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
			}
		}
	}
	
	// PER LE RAGIONI ILLUSTRATE PIÙ IN BASSO, SI HA LA NECESSITÀ DI INDIVIDUARE E MOSTRARE IL PRODOTTO E LA PIATTAFORMA SU CUI PUÒ ESSERE MANDATO IN ESECUZIONE L'EVENTUALE VIDEOGIOCO 
	for($i=0; $i<$prodotti->length; $i++) {
		$prodotto=$prodotti->item($i);
		
		if($prodotto->getAttribute("id")==$offerta->getAttribute("idProdotto"))
			break;
	}
	
	if($prodotto->getElementsByTagName("videogioco")->length!=0) {
		for($i=0; $i<$piattaforme->length; $i++) {
			$piattaforma=$piattaforme->item($i);
			
			if($piattaforma->getAttribute("id")==$prodotto->getElementsByTagName("piattaforma")->item(0)->getAttribute("idPiattaforma"))
				break;
		}
	}
	
	// INOLTRE, BISOGNERÀ FARE IN MODO CHE L'ELEMENTO DI INTERESSE SIA EFFETTIVAMENTE RICONDUCIBILE AL PRODOTTO A CUI SI RIFERISCE L'OFFERTA SELEZIONATA
	$contributo_esistente=false;
	
	if(isset($_GET["id_Recensione"])) {
		for($i=0; $i<$prodotto->getElementsByTagName("recensione")->length && !$contributo_esistente; $i++) {
			if($prodotto->getElementsByTagName("recensione")->item($i)->getAttribute("idRecensione")==$recensione->getAttribute("id"))
				$contributo_esistente=true;
		}
	}
	else {
		if(isset($_GET["id_Discussione"])) {
			for($i=0; $i<$prodotto->getElementsByTagName("discussione")->length && !$contributo_esistente; $i++) {
				if($prodotto->getElementsByTagName("discussione")->item($i)->getAttribute("idDiscussione")==$discussione->getAttribute("id"))
					$contributo_esistente=true;
			}
		}
	}
	
	if(!$contributo_esistente) {
		$id_Offerta=$_GET["id_Offerta"];
		
		header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
	}
	
	// TRA I DETTAGLI CHE MEGLIO SI PRESTANO A RAPPRESENTARE UN DETERMINATO CONTRIBUTO, VI È SICURAMENTE L'USERNAME DELL'UTENTE (CLIENTE) CHE LO HA PUBBLICATO
	if(isset($_GET["id_Recensione"])) {
		$sql="SELECT Username, Tipo_Utente FROM $tab WHERE ID=".$recensione->getAttribute("idUtente");
		$result=mysqli_query($conn, $sql);
		
		while($row=mysqli_fetch_array($result)) {
			$username=$row["Username"];
			$tipo_utente=$row["Tipo_Utente"];
		}
		
		// SE L'UTENTE CHE SI INTENDE SEGNALARE RISULTA ESSERE UN GESTORE O UN AMMINISTRATORE, BISOGNERÀ IMPEDIRE IL BUON ESITO DELL'OPERAZIONE
		if($tipo_utente!="C") {
			$id_Offerta=$_GET["id_Offerta"];
			
			header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
		}
	}
	else {
		// ***
		if(isset($_GET["id_Discussione"])) {
			$sql="SELECT Username, Tipo_Utente FROM $tab WHERE ID=".$discussione->getAttribute("idAutore");
			$result=mysqli_query($conn, $sql);
			
			while($row=mysqli_fetch_array($result)) {
				$username=$row["Username"];
				$tipo_utente=$row["Tipo_Utente"];
			}
			
			// ***
			if($tipo_utente!="C") {
				$id_Offerta=$_GET["id_Offerta"];
				
				header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
			}
			
			// ***
			if(isset($_GET["id_Intervento"])) {
				$sql="SELECT Username, Tipo_Utente FROM $tab WHERE ID=".$intervento->getAttribute("idPartecipante");
				$result=mysqli_query($conn, $sql);
				
				while($row=mysqli_fetch_array($result)) {
					$username=$row["Username"];
					$tipo_utente=$row["Tipo_Utente"];
				}
				
				// ***
				if($tipo_utente!="C") {
					$id_Offerta=$_GET["id_Offerta"];
					
					header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
				}
			}
		}
	}
	
	// A SEGUITO DELL'INDIVIDUAZIONE DI UN DETERMINATO ERRORE, LA PAGINA VERRÀ RICARICATA MOSTRANDO IL RELATIVO MESSAGGIO DI POPUP. PROPRIO PER QUESTO, LE INFORMAZIONI PRECEDENTEMENTE CONTENUTE ALL'INTERNO DEL VETTORE RELATIVO AI PARAMETRI PASSATI TRAMITE METODO GET VERREBBERO PERDUTI
	// IN QUEST'OTTICA, ABBIAMO DECISO DI AGGIUNGERE UNA VARIABILE CHE, ALL'OCCORRENZA, MANTERRÀ GLI IDENTIFICATORI DEI CAMPI SELEZIONATI AL SOLO SCOPO DI RIPRISTINARNE, PREVIA COSTANTE ASSEGNAMENTO, IL VALORE ALL'INTERNO DELL'ARRAY DI CUI SOPRA 
	// PER DI PIÙ, UN SIMILE RAGIONAMENTO VERRÀ ADOTTATO IN TUTTE QUELLE PAGINE CHE POSSONO ESSERE RAGGIUNTE SOLTANTO DOPO AVER SCELTO UNA DETERMINATA VOCE DALLA CORRISPONDENTE PAGINA DI RIEPILOGO E CHE PRESENTANO UN MODULO CHE PREVEDE L'UTILIZZO DEL METODO POST
	if(isset($_GET["id_Recensione"]))
		$parametri="id_Offerta=".$_GET["id_Offerta"]."&amp;id_Recensione=".$_GET["id_Recensione"];
	else {
		if(isset($_GET["id_Discussione"])) {
			if(!isset($_GET["id_Intervento"]))
				$parametri="id_Offerta=".$_GET["id_Offerta"]."&amp;id_Discussione=".$_GET["id_Discussione"];
			else
				$parametri="id_Offerta=".$_GET["id_Offerta"]."&amp;id_Discussione=".$_GET["id_Discussione"]."&amp;id_Intervento=".$_GET["id_Intervento"];
		}
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
		$_POST["testo"]=trim($_POST["testo"]);
		$_POST["testo"]=rtrim($_POST["testo"]);
		
		// PER QUESTIONI DI FORMATTAZIONE DEL DOCUMENTO XML, È STATO NECESSARIO DISPORRE TUTTE LE COMPONENTI DELLA RECENSIONE DELL'ARTICOLO ALL'INTERNO DI UN'UNICA RIGA. A TALE SCOPO, ABBIAMO USUFRUITO DEL METODO explode(...) SPECIFICANDO "\n" COME PARAMETRO DELIMITATORE PER EFFETTUARE LA SEPARAZIONE DELLA STRINGA
		$testo=explode("\n", $_POST["testo"]);
		$_POST["testo"]="";
		
		foreach($testo as $riga) {
			$_POST["testo"]=$_POST["testo"].$riga;
		}
		
		// AL FINE DI GARANTIRE UN DISCRETO LIVELLO DI SICUREZZA, PER I CAMPI PRIVI DI CONTROLLI INERENTI AD ESPRESSIONI REGOLARI (ADEGUATE PER IL CONTESTO) VERRANNO APPLICATI DEI CONTROLLI INERENTI AI CARATTERI AL LORO INTERNO 
		$_POST["testo"]=stripslashes($_POST["testo"]); // UN ESEMPIO, POTREBBE ESSERE LA RIMOZIONE DEI BACKSLASH \ PER EVITARE LA MySQL Injection
		
		// GIUNTI A QUESTO PUNTO, E SECONDO L'ORDINAMENTO INDOTTO DALLE PRECEDENTI OPERAZIONI, È NECESSARIO EFFETTUARE UN ULTERIORE CONTROLLO PER VALUTARE SE SI È EFFETTIVAMENTE INSERITO QUALCOSA ALL'INTERNO DEI VARI CAMPI 
		if(strlen($_POST["testo"])==0 || !(isset($_POST["categoria"]))) {
			// IN CASO DI ERRORE, SI DOVRÀ INFORMARE L'UTENTE COINVOLTO DELLE MANCANZE CHE HA AVUTO NEI CONFRONTI DEL MODULO PER L'INSERIMENTO DELLA PROPOSTA DI VENDITA. UN SIMILE MECCANISMO VERRÀ IMPLEMENTATO PER TUTTE LE CASISITCHE IN CUI SARÀ IMPOSSIBILE PROCEDERE  
			$campi_vuoti=true;
		}
		else {
			if($_POST["categoria"]=="Tematiche Inappropriate" || $_POST["categoria"]=="Contenuti Fraudolenti" || $_POST["categoria"]=="Linguaggio Volgare") {
				
				// PRIMA DI PROCEDERE CON L'INSERIMENTO DELLA NUOVA SEGNALAZIONE, BISOGNA EFFETTUARE DEI CONTROLLI PER VALUTARE SE UN DETERMINATO ELEMENTO ECCEDE LA DIMENSIONE MASSIMA INDICATA
				if(strlen($_POST["testo"])>1989) {
					// ***
					$superamento_testo=true;
				}
				else {
					// SE LE VERIFICHE DI CUI SOPRA NON HANNO INDIVIDUATO ALCUNA SORTA DI PROBLEMATICA, ALLORA È POSSIBILE PROCEDERE CON L'EFFETTIVO INSERIMENTO DELLA SEGNALAZIONE
					// LA RAPPRESENTAZIONE DI UNA SEGNALAZIONE È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUEST'ULTIMA. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI INDICATI DALL'UTENTE
					$nuova_segnalazione=$docSegnalazioni->createElement("segnalazione");
					
					// CONTESTUALMENTE ALLA DEFINIZIONE DI UN INDICE PER L'ELEMENTO SUDDETTO, È PREVISTO L'ADEGUAMENTO DELL'ATTRIBUTO RELATIVO ALLA RADICE DEL DOCUMENTO E INERENTE AL NUMERO DI RECENSIONI INSERITE FINORA  
					$rootSegnalazioni->setAttribute("ultimoId", $rootSegnalazioni->getAttribute("ultimoId")+1);
					$nuova_segnalazione->setAttribute("id", $rootSegnalazioni->getAttribute("ultimoId"));
					
					if(isset($_GET["id_Recensione"]))
						$nuova_segnalazione->setAttribute("idSegnalato", $recensione->getAttribute("idUtente"));
					else {
						if(isset($_GET["id_Discussione"])) {
							if(!isset($_GET["id_Intervento"]))
								$nuova_segnalazione->setAttribute("idSegnalato", $discussione->getAttribute("idAutore"));
							else
								$nuova_segnalazione->setAttribute("idSegnalato", $intervento->getAttribute("idPartecipante"));
						}
					}
					
					$nuova_segnalazione->setAttribute("idSegnalatore", $_SESSION["id_Utente"]);
					$nuova_segnalazione->setAttribute("dataOraSegnalazione", date("Y-m-d H:i:s"));
					$nuova_segnalazione->setAttribute("categoria", $_POST["categoria"]);
					$nuova_segnalazione->setAttribute("seen", "No");
					
					$testo=$docSegnalazioni->createElement("testo", $_POST["testo"]);
					
					$nuova_segnalazione->appendChild($testo);
					
					$tipoSegnalazione=$docSegnalazioni->createElement("tipoSegnalazione");
					
					// LA COMPOSIZIONE DELL'ENTITÀ DI INTERESSE VARIERÀ A SECONDA DEL TIPO DI ELEMENTO CHE SI INTENDE SEGNALARE
					if(isset($_GET["id_Recensione"])) {
						$perRecensione=$docSegnalazioni->createElement("perRecensione");
						$perRecensione->setAttribute("idRecensione", $recensione->getAttribute("id"));
						
						$tipoSegnalazione->appendChild($perRecensione);
					}
					else {
						if(isset($_GET["id_Discussione"])) {
							if(!isset($_GET["id_Intervento"])) {
								$perDiscussione=$docSegnalazioni->createElement("perDiscussione");
								$perDiscussione->setAttribute("idDiscussione", $discussione->getAttribute("id"));
								
								$tipoSegnalazione->appendChild($perDiscussione);
							}
							else {
								$perIntervento=$docSegnalazioni->createElement("perIntervento");
								$perIntervento->setAttribute("idDiscussione", $discussione->getAttribute("id"));
								$perIntervento->setAttribute("idIntervento", $intervento->getAttribute("id"));
								
								$tipoSegnalazione->appendChild($perIntervento);
							}
						}
					}
					
					$nuova_segnalazione->appendChild($tipoSegnalazione);
					
					$rootSegnalazioni->appendChild($nuova_segnalazione);
					
					// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
					if($docSegnalazioni->schemaValidate("../../XML/Schema/Segnalazioni.xsd")){
						
						// AL FINE DI GARANTIRE UNA STAMPA GODIBILE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
						$docSegnalazioni->preserveWhiteSpace = false;
						$docSegnalazioni->formatOutput = true;
						$docSegnalazioni->save("../../XML/Segnalazioni.xml");
						
						// PRIMA DI ESSERE REINDIRIZZATI, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
						$_SESSION["modifica_Effettuata"]=true;
						
						$id_Offerta=$_GET["id_Offerta"];
						
						header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
					}
					else {
						
						// ***
						setcookie("errore_Validazione", true);
						
						$id_Offerta=$_GET["id_Offerta"];
						
						header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
					}
				}
			}
			else {
				// ***
				$categoria_errata=true;
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
				if(isset($categoria_errata) && $categoria_errata) {
					// *** 
					$categoria_errata=false;
					
					echo "<div class=\"error_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
					echo "\t\t\t\t\t<p>LA CATEGORIA INDICATA NON &Egrave; AMMESSA...</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
				}
				else {
					// ***
					if(isset($superamento_testo) && $superamento_testo) {
						// *** 
						$superamento_testo=false;
						
						echo "<div class=\"error_message\">\n";
						echo "\t\t\t<div class=\"container_message\">\n";
						echo "\t\t\t\t<div class=\"container_img\">\n";
						echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t\t<div class=\"message\">\n";
						echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
						echo "\t\t\t\t\t<p>LA DIMENSIONE DEI DETTAGLI ECCEDE IL NUMERO MASSIMO DI CARATTERI CONSENTITI...</p>\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t</div>\n";
						echo "\t\t</div>\n";
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
									<img src="../../Immagini/gavel-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Aggiungi una nuova segnalazione...</h2>
							</div>
						</div>
						<form class="corpo_form" action="<?php echo $_SERVER['PHP_SELF']."?".$parametri; ?>" method="post">
							<div class="container_corpo_form">
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											<?php
												// POICHÈ SI VUOLE RENDERE LA PAGINA SPENDIBILE PER OGNUNO DEGLI ELEMENTI CHE SI PUÒ SEGNALARE, È STATO RITENUTO OPPORTUNO EFFETTUARE UNA SERIE DI CONTROLLI PER INDIVIDUARE A QUALE DELLE TRE ENTITÀ (RECENSIONI, DOMANDE E RISPOSTE) SI STA FACENDO RIFERIMENTO
												if(isset($_GET["id_Recensione"])) 
													echo "Recensione (Informativo)\n";
												else {
													if(isset($_GET["id_Discussione"])) {
														if(!isset($_GET["id_Intervento"]))
															echo "Discussione (Informativo)\n";
														else
															echo "Intervento (Informativo)\n";
													}
												}
											?>
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p>
												Articolo
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
													if(isset($_GET["id_Recensione"])) 
														echo "Condivisa da";
													else {
														if(isset($_GET["id_Discussione"])) {
															if(!isset($_GET["id_Intervento"]))
																echo "Avviata da";
															else
																echo "Pubblicato da";
														}
													}
												?>
											</p>
											<p>
												<?php
													echo "<input type=\"text\" disabled=\"disabled\" value=\"".$username."\" />\n";
												?>
											</p>		
										</div>
										<div class="campo">
											<p>
												Titolo <?php if(isset($_GET["id_Intervento"])) echo "della Discussione"; ?>
											</p>
											<p>
												<?php
													// ***
													if(isset($_GET["id_Recensione"])) 
														echo "<input type=\"text\" disabled=\"disabled\" value=\"".$recensione->getElementsByTagName("titolo")->item(0)->textContent."\" />\n";
													else {
														if(isset($_GET["id_Discussione"])) {
															echo "<input type=\"text\" disabled=\"disabled\" value=\"".$discussione->getElementsByTagName("titolo")->item(0)->textContent."\" />\n";
														}
													}
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
													if(isset($_GET["id_Recensione"])) 
														echo "<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$recensione->getElementsByTagName("testo")->item(0)->textContent."</textarea>\n";
													else {
														if(isset($_GET["id_Discussione"])) {
															if(!isset($_GET["id_Intervento"]))
																echo "<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$discussione->getElementsByTagName("descrizione")->item(0)->textContent."</textarea>\n";
															else
																echo "<textarea rows=\"0\" cols=\"0\" disabled=\"disabled\">".$intervento->getElementsByTagName("testo")->item(0)->textContent."</textarea>\n";
														}
													}
												?>
											</p>		
										</div>
										<?php
											// ***
											if(isset($_GET["id_Recensione"])) 
												echo "<p class=\"nota\"><strong>N.B.</strong> La precedente sezione si limita a riportare le informazioni principali inerenti alla recensione di interesse.</p>\n";
											else {
												if(isset($_GET["id_Discussione"])) {
													if(!isset($_GET["id_Intervento"]))
														echo "<p class=\"nota\"><strong>N.B.</strong> La precedente sezione si limita a riportare le informazioni principali inerenti alla discussione di interesse.</p>\n";
													else
														echo "<p class=\"nota\"><strong>N.B.</strong> La precedente sezione si limita a riportare le informazioni principali inerenti all'intervento di interesse.</p>\n";
												}
											}
										?>
									</div>
								</div>
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Segnalazione (Obbligatorio)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo">
											<p style="display: flex; align-items: center; width: 100%;">
												<?php
													if(isset($_POST["categoria"]) && $_POST["categoria"]=="Tematiche Inappropriate")
														echo "<input type=\"radio\" name=\"categoria\" checked=\"checked\" value=\"Tematiche Inappropriate\" />\n";
													else
														echo "<input type=\"radio\" name=\"categoria\" value=\"Tematiche Inappropriate\" />\n";
												?>
												Tematiche Inappropriate
											</p>
										</div>
										<div class="campo">
											<p style="display: flex; align-items: center; width: 100%;">
												<?php
													if(isset($_POST["categoria"]) && $_POST["categoria"]=="Contenuti Fraudolenti")
														echo "<input type=\"radio\" name=\"categoria\" checked=\"checked\" value=\"Contenuti Fraudolenti\" />\n";
													else
														echo "<input type=\"radio\" name=\"categoria\" value=\"Contenuti Fraudolenti\" />\n";
												?>
												Contenuti Fraudolenti
											</p>
										</div>
										<div class="campo">
											<p style="display: flex; align-items: center; width: 100%;">
												<?php
													if(isset($_POST["categoria"]) && $_POST["categoria"]=="Linguaggio Volgare")
														echo "<input type=\"radio\" name=\"categoria\" checked=\"checked\" value=\"Linguaggio Volgare\" />\n";
													else
														echo "<input type=\"radio\" name=\"categoria\" value=\"Linguaggio Volgare\" />\n";
												?>
												Linguaggio Volgare
											</p>
										</div>
										<p class="nota"><strong>N.B.</strong> Le voci proposte rappresentano l'insieme di tutte le possibili violazioni tra cui &egrave; possibile scegliere per poter catalogare correttamente il commento in esame.</p>		
									</div>
								</div>
								<div class="intestazione_sezione"> 
									<div class="container_intestazione_sezione">
										<span>
											Approfondimenti (Obbligatorio)
										</span>
									</div>
								</div>
								<div class="elenco_campi">
									<div class="container_elenco_campi">
										<div class="campo_descrizione">
											<p>
												Motivazioni (max. 1989 caratteri)
											</p>
											<p>
												<textarea name="testo" rows="0" cols="0"><?php if(isset($_POST['testo'])) echo $_POST['testo']; else echo '';?></textarea>
											</p>	
										</div>
										<p class="nota"><strong>N.B.</strong> L'ultimo campo permette al cliente di fornire ulteriori spiegazioni per comprendere meglio le ragioni della segnalazione. In ogni caso, si prega di essere concisi e non offensivi.</p>		
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