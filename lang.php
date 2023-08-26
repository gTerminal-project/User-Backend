<?php
function getLang() {
	$supportedLangs = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/lang.json'), true)['langs'];
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$userAcceptedLangs = explode(',', explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE'])[0]);
	} else {
		$userAcceptedLangs = array();
	}

	//Set fallback language
	$lang = "en";

	//Check if lang parameter is given
	if (isset($_GET['lang'])) {
		//Check if given language is supported
		if (in_array($_GET['lang'], $supportedLangs)) {
			$lang = $_GET['lang'];
		} else {
			//Check if one of browser accepted language is supported
			foreach ($userAcceptedLangs as $userAcceptedLang) {
				$userAcceptedLang = explode('-', $userAcceptedLang)[0];
				if (in_array($userAcceptedLang, $supportedLangs)) {
					$lang = $userAcceptedLang;
					break;
				}
			}
		}
	} elseif (isset($_POST['lang'])){
		//Check if given language is supported
		if (in_array($_POST['lang'], $supportedLangs)) {
			$lang = $_POST['lang'];
		} else {
			//Check if one of browser accepted language is supported
			foreach ($userAcceptedLangs as $userAcceptedLang) {
				$userAcceptedLang = explode('-', $userAcceptedLang)[0];
				if (in_array($userAcceptedLang, $supportedLangs)) {
					$lang = $userAcceptedLang;
					break;
				}
			}
		}
	} elseif (isset($_COOKIE['lang'])) {
		//Check if given language is supported
		if (in_array($_COOKIE['lang'], $supportedLangs)) {
			$lang = $_COOKIE['lang'];
		} else {
			//Check if one of browser accepted language is supported
			foreach ($userAcceptedLangs as $userAcceptedLang) {
				$userAcceptedLang = explode('-', $userAcceptedLang)[0];
				if (in_array($userAcceptedLang, $supportedLangs)) {
					$lang = $userAcceptedLang;
					break;
				}
			}
		}
	} else {
		//Check if one of browser accepted language is supported
		foreach ($userAcceptedLangs as $userAcceptedLang) {
			$userAcceptedLang = explode('-', $userAcceptedLang)[0];
			if (in_array($userAcceptedLang, $supportedLangs)) {
				$lang = $userAcceptedLang;
				break;
			}
		}
	}

	//Return language
	return $lang;
}
function getString($id) {
	$lang = getLang();
	$langIndex = array_search($lang, json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/lang.json'), true)['langs']);
	$strings =  json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/lang.json'), true)['pack'];
	return $strings[$id][$langIndex];
}
?>