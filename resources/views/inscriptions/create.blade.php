@extends('layouts.app')

@section('titre', 'Nouvelle inscription')

@section('contenu')
    <h1>Nouvelle inscription ({{ $annee->libelleAnnee }})</h1>
   <p>{{ $eleves->count() }} élève(s) disponible(s)</p>
    <div class="carte-formulaire">
        <form method="POST" action="/inscriptions">

                    @if($eleves->isEmpty())
                <div class="message message-erreur">
                    Tous les élèves sont déjà inscrits cette année.
                    <a href="/eleves/create">Ajouter un nouvel élève</a>
                </div>
            @endif
            @csrf

            <div class="grille-champs">
                <div class="champ">
                    <label for="eleve">Élève</label>
                    <select id="eleve" name="Eleve_idEleve" class="@error('Eleve_idEleve') invalide @enderror" required>
                        <option value="">-- Choisir un élève --</option>
                        @foreach($eleves as $e)
                        <option value="{{ $e->idEleve }}" {{ old('Eleve_idEleve', request('eleve')) == $e->idEleve ? 'selected' : '' }}>     
                        {{ $e->matriculeEleve }} - {{ $e->nomEleve }} {{ $e->prenomEleve }}
                            </option>
                        @endforeach
                    </select>
                    @error('Eleve_idEleve') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="classe">Classe</label>
                    <select id="classe" name="Classe_idClasse" class="@error('Classe_idClasse') invalide @enderror" required>
                        <option value="">-- Choisir une classe --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->idClasse }}"
                                    data-montant="{{ $tarifs[$c->Niveau_idNiveau] ?? 0 }}"
                                    {{ old('Classe_idClasse') == $c->idClasse ? 'selected' : '' }}>
                                {{ $c->libelleClasse }} ({{ $c->niveau->libelleNiveau }})
                            </option>
                        @endforeach
                    </select>
                    @error('Classe_idClasse') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="montant-auto" id="boiteMontant" style="display:none;">
                <div>Scolarité du niveau : <strong id="montant">0</strong> FCFA</div>
                <div>Pour être inscrit, il faut verser au moins <strong id="seuil">0</strong> FCFA
                    ({{ \App\Models\Inscription::SEUIL_POURCENT }} %)</div>
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">Créer le dossier</button>
                <a href="/inscriptions" class="btn btn-secondaire">Annuler</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        var pourcent = {{ \App\Models\Inscription::SEUIL_POURCENT }};
        var classe = document.getElementById('classe');

        function formater(n) { return n.toLocaleString('fr-FR'); }

        function majMontant() {
            var option = classe.options[classe.selectedIndex];
            var montant = parseInt(option.dataset.montant || 0, 10);
            var boite = document.getElementById('boiteMontant');
            if (!option.value) { boite.style.display = 'none'; return; }
            boite.style.display = 'block';
            document.getElementById('montant').textContent = montant ? formater(montant) : 'non défini';
            document.getElementById('seuil').textContent = formater(Math.ceil(montant * pourcent / 100));
        }

        classe.addEventListener('change', majMontant);
        majMontant();
    </script>
    @endpush
@endsection