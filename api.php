<?php
#Header
header('Content-Type: text/html; charset=utf-8');

#Requirements
require './vendor/autoload.php';
use ReallySimpleJWT\Token;

#Multi-language support
include './lang.php';

#You should edit the following variables
#Global variables
$GLOBALS['host'] = '';

$GLOBALS['mySQLHost'] = '';
$GLOBALS['mySQLUsername'] = '';
$GLOBALS['mySQLPassword'] = '';

$GLOBALS['usernameRegex'] = '/[\p{L}0-9\-]+/';
$GLOBALS['passwordRegex'] = '/(?=(?:.*\p{L}){3,})(?=(?:.*[0-9]){3,})(?=(?:.*[^\p{L}0-9]){2,}).{12,}/';

#Forbidden usernames
$forbiddenUsernames = array("data");

function getPasswordHash($username) {
	#Init DB
	$pdo = new PDO(sprintf('mysql:host=%s;dbname=gTerminal;charset=utf8', $GLOBALS['mySQLHost']), $GLOBALS['mySQLUsername'], $GLOBALS['mySQLPassword']);

	#Get password hash for username
	$sql = "SELECT * FROM data WHERE username='$username'";
	if (empty($pdo->query($sql)->fetchAll())) {
		$passwordHash = '';
	} else {
		$passwordHash = $pdo->query($sql)->fetchAll()[0]['passwordHash'];
	}
	
	return $passwordHash;
}

function authenticate($username, $password) {
	#Verify password
	return password_verify($password, getPasswordHash($username));
}

