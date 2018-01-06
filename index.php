<?php

	session_start();
	session_destroy();
    /*
		Mise en place
    */
    $destinataire = 'c.maletras@gmail.com';
   
    // Copie du mail pour le client?
    $copie = 'oui';

    // Action du formulaire (si votre page a des paramètres dans l'URL)
	// si cette page est index.php?page=contact alors mettez index.php?page=contact
	// sinon, laissez vide
	$form_action = '';

    // Messages de confirmation du mail
    $message_envoye = "Votre message nous est bien parvenu !";
    $message_non_envoye = "L'envoi du mail a échoué, veuillez réessayer SVP.";
     
    // Message d'erreur du formulaire
    $message_formulaire_invalide = "Vérifiez que tous les champs soient bien remplis et que l'email soit sans erreur.";
     
    /*
     * Fonction qui nettoye et enregistrer un texte
     */
    function Rec($text)
    {
    	$text = htmlspecialchars(trim($text), ENT_QUOTES);
    	if (1 === get_magic_quotes_gpc())
    	{
    		$text = stripslashes($text);
    	}
     
    	$text = nl2br($text);
    	return $text;
    };
     
    /*
     * Fonction servant à vérifier la syntaxe d'un Email
     */
    function IsEmail($InputEmail)
    {
    	$value = preg_match('/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/', $InputEmail);
    	return (($value === 0) || ($value === false)) ? false : true;
    }
     
    // On récupère tous les champs
    $InputNom     = (isset($_POST['InputNom']))     ? Rec($_POST['InputNom'])     : '';
    $InputEmail   = (isset($_POST['InputEmail']))   ? Rec($_POST['InputEmail'])   : '';
    $InputObjet   = (isset($_POST['InputObjet']))   ? Rec($_POST['InputObjet'])   : '';
    $InputMessage = (isset($_POST['InputMessage'])) ? Rec($_POST['InputMessage']) : '';
    $InputRadio = (isset($_POST['exampleRadios']))  ? $_POST['exampleRadios']     : '';
    if($InputRadio=="option1"){
		$InputRadio="Professionnel";
	}else{
		$InputRadio="Particulier";
	}

    // On va vérifier les variables et l'Email ...
    $InputEmail = (IsEmail($InputEmail)) ? $InputEmail : ''; // Soit l'Email est vide si erroné, soit il vaut l'Email entré
    $err_formulaire = false; // Pour remplir le formulaire en cas d'erreur
    
    if (isset($_POST['envoi']))
    {
    	if (($InputNom != '') && ($InputEmail != '') && ($InputObjet != '') && ($InputMessage != ''))
    	{
    		// les 4 variables sont remplies, on génère puis envoie le mail
    		$headers  = 'From:'.$InputNom.' <'.$InputEmail.'>' . "\r\n";
    		$headers .= 'Reply-To: '.$InputEmail. "\r\n" ;
    		$headers .= 'X-Mailer:PHP/'.phpversion();
     
    		// envoyer une copie au visiteur ?
    		if ($copie == 'oui')
    		{
    			$cible = $destinataire.';'.$InputEmail;
    		}
    		else
    		{
    			$cible = $destinataire;
    		};
     
    		// Remplacement de certains caractères spéciaux
    		$InputMessage = str_replace("&#039;","'",$InputMessage);
    		$InputMessage = str_replace("&#8217;","'",$InputMessage);
    		$InputMessage = str_replace("&quot;",'"',$InputMessage);
    		$InputMessage = str_replace('&lt;br&gt;','',$InputMessage);
    		$InputMessage = str_replace('&lt;br /&gt;','',$InputMessage);
    		$InputMessage = str_replace("&lt;","&lt;",$InputMessage);
    		$InputMessage = str_replace("&gt;","&gt;",$InputMessage);
    		$InputMessage = str_replace("&amp;","&",$InputMessage);
     
    		// Envoi du mail
    		$num_InputEmails = 0;
    		$tmp = explode(';', $cible);
    		foreach($tmp as $InputEmail_destinataire)
    		{
    			if (mail($InputEmail_destinataire, $InputObjet, $InputMessage, $headers))
    				$num_InputEmails++;
    		}
     
			
    		if ((($copie == 'oui') && ($num_InputEmails == 2)) || (($copie == 'non') && ($num_InputEmails == 1)))
    		{
    			$_SESSION['message_envoye'] = $message_envoye;
				
			//////////////////////////////////////////////////////////////////
				// Connection "Base de Donnée"
				
					try{
						//Déclaration variables connection Base de données
						$db_host='localhost';
						$db_name='testcm';
						$db_user='root';
						$db_mdp='';

						//On se connecte
						$conn = new PDO("mysql:host={$db_host};dbname={$db_name}",$db_user,$db_mdp);
						$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						echo "test";
						
					}catch(Exeption $e){
						die('Erreur : '.$e->getMessage());
					}
					// Evite copie inutile de clients
					$nomExiste = true;
					$stmt = $conn->prepare('SELECT NOM_CLIENTS, EMAIL_CLIENTS FROM clients');
					$stmt->execute();

					while($donnees = $stmt->fetch()){
						if((($donnees['NOM_CLIENTS'] != $InputNom) && ($donnees['EMAIL_CLIENTS'] != $InputEmail))){
							$nomExiste = false;
						}else{
							$nomExiste = true;
						}
					}
					if($nomExiste==false){	   
						// Prepare statment
						$stmt = $conn->prepare("INSERT INTO clients (NOM_CLIENTS, EMAIL_CLIENTS,TYPE_CLIENTS) VALUES (?,?,?)");
						$stmt->bindParam(1, $InputNom);
						$stmt->bindParam(2, $InputEmail);
						$stmt->bindParam(3, $InputRadio);
						$stmt->execute();
					}

			//////////////////////////////////////////////////////////////////
					}
					else
					{
						$_SESSION['message_non_envoye'] = $message_non_envoye;
						$err_formulaire = true;
					};
    	}
    	else
    	{
    		// une des 3 variables (ou plus) est vide ...
    		$_SESSION['message_formulaire_invalide'] = $message_formulaire_invalide;
    		$err_formulaire = true;
    	};
    }; // fin du if (!isset($_POST['envoi']))
