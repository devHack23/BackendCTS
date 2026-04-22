<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Services\CandidatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CandidatController extends Controller
{
    protected $candidatService;

    public function __construct(CandidatService $candidatService)
    {
        $this->candidatService = $candidatService;
    }

    /**
     * Liste tous les candidats
     */
    public function index(): JsonResponse
    {
        try {
            $candidates = $this->candidatService->getAll();
            return response()->json([
                'success' => true,
                'data' => $candidates
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    /**
     * Créer une nouvelle candidature (Admin seulement)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|exists:users,id',
            'position_id' => 'required|exists:positions,id',
            'bio'         => 'nullable|string',
            'photo_path'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $candidate = $this->candidatService->create($request->all());
            return response()->json([
                'message' => 'Candidat enregistré avec succès',
                'data' => $candidate
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    /**
     * Récupérer le profil candidat de l'utilisateur connecté
     */
    public function profile(): JsonResponse
    {
        try {
            $profile = $this->candidatService->getProfile();
            return response()->json(['data' => $profile]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    /**
     * Mettre à jour une candidature
     */
    public function update(Request $request, Candidate $candidate): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bio'         => 'nullable|string',
            'photo_path'  => 'nullable|string',
            'position_id' => 'sometimes|exists:positions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->candidatService->update($candidate, $request->all());
            return response()->json(['message' => 'Profil mis à jour avec succès']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    /**
     * Supprimer une candidature (Admin seulement)
     */
    public function destroy(Candidate $candidate): JsonResponse
    {
        try {
            $this->candidatService->delete($candidate);
            return response()->json(['message' => 'Candidat supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}