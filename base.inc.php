<?php
        try{
                //D�claration variables connection Base de donn�es
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
?>
