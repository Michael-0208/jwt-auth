@extends('layouts.app')

@section('title', 'Travel History')

@section('head')
<link href="{{ asset('css/travel-history.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <h2 class="mb-4">Your Travel History</h2>
    <div class="travel-container">
        <div class="map-container">
            <div id="map"></div>
        </div>
        <div class="coordinates-list">
            <div class="loading">Loading travel history...</div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key="></script>
<script>
let map;
let pathCoordinates = [];
let markers = [];
let polyline;

function initMap() {
    // Initialize map
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: { lat: 0, lng: 0 },
        mapTypeId: 'terrain'
    });

    // Get travel history
    fetch('/api/travel-history', {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.coordinates.length > 0) {
            // Update coordinates list
            const coordinatesList = document.querySelector('.coordinates-list');
            coordinatesList.innerHTML = data.coordinates.map(coord => `
                <div class="coordinate-item">
                    <div class="timestamp">${coord.timestamp}</div>
                    <div class="coordinates">
                        Lat: ${coord.latitude.toFixed(6)}, 
                        Lng: ${coord.longitude.toFixed(6)}
                    </div>
                </div>
            `).join('');

            // Process coordinates for map
            pathCoordinates = data.coordinates.map(coord => ({
                lat: parseFloat(coord.latitude),
                lng: parseFloat(coord.longitude)
            }));

            // Center map on first coordinate
            map.setCenter(pathCoordinates[0]);
            
            // Create polyline
            polyline = new google.maps.Polyline({
                path: pathCoordinates,
                geodesic: true,
                strokeColor: '#FF0000',
                strokeOpacity: 1.0,
                strokeWeight: 2
            });
            polyline.setMap(map);

            // Add markers
            pathCoordinates.forEach((coord, index) => {
                const marker = new google.maps.Marker({
                    position: coord,
                    map: map,
                    title: `Point ${index + 1}`,
                    label: (index + 1).toString()
                });
                markers.push(marker);
            });

            // Fit map to show all points
            const bounds = new google.maps.LatLngBounds();
            pathCoordinates.forEach(coord => bounds.extend(coord));
            map.fitBounds(bounds);
        } else {
            document.querySelector('.coordinates-list').innerHTML = 
                '<div class="alert alert-info">No travel history available</div>';
        }
    })
    .catch(error => {
        console.error('Error fetching travel history:', error);
        document.querySelector('.coordinates-list').innerHTML = 
            '<div class="alert alert-danger">Error loading travel history</div>';
    });
}

// Initialize map when the page loads
document.addEventListener('DOMContentLoaded', initMap);
</script>
@endsection 