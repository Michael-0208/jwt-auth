<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UserCoordinate;
use Illuminate\Support\Facades\Auth;

class TravelHistoryController extends Controller
{
    /**
     * Display the travel history view
     */
    public function index()
    {
        return view('travel-history');
    }

    /**
     * Calculate and display total distance traveled per day
     */
    public function totalTravelingDistance()
    {
        $user = Auth::user();
        
        // Get all coordinates for the user, ordered by date
        $coordinates = $user->coordinates()
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(function($item) {
                return $item->created_at->format('Y-m-d');
            });

        $dailyDistances = [];
        
        foreach ($coordinates as $date => $dayCoordinates) {
            $totalDistance = 0;
            // Calculate distance between consecutive points
            for ($i = 0; $i < count($dayCoordinates) - 1; $i++) {
                $point1 = $dayCoordinates[$i];
                $point2 = $dayCoordinates[$i + 1];
                
                $distance = $this->calculateDistance(
                    $point1->latitude,
                    $point1->longitude,
                    $point2->latitude,
                    $point2->longitude
                );
                
                $totalDistance += $distance;
            }
            
            $dailyDistances[$date] = [
                'date' => $date,
                'total_distance' => round($totalDistance, 2), // Distance in kilometers
                'points_count' => count($dayCoordinates)
            ];
        }
        
        // Sort by date in descending order
        krsort($dailyDistances);

        return view('travel-distance', ['dailyDistances' => $dailyDistances]);
    }

    /**
     * Calculate distance between two points using Haversine formula
     * @param float $lat1 Latitude of first point
     * @param float $lon1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lon2 Longitude of second point
     * @return float Distance in kilometers
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta/2) * sin($latDelta/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta/2) * sin($lonDelta/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
} 