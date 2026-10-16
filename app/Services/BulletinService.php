<?php

namespace App\Services;

use App\Models\Absence;
use App\Models\Classe;
use App\Models\CoefficientMatiere;
use App\Models\Enseignement;
use App\Models\Evaluation;
use App\Models\Inscription;
use App\Models\Note;
use App\Models\Periode;
use App\Models\TypeEvaluation;

class BulletinService
{
    // Seuils d'appréciation (modifiables ici)
    public static function appreciation($moyenne)
    {
        if ($moyenne === null) return '—';
        if ($moyenne >= 16) return 'Excellent';
        if ($moyenne >= 14) return 'Très bien';
        if ($moyenne >= 12) return 'Bien';
        if ($moyenne >= 10) return 'Passable';
        if ($moyenne >= 8) return 'Insuffisant';
        return 'Très insuffisant';
    }

    public static function ordinal($rang)
    {
        if ($rang === null) return '—';
        return $rang == 1 ? '1er' : $rang . 'e';
    }

    /**
     * Résultats d'une classe pour une période.
     * Un absent non justifié compte 0 ; un absent justifié attend sa note de rattrapage.
     */
    public function calculer(Classe $classe, Periode $periode)
    {
        $classe->loadMissing(['niveau', 'anneeScolaire']);

        $enseignements = Enseignement::with(['matiere', 'enseignant'])
            ->where('Classe_idClasse', $classe->idClasse)
            ->get();

        $coefs = CoefficientMatiere::where('Niveau_idNiveau', $classe->Niveau_idNiveau)
            ->pluck('coefficient', 'Matiere_idMatiere');
        $ponderations = TypeEvaluation::pluck('ponderation', 'idTypeEvaluation');

        $matieres = [];
        foreach ($enseignements->sortBy(fn ($e) => $e->matiere->libelleMatiere) as $e) {
            $matieres[$e->idEnseignement] = [
                'id' => $e->idEnseignement,
                'libelle' => $e->matiere->libelleMatiere,
                'enseignant' => mb_strtoupper($e->enseignant->nomEnseignant) . ' ' . $e->enseignant->prenomEnseignant,
                'coef' => (int) ($coefs[$e->Matiere_idMatiere] ?? 1),
                'sansCoef' => !isset($coefs[$e->Matiere_idMatiere]),
            ];
        }

        $inscrits = $this->inscrits($classe->idClasse);
        $eleveIds = $inscrits->pluck('Eleve_idEleve')->all();

        $evals = Evaluation::where('Periode_idPeriode', $periode->idPeriode)
            ->whereIn('Enseignement_idEnseignement', array_keys($matieres))
            ->get();

        $notes = Note::whereIn('Evaluation_idEvaluation', $evals->pluck('idEvaluation'))
            ->whereIn('Eleve_idEleve', $eleveIds)
            ->get()
            ->groupBy('Evaluation_idEvaluation')
            ->map(fn ($g) => $g->keyBy('Eleve_idEleve')->all())
            ->all();

        // $valeurs[élève][enseignement][type d'évaluation][] = note
        $valeurs = [];
        foreach ($evals as $ev) {
            $absences = $ev->absences();

            foreach ($eleveIds as $eid) {
                $note = $notes[$ev->idEvaluation][$eid] ?? null;

                if ($note) {
                    $v = $note->valeurNote;
                } else {
                    $abs = $absences->get($eid);
                    if ($abs && !$abs->justifiee) {
                        $v = 0;
                    } else {
                        continue;
                    }
                }

                $valeurs[$eid][$ev->Enseignement_idEnseignement][$ev->TypeEvaluation_idTypeEvaluation][] = $v;
            }
        }

        $lignes = [];
        foreach ($inscrits as $i) {
            $eid = $i->Eleve_idEleve;
            $moyennes = [];
            $points = 0;
            $totalCoef = 0;

            foreach ($matieres as $id => $m) {
                if (!isset($valeurs[$eid][$id])) {
                    $moyennes[$id] = null;
                    continue;
                }
                $moy = $this->moyenneMatiere($valeurs[$eid][$id], $ponderations);
                $moyennes[$id] = $moy;
                $points += $moy * $m['coef'];
                $totalCoef += $m['coef'];
            }

            $lignes[$eid] = [
                'eleve' => $i->eleve,
                'moyennes' => $moyennes,
                'rangsMatiere' => [],
                'totalPoints' => $points,
                'totalCoef' => $totalCoef,
                'moyenne' => $totalCoef > 0 ? $points / $totalCoef : null,
                'rang' => null,
                'absJust' => 0,
                'absNon' => 0,
            ];
        }

        // Rang général
        $rangs = $this->rangs(array_map(fn ($l) => $l['moyenne'], $lignes));
        foreach ($lignes as $eid => $l) {
            $lignes[$eid]['rang'] = $rangs[$eid] ?? null;
        }

        // Rang et statistiques par matière
        $statsMatiere = [];
        foreach ($matieres as $id => $m) {
            $moyId = array_map(fn ($l) => $l['moyennes'][$id], $lignes);
            $rangsM = $this->rangs($moyId);
            foreach ($lignes as $eid => $l) {
                $lignes[$eid]['rangsMatiere'][$id] = $rangsM[$eid] ?? null;
            }

            $v = array_values(array_filter($moyId, fn ($x) => $x !== null));
            $statsMatiere[$id] = count($v)
                ? ['moy' => array_sum($v) / count($v), 'min' => min($v), 'max' => max($v)]
                : null;
        }

        // Absences de la période, en heures
        $absences = Absence::whereIn('Eleve_idEleve', $eleveIds)
            ->whereBetween('dateAbsence', [$periode->dateDebutPeriode, $periode->dateFinPeriode])
            ->get();
        foreach ($absences as $a) {
            $minutes = max(0, (strtotime($a->heureFinAbsence) - strtotime($a->heureDebutAbsence)) / 60);
            $cle = $a->justifiee ? 'absJust' : 'absNon';
            $lignes[$a->Eleve_idEleve][$cle] += $minutes / 60;
        }

        $moyennesClasse = array_values(array_filter(array_map(fn ($l) => $l['moyenne'], $lignes), fn ($x) => $x !== null));

        return [
            'matieres' => $matieres,
            'eleves' => $lignes,
            'effectif' => count($lignes),
            'moyenneClasse' => count($moyennesClasse) ? array_sum($moyennesClasse) / count($moyennesClasse) : null,
            'moyMax' => count($moyennesClasse) ? max($moyennesClasse) : null,
            'moyMin' => count($moyennesClasse) ? min($moyennesClasse) : null,
            'statsMatiere' => $statsMatiere,
        ];
    }

