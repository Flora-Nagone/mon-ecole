<?php

use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnneeController;
use App\Http\Controllers\AppelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BulletinController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\CoefficientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomaineController;
use App\Http\Controllers\EleveController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\EnseignementController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\MatiereController;
use App\Http\Controllers\NiveauController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\TarifController;
use App\Http\Controllers\TypeEvaluationController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\VersementController;

// ---------- Pages publiques ----------
Route::get('/login', [AuthController::class, 'formulaire'])->name('login');
Route::post('/login', [AuthController::class, 'connexion']);

// ---------- Utilisateur connecté ----------
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'deconnexion']);
    Route::get('/', [DashboardController::class, 'index']);

    // Élèves : consultation
    Route::middleware('permission:eleves.voir')->group(function () {
        Route::get('/eleves', [EleveController::class, 'index']);
    });

    // Élèves : écriture
    Route::middleware('permission:eleves.gerer')->group(function () {
        Route::get('/eleves/create', [EleveController::class, 'create']);
        Route::post('/eleves', [EleveController::class, 'store']);
        Route::get('/eleves/edit/{id}', [EleveController::class, 'edit']);
        Route::post('/eleves/edit/{id}', [EleveController::class, 'update']);
        Route::delete('/eleves/{id}', [EleveController::class, 'destroy']);
    });

    // Parents
    Route::middleware('permission:parents.gerer')->group(function () {
        Route::get('/parents', [ParentController::class, 'index']);
        Route::get('/parents/create', [ParentController::class, 'create']);
        Route::post('/parents', [ParentController::class, 'store']);
        Route::get('/parents/{id}/edit', [ParentController::class, 'edit']);
        Route::put('/parents/{id}', [ParentController::class, 'update']);
        Route::get('/parents/{id}', [ParentController::class, 'show'])->whereNumber('id');
        Route::delete('/parents/{id}', [ParentController::class, 'destroy']);
        Route::post('/parents/{id}/eleves', [ParentController::class, 'rattacher']);
        Route::delete('/parents/{id}/eleves/{eleveId}', [ParentController::class, 'retirer']);
    });

    // Inscriptions
    Route::middleware('permission:inscriptions.gerer')->group(function () {
        Route::get('/inscriptions', [InscriptionController::class, 'index']);
        Route::get('/inscriptions/create', [InscriptionController::class, 'create']);
        Route::post('/inscriptions', [InscriptionController::class, 'store']);
        Route::get('/inscriptions/{id}', [InscriptionController::class, 'show'])->whereNumber('id');
    });

    // Versements et reçus
    Route::middleware('permission:versements.gerer')->group(function () {
        Route::post('/inscriptions/{id}/versements', [VersementController::class, 'store']);
        Route::get('/versements/{id}/recu', [VersementController::class, 'recu']);
        Route::get('/pdf/recu/{id}', [PdfController::class, 'recu'])->whereNumber('id');
    });

    // Classes : consultation et listes
    Route::middleware('permission:classes.voir')->group(function () {
        Route::get('/classes', [ClasseController::class, 'index']);
        Route::get('/classes/{id}', [ClasseController::class, 'show'])->whereNumber('id');
        Route::get('/pdf/classe/{id}', [PdfController::class, 'classe'])->whereNumber('id');
    });

    // Classes : écriture
    Route::middleware('permission:classes.gerer')->group(function () {
        Route::get('/classes/create', [ClasseController::class, 'create']);
        Route::post('/classes', [ClasseController::class, 'store']);
        Route::get('/classes/{id}/edit', [ClasseController::class, 'edit']);
        Route::put('/classes/{id}', [ClasseController::class, 'update']);
        Route::delete('/classes/{id}', [ClasseController::class, 'destroy']);
    });

    // Séances (planning)
    Route::middleware('permission:seances.gerer')->group(function () {
        Route::get('/seances', [SeanceController::class, 'index']);
        Route::post('/seances', [SeanceController::class, 'store']);
        Route::put('/seances/{id}', [SeanceController::class, 'update']);
        Route::delete('/seances/{id}', [SeanceController::class, 'destroy']);
    });

    // Appel
    Route::middleware('permission:appel.faire')->group(function () {
        Route::get('/appels', [AppelController::class, 'index']);
        Route::get('/appels/{id}', [AppelController::class, 'show'])->whereNumber('id');
        Route::post('/appels/{id}', [AppelController::class, 'store'])->whereNumber('id');
    });

    // Évaluations
    Route::middleware('permission:evaluations.gerer')->group(function () {
        Route::get('/evaluations', [EvaluationController::class, 'index']);
        Route::post('/evaluations', [EvaluationController::class, 'store']);
        Route::put('/evaluations/{id}', [EvaluationController::class, 'update']);
        Route::delete('/evaluations/{id}', [EvaluationController::class, 'destroy']);
        Route::get('/evaluations/{id}/notes', [NoteController::class, 'depuisEvaluation'])->whereNumber('id');
    });

    // Feuilles de notes
    Route::middleware('permission:notes.saisir')->group(function () {
        Route::get('/notes', [NoteController::class, 'index']);
        Route::get('/notes/{enseignementId}', [NoteController::class, 'feuille'])->whereNumber('enseignementId');
        Route::post('/notes/{enseignementId}', [NoteController::class, 'enregistrer'])->whereNumber('enseignementId');
        Route::get('/pdf/notes/{id}', [PdfController::class, 'notes'])->whereNumber('id');
    });

    // Bulletins
    Route::middleware('permission:bulletins.voir')->group(function () {
        Route::get('/bulletins', [BulletinController::class, 'index']);
        Route::get('/bulletins/{classeId}/{periodeId}/eleve/{eleveId}', [BulletinController::class, 'eleve'])
            ->whereNumber(['classeId', 'periodeId', 'eleveId']);
        Route::get('/bulletins/{classeId}/{periodeId}/tous', [BulletinController::class, 'tous'])
            ->whereNumber(['classeId', 'periodeId']);
        Route::get('/pdf/bulletin/{classeId}/{periodeId}/{eleveId}', [PdfController::class, 'bulletin'])
            ->whereNumber(['classeId', 'periodeId', 'eleveId']);
        Route::get('/pdf/bulletins/{classeId}/{periodeId}', [PdfController::class, 'bulletins'])
            ->whereNumber(['classeId', 'periodeId']);
    });

    // Exports Excel (CSV)
    Route::middleware('permission:exports.faire')->group(function () {
        Route::get('/export/eleves', [ExportController::class, 'eleves']);
        Route::get('/export/inscriptions', [ExportController::class, 'inscriptions']);
        Route::get('/export/versements', [ExportController::class, 'versements']);
    });

    // Paramétrage de l'établissement
    Route::middleware('permission:parametrage.gerer')->group(function () {
        Route::get('/tarifs', [TarifController::class, 'index']);
        Route::post('/tarifs', [TarifController::class, 'store']);
        Route::post('/tarifs/generer', [TarifController::class, 'generer']);

        Route::get('/niveaux', [NiveauController::class, 'index']);
        Route::post('/niveaux', [NiveauController::class, 'store']);
        Route::put('/niveaux/{id}', [NiveauController::class, 'update']);
        Route::delete('/niveaux/{id}', [NiveauController::class, 'destroy']);

        Route::get('/salles', [SalleController::class, 'index']);
        Route::post('/salles', [SalleController::class, 'store']);
        Route::put('/salles/{id}', [SalleController::class, 'update']);
        Route::delete('/salles/{id}', [SalleController::class, 'destroy']);

        Route::get('/annees', [AnneeController::class, 'index']);
        Route::post('/annees', [AnneeController::class, 'store']);
        Route::put('/annees/{id}', [AnneeController::class, 'update']);
        Route::post('/annees/{id}/activer', [AnneeController::class, 'activer']);
        Route::delete('/annees/{id}', [AnneeController::class, 'destroy']);

        Route::get('/periodes', [PeriodeController::class, 'index']);
        Route::post('/periodes', [PeriodeController::class, 'store']);
        Route::put('/periodes/{id}', [PeriodeController::class, 'update']);
        Route::delete('/periodes/{id}', [PeriodeController::class, 'destroy']);

        Route::get('/domaines', [DomaineController::class, 'index']);
        Route::post('/domaines', [DomaineController::class, 'store']);
        Route::put('/domaines/{id}', [DomaineController::class, 'update']);
        Route::delete('/domaines/{id}', [DomaineController::class, 'destroy']);

        Route::get('/types-evaluation', [TypeEvaluationController::class, 'index']);
        Route::post('/types-evaluation', [TypeEvaluationController::class, 'store']);
        Route::put('/types-evaluation/{id}', [TypeEvaluationController::class, 'update']);
        Route::delete('/types-evaluation/{id}', [TypeEvaluationController::class, 'destroy']);

        Route::get('/enseignants', [EnseignantController::class, 'index']);
        Route::post('/enseignants', [EnseignantController::class, 'store']);
        Route::put('/enseignants/{id}', [EnseignantController::class, 'update']);
        Route::delete('/enseignants/{id}', [EnseignantController::class, 'destroy']);

        Route::get('/matieres', [MatiereController::class, 'index']);
        Route::post('/matieres', [MatiereController::class, 'store']);
        Route::put('/matieres/{id}', [MatiereController::class, 'update']);
        Route::delete('/matieres/{id}', [MatiereController::class, 'destroy']);

        Route::get('/coefficients', [CoefficientController::class, 'index']);
        Route::post('/coefficients', [CoefficientController::class, 'store']);
        Route::delete('/coefficients/{id}', [CoefficientController::class, 'destroy']);

        Route::get('/enseignements', [EnseignementController::class, 'index']);
        Route::post('/enseignements', [EnseignementController::class, 'store']);
        Route::delete('/enseignements/{id}', [EnseignementController::class, 'destroy']);
    });

    // Utilisateurs, rôles et permissions
    Route::middleware('permission:utilisateurs.gerer')->group(function () {
        Route::get('/utilisateurs', [UtilisateurController::class, 'index']);
        Route::post('/utilisateurs', [UtilisateurController::class, 'store']);
        Route::put('/utilisateurs/{id}', [UtilisateurController::class, 'update'])->whereNumber('id');
        Route::delete('/utilisateurs/{id}', [UtilisateurController::class, 'destroy'])->whereNumber('id');
        Route::post('/utilisateurs/{id}/basculer', [UtilisateurController::class, 'basculer'])->whereNumber('id');
        Route::get('/roles', [RoleController::class, 'index']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::put('/roles', [RoleController::class, 'update']);
        Route::put('/roles/{id}', [RoleController::class, 'modifier'])->whereNumber('id');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->whereNumber('id');
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::post('/permissions', [PermissionController::class, 'store']);
        Route::put('/permissions/{id}', [PermissionController::class, 'update'])->whereNumber('id');
        Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->whereNumber('id');
    });
});
