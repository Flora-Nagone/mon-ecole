@extends('layouts.app')

@section('titre', 'Rôles et permissions')

@section('contenu')
    <h1>Rôles et permissions</h1>

    <div class="montant-auto" style="margin-bottom: 22px;">
        Un <strong>rôle</strong> regroupe des <strong>permissions</strong>. Chaque page de l'application est protégée
        par une permission : pour donner un accès, il suffit d'ajouter la permission au rôle.
    </div>

    <div class="carte" style="padding: 18px;">
        <table>
            <thead>
                <tr>
                    <th>Permission</th>
                    @foreach($roles as $role)
                        <th class="groupe" style="text-align:center;">{{ $role->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($permissions as $p)
                    <tr>
                        <td><code>{{ $p->name }}</code></td>
                        @foreach($roles as $role)
                            <td style="text-align:center;">
                                @if($role->hasPermissionTo($p->name))
                                    <span class="badge badge-inscrit">✔</span>
                                @else
                                    <span class="fiche-sous">-</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection