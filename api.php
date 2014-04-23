<?php
	require_once('assets/includes/sonicflow.php');

	$action = (isset($_POST["action"]) ? $_POST["action"] : "none");
	$search = (isset($_POST["query"]) ? $_POST["query"] : null);
	$id = (isset($_POST["id"]) ? $_POST["id"] : null);
	$channel = "Headphone";

	$result = "{\"action\":\"".$action."\",\"result\":{";

	switch ($action) {
	case "vup":
		system("amixer -q set $channel 2+ unmute");
		$result = $result."\"change\":\"+2\"";
		break;
	case "vdown":
		system("amixer -q set $channel 2- unmute");
		$result = $result."\"change\":\"-2\"";
		break;
	case "toggle":
		system("amixer -q set $channel toggle");
		$result = $result."\"change\":\"toggle\"";
		break;
	case "mute":
		system("amixer -q set $channel mute");
		$result = $result."\"change\":\"mute\"";
		break;
	case "umute":
		system("amixer -q set $channel unmute");
		$result = $result."\"change\":\"unmute\"";
		break;
	case "search":
		if(isset($search)) {
			$searchResults = getSonicFlowResults($search);
			if (count($searchResults) == 0) {
				$provider = "grooveshark";
				$searchResults = getGroovesharkResults($search);
			}
			$numResults = count($searchResults);
	
			$result = $result . 	"\"size\":".$numResults."," . "\"provider\":\"" . $providerName . "\", \"results\":[";
	
			if (is_null($searchResults)) {
				break;
			}
	
			$result = $result . json_encode($searchResults) . "]";
		}else{
			$result = $result."\"result\":\"error\", \"message\":\"Query not set\"";
		}
		break;
	case "queue-add":
	        if(isset($id)) {
                	$added = addSongToQueue($id);
                	unset($_POST['id']);
                	if ($added == R_SUCCESS) {
				$result = $result."\"result\":\"success\", \"message\":\"Song added\"";
                	} else if ($added == R_SONG_REQUEST_TOO_SOON) {
                	        $timeSince = time() - getSongRequestTime($id);
                	        $t = ceil((SONG_REQUEST_LIMIT - $timeSince) / 60);
                	        $s = ($t != 1) ? 's' : '';
				$result = $result."\"result\":\"error\", \"message\":\"Song requested too soon. It can be requested again in $t minute$s\"";
                	}
        	}else{
			$result = $result."\"result\":\"error\", \"message\":\"ID not set\"";
		}
                break;
	case "queue-list":
		$currentQueue = getQueue();
		$result = $result . "[" . json_encode($currentQueue) . 
"]";
	}
	
	$result = $result."}}";

	echo $result;
?>
