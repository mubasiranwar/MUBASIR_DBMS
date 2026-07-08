<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Performance Card PDF</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        h2 { color: #007bff; text-align: center; margin-bottom: 4px; }
        h4 { text-align: center; color: #555; margin-bottom: 4px; }
        p.subtitle { text-align: center; color: #888; font-size: 11px; margin-bottom: 20px; }
        .section-header { background: #28a745; color: white; padding: 8px 12px; font-weight: bold; }
        .card { border: 1px solid #ddd; margin-bottom: 16px; }
        .card-body { padding: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 7px 10px; text-align: left; }
        th { background: #f8f9fa; font-weight: 600; }
        .summary { background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 16px; }
        .footer { text-align: center; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; color: #888; font-size: 10px; }
    </style>
</head>
<body>
    <h2>School Management System</h2>
    <h4>Student Performance Card</h4>
    <p class="subtitle">Generated on: {{ \Carbon\Carbon::now()->format('l, d-m-Y h:i A') }}</p>

    <div class="card">
        <div class="section-header">Student Information</div>
        <div class="card-body">
            <table>
                <tr>
                    <th>Name</th><td>{{ $student->user->name ?? 'N/A' }}</td>
                    <th>Admission No</th><td>{{ $student->admission_no ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Roll No</th><td>{{ $student->roll_no ?? 'N/A' }}</td>
                    <th>Gender</th><td>{{ $student->gender ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="section-header">Overall Performance Summary</div>
        <div class="card-body">
            <table>
                <tr>
                    <th>Total Marks</th><td>{{ $totalMarks ?? 0 }}</td>
                    <th>Obtained Marks</th><td>{{ $obtainedMarks ?? 0 }}</td>
                </tr>
                <tr>
                    <th>Overall Percentage</th><td>{{ $overallPercentage ?? 0 }}%</td>
                    <th>Overall Grade</th><td>{{ $overallGrade['letter'] ?? 'N/A' }} - {{ $overallGrade['description'] ?? '' }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if(!empty($subjectMarks))
    <div class="card">
        <div class="section-header">Subject-wise Performance</div>
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>Total Marks</th>
                        <th>Obtained Marks</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjectMarks as $subject)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $subject['subject_name'] }}</td>
                        <td>{{ $subject['total'] }}</td>
                        <td>{{ $subject['obtained'] }}</td>
                        <td>{{ $subject['percentage'] }}%</td>
                        <td>{{ $subject['grade']['letter'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>This is a computer generated document. No signature required.</p>
        <p>&copy; {{ \Carbon\Carbon::now()->format('Y') }} School Management System. All rights reserved.</p>
    </div>
</body>
</html>
