<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>LEV: Libri &amp; Videogiochi</title>
		<link rel="icon" href="../../Immagini/Icona_LEV.png" />
		<link rel="stylesheet" href="../../Stili CSS/style_area_riservata.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_intestazione_sito.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_common.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_footer_sito.css" type="text/css" />
		<script type="text/javascript" src="../JavaScript/gestioneMenuTendina.js"></script>
	</head>
	<body>
		<?php
			require_once ("./intestazione_sito.php");
		?>
		<div class="corpo_pagina">
			<div class="area_riservata">
				<div class="container_area_riservata">
					<div class="intestazione_area_riservata">	
						<div class="container_intestazione_area_riservata">
							<span class="icona_area_riservata">
								<img src="../../Immagini/user-lock-solid.svg" alt="Icona Area Riservata" />
							</span>
							<h2>Area Riservata</h2>
						</div>
					</div>
					<div class="corpo_area_riservata">
						<div class="container_corpo_area_riservata">
							<div class="riga_elenco_funzioni">
								<a href="" class="cella_funzione">
									<span class="container_cella_funzione">
										<span class="icona_funzione">
											<span class="container_icona_funzione">
												<img src="../../Immagini/box-solid.svg" alt="Icona Acquisti" />
											</span>
										</span>
										<span class="corpo_funzione">
											<span>I miei Acquisti</span>
											<span>Visualizza il riepilogo degli ordini effettuati</span>
										</span>
									</span>
								</a>
								<a href="" class="cella_funzione">
									<span class="container_cella_funzione">
										<span class="icona_funzione">
											<span class="container_icona_funzione">
												<img src="../../Immagini/user-shield-solid.svg" alt="Icona Sicurezza" />
											</span>
										</span>
										<span class="corpo_funzione">
											<span>Accesso e Sicurezza</span>
											<span>Modifica il nome utente e la parola chiave</span>
										</span>
									</span>
								</a>
								<a href="" class="cella_funzione">
									<span class="container_cella_funzione">
										<span class="icona_funzione">
											<span class="container_icona_funzione">
												<img src="../../Immagini/wallet-solid.svg" alt="Icona Portafoglio" />
											</span>
										</span>
										<span class="corpo_funzione">
											<span>Saldo e Ricarica</span>
											<span>Visualizza il totale dei crediti e richiedine altri</span>
										</span>
									</span>
								</a>
							</div>
							<div class="riga_elenco_funzioni">
								<a href="" class="cella_funzione">
									<span class="container_cella_funzione">
										<span class="icona_funzione">
											<span class="container_icona_funzione">
												<img src="../../Immagini/user-solid.svg" alt="Icona Account" />
											</span>
										</span>
										<span class="corpo_funzione">
											<span>Anagrafica</span>
											<span>Visualizza e aggiorna le informazioni personali</span>
										</span>
									</span>
								</a>
							</div>
						</div>
					</div>	
				</div>
			</div>
		</div>
		
		<?php
			require_once ("./footer_sito.php");
		?>
	</body>
</html>