<?php

namespace App\Services;

use App\Models\Position;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class PositionService 
{
    /**
     * Créer un nouveau poste (Réservé à l'admin)
     */
    public function create(array $data): Position
    {
        $user = auth('api')->user();

        // Vérification des droits admin basée sur l'énumération de la table users
        if (!$user || $user->role !== 'admin') {
            throw new \Exception('Seul un administrateur peut créer un poste.', 403);
        }

        return Position::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'is_active'   => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Récupérer tous les postes (avec leurs candidats)
     */
    public function getAll(): Collection
    {
        // On charge les candidats et les informations utilisateur liées pour l'affichage
        return Position::with(['candidates.user'])->get();
    }

    /**
     * Mettre à jour un poste (Titre, description ou activation)
     */
    public function update(Position $position, array $data): bool
    {
        $user = auth('api')->user();

        if (!$user || $user->role !== 'admin') {
            throw new \Exception('Action non autorisée.', 403);
        }

        return $position->update($data);
    }

    /**
     * Basculer l'état d'un poste (Activer/Désactiver les votes)
     */
    public function toggleStatus(Position $position): bool
    {
        $user = auth('api')->user();

        if (!$user || $user->role !== 'admin') {
            throw new \Exception('Action non autorisée.', 403);
        }

        return $position->update([
            'is_active' => !$position->is_active
        ]);
    }

    /**
     * Supprimer un poste
     * Note: La suppression entraînera celle des candidats et votes associés (onDelete cascade)
     */
    public function delete(Position $position): bool
    {
        $user = auth('api')->user();

        if (!$user || $user->role !== 'admin') {
            throw new \Exception('Seul un administrateur peut supprimer un poste.', 403);
        }

        return $position->delete();
    }

    /**
     * Récupérer les postes qui sont actuellement ouverts aux votes
     */
    public function getActivePositions(): Collection
    {
        return Position::where('is_active', true)->get();
    }
}