function hardClean($string) {
	# Clean string to only allow the letters from a-z (also uppercase) and digits
	$string = str_replace('’', '&rsquo;', $string);
	return strtolower(mb_convert_encoding(preg_replace('/[^a-zA-Z0-9\-]+/', '', mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8')), 'UTF-8', 'ISO-8859-1'));
}
function clean($string) {
	# Clean string to only allow the letters from a-z (also uppercase), digits and special characters
	$string = str_replace('’', '&rsquo;', $string);
	$result = mb_convert_encoding(preg_replace('/[^\\p{L}0-9 !"#$§%&\'()*+,-.\/:;<=>?@€\[\]\^°_{\|}\*~’]+/', '', mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8')), 'UTF-8', 'ISO-8859-1');
	return str_replace('\'', "\'", str_replace('&rsquo;', '’', $result));
}

function randomString($length) {
	return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

# This function is called when the user is authenticated successfully
function authenticated($username) {
	#Initialize DB
	$pdo = new PDO(sprintf('mysql:host=%s;dbname=gTerminal;charset=utf8', $GLOBALS['mySQLHost']), $GLOBALS['mySQLUsername'], $GLOBALS['mySQLPassword']);

	#Check if cmd is given
	if (explode('?', explode(basename(__FILE__), $_SERVER['REQUEST_URI'])[1])[0] != null) {
		#Get cmd
		$cmd = explode('?', explode(basename(__FILE__), $_SERVER['REQUEST_URI'])[1])[0];

		if ($cmd == '/repo/add') {
			if (isset($_POST['repo'])) {
				#Get parameter
				$repo = clean($_POST['repo']);

				if ($repo != '') {
					#Add given repo to DB
					$sql = "INSERT INTO `$username-repos` (repo) VALUES ('$repo');";
					$pdo->prepare($sql)->execute();

					#Build result
					$result = array(
						'content' => getString('gTerminal.repoAddedSuccessfully'),
						'success' => true
					);
				} else {
					#Build result
					$result = array(
						'content' => getString('gTerminal.noParamsGiven'),
						'success' => false
					);
				}
			} else {
				#Build result
				$result = array(
					'content' => getString('gTerminal.noParamsGiven'),
					'success' => false
				);
			}

			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		} elseif ($cmd == '/repo/remove') {
			if (isset($_POST['repo'])) {
				#Get parameter
				$repo = clean($_POST['repo']);

				if ($repo != '') {
					#Remove repo from DB
					$sql = "DELETE FROM `$username-repos` WHERE repo='$repo';";
					$pdo->prepare($sql)->execute();
				
					#Build result
					$result = array(
						'content' => getString('gTerminal.repoRemovedSuccessfully'),
						'success' => true
					);
				} else {
					#Build result
					$result = array(
						'content' => getString('gTerminal.noParamsGiven'),
						'success' => false
					);
				}
			} else {
				#Build result
				$result = array(
					'content' => getString('gTerminal.noParamsGiven'),
					'success' => false
				);
			}

			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		} elseif ($cmd == '/repo/getAll') {
			#Get all repos from DB
			$sql = "SELECT * FROM `$username-repos` ORDER BY repo";
			$data = $pdo->query($sql)->fetchAll();

			$repos = array();
			foreach ($data as $repo) {
				array_push($repos, $repo[0]);
			}

			#Return result
			$result = array(
				'content' => $repos,
				'success' => true
			);
			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		} elseif ($cmd == '/module/add') {
			if (isset($_POST['module'])) {
				#Get parameter
				$module = clean($_POST['module']);

				if ($module != '') {
					#Add module to DB
					$sql = "INSERT INTO `$username-modules` (module) VALUES ('$module');";
					$pdo->prepare($sql)->execute();
				
					#Build result
					$result = array(
						'content' => getString('gTerminal.addedSuccessfully'),
						'success' => true
					);
				} else {
					#Build result
					$result = array(
						'content' => getString('gTerminal.noParamsGiven'),
						'success' => false
					);
				}
			} else {
				#Build result
				$result = array(
					'content' => getString('gTerminal.noParamsGiven'),
					'success' => false
				);
			}

			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		} elseif ($cmd == '/module/delete') {
			if (isset($_POST['module'])) {
				#Get parameter
				$module = clean($_POST['module']);

				if ($module != '') {
					#Remove module from DB
					$sql = "DELETE FROM `$username-modules` WHERE module='$module';";
					$pdo->prepare($sql)->execute();

					#Build result
					$result = array(
						'content' => getString('gTerminal.moduleRemovedSuccessfully'),
						'success' => true
					);
				} else {
					#Build result
					$result = array(
						'content' => getString('gTerminal.noParamsGiven'),
						'success' => false
					);
				}
			} else {
				#Build result
				$result = array(
					'content' => getString('gTerminal.noParamsGiven'),
					'success' => false
				);
			}

			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		} elseif ($cmd == '/module/getAll') {
			#Get all modules from DB
			$sql = "SELECT * FROM `$username-modules` ORDER BY module";
			$data = $pdo->query($sql)->fetchAll();

			$modules = array();
			foreach ($data as $module) {
				array_push($modules, $module[0]);
			}

			#Return result
			$result = array(
				'content' => $modules,
				'success' => true
			);
			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		} elseif ($cmd == '/account/changeUsername') {
			if (isset($_POST['newUsername'])) {
				#Get parameter
				$newUsername = clean($_POST['newUsername']);

				if (preg_match($GLOBALS['usernameRegex'], $newUsername)) {
					if ($newUsername != '') {
						#Update username in data table
						$sql = "UPDATE data SET username='$newUsername' WHERE username='$username';";
						$pdo->prepare($sql)->execute();

						#Rename user’s repos and modules table
						$sql = "RENAME TABLE `$username-repos` TO `$newUsername-repos`;";
						$pdo->prepare($sql)->execute();
						$sql = "RENAME TABLE `$username-modules` TO `$newUsername-modules`;";
						$pdo->prepare($sql)->execute();

						#Build result
						$result = array(
							'content' => getString('gTerminal.usernameChangedSuccessfully'),
							'success' => true
						);
					} else {
						#Build result
						$result = array(
							'content' => getString('gTerminal.noParamsGiven'),
							'success' => false
						);
					}
				} else {
					#Build Result
					$result = array(
						'content' => getString('gTerminal.usernameDoesntMatchRegex'),
						'success' => false
					);
				}
			} else {
				#Build result
				$result = array(
					'content' => getString('gTerminal.noParamsGiven'),
					'success' => false
				);
			}

			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		} elseif ($cmd == '/account/changePassword') {
			if (isset($_POST['password']) && isset($_POST['newPassword'])) {
				#Get parameters
				$password = clean($_POST['password']);
				$newPassword = clean($_POST['newPassword']);
				
				if (authenticate($username, $password)) {
					if ($newPassword != '') {
						if (preg_match($GLOBALS['passwordRegex'], $newPassword)) {
							$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
		
							#Update password hash in data table
							$sql = "UPDATE `data` SET passwordHash='$passwordHash' WHERE username='$username';";
							$pdo->prepare($sql)->execute();
		
							#Build result
							$result = array(
								'content' => getString('gTerminal.passwordChangedSuccessfully'),
								'success' => true
							);
						} else {
							$result = array(
								'content' => getString('gTerminal.passwordDoesntMatchRegex'),
								'success' => false
							);
						}
					} else {
						#Build result
						$result = array(
							'content' => getString('gTerminal.noParamsGiven'),
							'success' => false
						);
					}
				} else {
					#Build result
					$result = array(
						'content' => getString('gTerminal.loginCredsInvalid'),
						'success' => false
					);
				}
			} else {
				#Build result
				$result = array(
					'content' => getString('gTerminal.noParamsGiven'),
					'success' => false
				);
			}
	
			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		} elseif ($cmd == '/account/delete') {
			if (isset($_POST['password'])) {
				#Get parameter
				$password = clean($_POST['password']);
				
				if (authenticate($username, $password)) {
					#Remove user’s repos
					$sql = "DROP TABLE IF EXISTS `$username-repos`;";
					$pdo->prepare($sql)->execute();
					#Remove user’s modules
					$sql = "DROP TABLE IF EXISTS `$username-modules`;";
					$pdo->prepare($sql)->execute();

					##Remove user from data table
					$sql = "DELETE FROM data where username='$username'";
					$pdo->prepare($sql)->execute();

					#Build result
					$result = array(
						'content' => getString('gTerminal.accountSuccessfullyDeleted'),
						'success' => true
					);
				} else {
					#Build result
					$result = array(
						'content' => getString('gTerminal.loginCredsInvalid'),
						'success' => false
					);
				}
			} else {
				#Build result
				$result = array(
					'content' => getString('gTerminal.noParamsGiven'),
					'success' => false
				);
			}

			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		} else {
			#Return result
			$result = array(
				'content' => getString('gTerminal.cmdInvalid'),
				'success' => false
			);
			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		}
	} else {
		#Return result
		$result = array(
			'content' => getString('gTerminal.noCmdGiven'),
			'success' => false
		);
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	}
}

#If login credentials are given
if ((isset($_POST['username']) && isset($_POST['password'])) || (isset($_GET['username']) && isset($_GET['password']))) {
	#Get login credentials from GET or POST
	if (isset($_POST['username']) && isset($_POST['password'])) {
		$username = hardClean($_POST['username']);
		$password = $_POST['password'];
	} elseif (isset($_GET['username']) && isset($_GET['password'])) {
		$username = hardClean($_GET['username']);
		$password = $_GET['password'];
	}

	#Get cmd
	$cmd = explode('?', explode(basename(__FILE__), $_SERVER['REQUEST_URI'])[1])[0];

	#If authentication is successfull
	if (authenticate($username, $password)) {
        if ($cmd == '/account/authenticate') {
            #Return result
            $result = array(
                'content' => getString('gTerminal.authenticatedSuccessfully'),
                'jwt' => Token::create($username, getPasswordHash($username), time() + 604800, $GLOBALS['host']),
                'success' => true
            );
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        }
	} else {
        #Initialize DB
		$pdo = new PDO(sprintf('mysql:host=%s;dbname=gTerminal;charset=utf8', $GLOBALS['mySQLHost']), $GLOBALS['mySQLUsername'], $GLOBALS['mySQLPassword']);

		if ($cmd == '/account/register') {
			#Check if username is forbidden
			if (!in_array($username, $forbiddenUsernames)) {
				if (preg_match($GLOBALS['usernameRegex'], $username) && preg_match($GLOBALS['passwordRegex'], $password)) {
					#Get password hash
					$passwordHash = password_hash($password, PASSWORD_ARGON2ID);

					#Create data table if not exits
					$sql = 'CREATE TABLE IF NOT EXISTS data(
						username TEXT COLLATE utf8mb4_unicode_ci,
						passwordHash TEXT COLLATE utf8mb4_unicode_ci
						);';
					$pdo->exec($sql);

					#Add user’s username and password to data table
					$sql = "INSERT INTO data (username, passwordHash) VALUES ('$username', '$passwordHash')";
					$pdo->prepare($sql)->execute();

					#Create user’s repos table if not exits
					$sql = "CREATE TABLE IF NOT EXISTS `$username-repos`(
                        repo TEXT COLLATE utf8mb4_unicode_ci
						);";
					$pdo->exec($sql);

					#Create user’s modules table if not exits
					$sql = "CREATE TABLE IF NOT EXISTS `$username-modules`(
						module TEXT COLLATE utf8mb4_unicode_ci
						);";
					$pdo->exec($sql);

					#Build result
					$result = array(
						'content' => getString('gTerminal.registeredSuccessfully'),
						'success' => true
					);
				} elseif (!mb_ereg_match($GLOBALS['usernameRegex'], $username) && mb_ereg_match($GLOBALS['passwordRegex'], $password)) {
					#Build result
					$result = array(
						'content' => getString('gTerminal.usernameDoesntMatchRegex'),
						'success' => false
					);
				} elseif (mb_ereg_match($GLOBALS['usernameRegex'], $username) && !mb_ereg_match($GLOBALS['passwordRegex'], $password)) {
					#Build result
					$result = array(
						'content' => getString('gTerminal.passwordDoesntMatchRegex'),
						'success' => false
					);
				} else {
					#Build result
					$result = array(
						'content' => getString('gTerminal.usernameAndPasswordDontMatchRegex'),
						'success' => false
					);
				}

                echo json_encode($result, JSON_UNESCAPED_UNICODE);
			}
        } else {
			#Return result
			$result = array(
				'content' => getString('gTerminal.loginCredsInvalid'),
				'success' => false
			);
			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		}
    }
# If JWT is given
} elseif (isset($_POST['jwt']) || isset($_GET['jwt'])) {
	#Get JWT from GET or POST
	if (isset($_POST['jwt'])) {
		$jwt = $_POST['jwt'];
	} elseif (isset($_GET['jwt'])) {
		$jwt = $_GET['jwt'];
	}

	#Get username from JWT
	$username = Token::getPayload($jwt)['user_id'];

	if (Token::validate($jwt, getPasswordHash($username))) {
		#Get cmd
		$cmd = explode('?', explode(basename(__FILE__), $_SERVER['REQUEST_URI'])[1])[0];

		if ($cmd == '/account/renewJWT') {
			#Return result
			$result = array(
				'content' => getString('gTerminal.JWTRenewedSuccessfully'),
				'jwt' => Token::create($username, getPasswordHash($username), time() + 86400, $GLOBALS['host']),
				'success' => true
			);
			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		} else {
			authenticated($username);
		}
	} else {
		#Return error
		$result = array(
			'content' => getString('gTerminal.loginCredsInvalid'),
			'success' => false
		);
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	}
#If no login credentials are given
} else {
	#Initialize DB
	$pdo = new PDO(sprintf('mysql:host=%s;dbname=gTerminal;charset=utf8', $GLOBALS['mySQLHost']), $GLOBALS['mySQLUsername'], $GLOBALS['mySQLPassword']);

	#Get cmd
	$cmd = explode('?', explode(basename(__FILE__), $_SERVER['REQUEST_URI'])[1])[0];

	if ($cmd == '/account/isUsernameAvailable') {
		#Get username
		$username = hardClean($_GET['username']);

		if ($username != '') {
			#Get all users
			$sql = "SELECT * FROM data;";
			$data = $pdo->query($sql)->fetchAll();

			#Check if username is available
			$available = true;
			foreach ($data as $user) {
				if ($user[0] == $username) {
					$available = false;
				}
			}

			#Check if username is forbidden
			if (in_array($username, $forbiddenUsernames)) {
				$available = false;
			}

			#Build result
			$result = array(
				'content' => $available,
				'success' => true
			);
		} else {
			#Build result
			$result = array(
				'content' => getString('gTerminal.noParamsGiven'),
				'success' => false
			);
		}

		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	} else {
		#Return result
		$result = array(
			'content' => getString('gTerminal.noLoginCredsGiven'),
			'success' => false
		);
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	}
}
?>