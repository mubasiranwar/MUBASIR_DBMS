<!DOCTYPE html>
<html>
<head>
    <title>Transcript / DMC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
        }
        body { padding: 20px; font-family: Arial, sans-serif; background: #f8f9fa; }
        .card { border: 1px solid #ddd; margin-bottom: 20px; border-radius: 8px; overflow: hidden; }
        .card-header { padding: 12px 20px; font-weight: bold; }
        .card-body { padding: 20px; background: white; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 10px; border: 1px solid #ddd; }
        .table th { background: #f8f9fa; font-weight: 600; width: 200px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4 border-bottom pb-3">
            <h2 class="text-primary">School Management System</h2>
            <h4>Transcript / Detailed Marks Certificate (DMC)</h4>
            <p class="text-muted">Generated on: {{ \Carbon\Carbon::now()->format('l, d-m-Y h:i A') }}</p>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Student Information</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr><th>Student Name</th><td>{{ $student->user->name ?? 'N/A' }}</td><th>Admission No</th><td>{{ $student->admission_no ?? 'N/A' }}</td></tr>
                    <tr><th>Roll No</th><td>{{ $student->roll_no ?? 'N/A' }}</td><th>Class / Section</th><td>{{ $mark->examTimetable->schoolClass->class_name ?? 'N/A' }} / {{ $mark->examTimetable->section->section_name ?? 'N/A' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-info text-white">Exam Information</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr><th>Exam</th><td>{{ $mark->examTimetable->examType->name ?? 'N/A' }}</td></tr>
                    <tr><th>Academic Year</th><td>{{ $mark->examTimetable->academicYear->name ?? 'N/A' }}</td></tr>
                    <tr><th>Exam Date</th><td>{{ isset($mark->examTimetable->exam_date) ? \Carbon\Carbon::parse($mark->examTimetable->exam_date)->format('d-m-Y') : 'N/A' }}</td></tr>
                    <tr><th>Subject</th><td>{{ $mark->subject->subject_name ?? 'N/A' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-success text-white">Mark Details</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr><th>Total Marks</th><td>{{ $mark->total_marks ?? 0 }}</td></tr>
                    <tr><th>Obtained Marks</th><td><strong>{{ $mark->obtained_marks ?? 0 }}</strong></td></tr>
                    <tr><th>Percentage</th><td>{{ $percentage ?? 0 }}%</td></tr>
                    <tr><th>Grade</th><td>{{ $grade['letter'] ?? 'N/A' }} - {{ $grade['description'] ?? '' }}</td></tr>
                    <tr><th>Status</th><td>{{ $status ?? 'N/A' }}</td></tr>
                    <tr><th>Remarks</th><td>{{ $mark->remarks ?? 'No remarks' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="text-center mt-4 border-top pt-3">
            <p class="text-muted"><small>This is a computer generated document. No signature required.</small></p>
        </div>

        <div class="text-center mt-3 no-print">
            <button onclick="window.print()" class="btn btn-primary">Print</button>
            <a href="{{ route('student.transcript', $examTimetable->id) }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</body>
</html>
