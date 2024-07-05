<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>LEV: Libri &amp; Videogiochi</title>
		<link rel="icon" href="../../Immagini/Icona_LEV.png" />
		<link rel="stylesheet" href="../../Stili CSS/style_login.css" type="text/css" />
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
			<div class="login">
				<div class="container_login">
					<div class="intestazione_login">
						<div class="container_intestazione_login">
							<span class="icona_login">
								<img src="../../Immagini/right-to-bracket-solid.svg" alt="Icona Login" />
							</span>
							<h2>Accedi al tuo account!</h2>
						</div>
					</div>
					<div class="corpo_login">
						<form class="container_corpo_login" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
							<div class="riga_form">
								<div class="container_riga_form">
									<div class="container_intestazione_riga_form" title="Email">
										<img src="../../Immagini/envelope-solid.svg" alt="Icona Email" />
										<h3>Email:</h3>
									</div>
									<input type="text" name="email" value="<?php if(isset($_POST['email'])) echo $_POST['email']; else echo '';?>" />
								</div>
							</div>
							<div class="riga_form">
								<div class="container_riga_form">
									<div class="container_intestazione_riga_form" title="Password">
										<img src="../../Immagini/lock-solid.svg" alt="Icona Password" />
										<h3>Password:</h3>
									</div>
									<input type="password" name="password" value="<?php if(isset($_POST['password'])) echo $_POST['password']; else echo '';?>" />
								</div>
							</div>
							<div class="riga_form" style="margin-bottom: 0.5em; margin-top: 0%;">
								<div class="container_riga_form" style="justify-content: center; border: none;">
									<p>
										<a href="resume_password.php">
											Hai dimenticato la password?
										</a>
									</p>
								</div>
							</div>
							<div class="pulsante_form">
								<div class="container_pulsante_form">
									<button type="submit" name="confirm">Accedi!</button>
								</div>
							</div>
							<div class="footer_form">
								<div class="container_footer_form">
									<p>
										Non hai un account?
										<a href="registrazione.php">Registrati!</a>
									</p>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
			require_once ("./footer_sito.php");
		?>
	</body>
</html>