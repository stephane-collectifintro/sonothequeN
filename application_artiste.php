<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<?php echo '<programmation>'; ?>
<?php
    require('cn.php');
	$id=$_GET['id'];
	$sql = $pdo->query("SELECT * FROM artiste INNER JOIN portfolio_sonotheque ON portfolio_sonotheque.id_pj=artiste.id_photo WHERE artiste.id_artiste=".$id);
	 while($res = $sql->fetch(PDO::FETCH_OBJ)){
		 $nom_artiste = stripslashes(utf8_encode($res->nom_artiste));
		 $nom_artiste = str_replace ( "\'" , "'" , $nom_artiste ); 
		 $bio_artiste = stripslashes(utf8_encode($res->bio_artiste));
		 $bio_artiste = strip_tags($bio_artiste);
		 $bio_artiste = html_entity_decode($bio_artiste);
		 $bio_artiste = str_replace ( '"' , "'" , $bio_artiste ); 
		 $url_site = stripslashes(utf8_encode($res->url_site_web));
		 $url_site_2 = stripslashes(utf8_encode($res->url_site_web_2));
		 $image_artiste = $res->url_pj;
		 if($res->ville==""){
		 	$ville = "Inconnu";
		 }else{
			$ville = stripslashes(utf8_encode($res->ville));
		 }
		 if($image_artiste == "default_jaquette.png" && !is_readable($image_artiste)){
			 $sql2 = $pdo->query("SELECT * FROM media WHERE id_artiste=".$id);
			 $res2 = $sql2->fetch(PDO::FETCH_OBJ);
			 $image_artiste = $res2->path_media;
		 }else{
			 $image_artiste= $_MUSIC_ROOT.$image_artiste;
		 }
		 
		 
		 ?>
		 <evenement nomArtiste="<?php echo $nom_artiste;?>" bio_artiste="<?php echo $bio_artiste; ?>" url_site="<?php echo $url_site; ?>" url_site_2="<?php echo $url_site_2; ?>" image_artiste="<?php echo $image_artiste; ?>" ville="<?php echo $ville; ?>" />
<?php
	 }
?>

<?php echo '</programmation>'; ?>