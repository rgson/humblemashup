<?php

	require '../config.php';
	
	if(empty($_GET['id']))
		APIOutput::http_response(401, 'No bundle specified.');
	
	if(isset($_GET['steamid']) && !isset($_GET['steamkey']))
		APIOutput::http_response(401, 'No Steam API key provided.');

	$steamEnabled = isset($_GET['steamkey']) && isset($_GET['steamid']);
	
	switch($_GET['id']) {
	case 'regular':
		$bundle = HumbleBundle::getHumbleBundle();
		break;
	case 'weekly':
		$bundle = HumbleBundle::getWeeklyBundle();
		break;
	default:
		APIOutput::http_response(401, "Invalid bundle ID: \"{$_GET['id']}\".");
	}

	foreach($bundle->getGames() as $game) {
	
		$score = GiantBomb::getScore($game->getTitle());
		$appid = Steam::getAppId($game->getTitle());
		
		if(isset($appid)) {
			$picture = Steam::getPicture($appid);
			$url = Steam::getURL($appid);
		}
		
		if(isset($score))	$game->setScore($score);
		if(isset($appid))	$game->setAppid($appid);
		if(isset($picture))	$game->setPicture($picture);
		if(isset($url))		$game->setUrl($url);
	
		if($steamEnabled && isset($appid)) {
			$owned = Steam::ownedBy($game->getAppid(), $_GET['steamid'], $_GET['steamkey']);
			
			if(isset($owned))	$game->setOwned($owned);
		}
	}

	$response = array(
		'success' => true,
		'user' => $_GET['steamid'],
		'bundle' => $bundle
	);

	APIOutput::output($response);

?>
