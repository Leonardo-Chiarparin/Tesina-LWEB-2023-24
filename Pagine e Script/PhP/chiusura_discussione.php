<?php
	// LO SCRIPT PERMETTE DI CHIUDERE UNA DISCUSSIONE AVVIATA ALL'INTERNO DELLA SCHEDA RELATIVA AD UNA CERTA PROPOSTA DI VENDITA
	
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
		$id_Offerta=$_GET["id_Offerta"];
		
		header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
	}
	
	// LA PAGINA, ESSENDO RAGGIUNGIBILE DOPO AVER PREMUTO IL PULSANTE PER MODERARE UN CONTRIBUTO COME QUELLO CITATO IN PRECEDENZA, NECESSITA DI UN ULTERIORE CONTROLLO PER APPURARE SE È STATA EFFETTIVAMENTE PRESA UNA DECISIONE IN MERITO O MENO 
	if(!isset($_GET["id_Offerta"]))
		header("Location: index.php");
	
	// ***
	if(!(isset($_GET["id_Discussione"]))) {
		$id_Offerta=$_GET["id_Offerta"];
		
		header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
	}
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_offerte.php");
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
	
	// PER I CONTROLLI CHE VERRANNO APPLICATI PIÙ IN BASSO, SI HA LA NECESSITÀ DI INDIVIDUARE IL PRODOTTO A CUI SI RIFERISCE LA PROPOSTA DI VENDITA SELEZIONATA  
	$prodotto_individuato=false;
	
	for($i=0; $i<$prodotti->length; $i++) {
		$prodotto=$prodotti->item($i);
		
		// ***
		if($prodotto->getAttribute("id")==$_GET["id_Offerta"]) {
			$prodotto_individuato=true;
			break;
		}
	}
	
	if($prodotto_individuato==false) {
		header("Location: index.php");
	}
	
	// DATA LA LORO SOMIGLIANZA, IL MECCANISMO APPLICATO IN PRECEDENZA PER LE OFFERTE POTRÀ ESSERE ESTESO ANCHE PER I RESTANTI ELEMENTI DI INTERESSE
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
		
		// NEL CASO IN CUI IL CONTRIBUTO INDICATO NON SIA PRESENTE ALL'INTERNO DEL RELATIVO FILE XML O SE È GIÀ STATO MODERATO O RISOLTO, SI PROVVEDERÀ A REINDIRIZZARE L'UTENTE VERSO LA SCHERMATA DI RIEPILOGO DELLA PROPOSTA DI VENDITA
		if($discussione_individuata==false || $discussione->getAttribute("moderata")=="Si" || $discussione->getAttribute("risolta")=="Si") {
			$id_Offerta=$_GET["id_Offerta"];
		
			header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
		}
	}
	
	// INOLTRE, BISOGNERÀ FARE IN MODO CHE L'ELEMENTO DI INTERESSE SIA EFFETTIVAMENTE RICONDUCIBILE AL PRODOTTO A CUI SI RIFERISCE L'OFFERTA SELEZIONATA
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
	
	// PRIMA DI PROCEDERE CON LE VARIE OPERAZIONI DI INTERESSE, SARÀ NECESSARIO IMPEDIRE LA CHIUSURA DI TUTTE QUELLE DISCUSSIONI CHE NON SONO STATE AVVIATE DAL SOGGETTO CHE HA PREMUTO SUL RELATIVO PULSANTE
	if(isset($_GET["id_Discussione"])) {
		
		if($discussione->getAttribute("idAutore")!=$_SESSION["id_Utente"]) {
			$id_Offerta=$_GET["id_Offerta"];
			
			header("Location: riepilogo_scheda_offerta.php?id_Offerta=$id_Offerta");
		}
		
	}
	
	// SE LE VERIFICHE DI CUI SOPRA NON HANNO INDIVIDUATO ALCUNA SORTA DI PROBLEMATICA, ALLORA È POSSIBILE PROCEDERE CON LA MODIFICA DELL'ENTITÀ COINVOLTA
	if(isset($_GET["id_Discussione"])) {
		$discussione->setAttribute("risolta", "Si");
			
		// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA STRUTTURA DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NEL RELATIVO SCHEMA
		if($docDiscussioni->schemaValidate("../../XML/Schema/Discussioni.xsd")){
			
			// AL FINE DI GARANTIRE UNA STAMPA GODIBILE, SONO STATI DEFINITI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
			$docDiscussioni->preserveWhiteSpace = false;
			$docDiscussioni->formatOutput = true;
			$docDiscussioni->save("../../XML/Discussioni.xml");
			
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
?>