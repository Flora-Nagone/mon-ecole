@extends('layouts.app')

@section('titre', 'Fiche parent')

@section('contenu')
    <h1>{{ $parent->nomParent }} {{ $parent->prenomParent }}</h1>

    <div class="carte-formulaire" style="max-width: 900px;">
        <div class="chiffres">
            <div><span>Téléphone</span><strong>{{ $parent->telephoneParent }}</strong></div>
            <div><span>Email</span><strong style="font-size:1rem;">{{ $parent->emailParent }}</strong></div>
            <div><span>Adresse</span><strong style="font-size:1rem;">{{ $parent->adresseParent }}</strong></div>
            <div><span>Profession</span><strong style="font-size:1rem;">{{ $parent->professionParent ?: '—' }}</strong></div>
        </div>

        <div class="actions-formulaire" style="margin-top:0;">
            <a href="/parents/{{ $parent->idParent }}/edit" class="btn btn-modifier">Modifier</a>
            <a href="/parents" class="btn btn-secondaire">Retour aux parents</a>
        </div>
    </div>

    <h2 style="margin-top:30px;">Élèves dont il est responsable</h2>
    <div class="carte" style="max-width: 900px;">
        <table>
            <thead>
                <tr><th>Matricule</th><th>Élève</th><th>Lien</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($parent->eleves as $e)
                    <tr>
                        <td>{{ $e->matriculeEleve }}</td>
                        <td><strong>{{ $e->nomEleve }}</strong> {{ $e->prenomEleve }}</td>
                        <td><span class="badge badge-solde">{{ $e->pivot->lienParente }}</span></td>
                        <td class="actions">
                            <form action="/parents/{{ $parent->idParent }}/eleves/{{ $e->idEleve }}" method="POST"
                                  onsubmit="return confirm('Retirer cet élève de ce parent ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-supprimer">Retirer</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h2 style="margin-top:30px;">Rattacher un autre élève</h2>
    <div class="carte-formulaire" style="max-width: 900px;">
        <form method="POST" action="/parents/{{ $parent->idParent }}/eleves">
            @csrf
            <div class="grille-champs">
                <div class="champ">
                    <label for="eleve">Élève</label>
                    <select id="eleve" name="Eleve_idEleve" required>
                        <option value="">-- Choisir un élève --</option>
                        @foreach($disponibles as $e)
                            <option value="{{ $e->idEleve }}">{{ $e->matriculeEleve }} - {{ $e->nomEleve }} {{ $e->prenomEleve }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="champ">
                    <label for="lienParente">Lien de parenté</label>
                    <select id="lienParente" name="lienParente" required>
                        @foreach($liens as $lien)
                            <option value="{{ $lien }}">{{ $lien }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">Rattacher</button>
            </div>
        </form>
    </div>
@endsection