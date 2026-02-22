// TALE SCRIPT CONTIENE LA FUNZIONE CHE CONSENTE DI INTERAGIRE CON LA COMPONENTE DEDICATA ALLA SELEZIONE DI UN CERTO ORDINAMENTO E CONTENUTA ALL'INTERNO DI TUTTE QUELLE PAGINE IN CUI VIENE RIPORTATO A SCHERMO UN ELENCO DELLE PROPOSTE DI VENDITA CHE SI RIFERISCONO A DEGLI ARTICOLI CON CERTE PROPRIETÀ
// IN PARTICOLARE, È PREVISTO CHE, IN BASE ALLE CIRCOSTANZE, LE VARIE COMPONENTI APPAIANO E SCOMPAIANO MEDIANTE LA SOSTITUZIONE E L'APPLICAZIONE DI APPOSITE CLASSI CSS, LE QUALI, NEL NOSTRO CASO, SONO STATE NOMINATE, RISPETTIVAMENTE, "mostra" ("display: block") E "nascondi" ("display: none")
// ALLO SCOPO DI ARRICHIRE LA GRAFICA PROPOSTA A SCHERMO, È STATA INSERITA UNA FRECCIA CHE, OLTRE A CAMBIARE DIREZIONE IN RELAZIONE ALLO STATO ATTUALE DELLE "TENDINE", È STATA INTERAMENTE REALIZZATA MEDIANTE CLAUSOLE "border"  
function gestioneMenuOrdinamento() {
	if(document.getElementById("menu_ordinamento").classList.contains("nascondi")) {
		document.getElementById("menu_ordinamento").classList.remove("nascondi");
		document.getElementById("menu_ordinamento").classList.add("mostra");
		document.getElementById("freccia_tendina_ordinamento").classList.remove("giu");
		document.getElementById("freccia_tendina_ordinamento").classList.add("su");
	}
	else {
		document.getElementById("menu_ordinamento").classList.remove("mostra");
		document.getElementById("menu_ordinamento").classList.add("nascondi");
		document.getElementById("freccia_tendina_ordinamento").classList.remove("su");
		document.getElementById("freccia_tendina_ordinamento").classList.add("giu");
	}
}