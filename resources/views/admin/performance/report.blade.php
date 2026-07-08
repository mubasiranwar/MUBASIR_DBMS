@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Performance Report</h2>
        <div>
            <a href="{{ route('admin.performance.generate-report') }}" class="btn btn-danger">
                <i class="bi bi-file-pdf"></i> Generate PDF
            </a>
            <a href="{{ route('admin.performance.export') }}" class="btn btn-success">
                <i class="bi bi-file-excel"></i> Export CSV
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Performance Summary</h5>
            <hr>
            <div class="row">
                <div class="col-md-3">
                    <strong>Average Score:</strong> {{ $averageScore ?? 'N/A' }}%
                </div>
                <div class="col-md-3">
                    <strong>Pass Rate:</strong> {{ $passRate ?? 'N/A' }}%
                </div>
                <div class="col-md-3">
                    <strong>Highest Score:</strong> {{ $highestScore ?? 'N/A' }}%
                </div>
                <div class="col-md-3">
                    <strong>Lowest Score:</strong> {{ $lowestScore ?? 'N/A' }}%
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5>Student Performance List</h5>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Average Score</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students ?? [] as $student)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->schoolClass->class_name ?? 'N/A' }}</td>
                        <td>
                            @php
                                $avg = $student->marks->avg(function($mark) {
                                    return ($mark->total_marks > 0) ? ($mark->obtained_marks / $mark->total_marks * 100) : 0;
                                });
                            @endphp
                            {{ round($avg, 2) }}%
                        </td>
                        <td>
                            @if($avg >= 40)
                                <span class="badge bg-success">Pass</span>
                            @else
                                <span class="badge bg-danger">Fail</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection