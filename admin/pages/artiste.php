
<?php
$sql = new sql();

if(isset($_GET['maj']) && $_GET['maj']==="true"){
	$champs = array('date_maj_artiste');
	$values = array(date('Y-m-d'));
	$sql->UPDATE('artiste',$champs,$values,'id_artiste='.$_SESSION['id']);
	//echo $sql->getQuery();
	if($sql->execute()){
		echo "<script>window.location.href='artiste-".$id.".php';</script>";
	}
}

if(isset($_GET['idphoto']) && $_GET['idphoto']!=""){

	unlink($_GET['path']);

	$sql->delete('media','id_media='.$_GET['idphoto']);
	if($sql->execute()){
		echo "<script>window.location.href='artiste-".$id.".php';</script>";
	}


}

if(isset($_GET['firstphoto']) && $_GET['firstphoto']!=""){

	unlink($_GET['firstpath']);

	$champs = array('id_photo');
	$values = array('');
	$sql->update('artiste',$champs,$values,'id_artiste='.$id);
	$sql->execute();

	$sql->delete('portfolio_sonotheque','id_pj='.$_GET['firstphoto']);
	if($sql->execute()){
		echo "<script>window.location.href='artiste-".$id.".php';</script>";
	}


}

//============== INFOS =================//
if((isset($_POST['nom']) && $_POST['nom']!="")){
	//
	$nom = addslashes(utf8_decode($_POST['nom']));
	$bio = addslashes(utf8_decode($_POST['bio']));
	$ville = addslashes(utf8_decode($_POST['ville']));
	$site = utf8_decode($_POST['site']);
	$site2 = utf8_decode($_POST['site2']);

	//
	$jour_debut = $_POST['jour_debut'];
	$mois_debut = $_POST['mois_debut'];
	$annee_debut = $_POST['annee_debut'];
	$debut = $annee_debut.'-'.$mois_debut.'-'.$jour_debut;
	//
	$jour_fin = $_POST['jour_fin'];
	$mois_fin = $_POST['mois_fin'];
	$annee_fin = $_POST['annee_fin'];
	$fin = $annee_fin.'-'.$mois_fin.'-'.$jour_fin;

    //update Tags
    $idTag = array();
    $tagArray = $_POST['tag'];
		$tags = explode(",", $tagArray);
    foreach ($tags as $tag) {
        $stmt = $pdo->prepare("SELECT tag_id FROM tags WHERE tag_name= '".$tag."'");
        $stmt->execute();


        $result = $stmt->fetchColumn();
        if ($result == 0) {
             $sql2 = "INSERT INTO tags (tag_name) VALUES ('".$tag."')";
             $pdo->exec($sql2);
             array_push($idTag,$pdo->lastInsertId());

        }else{
            $stmt2 = $pdo->prepare("SELECT tag_id FROM tags WHERE tag_name= '".$tag."'");
            $stmt2->execute();
            $rows = $stmt2->fetchAll();
           foreach($rows as $row) {
                array_push($idTag,$row["tag_id"]);
            }
        }
    }
    $sqlDelete = "DELETE FROM item_tag WHERE id_item=".$id;
    $pdo->exec($sqlDelete);

    foreach ($tags as $key =>  $tag) {
        $sqlInsert = "INSERT INTO item_tag (id_tag,id_item) VALUES ('".$idTag[$key]."','".$id."')";
        $pdo->exec($sqlInsert);
    }

	//update type_artiste
	$champs = array('nom_artiste','bio_artiste','date_creation','date_fin','ville','url_site_web','url_site_web_2');
	$values = array($nom,$bio,$debut,$fin,$ville,$site,$site2);
	$sql->update('artiste',$champs,$values,"id_artiste='".$id."'");


	//echo $sql->getQuery();
	if($sql->execute()){
		echo "<script>window.location.href='artiste-".$id.".php';</script>";
	}

}


//============== CONTACT =================//
if((isset($_POST['type_contact']) && $_POST['type_contact']!="")){
	//
	$type_contact = utf8_encode($_POST['type_contact']);
	if($type_contact==1){
		$contact = utf8_encode($_POST['contact_personne']);
	}
	if($type_contact==0){
		$contact = utf8_encode($_POST['contact_structure']);
	}
	//
	$champs = array("type_contact","id_contact");
	$values = array($type_contact,$contact);
	$sql->update('artiste',$champs,$values,"id_artiste='".$id."'");
	//echo $sql->getQuery();
	if($sql->execute()){
		echo "<script>window.location.href='artiste-".$id.".php';</script>";
	}
}
//============== PHOTOS =================//

