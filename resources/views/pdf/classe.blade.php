<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste {{ $classe->libelleClasse }}</title>
    @include('pdf._style')
</head>
<body>
    <div class="entete">
        <h1>Excellence Nagoné École</h1>
        <p><strong>LISTE DE LA CLASSE {{ strtoupper($classe->libelleClasse) }}</strong> · Année scolaire {{ $classe->anneeScolaire->libelleAnnee }}</p>
        <p class="gris">{{ $classe->codeClasse }} · Niveau {{ $classe->niveau->libelleNiveau }} · Effectif : {{ $inscrits->count() }} élève(s)</p>
    </div>

    <table>
        <thead>
            <tr><th>N°</th><th>Matricule</th><th>Nom</th><th>Prénom</th><th>Sexe</th><th>Date de naissance</th></tr>
        </thead>
        <tbody>
            @forelse($inscrits as $i)
                <tr>
                    <td class="c">{{ $loop->iteration }}</td>
                    <td>{{ $i->eleve->matriculeEleve }}</td>
                    <td><strong>{{ $i->eleve->nomEleve }}</strong></td>
                    <td>{{ $i->eleve->prenomEleve }}</td>
                    <td class="c">{{ $i->eleve->sexeEleve }}</td>
                    <td>{{ \Carbon\Carbon::parse($i->eleve->dateNaissance)->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="c gris">Aucun élève officiellement inscrit.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="gris" style="margin-top: 10px;">Document édité le {{ now()->format('d/m/Y') }}</p>
</body>
</html>