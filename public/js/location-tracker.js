let userLatitude = null;
let userLongitude = null;
let locationUpdateInterval = null;

function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                userLatitude = position.coords.latitude;
                userLongitude = position.coords.longitude;
                sendLocationUpdate();
            },
            (error) => {
                console.error('Error getting location:', error);
            }
        );
    } else {
        console.error('Geolocation is not supported by this browser.');
    }
}

function sendLocationUpdate() {
    const token = localStorage.getItem('token');
    if (!token || !userLatitude || !userLongitude) return;

    fetch('/api/location-update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            latitude: userLatitude,
            longitude: userLongitude
        })
    })
    .then(response => response.json())
    .then(data => console.log('Location update sent:', data))
    .catch(error => console.error('Error sending location update:', error));
}

function startLocationUpdates() {
    // Get initial location
    getCurrentLocation();
    
    // Set up periodic updates every 5 minutes
    locationUpdateInterval = setInterval(() => {
        getCurrentLocation();
    }, 5 * 60 * 1000); // 5 minutes
}

// Start location updates when the page loads
document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('token');
    if (token) {
        startLocationUpdates();
    }
}); 