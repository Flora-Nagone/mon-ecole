<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletins {{ $classe->libelleClasse }}</title>
    @include('pdf._style')
</head>
<body>
    @forelse($res['eleves'] as $r)
        <div class="{{ $loop->last ? '' : 'saut' }}">
            @include('pdf._bulletin')
        </div>
    @empty
        <p>Aucun élève réellement inscrit dans cette classe.</p>
    @endforelse
</body>
</html>