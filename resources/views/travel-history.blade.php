@extends('layouts.app')

@section('title', 'Travel History')

@section('content')
<div class="container">
    <h2 class="mb-4">Your Travel History</h2>
    
    <!-- Date Range Selector -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="startDate" name="start_date">
                </div>
                <div class="col-md-4">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="endDate" name="end_date">
                </div>
            </div>
        </div>
    </div>

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

// Set date range limits
function setDateLimits() {
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const today = new Date();
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(today.getDate() - 30);

    // Set max date to today
    startDateInput.max = today.toISOString().split('T')[0];
    endDateInput.max = today.toISOString().split('T')[0];

    // Set min date to 30 days ago
    startDateInput.min = thirtyDaysAgo.toISOString().split('T')[0];
    endDateInput.min = thirtyDaysAgo.toISOString().split('T')[0];

    // Set initial values to last 7 days
    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(today.getDate() - 7);
    startDateInput.value = sevenDaysAgo.toISOString().split('T')[0];
    endDateInput.value = today.toISOString().split('T')[0];

    // Add event listeners for date range validation and AJAX loading
    startDateInput.addEventListener('change', function() {
        const startDate = new Date(this.value);
        const maxEndDate = new Date(startDate);
        maxEndDate.setDate(startDate.getDate() + 30);
        
        endDateInput.min = this.value;
        endDateInput.max = maxEndDate.toISOString().split('T')[0];
        
        if (new Date(endDateInput.value) < startDate) {
            endDateInput.value = this.value;
        }
        
        loadTravelHistory();
    });

    endDateInput.addEventListener('change', function() {
        const endDate = new Date(this.value);
        const minStartDate = new Date(endDate);
        minStartDate.setDate(endDate.getDate() - 30);
        
        startDateInput.max = this.value;
        startDateInput.min = minStartDate.toISOString().split('T')[0];
        
        if (new Date(startDateInput.value) > endDate) {
            startDateInput.value = this.value;
        }
        
        loadTravelHistory();
    });
}

function initMap() {
    // Initialize map
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: { lat: 0, lng: 0 },
        mapTypeId: 'terrain'
    });

    loadTravelHistory();
}

// Load travel history with date range
function loadTravelHistory() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    fetch(`/api/travel-history?start_date=${startDate}&end_date=${endDate}`, {
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
                        Lat: ${coord.latitude}, 
                        Lng: ${coord.longitude}
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
            
            // Clear existing markers and polyline
            markers.forEach(marker => marker.setMap(null));
            markers = [];
            if (polyline) {
                polyline.setMap(null);
            }
            
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
                '<div class="alert alert-info">No travel history available for selected date range</div>';
        }
    })
    .catch(error => {
        console.error('Error fetching travel history:', error);
        document.querySelector('.coordinates-list').innerHTML = 
            '<div class="alert alert-danger">Error loading travel history</div>';
    });
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    setDateLimits();
    initMap();
});
</script>
@endsection 