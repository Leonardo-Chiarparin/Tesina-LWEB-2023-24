// TALE SCRIPT CONTIENE UNA SERIE DI FUNZIONI CHE CONSENTONO DI INTERAGIRE CON LE SEZIONI FACOLTATIVE CONTENUTE ALL'INTERNO DELLA SCHERMATA DEDICATA ALLA CREAZIONE E ALLA MODIFICA DELLE SINGOLE PROPOSTE DI VENDITA DA PARTE DEI GESTORI DELLA PIATTAFORMA 
// IN PARTICOLARE, È PREVISTO CHE, IN BASE ALLE CIRCOSTANZE, LE VARIE COMPONENTI APPAIANO E SCOMPAIANO MEDIANTE LA SOSTITUZIONE E L'APPLICAZIONE DI APPOSITE CLASSI CSS, LE QUALI, NEL NOSTRO CASO, SONO STATE NOMINATE, RISPETTIVAMENTE, "mostra" ("display: block") E "nascondi" ("display: none")
// ALLO SCOPO DI ARRICHIRE LA GRAFICA PROPOSTA A SCHERMO, È STATA INSERITA UNA FRECCIA CHE, OLTRE A CAMBIARE DIREZIONE IN RELAZIONE ALLO STATO ATTUALE DELLE "TENDINE", È STATA INTERAMENTE REALIZZATA MEDIANTE CLAUSOLE "border"  
// PER DI PIÙ, QUALORA QUEST'ULTIMA VENISSE PREMUTA PER UNA SECONDA VOLTA, È PREVISTA LA RIMOZIONE DEI VALORI INSERITI E LA DISABILITAZIONE DI TUTTI QUEI CAMPI CHE SI RIFERISCONO AL "MENÙ" DELL'ENTITÀ (SCONTI O BONUS) COINVOLTA
function gestioneMenuSconti() {
	if(document.getElementById("elenco_sconti").classList.contains("nascondi")) {
		document.getElementById("elenco_sconti").classList.remove("nascondi");
		document.getElementById("elenco_sconti").classList.add("mostra");
		document.getElementById("freccia_menu_sconti").classList.remove("destra");
		document.getElementById("freccia_menu_sconti").classList.add("giu");
		document.getElementById("intestazione_sezione_sconti").style.marginBottom="1.5em";
	}
	else {
		document.getElementById("elenco_sconti").classList.remove("mostra");
		document.getElementById("elenco_sconti").classList.add("nascondi");
		document.getElementById("freccia_menu_sconti").classList.remove("giu");
		document.getElementById("freccia_menu_sconti").classList.add("destra");
		document.getElementById("intestazione_sezione_sconti").style.marginBottom="0.5em";
		
		document.getElementById("radio_sconto_a_tempo").checked=false;
		document.getElementById("radio_sconto_futuro").checked=false;
		
		document.getElementById("sconto_a_tempo").disabled=true;
		document.getElementById("sconto_futuro").disabled=true;
		document.getElementById("inizio_Applicazione").disabled=true;
		document.getElementById("fine_Applicazione").disabled=true;
		
		document.getElementById("sconto_a_tempo").value='';
		document.getElementById("sconto_futuro").value='';
		document.getElementById("inizio_Applicazione").value='';
		document.getElementById("fine_Applicazione").value='';
	}
}

function gestioneMenuBonus() {
	if(document.getElementById("elenco_bonus").classList.contains("nascondi")) {
		document.getElementById("elenco_bonus").classList.remove("nascondi");
		document.getElementById("elenco_bonus").classList.add("mostra");
		document.getElementById("freccia_menu_bonus").classList.remove("destra");
		document.getElementById("freccia_menu_bonus").classList.add("giu");
		document.getElementById("intestazione_sezione_bonus").style.marginBottom="1.5em";
	}
	else {
		document.getElementById("elenco_bonus").classList.remove("mostra");
		document.getElementById("elenco_bonus").classList.add("nascondi");
		document.getElementById("freccia_menu_bonus").classList.remove("giu");
		document.getElementById("freccia_menu_bonus").classList.add("destra");
		document.getElementById("intestazione_sezione_bonus").style.marginBottom="0.5em";
		
		document.getElementById("radio_bonus").checked=false;
		
		document.getElementById("bonus").disabled=true;
		
		document.getElementById("bonus").value='';
	}
}

function gestioneSelezioneScontiBonus() {
	if(document.getElementById("radio_sconto_a_tempo").checked==true){
		document.getElementById("sconto_a_tempo").disabled=false;
		document.getElementById("sconto_futuro").disabled=true;
		document.getElementById("sconto_futuro").value='';
		document.getElementById("inizio_Applicazione").disabled=false;
		document.getElementById("fine_Applicazione").disabled=false;
	}
	
	if(document.getElementById("radio_sconto_futuro").checked==true){
		document.getElementById("sconto_futuro").disabled=false;
		document.getElementById("sconto_a_tempo").disabled=true;
		document.getElementById("sconto_a_tempo").value='';
		document.getElementById("inizio_Applicazione").disabled=false;
		document.getElementById("fine_Applicazione").disabled=false;
	}
	
	if(document.getElementById("radio_bonus").checked==true){
		document.getElementById("bonus").disabled=false;
	}
}