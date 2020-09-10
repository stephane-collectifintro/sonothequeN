<?php
if(isset($_POST['telecharger'])){
	
	require('../cn.php');
	$sql = $pdo->query("SELECT * 
						FROM portfolio_sonotheque 
						INNER JOIN chanson ON portfolio_sonotheque.id_pj=chanson.id_morceau 
						INNER JOIN album ON chanson.id_album=album.id_album
						INNER JOIN artiste ON artiste.id_artiste=album.id_artiste
						WHERE counter_pj>0
						ORDER BY nom_artiste ASC, piste ASC");
	
	$date = date('Y-m-d');
	$file = 'sacem_'.$date.'.csv';
	
	$fp = fopen($file, 'w');
	
	$ligne = "CODE DECLARANT;CODE DIFFUSEUR;GRID;CODE EAN 13;CODE UPC;IDENTIFIANT PRODUIT DECLARANT;TYPE SERVICE;TYPE PRODUIT;TITRE PRODUIT;TITRE OEUVRE;NOM COMPOSITEUR;NOM AUTEUR;NOM EDITEUR;NOM INTERPRETE;LABEL;DUREE OEUVRE;DATE DEBUT PERIODE;DATE FIN PERIODE;TERRITOIRE VENTE;QUANTITE;NOM DE L'OFFRE COMMERCIALE;TYPE MODELE COMMERCIAL;TYPE CANAL DISTRIBUTION;TYPE UTILISATION;PRIX UNITAIRE HT;DEVISE";
	fputcsv($fp,explode(';',$ligne),';');
	
	$ligne = "";
	fputcsv($fp,explode(';',$ligne),';');
		
	
	
	while($res = $sql->fetch(PDO::FETCH_OBJ)){
		
		$ligne = ";
					;
					;
					;
					;
					;
					MUSI;
					SINGLE;
					".$res->titre.";
					".$res->nom.";
					".$res->nom_artiste.";
					;
					;
					;
					;
					".$res->duree.";
					".$res->date_creation.";
					".$res->date_fin.";
					FR;
					".$res->counter_pj.";
					;
					FREE;
					INTERNET;
					ON_DEM_STRE;
					0;
					EURO";

		fputcsv($fp,explode(';',$ligne),';');
		
	}

	fclose($fp);
	
	
     //----------------------------------------------- 
     //DECLARE LES VARIABLES 
     //----------------------------------------------- 

     $email_expediteur='gwereg@collectif-intro.com'; 
     $email_reply='gwereg@collectif-intro.com'; 
     $message_texte='Bonjour,'."\n\n".'Voici un message au format texte'; 
	$destinataire = 'stephane@collectif-intro.com';
     $message_html='<html> 
     <head> 
     <title>Titre</title> 
     </head> 
     <body>Test de message</body> 
     </html>'; 

     //----------------------------------------------- 
     //GENERE LA FRONTIERE DU MAIL ENTRE TEXTE ET HTML 
     //----------------------------------------------- 

     $frontiere = '-----=' . md5(uniqid(mt_rand())); 

     //----------------------------------------------- 
     //HEADERS DU MAIL 
     //----------------------------------------------- 

     $headers = 'From: "Moi" <'.$email_expediteur.'>'."\n"; 
     $headers .= 'Return-Path: <'.$email_reply.'>'."\n"; 
     $headers .= 'MIME-Version: 1.0'."\n"; 
     $headers .= 'Content-Type: multipart/mixed; boundary="'.$frontiere.'"'; 

     //----------------------------------------------- 
     //MESSAGE TEXTE 
     //----------------------------------------------- 
     $message = 'This is a multi-part message in MIME format.'."\n\n"; 

     $message .= '--'.$frontiere."\n"; 
     $message .= 'Content-Type: text/plain; charset="iso-8859-1"'."\n"; 
     $message .= 'Content-Transfer-Encoding: 8bit'."\n\n"; 
     $message .= $message_texte."\n\n"; 

     //----------------------------------------------- 
     //MESSAGE HTML 
     //----------------------------------------------- 
     $message .= '--'.$frontiere."\n"; 

     $message .= 'Content-Type: text/html; charset="iso-8859-1"'."\n"; 
     $message .= 'Content-Transfer-Encoding: 8bit'."\n\n"; 
     $message .= $message_html."\n\n"; 

     $message .= '--'.$frontiere."\n"; 

     //----------------------------------------------- 
     //PIECE JOINTE 
     //-----------------------------------------------

echo($file); 

     $message .= 'Content-Type: application/vnd.ms-excel; name="'.$file.'"'."\n"; 
     $message .= 'Content-Transfer-Encoding: base64'."\n"; 
     $message .= 'Content-Disposition:attachement; filename="'.$file.'"'."\n\n"; 

     $message .= chunk_split(base64_encode(file_get_contents($file)))."\n"; 

     if(mail($destinataire,"Rapport d'ecoute",$message,$headers)) 
     { 
          echo 'Le mail a été envoyé'; 
     } 
     else 
     { 
          echo 'Le mail n\'a pu être envoyé'; 
     } 
	
	
}else{
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Document sans titre</title>
</head>
<body>

<div style="text-align:center;">
<form action="index.php" method="post" ><input type="submit" name="telecharger" value="télécharger la dernière liste des emails" style="padding:30px 50px; background-color:#069; color:#FFF; margin-top:50px; font-size:18px; font-family:Arial, Helvetica, sans-serif; font-weight:bold; text-transform:uppercase; border:3px solid #09F; cursor:pointer;" /></form>
</div>

</body>
</html>
<?php
}
?>