?>

<!doctype html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/> 
	<title>Charlotte Malétras</title>
	<link rel="stylesheet" href="font_awesome_5.0.2/web-fonts-with-css/css/fontawesome-all.css">
    <link rel="stylesheet" href="css/bootstrap.css">
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="menu">
		<div class="container mb-5" id="cont_menu">
			<div class="main">
				<div class="row mt-3 ml-1">
					<div class="col text-left">
						<div class="logo">
							<span class="icon"><img src="img/logo.png" alt="logo"></span>
						</div>
					</div>
				</div>	
				<div class="row mt-3">
					<div class="col text-center">
						<div class="content">
							<br>
							<h1 class="text-white">Montage vidéo | Le Havre</h1>
							<p class="text-white">Freelance dans le montage vidéo, je vous accompagne pour la succès de vos visuels, que vous soyez un professionnel ou un particulier.</p>
							<br>
						</div>
					</div>
				</div>	
				<div class="row mb-3">
					<div class="col text-center">
						<div class="btn-group" role="group" aria-label="Basic example">
							<button id="btn_propos" type="button" class="btn btn-outline-light" onclick="propos()">A propos</button>
							<button id="btn_real" type="button" class="btn btn-outline-light" onclick="real()">Réalisations</button>
							<button id="btn_contact" type="button" class="btn btn-outline-light" onclick="contact()">Contact</button>
						</div>
					</div>
				</div>
				<div class="row mt-3 ml-1">
					<div class="col text-center">
						<label class="text-danger">
							<?php
								if(isset($_SESSION['message_non_envoye'])){
									echo $_SESSION['message_non_envoye'];
								}
								if(isset($_SESSION['message_formulaire_invalide'])){
									echo $_SESSION['message_formulaire_invalide'];
								}
							?>
						</label>
						<label class="text-success">
							<?php
								if(isset($_SESSION['message_envoye'])){
									echo $_SESSION['message_envoye'];
								}
							?>
						</label>
					</div>
				</div>
			</div>
		</div>
		<div class="container mb-5" id="cont_propos" style="display: none">
			<div class="main">
				<div class="row mt-3 mr-1">
					<div class="col text-right">
							<button type="button" class="btn btn-outline-light" onclick="propos()">X</button>
					</div>
				</div>				
				<div class="row mt-3 ml-4">
					<div class="col text-left">
						<div class="content">
							<h2 class="text-white"><u>A propos :</u></h2>
						</div>
					</div>
				</div>	
				<div class="row mt-3 ml-4 mb-4 mr-4">
					<div class="col-12 text-center">
						<div class="content">
							<img src="img/photo.jpg" alt="photo" id="photo"/>
						</div>
					</div>
					<div class="col-12 text-left mt-4">
						<div class="content">
							<p class="text-white">
								De formation DUT Métiers du multimédia et de l'Internet (anciennement Services et Réseaux de Communications) à l'antenne d'Elbeuf de l'université de Rouen, j'ai par la suite travaillé plusieurs années à Paris pour une chaîne de télévision. J'y ai appris le montage, la réalisation, le cadrage et divers métiers de l'audiovisuel, j'ai décidé de me spécialisé dans le montage vidéo.
							<br>
							<br>
								Passionné de vidéo, j'ai enrichit mes compétences en montage vidéo avec différents clients, tel que : la chaîne de télévision STAR 24, le groupe de radio 1981 (ADO, VOLTAGE), Sud Radio, montage son, blogueuses.
							<br>
								En plus du montage vidéo, je fais du doublage et de la voix off pour STAR24.
							<br>
							<br>	
								Aujourd'hui, monteuse vidéo à mon compte au Havre, je souhaite vous proposer mes services et vous accompagner dans votre créativité.
							<br>
							<br>
								Logiciels : Suite Adobe, Premier PRO, Photoshop, After Effects, Illustrator, InDesign...
							<br>
							<br>
								Matériel : Dell XPS, Digidesign MBOX 2 PRO, Monitoring Prodipe Pro 8, Microphone AKG Peception 420 + Filtre anti-pop, Casque Sennheiser HD25, Go Pro Hero 3, Canon 1100D
							</p>
						</div>
					</div>
				</div>	
			</div>
		</div>
		<div class="container mb-5" id="cont_real" style="display: none">
			<div class="main">
				<div class="row mt-3 mr-1">
					<div class="col text-right">
							<button type="button" class="btn btn-outline-light" onclick="real()">X</button>
					</div>
				</div>	
				<div class="row mt-3 ml-4">
					<div class="col text-left">
						<h2 class="text-white"><u>Réalisations :</u></h2>
						<p class="text-white">Mon activitée est très variées, télévisions, radios, vidéos youtube, mariages, compétitions sportives...<br>
						En voici quelques exemples :</p>
					</div>
				</div>		
				<div class="row mt-3">
					<div class="col text-center">
  						<img src="img/star24.jpg" alt="star24" class="logo_clients"/>
					</div>
					<div class="col text-center">
  						<img src="img/star24.jpg" alt="star24" class="logo_clients"/>
					</div>
					<div class="col text-center">
  						<img src="img/star24.jpg" alt="star24" class="logo_clients"/>
					</div>
				</div>
				<div class="row mt-3 mb-4">
					<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
						<div class="content">
							<h4 class="text-white">Star 24 (Montage et voix off)</h4>
							<object type="application/x-shockwave-flash" width="425" height="355" data="https://www.youtube.com/v/5UyzJrqhUZg">
								<param name="movie" value="https://www.youtube.com/v/5UyzJrqhUZg" />
								<param name="wmode" value="transparent" />
							</object>
						</div>	
					</div>	
					<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
						<h4 class="text-white">LHUT (Montage)</h4>
						<object type="application/x-shockwave-flash" width="425" height="355" data="https://www.youtube.com/v/wL-88DpVC70">
							<param name="movie" value="https://www.youtube.com/v/wL-88DpVC70" />
							<param name="wmode" value="transparent" />
						</object>
					</div>
				</div>		
			</div>
		</div>
		<div class="container mb-5" id="cont_contact" style="display: none">
			<div class="main">
				<div class="row mt-3 mr-1">
					<div class="col text-right">
							<button type="button" class="btn btn-outline-light" onclick="contact()">X</button>
					</div>
				</div>				
				<div class="row ml-4 mr-4">
					<div class="col">
						<div class="text-white text-left">
							<h2><u>Contact :</u></h2>
							<p>N'hésitez pas à me contacter pour tout devis ou bien pour toutes questions concernant vos projets.</p>
						</div>
					</div>
				</div>	
				<div class="row mt-3 mb-1 ml-2 mr-2">
					<div class="col text-center">
						<div class="content">
							<div class="container text-white">
								<div class="row">
									<div class="col">
										<?php  
											if (($err_formulaire) || (!isset($_POST['envoi'])))
											{
											// afficher le formulaire
											echo '
												<form class="form-horizontal" method="post" action="index.php">
												<div class="form-group">
													<label for="InputNom">Nom</label>
													<input type="text" class="form-control" id="InputNom" name="InputNom" placeholder="Votre nom" value="'.stripslashes($InputNom).'" tabindex="1">
												</div>
												<div class="form-group">
													<label for="InputEmail">Email</label>
													<input type="email" class="form-control" id="InputEmail" name="InputEmail" placeholder="nom@exemple.com" value="'.stripslashes($InputEmail).'" tabindex="2" >
												</div>
												<div class="form-group">
													<label for="InputObjet">Objet</label>
													<input type="text" class="form-control" id="InputObjet" name="InputObjet" placeholder="Objet" value="'.stripslashes($InputObjet).'" tabindex="3" >
												</div>
												<div class="form-group ">
													<label for="InputText">Votre message</label>
													<textarea  class="form-control" id="InputMessage" name="InputMessage" placeholder="Votre message" tabindex="4" rows="4">'.stripslashes($InputMessage).'</textarea> 
												</div>
												<div class="form-check text-left">
													<input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1" checked>
													<label class="form-check-label" for="exampleRadios1">
													Vous êtes un professionnel.
													</label>
												</div>
												<div class="form-check text-left mb-3">
													<input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="option2">
													<label class="form-check-label" for="exampleRadios2">
													Vous êtes un particulier.
													</label>
												</div>
												<button type="submit" class="btn btn-outline-light" name="envoi">Envoyer</button>
											</form>'
											;}else{
											echo'	
											<form class="form-horizontal" method="post" action="index.php">
												<div class="form-group">
													<label for="InputNom">Nom</label>
													<input type="text" class="form-control" id="InputNom" name="InputNom" placeholder="Votre nom">
												</div>
												<div class="form-group">
													<label for="InputEmail">Email</label>
													<input type="email" class="form-control" id="InputEmail" name="InputEmail" placeholder="nom@exemple.com">
												</div>
												<div class="form-group">
													<label for="InputObjet">Objet</label>
													<input type="text" class="form-control" id="InputObjet" name="InputObjet" placeholder="Objet">
												</div>
												<div class="form-group ">
													<label for="InputText">Votre message</label>
													<textarea  class="form-control" id="InputMessage" name="InputMessage" placeholder="Votre message" rows="4"></textarea> 
												</div>
												<div class="form-check text-left">
													<input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1" checked>
													<label class="form-check-label" for="exampleRadios1">
													Vous êtes un professionnel.
													</label>
												</div>
												<div class="form-check text-left mb-3">
													<input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="option2">
													<label class="form-check-label" for="exampleRadios2">
													Vous êtes un particulier.
													</label>
												</div>
												<button type="submit" class="btn btn-outline-light" name="envoi">Envoyer</button>
											</form>';
											};
										?>			
									</div>
								</div>	
								<div class="row mt-4" id="contact_sep">
									<div class="col">
										<ul class="list-inline text-left">
											<li><label><i class="fa fa-phone mt-4" aria-hidden="true"></i> | 06 69 47 40 98</label>
											<li><label><i class="fa fa-envelope" aria-hidden="true"></i>  | contact@charlottemaletras.com</label>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>	
			</div>
		</div>
	</div>
	
	<footer>
		<div class="text-center text-white fixed-bottom">
			<br>
			<p id="copyright">&copy; 2018 - CHARLOTTE MALETRAS | DESIGN: BENOIT MANGEARD</p>
		</div>
	</footer>
	<!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="jQuery/jquery-3.2.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="js/bootstrap.min.js"></script>

	<script>
		function propos() {
			var v_menu = document.getElementById("cont_menu");
			var v_propos = document.getElementById("cont_propos");
			if (v_menu.style.display === "none") {
				v_menu.style.display = "block";
				v_propos.style.display = "none";
			} else {
				v_menu.style.display = "none";
				v_propos.style.display = "block";
			}
		}
		function real() {
			var v_menu = document.getElementById("cont_menu");
			var v_real = document.getElementById("cont_real");
			if (v_menu.style.display === "none") {
				v_menu.style.display = "block";
				v_real.style.display = "none";
			} else {
				v_menu.style.display = "none";
				v_real.style.display = "block";
			}
		}
		function contact() {
			var v_menu = document.getElementById("cont_menu");
			var v_contact = document.getElementById("cont_contact");
			if (v_menu.style.display === "none") {
				v_menu.style.display = "block";
				v_contact.style.display = "none";
			} else {
				v_menu.style.display = "none";
				v_contact.style.display = "block";
			}
		}
	</script>
</body>
</html>
