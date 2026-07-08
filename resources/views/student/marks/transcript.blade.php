@extends('layouts.student')

@section('title', 'Transcript / DMC')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="fas fa-file-alt"></i> Transcript / DMC</h3>
        <div>
            <a href="{{ route('student.marks.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <a href="{{ route('student.transcript.print', $examTimetable->id) }}" class="btn btn-primary" target="_blank">
                <i class="fas fa-print"></i> Print
            </a>
            <a href="{{ route('student.transcript.download-pdf', $examTimetable->id) }}" class="btn btn-danger">
                <i class="fas fa-download"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Student Info -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user-graduate"></i> Student Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Name:</strong>
                    <p>{{ $student->user->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Admission No:</strong>
                    <p>{{ $student->admission_no ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Roll No:</strong>
                    <p>{{ $student->roll_no ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Class / Section:</strong>
                    <p>{{ $mark->examTimetable->schoolClass->class_name ?? 'N/A' }} / {{ $mark->examTimetable->section->section_name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Info -->
    <div class="card shadow mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Exam Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Exam:</strong>
                    <p>{{ $mark->examTimetable->examType->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <strong>Academic Year:</strong>
                    <p>{{ $mark->examTimetable->academicYear->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <strong>Exam Date:</strong>
                    <p>{{ isset($mark->examTimetable->exam_date) ? \Carbon\Carbon::parse($mark->examTimetable->exam_date)->format('d-m-Y') : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mark Details -->
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Mark Details</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th width="200">Subject</th>
                        <td>{{ $mark->subject->subject_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Total Marks</th>
                        <td>{{ $mark->total_marks ?? 0 }}</td>
                    </tr>
                    <tr>
                        <th>Obtained Marks</th>
                        <td><strong class="text-primary">{{ $mark->obtained_marks ?? 0 }}</strong></td>
                    </tr>
                    <tr>
                        <th>Percentage</th>
                        <td><span class="badge bg-info">{{ $percentage ?? 0 }}%</span></td>
                    </tr>
                    <tr>
                        <th>Grade</th>
                        <td>
                            <span class="badge bg-{{ $grade['color'] ?? 'secondary' }}">
                                {{ $grade['letter'] ?? 'N/A' }}
                            </span>
                            <small class="text-muted ms-2">{{ $grade['description'] ?? '' }}</small>
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($status == 'Pass')
                                <span class="badge bg-success">Pass</span>
                            @else
                                <span class="badge bg-danger">Fail</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Remarks</th>
                        <td>{{ $mark->remarks ?? 'No remarks' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
