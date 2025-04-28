

# JWT Authentication with Location Tracking

A Laravel application with JWT authentication and real-time location tracking capabilities.

## Features

- JWT Authentication
- User Registration and Login
- Password Reset Functionality
- Real-time Location Tracking
  - Browser-based geolocation updates
  - IP-based fallback location updates
  - Continuous location tracking with Supervisor
- Location History Storage
- User Dashboard

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy `.env.example` to `.env` and configure your environment variables
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Run migrations:
   ```bash
   php artisan migrate
   ```

## Location Tracking Setup

### Browser-based Tracking
The application automatically tracks user locations using the browser's geolocation API. This feature:
- Updates every 5 minutes while users are active
- Requires user permission
- Provides accurate GPS-based location data
- Works across all pages of the application

### Server-side Location Updates
For backup and fallback purposes, the application includes a server-side location update system:

1. Install Supervisor:
   ```bash
   # Ubuntu/Debian
   sudo apt-get update
   sudo apt-get install supervisor
   ```

2. Create Supervisor configuration:
   ```bash
   sudo nano /etc/supervisor/conf.d/location-update.conf
   ```

3. Add this configuration:
   ```ini
   [program:location-update]
   command=php /path/to/your/project/artisan user:update-locations
   directory=/path/to/your/project
   autostart=true
   autorestart=true
   user=www-data
   numprocs=1
   redirect_stderr=true
   stdout_logfile=/path/to/your/project/storage/logs/location-update.log
   stderr_logfile=/path/to/your/project/storage/logs/location-update-error.log
   ```

4. Start the location update process:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start location-update:*
   ```

### Location Data Storage
Location data is stored in two ways:
1. Real-time updates in the `user_locations` table
2. Latest location in the `users` table

## API Endpoints

- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `POST /api/forgot-password` - Request password reset
- `POST /api/reset-password/{id}` - Reset password
- `POST /api/location-update` - Update user location (requires authentication)

## Security Features

- JWT token-based authentication
- Password hashing
- CSRF protection
- Rate limiting on sensitive endpoints
- Secure password reset flow

## Monitoring

You can monitor the location update process using:

```bash
# Check Supervisor status
sudo supervisorctl status

# View logs
tail -f /path/to/your/project/storage/logs/location-update.log
tail -f /path/to/your/project/storage/logs/location-update-error.log
```

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License.