function FILE_UPLOADER($num_of_uploads=1, $file_types_array=array('jpg','jpeg'), $max_file_size=1048576, $upload_dir="", $callbackpage=''){

  if(!is_numeric($max_file_size)){
    $max_file_size = 1048576;
  }

  $max_file_size_Mo = $max_file_size/1048576;
  if(!isset($_POST['submitted'])){

    $form = '
    <form  action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data">
    <input type="hidden" name="submitted" value="TRUE" id="'.time().'">
    <input type="hidden" name="MAX_FILE_SIZE" value="'.$max_file_size.'">';
    for($x=0;$x<$num_of_uploads;$x++){
      $form .=  '<input id="image" onchange="readURL(this);" type="file" name="file[]" accept=".jpeg,.jpg,.JPEG,.JPG"><font color="red">*</font><br /><br />';
    }
    $form .= '<br><font color="red">*</font>
               Type(s) de fichiers autorisés : ';
    $y=count($file_types_array);
  for($x=0;$x<$y;$x++){
      if($x<$y-1){
        $form .= $file_types_array[$x].', ';
      }else{
        $form .= $file_types_array[$x].'.';
      }
    }
	$form .= "<br><font color='red'>*</font> Poids Max autorisé : ".($max_file_size/1024/1024)." Mo, tout fichier excédant le poids ne sera pas pris en compte<br><br>";
  //  $form .= '<footer><input type="submit" value="Envoyer"></footer></form>';
	$form .= '</form>';
    echo $form;



  }else{

		if(!file_exists($upload_dir)){
			mkdir($upload_dir);
		}
		$sql = new sql();

		foreach($_FILES['file']['error'] as $key => $value){
			if($_FILES['file']['name'][$key]!=""){
				if($value==UPLOAD_ERR_OK){
					$origfilename = $_FILES['file']['name'][$key];
					//
					$txt = new texte();
					$filename = $txt ->cleanFile($origfilename);
					$filenameext = $txt->getExtension();
					//
					$nextID = $sql->nextID('media');
					//
					$filename = $nextID.'-'.$_SESSION['id'].'-'.$filename;
					$file_ext_allow = FALSE;
					//par mesure de securité on suppose l'extenion du fichier fausse
					//verifions si notre fichier fait partie des types autorisés
					if(false !== ($iClef = array_search($filenameext, $file_types_array))) {
					$file_ext_allow = TRUE;
					}
					if($file_ext_allow){
						if($_FILES['file']['size'][$key]<$max_file_size){
							if(move_uploaded_file($_FILES['file']['tmp_name'][$key],'../'.$upload_dir.$filename)){


								$champs = array('path_media','id_artiste');
								$values = array(utf8_decode($upload_dir.$filename),$_SESSION['id']);
								$sql->insert('media',$champs,$values);
								//echo $sql->getQuery();
								$sql->execute();

								imagethumb('../'.$upload_dir.$filename,'../'.$upload_dir.$filename,700);

							}else{
								echo('Une erreur est survenue lors du transfert de '.'<strong>'.$origfilename.'</strong><br />');
								exit();
							}
						}else{
							echo('La taille du fichier '.''.$origfilename.''.' excède les '.$max_file_size_Mo.' Mo autorisé(s)');
							exit();
						}
					}else{
						echo('Le fichier '.''.$origfilename.''.'  a une extension invalide, ERREUR DE TRANSFERT !<br />');
						exit();
					}
				}else{
					echo('Une erreur est survenue lors du transfert de vos titres');
					exit();
				}
			}
		}

		if($callbackpage!=''){
			echo '<script>window.location.href="'.$callbackpage.'";</script>';
		}
	}
}
//============== FIN PHOTOS =================//
?>

