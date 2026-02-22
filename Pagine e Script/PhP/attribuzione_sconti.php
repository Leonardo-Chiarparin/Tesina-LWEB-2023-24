<?php
	// LO SCRIPT PERMETTE DI VALUTARE SE IL CLIENTE HA ACCESSO AD UNA DELLE RIDUZIONI DI PREZZO MESSE A DISPOSIZIONE LORO DALLA PIATTAFORMA
	// NEL DETTAGLIO, GLI UNICI SCONTI CHE SARÀ POSSIBILE CONSIDERARE A PRESCINDERE DAL NUMERO DI ACQUISTI EFFETTUATI O CONTRIBUTI PUBBLICATI, E DUNQUE DI CREDITI SPESI, RIGUARDANO IL TEMPO TRASCORSO DALLA REGISTRAZIONE AL SITO E L'INIZIALIZZAZIONE DEI VARI PARAMETRI A PARTIRE DA UNA CERTA DATA, LA QUALE, NEL NOSTRO CASO, COINCIDERÀ SEMPRE CON IL PRIMO GIORNO DELL'ANNO

	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");

	// LE INFORMAZIONI CHE SARANNO POI ELABORATE NEI VARI PUNTI DEL CODICE POTRANNO ESSERE REPERITE DALLE RELATIVE STRUTTURE DATI (FILE XML), IL CUI CONTENUTO SARÀ RESO ACCESSIBILE MEDIANTE UNA SERIE DI SCRIPT    
	require_once("./apertura_file_riduzioni.php");
	require_once("./apertura_file_tariffe.php");
	require_once("./apertura_file_acquisti.php");
	
	// IN MERITO ALLO SCONTO PER ANZIANITÀ, BISOGNERÀ INDIVIDUARE TUTTI QUEI CLIENTI CHE SI SONO REGISTRATI ALMENO DA UN MINIMO DI 1 FINO AD UN MASSIMO DI 3 ANNI PRIMA DELLA DATA CORRENTE E ATTRIBUIRE LORO LA CORRISPONDENTE PERCENTUALE 
	// NELL'ORDINE, SI ANDRÀ ALLA RICERCA DI:
	
	// 1) TUTTI QUEI SOGGETTI CHE SI SONO REGISTRATI DA ALMENO 1 ANNO E FINO AD UN MASSIMO DI 2 (ESCLUSI), I QUALI AVRANNO DIRITTO AD UNA RIDUZIONE PERMANENTE DELL'1% 
	// 2) TUTTI QUEI SOGGETTI CHE SI SONO REGISTRATI DA ALMENO 2 ANNI E FINO AD UN MASSIMO DI 3 (ESCLUSI), I QUALI AVRANNO DIRITTO AD UNA RIDUZIONE PERMANENTE DEL 2% 
	// 3) TUTTI QUEI SOGGETTI CHE SI SONO REGISTRATI DA ALMENO 3 ANNI, I QUALI AVRANNO DIRITTO AD UNA RIDUZIONE PERMANENTE DEL 3% 
	for($i=$rootTariffe->getElementsByTagName("tariffaDiAnzianita")->item(0)->getAttribute("sogliaAnniMinima"); $i<=$rootTariffe->getElementsByTagName("tariffaDiAnzianita")->item(0)->getAttribute("sogliaAnniMassima"); $i++) {
		
		// STANDO A QUANTO RIPORTATO IN PRECEDENZA, SI HA LA NECESSITÀ DI DISCRIMINARE LA CASISTICA A CUI SI STA ATTUALMENTE FACENDO RIFERIMENTO
		if($i!=$rootTariffe->getElementsByTagName("tariffaDiAnzianita")->item(0)->getAttribute("sogliaAnniMassima")) {
			$sql="SELECT ID FROM $tab WHERE Tipo_Utente='C' AND YEAR(CURDATE())-YEAR(Data_Registrazione)>=".$i." AND YEAR(CURDATE())-YEAR(Data_Registrazione)<".($i+1);
		}
		else {
			$sql="SELECT ID FROM $tab WHERE Tipo_Utente='C' AND YEAR(CURDATE())-YEAR(Data_Registrazione)>=".$i;
		}
		
		$result=mysqli_query($conn, $sql);
		
		while($row=mysqli_fetch_array($result)) {
			for($j=0; $j<$riduzioni->length; $j++) {
				$riduzione=$riduzioni->item($j);
				
				if($riduzione->getAttribute("idCliente")==$row["ID"]) {
					$riduzione->getElementsByTagName("diAnzianita")->item(0)->setAttribute("fruibile", 1);
					$riduzione->getElementsByTagName("diAnzianita")->item(0)->replaceChild($docRiduzioni->createElement("percentuale", number_format($rootTariffe->getElementsByTagName("tariffaDiAnzianita")->item(0)->getAttribute("basePercentuale")*$i,2,".","")), $riduzione->getElementsByTagName("diAnzianita")->item(0)->firstChild);
					
					// DAL MOMENTO CHE DOCUMENTO INTERESSATO SI RIFERISCE AD UNA STRUTTURA DESCRITTA TRAMITE DTD, È NECESSARIO SALVARNE PREVENTIVAMENTE IL CONTENUTO E IN SEGUITO VALUTARNE LA CORRETTEZZA TRAMITE IL METODO validate() 
					$docRiduzioni->preserveWhiteSpace = false;
					$docRiduzioni->formatOutput = true;
					$docRiduzioni->save("../../XML/Riduzioni_Prezzi.xml");
					
					$dom = new DOMDocument;
					$dom->load("../../XML/Riduzioni_Prezzi.xml");
					
					// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA COMPOSIZIONE DEL DOCUMENTO APPENA AGGIORNATO IN RELAZIONE A QUANTO ESPOSTO NELLA RELATIVA GRAMMATICA DTD
					if(!$dom->validate()){
						// ***
						setcookie("errore_Validazione", true);
						
						header("Location: index.php");
					}
				}
			}
		}
	}
	
	// IN MERITO AGLI SCONTI FEDELTÀ ELITÈ, UNA VOLTA RAGGIUNTO IL LORO TERMINE ULTIMO, SARÀ NECESSARIO AZZERARE TUTTI I PARAMETRI DI INTERESSE AL SOLO SCOPO DI CONSIDERARE SOLTANTO QUEI CREDITI SPESI DAI CLIENTI NEL CORSO DEL NUOVO ANNO
	// N.B.: QUESTE MODIFICHE DOVRANNO ESSERE PORTATE A COMPIMENTO SE L'UTENTE COINVOLTO NON HA GIÀ FATTO DEGLI ACQUISTI IN MERITO AL PERIODO INDICATO, IN QUANTO, OLTRE A POTER ESSERE APPLICATA UNA VOLTA SOLTANTO, CI POTREBBERO ESSERE DELLE INCOGRUENZE IN MERITO AL CALCOLO EFFETTIVO DEI VARI ELEMENTI. INOLTRE, ABBIAMO DECISO DI CONSIDERARE ANCHE TUTTE QUELLE DATE SUCCESSIVE AL PRIMO DELL'ANNO, POICHÈ NON È NOTO QUANDO IL CLIENTE DECIDERÀ DI AUTENTICARSI
	if(strtotime(date("Y-m-d"))>=strtotime(date("Y-01-01"))) {
		
		$sql="SELECT ID FROM $tab WHERE Tipo_Utente='C'";
		$result=mysqli_query($conn, $sql);
	
		while($row=mysqli_fetch_array($result)) {
			
			for($i=0; $i<$rootAcquisti->getElementsByTagName("acquistiPerCliente")->length; $i++) {
				
				$acquistiPerCliente=$rootAcquisti->getElementsByTagName("acquistiPerCliente")->item($i);
				$acquisti_effettuati=false;
				
				if($acquistiPerCliente->getAttribute("idCliente")==$row["ID"]) {
					for($j=0; $j<$acquistiPerCliente->getElementsByTagName("acquistoPerCliente")->length && !$acquisti_effettuati; $j++) {
				
						$acquistoPerCliente=$acquistiPerCliente->getElementsByTagName("acquistoPerCliente")->item($j);
						
						if(strtotime($acquistoPerCliente->getAttribute("dataAcquisto"))>=strtotime(date("Y-01-01"))) {
							$acquisti_effettuati=true;
						}
					}
					
					if(!$acquisti_effettuati) {
						
						// PER PRIMA COSA, SARÀ NECESSARIO IMPOSTARE IL NUOVO VALORE DELLA DATA DI CONTROLLO PONENDOLO PARI A QUELLO DEL PRIMO GIORNO DELL'ANNO
						$rootTariffe->getElementsByTagName("tariffaFedeltaElite")->item(0)->setAttribute("dataPerControllo", date("Y-01-01"));
						
						// INOLTRE, BISOGNERÀ RETTIFICARE ALCUNI DEI PARAMETRI CHE SI RIFERISCONO ALLA TIPOLOGIA DI SCONTO DI INTERESSE PER QUEL DETERMINATO CLIENTE
						for($k=0; $k<$riduzioni->length; $k++) {
							$riduzione=$riduzioni->item($k);
							
							if($riduzione->getAttribute("idCliente")==$row["ID"]) {
								$riduzione->getElementsByTagName("fedeltaElite")->item(0)->setAttribute("creditiSpesi", number_format(0, 2,".",""));
								$riduzione->getElementsByTagName("fedeltaElite")->item(0)->setAttribute("fruibile", 0);
								$riduzione->getElementsByTagName("fedeltaElite")->item(0)->setAttribute("esercitabile", 1);
								
								// ***
								$docTariffe->preserveWhiteSpace = false;
								$docTariffe->formatOutput = true;
								$docTariffe->save("../../XML/Tariffe_Sconti.xml");
								
								$docRiduzioni->preserveWhiteSpace = false;
								$docRiduzioni->formatOutput = true;
								$docRiduzioni->save("../../XML/Riduzioni_Prezzi.xml");
								
								$domTariffe = new DOMDocument;
								$domTariffe->load("../../XML/Tariffe_Sconti.xml");
								
								$domRiduzioni = new DOMDocument;
								$domRiduzioni->load("../../XML/Riduzioni_Prezzi.xml");
								
								// ***
								if(!($domTariffe->validate() && $domRiduzioni->validate())){
									// ***
									setcookie("errore_Validazione", true);
									
									header("Location: index.php");
								}
								else
									break;
							}
						}
					}
				}
			}
		}
	}
	
	// IN AGGIUNTA ALLE PRECEDENTI OPERAZIONI, E NONOSTANTE POSSA SEMBRARE DEL TUTTO SUPERFLUO, ABBIAMO DECISO DI INSERIRE UN CONTROLLO CHE SARÀ IN GRADO DI IMPEDIRE UNA MODIFICA MANUALE DEGLI SCONTI CHE SI RIFERISCONO ALLA REPUTAZIONE DEI VARI CLIENTI
	$sql="SELECT ID, Reputazione FROM $tab WHERE Tipo_Utente='C'";
	$result=mysqli_query($conn, $sql);
	
	while($row=mysqli_fetch_array($result)) {
		
		for($i=0; $i<$riduzioni->length; $i++) {
			$riduzione=$riduzioni->item($i);
			
			if($riduzione->getAttribute("idCliente")==$row["ID"] && $row["Reputazione"]>=$rootTariffe->getElementsByTagName("tariffaPerVIP")->item(0)->getAttribute("sogliaReputazione")) {
				$riduzione->getElementsByTagName("perVIP")->item(0)->setAttribute("fruibile", 1);
				
				$docRiduzioni->preserveWhiteSpace = false;
				$docRiduzioni->formatOutput = true;
				$docRiduzioni->save("../../XML/Riduzioni_Prezzi.xml");
				
				$domRiduzioni = new DOMDocument;
				$domRiduzioni->load("../../XML/Riduzioni_Prezzi.xml");
				
				// ***
				if(!($domRiduzioni->validate())){
					// ***
					setcookie("errore_Validazione", true);
					
					header("Location: index.php");
				}
				
			}	
		}
	}
	
?>