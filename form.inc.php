<?php

    if(!empty($_POST)){
        //Déclaration variables des données du formulaire
        $nomOk=false;
        $prenomOk=false;
        $mdpOk=false;

        //Test Nom
        if (ctype_alpha($_POST['inputNom'])) {
            $nomOk=true;
        } else {
            $nomOk=false;
        }
        //Test Prenom
        if (ctype_alpha($_POST['inputPrenom'])) {
            $prenomOk=true;
        } else {
            $prenomOk=false;
        }
        //Test mdp
        if (ctype_alnum($_POST['inputMdp'])) {
            $mdpOk=true;
        } else {
            $mdpOk=false;
        }
        if($nomOk==true && $prenomOk==true && $mdpOk==true){

            extract($_POST);
            $userNom=$inputNom;;
            $userPrenom=$inputPrenom;
            $userEmail=$inputEmail;
            $userMdp=password_hash($inputMdp, PASSWORD_DEFAULT);

            include "base.inc.php";

            $stmt = $conn->prepare('SELECT * FROM etudiant WHERE NOM_ETU=?');
            $stmt->bindParam(1, $userNom);
            $stmt->execute();

            while($donnees = $stmt->fetch()){
                $tempMdp=$donnees['MDP_ETU'];
                echo $tempMdp;
                if($tempMdp=='0'){
                    //On envoi
                    
                        $stmt = $conn->prepare("UPDATE etudiant SET MDP_ETU =? WHERE NOM_ETU=?");
                        $stmt->bindParam(1, $userMdp);
                        $stmt->bindParam(2, $userNom);
                        $stmt->execute();

                        //Session Start
                        session_start();

                        $_SESSION['User'] = $userPrenom;
                        $_SESSION['Groupe'] = $donnees['GROUP_ETU'];
                        $_SESSION['Sou'] = $donnees['ID_SOU_ETU'];
                        $_SESSION['Type'] = 'etudiant';

                        header("location:../edsa-conception_site_web/Page_d'acceuil_Connect.php");
                                 
                }else{

                    $errorMessageExist=true;
                    //Session Start
                    session_start();
                    $_SESSION['errorMessageExist']=$errorMessageExist;
                    header("location:../edsa-conception_site_web/Page_d'inscription.php");
                }
            }
            $stmt->closeCursor();
        }else{

                $errorMessageIns=true;
                //Session Start
                session_start();
                $_SESSION['errorMessageIns']=$errorMessageIns;
                header("location:../edsa-conception_site_web/Page_d'inscription.php");
        }
                $errorMessageIns=true;
                //Session Start
                session_start();
                $_SESSION['errorMessageIns']=$errorMessageIns;
                header("location:../edsa-conception_site_web/Page_d'inscription.php");
    }

?>