<div id="content">
<div class="titre">MODIFIER artiste</div>
	<?php
	//
	$sql = new sql();
	$sql->select('artiste',"id_artiste='".$id."'","*, DAY(date_creation) AS jour_debut, MONTH(date_creation) AS mois_debut, YEAR(date_creation) AS annee_debut, DAY(date_fin) AS jour_fin, MONTH(date_fin) AS mois_fin, YEAR(date_fin) AS annee_fin");
	$sql->execute();
	$res = $sql->result();
	//
	$nom = utf8_encode($res['nom_artiste']);
	$bio = utf8_encode($res['bio_artiste']);
	$type_contact = utf8_encode($res['type_contact']);
	$contact = utf8_encode($res['id_contact']);
	if($type_contact===0){
		if($res['contact_structure']!=0){
			$contact = utf8_encode($res['contact_structure']);
		}
	}else{
		if($res['contact_personne']!=0){
			$contact = utf8_encode($res['contact_personne']);
		}
	}
	$ville = utf8_encode($res['ville']);
	$site = utf8_encode($res['url_site_web']);
	$site2 = utf8_encode($res['url_site_web_2']);
	$stockage = utf8_encode($res['nom_stockage']);

	//
	$jour_debut = $res['jour_debut'];
	$mois_debut = $res['mois_debut'];
	$annee_debut = $res['annee_debut'];
	//
	$jour_fin = $res['jour_fin'];
	$mois_fin = $res['mois_fin'];
	$annee_fin = $res['annee_fin'];

    //Récupération des tags item_tag
	$tag= array();

    $sql_item = new sql();
    $sql_item->select('item_tag',"id_item='".$id."'","*");
    $sql_item->execute();
	while($res_toto = $sql_item->result()){
		$sql_tag = new sql();
		$sql_tag->select('tags',"tag_id=".$res_toto["id_tag"],"tag_name");
		$sql_tag->execute();
		$restag = $sql_tag->result();
		array_push($tag, $restag['tag_name']);

	}

    //
	if($res['date_maj_artiste']!=date('Y-m-d')){
	?>
    <a href="artiste-<?php echo $id; ?>.php?maj=true"><input type="button" value="Signaler Mise à jour" style="float:right; background-color:#9C6; color:#FFF; padding:5px; border:0;"></a><br><br>
    <div class="clear"></div>
	<?php
	}
	?>

    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">
    <article>

        <header>
          INFORMATION
        </header>

        <span class='champs'><strong>Nom :</strong><br /><input type="text" name="nom" value="<?php echo $nom;  ?>" /></span>

        <span class='champs'><strong>Biographie :</strong><br /><textarea name="bio" class="tinymce"><?php echo $bio; ?></textarea></span>
        <span class='champs'><strong>Tags :</strong><br /><input type="text" name="tag" value="<?php foreach ($tag as $key =>  $val) {if(!empty($val) && $key+1 < count($tag)){echo $val.',';}else{echo $val;}}  ?>" /></span>
        <span class='champs'><strong>Date de création :</strong><br />

             <select name="jour_debut" >
                  <?php
                        for($i=1;$i<=31;$i++){
                            if($i<10){
                                $zero = "0";
                            }else{
                                $zero = "";
                            }
                            if($i==$jour_debut){ echo "selected"; }
                    ?>
                  <option value="<?php echo $zero.$i; ?>" <?php if($i==$jour_debut){ echo "selected"; } ?> ><?php echo $zero.$i; ?></option>
                  <?php
                        }
                    ?>
                </select>
                <select name="mois_debut" >
                  <?php
                        for($i=1;$i<=12;$i++){
                            if($i<10){
                                $zero = "0";
                            }else{
                                $zero = "";
                            }
                            if($i==$mois_debut){
                                $selected = "selected";
                            }else{
                                $selected = "";
                            }
                    ?>
                  <option value="<?php echo $zero.$i; ?>" <?php if($i==$mois_debut){ echo "selected"; } ?> ><?php echo $zero.$i; ?></option>
                  <?php
                        }
                    ?>
                </select>
                <select name="annee_debut">
                  <?php

                        for($i=1960;$i<=date('Y');$i++){
                            if($i<10){
                                $zero = "0";
                            }else{
                                $zero = "";
                            }
                    ?>
                  <option value="<?php echo $zero.$i; ?>" <?php if($i==$annee_debut){ echo "selected"; } ?> ><?php echo $zero.$i; ?></option>
                  <?php
                        }
                    ?>
                </select>

        </span>

        <span class='champs'><strong>Date de fin :</strong><br />

             <select name="jour_fin" >
                  <?php
                        for($i=1;$i<=31;$i++){
                            if($i<10){
                                $zero = "0";
                            }else{
                                $zero = "";
                            }
                            if($i==$jour_fin){ echo "selected"; }
                    ?>
                  <option value="<?php echo $zero.$i; ?>" <?php if($i==$jour_fin){ echo "selected"; } ?> ><?php echo $zero.$i; ?></option>
                  <?php
                        }
                    ?>
                </select>
                <select name="mois_fin">
                  <?php
                        for($i=1;$i<=12;$i++){
                            if($i<10){
                                $zero = "0";
                            }else{
                                $zero = "";
                            }
                            if($i==$mois_fin){
                                $selected = "selected";
                            }else{
                                $selected = "";
                            }
                    ?>
                  <option value="<?php echo $zero.$i; ?>" <?php if($i==$mois_fin){ echo "selected"; } ?> ><?php echo $zero.$i; ?></option>
                  <?php
                        }
                    ?>
                </select>
                <select name="annee_fin">
                  <?php

                        for($i=1960;$i<=date('Y');$i++){
                            if($i<10){
                                $zero = "0";
                            }else{
                                $zero = "";
                            }
                    ?>
                  <option value="<?php echo $zero.$i; ?>" <?php if($i==$annee_fin){ echo "selected"; } ?> ><?php echo $zero.$i; ?></option>
                  <?php
                        }
                    ?>
                </select>

        </span>

        <span class='champs'><strong>Ville :</strong><br /><input type="text" name="ville" value="<?php echo $ville;  ?>" /></span>

        <span class='champs'><strong>Site web :</strong><br /><input type="text" name="site" value="<?php echo $site;  ?>" /></span>

        <span class='champs'><strong>Site web :</strong><br /><input type="text" name="site2" value="<?php echo $site2;  ?>" /></span>


        <footer><input type="submit" value="Modifier"/></footer>

    </article>
    </form>

    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">
    <article>
    	<header>
        CONTACT
        </header>

        <span class='champs'>
        <script>
		 function personne(){
			$('#contact_personne').css('display','inline-block');
			$('#contact_structure').css('display','none');
			$('#ajout_personne').css('display','inline-block');
			$('#ajout_structure').css('display','none');
		 }
		 function structure(){
			$('#contact_structure').css('display','inline-block');
			$('#contact_personne').css('display','none');
			$('#ajout_structure').css('display','inline-block');
			$('#ajout_personne').css('display','none');
		 }
		</script>
        <input type="radio" name="type_contact" id="type_artiste" value="1" onClick="personne()" <?php if($res['type_contact']==1){ echo "checked"; } ?> />
        <label for="type_artiste" onClick="personne()"> Personne </label>&nbsp;&nbsp;&nbsp;&nbsp;

        <input type="radio" name="type_contact" id="type_structure" value="0" onClick="structure()" <?php if($res['type_contact']==0){ echo "checked"; } ?> />
        <label for="type_structure" onClick="structure()"> Structure</label><br><br>


        <select name="contact_personne" id="contact_personne" <?php if($res['type_contact']==1){ ?> style="display:inline;" <?php }else{ ?> style="display:none;" <?php } ?> >
        	<option value="0">---</option>
        	<?php
				$sql2 = new sql();
				$sql2->select("personne",'','','ORDER BY nom_personne, prenom_personne');
				$sql2->execute();
				while($res2 = $sql2->result()){
					if($res['type_contact']==1 && ($res2['id_personne']==$res['id_contact'])){
						$personne_selected = "selected";
					}else{
						$personne_selected = "";
					}
			?>
        	<option value="<?php echo $res2['id_personne']; ?>" <?php echo $personne_selected ?> ><?php echo utf8_encode($res2['nom_personne']).' '.utf8_encode($res2['prenom_personne']); ?></option>
            <?php } ?>
        </select>
        <a href="ajout_personne.php" id="ajout_personne" class="lien_annexe"  ></a>

        <select name="contact_structure" id="contact_structure" <?php if($res['type_contact']==0){ ?> style="display:inline;" <?php }else{ ?> style="display:none;" <?php } ?>>
        	<option value="0">---</option>
        	<?php
				$sql2 = new sql();
				$sql2->select("structure_sonotk",'','','ORDER BY nom_structure');
				$sql2->execute();
				while($res2 = $sql2->result()){
					if($res['type_contact']==0 && ($res['id_contact']==$res2['id_structure'])){
						$structure_selected = "selected";
					}else{
						$structure_selected = "";
					}
			?>
        	<option value="<?php echo $res2['id_structure']; ?>" <?php echo $structure_selected ?> ><?php echo utf8_encode($res2['nom_structure']); ?></option>
            <?php } ?>
        </select>
        <a href="ajout_structure.php" id="ajout_structure" class="lien_annexe" style="display:none;" ></a>
        </span>

        <footer><input type="submit" value="modifier"/></footer>

    </article>
    </form>



    <article>
    	<header>
        ALBUM <a href="ajout_album.php" class="lien_annexe" style="vertical-align:bottom" ></a>
        </header>

        <?php
		$sql->select('album','id_artiste='.$id);
		$sql->execute();
		while($res=$sql->result()){
			$jaquette = $res['id_jaquette'];
			$sql2 = new sql();
			$sql2->select('portfolio_sonotheque','id_pj='.$jaquette);
			$sql2->execute();
			$res2 = $sql2->result();
			$jaquette = $res2['url_pj'];
		?>
        <a href="album-<?php echo $res['id_album']; ?>.php"><span class="champs" style="display:block;margin:1px; height:60px; padding:5px 20px 5px 20px; line-height:60px;"><img src="http://www.sonotheque-hn.com/musik_sonotk/<?php echo $jaquette; ?>" height="60" /><strong style="vertical-align:top; margin-left:20px;"><?php echo utf8_encode($res['titre']); ?></strong></span></a>
        <?php } ?>
    </article>

    <article>
		<style type="text/css">
			.image_container img {
				width: 100%; /* This rule is very important, please do not ignore this! */
			}

			article button.btn{
				margin: 10px 10px;
				width: 97%;
			}
		</style>
    	<header>  IMAGE </header>
			<button type="button" class="btn btn-primary pull-left" data-target="#modal" data-toggle="modal">
				Nouvelle photo
			</button>
				<!-- Modal -->
    <div class="modal fade" id="modal" role="dialog" aria-labelledby="modalLabel" tabindex="-1">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalLabel">Cropper l'image</h4>
          </div>
          <div class="modal-body row">
            <div class="img-container">
							<div class="image_container">
								<img id="blah" src="#" alt="your image" class="hidden"/>
							</div>
							<div id="cropped_result" class="col-md-12">
								<div id="cropped_result_view" class="row image_container">
									<img id="img_prev" src="#" alt="your image" class="hidden"/>
								</div>
							</div>
            </div>
          </div>
          <div class="modal-footer">
						<span class='champs'>
		        <?php FILE_UPLOADER($num_of_uploads=1, $file_types_array=array('jpg','jpeg','JPG','JPEG'), $max_file_size=4194304, $upload_dir='media/',$callbackpage='artiste-'.$id.'.php'); ?>
		        </span>
						<progress id="thebar" value="0" style="width: 100%; z-index: 100; margin-top: 20%;" max="0"></progress>
						<button id="crop_button" class="btn btn-secondary hidden">Crop</button>
						<button id="send_button" class="btn btn-success hidden">Valider</button>
            <button id="bt_close" type="button" class="btn btn-default" >Close</button>
          </div>
        </div>
      </div>
    </div>
        	<?php
				$sql2 = new sql();
				$sql2->select("artiste INNER JOIN portfolio_sonotheque ON portfolio_sonotheque.id_pj=artiste.id_photo",'id_artiste='.$id);
				//echo $sql2->getQuery();
				$sql2->execute();
				$res2 = $sql2->result();
				if($res2['id_photo']!=="0"){
			?>
            <span class="media">
            <div class="photo" style="background-image:url(../musik_sonotk/<?php echo $res2['url_pj']; ?>);">
            	<a href="artiste-<?php echo $id; ?>.php?firstphoto=<?php echo $res2['id_pj']; ?>&firstpath=../musik_sonotk/<?php echo $res2['url_pj']; ?>"><div class="close"></div></a>


            </div>
            </span>

        	<?php
				}

				$sql2 = new sql();
				$sql2->select("media",'id_artiste='.$id);
				//echo $sql2->getQuery();
				$sql2->execute();
				while($res2 = $sql2->result()){
			?>
            <span class="media">
            <div class="photo relative">
            	<a href="artiste-<?php echo $id; ?>.php?idphoto=<?php echo $res2['id_media']; ?>&path=../<?php echo $res2['path_media']; ?>"><div class="close"></div></a>
			<div class="inner">
            		<img src="../<?php echo $res2['path_media']; ?>" data-id-photo="<?php echo $res2['id_media']; ?>" style="top:-<?php print $res2['y_align_media']; ?>%">
			</div>
            </div>
            </span>
             <?php } ?>
            <div class="clear"></div>
    </article>