    /**
     * Résultats de toute l'année : moyenne annuelle = moyenne des moyennes de période.
     * Retourne aussi les résultats de chaque période (clé = identifiant de la période).
     */
    public function annuel(Classe $classe)
    {
        $classe->loadMissing(['niveau', 'anneeScolaire']);

        $periodes = Periode::where('AnneeScolaire_idAnnee', $classe->AnneeScolaire_idAnnee)
            ->orderBy('dateDebutPeriode')
            ->get();

        $parPeriode = [];
        $moyennes = [];
        $rangsPeriode = [];
        $provisoire = false;
        $aujourdhui = now()->toDateString();

        foreach ($periodes as $p) {
            $res = $this->calculer($classe, $p);
            $parPeriode[$p->idPeriode] = $res;

            foreach ($res['eleves'] as $eid => $l) {
                $moyennes[$eid][$p->idPeriode] = $l['moyenne'];
                $rangsPeriode[$p->idPeriode][$eid] = $l['rang'];
            }

            if ($p->dateFinPeriode >= $aujourdhui) {
                $provisoire = true;
            }
        }

        $annuelle = [];
        foreach ($moyennes as $eid => $parP) {
            $v = array_filter($parP, fn ($x) => $x !== null);
            $annuelle[$eid] = count($v) ? array_sum($v) / count($v) : null;
        }

        $rangs = $this->rangs($annuelle);
        $valeurs = array_values(array_filter($annuelle, fn ($x) => $x !== null));

        return [
            'periodes' => $periodes,
            'parPeriode' => $parPeriode,
            'moyennes' => $moyennes,
            'rangsPeriode' => $rangsPeriode,
            'annuelle' => $annuelle,
            'rangs' => $rangs,
            'moyenneClasse' => count($valeurs) ? array_sum($valeurs) / count($valeurs) : null,
            'moyMax' => count($valeurs) ? max($valeurs) : null,
            'moyMin' => count($valeurs) ? min($valeurs) : null,
            'provisoire' => $provisoire,
        ];
    }

    // Moyenne d'une matière : moyenne par type d'évaluation, pondérée par le type
    public function moyenneMatiere(array $parType, $ponderations)
    {
        $somme = 0;
        $poids = 0;

        foreach ($parType as $typeId => $valeurs) {
            $moy = array_sum($valeurs) / count($valeurs);
            $p = $ponderations[$typeId] ?? 0;
            $somme += $moy * $p;
            $poids += $p;
        }

        if ($poids > 0) {
            return $somme / $poids;
        }

        $toutes = array_merge(...array_values($parType));

        return array_sum($toutes) / count($toutes);
    }

    // Rangs : les ex æquo partagent le même rang. Entrée : [clé => valeur|null]
    public function rangs(array $valeurs)
    {
        $valeurs = array_filter($valeurs, fn ($v) => $v !== null);
        arsort($valeurs);

        $rangs = [];
        $position = 0;
        $rang = 0;
        $precedent = null;

        foreach ($valeurs as $cle => $v) {
            $position++;
            $arrondi = round($v, 2);
            if ($precedent === null || $arrondi !== $precedent) {
                $rang = $position;
                $precedent = $arrondi;
            }
            $rangs[$cle] = $rang;
        }

        return $rangs;
    }

    private function inscrits($classeId)
    {
        return Inscription::with('eleve')
            ->withSum('versements', 'montantVersement')
            ->where('Classe_idClasse', $classeId)
            ->get()
            ->filter(fn ($i) => $i->est_inscrit)
            ->sortBy(fn ($i) => $i->eleve->nomEleve . ' ' . $i->eleve->prenomEleve)
            ->values();
    }
}