<?php 

$host = "localhost"; // server
$user = "user"; // username of the db
$pass = "pass"; //pass of the database
$db   = "database"; // name of your database
$wordpressPrefix = "prefix"; //prefix of the wp

try {
	$connect = new PDO("mysql:host={$host};dbname={$db}", $user, $pass);
	$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e) {
	echo $e->getMessage();
}

$myfile = fopen("lastPost.txt", "w") or die("Unable to open file!");
if (filesize('lastPost.txt') == 0){
	$fetchDataPosProduk		= "SELECT * FROM ".$wordpressPrefix."_posts WHERE post_type='product' AND post_status='publish'";
}else{
    $batasProduk =  fread($myfile,filesize("lastPost.txt"));
    if(strlen($batasProduk)>1){
		$fetchDataPosProduk		= "SELECT * FROM ".$wordpressPrefix."_posts WHERE post_type='product' AND post_status='publish' AND id>".$batasProduk;
    }
}

fclose($myfile);

try{
	$stmstPosProduk			= $connect->prepare($fetchDataPosProduk);
	$stmstPosProduk->execute();
	$dataPostProduk			= $stmstPosProduk->fetchAll();
}catch(PDOException $e) {
	echo $e->getMessage();
}

foreach ($dataPostProduk as $dataPost) {
	$postId		= $dataPost['ID'];
	$postTitle	= $dataPost['post_title'];
	echo $postId;
	try{
		$fetchmetaPost	= "SELECT * FROM ".$wordpressPrefix."_postmeta WHERE post_id=:postId";
		$stmtFetchMeta	= $connect->prepare($fetchmetaPost);
		$stmtFetchMeta->bindParam(':postId',$postId);
		$stmtFetchMeta->execute();
		$metapostData =$stmtFetchMeta->fetchAll();
		// print_r($metapostData); exit;
		foreach ($metapostData as $dataMetaPost) {
			$_thumbnail_id='';
			$_product_image_gallery='';
			if($dataMetaPost['meta_key']=='_thumbnail_id'){
				$_thumbnail_id			= $dataMetaPost['meta_value'];

				try{
					$updateThumbTitle	= "UPDATE ".$wordpressPrefix."_posts SET post_title=:postTitle WHERE ID=:thumbId";
					$stmtUpdateThumb	= $connect->prepare($updateThumbTitle);
					$stmtUpdateThumb->bindParam(':postTitle',$postTitle);
					$stmtUpdateThumb->bindParam(':thumbId',$_thumbnail_id);
					$stmtUpdateThumb->execute();
					try{
						$cekMetaImage		= "SELECT * FROM ".$wordpressPrefix."_postmeta WHERE post_id=:ImageId";
						$stmtCekMetaId		= $connect->prepare($cekMetaImage);
						$stmtCekMetaId->bindParam(':ImageId',$imageId);
						$stmtCekMetaId->execute();
						$matakeyExist		= false;
						$metaId				= null;
						$cekmetaGambr = $stmtCekMetaId->fetchAll();
						foreach ($cekmetaGambr as $dataMetaImg) {
							if(in_array('_wp_attachment_image_alt', $dataMetaImg)){
								$metaID = $dataMetaImg['meta_id'];
								$matakeyExist=true;
							}
						}
						if($matakeyExist){
							try{
								$updatemetaKeyImg		= "UPDATE ".$wordpressPrefix."_postmeta SET meta_value=:imageAlt WHERE meta_id=:Mid";
								$stmtUpdtMetaImg		= $connect->prepare($updatemetaKeyImg);
								$stmtUpdtMetaImg->bindParam(':imageAlt',$postTitle);
								$stmtUpdtMetaImg->bindParam(':Mid',$metaID);
								$stmtUpdtMetaImg->execute();
								echo $postTitle." success update\n";
							}catch(PDOException $e) {
								echo $e->getMessage();
							}
							
						}else{
							$metakey = '_wp_attachment_image_alt';
							try{
								$insertMetaKey			= "INSERT ".$wordpressPrefix."_postmeta SET post_id=:postId,meta_key=:metaKey,meta_value=:metaValue";
								$stmtInsertMetaImg		= $connect->prepare($insertMetaKey);
								$stmtInsertMetaImg->bindParam(':postId',$imageId);
								$stmtInsertMetaImg->bindParam(':metaKey',$metakey);
								$stmtInsertMetaImg->bindParam(':metaValue',$postTitle);
								$stmtInsertMetaImg->execute();
								echo $postTitle." success change\n";
							}catch(PDOException $e) {
								echo $e->getMessage();
							}							
						}
					}catch(PDOException $e) {
						echo $e->getMessage();
					}

				}catch(PDOException $e) {
					echo $e->getMessage();
				}
			}
			if($dataMetaPost['meta_key']=='_product_image_gallery'){
				$_product_image_gallery	= $dataMetaPost['meta_value'];
				$data=explode(",", $_product_image_gallery);
				if(count($data)>0 & !empty($data[0])){
					foreach ($data as $dataImageGallerry) {
						$imageId			= $dataImageGallerry;
						try{
							$updateImageTitle	= "UPDATE ".$wordpressPrefix."_posts SET post_title=:postTitle WHERE ID=:imageId";
							$stmtUpdateImage	= $connect->prepare($updateImageTitle);
							$stmtUpdateImage->bindParam(':postTitle',$postTitle);
							$stmtUpdateImage->bindParam(':imageId',$imageId);
							$stmtUpdateImage->execute();
							try{
								$cekMetaImage		= "SELECT * FROM ".$wordpressPrefix."_postmeta WHERE post_id=:ImageId";
								$stmtCekMetaId		= $connect->prepare($cekMetaImage);
								$stmtCekMetaId->bindParam(':ImageId',$imageId);
								$stmtCekMetaId->execute();
								$cekmetaGambr = $stmtCekMetaId->fetchAll();
								$matakeyExist		= false;
								$metaId				= null;
								foreach ($cekmetaGambr as $dataMetaImg) {
									if(in_array('_wp_attachment_image_alt', $dataMetaImg)){
                                        $metaID = $dataMetaImg['meta_id'];										
                                        $matakeyExist=true;
									}
								}if($matakeyExist){
									try{
										$updatemetaKeyImg		= "UPDATE ".$wordpressPrefix."_postmeta SET meta_value=:imageAlt WHERE meta_id=:Mid";
										$stmtUpdtMetaImg		= $connect->prepare($updatemetaKeyImg);
										$stmtUpdtMetaImg->bindParam(':imageAlt',$postTitle);
										$stmtUpdtMetaImg->bindParam(':Mid',$metaID);
										$stmtUpdtMetaImg->execute();
										echo $postTitle." success update\n";
									}catch(PDOException $e) {
										echo $e->getMessage();
									}
									
								}else{
									$metakey = '_wp_attachment_image_alt';
									try{
										$insertMetaKey			= "INSERT ".$wordpressPrefix."_postmeta SET post_id=:postId,meta_key=:metaKey,meta_value=:metaValue";
										$stmtInsertMetaImg		= $connect->prepare($insertMetaKey);
										$stmtInsertMetaImg->bindParam(':postId',$imageId);
										$stmtInsertMetaImg->bindParam(':metaKey',$metakey);
										$stmtInsertMetaImg->bindParam(':metaValue',$postTitle);
										$stmtInsertMetaImg->execute();
										echo $postTitle." success change\n";
									}catch(PDOException $e) {
										echo $e->getMessage();
									}
									
								}
	
							}catch(PDOException $e) {
								echo $e->getMessage();
							}
														
						}catch(PDOException $e) {
							echo $e->getMessage();
						}
						
	
					}
				}
			}
		}
		
	}catch(PDOException $e) {
		echo $e->getMessage();
	}
	$myfile = fopen("lastPost.txt", "w") or die("Unable to open file!");
	$txt = $postId;
	fwrite($myfile, $txt);
	fclose($myfile);
}