<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
	// LA PAGINA PERMETTE DI VISUALIZZARE A SCHERMO IL RIEPILOGO DEGLI UTENTI REGISTRATI ALLA PIATTAFORMA

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
	
	// COME PER I FILE XML, SI PROCEDE CON UN'INTERROGAZIONE ALLA BASE DI DATI SUDDETTA AL FINE DI REPERIRE I DATI RELATIVI AI VARI UTENTI D'INTERESSE PER PERMETTERNE LA VISUALIZZAZIONE  
	// POICHÈ SI TRATTA DI UN ELEMENTO COMUNE TRA GESTORI E AMMINISTRATORE, SI È DECISO DI FILTARE DINAMICAMENTE IL CONTENUTO DELLA PAGINA MEDIANTE UN CONTROLLO RELATIVO ALLA VARIABILE CONTENENTE IL TIPO DI UTENTE CHE NE HA FATTO RICHIESTA 
	if($_SESSION["tipo_Utente"]=="A")
		$sql="SELECT ID, Nome, Cognome, Username, Tipo_Utente FROM $tab"; 
	else 
		$sql="SELECT ID, Nome, Cognome, Username, Tipo_Utente FROM $tab WHERE Tipo_Utente='C'";
	
	$result=mysqli_query($conn, $sql);
	
	// PER VALUTARE IL NUMERO DEGLI UTENTI, BISOGNERÀ DETERMINARE IL NUMERO DELLE RIGHE RESTITUITE DALL'ESECUZIONE DEL PRECEDENTE COMANDO SQL   
	$num_clienti=mysqli_num_rows($result);	
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
									<img src="../../Immagini/users-gear-solid.svg" alt="Immagine Non Disponibile..." />
								</span>
								<h2>Visualizza le informazioni <?php if($_SESSION["tipo_Utente"]=="A") echo "degli utenti"; else echo "dei clienti"; ?>!</h2>
							</div>
						</div>
						<div class="corpo_form">
							<div class="container_corpo_form">
								<?php
									// IN BASE AL QUANTITATIVO DI UTENTI DA GESTIRE, SARÀ POSSIBILE STABILIRE COSA PRESENTARE A SCHERMO
									if($num_clienti==0) {
										echo "<span class=\"nessun_elemento\">Non &egrave; stato trovato nessun utente con le specifiche di interesse.</span>\n";
									}
									else {
										// L'INTESTAZIONE DELLA TABELLA SARÀ UN FATTORE STATICO IN CUI VERRANNO PRESENTATI TUTTI GLI ELEMENTI, DEFINITI COME COLONNE, CHE LA COMPORRANNO 
										echo "<table>\n";
										echo "\t\t\t\t\t\t\t\t<thead>\n";
										echo "\t\t\t\t\t\t\t\t\t<tr>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Utente</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Nome</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Cognome</th>\n";
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Username</th>\n";
										
										if($_SESSION["tipo_Utente"]=="A")
											echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Tipo <strong style=\"color: rgb(217, 118, 64);\" title=\"clienti (C), gestori (G) o amministratore (A)\">*</strong></th>\n";
										else
											echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Tipo <strong style=\"color: rgb(217, 118, 64);\" title=\"clienti (C)\">*</strong></th>\n";
										
										echo "\t\t\t\t\t\t\t\t\t\t<th class=\"oggetto_td\">Azione</th>\n";
										echo "\t\t\t\t\t\t\t\t\t</tr>\n";
										echo "\t\t\t\t\t\t\t\t</thead>\n";
										echo "\t\t\t\t\t\t\t\t<tbody>\n";
										
										// IL CORPO DELLA COMPONENTE DI CUI SOPRA VERRÀ COMPOSTO DINAMICAMENTE E SEGUENDO L'ORDINE INDOTTO DALLE PRECEDENTI STAMPE
										while($row=mysqli_fetch_array($result)) {
											echo "\t\t\t\t\t\t\t\t\t<tr>\n"; 
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$row["ID"]."</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$row["Nome"]."</td>\n"; 
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$row["Cognome"]."</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$row["Username"]."</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\">".$row["Tipo_Utente"]."</td>\n";
											echo "\t\t\t\t\t\t\t\t\t\t<td class=\"oggetto_td\"><span class=\"pulsante_td\"><a href=\"riepilogo_utente_selezionato.php?id_Utente_Selezionato=".$row["ID"]."\" class=\"container_pulsante_td\" title=\"Visualizza!\"><img src=\"../../Immagini/info-solid.svg\" alt=\"Immagine Non Disponibile...\" /></a></span></td>\n";
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