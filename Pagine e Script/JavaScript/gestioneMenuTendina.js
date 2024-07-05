function gestisci_menu_utente() {
	if(document.getElementById("menu_utente").classList.contains("nascondi")){
		document.getElementById("freccia_tendina_utente").classList.remove("destra");
		document.getElementById("freccia_tendina_utente").classList.add("giu");
		document.getElementById("menu_utente").classList.remove("nascondi");
		document.getElementById("menu_utente").classList.add("mostra");
	}
	else {
		document.getElementById("freccia_tendina_utente").classList.remove("giu");
		document.getElementById("freccia_tendina_utente").classList.add("destra");
		document.getElementById("menu_utente").classList.remove("mostra");
		document.getElementById("menu_utente").classList.add("nascondi");
	}
}

function gestisci_carrello_utente() {
	if(document.getElementById("carrello_utente").classList.contains("nascondi")){
		document.getElementById("freccia_tendina_carrello").classList.remove("destra");
		document.getElementById("freccia_tendina_carrello").classList.add("giu");
		document.getElementById("carrello_utente").classList.remove("nascondi");
		document.getElementById("carrello_utente").classList.add("mostra");
	}
	else {
		document.getElementById("freccia_tendina_carrello").classList.remove("giu");
		document.getElementById("freccia_tendina_carrello").classList.add("destra");
		document.getElementById("carrello_utente").classList.remove("mostra");
		document.getElementById("carrello_utente").classList.add("nascondi");
	}
}

function gestisci_barra_navigazione() {
	if(document.getElementById("barra_navigazione").classList.contains("nascondi")){
		document.getElementById("freccia_tendina_navigazione").classList.remove("destra");
		document.getElementById("freccia_tendina_navigazione").classList.add("giu");
		document.getElementById("barra_navigazione").classList.remove("nascondi");
		document.getElementById("barra_navigazione").classList.add("mostra");
	}
	else {
		document.getElementById("freccia_tendina_navigazione").classList.remove("giu");
		document.getElementById("freccia_tendina_navigazione").classList.add("destra");
		document.getElementById("barra_navigazione").classList.remove("mostra");
		document.getElementById("barra_navigazione").classList.add("nascondi");
		
		if(document.getElementById("menu_tendina_prodotti").classList.contains("mostra")){
			document.getElementById("freccia_tendina_prodotti").classList.remove("su");
			document.getElementById("freccia_tendina_prodotti").classList.add("giu");
			document.getElementById("menu_tendina_prodotti").classList.remove("mostra");
			document.getElementById("menu_tendina_prodotti").classList.add("nascondi");
		}
	}
}

function gestisci_menu_prodotti() {
	if(document.getElementById("menu_tendina_prodotti").classList.contains("nascondi")){
		document.getElementById("freccia_tendina_prodotti").classList.remove("giu");
		document.getElementById("freccia_tendina_prodotti").classList.add("su");
		document.getElementById("menu_tendina_prodotti").classList.remove("nascondi");
		document.getElementById("menu_tendina_prodotti").classList.add("mostra");
	}
	else {
		document.getElementById("freccia_tendina_prodotti").classList.remove("su");
		document.getElementById("freccia_tendina_prodotti").classList.add("giu");
		document.getElementById("menu_tendina_prodotti").classList.remove("mostra");
		document.getElementById("menu_tendina_prodotti").classList.add("nascondi");
	}
}