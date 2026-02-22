<?php
	// LO SCRIPT PERMETTE DI CALCOLARE LA MEDIA DELLE VALUTAZIONI RICEVUTE DAI SINGOLI INTERVENTI AL SOLO SCOPO DI DEFINIRNE IL GRADO DI SUPPORTO E DI UTILITÀ PER GLI UTENTI DEL SITO
	$num_valutazioni=$intervento->getElementsByTagName("valutazione")->length;

	$media_supporto=$media_utilita=0;
	
	if($num_valutazioni!=0){
		
		// PER OGNUNO DEI CONTRIBUTI RELATIVI AD UNA CERTA DISCUSSIONE, SI DOVRÀ EFFETTUARE UNA SOMMA PARZIALE DEI VOTI RELATIVI AI DUE PARAMETRI CITATI IN PRECEDENZA 
		for($o=0; $o<$intervento->getElementsByTagName("valutazione")->length; $o++) {
			$valutazione=$intervento->getElementsByTagName("valutazione")->item($o);
			
			$media_supporto=$media_supporto+intval($valutazione->getAttribute("supporto"));
			$media_utilita=$media_utilita+intval($valutazione->getAttribute("utilita"));
		}
		
		// UNA VOLTA PORTATI A TERMINE I PASSI DI CUI SOPRA, BISOGNERÀ DIVIDERE OGNI VALORE OTTENUTO DALLA PRECEDENTE SCANSIONE CON L'AMMONTARE DELLE VALUTAZIONI E, IN SEGUITO, FORMATTARE I VARI RISULTATI IN MODO TALE CHE POSSANO PRESENTARE UNA SOLA CIFRA DECIMALE
		$media_supporto=number_format($media_supporto/$num_valutazioni, 1,".","");
		$media_utilita=number_format($media_utilita/$num_valutazioni, 1,".","");
	}
?>