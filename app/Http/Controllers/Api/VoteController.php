<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VoteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class VoteController extends Controller
{
    protected $voteService;

    public function __construct(VoteService $voteService)
    {
        $this->voteService = $voteService;
    }

    /**
     * Soumettre un vote
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'position_id'  => 'required|exists:positions,id',
            'candidate_id' => 'nullable|exists:candidates,id', // nullable pour le vote blanc
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $vote = $this->voteService->castVote(
                $request->position_id,
                $request->candidate_id
            );

            return response()->json([
                'message' => 'Votre vote a été enregistré avec succès.',
                'data' => $vote
            ], 201);
            
        } catch (\Exception $e) {
            // On capture les erreurs métier (déjà voté, poste fermé, etc.)
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * Voir les résultats globaux des votes
     */
    public function results(): JsonResponse
    {
        try {
            $results = $this->voteService->getResults();
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Récupérer les IDs des postes pour lesquels l'utilisateur a déjà voté
     * Utile pour griser les boutons de vote côté Frontend (Svelte)
     */
    public function myVotes(): JsonResponse
    {
        try {
            $votedPositionIds = $this->voteService->getUserVotes();
            return response()->json([
                'voted_positions' => $votedPositionIds
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }
}