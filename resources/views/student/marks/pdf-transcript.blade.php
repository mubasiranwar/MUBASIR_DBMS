<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transcript PDF</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        h2 { color: #007bff; text-align: center; margin-bottom: 4px; }
        h4 { text-align: center; color: #555; margin-bottom: 4px; }
        p.subtitle { text-align: center; color: #888; font-size: 11px; margin-bottom: 20px; }
        .section-header { background: #007bff; color: white; padding: 8px 12px; font-weight: bold; }
        .card { border: 1px solid #ddd; margin-bottom: 16px; }
        .card-body { padding: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 7px 10px; }
        th { background: #f8f9fa; font-weight: 600; width: 160px; }
        .footer { text-align: center; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; color: #888; font-size: 10px; }
    </style>
</head>
<body>
    <h2>School Management System</h2>
    <h4>Transcript / Detailed Marks Certificate (DMC)</h4>
    <p class="subtitle">Generated on: {{ \Carbon\Carbon::now()->format('l, d-m-Y h:i A') }}</p>

    <div class="card">
        <div class="section-header">Student Information</div>
        <div class="card-body">
            <table>
                <tr><th>Student Name</th><td>{{ $student->user->name ?? 'N/A' }}</td><th>Admission No</th><td>{{ $student->admission_no ?? 'N/A' }}</td></tr>
                <tr><th>Roll No</th><td>{{ $student->roll_no ?? 'N/A' }}</td><th>Class / Section</th><td>{{ $mark->examTimetable->schoolClass->class_name ?? 'N/A' }} / {{ $mark->examTimetable->section->section_name ?? 'N/A' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="section-header">Exam Information</div>
        <div class="card-body">
            <table>
                <tr><th>Exam</th><td>{{ $mark->examTimetable->examType->name ?? 'N/A' }}</td></tr>
                <tr><th>Academic Year</th><td>{{ $mark->examTimetable->academicYear->name ?? 'N/A' }}</td></tr>
                <tr><th>Exam Date</th><td>{{ isset($mark->examTimetable->exam_date) ? \Carbon\Carbon::parse($mark->examTimetable->exam_date)->format('d-m-Y') : 'N/A' }}</td></tr>
                <tr><th>Subject</th><td>{{ $mark->subject->subject_name ?? 'N/A' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="section-header">Mark Details</div>
        <div class="card-body">
            <table>
                <tr><th>Total Marks</th><td>{{ $mark->total_marks ?? 0 }}</td></tr>
                <tr><th>Obtained Marks</th><td><strong>{{ $mark->obtained_marks ?? 0 }}</strong></td></tr>
                <tr><th>Percentage</th><td>{{ $percentage ?? 0 }}%</td></tr>
                <tr><th>Grade</th><td>{{ $grade['letter'] ?? 'N/A' }} - {{ $grade['description'] ?? '' }}</td></tr>
                <tr><th>Status</th><td>{{ $status ?? 'N/A' }}</td></tr>
                <tr><th>Remarks</th><td>{{ $mark->remarks ?? 'No remarks' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer generated document. No signature required.</p>
        <p>&copy; {{ \Carbon\Carbon::now()->format('Y') }} School Management System. All rights reserved.</p>
    </div>
</body>
</html>
