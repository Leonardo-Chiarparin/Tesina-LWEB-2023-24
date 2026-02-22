<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO LO STATO ATTUALE (LIBERO O SOSPESO) INERENTE AL PROFILO DEI VARI CLIENTI

	// PRIMA DI OGNI ALTRA OPERAZIONE, ABBIAMO DECISO DI CONTROLLARE SE LE VARIE STRUTTURE DATI SONO STATE POPOLATE CORRETTAMENTE O MENO
	require_once("./monitoraggio_stato_strutture_dati.php");
	
	// INOLTRE, TENENDO CONTO DELLA DATA ODIERNA, SI DOVRÀ AGGIORNARE IL CONTENUTO DEL FILE INERENTE ALLE SOLE RIDUZIONI DI PREZZO A CUI I CLIENTI HANNO AVUTO ACCESSO O MENO A SECONDA DEL SODDISFACIMENTO DI ALCUNI REQUISITI
	require_once("./attribuzione_sconti.php");

	// PER QUESTIONI DI SICUREZZA, È STATO NECESSARIO L'INSERIMENTO DELLO SCRIPT "private_session_control.php" ALLO SCOPO DI GARANTIRE L'ESISTENZA DI UN'EVENTUALE SESSIONE APERTA. QUESTO MECCANISMO NON PERMETTERÀ L'ACCESSO AGLI UTENTI CHE NON SI AUTENTICATI TRAMITE IL RELATIVO SERVIZIO
	require_once("./private_session_control.php");
	
	// AL FINE DI REPERIRE TUTTE LE INFORMAZIONI DI INTERESSE, IN PARTICOLARE QUELLE RELATIVE AGLI UTENTI, È NECESSARIO FARE RIFERIMENTO AL CODICE IN GRADO DI INSTAURARE UNA CONNESSIONE CON LA BASE DI DATI 
	require_once("./connection.php");
	
	// NEL CASO IN CUI L'UTENTE SI SIA AUTENTICATO CORRETTAMENTE, BISOGNA VALUTARE LA TIPOLOGIA DI QUEST'ULTIMO, IN QUANTO LA FUNZIONE IN QUESTIONE DOVRÀ ESSERE ACCESSIBILE SOLTANTO ALL'AMMINISTRATORE DEL SITO
	if(isset($_SESSION["tipo_Utente"]) && $_SESSION["tipo_Utente"]!="A") {
		header("Location: area_riservata.php");
	}
	
	// COME PER I FILE XML, SI PROCEDE CON UN'INTERROGAZIONE ALLA BASE DI DATI SUDDETTA AL FINE DI REPERIRE I DATI RELATIVI AGLI UTENTI PER AGEVOLARNE LA PROCEDURA DI GESTIONE
	$sql="SELECT ID, Nome, Cognome, Username, Ban FROM $tab WHERE Tipo_Utente='C'";
	$result=mysqli_query($conn, $sql);
	
	// PER VALUTARE IL NUMERO DI CLIENTI, BISOGNERÀ DETERMINARE IL NUMERO DELLE RIGHE RESTITUITE DALL'ESECUZIONE DEL PRECEDENTE COMANDO SQL   
	$num_clienti=mysqli_num_rows($result);
	
	// UNA VOLTA SELEZIONATA IL PROFILO DA GESTIRE, BISOGNERÀ IDENTIFICARE L'OPERAZIONE DA ESEGUIRE SU QUEST'ULTIMO 
	if(isset($_GET["id_Utente"]) && isset($_GET["azione"]))
	{
		// DATO L'INTENTO DI VOLER CONFRONTARE L'ESITO DI UNA DETERMINATA QUERY, SARÀ NECESSARIO PREDISPORRE IL TUTTO ALL'INTERNO DI UN COSTRUTTO try ... catch ... AL FINE DI CATTURARE L'EVENTUALE ECCEZIONE E NOTIFICARE L'ACCADUTO ALL'UTENTE IN OGGETTO
		// INFATTI, UN POSSIBILE FALLIMENTO POTREBBE DIPENDERE DAL SUPERAMENTO DEL LIMITE DI CARATTERI CHE POSSONO ESSERE INSERITI ALL'INTERNO DI UN CAMPO DELLA TABELLA RELAZIONE COINVOLTA. SIMILMENTE, QUALORA CI SIA LA DUPLICAZIONE DEL CONTENUTO DEI CAMPI DEFINITI COME unique 
		try {	
			// PER UNA QUESTIONE PURAMENTE PRATICA, SARÀ UTILE DIFFERENZIARE IL CONTENUTO DELL'INTERROGAZIONE DA ESEGUIRE DIRETTAMENTE NEL CONTROLLO CHE SI EFFETTUA PER VALUTARE L'OPERAZIONE SELEZIONATA 
			if($_GET["azione"]=="disabilita")
			{
				$sql="UPDATE $tab SET Ban='Y' WHERE ID=".$_GET["id_Utente"];
			}
			else
			{
				$sql="UPDATE $tab SET Ban='N' WHERE ID=".$_GET["id_Utente"];
			}
		
			// COME ACCENNATO, PRIMA DI CONCLUDERE L'OPERAZIONE BISOGNERÀ VALUTARE L'ESITO DELL'ESECUZIONE INERENTE AL PRECEDENTE COMANDO SQL
			if(mysqli_query($conn,$sql)){

				// PRIMA DI ESSERE REIDERIZZATI, SI PREDISPONE UNA VARIABILE DI SESSIONE CHE SARÀ USATA COME FLAG PER RIPORTARE IL SUCCESSO DELL'OPERAZIONE ALL'UTENTE INTERESSATO
				$_SESSION["modifica_Effettuata"]=true;
				
				header("Location: area_riservata.php");
				
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
			if(isset($errore_query) && $errore_query) {
				
				// IN OCCASIONE DEL POSSIBILE RICARICAMENTO DELLA PAGINA, SI PROCEDE CON LA RIMOZIONE DEL FLAG ALLO SCOPO DI NON RIPROPORRE LA MEDESIMA NOTIFCA 
				$errore_query=false;
				
				echo "<div class=\"error_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
				echo "\t\t\t\t\t<p>DIMENSIONE DEL CAMPO INERENTE ALLO STATO ECCEDUTA...</p>\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t</div>\n";
				echo "\t\t</div>\n";
				
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
								<h2>Disabilita o ripristina il profilo degli utenti!</h2>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<?php
									// IN BASE AL NUMERO DI CLIENTI DA GESTIRE, SARÀ POSSIBILE STABILIRE COSA PRESENTARE A SCHERMO
									if($num_clienti==0) {
										echo "<span class=\"nessun_elemento\">Non &egrave; stato trovato nessun elemento con le specifiche di interesse.</span>\n";
									}
									else {
										// L'INTESTAZIONE DELLA TABELLA SARÀ UN FATTORE STATICO IN CUI VERRANNO PRESENTATI TUTTI GLI ELEMENTI, DEFINITI COME COLONNE, CHE LA COMPORRANNO 
										echo "<table>\n";
										echo "\t\t\t\t\t\t\t\t<thead>\n";
										echo "\t\t\t\t\t\t\t\t\t<tr>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Cliente</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Nome</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Cognome</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Username</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Stato</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Azione</th>\n";
										echo "\t\t\t\t\t\t\t\t\t</tr>\n";
										echo "\t\t\t\t\t\t\t\t</thead>\n";
										echo "\t\t\t\t\t\t\t\t<tbody>\n";
										
										// IL CORPO DELLA COMPONENTE DI CUI SOPRA VERRÀ COMPOSTO DINAMICAMENTE E SEGUENDO L'ORDINE INDOTTO DALLE PRECEDENTI STAMPE
										while($row=mysqli_fetch_array($result)) {
											
											if($row["Ban"]=="Y")
												$stato="Sospeso";
											else 
												$stato="Attivo";
											
											echo "\t\t\t\t\t\t\t\t\t<tr>\n"; 
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$row["ID"]."</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$row["Nome"]."</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$row["Cognome"]."</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$row["Username"]."</td>\n"; 
											
											// PER UNA STAMPA PIÙ GRADEVOLE, SI EFFETTUA UNA FORMATTAZIONE DELLE VARIE INFORMAZIONI
											if($stato=="Sospeso")
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\" style=\"color: rgb(119, 119, 119);\">".$stato."</td>\n";
											else 
												echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\" style=\"color: rgb(217, 118, 64);\">".$stato."</td>\n";
											
											
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">\n";
											echo "\t\t\t\t\t\t\t\t\t\t\t<span class=\"pulsante_td\">\n";
											
											// LA POSSIBILITÀ DI DISABILITARE O RIPRISTINARE IL PROFILO DEI VARI UTENTI VIENE GESTITA DINAMICAMENTE IN BASE ALLO STATO ATTUALE DEL PROFILO DEGLI UTENTI
											if($stato=="Sospeso")
												echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"restrizioni_clienti.php?id_Utente=".$row["ID"]."&amp;azione=ripristina\" class=\"container_pulsante_td\" title=\"Ripristina!\"><img src=\"../../Immagini/user-plus-solid.svg\" alt=\"Icona Ripristino\" /></a>\n";
											else
												echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"restrizioni_clienti.php?id_Utente=".$row["ID"]."&amp;azione=disabilita\" class=\"container_pulsante_td back\" title=\"Disabilita...\"><img src=\"../../Immagini/user-minus-solid.svg\" alt=\"Icona Sospensione\" /></a>\n";
											
											echo "\t\t\t\t\t\t\t\t\t\t\t</span>\n";
											echo "\t\t\t\t\t\t\t\t\t\t</td>\n";
											echo "\t\t\t\t\t\t\t\t\t</tr>\n"; 
										
										}
									
										echo "\t\t\t\t\t\t\t\t</tbody>\n";
										echo "\t\t\t\t\t\t\t</table>\n";
										
									}
								?>
								<div class="pulsante" style="justify-content: center; margin-top: 3.5%; margin-bottom: 0%;">
									<form action="area_riservata.php" method="post">
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