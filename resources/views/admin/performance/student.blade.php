@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Student Performance: {{ $student->name ?? 'N/A' }}</h2>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Overall Average: <strong>{{ round($averageScore ?? 0, 2) }}%</strong></h5>
            <hr>
            
            @if(isset($performanceData) && count($performanceData) > 0)
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Exam/Subject</th>
                            <th>Marks Obtained</th>
                            <th>Total Marks</th>
                            <th>Percentage</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($performanceData as $data)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $data['exam']->subject->name ?? 'N/A' }}</td>
                            <td>{{ $data['marks_obtained'] }}</td>
                            <td>{{ $data['total_marks'] }}</td>
                            <td>{{ $data['percentage'] }}%</td>
                            <td>
                                <span class="badge {{ $data['status'] == 'Pass' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $data['status'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-info">No marks available for this student.</div>
            @endif
        </div>
    </div>
</div>
@endsection