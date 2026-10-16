@extends('layouts.app')

@section('titre', 'Bulletins de la classe')

@section('contenu')
    <p class="no-print">
        <button onclick="window.print()" class="btn btn-principal">🖨 Imprimer les {{ $res['effectif'] }} bulletins</button>
        <a href="/pdf/bulletins/{{ $classe->idClasse }}/{{ $periode->idPeriode }}" class="btn btn-ajout">Télécharger en PDF</a>
        <a href="/bulletins?classe={{ $classe->idClasse }}&periode={{ $periode->idPeriode }}" class="btn btn-secondaire">Retour</a>
    </p>

    @if($periode->dateFinPeriode >= now()->toDateString())
        <div class="message message-erreur no-print">Période non terminée : bulletins provisoires.</div>
    @endif

    @forelse($res['eleves'] as $r)
        <div class="saut-page">
            @include('bulletins._fiche')
        </div>
    @empty
        <div class="message message-erreur">Aucun élève réellement inscrit dans cette classe.</div>
    @endforelse
@endsection