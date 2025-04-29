@extends('layouts.app')

@section('title', 'Travel History')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form id="dateRangeForm" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" required>
                        </div>
                        <div class="col-md-4">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" name="end_date" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Travel History</h3>
                </div>
                <div class="card-body">
                    <div id="map" style="height: 500px;"></div>
                    <div class="table-responsive mt-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<style>
    #map { 
        height: 500px; 
        border-radius: 4px;
    }
</style>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    let map;
    let markers = [];
    let polyline;

    // Initialize map
    function initMap() {
        map = L.map('map').setView([0, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
    }

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

        // Add event listeners for date range validation
        startDateInput.addEventListener('change', function() {
            const startDate = new Date(this.value);
            const maxEndDate = new Date(startDate);
            maxEndDate.setDate(startDate.getDate() + 30);
            
            endDateInput.min = this.value;
            endDateInput.max = maxEndDate.toISOString().split('T')[0];
            
            if (new Date(endDateInput.value) < startDate) {
                endDateInput.value = this.value;
            }
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
        });
    }

    // Load travel history data
    function loadTravelHistory(startDate, endDate) {
        fetch(`/api/travel-history?start_date=${startDate}&end_date=${endDate}`, {
            headers: {
                'Authorization': `Bearer ${getCookie('jwt_token')}`
            }
        })
        .then(response => response.json())
        .then(data => {
            updateTable(data);
            updateMap(data);
        })
        .catch(error => console.error('Error:', error));
    }

    // Update the table with travel history data
    function updateTable(data) {
        const tbody = document.getElementById('historyTableBody');
        tbody.innerHTML = '';

        data.forEach(record => {
            const row = document.createElement('tr');
            const date = new Date(record.created_at);
            
            row.innerHTML = `
                <td>${date.toLocaleDateString()}</td>
                <td>${date.toLocaleTimeString()}</td>
                <td>${record.latitude}</td>
                <td>${record.longitude}</td>
                <td>${record.location || 'N/A'}</td>
            `;
            
            tbody.appendChild(row);
        });
    }

    // Update the map with markers and polyline
    function updateMap(data) {
        // Clear existing markers and polyline
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];
        if (polyline) {
            map.removeLayer(polyline);
        }

        if (data.length > 0) {
            const coordinates = data.map(record => [record.latitude, record.longitude]);
            
            // Add markers
            data.forEach((record, index) => {
                const marker = L.marker([record.latitude, record.longitude])
                    .bindPopup(`Location ${index + 1}<br>${new Date(record.created_at).toLocaleString()}`);
                markers.push(marker);
                marker.addTo(map);
            });

            // Add polyline
            polyline = L.polyline(coordinates, {color: 'red'}).addTo(map);
            
            // Fit map to show all markers
            map.fitBounds(polyline.getBounds());
        }
    }

    // Helper function to get cookie value
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    // Initialize when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        setDateLimits();
        
        // Load initial data
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        loadTravelHistory(startDate, endDate);

        // Handle form submission
        document.getElementById('dateRangeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            loadTravelHistory(startDate, endDate);
        });
    });
</script>
@endsection 