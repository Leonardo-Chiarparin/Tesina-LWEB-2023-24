<?php
	// LO SCRIPT PERMETTE DI EFFETTUARE LA REGISTRAZIONE AL SITO DA PARTE DI UN DETERMINATO UTENTE, AL QUALE SARANNO RICHIESTI UNA SERIE DI DETTAGLI CHE DOVRANNO RISPETTARE LE SPECIFICHE MOSTRATE A SCHERMO
	// PER QUESTIONI DI LEGGIBILITÀ DEL CODICE, ABBIAMO DECISO DI SEPARARE L'INTERO MECCANISMO DI REGISTRAZIONE DALLA PAGINA IN CUI È CONTENUTO IL RELATIVO MODULO
	// IL PRIMO PASSO CONSISTE NELLA RIMOZIONE DEI POSSIBILI SPAZI (BIANCHI) COLLOCATI ALL'INIZIO E ALLA FINE DEGLI ELEMENTI COINVOLTI
	$_POST["nome"]=trim($_POST["nome"]);
	$_POST["nome"]=rtrim($_POST["nome"]);
	
	$_POST["cognome"]=trim($_POST["cognome"]);
	$_POST["cognome"]=rtrim($_POST["cognome"]);
	
	$_POST["num_telefono"]=trim($_POST["num_telefono"]);
	$_POST["num_telefono"]=rtrim($_POST["num_telefono"]);
	
	$_POST["indirizzo"]=trim($_POST["indirizzo"]);
	$_POST["indirizzo"]=rtrim($_POST["indirizzo"]);
	
	$_POST["citta"]=trim($_POST["citta"]);
	$_POST["citta"]=rtrim($_POST["citta"]);
	
	$_POST["cap"]=trim($_POST["cap"]);
	$_POST["cap"]=rtrim($_POST["cap"]);
	
	$_POST["username"]=trim($_POST["username"]);
	$_POST["username"]=rtrim($_POST["username"]);
	
	$_POST["email"]=trim($_POST["email"]);
	$_POST["email"]=rtrim($_POST["email"]);
	
	$_POST["password"]=trim($_POST["password"]);
	$_POST["password"]=rtrim($_POST["password"]);
	
	// AL FINE DI GARANTIRE UN DISCRETO LIVELLO DI SICUREZZA, PER I CAMPI PRIVI DI CONTROLLI INERENTI AD ESPRESSIONI REGOLARI (ADEGUATE PER IL CONTESTO), VERRANNO EFFETTUATE DELLE VERIFICHE IN MERITO AI CARATTERI CONTENUTI AL LORO INTERNO 
	$_POST["nome"]=stripslashes($_POST["nome"]); // UN ESEMPIO, POTREBBE ESSERE LA RIMOZIONE DEI BACKSLASH \ PER EVITARE LA MySQL Injection
	$_POST["cognome"]=stripslashes($_POST["cognome"]);    // ***
	$_POST["indirizzo"]=stripslashes($_POST["indirizzo"]); // ***
	$_POST["citta"]=stripslashes($_POST["citta"]);    // ***
	$_POST["username"]=stripslashes($_POST["username"]);    // ***
	
	// GIUNTI A QUESTO PUNTO, E SECONDO L'ORDINAMENTO INDOTTO DALLE PRECEDENTI OPERAZIONI, È NECESSARIO EFFETTUARE UN ULTERIORE CONTROLLO PER VALUTARE SE SI È EFFETTIVAMENTE INSERITO QUALCOSA ALL'INTERNO DEI VARI CAMPI 
	if((strlen($_POST["nome"])==0)||(strlen($_POST["cognome"])==0)||(strlen($_POST["num_telefono"])==0)||(strlen($_POST["indirizzo"])==0)||(strlen($_POST["citta"])==0)||(strlen($_POST["cap"])==0)||(strlen($_POST["username"])==0)||(strlen($_POST["email"])==0)||(strlen($_POST["password"])==0)){
		
		// IN CASO DI ERRORE, SI DOVRÀ INFORMARE L'UTENTE COINVOLTO DELLE MANCANZE CHE HA AVUTO. UN SIMILE MECCANISMO VERRÀ IMPLEMENTATO PER TUTTE LE CASISITCHE IN CUI SARÀ IMPOSSIBILE PROCEDERE  
		$campi_vuoti=true;
	}
	else {
		
		// PRIMA DI PROCEDERE, BISOGNA EFFETTUARE DEI CONTROLLI PRELIMINARI PER VALUTARE SE UN DETERMINATO ELEMENTO ECCEDE LA DIMENSIONE MASSIMA INDICATA
		if(strlen($_POST["nome"])>30) {
			
			// ***
			$superamento_nome=true;
		}
		
		if(strlen($_POST["cognome"])>35) {
			
			// ***
			$superamento_cognome=true;
		}
		
		if(strlen($_POST["num_telefono"])>10) {
			
			// ***
			$superamento_recapito=true;
		}
		
		if(strlen($_POST["indirizzo"])>60) {
			
			// ***
			$superamento_indirizzo=true;
		}
		
		if(strlen($_POST["citta"])>40) {
			
			// ***
			$superamento_citta=true;
		}
		
		if(strlen($_POST["cap"])>5) {
			
			// ***
			$superamento_cap=true;
		}
		
		if(strlen($_POST["username"])>30) {
			
			// ***
			$superamento_username=true;
		}
		
		if(strlen($_POST["email"])>35) {
			
			// ***
			$superamento_email=true;
		}
		
		if(strlen($_POST["password"])>16) {
			
			// ***
			$superamento_password=true;
		}
		
		// SE LE VERIFICHE DI CUI SOPRA NON HANNO INDIVIDUATO ALCUNA SORTA DI PROBLEMATICA, ALLORA È POSSIBILE PROCEDERE CON LE RESTANTI OPERAZIONI
		if(!(isset($superamento_nome) && $superamento_nome) && !(isset($superamento_cognome) && $superamento_cognome) && !(isset($superamento_recapito) && $superamento_recapito) && !(isset($superamento_indirizzo) && $superamento_indirizzo) && !(isset($superamento_citta) && $superamento_citta) && !(isset($superamento_cap) && $superamento_cap) && !(isset($superamento_username) && $superamento_username) && !(isset($superamento_email) && $superamento_email) && !(isset($superamento_password) && $superamento_password)) {
			
			// INOLTRE, PER QUESTIONI DI PRESENTAZIONE DELLE VARIE INFORMAZIONI, VERRÀ APPLICATA UN'OPPORTUNA FORMATTAZIONE PER IL NOMINATIVO DEL CLIENTE COINVOLTO. NEL DETTAGLIO, SOLTANTO LA PRIMA LETTERA DEL NOME E DEL COGNOME DOVRANNO ESSERE MAIUSCOLE
			$_POST["nome"]=strtolower($_POST["nome"]);
			$_POST["cognome"]=strtolower($_POST["cognome"]);
			
			$_POST["nome"]=ucfirst($_POST["nome"]);
			$_POST["cognome"]=ucfirst($_POST["cognome"]);
			
			// UNA VOLTA TERMINATE LE VERIFICHE PRELIMINARI, BISOGNERÀ PROCEDERE CON DEI CONTROLLI DISPOSTI A CASCATA, DUNQUE ANNIDATI, PER LA VERIFICA DEL FORMATO DEI VARI ELEMENTI COINVOLTI 
			// STANDO A COME È STATA CARATTERIZZATA LA PAGINA IN QUESTIONE, I PRIMI VALORI A DOVER RIPORTARE UNA COMPOSIZIONE BEN DELINEATA SONO PROPRIO IL RECAPITO TELEFONICO E IL CODICE DI AVVIAMENTO POSTALE FORNITI DALL'UTENTE 
			if(preg_match("/([[:digit:]]{10,10})/",$_POST["num_telefono"],$matches_recapito)){
				if($matches_recapito[0]!=$_POST["num_telefono"]) {
					
					// ***
					$recapito_errato=true;
				}
				else {
					if(preg_match("/([[:digit:]]{5,5})/",$_POST["cap"],$matches_cap)) {
						if($matches_cap[0]!=$_POST["cap"]) {
							
							// ***
							$cap_errato=true;
						}
						else {
							// TERMINATO IL PRECEDENTE CONTROLLO, SI PROCEDE CON LA VERIFICA IN MERITO AL FORMATO DELL'INDIRIZZO DI POSTA ELETTRONICA (ESEMPIO: example@example.dom, CON IL DOMINIO AVENTE UNA LUNGHEZZA PARI A 2 O A 3) E DELLA PASSWORD (AVENTE UNA LUNGHEZZA PARI, AL PIÙ, A 16 ELEMENTI, I QUALI, COME ANCHE RIPORTATO SOTTO, DOVRANNO ESSERE ALMENO UN NUMERO, UNA LETTERA MINUSCOLA E UNA LETTERA MAIUSCOLA)
							if (preg_match("/((([[:alpha:]]|(\d))+)@([[:alpha:]]+)(\.[[:alpha:]]{2,3}))/",$_POST["email"],$matches_email)) {
								if($matches_email[0]!=$_POST["email"]) {
									
									// ***
									$email_errata=true;
								}
								else {
									if(preg_match("/(((?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])).{3,16}$)/",$_POST["password"],$matches_password)) {
										if($matches_password[0]!=$_POST["password"]) {
									
											// ***
											$password_errata=true;
										}
										else {
											// DATO L'INTENTO DI VOLER CONFRONTARE L'ESITO DI UNA DETERMINATA QUERY, SARÀ NECESSARIO PREDISPORRE IL TUTTO ALL'INTERNO DI UN COSTRUTTO try ... catch ... AL FINE DI CATTURARE L'EVENTUALE ECCEZIONE E NOTIFICARE L'ACCADUTO ALL'UTENTE COINVOLTO
											// INFATTI, UN ESEMPIO POTREBBE ESSERE LA DUPLICAZIONE DEL CONTENUTO DEI CAMPI DEFINITI COME unique, OVVERO L'USERNAME E L'INDIRIZZO DI POSTA ELETTRONICA 
											// PER QUANTO CONCERNE L'EVENTUALITÀ PRESENTATA, SARÀ SUFFICIENTE VERIFICARE IL NUMERO DI RIGHE OTTENUTE COME RISULTATO A SEGUITO DELLA SEGUENTE INTERROGAZIONE ALLA BASE DI DATI
											$sql="SELECT * FROM $tab WHERE Username='".$_POST["username"]."'";
											$result=mysqli_query($conn, $sql);
											
											if(mysqli_num_rows($result)==0) {
												$sql="SELECT * FROM $tab WHERE Email='".$_POST["email"]."'";
												$result=mysqli_query($conn, $sql);
												
												if(mysqli_num_rows($result)==0) { 
													try {
														// SE NON È STATA EVIDENZIATA ALCUNA SORTA DI PROBLEMATICA, È POSSIBILE EFFETTUARE L'INSERIMENTO DEI DATI
														$sql="INSERT INTO $tab VALUES(NULL,'".$_POST["nome"]."','".$_POST["cognome"]."','".$_POST["num_telefono"]."','".$_POST["email"]."','".$_POST["username"]."','".$_POST["password"]."','".$_POST["indirizzo"]."',	'".$_POST["citta"]."','".$_POST["cap"]."','C','".date("Y-m-d")."',0.00,33,'N')";
														
														// COME ACCENNATO, PRIMA DI CONCLUDERE L'OPERAZIONE BISOGNERÀ VALUTARE L'ESITO DELL'ESECUZIONE INERENTE AL PRECEDENTE COMANDO SQL
														if(mysqli_query($conn,$sql)){
															
															// PER INDIVIDUARE L'ID DELL'UTENTE APPENA MEMORIZZATO, È POSSIBILE FAR USO DI UN'ULTERIORE INTERROGAZIONE TENENDO CONTO DELLA SUA EMAIL O USERNAME (UNICI A LIVELLO DI ENTRY). UN SIMILE RAGIONAMENTO È STATO CONCEPITO E REALIZZATO A FRONTE DI POSSIBILI INSERIMENTI SIMULTANEI DA PARTE DI PIÙ SOGGETTI, COSÌ DA OTTENERE L'ELEMENTO CORRETTO 
															$sql="SELECT ID FROM $tab WHERE Username='".$_POST["username"]."' OR Email='".$_POST["email"]."'";
															$result=mysqli_query($conn,$sql);
															
															while($row=mysqli_fetch_array($result))
																$id_Utente=$row["ID"];
															
															// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI ACQUISTI (CON ANNESSI I CARRELLI E LE RIDUZIONI DI PREZZO), È NECESSARIO FARE RIFERIMENTO AI CODICI PER APRIRE E DUNQUE INTERAGIRE CON I RELATIVI FILE XML 
															require_once("./apertura_file_acquisti.php");
															require_once("./apertura_file_carrelli.php");
															require_once("./apertura_file_riduzioni.php");
															
															// LA RAPPRESENTAZIONE DELL'INSIEME DEGLI ACQUISTI È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUEST'ULTIMO. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI DI INTERESSE
															$nuovi_acquistiPerCliente=$docAcquisti->createElement("acquistiPerCliente");
															
															$nuovi_acquistiPerCliente->setAttribute("idCliente", $id_Utente);
															$nuovi_acquistiPerCliente->setAttribute("ultimoIdPerAcquisto", 0);
															
															$rootAcquisti->appendChild($nuovi_acquistiPerCliente);
															
															// LA RAPPRESENTAZIONE DELL'INSIEME DEL CARRELLO DI UN UTENTE È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUEST'ULTIMO. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI DI INTERESSE 
															$nuovo_carrello=$docCarrelli->createElement("carrello");
															
															$nuovo_carrello->setAttribute("idCliente", $id_Utente);
															
															$rootCarrelli->appendChild($nuovo_carrello);
															
															// LA RAPPRESENTAZIONE DELL'INSIEME DELLE RIDUZIONI DI PREZZO DI UN UTENTE È COMPOSTA A PARTIRE DA UN ELEMENTO OMONIMO DI QUEST'ULTIMO. A SEGUITO DI CIÒ, È NECESSARIA LA SPECIFICA E L'AGGIUNTA DI TUTTI GLI ALTRI DETTAGLI DI INTERESSE 
															$nuova_riduzione=$docRiduzioni->createElement("riduzione");
															
															$nuova_riduzione->setAttribute("idCliente", $id_Utente);
															
															$nuovi_sconti=$docRiduzioni->createElement("sconti");
															
															$aSoglia=$docRiduzioni->createElement("aSoglia");
															$aSoglia->setAttribute("idTariffaSoglia", 1);
															$aSoglia->setAttribute("creditiSpesi", 0.00);
															$aSoglia->setAttribute("superamenti", 0);
															$aSoglia->setAttribute("fruibile", 0);
															
															$nuovi_sconti->appendChild($aSoglia);
															
															$fedeltaElite=$docRiduzioni->createElement("fedeltaElite");
															$fedeltaElite->setAttribute("idTariffaFedeltaElite", 2);
															$fedeltaElite->setAttribute("creditiSpesi", 0.00);
															$fedeltaElite->setAttribute("fruibile", 0);
															$fedeltaElite->setAttribute("esercitabile", 1);
															
															$nuovi_sconti->appendChild($fedeltaElite);
															
															$acquistoPromozionale=$docRiduzioni->createElement("acquistoPromozionale");
															$percentuale=$docRiduzioni->createElement("percentuale", 0.00);
															
															$acquistoPromozionale->appendChild($percentuale);
															$nuovi_sconti->appendChild($acquistoPromozionale);
															
															$perVIP=$docRiduzioni->createElement("perVIP");
															$perVIP->setAttribute("idTariffaPerVIP", 3);
															$perVIP->setAttribute("fruibile", 0);
															
															$nuovi_sconti->appendChild($perVIP);
															
															$diAnzianita=$docRiduzioni->createElement("diAnzianita");
															$diAnzianita->setAttribute("idTariffaDiAnzianita", 4);
															$diAnzianita->setAttribute("fruibile", 0);
															$percentuale=$docRiduzioni->createElement("percentuale", 0.00);
															
															$diAnzianita->appendChild($percentuale);
															$nuovi_sconti->appendChild($diAnzianita);
															
															$nuova_riduzione->appendChild($nuovi_sconti);
															
															$rootRiduzioni->appendChild($nuova_riduzione);
															
															// DAL MOMENTO CHE QUEST'ULTIMO DOCUMENTO SI RIFERISCE AD UNA STRUTTURA DESCRITTA TRAMITE DTD, È NECESSARIO SALVARNE PREVENTIVAMENTE IL CONTENUTO E IN SEGUITO VALUTARNE LA CORRETTEZZA TRAMITE IL METODO validate() 
															$docRiduzioni->preserveWhiteSpace = false;
															$docRiduzioni->formatOutput = true;
															$docRiduzioni->save("../../XML/Riduzioni_Prezzi.xml");
															
															$dom = new DOMDocument;
															$dom->load("../../XML/Riduzioni_Prezzi.xml");
															
															// PER CONCLUDERE L'OPERAZIONE, BISOGNA VALIDARE LA COMPOSIZIONE DEI DOCUMENTI APPENA AGGIORNATI IN RELAZIONE A QUANTO ESPOSTO NELLE RELATIVE GRAMMATICHE DTD E NEI RELATIVI SCHEMA
															if($docAcquisti->schemaValidate("../../XML/Schema/Acquisti_Clienti.xsd") && $docCarrelli->schemaValidate("../../XML/Schema/Carrelli_Clienti.xsd") && $dom->validate()){
																
																// PER UNA STAMPA OTTIMALE, SONO STATI APPLICATI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
																$docAcquisti->preserveWhiteSpace = false;
																$docAcquisti->formatOutput = true;
																$docAcquisti->save("../../XML/Acquisti_Clienti.xml");
																
																$docCarrelli->preserveWhiteSpace = false;
																$docCarrelli->formatOutput = true;
																$docCarrelli->save("../../XML/Carrelli_Clienti.xml");
																
																
															}
															else {
																
																// ***
																setcookie("errore_Validazione", true);
																
																header("Location: index.php");
															}
															
															// SE NON SI SONO RISCONTRATI DEGLI ERRORI LEGATI ALLA VALIDAZIONE DEI FILE DI CUI SOPRA, SI PROCEDE CON LA CREAZIONE DELLA RELATIVA SESSIONE E DELLE VARIBILI DEDICATE
															session_start();
															$_SESSION["id_Utente"]=$id_Utente;
															$_SESSION["username_Utente"]=$_POST["username"];
															$_SESSION["tipo_Utente"]="C";
															
															// PRIMA DI ESSERE REINDIRIZZATI, BISOGNA VALUTARE SE L'UTENTE COINVOLTO, PRIMA DI AUTENTICARSI, HA INSERITO QUALCHE OFFERTA ALL'INTERNO DEL PROPRIO CARRELLO. INFATTI, SI HA LA NECESSITÀ DI INTEGRARE LE INFORMAZIONI ATTUALMENTE PRESENTI NEL RELATIVO FILE XML CON QUELLE CONTENUTE ALL'INTERNO DEL COOKIE DI INTERESSE 
															require_once("./integrazione_contenuto_carrello.php");
				
															// INOLTRE, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
															$_SESSION["accesso_Effettuato"]=true;
															
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
												else {
													
													// ***
													$duplicazione_email=true;
												}
											}
											else {
												
												// ***
												$duplicazione_username=true;
											}
										}
									}
									else {
										
										// ***
										$password_errata=true;
									}
								}
							}
							else {
								
								// ***
								$email_errata=true;
							}
						}
					}
					else {
						// ***
						$cap_errato=true;
					}
				}
			}
			else {
				
				// ***
				$recapito_errato=true;
			}
		}
	}
?>