@extends('layouts.app')

@section('titre', 'Bulletin')

@section('contenu')
    <p class="no-print">
        <button onclick="window.print()" class="btn btn-principal">🖨 Imprimer le bulletin</button>
        <a href="/pdf/bulletin/{{ $classe->idClasse }}/{{ $periode->idPeriode }}/{{ $r['eleve']->idEleve }}" class="btn btn-ajout">Télécharger en PDF</a>
        <a href="/bulletins?classe={{ $classe->idClasse }}&periode={{ $periode->idPeriode }}" class="btn btn-secondaire">Retour</a>
    </p>

    @if($periode->dateFinPeriode >= now()->toDateString())
        <div class="message message-erreur no-print">Période non terminée : bulletin provisoire.</div>
    @endif

    @include('bulletins._fiche')
@endsection