</div>

	<script type="text/javascript" defer>
	$("#bt_close").on('click', function(){
		 location.reload();
	});
		function readURL(input) {
            if (input.files && input.files[0]) {
								$("span.champs form").addClass( "hidden" );
                var reader = new FileReader();
                reader.onload = function (e) {

                    $('#blah').attr('src', e.target.result);
										$('#blah').removeClass( "hidden" );
										$('#crop_button').removeClass( "hidden" );

                };

								console.log(input.files[0]);
                reader.readAsDataURL(input.files[0]);

                setTimeout(initCropper, 1000);
            }
        }

				function initCropper(){
					var minAspectRatio = 0.5;
      		var maxAspectRatio = 1.5;
		    	var image = document.getElementById('blah');

					var cropper = new Cropper(image, {
		        ready: function () {
		          var cropper = this.cropper;
		          var containerData = cropper.getContainerData();
		          var cropBoxData = cropper.getCropBoxData();
		          var aspectRatio = cropBoxData.width / cropBoxData.height;
		          var newCropBoxWidth;

		          if (aspectRatio < minAspectRatio || aspectRatio > maxAspectRatio) {
		            newCropBoxWidth = cropBoxData.height * ((minAspectRatio + maxAspectRatio) / 2);

		            cropper.setCropBoxData({
		              left: (containerData.width - newCropBoxWidth) / 2,
		              width: newCropBoxWidth
		            });
		          }
		        },
		        cropmove: function () {
		          var cropper = this.cropper;
		          var cropBoxData = cropper.getCropBoxData();
		          var aspectRatio = cropBoxData.width / cropBoxData.height;

		          if (aspectRatio < minAspectRatio) {
		            cropper.setCropBoxData({
		              width: cropBoxData.height * minAspectRatio
		            });
		          } else if (aspectRatio > maxAspectRatio) {
		            cropper.setCropBoxData({
		              width: cropBoxData.height * maxAspectRatio
		            });
		          }
		        }
		      });



				// On crop button clicked
				document.getElementById('crop_button').addEventListener('click', function(){

		    		var imgurl =  cropper.getCroppedCanvas().toDataURL("image/jpeg");

						$('#img_prev').removeClass( "hidden" );
						$( "#img_prev" ).addClass( "img-responsive" );
						$("#img_prev").attr("src",imgurl);
						$('#send_button').removeClass( "hidden" );
		    		/*var img = document.createElement("img");
		    		img.src = imgurl;*/

		    		//document.getElementById("cropped_result_view").appendChild(img);
						//$( "#cropped_result_view img" ).addClass( "img-responsive" );

					});
		    		/* ----------------	SEND IMAGE TO THE SERVER------------------------- */
				document.getElementById('send_button').addEventListener('click', function(){
						function afficherAvancement(e){
						   if(e.lengthComputable){
								$('progress').attr({value:e.loaded,max:e.total});
						   }
						};

						cropper.getCroppedCanvas().toBlob(function (blob) {
							var formData = new FormData();
	 						 formData.append('croppedImage', blob);
							 formData.append('origfilename', $('input[type=file]')[0].files[0]);
	 						 // Use `jQuery.ajax` method
	 						 $.ajax('/admin/views/upload.php', {
								 xhr: function() { // xhr qui traite la barre de progression
								   myXhr = $.ajaxSettings.xhr();
								   if(myXhr.upload){ // vérifie si l'upload existe
								      myXhr.upload.addEventListener('progress',afficherAvancement, false);
								   }
								return myXhr;
								 },
	 							 method: "POST",
	 							 data: formData,
	 							 processData: false,
	 							 contentType: false,
	 							 success: function (msg) {
	 								 console.log('Upload success');
									  console.log(msg);
										location.reload();
	 							 },
	 							 error: function () {
	 								 console.log('Upload error');
	 							 }
	 						 });
						 });
					});

		    }
 </script>
