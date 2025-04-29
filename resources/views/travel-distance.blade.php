@extends('layouts.app')

@section('title', 'Daily Travel Distance')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daily Travel Distance</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Total Distance (km)</th>
                                    <th>Number of Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailyDistances as $date => $data)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</td>
                                        <td>{{ number_format($data['total_distance'], 2) }}</td>
                                        <td>{{ $data['points_count'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No travel data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 