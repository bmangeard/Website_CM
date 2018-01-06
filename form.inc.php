<?php

    /*
		Mise en place
    */
    $destinataire = 'contact@charlottemaletras.com';
   
    // Copie du mail pour le client?
    $copie = 'oui';
     
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
     
    // On va vérifier les variables et l'Email ...
    $InputEmail = (IsEmail($InputEmail)) ? $InputEmail : ''; // Soit l'Email est vide si erroné, soit il vaut l'Email entré
    $err_formulaire = false; // Pour remplir le formulaire en cas d'erreur
     
    if (isset($_POST['envoi']))
    {
    	if (($InputNom != '') && ($InputEmail != '') && ($InputObjet != '') && ($InputMessage != ''))
    	{
    		// les 4 variables sont remplies, on génère puis envoie le mail
    		$headers  = 'From:'.$InputNom.' <'.$InputEmail.'>' . "\r\n";
    		//$headers .= 'Reply-To: '.$InputEmail. "\r\n" ;
    		//$headers .= 'X-Mailer:PHP/'.phpversion();
     
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
    			echo '<p>'.$message_envoye.'</p>';
				
				/*
				 *
				 *	Mettre en place le renseignement de la base de donnée ici
				 *
				*/
    		}
    		else
    		{
    			echo '<p>'.$message_non_envoye.'</p>';
    		};
    	}
    	else
    	{
    		// une des 3 variables (ou plus) est vide ...
    		echo '<p>'.$message_formulaire_invalide.'</p>';
    		$err_formulaire = true;
    	};
    }; // fin du if (!isset($_POST['envoi']))
     
    if (($err_formulaire) || (!isset($_POST['envoi'])))
    {
    	// afficher le formulaire
    	echo '
    	<form id="contact" method="post" action="'.$form_action.'">
    	<fieldset><legend>Vos coordonnées</legend>
    		<p><label for="InputNom">InputNom :</label><input type="text" id="InputNom" name="InputNom" value="'.stripslashes($InputNom).'" tabindex="1" /></p>
    		<p><label for="InputEmail">InputEmail :</label><input type="text" id="InputEmail" name="InputEmail" value="'.stripslashes($InputEmail).'" tabindex="2" /></p>
    	</fieldset>
     
    	<fieldset><legend>Votre InputMessage :</legend>
    		<p><label for="InputObjet">InputObjet :</label><input type="text" id="InputObjet" name="InputObjet" value="'.stripslashes($InputObjet).'" tabindex="3" /></p>
    		<p><label for="InputMessage">InputMessage :</label><textarea id="InputMessage" name="InputMessage" tabindex="4" cols="30" rows="8">'.stripslashes($InputMessage).'</textarea></p>
    	</fieldset>
     
    	<div style="text-align:center;"><input type="submit" name="envoi" value="Envoyer le formulaire !" /></div>
    	</form>';
    };
?>