<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>Réinitialisation du mot de passe</h2>

		<div>
			Pour créer un nouveau mot de passe, merci de bien vouloir cliquer sur ce lien : {{ URL::to('password/reset', array($token)) }}.<br/>
			Ce lien expire dans {{ Config::get('auth.reminder.expire', 60) }} minutes.
		</div>
	</body>
</html>
