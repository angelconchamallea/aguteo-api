<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AgeStageResource;
use App\Models\AgeStage;
use Illuminate\Http\JsonResponse;

class AgeStageController extends Controller
{
    public function index(): JsonResponse
    {
        $stages = AgeStage::orderBy('sort_order')->get();

        return response()->json(['data' => AgeStageResource::collection($stages)]);
    }
}
