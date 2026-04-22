<?php

namespace App\Services;

use App\Models\Vote;
use App\Models\Candidate;
use App\Models\Position;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class VoteService 
{
    /**
     * Enregistrer un vote pour un candidat sur un poste spécifique.
     */
    public function castVote(int $positionId, ?int $candidateId): Vote
    {
        $user = auth('api')->user();

        if (!$user) {
            throw new \Exception('Vous devez être connecté pour voter.', 401);
        }

        // 1. Vérifier si le poste est actif
        $position = Position::findOrFail($positionId);
        if (!$position->is_active) {
            throw new \Exception('Les votes pour ce poste sont clôturés.', 400);
        }

        // 2. Générer le hash_session basé sur l'ID utilisateur (pour l'unicité)
        // On utilise l'ID utilisateur pour remplir le champ 'hash_session' de la migration
        $hashSession = hash('sha256', $user->id . config('app.key'));

        // 3. Vérifier si l'utilisateur a déjà voté pour ce poste
        $alreadyVoted = Vote::where('position_id', $positionId)
                            ->where('hash_session', $hashSession)
                            ->exists();

        if ($alreadyVoted) {
            throw new \Exception('Vous avez déjà voté pour ce poste.', 403);
        }

        // 4. Créer le vote
        return Vote::create([
            'position_id'  => $positionId,
            'candidate_id' => $candidateId, // Peut être null si vote blanc
            'hash_session' => $hashSession,
        ]);
    }

    /**
     * Obtenir les résultats actuels pour tous les postes.
     */
    public function getResults(): Collection
    {
        return Position::with(['candidates.user'])
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'title' => $position->title,
                    'total_votes' => Vote::where('position_id', $position->id)->count(),
                    'candidates' => $position->candidates->map(function ($candidate) {
                        return [
                            'name' => $candidate->user->first_name . ' ' . $candidate->user->last_name,
                            'votes_count' => Vote::where('candidate_id', $candidate->id)->count(),
                        ];
                    })
                ];
            });
    }

    /**
     * Vérifier quels postes l'utilisateur a déjà voté.
     */
    public function getUserVotes(): Collection
    {
        $user = auth('api')->user();
        if (!$user) return collect();

        $hashSession = hash('sha256', $user->id . config('app.key'));

        return Vote::where('hash_session', $hashSession)
                   ->pluck('position_id');
    }
}