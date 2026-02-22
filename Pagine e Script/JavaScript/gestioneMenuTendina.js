// TALE SCRIPT CONTIENE UNA SERIE DI FUNZIONI CHE CONSENTONO DI INTERAGIRE CON I VARI MENÙ CONTENUTI ALL'INTERNO DELLA BARRA CHE COMPONE L'INTESTAZIONE CARATTERISTICA DEL SITO
// IN PARTICOLARE, È PREVISTO CHE, IN BASE ALLE CIRCOSTANZE, LE VARIE COMPONENTI APPAIANO E SCOMPAIANO MEDIANTE LA SOSTITUZIONE E L'APPLICAZIONE DI APPOSITE CLASSI CSS, LE QUALI, NEL NOSTRO CASO, SONO STATE NOMINATE, RISPETTIVAMENTE, "mostra" ("display: block") E "nascondi" ("display: none")
// ALLO SCOPO DI ARRICHIRE LA GRAFICA PROPOSTA A SCHERMO, È STATA INSERITA UNA FRECCIA CHE, OLTRE A CAMBIARE DIREZIONE IN RELAZIONE ALLO STATO ATTUALE DELLE TENDINE, È STATA INTERAMENTE REALIZZATA MEDIANTE CLAUSOLE "border"  
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
		document.getElementById("separatore_intestazione_sito").classList.add("estendi");
	}
	else {
		document.getElementById("freccia_tendina_navigazione").classList.remove("giu");
		document.getElementById("freccia_tendina_navigazione").classList.add("destra");
		document.getElementById("barra_navigazione").classList.remove("mostra");
		document.getElementById("barra_navigazione").classList.add("nascondi");
		document.getElementById("separatore_intestazione_sito").classList.remove("estendi");
		
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