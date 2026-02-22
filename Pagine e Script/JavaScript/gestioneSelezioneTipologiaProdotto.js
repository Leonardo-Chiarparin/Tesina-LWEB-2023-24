// TALE SCRIPT CONTIENE UNA SERIE DI FUNZIONI CHE CONSENTONO DI INTERAGIRE CON LE SEZIONI "NASCOSTE" CONTENUTE ALL'INTERNO DELLA SCHERMATA DEDICATA ALLA REGISTRAZIONE E ALLA MODIFICA DEI SINGOLI ARTICOLI DA PARTE DELL'AMMINISTRATORE DELLA PIATTAFORMA 
// IN PARTICOLARE, È PREVISTO CHE, IN BASE ALLE CIRCOSTANZE, LE VARIE COMPONENTI APPAIANO E SCOMPAIANO MEDIANTE LA SOSTITUZIONE E L'APPLICAZIONE DI APPOSITE CLASSI CSS, LE QUALI, NEL NOSTRO CASO, SONO STATE NOMINATE, RISPETTIVAMENTE, "mostra" ("display: block") E "nascondi" ("display: none")
// PER DI PIÙ, QUALORA VENISSE PREMUTA UNA DELLE VOCI RELATIVE ALLA TIPOLOGIA DEL PRODOTTO, È PREVISTA LA RIMOZIONE DEI VALORI INSERITI E LA DISABILITAZIONE DI TUTTI QUEI CAMPI CHE SI RIFERISCONO AL "MENÙ" DELL'ENTITÀ (LIBRO O VIDEOGIOCHI) COINVOLTA 
function gestioneSelezioneTipologiaProdotto() {
	if(document.getElementById("radio_libro").checked==true) {
		if(document.getElementById("intestazione_categorie_libri").classList.contains("nascondi")) {
			document.getElementById("intestazione_categorie_libri").classList.remove("nascondi");
			document.getElementById("intestazione_categorie_libri").classList.add("mostra");
			document.getElementById("elenco_categorie_libri").classList.remove("nascondi");
			document.getElementById("elenco_categorie_libri").classList.add("mostra");
			
			document.getElementById("intestazione_autore_libro").classList.remove("nascondi");
			document.getElementById("intestazione_autore_libro").classList.add("mostra");
			document.getElementById("campi_autore_libro").classList.remove("nascondi");
			document.getElementById("campi_autore_libro").classList.add("mostra");
			
			document.getElementById("intestazione_coautore_libro").classList.remove("nascondi");
			document.getElementById("intestazione_coautore_libro").classList.add("mostra");
			document.getElementById("campi_coautore_libro").classList.remove("nascondi");
			document.getElementById("campi_coautore_libro").classList.add("mostra");
			
			document.getElementById("intestazione_piattaforme_videogiochi").classList.remove("mostra");
			document.getElementById("intestazione_piattaforme_videogiochi").classList.add("nascondi");
			document.getElementById("elenco_piattaforme_videogiochi").classList.remove("mostra");
			document.getElementById("elenco_piattaforme_videogiochi").classList.add("nascondi");
			
			var checkboxes_piattaforme_videogiochi=document.getElementById("elenco_piattaforme_videogiochi").getElementsByTagName("input");
			for(var i=0; i<checkboxes_piattaforme_videogiochi.length; i++) {
				checkboxes_piattaforme_videogiochi[i].checked=false;
			}
			
			document.getElementById("intestazione_generi_videogiochi").classList.remove("mostra");
			document.getElementById("intestazione_generi_videogiochi").classList.add("nascondi");
			document.getElementById("elenco_generi_videogiochi").classList.remove("mostra");
			document.getElementById("elenco_generi_videogiochi").classList.add("nascondi");
			
			var checkboxes_generi_videogiochi=document.getElementById("elenco_generi_videogiochi").getElementsByTagName("input");
			for(var i=0; i<checkboxes_generi_videogiochi.length; i++) {
				checkboxes_generi_videogiochi[i].checked=false;
			}
			
			document.getElementById("intestazione_casa_produzione_videogioco").classList.remove("mostra");
			document.getElementById("intestazione_casa_produzione_videogioco").classList.add("nascondi");
			document.getElementById("campi_casa_produzione_videogioco").classList.remove("mostra");
			document.getElementById("campi_casa_produzione_videogioco").classList.add("nascondi");
			
			document.getElementById("casa_produzione_videogioco").value='';
		}
	}
	
	if(document.getElementById("radio_videogioco").checked==true) {
		if(document.getElementById("intestazione_piattaforme_videogiochi").classList.contains("nascondi")) {
			document.getElementById("intestazione_piattaforme_videogiochi").classList.remove("nascondi");
			document.getElementById("intestazione_piattaforme_videogiochi").classList.add("mostra");
			document.getElementById("elenco_piattaforme_videogiochi").classList.remove("nascondi");
			document.getElementById("elenco_piattaforme_videogiochi").classList.add("mostra");
			
			document.getElementById("intestazione_generi_videogiochi").classList.remove("nascondi");
			document.getElementById("intestazione_generi_videogiochi").classList.add("mostra");
			document.getElementById("elenco_generi_videogiochi").classList.remove("nascondi");
			document.getElementById("elenco_generi_videogiochi").classList.add("mostra");
			
			document.getElementById("intestazione_casa_produzione_videogioco").classList.remove("nascondi");
			document.getElementById("intestazione_casa_produzione_videogioco").classList.add("mostra");
			document.getElementById("campi_casa_produzione_videogioco").classList.remove("nascondi");
			document.getElementById("campi_casa_produzione_videogioco").classList.add("mostra");
			
			document.getElementById("intestazione_categorie_libri").classList.remove("mostra");
			document.getElementById("intestazione_categorie_libri").classList.add("nascondi");
			document.getElementById("elenco_categorie_libri").classList.remove("mostra");
			document.getElementById("elenco_categorie_libri").classList.add("nascondi");
			
			var checkboxes_categorie_libri=document.getElementById("elenco_categorie_libri").getElementsByTagName("input");
			for(var i=0; i<checkboxes_categorie_libri.length; i++) {
				checkboxes_categorie_libri[i].checked=false;
			}
			
			document.getElementById("intestazione_autore_libro").classList.remove("mostra");
			document.getElementById("intestazione_autore_libro").classList.add("nascondi");
			document.getElementById("campi_autore_libro").classList.remove("mostra");
			document.getElementById("campi_autore_libro").classList.add("nascondi");
			
			document.getElementById("nome_autore_libro").value='';
			document.getElementById("cognome_autore_libro").value='';
			
			document.getElementById("intestazione_coautore_libro").classList.remove("mostra");
			document.getElementById("intestazione_coautore_libro").classList.add("nascondi");
			document.getElementById("campi_coautore_libro").classList.remove("mostra");
			document.getElementById("campi_coautore_libro").classList.add("nascondi");
			
			document.getElementById("nome_coautore_libro").value='';
			document.getElementById("cognome_coautore_libro").value='';
			
		}
	}
}