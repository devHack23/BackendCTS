<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    protected $positionService;

    public function __construct(PositionService $positionService)
    {
        $this->positionService = $positionService;
    }

    /**
     * Liste tous les postes (Accessible à tous les authentifiés)
     */
    public function index(): JsonResponse
    {
        $positions = $this->positionService->getAll();
        return response()->json([
            'success' => true,
            'data' => $positions
        ]);
    }

    /**
     * Créer un nouveau poste (Admin uniquement)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|unique:positions,title|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $position = $this->positionService->create($request->all());
            return response()->json([
                'message' => 'Poste créé avec succès',
                'data' => $position
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    /**
     * Mettre à jour un poste (Admin uniquement)
     */
    public function update(Request $request, Position $position): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'sometimes|string|unique:positions,title,' . $position->id,
            'description' => 'nullable|string',
            'is_active'   => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->positionService->update($position, $request->all());
            return response()->json(['message' => 'Poste mis à jour avec succès']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    /**
     * Activer ou désactiver un poste (Admin uniquement)
     */
    public function toggle(Position $position): JsonResponse
    {
        try {
            $this->positionService->toggleStatus($position);
            $status = $position->is_active ? 'activé' : 'désactivé';
            return response()->json(['message' => "Le poste est désormais $status"]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    /**
     * Supprimer un poste (Admin uniquement)
     */
    public function destroy(Position $position): JsonResponse
    {
        try {
            $this->positionService->delete($position);
            return response()->json(['message' => 'Poste supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    /**
     * Liste des postes actifs pour les électeurs
     */
    public function activePositions(): JsonResponse
    {
        $positions = $this->positionService->getActivePositions();
        return response()->json(['data' => $positions]);
    }
}