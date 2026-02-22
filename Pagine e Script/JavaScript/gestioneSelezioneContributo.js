// TALE SCRIPT CONTIENE UNA SERIE DI FUNZIONI CHE CONSENTONO DI INTERAGIRE CON LE SEZIONI CONTENUTE ALL'INTERNO DELLA SCHERMATA DEDICATA ALLA PUBBLICAZIONE DI UNA NUOVA FAQ DA PARTE DELL'AMMINISTRATORE DELLA PIATTAFORMA 
// IN PARTICOLARE, QUALORA VENISSE PREMUTA UNA DELLE VOCI RELATIVE AL CONTRIBUTO CHE SI VUOLE ELEVARE O COMPILARE, È PREVISTA LA RIMOZIONE DEI VALORI INSERITI E LA DISABILITAZIONE DI TUTTI QUEI CAMPI CHE SI RIFERISCONO ALLE LORO CONTROPARTI 
function gestioneSelezioneContributo() {
	if(document.getElementById("radio_nuovo_intervento").checked==true) {
		document.getElementById("testo_nuovo_intervento").disabled=false;
	}
	else {
		document.getElementById("testo_nuovo_intervento").disabled=true;
		
		document.getElementById("testo_nuovo_intervento").value='';
	}
}

function gestioneSelezioneDiscussione() {
	if(document.getElementById("radio_nuova_discussione").checked==true) {
		document.getElementById("titolo_nuova_discussione").disabled=false;
		document.getElementById("descrizione_nuova_discussione").disabled=false;
	}
	else {
		document.getElementById("titolo_nuova_discussione").disabled=true;
		document.getElementById("descrizione_nuova_discussione").disabled=true;
		
		document.getElementById("titolo_nuova_discussione").value='';
		document.getElementById("descrizione_nuova_discussione").value='';
	}
}