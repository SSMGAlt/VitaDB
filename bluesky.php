<?php
// Minimal Bluesky (AT Protocol) client for posting release announcements.
// Needs $bluesky_handle and $bluesky_app_password from config.php - use an
// app password (Settings > App Passwords on bsky.app), never the main
// account password. See the Bluesky setup guide for how to get one.

class Bluesky {
	private $pds = "https://bsky.social";
	private $accessJwt;
	private $did;

	// logs in, returns true/false
	public function login($handle, $appPassword) {
		if (strlen($handle) < 2 || strlen($appPassword) < 2) return false;
		$res = $this->call("com.atproto.server.createSession", array(
			"identifier" => $handle,
			"password" => $appPassword
		), null);
		if (!isset($res['accessJwt'])) return false;
		$this->accessJwt = $res['accessJwt'];
		$this->did = $res['did'];
		return true;
	}

	// posts text, optionally with up to 4 images (by URL)
	public function post($text, $imageUrls = array()) {
		if (!$this->accessJwt) return false;

		$record = array(
			'$type' => 'app.bsky.feed.post',
			'text' => $text,
			'createdAt' => gmdate("Y-m-d\TH:i:s\Z")
		);

		$images = array();
		foreach (array_slice($imageUrls, 0, 4) as $url) {
			$blob = $this->uploadImage($url);
			if ($blob) $images[] = array('image' => $blob, 'alt' => '');
		}
		if (count($images) > 0) {
			$record['embed'] = array(
				'$type' => 'app.bsky.embed.images',
				'images' => $images
			);
		}

		return $this->call("com.atproto.repo.createRecord", array(
			'repo' => $this->did,
			'collection' => 'app.bsky.feed.post',
			'record' => $record
		), $this->accessJwt);
	}

	private function uploadImage($url) {
		$data = @file_get_contents($url);
		if ($data === false) return null;
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_buffer($finfo, $data) ?: 'image/png';
		finfo_close($finfo);

		$ch = curl_init($this->pds . "/xrpc/com.atproto.repo.uploadBlob");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: " . $mime,
			"Authorization: Bearer " . $this->accessJwt
		));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = json_decode(curl_exec($ch), true);
		curl_close($ch);
		return isset($res['blob']) ? $res['blob'] : null;
	}

	private function call($endpoint, $body, $bearer) {
		$headers = array("Content-Type: application/json");
		if ($bearer) $headers[] = "Authorization: Bearer " . $bearer;
		$ch = curl_init($this->pds . "/xrpc/" . $endpoint);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = json_decode(curl_exec($ch), true);
		curl_close($ch);
		return $res ?: array();
	}
}
?>
