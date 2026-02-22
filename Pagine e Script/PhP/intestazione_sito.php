<?php
	// LO SCRIPT PREVEDE LA STAMPA, OPPORTUNAMENTE INDENTATA (\t E \n), DELLA COMPONENTE PRINCIPALE RAPPRESENTANTE IL MENÙ A SUPPORTO DEI VARI UTENTI

	echo "<div class=\"intestazione_sito\">\n";	
	echo "\t\t\t<div class=\"container_intestazione\">\n";
	echo "\t\t\t\t<div class=\"oggetti_intestazione\">\n";
	echo "\t\t\t\t\t<div class=\"oggetto_intestazione\">\n";
	echo "\t\t\t\t\t\t<a href=\"index.php\" class=\"logo\">\n";
	echo "\t\t\t\t\t\t\t<img src=\"../../Immagini/Logo_LEV.png\" alt=\"Immagine Non Disponibile...\" title=\"LEV: Libri &amp; Videogiochi\" />\n";
	echo "\t\t\t\t\t\t</a>\n";
	echo "\t\t\t\t\t</div>\n";
	echo "\t\t\t\t\t<div class=\"oggetto_intestazione\">\n";
	echo "\t\t\t\t\t\t<div class=\"barra_ricerca\">\n";
	echo "\t\t\t\t\t\t\t<form method=\"get\" action=\"elenco_risultati_ricerca.php\">\n";
	echo "\t\t\t\t\t\t\t\t<div class=\"container_input\">\n";
	echo "\t\t\t\t\t\t\t\t\t<span>Ricerca:</span>\n";							
	echo "\t\t\t\t\t\t\t\t\t<input type=\"text\" name=\"prodotto_Ricercato\" value='";
	
	// DOPO ESSERE STATI REINDERIZZATI ALLA PAGINA DEDICATA AL CATALOGO, LA BARRA DI RICERCA CONTINUERÀ A PRESENTARE I CARATTERI IMMESSI DAGLI UTENTI
	if(isset($_GET["prodotto_Ricercato"]))
		echo $_GET["prodotto_Ricercato"]; 
	else 
		echo '';
	
	echo "' />\n";																
	echo "\t\t\t\t\t\t\t\t\t<span class=\"pulsante_ricerca\">\n";
	echo "\t\t\t\t\t\t\t\t\t\t<button><img src=\"../../Immagini/magnifying-glass-solid.svg\" alt=\"Immagine Non Disponibile...\" /></button>\n";
	echo "\t\t\t\t\t\t\t\t\t</span>\n";									
	echo "\t\t\t\t\t\t\t\t</div>\n";									
	echo "\t\t\t\t\t\t\t</form>\n";							
	echo "\t\t\t\t\t\t</div>\n";
	echo "\t\t\t\t\t</div>\n";
	echo "\t\t\t\t\t<div class=\"oggetto_intestazione\">\n";
	echo "\t\t\t\t\t\t<div class=\"pannello_utente\">\n";
	echo "\t\t\t\t\t\t\t<ul class=\"menu_utente\">\n";
	echo "\t\t\t\t\t\t\t\t<li class=\"voce_pannello\" onclick=\"gestisci_menu_utente()\">\n";
	echo "\t\t\t\t\t\t\t\t\t<span class=\"freccia_tendina destra\" id=\"freccia_tendina_utente\"></span>\n";
	echo "\t\t\t\t\t\t\t\t\t<span class=\"icona\">\n";
	
	// IL MENÙ DI CUI SOPRA È STATO IMPLEMENTATO IN MODO TALE DA PRESENTARE E OFFRIRE DELLE FUNZIONALITÀ CHE VARIANO A SECONDA DELL'UTENTE IN OGGETTO (AUTENTICATO O MENO)
	// NEL DETTAGLIO, COLORO CHE DISPONGONO DI UN PROFILO POTRANNO ACCEDERE ALL'AREA LORO RISERVATA PREVIA IL CORRISPONDENTE COLLEGAMENTO O DECIDERE DI DISCONNETTERSI. D'ALTRO CANTO, I RESTANTI POTRANNO DECIDERE SE AUTENTICARSI O REGISTRARSI ALLA PIATTAFORMA
	// PER RENDERE VISIBILE LA VARIAZIONE APPENA CITATA, È STATO PREDISPONTO ANCHE UN AGGIORNAMENTO DELL'ICONA CORRISPONDENTE 
	if(isset($_SESSION["id_Utente"]))
		echo "\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/user-check-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
	else 
		echo "\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/user-xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
	
	
	echo "\t\t\t\t\t\t\t\t\t</span>\n";
	echo "\t\t\t\t\t\t\t\t\t<ul class=\"menu_tendina utente nascondi\" id=\"menu_utente\">\n";	
	
	if(isset($_SESSION["id_Utente"])){
		echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
		
		// DATE LE MODIFICHE A CUI POTREBBE ESSERE SOTTOPOSTO IL PROFILO DI UN CERTO UTENTE, SARÀ NECESSARIO TENERE TRACCIA DEL VALORE INERENTE AL PROPRIO USERNAME 
		require_once("./connection.php");
		
		$sql_username="SELECT Username FROM $tab WHERE ID=".$_SESSION["id_Utente"];
		$result_username=mysqli_query($conn, $sql_username);
		
		while($row_username=mysqli_fetch_array($result_username))
			$username_attuale=$row_username["Username"];
		
		if($username_attuale!=$_SESSION["username_Utente"])
			$_SESSION["username_Utente"]=$username_attuale;
		
		echo "\t\t\t\t\t\t\t\t\t\t\t<span title=\"Il mio Username\">".$_SESSION["username_Utente"]."</span>\n";
		echo "\t\t\t\t\t\t\t\t\t\t</li>\n";	
		echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
		echo "\t\t\t\t\t\t\t\t\t\t\t<a href=\"area_riservata.php\" title=\"Il mio Account &amp; Ruolo\">Account (".$_SESSION["tipo_Utente"].")</a>\n";
		echo "\t\t\t\t\t\t\t\t\t\t</li>\n";
	echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
		echo "\t\t\t\t\t\t\t\t\t\t\t<a href=\"logout.php\" title=\"Disconnettiti\">Disconnettiti</a>\n";
		echo "\t\t\t\t\t\t\t\t\t\t</li>\n";			
	}
	else {
		echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
		echo "\t\t\t\t\t\t\t\t\t\t\t<a href=\"login.php\" title=\"Accedi\">Accedi</a>\n";
		echo "\t\t\t\t\t\t\t\t\t\t</li>\n";									
		echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
		echo "\t\t\t\t\t\t\t\t\t\t\t<a href=\"registrazione.php\" title=\"Registrati\">Registrati</a>\n";
		echo "\t\t\t\t\t\t\t\t\t\t</li>\n";
	}	
		
	echo "\t\t\t\t\t\t\t\t\t</ul>\n";		
	echo "\t\t\t\t\t\t\t\t</li>\n";	
	
	// POICHÈ CI SI STA RIFERENDO AD UN SITO PER IL COMMERCIO ONLINE, È STATO NECESSARIO INCLUDERE UN MECCANISMO PER TENERE TRACCIA DEI PRODOTTI INCLUSI NEL CARRELLO
	// PER DI PIÙ, ESSENDO UNA FUNZIONALITÀ DEDICATA AI SOLI CLIENTI DELLA PIATTAFORMA, SARÀ NECESSARIO NASCONDERLA NEL CASO IN CUI L'UTENTE SIA UNO DEI GESTORE O L'AMMINISTRATORE 
	if(!isset($_SESSION["id_Utente"]) || (isset($_SESSION["id_Utente"]) && $_SESSION["tipo_Utente"]=="C")) {
		
		// IL NUMERO DELLE OFFERTE ATTUALMENTE PRESENTI NEL CARRELLO SARANNO CALCOLATE MEDIANTE LO SCRIPT DI CUI SOTTO
		require_once("./calcolo_offerte_carrello.php");
		
		echo "\t\t\t\t\t\t\t\t<li class=\"voce_pannello\" onclick=\"gestisci_carrello_utente()\">\n";
		echo "\t\t\t\t\t\t\t\t\t<span class=\"freccia_tendina destra\" id=\"freccia_tendina_carrello\"></span>\n";
		echo "\t\t\t\t\t\t\t\t\t<span class=\"icona\">\n";
		
		if($num_prodotti_carrello)
			echo "\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/cart-flatbed-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
		else
			echo "\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/cart-shopping-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
		
		echo "\t\t\t\t\t\t\t\t\t</span>\n";
		echo "\t\t\t\t\t\t\t\t\t<ul class=\"menu_tendina carrello nascondi\" id=\"carrello_utente\">\n";
		echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
		echo "\t\t\t\t\t\t\t\t\t\t\t<span title=\"Il Mio Carrello\">Prodotti: ";
		
		
		echo $num_prodotti_carrello;
		
		echo "</span>\n";
		echo "\t\t\t\t\t\t\t\t\t\t</li>\n";	
		echo "\t\t\t\t\t\t\t\t\t\t<li>\n";									
		echo "\t\t\t\t\t\t\t\t\t\t\t<a href=\"riepilogo_carrello.php\" title=\"Riepilogo\">Riepilogo</a>\n";								
		echo "\t\t\t\t\t\t\t\t\t\t</li>\n";	
		echo "\t\t\t\t\t\t\t\t\t</ul>\n";	
		echo "\t\t\t\t\t\t\t\t</li>\n";
	}
	
	// INOLTRE, È STATA IMPLEMENTATA UNA COMPONENTE A TENDINA PER LA NAVIGAZIONE TRA LE VARIE PAGINE PUBBLICHE, DUNQUE VISIBILI ANCHE AI SEMPLICI VISITATORI
	echo "\t\t\t\t\t\t\t\t<li class=\"voce_pannello\" onclick=\"gestisci_barra_navigazione()\">\n";	
	echo "\t\t\t\t\t\t\t\t\t<span class=\"freccia_tendina destra\" id=\"freccia_tendina_navigazione\"></span>\n";							
	echo "\t\t\t\t\t\t\t\t\t<span class=\"icona\">\n";								
	echo "\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/bars-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";								
	echo "\t\t\t\t\t\t\t\t\t</span>\n";									
	echo "\t\t\t\t\t\t\t\t</li>\n";
	echo "\t\t\t\t\t\t\t</ul>\n";
	echo "\t\t\t\t\t\t</div>\n";	
	echo "\t\t\t\t\t</div>\n";
	echo "\t\t\t\t</div>\n";
	echo "\t\t\t\t<div class=\"oggetti_intestazione nascondi\" id=\"barra_navigazione\">\n";
	echo "\t\t\t\t\t<div class=\"oggetto_intestazione\">\n";			
	echo "\t\t\t\t\t\t<div class=\"barra_navigazione\">\n";
	echo "\t\t\t\t\t\t\t<ul class=\"menu_principale\">\n";
	echo "\t\t\t\t\t\t\t\t<li class=\"voce_menu\">\n";
	echo "\t\t\t\t\t\t\t\t\t<a href=\"index.php\" title=\"Home\">Home</a>\n";
	echo "\t\t\t\t\t\t\t\t</li>\n";					
	echo "\t\t\t\t\t\t\t\t<li class=\"voce_menu\" onclick=\"gestisci_menu_prodotti()\">\n";
	echo "\t\t\t\t\t\t\t\t\t<a title=\"Prodotti\">\n";
	echo "\t\t\t\t\t\t\t\t\t\tProdotti\n"; 
	echo "\t\t\t\t\t\t\t\t\t\t<span id=\"freccia_tendina_prodotti\" class=\"freccia_tendina giu\"></span>\n";
	echo "\t\t\t\t\t\t\t\t\t</a>\n";
	echo "\t\t\t\t\t\t\t\t\t<ul class=\"menu_tendina prodotti nascondi\" id=\"menu_tendina_prodotti\">\n";
	echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
	echo "\t\t\t\t\t\t\t\t\t\t\t<a href=\"anteprima_libri.php\" title=\"Libri\">Libri</a>\n";
	echo "\t\t\t\t\t\t\t\t\t\t</li>\n";	
	echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
	echo "\t\t\t\t\t\t\t\t\t\t\t<a href=\"anteprima_videogiochi.php\" title=\"Videogiochi\">Videogiochi</a>\n";
	echo "\t\t\t\t\t\t\t\t\t\t</li>\n";	
	echo "\t\t\t\t\t\t\t\t\t</ul>\n";
	echo "\t\t\t\t\t\t\t\t</li>\n";						
	echo "\t\t\t\t\t\t\t\t<li class=\"voce_menu\">\n";
	echo "\t\t\t\t\t\t\t\t\t<a href=\"chi_siamo.php\" title=\"Chi Siamo\">Chi Siamo</a>\n";	
	echo "\t\t\t\t\t\t\t\t</li>\n";		
	echo "\t\t\t\t\t\t\t\t<li class=\"voce_menu\">\n";
	echo "\t\t\t\t\t\t\t\t\t<a href=\"riepilogo_faq.php\" title=\"FAQ\">FAQ</a>\n"; // INSERIRE SCRIPT DI DESTINAZIONE PER FAQ	
	echo "\t\t\t\t\t\t\t\t</li>\n";									
	echo "\t\t\t\t\t\t\t\t<li class=\"voce_menu\">\n";
	echo "\t\t\t\t\t\t\t\t\t<a href=\"contatti.php\" title=\"Contatti\">Contatti</a>\n";	
	echo "\t\t\t\t\t\t\t\t</li>\n";
	echo "\t\t\t\t\t\t\t</ul>\n";	
	echo "\t\t\t\t\t\t</div>\n";
	echo "\t\t\t\t\t</div>\n";
	echo "\t\t\t\t</div>\n";
	echo "\t\t\t</div>\n";
	echo "\t\t</div>\n";
	
	// PER IMPEDIRE CHE CI SIA UNA SOVRAPPOSIZIONE DELL'INTESTAZIONE CON LE VARIE COMPONENTI SOTTOSTANTI, ABBIAMO DECISO DI INSERIRE UN SEPARATORE PER OFFRIRE UNA MAGGIORE FRUIBILITÀ DELLE SINGOLE PAGINE DELLA PIATTAFORMA
	echo "\t\t<p id=\"separatore_intestazione_sito\" class=\"separatore_intestazione_sito\"></p>\n";
?>