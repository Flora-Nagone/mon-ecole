<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - Excellence Nagoné École</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Lora:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="page-login">
    <div class="boite-login">
        <h1>🎓 Excellence Nagoné École</h1>
        <p class="fiche-sous" style="text-align:center; margin-bottom: 22px;">Connectez-vous pour accéder à l'application</p>

        @if(session('erreur'))
            <div class="message message-erreur">{{ session('erreur') }}</div>
        @endif
        @if(session('succes'))
            <div class="message message-succes">{{ session('succes') }}</div>
        @endif

        <form method="POST" action="/login">
            @csrf

            <div class="champ" style="margin-bottom: 16px;">
                <label for="login">Identifiant</label>
                <input id="login" name="login" value="{{ old('login') }}"
                       class="@error('login') invalide @enderror" autofocus required>
                @error('login') <div class="erreur">{{ $message }}</div> @enderror
            </div>

            <div class="champ" style="margin-bottom: 22px;">
                <label for="motDePasse">Mot de passe</label>
                <input id="motDePasse" type="password" name="motDePasse"
                       class="@error('motDePasse') invalide @enderror" required>
                @error('motDePasse') <div class="erreur">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-principal" style="width:100%;">Se connecter</button>
        </form>
    </div>
</body>
</html>