<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre') - Excellence Nagoné École</title>
    <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/jquery.dataTables.min.css') }}">
    @stack('styles')
</head>
<body>

    @php
        $utilisateurConnecte = auth()->user();
        $nomUtil = $utilisateurConnecte?->nom_complet ?? '';
    @endphp

    <div class="app">

        <aside class="sidebar" id="sidebar">
            <a href="/" class="side-marque">
                <span class="side-logo">🎓</span>
                <span>Excellence<br>Nagoné École</span>
            </a>

            <nav class="side-nav">
                <a href="/" class="side-lien {{ request()->is('/') ? 'actif' : '' }}">
                    <span class="side-icone">🏠</span> Tableau de bord
                </a>

                @canany(['eleves.voir', 'parents.gerer', 'inscriptions.gerer'])
                    <details class="side-groupe" {{ request()->is('eleves*', 'parents*', 'inscriptions*', 'versements*') ? 'open' : '' }}>
                        <summary><span class="side-icone">👨‍👩‍👧</span> Élèves &amp; Familles <span class="chevron">▾</span></summary>
                        <div class="side-sous">
                            @can('eleves.voir')
                                <a href="/eleves" class="side-lien {{ request()->is('eleves*') ? 'actif' : '' }}">Élèves</a>
                            @endcan
                            @can('parents.gerer')
                                <a href="/parents" class="side-lien {{ request()->is('parents*') ? 'actif' : '' }}">Parents</a>
                            @endcan
                            @can('inscriptions.gerer')
                                <a href="/inscriptions" class="side-lien {{ request()->is('inscriptions*', 'versements*') ? 'actif' : '' }}">Inscriptions</a>
                            @endcan
                        </div>
                    </details>
                @endcanany

                @canany(['classes.voir', 'parametrage.gerer'])
                    <details class="side-groupe" {{ request()->is('classes*', 'annees*', 'periodes*', 'niveaux*', 'tarifs*', 'salles*') ? 'open' : '' }}>
                        <summary><span class="side-icone">🏫</span> Scolarité <span class="chevron">▾</span></summary>
                        <div class="side-sous">
                            @can('classes.voir')
                                <a href="/classes" class="side-lien {{ request()->is('classes*') ? 'actif' : '' }}">Classes</a>
                            @endcan
                            @can('parametrage.gerer')
                                <a href="/annees" class="side-lien {{ request()->is('annees*') ? 'actif' : '' }}">Années scolaires</a>
                                <a href="/periodes" class="side-lien {{ request()->is('periodes*') ? 'actif' : '' }}">Périodes</a>
                                <a href="/niveaux" class="side-lien {{ request()->is('niveaux*') ? 'actif' : '' }}">Niveaux</a>
                                <a href="/tarifs" class="side-lien {{ request()->is('tarifs*') ? 'actif' : '' }}">Tarifs</a>
                                <a href="/salles" class="side-lien {{ request()->is('salles*') ? 'actif' : '' }}">Salles</a>
                            @endcan
                        </div>
                    </details>
                @endcanany

                @can('parametrage.gerer')
                    <details class="side-groupe" {{ request()->is('enseignants*', 'matieres*', 'coefficients*', 'enseignements*', 'domaines*', 'types-evaluation*') ? 'open' : '' }}>
                        <summary><span class="side-icone">📚</span> Enseignement <span class="chevron">▾</span></summary>
                        <div class="side-sous">
                            <a href="/enseignants" class="side-lien {{ request()->is('enseignants*') ? 'actif' : '' }}">Enseignants</a>
                            <a href="/domaines" class="side-lien {{ request()->is('domaines*') ? 'actif' : '' }}">Domaines</a>
                            <a href="/matieres" class="side-lien {{ request()->is('matieres*') ? 'actif' : '' }}">Matières</a>
                            <a href="/coefficients" class="side-lien {{ request()->is('coefficients*') ? 'actif' : '' }}">Coefficients</a>
                            <a href="/enseignements" class="side-lien {{ request()->is('enseignements*') ? 'actif' : '' }}">Enseignements</a>
                            <a href="/types-evaluation" class="side-lien {{ request()->is('types-evaluation*') ? 'actif' : '' }}">Types d'évaluation</a>
                        </div>
                    </details>
                @endcan

                @canany(['seances.gerer', 'appel.faire', 'evaluations.gerer', 'notes.saisir', 'bulletins.voir'])
                    <details class="side-groupe" {{ request()->is('seances*', 'appels*', 'evaluations*', 'notes*', 'bulletins*') ? 'open' : '' }}>
                        <summary><span class="side-icone">📝</span> Suivi pédagogique <span class="chevron">▾</span></summary>
                        <div class="side-sous">
                            @can('seances.gerer')
                                <a href="/seances" class="side-lien {{ request()->is('seances*') ? 'actif' : '' }}">Séances</a>
                            @endcan
                            @can('appel.faire')
                                <a href="/appels" class="side-lien {{ request()->is('appels*') ? 'actif' : '' }}">Appel</a>
                            @endcan
                            @can('evaluations.gerer')
                                <a href="/evaluations" class="side-lien {{ request()->is('evaluations*') ? 'actif' : '' }}">Évaluations</a>
                            @endcan
                            @can('notes.saisir')
                                <a href="/notes" class="side-lien {{ request()->is('notes*') ? 'actif' : '' }}">Feuilles de notes</a>
                            @endcan
                            @can('bulletins.voir')
                                <a href="/bulletins" class="side-lien {{ request()->is('bulletins*') ? 'actif' : '' }}">Bulletins</a>
                            @endcan
                        </div>
                    </details>
                @endcanany

                @can('utilisateurs.gerer')
                    <details class="side-groupe" {{ request()->is('utilisateurs*', 'roles*') ? 'open' : '' }}>
                        <summary><span class="side-icone">🔐</span> Administration <span class="chevron">▾</span></summary>
                        <div class="side-sous">
                            <a href="/utilisateurs" class="side-lien {{ request()->is('utilisateurs*') ? 'actif' : '' }}">Utilisateurs</a>
                            <a href="/roles" class="side-lien {{ request()->is('roles*') ? 'actif' : '' }}">Rôles</a>
