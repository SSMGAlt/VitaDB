<?
	// Issues a fresh CSRF token. Cryptographically random (not rand()),
	// time-limited, and inserted as a new row rather than overwriting the
	// IP's existing one - an IP can hold several valid tokens at once, so
	// multiple tabs/concurrent requests don't stomp on each other's token.
	function createXSRF($con){
		$ip = $_SERVER['REMOTE_ADDR'];
		$token = bin2hex(random_bytes(32));
		$expires = date('Y-m-d H:i:s', time() + 14400); // 4 hours

		$sth = mysqli_prepare($con,"INSERT INTO vitadb_csrf (ip, token, expires_at) VALUES (?,?,?)");
		mysqli_stmt_bind_param($sth, "sss", $ip, $token, $expires);
		mysqli_stmt_execute($sth);
		mysqli_stmt_close($sth);

		// best-effort cleanup so this table doesn't grow forever
		mysqli_query($con, "DELETE FROM vitadb_csrf WHERE expires_at < NOW()");

		return $token;
	}

	// Token must belong to this IP, match exactly, and not be expired.
	function checkXSRF($con, $client_token){
		$ip = $_SERVER['REMOTE_ADDR'];
		$sth = mysqli_prepare($con,"SELECT id FROM vitadb_csrf WHERE ip=? AND token=? AND expires_at > NOW()");
		mysqli_stmt_bind_param($sth, "ss", $ip, $client_token);
		mysqli_stmt_execute($sth);
		$data = mysqli_stmt_get_result($sth);
		$valid = (mysqli_num_rows($data) > 0);
		mysqli_stmt_close($sth);
		return $valid;
	}
?>
