<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Performance Report PDF</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        h2 { color: #007bff; text-align: center; margin-bottom: 4px; }
        h4 { text-align: center; color: #555; margin-bottom: 4px; }
        p.subtitle { text-align: center; color: #888; font-size: 11px; margin-bottom: 20px; }
        .section-header { background: #007bff; color: white; padding: 8px 12px; font-weight: bold; }
        .card { border: 1px solid #ddd; margin-bottom: 16px; }
        .card-body { padding: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 7px 10px; text-align: left; }
        th { background: #f8f9fa; font-weight: 600; }
        .badge-pass { background: #28a745; color: white; padding: 2px 6px; border-radius: 3px; }
        .badge-fail { background: #dc3545; color: white; padding: 2px 6px; border-radius: 3px; }
        .footer { text-align: center; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; color: #888; font-size: 10px; }
        .stats-row td { font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>
    <h2>School Management System</h2>
    <h4>Performance Report</h4>
    <p class="subtitle">Generated on: {{ \Carbon\Carbon::now()->format('l, d-m-Y h:i A') }}</p>

    <!-- Summary Stats -->
    <div class="card">
        <div class="section-header">Performance Summary</div>
        <div class="card-body">
            <table>
                <tr class="stats-row">
                    <th>Average Score</th>
                    <td>{{ $averageScore ?? 'N/A' }}{{ is_numeric($averageScore ?? null) ? '%' : '' }}</td>
                    <th>Pass Rate</th>
                    <td>{{ $passRate ?? 'N/A' }}{{ is_numeric($passRate ?? null) ? '%' : '' }}</td>
                </tr>
                <tr class="stats-row">
                    <th>Highest Score</th>
                    <td>{{ $highestScore ?? 'N/A' }}{{ is_numeric($highestScore ?? null) ? '%' : '' }}</td>
                    <th>Lowest Score</th>
                    <td>{{ $lowestScore ?? 'N/A' }}{{ is_numeric($lowestScore ?? null) ? '%' : '' }}</td>
                </tr>
                <tr class="stats-row">
                    <th>Top Performers (≥90%)</th>
                    <td colspan="3">{{ $topPerformersCount ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Student Performance Table -->
    <div class="card">
        <div class="section-header">Student Performance List</div>
        <div class="card-body">
            <table>
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
                    @php
                        $avg = $student->marks->avg(function($mark) {
                            return ($mark->total_marks > 0) ? ($mark->obtained_marks / $mark->total_marks * 100) : 0;
                        });
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $student->user->name ?? 'N/A' }}</td>
                        <td>{{ $student->schoolClass->class_name ?? 'N/A' }}</td>
                        <td>{{ round($avg ?? 0, 2) }}%</td>
                        <td>
                            @if(($avg ?? 0) >= 40)
                                <span class="badge-pass">Pass</span>
                            @else
                                <span class="badge-fail">Fail</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer generated document. No signature required.</p>
        <p>&copy; {{ \Carbon\Carbon::now()->format('Y') }} School Management System. All rights reserved.</p>
    </div>
</body>
</html>