<a href="/permissions" class="side-lien {{ request()->is('permissions*') ? 'actif' : '' }}">Permissions</a>
                        </div>
                    </details>
                @endcan
            </nav>

            @auth
                <div class="side-user">
                    <span class="side-avatar">{{ mb_strtoupper(mb_substr($nomUtil, 0, 1)) }}</span>
                    <div class="side-user-infos">
                        <strong>{{ $nomUtil }}</strong>
                        <small>{{ $utilisateurConnecte->getRoleNames()->first() }}</small>
                    </div>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="side-deconnexion" title="Déconnexion">⏻</button>
                    </form>
                </div>
            @endauth
        </aside>

        <div class="fond-menu" id="fondMenu"></div>

        <div class="principal">
            <header class="topbar">
                <button type="button" class="burger" id="burger" aria-label="Menu">☰</button>
                <div class="topbar-titre">@yield('titre')</div>
                <div class="topbar-date">{{ now()->translatedFormat('d F Y') }}</div>
            </header>

            <main class="conteneur">
                @if(session('succes'))
                    <div class="message message-succes">{{ session('succes') }}</div>
                @endif

                @if(session('erreur'))
                    <div class="message message-erreur">{{ session('erreur') }}</div>
                @endif

                @yield('contenu')
            </main>
        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>

    <script>
        // Menu latéral sur petit écran
        var sidebar = document.getElementById('sidebar');
        var fond = document.getElementById('fondMenu');

        document.getElementById('burger').addEventListener('click', function () {
            sidebar.classList.toggle('ouvert');
            fond.classList.toggle('visible');
        });

        fond.addEventListener('click', function () {
            sidebar.classList.remove('ouvert');
            fond.classList.remove('visible');
        });
    </script>

    @stack('scripts')
</body>
</html>