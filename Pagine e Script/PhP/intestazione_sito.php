<?php
	echo "<div class=\"intestazione_sito\">\n";
	echo "\t\t\t<div class=\"container_intestazione\">\n";
	echo "\t\t\t\t<div class=\"oggetti_intestazione\">\n";
	echo "\t\t\t\t\t<div class=\"oggetto_intestazione\">\n";
	echo "\t\t\t\t\t\t<div class=\"logo\">\n";
	echo "\t\t\t\t\t\t\t<img src=\"../../Immagini/Logo_LEV.png\" alt=\"LEV: Libri &amp; Videogiochi\" title=\"LEV: Libri &amp; Videogiochi\" />\n";
	echo "\t\t\t\t\t\t</div>\n";
	echo "\t\t\t\t\t</div>\n";
	echo "\t\t\t\t\t<div class=\"oggetto_intestazione\">\n";
	echo "\t\t\t\t\t\t<div class=\"barra_ricerca\">\n";
	echo "\t\t\t\t\t\t\t<form method=\"get\" action=\"\">\n"; // INSERIRE SCRIPT DI DESTINAZIONE
	echo "\t\t\t\t\t\t\t\t<div class=\"container_input\">\n";
	echo "\t\t\t\t\t\t\t\t\t<span>Ricerca:</span>\n";							
	echo "\t\t\t\t\t\t\t\t\t<input type=\"text\" name=\"prodottoRicercato\" value='";
	
	if(isset($_GET['prodottoRicercato']))
		echo $_GET['prodottoRicercato']; 
	else 
		echo '';
	echo "' />\n";								
									
	echo "\t\t\t\t\t\t\t\t\t<span class=\"pulsante_ricerca\">\n";
	echo "\t\t\t\t\t\t\t\t\t\t<button><img src=\"../../Immagini/magnifying-glass-solid.svg\" alt=\"Lente Di Ricerca\" /></button>\n";
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
	
	if(isset($_SESSION["idUtente"]))
		echo "\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/user-check-solid.svg\" alt=\"Utente Identificato\" />\n";
	else 
		echo "\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/user-xmark-solid.svg\" alt=\"Utente Non Identificato\" />\n";
	
	
	echo "\t\t\t\t\t\t\t\t\t</span>\n";
	echo "\t\t\t\t\t\t\t\t\t<ul class=\"menu_tendina utente nascondi\" id=\"menu_utente\">\n";	
	
	if(isset($_SESSION["idUtente"])){
		echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
		echo "\t\t\t\t\t\t\t\t\t\t\t<span title=\"Il Mio Username\">".$_SESSION["usernameUtente"]."</span>\n";
		echo "\t\t\t\t\t\t\t\t\t\t</li>\n";
		echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
		echo "\t\t\t\t\t\t\t\t\t\t\t<a href=\"account.php\" title=\"Account\">Account</a>\n";
		echo "\t\t\t\t\t\t\t\t\t\t</li>\n";	
		echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
		echo "\t\t\t\t\t\t\t\t\t\t\t<a href=\"area_riservata.php\" title=\"Area Riservata\">Area Riservata</a>\n";
		echo "\t\t\t\t\t\t\t\t\t\t</li>\n";
	echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
		echo "\t\t\t\t\t\t\t\t\t\t\t<a href=\"\" title=\"Disconnettiti\">Disconnettiti</a>\n";
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
	echo "\t\t\t\t\t\t\t\t<li class=\"voce_pannello\" onclick=\"gestisci_carrello_utente()\">\n";
	echo "\t\t\t\t\t\t\t\t\t<span class=\"freccia_tendina destra\" id=\"freccia_tendina_carrello\"></span>\n";
	echo "\t\t\t\t\t\t\t\t\t<span class=\"icona\">\n";
	echo "\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/cart-shopping-solid.svg\" alt=\"Carrello Utente\" />\n";
	echo "\t\t\t\t\t\t\t\t\t</span>\n";
	echo "\t\t\t\t\t\t\t\t\t<ul class=\"menu_tendina carrello nascondi\" id=\"carrello_utente\">\n";
	echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
	echo "\t\t\t\t\t\t\t\t\t\t\t<span title=\"Il Mio Carrello\">Prodotti: ";
	
	if(isset($_SESSION["numeroProdottiCarrello"]))
		echo $_SESSION["numeroProdottiCarrello"];
	else
		echo "0";
	
	echo "</span>\n";
	echo "\t\t\t\t\t\t\t\t\t\t</li>\n";	
	echo "\t\t\t\t\t\t\t\t\t\t<li>\n";									
	echo "\t\t\t\t\t\t\t\t\t\t\t<a href=\"carrello.php\" title=\"Riepilogo\">Riepilogo</a>\n";								
	echo "\t\t\t\t\t\t\t\t\t\t</li>\n";	
	echo "\t\t\t\t\t\t\t\t\t</ul>\n";	
	echo "\t\t\t\t\t\t\t\t</li>\n";
	echo "\t\t\t\t\t\t\t\t<li class=\"voce_pannello\" onclick=\"gestisci_barra_navigazione()\">\n";	
	echo "\t\t\t\t\t\t\t\t\t<span class=\"freccia_tendina destra\" id=\"freccia_tendina_navigazione\"></span>\n";							
	echo "\t\t\t\t\t\t\t\t\t<span class=\"icona\">\n";								
	echo "\t\t\t\t\t\t\t\t\t\t<img src=\"../../Immagini/bars-solid.svg\" alt=\"Menù Utente\" />\n";								
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
	echo "\t\t\t\t\t\t\t\t\t\t\t<a href=\"\" title=\"Libri\">Libri</a>\n"; // INSERIRE SCRIPT DI DESTINAZIONE CON CATEGORIA = LIBRI
	echo "\t\t\t\t\t\t\t\t\t\t</li>\n";	
	echo "\t\t\t\t\t\t\t\t\t\t<li>\n";
	echo "\t\t\t\t\t\t\t\t\t\t\t<a href=\"\" title=\"Videogiochi\">Videogiochi</a>\n"; // INSERIRE SCRIPT DI DESTINAZIONE CON CATEGORIA = VIDEOGIOCHI
	echo "\t\t\t\t\t\t\t\t\t\t</li>\n";	
	echo "\t\t\t\t\t\t\t\t\t</ul>\n";
	echo "\t\t\t\t\t\t\t\t</li>\n";						
	echo "\t\t\t\t\t\t\t\t<li class=\"voce_menu\">\n";
	echo "\t\t\t\t\t\t\t\t\t<a href=\"chi_siamo.php\" title=\"Chi Siamo\">Chi Siamo</a>\n"; // INSERIRE SCRIPT DI DESTINAZIONE PER CHI SIAMO	
	echo "\t\t\t\t\t\t\t\t</li>\n";		
	echo "\t\t\t\t\t\t\t\t<li class=\"voce_menu\">\n";
	echo "\t\t\t\t\t\t\t\t\t<a href=\"\" title=\"FAQ\">FAQ</a>\n"; // INSERIRE SCRIPT DI DESTINAZIONE PER FAQ	
	echo "\t\t\t\t\t\t\t\t</li>\n";									
	echo "\t\t\t\t\t\t\t\t<li class=\"voce_menu\">\n";
	echo "\t\t\t\t\t\t\t\t\t<a href=\"contatti.php\" title=\"Contatti\">Contatti</a>\n"; // INSERIRE SCRIPT DI DESTINAZIONE PER CONTATTI	
	echo "\t\t\t\t\t\t\t\t</li>\n";
	echo "\t\t\t\t\t\t\t</ul>\n";	
	echo "\t\t\t\t\t\t</div>\n";
	echo "\t\t\t\t\t</div>\n";
	echo "\t\t\t\t</div>\n";
	echo "\t\t\t</div>\n";
	echo "\t\t</div>\n";
?>