<?php
	
	// Builds an absolute URL to this deployment - avoids hardcoding any one domain
	function current_site_url() {
		$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		return $scheme . "://" . $_SERVER['HTTP_HOST'];
	}

	// Getting POST data, performing some security checks
	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata);
	$email = $request->user;
	$email2 = addslashes($request->user);
	if ($email != $email2) die("Invalid email");
	$log_author = $request->log_author;
	$log_author2 = addslashes($request->log_author);
	if ($log_author != $log_author2) die("Invalid author name");
	$pass = $request->password;
	$password2 = addslashes($request->password);
	if ($pass != $password2) die("Invalid password");
	$name = $request->name;
	$name2 = addslashes($request->name);
	if ($name != $name2) die("Invalid name");
	$version = $request->version;
	$version2 = addslashes($request->version);
	if ($version != $version2) die("Invalid version value");
	$author = $request->author;
	$author2 = addslashes($request->author);
	if ($author != $author2) die("Invalid author value");
	$url = $request->url;
	$url2 = addslashes($request->url);
	if ($url != $url2) die("Invalid url");
	$day = $request->date;
	$day2 = addslashes($request->date);
	if ($day != $day2) die("Invalid date");
	$id = $request->id;
	$hb_id = $id;
	$id2 = addslashes($request->id);
	if ($id != $id2) die("Invalid ID");
	$description = $request->description;
	$long_description = $request->long_description;
	$sshot = $request->sshot;
	if (strlen($sshot) < 5) $sshot = "";
	$source = $request->source;
	$release_page = $request->release_page;
	$trailer = $request->trailer;
	$new_release = $request->new_release;
	$new_release2 = addslashes($request->new_release);
	if ($new_release != $new_release2) die("Invalid twitter option");
	
	// Creating connection
	include 'config.php';
	$con = mysqli_connect($servername, $username, $password, $dbname);
	
	// Checking connection
	if (mysqli_connect_errno()){
		die("Connection failed: " . mysqli_connect_error());
	}
	
	// Checking CSRF token
	include 'xsrf.php';
	$xsrf = $_COOKIE['XSRF-TOKEN'];
	$hdr_xsrf = $_SERVER['HTTP_X_XSRF_TOKEN'];
	if ((!is_string($xsrf)) or (!is_string($hdr_xsrf)) or (!hash_equals($xsrf, $hdr_xsrf)) or (!checkXSRF($con, $xsrf))){
		mysqli_close($con);
		die("Unauthorized access.");
	}
	
	$sth = mysqli_prepare($con,"SELECT roles FROM vitadb_users WHERE email=? AND password=?");
	mysqli_stmt_bind_param($sth, "ss", $email, $pass);
	mysqli_stmt_execute($sth);
	$data = mysqli_stmt_get_result($sth);
	
	if (mysqli_num_rows($data)>0){
		while($r = mysqli_fetch_assoc($data)) {
			$roles = explode(";",$r['roles']);	
		}
		mysqli_stmt_close($sth);
		if ((strcmp($roles[0],"1") == 0) or (strcmp($roles[0],"2") == 0) or (strcmp($roles[0],"3") == 0)){
			
			stream_context_set_default(
				array(
					'http' => array(
						'method' => 'HEAD'
					)
				)
			);
			$headers = get_headers($url, 1);
			$content_length =  $headers['Content-Length'];
			$size = 0;
			$size2 = 0;

			if (is_array($content_length)) {
				$size = array_values(array_slice($content_length , -1))[0];
			} else {
				$size = $content_length;
			}
			
			$sth2 = mysqli_prepare($con,"UPDATE vitadb SET name=?,version=?,author=?,url=?,description=?,date=?,long_description=?,screenshots=?,source=?,release_page=?,trailer=?,size=?,data_size=? WHERE id=?");
			mysqli_stmt_bind_param($sth2, "sssssssssssssi", $name, $version, $author, $url, $description, $day, $long_description, $sshot, $source, $release_page, $trailer, $size, $size2, $id);
			mysqli_stmt_execute($sth2);
			mysqli_stmt_close($sth2);
			$sth3 = mysqli_prepare($con,"INSERT INTO vitadb_log(author,object,hb,date) VALUES(?,?,?,?)");
			$obj = "updated";
			$date = date('Y-m-d H:i:s');
			mysqli_stmt_bind_param($sth3, "ssss", $log_author, $obj, $name, $date);
			mysqli_stmt_execute($sth3);
			mysqli_stmt_close($sth3);
			mysqli_stmt_bind_param($sth3, "ssss", $log_author, $obj, $name, $date);
			mysqli_stmt_execute($sth3);
			mysqli_stmt_close($sth3);
			if ($new_release == 1) {
				require_once('bluesky.php');
				$bsky = new Bluesky();
				if ($bsky->login($bluesky_handle, $bluesky_app_password)) {
					$post_text = "$name $version by $author can now be downloaded from VitaDB or Easy Plugin! More info is available here: " . current_site_url() . "/#/info/$hb_id";
					if (strlen($sshot) > 5){
						$screenshots = explode(';', $sshot);
						$image_urls = array();
						foreach ($screenshots as $screenshot) {
							$image_urls[] = current_site_url() . "/" . $screenshot;
						}
						$bsky->post($post_text, $image_urls);
					} else {
						$bsky->post($post_text);
					}
				}
			}
		}
	} else {		
		mysqli_stmt_close($sth);
		echo("An error occurred: " . mysqli_error($con));
	}

	mysqli_close($con);
?>