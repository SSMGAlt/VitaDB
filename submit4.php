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
	$icon = $request->icon;
	$icon2 = addslashes($request->icon);
	if ($icon != $icon2) die("Invalid icon name");
	$version = $request->version;
	$version2 = addslashes($request->version);
	if ($version != $version2) die("Invalid version value");
	$author = $request->author;
	$author2 = addslashes($request->author);
	if ($author != $author2) die("Invalid author value");
	$url = $request->url;
	$url2 = addslashes($request->url);
	if ($url != $url2) die("Invalid url");
	$url3 = $request->data;
	$url4 = addslashes($request->data);
	if ($url3 != $url4) die("Invalid data url");
	$type = intval($request->type);
	if ($type < 10 || $type > 13) die("Invalid PSP homebrew genre");
	$day = $request->date;
	$day2 = addslashes($request->date);
	if ($day != $day2) die("Invalid date");
	$tid = $request->titleid;
	$tid2 = addslashes($request->titleid);
	if ($tid != $tid2) die("Invalid title ID");
	$description = $request->description;
	$long_description = $request->long_description;
	if (strlen($url3) < 5) $url3 = "";
	$sshot = $request->sshot;
	if (strlen($sshot) < 5) $sshot = "";
	$source = $request->source;
	if (strlen($source) < 5) $source = "";
	$release_page = $request->release_page;
	if (strlen($release_page) < 5) $release_page = "";
	$trailer = $request->trailer;
	if (strlen($trailer) < 5) $trailer = "";
	
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
		if ((strcmp($roles[0],"1") == 0) or (strcmp($roles[0],"2") == 0)){
			
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
			
			if (strlen($url3) > 2) {
				$headers = get_headers($url3, 1);
				$content_length =  $headers['Content-Length'];
				if (is_array($content_length)) {
					$size2 = array_values(array_slice($content_length , -1))[0];
				} else {
					$size2 = $content_length;
				}
			}
			
			$sth2 = mysqli_prepare($con,"INSERT INTO vitadb (name, icon, version, author, url, type, description, data, date, titleid, long_description, screenshots, source, release_page, trailer, size, data_size) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
			mysqli_stmt_bind_param($sth2, "sssssisssssssssss", $name, $icon, $version, $author, $url, $type, $description, $url3, $day, $tid, $long_description, $sshot, $source, $release_page, $trailer, $size, $size2);
			mysqli_stmt_execute($sth2);
			mysqli_stmt_close($sth2);
			$sth3 = mysqli_prepare($con,"INSERT INTO vitadb_log(author,object,hb,date) VALUES(?,?,?,?)");
			$obj = "added";
			$date = date('Y-m-d H:i:s');
			mysqli_stmt_bind_param($sth3, "ssss", $log_author, $obj, $name, $date);
			mysqli_stmt_execute($sth3);
			mysqli_stmt_close($sth3);
			$sth4 = mysqli_query($con,"SELECT MAX(id) AS max_id FROM vitadb");
			$row = mysqli_fetch_array($sth4);
			$hb_id = $row['max_id'];
			require_once('bluesky.php');
			$bsky = new Bluesky();
			if ($bsky->login($bluesky_handle, $bluesky_app_password)) {
				$post_text = "$name $version by $author (PSP Homebrew) can now be downloaded from VitaDB! More info is available here: " . current_site_url() . "/#/info/$hb_id";
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
	} else {		
		mysqli_stmt_close($sth);
		echo("An error occurred: " . mysqli_error($con));
	}

	mysqli_close($con);

?>