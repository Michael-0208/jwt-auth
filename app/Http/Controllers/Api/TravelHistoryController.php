<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserCoordinate;
use Illuminate\Http\Request;

class TravelHistoryController extends Controller
{
    /**
     * Get user's travel history data
     */
    public function getTravelHistory(Request $request)
    {
        $coordinates = UserCoordinate::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($coordinate) {
                return [
                    'latitude' => $coordinate->latitude,
                    'longitude' => $coordinate->longitude,
                    'timestamp' => $coordinate->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'coordinates' => $coordinates,
            'total_points' => $coordinates->count()
        ]);
    }
} 