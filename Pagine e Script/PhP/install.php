<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LO SCRIPT DI INSTALLAZIONE RAPPRESENTA UN ELEMENTO FONDAMENTALE PER FORNIRE UNA CORRETTA INTERAZIONE TRA I SOGGETTI COINVOLTI E LE VARIE STRUTTURE DATI (BASE DI DATI E/O FILE XML)
	// PER ALCUNE DELLE ENTITÀ DA RAPPRESENTARE, IL MECCANISMO CHE SI ANDRÀ AD ADOTTARE CONSISTE NELLA CREAZIONE DI UNA TABELLA RELAZIONALE, LA QUALE, OLTRE AD IMPORRE DELLE CONDIZIONI INIZIALI PER L'UNICITÀ DEI CAMPI CHE LA COMPONGONO, SARÀ EVENTUALMENTE SOGGETTA AD UNA COPIA DEL PROPRIO CONTENUTO AL FINE DI TRASFERIRE LE VARIE INFORMAZIONI LADDOVE RISULTI NECESSARIO
	// IL PRIMO PASSO PREVEDE L'INSTAURAZIONE DI UNA CONNESSIONE DIRETTAMENTE CON IL DBMS, IL CUI ESITO SARÀ IN GRADO DI STABILIRE SE È POSSIBILE CONTINUARE CON L'OPERAZIONE O MENO
	require_once("./variabili_connessioni.php");
	
	$conn = new mysqli($host,$user,$pass);
	if(mysqli_connect_errno()){
		printf("ERRORE DI CONNESSIONE CON IL DBMS: %s\n", mysqli_connect_error());
		exit();
	}
	
	// LA COSTRUZIONE E IL POPOLAMENTO DELLE VARIE TABELLE, COSÌ COME QUELLI DELLA BASE DI DATI, DEVE POTER ESSERE APPLICABILE PER OGNI CONTESTO E IN QUALUNQUE MOMENTO
	// PROPRIO PER QUESTO, ABBIAMO DECISO DI INCLUDERE UN COMANDO SQL DI DATA DEFINITION LANGUAGE PER ELIMINARE TUTTE LE VERSIONI EVENTUALMENTE INSTALLATE IN PRECEDENZA
	mysqli_query($conn,"DROP DATABASE IF EXISTS $db");
	
	// BASE DI DATI
	mysqli_query($conn,"CREATE DATABASE $db");
	mysqli_query($conn,"USE $db");
	
	// UTENTI. PER GARANTIRE UNA GESTIONE  	
	mysqli_query($conn, "DROP TABLE IF EXISTS $tab");
	
	// PER QUESTIONI DI SICUREZZA, LA PASSWORD, AL CONTRARIO DELL'INDIRIZZO DI POSTA ELETTRONICA E DELLO USERNAME, DOVRÀ ESSERE RESA SENSIBILE A VARIAZIONI LEGATE ALLA PRESENZA DI CARATTERI MAIUSCOLI E/O MINUSCOLI  
	$sql="CREATE TABLE $tab (ID int NOT NULL AUTO_INCREMENT, Nome varchar(30) NOT NULL, Cognome varchar(35) NOT NULL, Num_Telefono char(10) NOT NULL, Email varchar(35) NOT NULL, Username varchar(30) NOT NULL, Password varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL, Indirizzo varchar(60) NOT NULL, Citta varchar(40) NOT NULL, CAP char(5) NOT NULL, Tipo_Utente char(1) NOT NULL, Data_Registrazione date NOT NULL, Portafoglio_Crediti decimal (10,2) NOT NULL DEFAULT '0.00', Reputazione int NOT NULL DEFAULT '33', Ban char(1) NOT NULL DEFAULT 'N', PRIMARY KEY (ID), UNIQUE (Email), UNIQUE (Username))";
	mysqli_query($conn,$sql);
	
	$sql="INSERT INTO $tab VALUES (1,'Matteo','Rosso','3801932038','admin@lev.it','admin','Adm1n','Via Ezio, 3','Latina','04100','A','2021-02-01',0.00,100,'N'),(2,'Ettore','Cantile','3451772123','cantile@lev.it','Ecantile','Ettore2002','Via dei Girasoli, 3','Latina Scalo','04013','G','2021-02-02',0.00,100,'N'),(3,'Leonardo','Chiarparin','3337279141','chiarparin@lev.it','Lchiarparin','Leonardo2002','Via Migliara 54, 2956','Sabaudia','04016','G','2021-02-02',0.00,100,'N'),(4,'Alessia','Milani','3497654234','alessia1@gmail.com','AleMilani','Alessia93','Via del Salice, 82','Latina','04100','C','2022-05-04',47.55,33,'N'),(5,'Matteo','Romani','3279864356','mromani@alice.it','MatRomani','Matteo64','Str. Acque Alte, 42','Latina','04100','C','2024-05-05',0.00,12,'N'),(6,'Marianna','Rossi','3456298543','mrossi@gmail.com','MarRossi','Marianna98','Via Don Carlo Torello, 112','Latina','04100','C','2023-05-06',50.50,66,'N')";
	mysqli_query($conn,$sql);
	
	// CATEGORIE DEI LIBRI (DB)
	mysqli_query($conn, "DROP TABLE IF EXISTS Categorie_Libri");
	$sql="CREATE TABLE Categorie_Libri (ID int NOT NULL AUTO_INCREMENT, Denominazione varchar(100) NOT NULL, Immagine varchar(150) NOT NULL, PRIMARY KEY (ID), UNIQUE (Denominazione) )";
	mysqli_query($conn,$sql);
	
	$sql="INSERT INTO Categorie_Libri VALUES (1,'Biografia','../../Immagini/feather-solid.svg'),(2,'Manga','../../Immagini/torii-gate-solid.svg'),(3,'Romance','../../Immagini/heart-pulse-solid.svg'),(4,'Commedia','../../Immagini/face-grin-squint-tears-solid.svg'),(5,'Sport','../../Immagini/medal-solid.svg'),(6,'Horror','../../Immagini/ghost-solid.svg'),(7,'Fantasy','../../Immagini/hat-wizard-solid.svg'),(8,'Avventura','../../Immagini/rocket-solid.svg')";
	mysqli_query($conn,$sql);
	
	// PIATTAFORME DEI VIDEOGIOCHI (DB)
	mysqli_query($conn, "DROP TABLE IF EXISTS Piattaforme_Videogiochi");
	$sql="CREATE TABLE Piattaforme_Videogiochi (ID int NOT NULL AUTO_INCREMENT, Denominazione varchar(100) NOT NULL, Immagine varchar(150) NOT NULL, PRIMARY KEY (ID), UNIQUE (Denominazione) )";
	mysqli_query($conn,$sql);
	
	$sql="INSERT INTO Piattaforme_Videogiochi VALUES (1,'PlayStation 4','../../Immagini/playstation-brands-solid.svg'),(2,'PlayStation 5','../../Immagini/playstation-brands-solid.svg'),(3,'Xbox Series X e S','../../Immagini/xbox-brands-solid.svg'),(4,'Nintendo Switch','../../Immagini/gamepad-solid.svg')";
	mysqli_query($conn,$sql);
	
	// GENERI DEI VIDEOGIOCHI (DB)
	mysqli_query($conn, "DROP TABLE IF EXISTS Generi_Videogiochi");
	$sql="CREATE TABLE Generi_Videogiochi (ID int NOT NULL AUTO_INCREMENT, Denominazione varchar(100) NOT NULL, PRIMARY KEY (ID), UNIQUE (Denominazione) )";
	mysqli_query($conn,$sql);
	
	$sql="INSERT INTO Generi_Videogiochi VALUES (1,'Azione'),(2,'Avventura'),(3,'Arcade'),(4,'Sportivo'),(5,'Picchiaduro'),(6,'Sparatutto'),(7,'Enigmi e Puzzle'),(8,'Simulazione'),(9,'Horror'),(10,'Di Ruolo'),(11,'Educativo') ";
	mysqli_query($conn,$sql);
	
	// RICHIESTE PER LA RICARICA DEL SALDO (DB)
	mysqli_query($conn, "DROP TABLE IF EXISTS Richieste_Crediti");
	$sql="CREATE TABLE Richieste_Crediti (ID int NOT NULL AUTO_INCREMENT, ID_Richiedente int NOT NULL, Data_Ora_Richiesta datetime NOT NULL, Numero_Crediti decimal (10,2) NOT NULL, Stato varchar(10) NOT NULL, PRIMARY KEY (ID), CONSTRAINT Effettuata_Da FOREIGN KEY (ID_Richiedente) REFERENCES $tab(ID))";
	mysqli_query($conn,$sql);
	
	$sql="INSERT INTO Richieste_Crediti VALUES (1,4,'2024-05-27 09:00:00', 52.45, 'Accettata'),(2,6,'2024-05-31 10:00:00', 84.50, 'In Corso')";
	mysqli_query($conn,$sql);
	
	// DOMANDE DI ASSISTENZA PER IL RECUPERO DELLA PASSWORD (DB)
	mysqli_query($conn, "DROP TABLE IF EXISTS Domande_Assistenza");
	$sql="CREATE TABLE Domande_Assistenza (ID int NOT NULL AUTO_INCREMENT, ID_Richiedente int NOT NULL, Data_Ora_Richiesta datetime NOT NULL, Seen char(2) NOT NULL, PRIMARY KEY (ID), CONSTRAINT Inoltrata_Da FOREIGN KEY (ID_Richiedente) REFERENCES $tab(ID))";
	mysqli_query($conn,$sql);
	
	$sql="INSERT INTO Domande_Assistenza VALUES (1,5,'2024-05-30 13:00:00', 'No')";
	mysqli_query($conn,$sql);
	
	// UNA VOLTA DELINEATE TUTTE LE STRUTTURE CHE, A NOSTRO PARERE, NECESSITANO DI UN'ENTITÀ IN GRADO DI DESCRIVERLE PRELIMINARMENTE ALL'INTERNO DELLA BASE DI DATI, È POSSIBILE PROCEDERE CON LA CARATTERIZZAZIONE DEI VARI FILE XML, LA QUALE SARÀ OVVIAMENTE REALIZZATA IN FUNZIONE DEI DETTAGLI CONTENUTI NELLE TABELLE E NELLE GRAMMATICHE DTD ($dom->appendChild($implementation->createDocumentType("...","","Dtd/....dtd"))) O SCHEMA DI RIFERIMENTO ($root->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance"); $root->setAttribute("xsi:noNamespaceSchemaLocation", "Schema/...xsd")) PER QUEST'ULTIMI
	// PER DI PIÙ, È STATO RITENUTO OPPORTUNO DISPORRE GERARCHICAMENTE I VARI CONTROLLI COSÌ DA POTER GESTIRE AL MEGLIO TUTTE LE POSSIBILI CASISTICHE D'INTERESSE 
	// N.B.: CIASCUNO DEI VARI DOCUMENTI SARÀ CREATO A PARTIRE DALLA VERSIONE DI XML, DALL'ENCODING E DAL FORMATO CHE ABBIAMO DECISO DI UTILIZZARE E DI FAR RISPETTARE. INOLTRE, VERRANNO APPLICATI DEI METODI, QUALI preserveWhiteSpace O formatOutput, PER FAR SÌ CHE IL RISULTATO PRODOTTO POSSA ESSERE PRESENTATO IN MANIERA ADEGUATA E PER EFFETTUARE IL SALVATAGGIO ($dom->save(...)) E LA VALIDAZIONE ($dom->validate() O $dom->schemaValidate("../../XML/Schema/....xsd")) DI QUEST'ULTIMO. IN PRESENZA DI VIOLAZIONI, SI RICORRERÀ ALLA CREAZIONE DI UN FLAG PER LA NOTIFICA DELL'ERRORE E ALLA CONSEGUENTE RIMOZIONE (unlink(...)) DEI DOCUMENTI INTERESSATI
	
	// CATEGORIE DEI LIBRI (XML)
	$sql = "SELECT ID, Denominazione, Immagine FROM Categorie_Libri";
	$result=mysqli_query($conn, $sql);
	
	$dom = new DOMDocument("1.0", "UTF-8");
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	
	$implementation = new DOMImplementation();
	$dom->appendChild($implementation->createDocumentType("categorie","","Dtd/Categorie_Libri.dtd"));
	
	$root=$dom->createElement("categorie");
	
	while($row=mysqli_fetch_array($result)){
		
		$categoria=$dom->createElement("categoria");
		$categoria->setAttribute("id", $row["ID"]);
		
		$denominazione=$dom->createElement("denominazione", $row["Denominazione"]);
		$categoria->appendChild($denominazione);
		
		$immagine=$dom->createElement("immagine", $row["Immagine"]);
		$categoria->appendChild($immagine);
		
		$root->appendChild($categoria);			
	}
	
	$dom->appendChild($root);
	
	$dom->save("../../XML/Categorie_Libri.xml");
	
	$docCategorieLibri=new DOMDocument();
	$docCategorieLibri->load("../../XML/Categorie_Libri.xml");
	
	if($docCategorieLibri->validate()){
		
		// PIATTAFORME DEI VIDEOGIOCHI (XML)
		$sql = "SELECT ID, Denominazione, Immagine FROM Piattaforme_Videogiochi";
		$result=mysqli_query($conn, $sql);
		
		$dom = new DOMDocument("1.0", "UTF-8");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		
		$implementation = new DOMImplementation();
		$dom->appendChild($implementation->createDocumentType("piattaforme","","Dtd/Piattaforme_Videogiochi.dtd"));
		
		$root=$dom->createElement("piattaforme");
		
		while($row=mysqli_fetch_array($result)){
			
			$piattaforma=$dom->createElement("piattaforma");
			$piattaforma->setAttribute("id", $row["ID"]);
			
			$denominazione=$dom->createElement("denominazione", $row["Denominazione"]);
			$piattaforma->appendChild($denominazione);
			
			$immagine=$dom->createElement("immagine", $row["Immagine"]);
			$piattaforma->appendChild($immagine);
			
			$root->appendChild($piattaforma);			
		}
		
		$dom->appendChild($root);
		
		$dom->save("../../XML/Piattaforme_Videogiochi.xml");
		
		$docPiattaformeVideogiochi=new DOMDocument();
		$docPiattaformeVideogiochi->load("../../XML/Piattaforme_Videogiochi.xml");
		
		if($docPiattaformeVideogiochi->validate()){
			
			// CARRELLO DEI CLIENTI
			$sql = "SELECT ID FROM $tab WHERE Tipo_Utente='C'";
			$result=mysqli_query($conn, $sql);
			
			$dom = new DOMDocument("1.0", "UTF-8");
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			
			$root=$dom->createElement("carrelli");
			$root->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
			$root->setAttribute("xsi:noNamespaceSchemaLocation", "Schema/Carrelli_Clienti.xsd");
			
			while($row=mysqli_fetch_array($result)){
				
				$carrello=$dom->createElement("carrello");
				$carrello->setAttribute("idCliente", $row["ID"]);
				
				if($row["ID"]==6) {
					$offerta=$dom->createElement("offerta");
					$offerta->setAttribute("id", 2);
					$offerta->setAttribute("idProdotto", 2);
					
					$prezzoContabile=$dom->createElement("prezzoContabile", number_format(84.50, 2,".",""));
					
					$offerta->appendChild($prezzoContabile);
					
					$sconto=$dom->createElement("sconto");
					
					$scontoFuturo=$dom->createElement("scontoFuturo");
					$scontoFuturo->setAttribute("percentuale", number_format(2, 2,".",""));
					$scontoFuturo->setAttribute("inizioApplicazione", "2024-06-01");
					$scontoFuturo->setAttribute("fineApplicazione", "2024-09-01");
					
					$sconto->appendChild($scontoFuturo);
					
					$offerta->appendChild($sconto);
					
					$quantitativo=$dom->createElement("quantitativo", 1);
					
					$offerta->appendChild($quantitativo);
					
					$carrello->appendChild($offerta);
				}
				
				$root->appendChild($carrello);			
			}
			
			$dom->appendChild($root);
			
			$dom->save("../../XML/Carrelli_Clienti.xml");
			
			$docCarrelli=new DOMDocument();
			$docCarrelli->load("../../XML/Carrelli_Clienti.xml");
			
			if($docCarrelli->schemaValidate("../../XML/Schema/Carrelli_Clienti.xsd")){
				
				// ACQUISTI DEI CLIENTI
				$sql = "SELECT ID FROM $tab WHERE Tipo_Utente='C'";
				$result=mysqli_query($conn, $sql);
				
				$dom = new DOMDocument("1.0", "UTF-8");
				$dom->preserveWhiteSpace = false;
				$dom->formatOutput = true;
				
				$root=$dom->createElement("acquisti");
				$root->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
				$root->setAttribute("xsi:noNamespaceSchemaLocation", "Schema/Acquisti_Clienti.xsd");
				
				while($row=mysqli_fetch_array($result)){
					
					$acquistiPerCliente=$dom->createElement("acquistiPerCliente");
					$acquistiPerCliente->setAttribute("idCliente", $row["ID"]);
					
					if($row["ID"]==4) {
						$acquistiPerCliente->setAttribute("ultimoIdPerAcquisto", 2);
						
						$acquistoPerCliente=$dom->createElement("acquistoPerCliente");
						$acquistoPerCliente->setAttribute("id", 1);
						$acquistoPerCliente->setAttribute("indirizzoConsegna", "Via del Salice, 82");
						$acquistoPerCliente->setAttribute("cittaConsegna", "Latina");
						$acquistoPerCliente->setAttribute("capConsegna", "04100");
						$acquistoPerCliente->setAttribute("dataAcquisto", "2024-05-28");
						$acquistoPerCliente->setAttribute("dataConsegna", "2024-05-30");
						$acquistoPerCliente->setAttribute("prezzoTotale", 52.45);
						$acquistoPerCliente->setAttribute("scontoGenerale", number_format(0, 2,".",""));
						
						$offerta=$dom->createElement("offerta");
						$offerta->setAttribute("id", 1);
						$offerta->setAttribute("idProdotto", 1);
						
						$prezzoContabile=$dom->createElement("prezzoContabile", 52.45);
						
						$offerta->appendChild($prezzoContabile);
						
						$quantitativo=$dom->createElement("quantitativo", 1);
						
						$offerta->appendChild($quantitativo);
						
						$bonus=$dom->createElement("bonus");
						
						$numeroCrediti=$dom->createElement("numeroCrediti", number_format(25, 2,".",""));
						
						$bonus->appendChild($numeroCrediti);
						
						$offerta->appendChild($bonus);
						
						$acquistoPerCliente->appendChild($offerta);
						
						$acquistiPerCliente->appendChild($acquistoPerCliente);
						
						$acquistoPerCliente=$dom->createElement("acquistoPerCliente");
						$acquistoPerCliente->setAttribute("id", 2);
						$acquistoPerCliente->setAttribute("indirizzoConsegna", "Via del Salice, 82");
						$acquistoPerCliente->setAttribute("cittaConsegna", "Latina");
						$acquistoPerCliente->setAttribute("capConsegna", "04100");
						$acquistoPerCliente->setAttribute("dataAcquisto", "2024-05-31");
						$acquistoPerCliente->setAttribute("dataConsegna", "2024-06-02");
						$acquistoPerCliente->setAttribute("prezzoTotale", number_format(175,2,".",""));
						$acquistoPerCliente->setAttribute("scontoGenerale", number_format(0, 2,".",""));
						
						$offerta=$dom->createElement("offerta");
						$offerta->setAttribute("id", 4);
						$offerta->setAttribute("idProdotto", 4);
						
						$prezzoContabile=$dom->createElement("prezzoContabile", number_format(35,2,".",""));
						
						$offerta->appendChild($prezzoContabile);
						
						$quantitativo=$dom->createElement("quantitativo", 5);
						
						$offerta->appendChild($quantitativo);
						
						$acquistoPerCliente->appendChild($offerta);
						
						$acquistiPerCliente->appendChild($acquistoPerCliente);
						
					}
					else {
						if($row["ID"]==6) {
							$acquistiPerCliente->setAttribute("ultimoIdPerAcquisto", 1);
							
							$acquistoPerCliente=$dom->createElement("acquistoPerCliente");
							$acquistoPerCliente->setAttribute("id", 1);
							$acquistoPerCliente->setAttribute("indirizzoConsegna", "Via Don Carlo Torello, 112");
							$acquistoPerCliente->setAttribute("cittaConsegna", "Latina");
							$acquistoPerCliente->setAttribute("capConsegna", "04100");
							$acquistoPerCliente->setAttribute("dataAcquisto", "2024-05-28");
							$acquistoPerCliente->setAttribute("dataConsegna", "2024-05-30");
							$acquistoPerCliente->setAttribute("prezzoTotale", number_format(249.50,2,".",""));
							$acquistoPerCliente->setAttribute("scontoGenerale", number_format(0, 2,".",""));
							
							$offerta=$dom->createElement("offerta");
							$offerta->setAttribute("id", 3);
							$offerta->setAttribute("idProdotto", 3);
							
							$prezzoContabile=$dom->createElement("prezzoContabile", number_format(249.50,2,".",""));
							
							$offerta->appendChild($prezzoContabile);
							
							$sconto=$dom->createElement("sconto");
										
							$scontoFuturo=$dom->createElement("scontoFuturo");
							$scontoFuturo->setAttribute("percentuale", number_format(5,2,".",""));
							$scontoFuturo->setAttribute("inizioApplicazione", "2024-09-01");
							$scontoFuturo->setAttribute("fineApplicazione", "2024-12-01");
							
							$sconto->appendChild($scontoFuturo);
							
							$offerta->appendChild($sconto);
							
							$quantitativo=$dom->createElement("quantitativo", 1);
							
							$offerta->appendChild($quantitativo);
							
							$acquistoPerCliente->appendChild($offerta);
							
							$acquistiPerCliente->appendChild($acquistoPerCliente);
						}
						else
							$acquistiPerCliente->setAttribute("ultimoIdPerAcquisto", 0);
					}	
					
					$root->appendChild($acquistiPerCliente);			
				}
				
				$dom->appendChild($root);
				
				$dom->save("../../XML/Acquisti_Clienti.xml");
				
				$docAcquisti=new DOMDocument();
				$docAcquisti->load("../../XML/Acquisti_Clienti.xml");
				
				if($docAcquisti->schemaValidate("../../XML/Schema/Acquisti_Clienti.xsd")){
				
					// TARIFFE RELATIVE ALLE RIDUZIONI DI PREZZO (SCONTI)
					$sql="SELECT MAKEDATE(YEAR(NOW()),1) AS Primo_Giorno";
					$result=mysqli_query($conn, $sql);
					
					while($row=mysqli_fetch_array($result))
						$dataPerControllo=$row["Primo_Giorno"];
					
					$dom = new DOMDocument("1.0", "UTF-8");
					$dom->preserveWhiteSpace = false;
					$dom->formatOutput = true;
					
					$implementation = new DOMImplementation();
					$dom->appendChild($implementation->createDocumentType("tariffe","","Dtd/Tariffe_Sconti.dtd"));
					
					$root=$dom->createElement("tariffe");
					
					$tariffaASoglia=$dom->createElement("tariffaASoglia");
					$tariffaASoglia->setAttribute("id", 1);
					$tariffaASoglia->setAttribute("soglia", number_format(200, 2,".",""));
					$tariffaASoglia->setAttribute("basePercentuale", number_format(2, 2,".",""));
					
					$root->appendChild($tariffaASoglia);
					
					$tariffaFedeltaElite=$dom->createElement("tariffaFedeltaElite");
					$tariffaFedeltaElite->setAttribute("id", 2);
					$tariffaFedeltaElite->setAttribute("soglia", number_format(150, 2));
					$tariffaFedeltaElite->setAttribute("basePercentuale", number_format(5, 2,".",""));
					$tariffaFedeltaElite->setAttribute("dataPerControllo", $dataPerControllo);
					
					$root->appendChild($tariffaFedeltaElite);
					
					$tariffaPerVIP=$dom->createElement("tariffaPerVIP");
					$tariffaPerVIP->setAttribute("id", 3);
					$tariffaPerVIP->setAttribute("sogliaReputazione", 66);
					$tariffaPerVIP->setAttribute("basePercentuale",number_format(5, 2,".",""));
					
					$root->appendChild($tariffaPerVIP);
					
					$tariffaDiAnzianita=$dom->createElement("tariffaDiAnzianita");
					$tariffaDiAnzianita->setAttribute("id", 4);
					$tariffaDiAnzianita->setAttribute("sogliaAnniMinima", 1);
					$tariffaDiAnzianita->setAttribute("sogliaAnniMassima", 3);
					$tariffaDiAnzianita->setAttribute("basePercentuale", number_format(1, 2,".",""));
					
					$root->appendChild($tariffaDiAnzianita);
					
					$dom->appendChild($root);
					
					$dom->save("../../XML/Tariffe_Sconti.xml");
					
					$docTariffeSconti=new DOMDocument();
					$docTariffeSconti->load("../../XML/Tariffe_Sconti.xml");
					
					if($docTariffeSconti->validate()){
						
						// STATO RELATIVO ALLE RIDUZIONI DI PREZZO ACCESSIBILI AI VARI CLIENTI
						$sql="SELECT ID FROM $tab WHERE Tipo_Utente='C'";
						$result=mysqli_query($conn, $sql);
						
						$dom = new DOMDocument("1.0", "UTF-8");
						$dom->preserveWhiteSpace = false;
						$dom->formatOutput = true;
						
						$implementation = new DOMImplementation();
						$dom->appendChild($implementation->createDocumentType("riduzioni","","Dtd/Riduzioni_Prezzi.dtd"));
						
						$root=$dom->createElement("riduzioni");
						
						while($row=mysqli_fetch_array($result)) {
							$riduzione=$dom->createElement("riduzione");
							$riduzione->setAttribute("idCliente", $row["ID"]);
							
							$sconti=$dom->createElement("sconti");
							
							$aSoglia=$dom->createElement("aSoglia");
							$aSoglia->setAttribute("idTariffaSoglia", 1);
							
							if($row["ID"]==4)
								$aSoglia->setAttribute("creditiSpesi", 227.45);
							else {
								if($row["ID"]==6)
									$aSoglia->setAttribute("creditiSpesi", number_format(249.50,2,".",""));
								else
									$aSoglia->setAttribute("creditiSpesi", number_format(0, 2,".",""));
							}
							
							if($row["ID"]==4 || $row["ID"]==6) {
								$aSoglia->setAttribute("superamenti", 1);
								$aSoglia->setAttribute("fruibile", 1);
							}
							else {
								$aSoglia->setAttribute("superamenti", 0);
								$aSoglia->setAttribute("fruibile", 0);
							}
							
							$sconti->appendChild($aSoglia);
							
							$fedeltaElite=$dom->createElement("fedeltaElite");
							$fedeltaElite->setAttribute("idTariffaFedeltaElite", 2);
							
							if($row["ID"]==4)
								$fedeltaElite->setAttribute("creditiSpesi", 227.45);
							else {
								if($row["ID"]==6)
									$fedeltaElite->setAttribute("creditiSpesi", 249.50);
								else
									$fedeltaElite->setAttribute("creditiSpesi", number_format(0, 2,".",""));
							}
								
							if($row["ID"]==4 || $row["ID"]==6) {
								$fedeltaElite->setAttribute("fruibile", 1);
								$fedeltaElite->setAttribute("esercitabile", 1);
							}
							else {
								$fedeltaElite->setAttribute("fruibile", 0);
								$fedeltaElite->setAttribute("esercitabile", 1);
							}
							
							$sconti->appendChild($fedeltaElite);
							
							$acquistoPromozionale=$dom->createElement("acquistoPromozionale");
							
							if($row["ID"]==6)
								$percentuale=$dom->createElement("percentuale", number_format(5, 2,".",""));
							else
								$percentuale=$dom->createElement("percentuale", number_format(0, 2,".",""));	
							
							$acquistoPromozionale->appendChild($percentuale);
							$sconti->appendChild($acquistoPromozionale);
							
							$perVIP=$dom->createElement("perVIP");
							$perVIP->setAttribute("idTariffaPerVIP", 3);
							
							if($row["ID"]==6)
								$perVIP->setAttribute("fruibile", 1);
							else
								$perVIP->setAttribute("fruibile", 0);
							
							$sconti->appendChild($perVIP);
							
							$diAnzianita=$dom->createElement("diAnzianita");
							$diAnzianita->setAttribute("idTariffaDiAnzianita", 4);
							$diAnzianita->setAttribute("fruibile", 0);
							$percentuale=$dom->createElement("percentuale", number_format(0, 2,".",""));
							
							$diAnzianita->appendChild($percentuale);
							$sconti->appendChild($diAnzianita);
							
							$riduzione->appendChild($sconti);
							
							$root->appendChild($riduzione);
						}
						
						$dom->appendChild($root);
						
						$dom->save("../../XML/Riduzioni_Prezzi.xml");
						
						$docRiduzioniPrezzi = new DOMDocument;
						$docRiduzioniPrezzi->load("../../XML/Riduzioni_Prezzi.xml");
						
						if($docRiduzioniPrezzi->validate()) {
							
							// RICHIESTE PER LA RICARICA DEL SALDO (XML)
							$sql = "SELECT ID, ID_Richiedente, Data_Ora_Richiesta, Numero_Crediti, Stato FROM Richieste_Crediti";
							$result=mysqli_query($conn, $sql);
							
							$dom = new DOMDocument("1.0", "UTF-8");
							$dom->preserveWhiteSpace = false;
							$dom->formatOutput = true;
							
							$root=$dom->createElement("richieste");
							$root->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
							$root->setAttribute("xsi:noNamespaceSchemaLocation", "Schema/Richieste_Crediti.xsd");
							$root->setAttribute("ultimoId", mysqli_num_rows($result));
							
							while($row=mysqli_fetch_array($result)){
								
								$richiesta=$dom->createElement("richiesta");
								$richiesta->setAttribute("id", $row["ID"]);
								$richiesta->setAttribute("idRichiedente", $row["ID_Richiedente"]);
								$richiesta->setAttribute("dataOraRichiesta", $row["Data_Ora_Richiesta"]);
								$richiesta->setAttribute("numeroCrediti", $row["Numero_Crediti"]);
								$richiesta->setAttribute("stato", $row["Stato"]);
								
								$root->appendChild($richiesta);			
							}
							
							$dom->appendChild($root);
							
							$dom->save("../../XML/Richieste_Crediti.xml");
							
							$docRichieste=new DOMDocument();
							$docRichieste->load("../../XML/Richieste_Crediti.xml");
							
							if($docRichieste->schemaValidate("../../XML/Schema/Richieste_Crediti.xsd")){
							
								// DISCUSSIONI (DOMANDE E/O RISPOSTE) DEGLI UTENTI
								$dom = new DOMDocument("1.0", "UTF-8");
								$dom->preserveWhiteSpace = false;
								$dom->formatOutput = true;
								
								$root=$dom->createElement("discussioni");
								$root->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
								$root->setAttribute("xsi:noNamespaceSchemaLocation", "Schema/Discussioni.xsd");
								$root->setAttribute("ultimoId", 1);
								
								$discussione=$dom->createElement("discussione");
								$discussione->setAttribute("id", 1);
								$discussione->setAttribute("idAutore", 6);
								$discussione->setAttribute("dataOraIssue", "2024-06-03 10:32:00");
								$discussione->setAttribute("moderata", "No");
								$discussione->setAttribute("risolta", "No");
								
								$titolo=$dom->createElement("titolo", "Offline?");
								$discussione->appendChild($titolo);
								
								$descrizione=$dom->createElement("descrizione", "È richiesto l'abbonamento a PlayStation Plus oppure si può giocare anche senza?");
								$discussione->appendChild($descrizione);
								
								$interventi=$dom->createElement("interventi");
								$interventi->setAttribute("ultimoId", 2);
								
								$intervento=$dom->createElement("intervento");
								$intervento->setAttribute("id", 1);
								$intervento->setAttribute("idPartecipante", 4);
								$intervento->setAttribute("dataOraIssue", "2024-06-03 14:17:00");
								$intervento->setAttribute("moderato", "No");
								
								$testo=$dom->createElement("testo", "Puoi giocare anche offline. Però ti assicuro che la modalità online ti può aiutare molto nei momenti in cui il gioco si fa estremamente difficile. Puoi chiedere il supporto di un altro giocatore scrivendo un messaggio nella community di Bloodborne su PS, così facendo il suo cacciatore interverrà direttamente nel tuo scenario di gioco e ti sosterrà contro i Boss più ostici.");
								
								$intervento->appendChild($testo);
								
								$valutazioni=$dom->createElement("valutazioni");
								
								$valutazione=$dom->createElement("valutazione");
								$valutazione->setAttribute("idVotante", 6);
								$valutazione->setAttribute("supporto", 5);
								$valutazione->setAttribute("utilita", 5);
								
								$valutazioni->appendChild($valutazione);
								
								$intervento->appendChild($valutazioni);
								
								$interventi->appendChild($intervento);
								
								$intervento=$dom->createElement("intervento");
								$intervento->setAttribute("id", 2);
								$intervento->setAttribute("idPartecipante", 5);
								$intervento->setAttribute("dataOraIssue", "2024-06-03 15:00:00");
								$intervento->setAttribute("moderato", "No");
								
								$testo=$dom->createElement("testo", "Domanda inutile a mio avviso... Ti basta fare una ricerca su Internet!!!");
								
								$intervento->appendChild($testo);
								
								$valutazioni=$dom->createElement("valutazioni");
								
								$intervento->appendChild($valutazioni);
								
								$interventi->appendChild($intervento);
								
								$discussione->appendChild($interventi);
								
								$root->appendChild($discussione);
								
								$dom->appendChild($root);
							
								$dom->save("../../XML/Discussioni.xml");
								
								$docDiscussioni=new DOMDocument();
								$docDiscussioni->load("../../XML/Discussioni.xml");
								
								if($docDiscussioni->schemaValidate("../../XML/Schema/Discussioni.xsd")){
									
									// PRODOTTI IN MAGAZZINO
									$dom = new DOMDocument("1.0", "UTF-8");
									$dom->preserveWhiteSpace = false;
									$dom->formatOutput = true;
									
									$implementation = new DOMImplementation();
									$dom->appendChild($implementation->createDocumentType("prodotti","","Dtd/Prodotti.dtd"));
									
									$root=$dom->createElement("prodotti");
									$root->setAttribute("ultimoId", 10);
									
									$prodotto=$dom->createElement("prodotto");
									$prodotto->setAttribute("id", 1);
									
									$prodotto->appendChild($dom->createElement("nome", "Bloodborne"));
									
									$tipologia=$dom->createElement("tipologia");
									
									$videogioco=$dom->createElement("videogioco");
									
									$piattaforma=$dom->createElement("piattaforma");
									$piattaforma->setAttribute("idPiattaforma", 1);
									
									$videogioco->appendChild($piattaforma);
									
									$generi=$dom->createElement("generi");
									
									$genere=$dom->createElement("genere");
									$genere->setAttribute("idGenere", 1);
									
									$generi->appendChild($genere);
									
									$genere=$dom->createElement("genere");
									$genere->setAttribute("idGenere", 2);
									
									$generi->appendChild($genere);
									
									$videogioco->appendChild($generi);
									
									$videogioco->appendChild($dom->createElement("casaProduzione", "FromSoftware"));
									
									$tipologia->appendChild($videogioco);
									
									$prodotto->appendChild($tipologia);
									
									$prodotto->appendChild($dom->createElement("descrizione", "Un incredibile viaggio in una città gotica ricostruita con una dovizia di dettagli e popolata da incubi e tenebre!"));
									
									$prodotto->appendChild($dom->createElement("immagine", "../../Immagini/Catalogo/Bloodborne_PS4.jpg"));
									
									$prodotto->appendChild($dom->createElement("prezzoListino", 104.95));
									
									$prodotto->appendChild($dom->createElement("annoUscita", 2015));
									
									$recensioni=$dom->createElement("recensioni");
									
									$recensione=$dom->createElement("recensione");
									$recensione->setAttribute("idRecensione", 3);
									
									$recensioni->appendChild($recensione);
									
									$prodotto->appendChild($recensioni);
									
									$discussioni=$dom->createElement("discussioni");
									
									$discussione=$dom->createElement("discussione");
									$discussione->setAttribute("idDiscussione", 1);
									
									$discussioni->appendChild($discussione);
									
									$prodotto->appendChild($discussioni);
									
									$root->appendChild($prodotto);
									
									$prodotto=$dom->createElement("prodotto");
									$prodotto->setAttribute("id", 2);
									
									$prodotto->appendChild($dom->createElement("nome", "Fuga dal Campo 14"));
									
									$tipologia=$dom->createElement("tipologia");
									
									$libro=$dom->createElement("libro");
									
									$categorie=$dom->createElement("categorie");
									
									$categoria=$dom->createElement("categoria");
									$categoria->setAttribute("idCategoria", 1);
									
									$categorie->appendChild($categoria);
									
									$libro->appendChild($categorie);
									
									$autori=$dom->createElement("autori");
									
									$autore=$dom->createElement("autore");
									$autore->appendChild($dom->createElement("nome", "Blain"));
									$autore->appendChild($dom->createElement("cognome", "Harden"));
									
									$autori->appendChild($autore);
									
									$libro->appendChild($autori);
									
									$tipologia->appendChild($libro);
									
									$prodotto->appendChild($tipologia);
									
									$prodotto->appendChild($dom->createElement("descrizione", "Shin Dong-hyuk è l'unico uomo nato in un campo di prigionia della Corea del Nord ad essere riuscito a scappare. La sua fuga e il libro che la racconta sono diventati un caso internazionale, che ha convinto le Nazioni Unite a costituire una commissione d'indagine sui campi di prigionia nordcoreani. Il Campo 14 è grande quanto Los Angeles, ed è visibile su Google Maps: eppure resta invisibile agli occhi del mondo. Il crimine che Shin ha commesso è avere uno zio che negli anni cinquanta fuggì in Corea del Sud; nasce quindi nel 1982 dietro il filo spinato del campo, dove la sua famiglia è stata rinchiusa da decenni. Non sa che esiste il mondo esterno, ed è a tutti gli effetti uno schiavo. Solo a ventitré anni riuscirà a fuggire, grazie all'aiuto di un compagno che tenterà la fuga con lui, e ad arrivare a piedi e con vestiti di fortuna in Cina, e da lì in America. Questa è la sua storia."));
									
									$prodotto->appendChild($dom->createElement("immagine", "../../Immagini/Catalogo/Fuga_dal_Campo_14.jpg"));
									
									$prodotto->appendChild($dom->createElement("prezzoListino", number_format(84.50, 2, ".", "")));
									
									$prodotto->appendChild($dom->createElement("annoUscita", 2012));
									
									$prodotto->appendChild($dom->createElement("recensioni"));
									
									$prodotto->appendChild($dom->createElement("discussioni"));
									
									$root->appendChild($prodotto);
									
									$prodotto=$dom->createElement("prodotto");
									$prodotto->setAttribute("id", 3);
									
									$prodotto->appendChild($dom->createElement("nome", "La Forma della Voce (Ultimate Box)"));
									
									$tipologia=$dom->createElement("tipologia");
									
									$libro=$dom->createElement("libro");
									
									$categorie=$dom->createElement("categorie");
									
									$categoria=$dom->createElement("categoria");
									$categoria->setAttribute("idCategoria", 2);
									
									$categorie->appendChild($categoria);
									
									$categoria=$dom->createElement("categoria");
									$categoria->setAttribute("idCategoria", 3);
									
									$categorie->appendChild($categoria);
									
									$libro->appendChild($categorie);
									
									$autori=$dom->createElement("autori");
									
									$autore=$dom->createElement("autore");
									$autore->appendChild($dom->createElement("nome", "Yoshitoki"));
									$autore->appendChild($dom->createElement("cognome", "Oima"));
									
									$autori->appendChild($autore);
									
									$libro->appendChild($autori);
									
									$tipologia->appendChild($libro);
									
									$prodotto->appendChild($tipologia);
									
									$prodotto->appendChild($dom->createElement("descrizione", "Shoya Ishida frequenta le scuole elementari e non sopporta in alcun modo le ragazze. Adora invece mettersi alla prova con i compagni maschi, ingaggiando assurde prove di coraggio. Le cose cambiano quando nella sua classe arriva una nuova alunna, Shoko Nishimiya, una bambina non udente che usa un quaderno per comunicare con gli altri... Shoko viene subito presa di mira dai bulletti della scuola, in special modo da Shoya. Il ragazzino, però, non può ancora sapere che gli effetti del suo comportamento innescheranno la miccia che sconvolgerà la sua prospettiva sulle cose, stravolgendo il suo futuro e quello della sua compagna..."));
									
									$prodotto->appendChild($dom->createElement("immagine", "../../Immagini/Catalogo/La_Forma_della_Voce.jpg"));
									
									$prodotto->appendChild($dom->createElement("prezzoListino", number_format(249.5, 2, ".", "")));
									
									$prodotto->appendChild($dom->createElement("annoUscita", 2021));
									
									$recensioni=$dom->createElement("recensioni");
									
									$recensione=$dom->createElement("recensione");
									$recensione->setAttribute("idRecensione", 2);
									
									$recensioni->appendChild($recensione);
									
									$prodotto->appendChild($recensioni);
									
									$prodotto->appendChild($dom->createElement("discussioni"));
									
									$root->appendChild($prodotto);
									
									$prodotto=$dom->createElement("prodotto");
									$prodotto->setAttribute("id", 4);
									
									$prodotto->appendChild($dom->createElement("nome", "Slam Dunk (Vol. 1)"));
									
									$tipologia=$dom->createElement("tipologia");
									
									$libro=$dom->createElement("libro");
									
									$categorie=$dom->createElement("categorie");
									
									$categoria=$dom->createElement("categoria");
									$categoria->setAttribute("idCategoria", 2);
									
									$categorie->appendChild($categoria);
									
									$categoria=$dom->createElement("categoria");
									$categoria->setAttribute("idCategoria", 5);
									
									$categorie->appendChild($categoria);
									
									$libro->appendChild($categorie);
									
									$autori=$dom->createElement("autori");
									
									$autore=$dom->createElement("autore");
									$autore->appendChild($dom->createElement("nome", "Takehiko"));
									$autore->appendChild($dom->createElement("cognome", "Inoue"));
									
									$autori->appendChild($autore);
									
									$libro->appendChild($autori);
									
									$tipologia->appendChild($libro);
									
									$prodotto->appendChild($tipologia);
									
									$prodotto->appendChild($dom->createElement("descrizione", "Hanamichi Sakuragi è un giovane teppista attaccabrighe che si iscrive al primo anno della scuola superiore Shohoku, nella prefettura di Kanagawa. È particolarmente sfortunato con le ragazze e ciò lo rende il triste bersaglio delle ripetute prese in giro da parte dei suoi amici Mito, Okusu, Takamiya e Noma. Dopo essere stato scaricato da una ragazza che era innamorata di un giocatore di basket, Hanamichi inizia ad odiare visceralmente questo sport; le cose, tuttavia, cambiano quando incontra Haruko Akagi, matricola come lui e appassionata di pallacanestro: la ragazza, infatti, lo incoraggia ad entrare nella squadra di basket della scuola impressionata dalla sua altezza e dalla sua prestanza fisica. Hanamichi se ne innamora perdutamente e accetta solo per poterla conquistare."));
									
									$prodotto->appendChild($dom->createElement("immagine", "../../Immagini/Catalogo/Slam_Dunk_1.jpg"));
									
									$prodotto->appendChild($dom->createElement("prezzoListino", number_format(35, 2, ".", "")));
									
									$prodotto->appendChild($dom->createElement("annoUscita", 2020));
									
									$recensioni=$dom->createElement("recensioni");
									
									$recensione=$dom->createElement("recensione");
									$recensione->setAttribute("idRecensione", 1);
									
									$recensioni->appendChild($recensione);
									
									$prodotto->appendChild($recensioni);
									
									$prodotto->appendChild($dom->createElement("discussioni"));
									
									$root->appendChild($prodotto);
									
									$prodotto=$dom->createElement("prodotto");
									$prodotto->setAttribute("id", 5);
									
									$prodotto->appendChild($dom->createElement("nome", "Persona 5: Royal"));
									
									$tipologia=$dom->createElement("tipologia");
									
									$videogioco=$dom->createElement("videogioco");
									
									$piattaforma=$dom->createElement("piattaforma");
									$piattaforma->setAttribute("idPiattaforma", 3);
									
									$videogioco->appendChild($piattaforma);
									
									$generi=$dom->createElement("generi");
									
									$genere=$dom->createElement("genere");
									$genere->setAttribute("idGenere", 2);
									
									$generi->appendChild($genere);
									
									$genere=$dom->createElement("genere");
									$genere->setAttribute("idGenere", 10);
									
									$generi->appendChild($genere);
									
									$videogioco->appendChild($generi);
									
									$videogioco->appendChild($dom->createElement("casaProduzione", "Atlus, P Studio"));
									
									$tipologia->appendChild($videogioco);
									
									$prodotto->appendChild($tipologia);
									
									$prodotto->appendChild($dom->createElement("descrizione", "Tentando di fermare un'aggressione ai danni di una donna, il protagonista del gioco, un ragazzo sedicenne il cui nome viene scelto dal giocatore, viene denunciato e arrestato dalla polizia dopo aver ferito involontariamente il colpevole. A causa di ciò, la sua fedina penale viene macchiata e per questo viene espulso dalla sua vecchia scuola e costretto a trasferirsi a Tokyo per frequentare la Shujin Academy, l'unica scuola disposta ad accettarlo. Il giovane va dunque a vivere nel quartiere di Yongen-Jaya, al caffè Leblanc, gestito da Sōjirō Sakura, conoscente dei suoi genitori, che accetta di prenderlo sotto la sua custodia durante il suo anno di libertà vigilata."));
									
									$prodotto->appendChild($dom->createElement("immagine", "../../Immagini/Catalogo/Persona_5_Royal.jpg"));
									
									$prodotto->appendChild($dom->createElement("prezzoListino", 299.95));
									
									$prodotto->appendChild($dom->createElement("annoUscita", 2022));
									
									$prodotto->appendChild($dom->createElement("recensioni"));
									
									$prodotto->appendChild($dom->createElement("discussioni"));
									
									$root->appendChild($prodotto);
									
									$prodotto=$dom->createElement("prodotto");
									$prodotto->setAttribute("id", 6);
									
									$prodotto->appendChild($dom->createElement("nome", "The Last of Us: Parte I"));
									
									$tipologia=$dom->createElement("tipologia");
									
									$videogioco=$dom->createElement("videogioco");
									
									$piattaforma=$dom->createElement("piattaforma");
									$piattaforma->setAttribute("idPiattaforma", 2);
									
									$videogioco->appendChild($piattaforma);
									
									$generi=$dom->createElement("generi");
									
									$genere=$dom->createElement("genere");
									$genere->setAttribute("idGenere", 2);
									
									$generi->appendChild($genere);
									
									$genere=$dom->createElement("genere");
									$genere->setAttribute("idGenere", 6);
									
									$generi->appendChild($genere);
									
									$videogioco->appendChild($generi);
									
									$videogioco->appendChild($dom->createElement("casaProduzione", "Naughty Dog"));
									
									$tipologia->appendChild($videogioco);
									
									$prodotto->appendChild($tipologia);
									
									$prodotto->appendChild($dom->createElement("descrizione", "The Last of Us è ambientato nel 2013, anno di uscita del gioco. Il fungo cordyceps (realmente esistente) causa un’epidemia di infezioni corrompendo la mente e i corpi delle persone e trasformandole in pericolosi mostri aggressivi e letali. Nel giro di pochi giorni la società collassa e poco possono istituzioni ed esercito di fronte all’irrefrenabile e crescente orda di infetti."));
									
									$prodotto->appendChild($dom->createElement("immagine", "../../Immagini/Catalogo/The_Last_of_Us_I.jpg"));
									
									$prodotto->appendChild($dom->createElement("prezzoListino", 299.95));
									
									$prodotto->appendChild($dom->createElement("annoUscita", 2022));
									
									$prodotto->appendChild($dom->createElement("recensioni"));
									
									$prodotto->appendChild($dom->createElement("discussioni"));
									
									$root->appendChild($prodotto);
									
									$prodotto=$dom->createElement("prodotto");
									$prodotto->setAttribute("id", 7);
									
									$prodotto->appendChild($dom->createElement("nome", "The Legend of Zelda: Breath of the Wild"));
									
									$tipologia=$dom->createElement("tipologia");
									
									$videogioco=$dom->createElement("videogioco");
									
									$piattaforma=$dom->createElement("piattaforma");
									$piattaforma->setAttribute("idPiattaforma", 4);
									
									$videogioco->appendChild($piattaforma);
									
									$generi=$dom->createElement("generi");
									
									$genere=$dom->createElement("genere");
									$genere->setAttribute("idGenere", 2);
									
									$generi->appendChild($genere);
									
									$genere=$dom->createElement("genere");
									$genere->setAttribute("idGenere", 5);
									
									$generi->appendChild($genere);
									
									$genere=$dom->createElement("genere");
									$genere->setAttribute("idGenere", 6);
									
									$generi->appendChild($genere);
									
									$genere=$dom->createElement("genere");
									$genere->setAttribute("idGenere", 7);
									
									$generi->appendChild($genere);
									
									$videogioco->appendChild($generi);
									
									$videogioco->appendChild($dom->createElement("casaProduzione", "Nintendo EPD"));
									
									$tipologia->appendChild($videogioco);
									
									$prodotto->appendChild($tipologia);
									
									$prodotto->appendChild($dom->createElement("descrizione", "The Legend of Zelda: Breath of the Wild rivoluziona i capisaldi che hanno caratterizzato la serie per 30 anni, mantenendosi però fedele al concept di fondo. La trama del gioco inizia avvolta nel mistero... Link si risveglia dopo un sonno durato 100 anni in un luogo che non aveva mai visto prima. Per di più non ha nessun ricordo del passato e si ritrova davanti agli occhi un regno in rovina. Mossi i primi passi in questa landa desolata, Link decide di intraprendere un grande viaggio per recuperare la memoria e scoprire cosa è accaduto al Regno di Hyrule. Ma un’entità malefica incombe... Riuscirà l’eroe leggendario a portare a termine il suo compito? La libertà concessa al giocatore in questo capitolo di The Legend of Zelda non ha precedenti nella serie. Per la prima volta in assoluto Link potrà esplorare un mondo open-world che si espande a perdita d’occhio e dove ogni luogo visibile può essere raggiunto e visitato.I giocatori si ritroveranno a scalare torri e montagne in cerca di nuove destinazioni, per poi scegliere un percorso per raggiungerle. Lungo la strada dovranno affrontare nemici giganteschi, cacciare animali selvatici e raccogliere il cibo e gli oggetti che servono per sopravvivere."));
									
									$prodotto->appendChild($dom->createElement("immagine", "../../Immagini/Catalogo/The_Legend_of_Zelda_BOTW.jpg"));
									
									$prodotto->appendChild($dom->createElement("prezzoListino", number_format(224.5,2,".","")));
									
									$prodotto->appendChild($dom->createElement("annoUscita", 2017));
									
									$prodotto->appendChild($dom->createElement("recensioni"));
									
									$prodotto->appendChild($dom->createElement("discussioni"));
									
									$root->appendChild($prodotto);
									
									$prodotto=$dom->createElement("prodotto");
									$prodotto->setAttribute("id", 8);
									
									$prodotto->appendChild($dom->createElement("nome", "Elden Ring: Edizione Shadow of the Erdtree"));
									
									$tipologia=$dom->createElement("tipologia");
									
									$videogioco=$dom->createElement("videogioco");
									
									$piattaforma=$dom->createElement("piattaforma");
									$piattaforma->setAttribute("idPiattaforma", 2);
									
									$videogioco->appendChild($piattaforma);
									
									$generi=$dom->createElement("generi");
									
									$genere=$dom->createElement("genere");
									$genere->setAttribute("idGenere", 2);
									
									$generi->appendChild($genere);
									
									$genere=$dom->createElement("genere");
									$genere->setAttribute("idGenere", 5);
									
									$generi->appendChild($genere);
									
									$genere=$dom->createElement("genere");
									$genere->setAttribute("idGenere", 10);
									
									$generi->appendChild($genere);
									
									$videogioco->appendChild($generi);
									
									$videogioco->appendChild($dom->createElement("casaProduzione", "FromSoftware"));
									
									$tipologia->appendChild($videogioco);
									
									$prodotto->appendChild($tipologia);
									
									$prodotto->appendChild($dom->createElement("descrizione", "Nell'Interregno governato dalla regina Marika l'Eterna, l'Anello ancestrale, sorgente dell'Albero Madre, è andato in frantumi. I discendenti di Marika, progenie di semidei, rivendicarono i frammenti dell'Anello ancestrale, conosciuti col nome di rune maggiori. La follia derivata dalla loro nuova forza diede origine a un conflitto: la Disgregazione. Una guerra che portò all'abbandono della Volontà superiore. E ora la guida della grazia scenderà sui Senzaluce, cui è stata preclusa la grazia dell'oro e sono stati esiliati dall'Interregno. Senza vita eppure vivente, avendo perduto la tua grazia da tempo, segui il sentiero verso l'Interregno oltre il mare nebbioso per apparire dinanzi all'Anello ancestrale. E divenire Lord. Espansione Shadow of the Erdtree: Le terre delle ombre. Un luogo offuscato dall'Albero Madre. Qui per prima giunse la divina Marika. Una landa distrutta da una battaglia dimenticata. Arsa dalle fiamme di Messmer. Fu alla volta di questa regione che partì Miquella. Spogliandosi del suo corpo, della sua forza, del suo retaggio. Di ogni tratto aureo. E ora Miquella attende il ritorno del suo Lord promesso."));
									
									$prodotto->appendChild($dom->createElement("immagine", "../../Immagini/Catalogo/Elden_Ring_SOTE.jpg"));
									
									$prodotto->appendChild($dom->createElement("prezzoListino", 399.95));
									
									$prodotto->appendChild($dom->createElement("annoUscita", 2024));
									
									$prodotto->appendChild($dom->createElement("recensioni"));
									
									$prodotto->appendChild($dom->createElement("discussioni"));
									
									$root->appendChild($prodotto);
									
									$prodotto=$dom->createElement("prodotto");
									$prodotto->setAttribute("id", 9);
									
									$prodotto->appendChild($dom->createElement("nome", "Harry Potter. Edizione Castello di Hogwarts"));
									
									$tipologia=$dom->createElement("tipologia");
									
									$libro=$dom->createElement("libro");
									
									$categorie=$dom->createElement("categorie");
									
									$categoria=$dom->createElement("categoria");
									$categoria->setAttribute("idCategoria", 4);
									
									$categorie->appendChild($categoria);
									
									$categoria=$dom->createElement("categoria");
									$categoria->setAttribute("idCategoria", 7);
									
									$categorie->appendChild($categoria);
									
									$categoria=$dom->createElement("categoria");
									$categoria->setAttribute("idCategoria", 8);
									
									$categorie->appendChild($categoria);
									
									$libro->appendChild($categorie);
									
									$autori=$dom->createElement("autori");
									
									$autore=$dom->createElement("autore");
									$autore->appendChild($dom->createElement("nome", "Joanne Kathleen"));
									$autore->appendChild($dom->createElement("cognome", "Rowling"));
									
									$autori->appendChild($autore);
									
									$libro->appendChild($autori);
									
									$tipologia->appendChild($libro);
									
									$prodotto->appendChild($tipologia);
									
									$prodotto->appendChild($dom->createElement("descrizione", "Harry Potter è un ragazzo normale, o quantomeno è convinto di esserlo, anche se a volte provoca strani fenomeni, come farsi ricrescere i capelli inesorabilmente tagliati dai perfidi zii. Vive con loro al numero 4 di Privet Drive: una strada di periferia come tante, dove non succede mai nulla fuori dall’ordinario. Finché un giorno, poco prima del suo undicesimo compleanno, riceve una misteriosa lettera che gli rivela la sua vera natura: Harry è un mago e la Scuola di Magia e Stregoneria di Hogwarts è pronta ad accoglierlo..."));
									
									$prodotto->appendChild($dom->createElement("immagine", "../../Immagini/Catalogo/Harry_Potter_la_Serie_Completa.jpg"));
									
									$prodotto->appendChild($dom->createElement("prezzoListino", number_format(694, 2, ".", "")));
									
									$prodotto->appendChild($dom->createElement("annoUscita", 2022));
									
									$prodotto->appendChild($dom->createElement("recensioni"));
									
									$prodotto->appendChild($dom->createElement("discussioni"));
									
									$root->appendChild($prodotto);
									
									$prodotto=$dom->createElement("prodotto");
									$prodotto->setAttribute("id", 10);
									
									$prodotto->appendChild($dom->createElement("nome", "Homunculus. L'occhio dell'anima (Vol. 1)"));
									
									$tipologia=$dom->createElement("tipologia");
									
									$libro=$dom->createElement("libro");
									
									$categorie=$dom->createElement("categorie");
									
									$categoria=$dom->createElement("categoria");
									$categoria->setAttribute("idCategoria", 2);
									
									$categorie->appendChild($categoria);
									
									$categoria=$dom->createElement("categoria");
									$categoria->setAttribute("idCategoria", 6);
									
									$categorie->appendChild($categoria);
									
									$libro->appendChild($categorie);
									
									$autori=$dom->createElement("autori");
									
									$autore=$dom->createElement("autore");
									$autore->appendChild($dom->createElement("nome", "Hideo"));
									$autore->appendChild($dom->createElement("cognome", "Yamamoto"));
									
									$autori->appendChild($autore);
									
									$libro->appendChild($autori);
									
									$tipologia->appendChild($libro);
									
									$prodotto->appendChild($tipologia);
									
									$prodotto->appendChild($dom->createElement("descrizione", "Susumu è uno strano e misterioso individuo, con un passato da nascondere e il vizio della menzogna. Indossa un completo, ha un aspetto distinto, eppure è un senzatetto. Vive ai margini di un parco, residenza fissa di un gruppo di clochard, e sebbene spesso si unisca a loro, allo stesso tempo mantiene le distanze, dimostrando diffidenza per tutto e tutti. L'unica cosa a cui è legato è la sua macchina, una specie di ventre materno, di cui sente il morboso bisogno. E proprio per guadagnare i soldi necessari a riscattare l'amato veicolo, rimosso dalla polizia, Susumu accetta di sottoporsi alla Trapanazione, un intervento chirurgico al cranio teso a destare i sensi sopiti dell'uomo. L'esperimento è condotto e finanziato da un ricco ed eccentrico studente di medicina, curioso di addentrarsi nel mondo del paranormale… Un viaggio a tinte fosche nell'animo umano."));
									
									$prodotto->appendChild($dom->createElement("immagine", "../../Immagini/Catalogo/Homunculus_1.jpg"));
									
									$prodotto->appendChild($dom->createElement("prezzoListino", number_format(35, 2, ".", "")));
									
									$prodotto->appendChild($dom->createElement("annoUscita", 2020));
									
									$prodotto->appendChild($dom->createElement("recensioni"));
									
									$prodotto->appendChild($dom->createElement("discussioni"));
									
									$root->appendChild($prodotto);
									
									$dom->appendChild($root);
								
									$dom->save("../../XML/Prodotti.xml");
									
									$docProdotti=new DOMDocument();
									$docProdotti->load("../../XML/Prodotti.xml");
									
									if($docProdotti->validate()){
										
										// OFFERTE NEL CATALOGO
										$dom = new DOMDocument("1.0", "UTF-8");
										$dom->preserveWhiteSpace = false;
										$dom->formatOutput = true;
										
										$root=$dom->createElement("offerte");
										$root->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
										$root->setAttribute("xsi:noNamespaceSchemaLocation", "Schema/Offerte.xsd");
										$root->setAttribute("ultimoId", 8);
										
										$offerta=$dom->createElement("offerta");
										$offerta->setAttribute("id", 1);
										$offerta->setAttribute("idProdotto", 1);
										
										$offerta->appendChild($dom->createElement("prezzoContabile", 52.45));
										
										$offerta->appendChild($dom->createElement("quantitativo", 29));
										
										$bonus=$dom->createElement("bonus");
										
										$bonus->appendChild($dom->createElement("numeroCrediti", number_format(25,2,".","")));
										
										$offerta->appendChild($bonus);
										
										$root->appendChild($offerta);
										
										$offerta=$dom->createElement("offerta");
										$offerta->setAttribute("id", 2);
										$offerta->setAttribute("idProdotto", 2);
										
										$offerta->appendChild($dom->createElement("prezzoContabile", number_format(84.5,2,".","")));
										
										$sconto=$dom->createElement("sconto");
										
										$scontoFuturo=$dom->createElement("scontoFuturo");
										$scontoFuturo->setAttribute("percentuale", number_format(2,2,".",""));
										$scontoFuturo->setAttribute("inizioApplicazione", "2024-06-01");
										$scontoFuturo->setAttribute("fineApplicazione", "2024-09-01");
										
										$sconto->appendChild($scontoFuturo);
										
										$offerta->appendChild($sconto);
										
										$offerta->appendChild($dom->createElement("quantitativo", 29));
										
										$root->appendChild($offerta);
										
										$offerta=$dom->createElement("offerta");
										$offerta->setAttribute("id", 3);
										$offerta->setAttribute("idProdotto", 3);
										
										$offerta->appendChild($dom->createElement("prezzoContabile", number_format(249.5,2,".","")));
										
										$sconto=$dom->createElement("sconto");
										
										$scontoFuturo=$dom->createElement("scontoFuturo");
										$scontoFuturo->setAttribute("percentuale", number_format(5,2,".",""));
										$scontoFuturo->setAttribute("inizioApplicazione", "2024-09-01");
										$scontoFuturo->setAttribute("fineApplicazione", "2024-12-01");
										
										$sconto->appendChild($scontoFuturo);
										
										$offerta->appendChild($sconto);
										
										$offerta->appendChild($dom->createElement("quantitativo", 9));
										
										$root->appendChild($offerta);
										
										$offerta=$dom->createElement("offerta");
										$offerta->setAttribute("id", 4);
										$offerta->setAttribute("idProdotto", 4);
										
										$offerta->appendChild($dom->createElement("prezzoContabile", number_format(35,2,".","")));
										
										$offerta->appendChild($dom->createElement("quantitativo", 25));
										
										$root->appendChild($offerta);
										
										$offerta=$dom->createElement("offerta");
										$offerta->setAttribute("id", 5);
										$offerta->setAttribute("idProdotto", 5);
										
										$offerta->appendChild($dom->createElement("prezzoContabile", number_format(299.95,2,".","")));
										
										$sconto=$dom->createElement("sconto");
										
										$scontoATempo=$dom->createElement("scontoATempo");
										$scontoATempo->setAttribute("percentuale", number_format(15,2,".",""));
										$scontoATempo->setAttribute("inizioApplicazione", "2024-08-01");
										$scontoATempo->setAttribute("fineApplicazione", "2024-10-01");
										
										$sconto->appendChild($scontoATempo);
										
										$offerta->appendChild($sconto);
										
										$offerta->appendChild($dom->createElement("quantitativo", 5));
										
										$root->appendChild($offerta);
										
										$offerta=$dom->createElement("offerta");
										$offerta->setAttribute("id", 6);
										$offerta->setAttribute("idProdotto", 6);
										
										$offerta->appendChild($dom->createElement("prezzoContabile", number_format(299.95,2,".","")));
										
										$sconto=$dom->createElement("sconto");
										
										$scontoFuturo=$dom->createElement("scontoFuturo");
										$scontoFuturo->setAttribute("percentuale", number_format(2.5,2,".",""));
										$scontoFuturo->setAttribute("inizioApplicazione", "2024-05-01");
										$scontoFuturo->setAttribute("fineApplicazione", "2024-09-01");
										
										$sconto->appendChild($scontoFuturo);
										
										$offerta->appendChild($sconto);
										
										$offerta->appendChild($dom->createElement("quantitativo", 20));
										
										$root->appendChild($offerta);
										
										$offerta=$dom->createElement("offerta");
										$offerta->setAttribute("id", 7);
										$offerta->setAttribute("idProdotto", 7);
										
										$offerta->appendChild($dom->createElement("prezzoContabile", number_format(224.5,2,".","")));
										
										$sconto=$dom->createElement("sconto");
										
										$scontoATempo=$dom->createElement("scontoATempo");
										$scontoATempo->setAttribute("percentuale", number_format(5,2,".",""));
										$scontoATempo->setAttribute("inizioApplicazione", "2024-05-01");
										$scontoATempo->setAttribute("fineApplicazione", "2024-09-01");
										
										$sconto->appendChild($scontoATempo);
										
										$offerta->appendChild($sconto);
										
										$offerta->appendChild($dom->createElement("quantitativo", 30));
										
										$bonus=$dom->createElement("bonus");
										
										$bonus->appendChild($dom->createElement("numeroCrediti", number_format(5,2,".","")));
										
										$offerta->appendChild($bonus);
										
										$root->appendChild($offerta);
										
										$offerta=$dom->createElement("offerta");
										$offerta->setAttribute("id", 8);
										$offerta->setAttribute("idProdotto", 8);
										
										$offerta->appendChild($dom->createElement("prezzoContabile", number_format(399.95,2,".","")));
										
										$offerta->appendChild($dom->createElement("quantitativo", 30));
										
										$root->appendChild($offerta);
										
										$dom->appendChild($root);
									
										$dom->save("../../XML/Offerte.xml");
										
										$docOfferte=new DOMDocument();
										$docOfferte->load("../../XML/Offerte.xml");
										
										if($docOfferte->schemaValidate("../../XML/Schema/Offerte.xsd")){
										
											// RECENSIONI DEGLI UTENTI
											$dom = new DOMDocument("1.0", "UTF-8");
											$dom->preserveWhiteSpace = false;
											$dom->formatOutput = true;
											
											$implementation = new DOMImplementation();
											$dom->appendChild($implementation->createDocumentType("recensioni","","Dtd/Recensioni_Prodotti.dtd"));
											
											$root=$dom->createElement("recensioni");
											$root->setAttribute("ultimoId", 3);
											
											$recensione=$dom->createElement("recensione");
											$recensione->setAttribute("id", 1);
											$recensione->setAttribute("idUtente", 2);
											$recensione->setAttribute("dataPubblicazione", "2024-06-01");
											$recensione->setAttribute("moderata", "No");
											
											$recensione->appendChild($dom->createElement("titolo", "Miglior spokon manga di sempre"));
											
											$recensione->appendChild($dom->createElement("testo", "C'è poco da dire, esistono due tipi di persone: chi ha letto Slam Dunk e chi dovrebbe leggerlo! Sicuramente il miglior spokon manga di tutti i tempi (spokon manga=manga sportivo). Slam Dunk, ci racconta la vita da liceale di Hanamichi Sakuragi, un teppistello che s’iscrive al primo anno della scuola superiore Shohoku. Hanamichi passa le sue giornate tra risse e abbordaggi malriusciti delle ragazze, motivo che lo porta ad essere continuamente deriso dai suoi compagni. Un giorno, una bella ragazza di nome Haruko Akagi, sorella di Takenori, capitano della squadra di basket del liceo Shohoku, noterà Hanamichi, o meglio il suo fisico che, a suo parere, è perfetto per un giocatore di pallacanestro. La nostra matricola non resiste alle moine senza malizia di Haruko e per conquistarla, decide di proporsi alla squadra. Hanamichi non ha mai giocato, non conosce nemmeno i fondamentali e questo lo costringerà ad allenarsi duramente e a non poter prendere parte nemmeno alle partite di allenamento. In cerca di una motivazione che lo spronasse a dare il meglio di sé, e che sembrava non essere nascosta da nessuna parte, il nostro protagonista la troverà inaspettatamente nel nuovo arrivato Kaede Rukawa, stella nascente del basket, idolatrato dalle ragazze. Questa nuova edizione presenta delle immagini extra alla fine dei capitoli ed una nuova suddivisione dei capitoli."));
											
											$valutazione=$dom->createElement("valutazione");
											
											$perLibro=$dom->createElement("perLibro");
											$perLibro->setAttribute("trama", 5);
											$perLibro->setAttribute("caratterizzazionePersonaggi", 5);
											$perLibro->setAttribute("ambientazione", 5);
											
											$valutazione->appendChild($perLibro);
											
											$recensione->appendChild($valutazione);
											
											$root->appendChild($recensione);
											
											$recensione=$dom->createElement("recensione");
											$recensione->setAttribute("id", 2);
											$recensione->setAttribute("idUtente", 3);
											$recensione->setAttribute("dataPubblicazione", "2024-06-01");
											$recensione->setAttribute("moderata", "No");
											
											$recensione->appendChild($dom->createElement("titolo", "Capolavoro"));
											
											$recensione->appendChild($dom->createElement("testo", "Koe no Katachi - La Forma della Voce è un manga ideato da Yoshitoki Oima nel 2013, sotto la supervisione della federazione giapponese per la sordità. La trama vede protagonista Shoya Ishida, un ragazzino molto vivace che cerca di vincere contro la noia facendo giochi pericolosi e prendendo in giro una sua compagna di classe sorda, Shoko Nishimiya, diventando per tutti un bullo da evitare. Infatti la povera ragazzina sorda si trasferisce in un'altra scuola, e Ishida si sente il colpevole di ciò; anni dopo i due si ritrovano, entrambi cresciuti, e Ishida decide di dover rimediare assolutamente al suo passato comportamento frequentando Nishimiya. I due troveranno parecchie difficoltà lungo il cammino. Lo sviluppo della trama è sempre più coinvolgente, non accade tutti i giorni di appassionarmi alla lettura dopo 3 o 4 capitoli, infatti inizialmente la storia presenta già qualche alto tono pronto a farti incuriosire, per poi tornare subito nel passato, per rivivere i vecchi momenti dei due protagonisti. Fase molto importante della storia, anzi sono le basi di questa storia, che poi porteranno gran parte dei guai nei capitoli successivi. La trama potrebbe sembrare forzata, una bambina affetta da un handicap può essere un aspetto indicato a commuovere in certi casi o a far pena, ma non è così, personalmente ho trovato incredibile come l'autrice riesca a farti immedesimare nella ragazza. Sette volumi sono stati più che sufficienti a farti vivere una storia drammatica, piena di alti e bassi, ma l'unica pecca del manga è che manca un po' di romanticismo, però quest'ultimo aspetto forse avrebbe forzato un po' alcuni eventi. La storia procede benissimo fino ad arrivare agli ultimi battenti, quando tutto sta per concludersi lo si può avvertire, da come procedono gli alti e bassi, e dai rapporti tra i personaggi; il finale l'ho trovato abbastanza piacevole, giusto e coerente, non poteva esserci un finale migliore."));
											
											$valutazione=$dom->createElement("valutazione");
											
											$perLibro=$dom->createElement("perLibro");
											$perLibro->setAttribute("trama", 5);
											$perLibro->setAttribute("caratterizzazionePersonaggi", 5);
											$perLibro->setAttribute("ambientazione", 5);
											
											$valutazione->appendChild($perLibro);
											
											$recensione->appendChild($valutazione);
											
											$root->appendChild($recensione);
											
											$recensione=$dom->createElement("recensione");
											$recensione->setAttribute("id", 3);
											$recensione->setAttribute("idUtente", 4);
											$recensione->setAttribute("dataPubblicazione", "2024-06-02");
											$recensione->setAttribute("moderata", "No");
											
											$recensione->appendChild($dom->createElement("titolo", "Stratosferico"));
											
											$recensione->appendChild($dom->createElement("testo", "Bloodborne è un'esperienza di gioco impeccabile. L'ambientazione gotica e il gameplay frenetico creano una tensione costante. La sfida elevata rende ogni vittoria gratificante. Il design dei nemici e dei livelli è stupefacente. Un capolavoro assoluto."));
											
											$valutazione=$dom->createElement("valutazione");
											
											$perVideogioco=$dom->createElement("perVideogioco");
											$perVideogioco->setAttribute("sceneggiatura", 5);
											$perVideogioco->setAttribute("tecnica", 5);
											$perVideogioco->setAttribute("giocabilita", 4);
											
											$valutazione->appendChild($perVideogioco);
											
											$recensione->appendChild($valutazione);
											
											$root->appendChild($recensione);
											
											$dom->appendChild($root);
											
											$dom->save("../../XML/Recensioni_Prodotti.xml");
											
											$docRecensioni=new DOMDocument();
											$docRecensioni->load("../../XML/Recensioni_Prodotti.xml");
											
											if($docRecensioni->validate()){
												
												// GENERI DEI VIDEOGIOCHI (XML)
												$sql = "SELECT ID, Denominazione FROM Generi_Videogiochi ORDER BY ID";
												$result=mysqli_query($conn, $sql);
												
												$dom = new DOMDocument("1.0", "UTF-8");
												$dom->preserveWhiteSpace = false;
												$dom->formatOutput = true;
												
												$implementation = new DOMImplementation();
												$dom->appendChild($implementation->createDocumentType("generi","","Dtd/Generi_Videogiochi.dtd"));
												
												$root=$dom->createElement("generi");
												
												while($row=mysqli_fetch_array($result)){
													
													$genere=$dom->createElement("genere");
													$genere->setAttribute("id", $row["ID"]);
													
													$denominazione=$dom->createElement("denominazione", $row["Denominazione"]);
													$genere->appendChild($denominazione);
													
													$root->appendChild($genere);			
												}
												
												$dom->appendChild($root);
												
												$dom->save("../../XML/Generi_Videogiochi.xml");
												
												$docGeneriVideogiochi=new DOMDocument();
												$docGeneriVideogiochi->load("../../XML/Generi_Videogiochi.xml");
												
												if($docGeneriVideogiochi->validate()){
													
													// SEGNALAZIONI DEI CLIENTI
													$dom = new DOMDocument("1.0", "UTF-8");
													$dom->preserveWhiteSpace = false;
													$dom->formatOutput = true;
													
													$root=$dom->createElement("segnalazioni");
													$root->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
													$root->setAttribute("xsi:noNamespaceSchemaLocation", "Schema/Segnalazioni.xsd");
													$root->setAttribute("ultimoId", 1);
													
													$segnalazione=$dom->createElement("segnalazione");
													$segnalazione->setAttribute("id", 1);
													$segnalazione->setAttribute("idSegnalato", 5);
													$segnalazione->setAttribute("idSegnalatore", 6);
													$segnalazione->setAttribute("dataOraSegnalazione", "2024-06-03 16:00:00");
													$segnalazione->setAttribute("categoria", "Linguaggio Volgare");
													$segnalazione->setAttribute("seen", "No");
													
													$segnalazione->appendChild($dom->createElement("testo", "L'utente indicato ha offeso la mia persona per via del modo con cui ha risposto ad una mia domanda."));
													
													$tipoSegnalazione=$dom->createElement("tipoSegnalazione");
													
													$perIntervento=$dom->createElement("perIntervento");
													$perIntervento->setAttribute("idDiscussione", 1);
													$perIntervento->setAttribute("idIntervento", 2);
													
													$tipoSegnalazione->appendChild($perIntervento);
													
													$segnalazione->appendChild($tipoSegnalazione);
													
													$root->appendChild($segnalazione);
													
													$dom->appendChild($root);
												
													$dom->save("../../XML/Segnalazioni.xml");
													
													$docSegnalazioni=new DOMDocument();
													$docSegnalazioni->load("../../XML/Segnalazioni.xml");
													
													if($docSegnalazioni->schemaValidate("../../XML/Schema/Segnalazioni.xsd")){
														
														// DOMANDE PIÙ FREQUENTI (FAQ)
														$dom = new DOMDocument("1.0", "UTF-8");
														$dom->preserveWhiteSpace = false;
														$dom->formatOutput = true;
														
														$root=$dom->createElement("faq");
														$root->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
														$root->setAttribute("xsi:noNamespaceSchemaLocation", "Schema/FAQ.xsd");
														$root->setAttribute("ultimoId", 1);
														
														$singolaFaq=$dom->createElement("singolaFaq");
														$singolaFaq->setAttribute("id", 1);
														
														$resocontoDiscussione=$dom->createElement("resocontoDiscussione");
														
														$discussioneDaZero=$dom->createElement("discussioneDaZero");
														
														$discussioneDaZero->appendChild($dom->createElement("titolo", "Istruzioni per la consegna"));
														
														$discussioneDaZero->appendChild($dom->createElement("descrizione", "Come posso cambiare l'indirizzo di spedizione prima di procedere con il pagamento dell'ordine?"));
														
														$resocontoDiscussione->appendChild($discussioneDaZero);
														
														$singolaFaq->appendChild($resocontoDiscussione);
														
														$interventoDiscussione=$dom->createElement("interventoDiscussione");
														
														$interventoDaZero=$dom->createElement("interventoDaZero");
														
														$interventoDaZero->appendChild($dom->createElement("testo", "Se hai bisogno di modificare l'ubicazione a cui recapitare gli articoli da acquistare, è possibile compilare il relativo modulo nella schermata di conferma dell'acquisto. Proprio per questo, non avere timore di apportare dei cambiamenti alle tue preferenze."));
														
														$interventoDiscussione->appendChild($interventoDaZero);
														
														$singolaFaq->appendChild($interventoDiscussione);
														
														$root->appendChild($singolaFaq);
														
														$dom->appendChild($root);
													
														$dom->save("../../XML/FAQ.xml");
														
														$docFaq=new DOMDocument();
														$docFaq->load("../../XML/FAQ.xml");
														
														if($docFaq->schemaValidate("../../XML/Schema/FAQ.xsd")){
															
															// DOMANDE DI ASSISTENZA PER IL RECUPERO DELLA PASSWORD (XML)
															$sql = "SELECT ID, ID_Richiedente, Data_Ora_Richiesta, Seen FROM Domande_Assistenza";
															$result=mysqli_query($conn, $sql);
															
															$dom = new DOMDocument("1.0", "UTF-8");
															$dom->preserveWhiteSpace = false;
															$dom->formatOutput = true;
															
															$root=$dom->createElement("domande");
															$root->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
															$root->setAttribute("xsi:noNamespaceSchemaLocation", "Schema/Domande_Assistenza.xsd");
															$root->setAttribute("ultimoId", mysqli_num_rows($result));
															
															while($row=mysqli_fetch_array($result)){
																
																$domanda=$dom->createElement("domanda");
																$domanda->setAttribute("id", $row["ID"]);
																$domanda->setAttribute("idRichiedente", $row["ID_Richiedente"]);
																$domanda->setAttribute("dataOraRichiesta", $row["Data_Ora_Richiesta"]);
																$domanda->setAttribute("seen", $row["Seen"]);
																
																$root->appendChild($domanda);			
															}
															
															$dom->appendChild($root);
															
															$dom->save("../../XML/Domande_Assistenza.xml");
															
															$docRichieste=new DOMDocument();
															$docRichieste->load("../../XML/Domande_Assistenza.xml");
															
															if($docRichieste->schemaValidate("../../XML/Schema/Domande_Assistenza.xsd")){
															
																// TERMINATA LA CREAZIONE DELLE VARIE STRUTTURE DATI, SI PROCEDE CON LA RIMOZIONE DI TUTTE QUELLE TABELLE RELAZIONALI CHE, DATE LE CIRCOSTANZE, RISULTANO SUPREFLUE O RIDONDANTI 
																mysqli_query($conn, "DROP TABLE IF EXISTS Categorie_Libri");
																mysqli_query($conn, "DROP TABLE IF EXISTS Piattaforme_Videogiochi");
																mysqli_query($conn, "DROP TABLE IF EXISTS Richieste_Crediti");
																mysqli_query($conn, "DROP TABLE IF EXISTS Generi_Videogiochi");
																mysqli_query($conn, "DROP TABLE IF EXISTS Domande_Assistenza");
																
																// PRIMA DI ESSERE REINDIRIZZATI VERSO LA PAGINA INIZIALE DEL SITO, OLTRE ALLA RIMOZIONE DI EVENTUALI SESSIONI RIMASTE APERTE, VERRÀ IMPOSTATO UN COOKIE IN MODO TALE CHE POSSA ESSERE MOSTRATO A SCHERMO UN MESSAGGIO INDICANTE IL BUON ESITO DELL'OPERAZIONE DI INSTALLAZIONE
																require_once("./session_destruction.php");
																
																setcookie("caricamento_Effettuato", true);
																
																header("Location: index.php");
															}
															else {
																$errore_validazione=true;
								
																unlink("../../XML/Categorie_Libri.xml");
																unlink("../../XML/Piattaforme_Videogiochi.xml");
																unlink("../../XML/Carrelli_Clienti.xml");
																unlink("../../XML/Acquisti_Clienti.xml");
																unlink("../../XML/Tariffe_Sconti.xml");
																unlink("../../XML/Riduzioni_Prezzi.xml");
																unlink("../../XML/Richieste_Crediti.xml");
																unlink("../../XML/Discussioni.xml");
																unlink("../../XML/Prodotti.xml");
																unlink("../../XML/Offerte.xml");
																unlink("../../XML/Recensioni_Prodotti.xml");
																unlink("../../XML/Generi_Videogiochi.xml");
																unlink("../../XML/Segnalazioni.xml");
																unlink("../../XML/FAQ.xml");
																unlink("../../XML/Domande_Assistenza.xml");
															}
														}
														else {
															$errore_validazione=true;
								
															unlink("../../XML/Categorie_Libri.xml");
															unlink("../../XML/Piattaforme_Videogiochi.xml");
															unlink("../../XML/Carrelli_Clienti.xml");
															unlink("../../XML/Acquisti_Clienti.xml");
															unlink("../../XML/Tariffe_Sconti.xml");
															unlink("../../XML/Riduzioni_Prezzi.xml");
															unlink("../../XML/Richieste_Crediti.xml");
															unlink("../../XML/Discussioni.xml");
															unlink("../../XML/Prodotti.xml");
															unlink("../../XML/Offerte.xml");
															unlink("../../XML/Recensioni_Prodotti.xml");
															unlink("../../XML/Generi_Videogiochi.xml");
															unlink("../../XML/Segnalazioni.xml");
															unlink("../../XML/FAQ.xml");
														}
													}
													else {
														$errore_validazione=true;
								
														unlink("../../XML/Categorie_Libri.xml");
														unlink("../../XML/Piattaforme_Videogiochi.xml");
														unlink("../../XML/Carrelli_Clienti.xml");
														unlink("../../XML/Acquisti_Clienti.xml");
														unlink("../../XML/Tariffe_Sconti.xml");
														unlink("../../XML/Riduzioni_Prezzi.xml");
														unlink("../../XML/Richieste_Crediti.xml");
														unlink("../../XML/Discussioni.xml");
														unlink("../../XML/Prodotti.xml");
														unlink("../../XML/Offerte.xml");
														unlink("../../XML/Recensioni_Prodotti.xml");
														unlink("../../XML/Generi_Videogiochi.xml");
														unlink("../../XML/Segnalazioni.xml");
													}
												}
											}
											else {
												$errore_validazione=true;
								
												unlink("../../XML/Categorie_Libri.xml");
												unlink("../../XML/Piattaforme_Videogiochi.xml");
												unlink("../../XML/Carrelli_Clienti.xml");
												unlink("../../XML/Acquisti_Clienti.xml");
												unlink("../../XML/Tariffe_Sconti.xml");
												unlink("../../XML/Riduzioni_Prezzi.xml");
												unlink("../../XML/Richieste_Crediti.xml");
												unlink("../../XML/Discussioni.xml");
												unlink("../../XML/Prodotti.xml");
												unlink("../../XML/Offerte.xml");
												unlink("../../XML/Recensioni_Prodotti.xml");
												unlink("../../XML/Generi_Videogiochi.xml");
											}
										}
										else {
											$errore_validazione=true;
								
											unlink("../../XML/Categorie_Libri.xml");
											unlink("../../XML/Piattaforme_Videogiochi.xml");
											unlink("../../XML/Carrelli_Clienti.xml");
											unlink("../../XML/Acquisti_Clienti.xml");
											unlink("../../XML/Tariffe_Sconti.xml");
											unlink("../../XML/Riduzioni_Prezzi.xml");
											unlink("../../XML/Richieste_Crediti.xml");
											unlink("../../XML/Discussioni.xml");
											unlink("../../XML/Prodotti.xml");
											unlink("../../XML/Offerte.xml");
										}
									}
									else {
										$errore_validazione=true;
								
										unlink("../../XML/Categorie_Libri.xml");
										unlink("../../XML/Piattaforme_Videogiochi.xml");
										unlink("../../XML/Carrelli_Clienti.xml");
										unlink("../../XML/Acquisti_Clienti.xml");
										unlink("../../XML/Tariffe_Sconti.xml");
										unlink("../../XML/Riduzioni_Prezzi.xml");
										unlink("../../XML/Richieste_Crediti.xml");
										unlink("../../XML/Discussioni.xml");
										unlink("../../XML/Prodotti.xml");
									}
								}
								else {
									$errore_validazione=true;
								
									unlink("../../XML/Categorie_Libri.xml");
									unlink("../../XML/Piattaforme_Videogiochi.xml");
									unlink("../../XML/Carrelli_Clienti.xml");
									unlink("../../XML/Acquisti_Clienti.xml");
									unlink("../../XML/Tariffe_Sconti.xml");
									unlink("../../XML/Riduzioni_Prezzi.xml");
									unlink("../../XML/Richieste_Crediti.xml");
									unlink("../../XML/Discussioni.xml");
								}
							}
							else {
								$errore_validazione=true;
								
								unlink("../../XML/Categorie_Libri.xml");
								unlink("../../XML/Piattaforme_Videogiochi.xml");
								unlink("../../XML/Carrelli_Clienti.xml");
								unlink("../../XML/Acquisti_Clienti.xml");
								unlink("../../XML/Tariffe_Sconti.xml");
								unlink("../../XML/Riduzioni_Prezzi.xml");
								unlink("../../XML/Richieste_Crediti.xml");
							}
						}
						else {
							$errore_validazione=true;
							
							unlink("../../XML/Categorie_Libri.xml");
							unlink("../../XML/Piattaforme_Videogiochi.xml");
							unlink("../../XML/Carrelli_Clienti.xml");
							unlink("../../XML/Acquisti_Clienti.xml");
							unlink("../../XML/Tariffe_Sconti.xml");
							unlink("../../XML/Riduzioni_Prezzi.xml");
						}
					}
					else {
						$errore_validazione=true;
						
						unlink("../../XML/Categorie_Libri.xml");
						unlink("../../XML/Piattaforme_Videogiochi.xml");
						unlink("../../XML/Carrelli_Clienti.xml");
						unlink("../../XML/Acquisti_Clienti.xml");
						unlink("../../XML/Tariffe_Sconti.xml");
					}
				}
				else {
					$errore_validazione=true;
					
					unlink("../../XML/Categorie_Libri.xml");
					unlink("../../XML/Piattaforme_Videogiochi.xml");
					unlink("../../XML/Carrelli_Clienti.xml");
					unlink("../../XML/Acquisti_Clienti.xml");
				}
			}
			else {
				$errore_validazione=true;
				
				unlink("../../XML/Categorie_Libri.xml");
				unlink("../../XML/Piattaforme_Videogiochi.xml");
				unlink("../../XML/Carrelli_Clienti.xml");
			}
		}
		else {
			$errore_validazione=true;
			
			unlink("../../XML/Categorie_Libri.xml");
			unlink("../../XML/Piattaforme_Videogiochi.xml");
		}
	}
	else {
		$errore_validazione=true;
		
		unlink("../../XML/Categorie_Libri.xml");
	}
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>LEV: Libri &amp; Videogiochi</title>
		<link rel="icon" href="../../Immagini/Icona_LEV.png" />
		<link rel="stylesheet" href="../../Stili CSS/style_common.css" type="text/css" />
	</head>
	<body>
		<?php
			// LA MANCATA VALIDAZIONE RAPPRESENTA UN FATTORE DECISAMENTE NEGATIVO PER IL CORRETTO FUNZIONAMENTO DELLA PIATTAFORMA
			if(isset($errore_validazione) && $errore_validazione) {
					
					// IN OCCASIONE DEL POSSIBILE RICARICAMENTO DELLA PAGINA, SI PROCEDE CON LA RIMOZIONE DEL FLAG AL SOLO SCOPO DI NON RIPROPORRE LA MEDESIMA NOTIFCA 
					$errore_validazione=false;
					
					echo "<div class=\"error_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
					echo "\t\t\t\t\t<p>QUALCOSA &Egrave; ANDATO STORTO DURANTE LA CREAZIONE DEI FILE XML...</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
			}
		?>
	</body>
</html>