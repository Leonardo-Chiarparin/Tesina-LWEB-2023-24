<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO TUTTI I DETTAGLI INERENTI ALL'ORDINE CHE IL CLIENTE INTENDE ACQUISTARE

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
		header("Location: riepilogo_carrello.php");
	}
	
	// COME PER I FILE XML, SI PROCEDE CON UN'INTERROGAZIONE ALLA BASE DI DATI SUDDETTA AL FINE DI REPERIRE I DATI RELATIVI ALL'UTENTE COINVOLTO  
	$sql="SELECT Nome, Cognome, Num_Telefono, Indirizzo, Citta, CAP FROM $tab WHERE ID=".$_SESSION["id_Utente"];
	$result=mysqli_query($conn, $sql);
	
	// NEL CASO IN CUI CI SIANO DELLE CORRISPONDENZE (IN REALTÀ UNA SOLTANTO), SI PROCEDE CON IL SALVATAGGIO DI TUTTI GLI ELEMENTI DI CUI SI È FATTA RICHIESTA
	while($row=mysqli_fetch_array($result))
	{
		$nome=$row["Nome"];
		$cognome=$row["Cognome"];
		$num_telefono=$row["Num_Telefono"];
		$indirizzo=$row["Indirizzo"];
		$citta=$row["Citta"];
		$cap=$row["CAP"];
	}
	
	// PRIMA DI PROCEDERE CON LA COMPOSIZIONE DELLA PAGINA, È NECESSARIO VALUTARE IL NUMERO DI VOCI CHE COMPONGONO IL CARRELLO DELL'UTENTE INTERESSATO. INFATTI, QUALORA SIANO GIÀ STATE CONSIDERATE, SI DOVRÀ STAMPARE UN MESSAGGIO CHE NOTIFICHERÀ L'ASSENZA ELEMENTI AL SUO INTERNO
	require_once("./calcolo_offerte_carrello.php");
	
	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_acquisti.php");
	require_once("./apertura_file_carrelli.php");
	require_once("./apertura_file_prodotti.php");
	require_once("./apertura_file_piattaforme_videogiochi.php");
	require_once("./apertura_file_riduzioni.php");
	require_once("./apertura_file_tariffe.php");
	
	// NEL CASO IN CUI CI SIANO DELLE OFFERTE ALL'INTERNO DEL CARRELLO, SARÀ NECESSARIO DETERMINARNE IL SUBTOTALE, OVVERO LA SOMMA DEI PREZZI DELLE SINGOLE PROPOSTE DI VENDITA A CUI SONO STATI APPLICATI I RELATIVI SCONTI "INTERNI", E IL TOTALE, IL QUALE COINCIDERÀ CON IL VALORE DEL PRECEDENTE ELEMENTO AL NETTO DI ULTERIORI AGEVOLAZIONI PERSONALI (SCONTI FEDELTÀ, PROMOZIONI, ETC...)
	if($num_prodotti_carrello!=0) {
	
		// CONTRARIMENTE ALLA SCHERMATA DI RIEPILOGO INERENTE AL CONTENUTO DEL CARRELLO, NON SI HA PIÙ LA NECESSITÀ DI DISCRIMINARE L'ENTITÀ DA CUI REPERIRE LE INFORMAZIONI DA PRESENTARE, IN QUANTO, DURANTE L'AUTENTICAZIONE, IL CARRELLO D'INTERESSE, PRESENTE ALL'INTERNO DEL RELATIVO FILE XML, È STATO INTEGRATO CON QUANTO RIPORTATO NEL COOKIE RAFFIGURANTE LA PRECEDENTE ENTITÀ MA PER I VISITATORI
		$subtotale=0.00;
		$sconto_generale=0.00;
		$messaggio_sconto="|";
		$totale=0.00;
			
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
		
		// UNA VOLTA OTTENUTO IL PRECEDENTE RISULTATO, SARÀ NECESSARIO ANDARE A CONSIDERARE LE EVENTUALI RIDUZIONI DI PREZZO A CUI IL CLIENTE HA ATTUALMENTE ACCESSO. A TAL FINE, BISOGNERÀ SCANSIONARE IL RELATIVO FILE XML ALLA RICERCA DELL'ENTITÀ CHE LE RAPPRESENTA
		for($i=0; $i<$riduzioni->length; $i++) {
			$riduzione=$riduzioni->item($i);
			
			if($riduzione->getAttribute("idCliente")==$_SESSION["id_Utente"])
				break;
		}
		
		// IN PARTICOLARE, LE POSSIBILI AGEVOLAZIONI PERSONALI A CUI IL SOGGETTO D'INTERESSE PUÒ AVERE ACCESSO RISULTANO ESSERE:
		// 1) SCONTO A SOGLIA, PER IL QUALE È PREVISTA L'APPLICAZIONE DI UNA BASE PERCENTUALE (FISSA) REPERIBILE DAL DOCUMENTO INERENTE ALLE TARIFFE
		if($riduzione->getElementsByTagName("aSoglia")->item(0)->getAttribute("fruibile")==1) {
			$sconto_generale=number_format($sconto_generale + floatval($rootTariffe->getElementsByTagName("tariffaASoglia")->item(0)->getAttribute("basePercentuale")), 2,".","");
			
			$messaggio_sconto=$messaggio_sconto." a Soglia |";
		}
		
		// 2) SCONTO FEDELTÀ ELITÈ, ***
		if($riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("fruibile")==1 && $riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("esercitabile")==1) {
			$sconto_generale=number_format($sconto_generale + floatval($rootTariffe->getElementsByTagName("tariffaFedeltaElite")->item(0)->getAttribute("basePercentuale")), 2,".","");
		
			$messaggio_sconto=$messaggio_sconto." Fedelt&agrave; Elit&egrave; |";
		}
		
		// 3) PROMOZIONI DERIVANTI DALL'ACQUISTO PRECEDENTE, PER LE QUALI È PREVISTA L'APPLICAZIONE DI UNA BASE PERCENTUALE VARIABILE
		$sconto_generale=number_format($sconto_generale + floatval($riduzione->getElementsByTagName("acquistoPromozionale")->item(0)->firstChild->textContent), 2,".","");
		
		if(floatval($riduzione->getElementsByTagName("acquistoPromozionale")->item(0)->firstChild->textContent)!=0)
			$messaggio_sconto=$messaggio_sconto." Promozionale |";
		
		// 4) SCONTO PER VIP, ***
		if($riduzione->getElementsByTagName("perVIP")->item(0)->getAttribute("fruibile")==1) {
			$sconto_generale=number_format($sconto_generale + floatval($rootTariffe->getElementsByTagName("tariffaPerVIP")->item(0)->getAttribute("basePercentuale")), 2,".","");
		
			$messaggio_sconto=$messaggio_sconto." per VIP |";
		}
		
		// 5) SCONTO DI ANZIANITÀ, PER IL QUALE È PREVISTA L'APPLICAZIONE DI UNA BASE PERCENTUALE VARIABILE
		if($riduzione->getElementsByTagName("diAnzianita")->item(0)->getAttribute("fruibile")==1) {
			$sconto_generale=number_format($sconto_generale + floatval($riduzione->getElementsByTagName("diAnzianita")->item(0)->firstChild->textContent), 2,".","");
		
			$messaggio_sconto=$messaggio_sconto." di Anzianit&agrave; |";
		}
		
		// PRIMA DI PROCEDERE, BISOGNERÀ FARE IN MODO CHE LO SCONTO GENERALE SIA AL PIÙ PARI AL 100 %
		if($sconto_generale>100)
			$sconto_generale=number_format(100, 2,".","");
		
		// GIUNTI A QUESTO PUNTO, È POSSIBILE CALCOLARE IL TOTALE EFFETTIVO DELL'ORDINE
		$totale=number_format($subtotale - ($subtotale*($sconto_generale/100)), 2,".","");
		
	}
	else {
		// D'ALTRO CANTO, BISOGNERÀ IMPEDIRE LA VISUALIZZAZIONE DELLA PAGINA DA PARTE DEL CLIENTE INTERESSATO
		header("Location: riepilogo_carrello.php");
	}
	
	// COME RIPORTATO PIÙ VOLTE, LA CONSEGNA DEGLI ARTICOLI CHE COMPONGONO UN CERTO ORDINE AVVERRÀ ENTRO LE 24 O LE 48 ORE SUCCESSIVE ALL'ACQUISTO. PROPRIO PER QUESTO, E AL FINE DI GARANTIRE UNA CERTA DINAMICITÀ ALLA PAGINA, ABBIAMO DECISO DI STAMPARE LA DATA SUCCESSIVA A QUELLA CORRENTE OPPURE QUELLA SUBITO DOPO QUEST'ULTIMA
	// IN CASO DI CONFERMA DA PARTE DELL'UTENTE INTERESSATO, UNA DELLE PRECEDENTI DUE SCELTE VERRÀ MEMORIZZATA COME PARAMETRO IN CORRISPONDENZA DELL'ENTITÀ CHE, ALL'INTERNO DEL RELATIVO FILE XML, RAPPRESENTERÀ L'ACQUISTO EFFETTUATO
	// N.B.: AL FINE DI PRODURRE UN RISULTATO ARBITRARIO E IL PIÙ VERITIERO POSSIBILE, È STATO RITENUTO OPPORTUNO UTILIZZARE IL METODO mt_rand(...), IL CUI VALORE SARÀ MEMORIZZATO ALL'INTERNO DI UN COOKIE CHE, A SUA VOLTA E IN BASE A DEI CONFRONTI, SARÀ AGGIORNATO CON I RISULTATI PIÙ RECENTI
	if(isset($_COOKIE["data_Consegna"])) {
		// SE LA DATA CONTENUTA NEL COOKIE DI CUI SOPRA È STATA RAGGIUNTA O SUPERATA, BISOGNERÀ AGGIORARNE IL CONTENUTO APPLICANDO NUOVAMENTE L'ISTRUZIONE UTILIZZATA PER CREARLO LA PRIMA VOLTA
		if(strtotime(date("Y-m-d"))>=strtotime($_COOKIE["data_Consegna"])) {
			$data_Consegna=date("Y-m-d", strtotime("+".mt_rand(1,2)." Days"));
			setcookie("data_Consegna", $data_Consegna);
		}
		else
			$data_Consegna=$_COOKIE["data_Consegna"];
	}
	else {
		$data_Consegna=date("Y-m-d", strtotime("+".mt_rand(1,2)." Days"));
		setcookie("data_Consegna", $data_Consegna);
	}
	
	// UNA VOLTA CONFERMATE LE PROPRIE SCELTE, BISOGNA PROCEDERE CON I CONTROLLI PER VALUTARE LA CORRETTEZZA E L'INTEGRITÀ DEI VARI ELEMENTI DI INTERESSE   
	if(isset($_POST["confirm"])) {
		
		// IL PRIMO PASSO CONSISTE NEL VALUTARE SE IL CLIENTE COINVOLTO HA UN NUMERO DI CREDITI SUFFICIENTI PER PROCEDERE CON L'ACQUISTO DEI VARI ARTICOLI
		$sql="SELECT Portafoglio_Crediti FROM $tab WHERE ID=".$_SESSION["id_Utente"];
		$result=mysqli_query($conn, $sql);
		
		while($row=mysqli_fetch_array($result)) {
			$portafoglio_crediti=$row["Portafoglio_Crediti"];
		}
		
		// NEL CASO IN CUI IL SALDO NON SIA IN GRADO DI COPRIRE LA SPESA, L'UTENTE VERRÀ REINDIRIZZATO VERSO LA SCHERMATA IN CUI POTRÀ DECIDERE SE RICARICARE IL PROPRIO PORTAFOGLIO O MENO
		if($totale>$portafoglio_crediti) {
			
			// ***
			$_SESSION["credito_Insufficiente"]=true;
			
			header("Location: saldo_clienti.php");
		}
		else {
			// DATO L'INTENTO DI VOLER CONFRONTARE L'ESITO DI UNA DETERMINATA QUERY, SARÀ NECESSARIO PREDISPORRE IL TUTTO ALL'INTERNO DI UN COSTRUTTO try ... catch ... AL FINE DI CATTURARE L'EVENTUALE ECCEZIONE E NOTIFICARE L'ACCADUTO ALL'UTENTE IN OGGETTO
			// INFATTI, UN POSSIBILE FALLIMENTO POTREBBE DIPENDERE DAL SUPERAMENTO DEL LIMITE DI CARATTERI CHE POSSONO ESSERE INSERITI ALL'INTERNO DI UN CAMPO DELLA TABELLA RELAZIONALE COINVOLTA 
			try {
				// SE NON È STATA EVIDENZIATA ALCUNA SORTA DI PROBLEMATICA, È POSSIBILE EFFETTUARE L'ADEGUAMENTO DEI DATI ALL'INTERNO DELLA BASE DI DATI
				$sql="UPDATE $tab SET Portafoglio_Crediti=".$portafoglio_crediti."-".$totale." WHERE ID=".$_SESSION["id_Utente"];
				
				// COME ACCENNATO, PRIMA DI CONCLUDERE L'OPERAZIONE BISOGNERÀ VALUTARE L'ESITO DELL'ESECUZIONE INERENTE AL PRECEDENTE COMANDO SQL
				if(mysqli_query($conn, $sql)){
					
					// PER DI PIÙ, OLTRE ALLA RIMOZIONE DEI VARI ELEMENTI DAL CARRELLO E ALLA LORO REGISTRAZIONE NEL FILE CONTENENTE GLI ACQUISTI DEL CLIENTE, SARÀ NECESSARIO AGGIORNARE LE INFORMAZIONI INERENTI ALLO STATO DELLE RIDUZIONI DI PREZZO COINVOLTE
					// IN PARTICOLARE, SE È STATO UTILIZZATO UNO SCONTO A SOGLIA O FEDELTÀ ELITÈ, BISOGNERÀ CONSIDERARE L'AMMONTARE DEI CREDITI SPESI FINORA, I QUALI SARANNO PARAMETRIZZATI E FORMATTATI A SECONDA DEL NUMERO DI SUPERAMENTI RAGGIUNTI, PER VALUTARE SE, A SEGUITO DELL'OPERAZIONE E IN BASE AI VALORI PRESENTATI DALLE RELATIVE TARIFFE, I PRECEDENTI SARANNO ACCESSIBILI O MENO
					// 1) SCONTO A SOGLIA
					if($riduzione->getElementsByTagName("aSoglia")->item(0)->getAttribute("fruibile")==1)
						$riduzione->getElementsByTagName("aSoglia")->item(0)->setAttribute("fruibile", 0);
					
					// NEL CASO IN CUI SIA STATO SUPERATO O EGUAGLIATO UNO DEI MULTIPLI SUCCESSIVI DELLA SOGLIA IMPOSTA, SARÀ NECESSARIO ADEGUARE TUTTI GLI ASPETTI CHE RIGUARDANO LA RIDUZIONI DI INTERESSE, OVVERO IL NUMERO DI CREDITI SPESI, DI SUPERAMENTI RAGGIUNTI E LA FRUIBILITÀ DELLA PERCENTUALE DI SCONTO 
					if($totale+floatval($riduzione->getElementsByTagName("aSoglia")->item(0)->getAttribute("creditiSpesi"))-(intval($riduzione->getElementsByTagName("aSoglia")->item(0)->getAttribute("superamenti"))*intval($rootTariffe->getElementsByTagName("tariffaASoglia")->item(0)->getAttribute("soglia")))>=intval($rootTariffe->getElementsByTagName("tariffaASoglia")->item(0)->getAttribute("soglia"))) {
						$riduzione->getElementsByTagName("aSoglia")->item(0)->setAttribute("creditiSpesi", number_format($totale+floatval($riduzione->getElementsByTagName("aSoglia")->item(0)->getAttribute("creditiSpesi")), 2, ".", ""));
						$riduzione->getElementsByTagName("aSoglia")->item(0)->setAttribute("superamenti", intval(floatval($riduzione->getElementsByTagName("aSoglia")->item(0)->getAttribute("creditiSpesi"))/intval($rootTariffe->getElementsByTagName("tariffaASoglia")->item(0)->getAttribute("soglia"))));
						$riduzione->getElementsByTagName("aSoglia")->item(0)->setAttribute("fruibile", 1);
					}
					else {
						$riduzione->getElementsByTagName("aSoglia")->item(0)->setAttribute("creditiSpesi", number_format($totale+floatval($riduzione->getElementsByTagName("aSoglia")->item(0)->getAttribute("creditiSpesi")), 2,".",""));
					}
					
					// 2) SCONTO FEDELTÀ ELITÈ
					if($riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("fruibile")==1 && $riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("esercitabile")==1)
						$riduzione->getElementsByTagName("fedeltaElite")->item(0)->setAttribute("esercitabile", 0);
					
					// ***
					if(($totale+floatval($riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("creditiSpesi")))>=intval($rootTariffe->getElementsByTagName("tariffaFedeltaElite")->item(0)->getAttribute("soglia")) && $riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("esercitabile")==1) {
						$riduzione->getElementsByTagName("fedeltaElite")->item(0)->setAttribute("creditiSpesi", number_format($totale+floatval($riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("creditiSpesi")), 2, ".", ""));
						$riduzione->getElementsByTagName("fedeltaElite")->item(0)->setAttribute("fruibile", 1);
					}
					else {
						// MALGRADO POSSA ESSERE UTILIZZATA UNA VOLTA SOLTANTO NEL CORSO DELL'ANNO, ABBIAMO DECISO DI CONTINUARE A TENERE TRACCIA DEL NUMERO DI CREDITI SPESI DAL CLIENTE
						$riduzione->getElementsByTagName("fedeltaElite")->item(0)->setAttribute("creditiSpesi", number_format($totale+floatval($riduzione->getElementsByTagName("fedeltaElite")->item(0)->getAttribute("creditiSpesi")), 2,".",""));
					}
					
					// 3) PROMOZIONI, CREDITI BONUS E ACQUISTI
					// PER POTERLI CONSIDERARE CORRETTAMENTE, SARÀ POSSIBILE SCANSIONARE IL CONTENUTO DEL CARRELLO, IL QUALE SUBIRÀ ANCHE LA RIMOZIONE DI TUTTE LE PROPOSTE DI VENDITA CHE SONO CONTENUTE AL SUO INTERNO. INOLTRE, SI PROCEDERÀ CON LA CREAZIONE E L'AGGIORNAMENTO DELLE VARIE COMPONENTI CHE RAPPRESENTERANNO L'ORDINE ALL'INTERNO DEL RELATIVO FILE XML
					$promozioni=0.00;
					$crediti_bonus=0.00;
					
					for($i=0; $i<$acquisti->length; $i++) {
						$acquistiPerCliente=$acquisti->item($i);
						
						if($acquistiPerCliente->getAttribute("idCliente")==$_SESSION["id_Utente"])
							break;
						
					}
					
					// LA RAPPRESENTAZIONE DI UN ACQUISTO È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUEST'ULTIMO. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI DI INTERESSE
					$acquistiPerCliente->setAttribute("ultimoIdPerAcquisto", $acquistiPerCliente->getAttribute("ultimoIdPerAcquisto")+1);
						
					$acquistoPerCliente=$docAcquisti->createElement("acquistoPerCliente");
					$acquistoPerCliente->setAttribute("id", $acquistiPerCliente->getAttribute("ultimoIdPerAcquisto"));
					$acquistoPerCliente->setAttribute("indirizzoConsegna", $indirizzo);
					$acquistoPerCliente->setAttribute("cittaConsegna", $citta);
					$acquistoPerCliente->setAttribute("capConsegna", $cap);
					$acquistoPerCliente->setAttribute("dataAcquisto", date("Y-m-d"));
					$acquistoPerCliente->setAttribute("dataConsegna", $data_Consegna);
					$acquistoPerCliente->setAttribute("prezzoTotale", number_format($totale,2,".",""));
					$acquistoPerCliente->setAttribute("scontoGenerale", number_format($sconto_generale, 2,".",""));
					
					// PER OGNI ELEMENTO ATTUALMENTE PRESENTE NEL CARRELLO, OLTRE A TENERE TRACCIA DELLE VARIE PROMOZIONI E BONUS, SI DOVRÀ EFFETTUARE LA COPIA DELL'ENTITÀ CHE LO RAPPRESENTA
					for($i=0; $i<$carrello->getElementsByTagName("offerta")->length; $i++) {
						$offerta=$carrello->getElementsByTagName("offerta")->item($i);
						
						if($offerta->getElementsByTagName("scontoFuturo")->length!=0) {
							$promozioni=number_format($promozioni+floatval($offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale")), 2,".","");
						}
						
						if($offerta->getElementsByTagName("bonus")->length!=0) {
							$crediti_bonus=number_format($crediti_bonus+floatval($offerta->getElementsByTagName("numeroCrediti")->item(0)->textContent), 2,".","");
						}
						
						// PRIMA DI RIMUOVERE L'OFFERTA INTERESSATA, BISOGNERÀ PROCEDERE CON SALVATAGGIO DELLA STESSA ALL'INTERNO DEL DOCUMENTO CONTENTE GLI ACQUISTI DI UN CERTO CLIENTE
						$nuova_offerta=$docAcquisti->createElement("offerta");
						$nuova_offerta->setAttribute("id", $offerta->getAttribute("id"));
						$nuova_offerta->setAttribute("idProdotto", $offerta->getAttribute("idProdotto"));
						
						$nuova_offerta->appendChild($docAcquisti->createElement("prezzoContabile",$offerta->firstChild->textContent));
						
						if($offerta->getElementsByTagName("sconto")->length!=0) {
						
							$nuovo_sconto=$docAcquisti->createElement("sconto");
							
							if($offerta->getElementsByTagName("scontoATempo")->length!=0) {
								$scontoATempo=$docAcquisti->createElement("scontoATempo");
								$scontoATempo->setAttribute("percentuale", $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("percentuale"));
								$scontoATempo->setAttribute("inizioApplicazione", $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("inizioApplicazione"));
								$scontoATempo->setAttribute("fineApplicazione", $offerta->getElementsByTagName("scontoATempo")->item(0)->getAttribute("fineApplicazione"));
								$nuovo_sconto->appendChild($scontoATempo);
							}
							else {
								$scontoFuturo=$docAcquisti->createElement("scontoFuturo");
								$scontoFuturo->setAttribute("percentuale", $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("percentuale"));
								$scontoFuturo->setAttribute("inizioApplicazione", $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("inizioApplicazione"));
								$scontoFuturo->setAttribute("fineApplicazione", $offerta->getElementsByTagName("scontoFuturo")->item(0)->getAttribute("fineApplicazione"));
								$nuovo_sconto->appendChild($scontoFuturo);
							}
							
							$nuova_offerta->appendChild($nuovo_sconto);
							
						}
						
						$nuova_offerta->appendChild($docAcquisti->createElement("quantitativo",$offerta->getElementsByTagName("quantitativo")->item(0)->textContent));
						
						if($offerta->getElementsByTagName("bonus")->length!=0) {
						
							$nuovo_bonus=$docAcquisti->createElement("bonus");
							
							$nuovo_bonus->appendChild($docAcquisti->createElement("numeroCrediti", $offerta->getElementsByTagName("bonus")->item(0)->firstChild->textContent));
							
							$nuova_offerta->appendChild($nuovo_bonus);
							
						}
						
						$acquistoPerCliente->appendChild($nuova_offerta);
						
					}
					
					$acquistiPerCliente->appendChild($acquistoPerCliente);
					
					// AL FINE DI RIMUOVERE TUTTE LE OFFERTE ATTUALMENTE PRESENTI ALL'INTERNO DEL CARRELLO, SARÀ NECESSARIO RIMPIAZZARNE L'ENTITÀ CHE LO RAPPRESENTA
					$nuovo_carrello=$docCarrelli->createElement("carrello");
					$nuovo_carrello->setAttribute("idCliente", $carrello->getAttribute("idCliente"));
					
					$rootCarrelli->replaceChild($nuovo_carrello, $carrello);
					
					// PER QUANTO CONCERNE I BONUS DI CUI SOPRA, ABBIAMO DECISO DI ASSEGNARE, COME SE FOSSE UNA PROMOZIONE, UN'ULTERIORE PERCENTUALE DI SCONTO PARI AL 3.00% QUALORA NON SIA POSSIBILE ACCREDITARLI AI VARI CLIENTI A CAUSA DEL RAGGIUNGIMENTO DEL LIMITE DI 10000 UNITÀ IMPOSTO SUL LORO PORTAFOGLIO 
					// IN OGNI CASO, LE PRECEDENTI PROMOZIONI VERRANNO SOSTITUITE IN FAVORE DI QUELLE PIÙ RECENTI, IN QUANTO L'UTENTE, TRAMITE L'ACQUISTO IN ESAME, HA GIÀ AVUTO L'OCCASIONE DI USUFRUIRNE
					if(($portafoglio_crediti-$totale)+$crediti_bonus>10000) {
						
						$sql="UPDATE $tab SET Portafoglio_Crediti=10000 WHERE ID=".$_SESSION["id_Utente"];
						mysqli_query($conn, $sql);
						
						$nuova_percentuale=$docRiduzioni->createElement("percentuale", number_format($promozioni+3.00,2,".",""));
						
						$riduzione->getElementsByTagName("acquistoPromozionale")->item(0)->replaceChild($nuova_percentuale, $riduzione->getElementsByTagName("acquistoPromozionale")->item(0)->firstChild);
						
					}
					else {
						$sql="UPDATE $tab SET Portafoglio_Crediti=Portafoglio_Crediti+".$crediti_bonus." WHERE ID=".$_SESSION["id_Utente"];
						mysqli_query($conn, $sql);
						
						$nuova_percentuale=$docRiduzioni->createElement("percentuale", number_format($promozioni,2,".",""));
						
						$riduzione->getElementsByTagName("acquistoPromozionale")->item(0)->replaceChild($nuova_percentuale, $riduzione->getElementsByTagName("acquistoPromozionale")->item(0)->firstChild);
					}
					
					// DAL MOMENTO CHE DOCUMENTO INTERESSATO SI RIFERISCE AD UNA STRUTTURA DESCRITTA TRAMITE DTD, È NECESSARIO SALVARNE PREVENTIVAMENTE IL CONTENUTO E IN SEGUITO VALUTARNE LA CORRETTEZZA TRAMITE IL METODO validate() 
					$docRiduzioni->preserveWhiteSpace = false;
					$docRiduzioni->formatOutput = true;
					$docRiduzioni->save("../../XML/Riduzioni_Prezzi.xml");
					
					$dom = new DOMDocument;
					$dom->load("../../XML/Riduzioni_Prezzi.xml");
					
					// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA COMPOSIZIONE DEI DOCUMENTI APPENA AGGIORNATI IN RELAZIONE A QUANTO ESPOSTO NELLE RELATIVE GRAMMATICHE DTD E NEI RELATIVI SCHEMA
					if(!($dom->validate() && $docAcquisti->schemaValidate("../../XML/Schema/Acquisti_Clienti.xsd") && $docCarrelli->schemaValidate("../../XML/Schema/Carrelli_Clienti.xsd"))){
						// ***
						setcookie("errore_Validazione", true);
						
						header("Location: index.php");
					}
					
					// PER UNA STAMPA OTTIMALE, SONO STATI APPLICATI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
					$docAcquisti->preserveWhiteSpace = false;
					$docAcquisti->formatOutput = true;
					$docAcquisti->save("../../XML/Acquisti_Clienti.xml");
					
					$docCarrelli->preserveWhiteSpace = false;
					$docCarrelli->formatOutput = true;
					$docCarrelli->save("../../XML/Carrelli_Clienti.xml");
					
					// PRIMA DI ESSERE REIDERIZZATI, SI PREDISPONE UN COOKIE CHE SARÀ USATO COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
					setcookie("modifica_Effettuata", true);
					
					header("Location: index.php");
					
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
			if(isset($_COOKIE["modifica_Effettuata"])) {
				
				// IN OCCASIONE DEL POSSIBILE RICARICAMENTO DELLA PAGINA, SI PROCEDE CON LA RIMOZIONE DEL FLAG AL SOLO SCOPO DI NON RIPROPORRE LA MEDESIMA NOTIFCA 
				setcookie("modifica_Effettuata", "", time()-60);
				
				echo "<div class=\"confirm_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/check-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"con\">OTTIMO!</p>\n";
				echo "\t\t\t\t\t<p>OPERAZIONE EFFETTUATA CON SUCCESSO!</p>\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t</div>\n";
				echo "\t\t</div>\n";
			}
			else {
				if(isset($errore_query) && $errore_query) {
					
					// ***
					$errore_query=false;
					
					echo "<div class=\"error_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
					echo "\t\t\t\t\t<p>NON &Egrave; STATO POSSIBILE COMPLETARE L'ACQUISTO DELL'ORDINE...</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
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
									<img src="../../Immagini/cash-register-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Procedi con la conferma del tuo ordine!</h2>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<div class="campo_riferimenti_temporali">
									<div class="container_campo_riferimenti_temporali">
										<span class="riferimento_temporale">
											Acquisto: <?php  echo date("d/m/Y"); ?>
										</span>
										<span class="riferimento_temporale">
											Consegna: <?php echo date_format(date_create($data_Consegna), "d/m/Y"); ?>
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
																	<li><span><?php echo $indirizzo; ?></span></li>
																	<li><span><?php echo $citta.", ".$cap; ?></span></li>
																	<li><span><?php echo $num_telefono; ?></span></li>
																	<li style="margin-top: 0.3175em; display: flex; justify-content: center;"><a href="modifica_domicilio_consegna.php" class="container_pulsante_domicilio">Modifica!</a></li>
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
																	<li><span>Contenuto</span> <span><?php echo $num_prodotti_carrello." Art."; ?></span></li>
																	<li><span>Subtotale</span> <span><?php echo number_format($subtotale, 2,".","")." Cr."; ?></span></li>
																	<?php
																		if($sconto_generale!=0)
																			echo "<li><span>Sconto <span style=\"color: rgb(217, 118, 64); cursor: pointer;\" title=\"".$messaggio_sconto."\">*</span></span> <span>".number_format($sconto_generale, 2,".","")." %</span></li>\n";
																		else
																			echo "<li><span>Sconto </span> <span>".number_format($sconto_generale, 2,".","")." %</span></li>\n";
																	?>
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
													echo "\t\t\t\t\t\t\t\t\t\t\t<table>\n";
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
									<form action="index.php" method="post">
										<p>
											<button type="submit" class="container_pulsante back" style="margin-left: 1.5em; margin-right: 1.5em; padding: 0em;">Annulla!</button>
										</p>
									</form>
									<form action="conferma_acquisto_ordine.php" method="post">
										<p>
											<button type="submit" class="container_pulsante" name="confirm" style="margin-left: 1.5em; margin-right: 1.5em; padding: 0em;">Conferma!</button>
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