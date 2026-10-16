@extends('layouts.app')

@section('titre', 'Classe ' . $classe->libelleClasse)

@section('contenu')
    <h1>Liste de la classe {{ $classe->libelleClasse }}</h1>
    <p class="fiche-sous" style="margin-bottom: 18px;">
        {{ $classe->codeClasse }} · {{ $classe->niveau->libelleNiveau }} ·
        Année {{ $classe->anneeScolaire->libelleAnnee }} ·
        <strong>Effectif : {{ $inscrits->count() }} élève(s)</strong>
    </p>

    <p class="no-print">
        <button onclick="window.print()" class="btn btn-principal">🖨 Imprimer la liste</button>
        <a href="/pdf/classe/{{ $classe->idClasse }}" class="btn btn-ajout">Télécharger en PDF</a>
        <a href="/classes" class="btn btn-secondaire">Retour aux classes</a>
    </p>

    <div class="carte" style="margin-top: 16px;">
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Sexe</th>
                    <th>Naissance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inscrits as $i)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $i->eleve->matriculeEleve }}</td>
                        <td><strong>{{ $i->eleve->nomEleve }}</strong></td>
                        <td>{{ $i->eleve->prenomEleve }}</td>
                        <td>{{ $i->eleve->sexeEleve }}</td>
                        <td>{{ $i->eleve->dateNaissance }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="vide">Aucun élève officiellement inscrit dans cette classe.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($enAttente->count() > 0)
        <div class="no-print" style="margin-top: 24px;">
            <h2>En attente de versement ({{ $enAttente->count() }})</h2>
            <p class="fiche-sous">Ces élèves ne figurent pas encore sur la liste officielle.</p>
            <div class="carte" style="padding: 14px 18px;">
                @foreach($enAttente as $i)
                    <div class="recu-ligne">
                        <span>{{ $i->eleve->nomEleve }} {{ $i->eleve->prenomEleve }}</span>
                        <a href="/inscriptions/{{ $i->idInscription }}" class="lien-simple">Ouvrir le dossier</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection