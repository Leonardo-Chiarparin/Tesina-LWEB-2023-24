<?php
	// LO SCRIPT PERMETTE DI INSERIRE O DI INCREMENTARE IL QUANTITATIVO DI UNA CERTA PROPOSTA DI VENDITA IN BASE ALL'ESITO DEI CONFRONTI EFFETTUATI ALL'INTERNO DEL FILE "integrazione_contenuto_carrello.php" 
	
	// NEL CASO IN CUI CI SIA UNA CORRISPONDENZA, SI PROCEDERÀ CON L'ADEGUAMENTO DEL NUMERO DI PEZZI INERENTI ALLA VOCE PRESENTE NEL FILE XML DEDICATO AI CARRELLI DEI CLIENTI
	if($offerta_presente) {
		$nuovo_quantitativo=$docCarrelli->createElement("quantitativo", intval($offerta_interessata->getElementsByTagName("quantitativo")->item(0)->textContent)+$offerta_carrello_cookie["quantitativo"]);
	
		$offerta_interessata->replaceChild($nuovo_quantitativo, $offerta_interessata->getElementsByTagName("quantitativo")->item(0));
	}
	else {
		// D'ALTRO CANTO, SI PROCEDERÀ CON LA CREAZIONE DI UN ELEMENTO CHE VERRÀ POI INSERITO ALL'INTERNO DEL CARRELLO DI INTERESSE
		$nuova_offerta_carrello=$docCarrelli->createElement("offerta");
		$nuova_offerta_carrello->setAttribute("id", $offerta_carrello_cookie["id"]);
		$nuova_offerta_carrello->setAttribute("idProdotto", $offerta_carrello_cookie["idProdotto"]);
		
		$nuova_offerta_carrello->appendChild($docCarrelli->createElement("prezzoContabile", $offerta_carrello_cookie["prezzoContabile"]));
		
		if(array_key_exists("scontoATempo", $offerta_carrello_cookie) || array_key_exists("scontoFuturo", $offerta_carrello_cookie)) {
			$sconto=$docCarrelli->createElement("sconto");
			
			if(array_key_exists("scontoATempo", $offerta_carrello_cookie)) {
				$scontoATempo=$docCarrelli->createElement("scontoATempo");
				$scontoATempo->setAttribute("percentuale", $offerta_carrello_cookie["scontoATempo"]);
				$scontoATempo->setAttribute("inizioApplicazione", $offerta_carrello_cookie["inizioApplicazione"]);
				$scontoATempo->setAttribute("fineApplicazione", $offerta_carrello_cookie["fineApplicazione"]);
				
				$sconto->appendChild($scontoATempo);
			}
			else {
				$scontoFuturo=$docCarrelli->createElement("scontoFuturo");
				$scontoFuturo->setAttribute("percentuale", $offerta_carrello_cookie["scontoFuturo"]);
				$scontoFuturo->setAttribute("inizioApplicazione", $offerta_carrello_cookie["inizioApplicazione"]);
				$scontoFuturo->setAttribute("fineApplicazione", $offerta_carrello_cookie["fineApplicazione"]);
				
				$sconto->appendChild($scontoFuturo);
			}
			
			$nuova_offerta_carrello->appendChild($sconto);
			
		}

		$nuova_offerta_carrello->appendChild($docCarrelli->createElement("quantitativo", $offerta_carrello_cookie["quantitativo"]));
		
		if(array_key_exists("numeroCrediti", $offerta_carrello_cookie)) {
			$bonus=$docCarrelli->createElement("bonus");
			$bonus->appendChild($docCarrelli->createElement("numeroCrediti", $offerta_carrello_cookie["numeroCrediti"]));
			$nuova_offerta_carrello->appendChild($bonus);
		}
		
		$carrello->appendChild($nuova_offerta_carrello);
	}
	
	// IN AMBEDUE LE CASISTICHE PRESENTATE, BISOGNERÀ DIMINUIRE DELLO STESSO VALORE QUELLO DELLA PROPOSTA DI VENDITA COLLOCATA ALL'INTERNO DEL DOCUMENTO RAFFIGURANTE IL CATALOGO DELLA PIATTAFORMA
	// PER INDIVIDUARE LA VOCE INTERESSATA, SARÀ SUFFICIENTE SCANSIONARE IL RELATIVO FILE PER INDIVIDUARE L'OFFERTA CHE HA L'IDENTIFICATORE DI QUELLA CHE È STATA APPENA INSERITA O MODIFICATA
	for($k=0; $k<$offerte->length; $k++) {
		$offerta=$offerte->item($k);
		
		if($offerta_presente) {
			if($offerta->getAttribute("id")==$offerta_interessata->getAttribute("id") || $offerta->getAttribute("idProdotto")==$offerta_interessata->getAttribute("idProdotto") ) {
				$nuovo_quantitativo=$docOfferte->createElement("quantitativo", intval($offerta->getElementsByTagName("quantitativo")->item(0)->textContent)-$offerta_carrello_cookie["quantitativo"]);
				
				// PER LIMITARE DEI PROBLEMI INERENTI ALLA CONCORRENZA, È STATO NECESSARIO EFFETTUARE IL SUCCESSIVO CONTROLLO
				if($nuovo_quantitativo->textContent>=0)
					$offerta->replaceChild($nuovo_quantitativo, $offerta->getElementsByTagName("quantitativo")->item(0));
				
				// UNA VOLTA PROCESSATO L'ELEMENTO DI INTERESSE, SI PROCEDERÀ ALLA SUA RIMOZIONE (unset(...)) E AL CONSEGUENTE RIORDINAMENTO (array_values(...)) DEL VETTORE IN CUI ERA CONTENUTO
				unset($carrello_Offerte[array_search($offerta_carrello_cookie, $carrello_Offerte)]);
				
				$carrello_Offerte=array_values($carrello_Offerte);
				
				break;
			}
		}
		else {
			if($offerta->getAttribute("id")==$nuova_offerta_carrello->getAttribute("id") || $offerta->getAttribute("idProdotto")==$nuova_offerta_carrello->getAttribute("idProdotto")) {
				$nuovo_quantitativo=$docOfferte->createElement("quantitativo", intval($offerta->getElementsByTagName("quantitativo")->item(0)->textContent)-$offerta_carrello_cookie["quantitativo"]);
				
				// ***
				if($nuovo_quantitativo->textContent>=0)
					$offerta->replaceChild($nuovo_quantitativo, $offerta->getElementsByTagName("quantitativo")->item(0));
				
				// ***
				unset($carrello_Offerte[array_search($offerta_carrello_cookie, $carrello_Offerte)]);
				
				$carrello_Offerte=array_values($carrello_Offerte);
				
				break;
			}
		}
	}
	
	// GIUNTI A QUESTO PUNTO, SI PROCEDE CON IL SALVATAGGIO DEL CONTENUTO DEI VARI DOCUMENTI
	if($docCarrelli->schemaValidate("../../XML/Schema/Carrelli_Clienti.xsd") && $docOfferte->schemaValidate("../../XML/Schema/Offerte.xsd")){
		
		// PER UNA STAMPA OTTIMALE, SONO STATI APPLICATI UNA SERIE DI METODI PER LA FORMATTAZIONE DEL RISULTATO PRODOTTO
		$docCarrelli->preserveWhiteSpace = false;
		$docCarrelli->formatOutput = true;
		$docCarrelli->save("../../XML/Carrelli_Clienti.xml");
		
		// ***
		$docOfferte->preserveWhiteSpace = false;
		$docOfferte->formatOutput = true;
		$docOfferte->save("../../XML/Offerte.xml");
	}
	else {
		
		// ***
		setcookie("errore_Validazione", true);
		
		header("Location: index.php");
	}
	
?>