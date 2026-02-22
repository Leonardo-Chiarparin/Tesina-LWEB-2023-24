<?php
	// COSÌ COME PER IL MECCANISMO PER LA REGISTRAZIONE DEI VARI CLIENTI, È STATO RITENUTO OPPORTUNO SEPARARE, DATA LA LUNGHEZZA, IL SEGUENTE SCRIPT DA QUELLO CONTENENTE IL RELATIVO MODULO. PER DI PIÙ, DATO IL NUMERO (PARECCHIO ELEVATO MA NON COMPLETO) DI CASISTICHE TRATTATE DAL SEGUENTE CODICE, ABBIAMO POTUTO INCLUDERLO LADDOVE CI È SEMBRATO CONVENIENTE 
	// DATA LA VARIETÀ DELLE CASISTICHE CHE POSSONO MANIFESTARE, ABBIAMO DEFINITO UNA GERARCHIA DI CONTROLLI PER GESTIRE AL MEGLIO LE STAMPE DEI VARI MESSAGGI DI POPUP, I QUALI, MEDIANTE UNA SIMILE IMPOSTAZIONE, SARANNO PRESENTATI SINGOLARMENTE AI SOGGETTI INTERESSATI
	if(isset($campi_vuoti) && $campi_vuoti) { 
		// IN OCCASIONE DEL POSSIBILE RICARICAMENTO DELLA PAGINA, SI PROCEDE CON LA RIMOZIONE DEL FLAG ALLO SCOPO DI NON RIPROPORRE LA MEDESIMA NOTIFCA 
		$campi_vuoti=false;
		
		echo "<div class=\"error_message\">\n";
		echo "\t\t\t<div class=\"container_message\">\n";
		echo "\t\t\t\t<div class=\"container_img\">\n";
		echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
		echo "\t\t\t\t</div>\n";	  
		echo "\t\t\t\t<div class=\"message\">\n";
		echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
		echo "\t\t\t\t\t<p>COMPILARE TUTTI I CAMPI...</p>\n";
		echo "\t\t\t\t</div>\n";	  
		echo "\t\t\t</div>\n";
		echo "\t\t</div>\n";
	}
	else {
		// ***
		if(isset($superamento_nome) && $superamento_nome) {
			// ***
			$superamento_nome=$superamento_cognome=$superamento_recapito=$superamento_indirizzo=$superamento_citta=$superamento_cap=$superamento_username=$superamento_email=$superamento_password=false;
			
			echo "<div class=\"error_message\">\n";
			echo "\t\t\t<div class=\"container_message\">\n";
			echo "\t\t\t\t<div class=\"container_img\">\n";
			echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
			echo "\t\t\t\t</div>\n";	  
			echo "\t\t\t\t<div class=\"message\">\n";
			echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
			echo "\t\t\t\t\t<p>LA DIMENSIONE DEL NOME ECCEDE QUELLA MASSIMA PREVISTA...</p>\n";
			echo "\t\t\t\t</div>\n";	  
			echo "\t\t\t</div>\n";
			echo "\t\t</div>\n";
		}
		else {
			// ***
			if(isset($superamento_cognome) && $superamento_cognome) {
				// ***
				$superamento_nome=$superamento_cognome=$superamento_recapito=$superamento_indirizzo=$superamento_citta=$superamento_cap=$superamento_username=$superamento_email=$superamento_password=false;
				
				echo "<div class=\"error_message\">\n";
				echo "\t\t\t<div class=\"container_message\">\n";
				echo "\t\t\t\t<div class=\"container_img\">\n";
				echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t\t<div class=\"message\">\n";
				echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
				echo "\t\t\t\t\t<p>LA DIMENSIONE DEL COGNOME ECCEDE QUELLA MASSIMA PREVISTA...</p>\n";
				echo "\t\t\t\t</div>\n";	  
				echo "\t\t\t</div>\n";
				echo "\t\t</div>\n";
			}
			else {
				// ***
				if(isset($superamento_recapito) && $superamento_recapito) {
					// ***
					$superamento_nome=$superamento_cognome=$superamento_recapito=$superamento_indirizzo=$superamento_citta=$superamento_cap=$superamento_username=$superamento_email=$superamento_password=false;
					
					echo "<div class=\"error_message\">\n";
					echo "\t\t\t<div class=\"container_message\">\n";
					echo "\t\t\t\t<div class=\"container_img\">\n";
					echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t\t<div class=\"message\">\n";
					echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
					echo "\t\t\t\t\t<p>LA DIMENSIONE DEL RECAPITO TELEFONICO ECCEDE QUELLA MASSIMA PREVISTA...</p>\n";
					echo "\t\t\t\t</div>\n";	  
					echo "\t\t\t</div>\n";
					echo "\t\t</div>\n";
				}
				else {
					// ***
					if(isset($superamento_indirizzo) && $superamento_indirizzo) {
						// ***
						$superamento_nome=$superamento_cognome=$superamento_recapito=$superamento_indirizzo=$superamento_citta=$superamento_cap=$superamento_username=$superamento_email=$superamento_password=false;
						
						echo "<div class=\"error_message\">\n";
						echo "\t\t\t<div class=\"container_message\">\n";
						echo "\t\t\t\t<div class=\"container_img\">\n";
						echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t\t<div class=\"message\">\n";
						echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
						echo "\t\t\t\t\t<p>LA DIMENSIONE DELL'INDIRIZZO ECCEDE QUELLA MASSIMA PREVISTA...</p>\n";
						echo "\t\t\t\t</div>\n";	  
						echo "\t\t\t</div>\n";
						echo "\t\t</div>\n";
					}
					else {
						// ***
						if(isset($superamento_citta) && $superamento_citta) {
							// ***
							$superamento_nome=$superamento_cognome=$superamento_recapito=$superamento_indirizzo=$superamento_citta=$superamento_cap=$superamento_username=$superamento_email=$superamento_password=false;
							
							echo "<div class=\"error_message\">\n";
							echo "\t\t\t<div class=\"container_message\">\n";
							echo "\t\t\t\t<div class=\"container_img\">\n";
							echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t\t<div class=\"message\">\n";
							echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
							echo "\t\t\t\t\t<p>LA DIMENSIONE DELLA CITT&Agrave; ECCEDE QUELLA MASSIMA PREVITSA...</p>\n";
							echo "\t\t\t\t</div>\n";	  
							echo "\t\t\t</div>\n";
							echo "\t\t</div>\n";
						}
						else {
							// ***
							if(isset($superamento_cap) && $superamento_cap) {
								// ***
								$superamento_nome=$superamento_cognome=$superamento_recapito=$superamento_indirizzo=$superamento_citta=$superamento_cap=$superamento_username=$superamento_email=$superamento_password=false;
								
								echo "<div class=\"error_message\">\n";
								echo "\t\t\t<div class=\"container_message\">\n";
								echo "\t\t\t\t<div class=\"container_img\">\n";
								echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t\t<div class=\"message\">\n";
								echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
								echo "\t\t\t\t\t<p>LA DIMENSIONE DEL CAP ECCEDE QUELLA MASSIMA PREVISTA...</p>\n";
								echo "\t\t\t\t</div>\n";	  
								echo "\t\t\t</div>\n";
								echo "\t\t</div>\n";
							}
							else {
								// ***
								if(isset($superamento_username) && $superamento_username) {
									// ***
									$superamento_nome=$superamento_cognome=$superamento_recapito=$superamento_indirizzo=$superamento_citta=$superamento_cap=$superamento_username=$superamento_email=$superamento_password=false;
									
									echo "<div class=\"error_message\">\n";
									echo "\t\t\t<div class=\"container_message\">\n";
									echo "\t\t\t\t<div class=\"container_img\">\n";
									echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
									echo "\t\t\t\t</div>\n";	  
									echo "\t\t\t\t<div class=\"message\">\n";
									echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
									echo "\t\t\t\t\t<p>LA DIMENSIONE DELL'USERNAME ECCEDE QUELLA MASSIMA PREVISTA...</p>\n";
									echo "\t\t\t\t</div>\n";	  
									echo "\t\t\t</div>\n";
									echo "\t\t</div>\n";
								}
								else {
									// ***
									if(isset($superamento_email) && $superamento_email) {
										// ***
										$superamento_nome=$superamento_cognome=$superamento_recapito=$superamento_indirizzo=$superamento_citta=$superamento_cap=$superamento_username=$superamento_email=$superamento_password=false;
										
										echo "<div class=\"error_message\">\n";
										echo "\t\t\t<div class=\"container_message\">\n";
										echo "\t\t\t\t<div class=\"container_img\">\n";
										echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
										echo "\t\t\t\t</div>\n";	  
										echo "\t\t\t\t<div class=\"message\">\n";
										echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
										echo "\t\t\t\t\t<p>LA DIMENSIONE DELL'EMAIL ECCEDE QUELLA MASSIMA PREVISTA...</p>\n";
										echo "\t\t\t\t</div>\n";	  
										echo "\t\t\t</div>\n";
										echo "\t\t</div>\n";
									}
									else {
										// ***
										if(isset($superamento_password) && $superamento_password) {
											// ***
											$superamento_nome=$superamento_cognome=$superamento_recapito=$superamento_indirizzo=$superamento_citta=$superamento_cap=$superamento_username=$superamento_email=$superamento_password=false;
											
											echo "<div class=\"error_message\">\n";
											echo "\t\t\t<div class=\"container_message\">\n";
											echo "\t\t\t\t<div class=\"container_img\">\n";
											echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
											echo "\t\t\t\t</div>\n";	  
											echo "\t\t\t\t<div class=\"message\">\n";
											echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
											echo "\t\t\t\t\t<p>LA DIMENSIONE DELLA PASSWORD ECCEDE QUELLA MASSIMA PREVISTA...</p>\n";
											echo "\t\t\t\t</div>\n";	  
											echo "\t\t\t</div>\n";
											echo "\t\t</div>\n";
										}
										else {
											// ***
											if(isset($recapito_errato) && $recapito_errato) {
												// ***
												$recapito_errato=false;
												
												echo "<div class=\"error_message\">\n";
												echo "\t\t\t<div class=\"container_message\">\n";
												echo "\t\t\t\t<div class=\"container_img\">\n";
												echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
												echo "\t\t\t\t</div>\n";	  
												echo "\t\t\t\t<div class=\"message\">\n";
												echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
												echo "\t\t\t\t\t<p>IL FORMATO DEL RECAPITO TELEFONICO NON È VALIDO...</p>\n";
												echo "\t\t\t\t</div>\n";	  
												echo "\t\t\t</div>\n";
												echo "\t\t</div>\n";
											}
											else {
												// ***
												if(isset($cap_errato) && $cap_errato) {
													// ***
													$cap_errato=false;
													
													echo "<div class=\"error_message\">\n";
													echo "\t\t\t<div class=\"container_message\">\n";
													echo "\t\t\t\t<div class=\"container_img\">\n";
													echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
													echo "\t\t\t\t</div>\n";	  
													echo "\t\t\t\t<div class=\"message\">\n";
													echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
													echo "\t\t\t\t\t<p>IL FORMATO DEL CODICE DI AVVIAMENTO POSTALE NON È VALIDO...</p>\n";
													echo "\t\t\t\t</div>\n";	  
													echo "\t\t\t</div>\n";
													echo "\t\t</div>\n";
												}
												else {
													// ***
													if(isset($email_errata) && $email_errata) {
														// ***
														$email_errata=false;
														
														echo "<div class=\"error_message\">\n";
														echo "\t\t\t<div class=\"container_message\">\n";
														echo "\t\t\t\t<div class=\"container_img\">\n";
														echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
														echo "\t\t\t\t</div>\n";	  
														echo "\t\t\t\t<div class=\"message\">\n";
														echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
														echo "\t\t\t\t\t<p>IL FORMATO DELL'INDIRIZZO DI POSTA ELETTRONICA NON È VALIDO...</p>\n";
														echo "\t\t\t\t</div>\n";	  
														echo "\t\t\t</div>\n";
														echo "\t\t</div>\n";
													}
													else {
														// ***
														if(isset($password_errata) && $password_errata) {
															// ***
															$password_errata=false;
															
															echo "<div class=\"error_message\">\n";
															echo "\t\t\t<div class=\"container_message\">\n";
															echo "\t\t\t\t<div class=\"container_img\">\n";
															echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
															echo "\t\t\t\t</div>\n";	  
															echo "\t\t\t\t<div class=\"message\">\n";
															echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
															echo "\t\t\t\t\t<p>IL FORMATO DELLA PAROLA CHIAVE SELEZIONATA NON È VALIDO...</p>\n";
															echo "\t\t\t\t</div>\n";	  
															echo "\t\t\t</div>\n";
															echo "\t\t</div>\n";
														}
														else {
															// ***
															if(isset($password_differenti) && $password_differenti) {
																// ***
																$password_differenti=false;
																
																echo "<div class=\"error_message\">\n";
																echo "\t\t\t<div class=\"container_message\">\n";
																echo "\t\t\t\t<div class=\"container_img\">\n";
																echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
																echo "\t\t\t\t</div>\n";	  
																echo "\t\t\t\t<div class=\"message\">\n";
																echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
																echo "\t\t\t\t\t<p>LA VECCHIA PASSWORD NON &Egrave; QUELLA CORRETTA...</p>\n";
																echo "\t\t\t\t</div>\n";	  
																echo "\t\t\t</div>\n";
																echo "\t\t</div>\n";
															}
															else {
																// ***
																if(isset($duplicazione_username) && $duplicazione_username) {
																	// ***
																	$duplicazione_username=false;
																	
																	echo "<div class=\"error_message\">\n";
																	echo "\t\t\t<div class=\"container_message\">\n";
																	echo "\t\t\t\t<div class=\"container_img\">\n";
																	echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
																	echo "\t\t\t\t</div>\n";	  
																	echo "\t\t\t\t<div class=\"message\">\n";
																	echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
																	echo "\t\t\t\t\t<p>L'USERNAME INSERITO &Egrave; GI&Agrave; IN USO...</p>\n";
																	echo "\t\t\t\t</div>\n";	  
																	echo "\t\t\t</div>\n";
																	echo "\t\t</div>\n";
																}
																else {
																	// ***
																	if(isset($duplicazione_email) && $duplicazione_email) {
																		// ***
																		$duplicazione_email=false;
																		
																		echo "<div class=\"error_message\">\n";
																		echo "\t\t\t<div class=\"container_message\">\n";
																		echo "\t\t\t\t<div class=\"container_img\">\n";
																		echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Immagine Non Disponibile...\" />\n";
																		echo "\t\t\t\t</div>\n";	  
																		echo "\t\t\t\t<div class=\"message\">\n";
																		echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
																		echo "\t\t\t\t\t<p>L'EMAIL INSERITA &Egrave; GI&Agrave; IN USO...</p>\n";
																		echo "\t\t\t\t</div>\n";	  
																		echo "\t\t\t</div>\n";
																		echo "\t\t</div>\n";
																	}
																	else {
																		// ***
																		if(isset($errore_query) && $errore_query) {
																			// ***
																			$errore_query=false;
																			
																			echo "<div class=\"error_message\">\n";
																			echo "\t\t\t<div class=\"container_message\">\n";
																			echo "\t\t\t\t<div class=\"container_img\">\n";
																			echo "\t\t\t\t\t<img src=\"../../Immagini/xmark-solid.svg\" alt=\"Icona Errore\" />\n";
																			echo "\t\t\t\t</div>\n";	  
																			echo "\t\t\t\t<div class=\"message\">\n";
																			echo "\t\t\t\t\t<p class=\"err\">ERRORE!</p>\n";
																			echo "\t\t\t\t\t<p>L'OPERAZIONE NON &Egrave; ANDATA A BUON FINE...</p>\n";
																			echo "\t\t\t\t</div>\n";	  
																			echo "\t\t\t</div>\n";
																			echo "\t\t</div>\n";
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
?>