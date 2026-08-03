<?php

	// Ranks developers by number of Game Ports (type = 2) they've made.
	// Multi-author entries ("Name1 & Name2") are split and each person
	// credited individually, same convention used everywhere else in the app.

	// Creating connection
	include 'config.php';
	$con = mysqli_connect($servername, $username, $password, $dbname);
	
	// Checking connection
	if (mysqli_connect_errno()){
		die("Connection failed: " . mysqli_connect_error());
	} 
	
	$sth = mysqli_query($con,"SELECT author FROM vitadb WHERE type = 2");
	if ($sth){
		$counts = array();
		while($r = mysqli_fetch_assoc($sth)) {
			$authors = explode(' & ', $r['author']);
			foreach ($authors as $person) {
				$person = trim($person);
				if (strlen($person) == 0) continue;
				if (!isset($counts[$person])) {
					$counts[$person] = 0;
				}
				$counts[$person]++;
			}
		}
		
		arsort($counts);
		$counts = array_slice($counts, 0, 50, true);
		
		$rows = array();
		foreach ($counts as $name => $count) {
			$avatar = "";
			$sth2 = mysqli_prepare($con,"SELECT avatar FROM vitadb_users WHERE name=?");
			mysqli_stmt_bind_param($sth2, "s", $name);
			mysqli_stmt_execute($sth2);
			$data = mysqli_stmt_get_result($sth2);
			if (mysqli_num_rows($data)>0){
				$u = mysqli_fetch_assoc($data);
				$avatar = $u['avatar'];
			}
			mysqli_stmt_close($sth2);
			$rows[] = array('name' => $name, 'ports' => $count, 'avatar' => $avatar);
		}
		
		echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	} else {
		echo("An error occurred: " . mysqli_error($con));
	}

	mysqli_close($con);
?>