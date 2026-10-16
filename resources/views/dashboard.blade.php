@extends('layouts.app')

@section('titre', 'Tableau de bord')

@section('contenu')
    <h1>Tableau de bord @if($annee) — {{ $annee->libelleAnnee }} @endif</h1>

    {{-- Effectifs : visibles par tous ceux qui peuvent consulter les élèves --}}
    @can('eleves.voir')
        <div class="stats">
            <div class="stat">
                <span class="stat-nombre" data-cible="{{ $nbEleves }}">0</span>
                <span class="stat-label">Élèves enregistrés</span>
            </div>
            <div class="stat">
                <span class="stat-nombre" data-cible="{{ $nbInscrits }}">0</span>
                <span class="stat-label">Élèves inscrits</span>
            </div>
            <div class="stat">
                <span class="stat-nombre" data-cible="{{ $nbAttente }}">0</span>
                <span class="stat-label">En attente de versement</span>
            </div>
        </div>
    @endcan

    {{-- Finances : réservées à ceux qui gèrent les versements --}}
    @can('versements.gerer')
        <div class="stats">
            <div class="stat">
                <span class="stat-nombre" data-cible="{{ $totalVerse }}" data-suffixe=" F">0</span>
                <span class="stat-label">Total encaissé</span>
            </div>
            <div class="stat">
                <span class="stat-nombre" data-cible="{{ $reste }}" data-suffixe=" F">0</span>
                <span class="stat-label">Reste à encaisser</span>
            </div>
            <div class="stat">
                <span class="stat-nombre" data-cible="{{ $taux }}" data-suffixe=" %">0</span>
                <span class="stat-label">Taux de recouvrement</span>
            </div>
        </div>
    @endcan

    <div class="grille-dashboard">
        @can('eleves.voir')
            <div class="carte-formulaire" style="max-width:none;">
                <h2>Élèves inscrits par classe</h2>
                @forelse($parClasse as $classe => $nb)
                    <div class="barre-ligne">
                        <span class="barre-nom">{{ $classe }}</span>
                        <div class="barre-piste">
                            <div class="barre-remplie" style="width: {{ round($nb * 100 / $parClasse->max()) }}%"></div>
                        </div>
                        <strong>{{ $nb }}</strong>
                    </div>
                @empty
                    <p class="fiche-sous">Aucun élève inscrit pour le moment.</p>
                @endforelse
            </div>
        @endcan

        @can('versements.gerer')
            <div class="carte-formulaire" style="max-width:none;">
                <h2>Derniers versements</h2>
                @forelse($derniers as $v)
                    <div class="recu-ligne">
                        <span>
                            {{ $v->inscription?->eleve?->nomEleve }} {{ $v->inscription?->eleve?->prenomEleve }}<br>
                            <small>{{ $v->numRecu }} · {{ $v->dateVersement }}</small>
                        </span>
                        <strong>{{ number_format($v->montantVersement, 0, ',', ' ') }} F</strong>
                    </div>
                @empty
                    <p class="fiche-sous">Aucun versement pour le moment.</p>
                @endforelse
            </div>
        @endcan
    </div>

    {{-- Accès rapides : chaque carte dépend d'une permission --}}
    <h2 style="margin-top: 28px;">Accès rapides</h2>
    <div class="acces-grille">
        @can('inscriptions.gerer')
            <a href="/inscriptions/create" class="acces-carte">
                <span class="acces-icone">📝</span>
                <span><strong>Nouvelle inscription</strong><small>Inscrire un élève dans une classe</small></span>
            </a>
        @endcan

        @can('eleves.gerer')
            <a href="/eleves/create" class="acces-carte">
                <span class="acces-icone">🧒</span>
                <span><strong>Nouvel élève</strong><small>Matricule généré automatiquement</small></span>
            </a>
        @endcan

        @can('parents.gerer')
            <a href="/parents/create" class="acces-carte">
                <span class="acces-icone">👨‍👩‍👧</span>
                <span><strong>Nouveau parent</strong><small>Rattaché à au moins un élève</small></span>
            </a>
        @endcan

        @can('classes.voir')
            <a href="/classes" class="acces-carte">
                <span class="acces-icone">🏫</span>
                <span><strong>Classes</strong><small>Listes imprimables par classe</small></span>
            </a>
        @endcan

        @can('appel.faire')
            <a href="/appels" class="acces-carte">
                <span class="acces-icone">✅</span>
                <span><strong>Faire l'appel</strong><small>Présences et absences par séance</small></span>
            </a>
        @endcan

        @can('notes.saisir')
            <a href="/notes" class="acces-carte">
                <span class="acces-icone">📊</span>
                <span><strong>Feuilles de notes</strong><small>Saisir et consulter les notes</small></span>
            </a>
        @endcan

        @can('bulletins.voir')
            <a href="/bulletins" class="acces-carte">
                <span class="acces-icone">📄</span>
                <span><strong>Bulletins</strong><small>Consulter et télécharger en PDF</small></span>
            </a>
        @endcan

        @can('parametrage.gerer')
            <a href="/tarifs" class="acces-carte">
                <span class="acces-icone">💰</span>
                <span><strong>Tarifs</strong><small>Scolarité par niveau</small></span>
            </a>
        @endcan

        @can('utilisateurs.gerer')
            <a href="/utilisateurs" class="acces-carte">
                <span class="acces-icone">🔐</span>
                <span><strong>Utilisateurs</strong><small>Comptes et rôles</small></span>
            </a>
        @endcan
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('[data-cible]').forEach(function (el) {
            var cible = parseInt(el.dataset.cible, 10);
            var suffixe = el.dataset.suffixe || '';
            var courant = 0;
            var pas = Math.max(1, Math.ceil(cible / 40));
            var minuteur = setInterval(function () {
                courant += pas;
                if (courant >= cible) { courant = cible; clearInterval(minuteur); }
                el.textContent = courant.toLocaleString('fr-FR') + suffixe;
            }, 30);
        });
    </script>
    @endpush
@endsection