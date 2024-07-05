<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>LEV: Libri &amp; Videogiochi</title>
		<link rel="icon" href="../../Immagini/Icona_LEV.png" />
		<link rel="stylesheet" href="../../Stili CSS/style_registrazione.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_intestazione_sito.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_common.css" type="text/css" />
		<link rel="stylesheet" href="../../Stili CSS/style_footer_sito.css" type="text/css" />
		<script type="text/javascript" src="../JavaScript/gestioneMenuTendina.js"></script>
	</head>
	<body>
		<?php
			require_once("./intestazione_sito.php");
		?>
		<div class="corpo_pagina">
			<div class="registrazione">
				<div class="container_registrazione">
					<div class="intestazione_registrazione">
						<div class="container_intestazione_registrazione">
							<span class="icona_registrazione">
								<img src="../../Immagini/signature-solid.svg" alt="Icona Registrazione" />
							</span>
							<h2>Crea un account!</h2>
						</div>
					</div>
					<div class="corpo_registrazione">
						<div class="container_corpo_registrazione">
							<div class="intestazione_sezione"> 
        						<div class="container_intestazione_sezione">Profilo Personale (Obbligatorio)</div>
							</div>
							<div class="elenco_campi">
								<div class="container_elenco_campi">
									<div class="campo">
										<p>
											Nome (max. 30 caratteri)
										</p>
										<p>
											<input type="text" name="nome" value="<?php if(isset($_POST['nome'])) echo $_POST['nome']; else echo '';?>"  />
										</p>	
									</div>
									<div class="campo">
										<p>
											Cognome (max. 35 caratteri)
										</p>
										<p>
											<input type="text" name="cognome" value="<?php if(isset($_POST['cognome'])) echo $_POST['cognome']; else echo '';?>"  />
										</p>	
									</div>
									<div class="campo">
										<p>
											Recapito Telefonico
										</p>
										<p>
											<input type="text" name="num_telefono" value="<?php if(isset($_POST['num_telefono'])) echo $_POST['num_telefono']; else echo '';?>"  />
										</p>										
									</div>
									<p class="nota"><strong>N.B.</strong> Il numero di telefono deve essere formato da una sequenza di 10 cifre.</p>
								    <div class="campo">
										<p>
											Indirizzo (max. 60 caratteri)
										</p>
										<p>
											<input type="text" name="indirizzo" value="<?php if(isset($_POST['indirizzo'])) echo $_POST['indirizzo']; else echo '';?>"  />
										</p>	
									</div>
									<div class="campo">
										<p>
											Citt&agrave; (max. 40 caratteri)
										</p>
										<p>
											<input type="text" name="citta" value="<?php if(isset($_POST['citta'])) echo $_POST['citta']; else echo '';?>"  />
										</p>	
									</div>
									<div class="campo">
										<p>
											CAP
										</p>
										<p>
											<input type="text" name="cap" value="<?php if(isset($_POST['cap'])) echo $_POST['cap']; else echo '';?>"  />
										</p>	
									</div>
									<p class="nota"><strong>N.B.</strong> Il codice di avviamento postale deve essere formato da una sequenza di 5 cifre.</p>		
								</div>
							</div>
							<div class="intestazione_sezione"> 
        						<div class="container_intestazione_sezione">Profilo Utente (Obbligatorio)</div>
							</div>
							<div class="elenco_campi">
								<div class="container_elenco_campi">
									<div class="campo">
										<p>
											Username (max. 30 caratteri)
										</p>
										<p>
											<input type="text" name="username" value="<?php if(isset($_POST['username'])) echo $_POST['username']; else echo '';?>"  />
										</p>	
									</div>
									<div class="campo">
										<p>
											Email
										</p>
										<p>
											<input type="text" name="email" value="<?php if(isset($_POST['email'])) echo $_POST['email']; else echo '';?>"  />
										</p>	
									</div>
									<p class="nota"><strong>N.B.</strong> La lunghezza complessiva dell'indirizzo di posta elettronica non pu&ograve; essere superiore a 35 caratteri.</p>
									<div class="campo">
										<p>
											Password
										</p>
										<p>
											<input type="password" name="password" value="<?php if(isset($_POST['password'])) echo $_POST['password']; else echo '';?>"  />
										</p>	
									</div>
									<p class="nota"><strong>N.B.</strong> La parola chiave dovr&agrave; contenere al pi&ugrave; 16 elementi, di cui (almeno): un numero, una lettera minuscola e una lettera maiuscola.</p>
								</div>
							</div>
							<div class="pulsante">
								<button type="submit" name="confirm" class="container_pulsante">Conferma!</button>
							</div>  
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
			require_once("./footer_sito.php");
		?>
	</body>
</html>