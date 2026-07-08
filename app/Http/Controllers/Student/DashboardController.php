<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Mark;
use App\Models\ExamTimetable;
use App\Models\Student;
use App\Models\StudentEnrollment;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user is a student
        if ($user->role !== 'student') {
            abort(403, 'Access denied. Student only area.');
        }
        
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            // Redirect to login with error — NOT to /dashboard (that causes a loop)
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'No student profile found for your account. Please contact the admin.'
            ]);
        }

        // Get student's enrollments
        $enrollments = StudentEnrollment::where('student_id', $student->id)
            ->with(['classSection.schoolClass', 'classSection.section', 'academicYear'])
            ->get();

        // Get student's marks
        $marks = Mark::with([
            'examTimetable.examType',
            'examTimetable.schoolClass',
            'examTimetable.section',
            'subject'
        ])
        ->where('student_id', $student->id)
        ->orderBy('created_at', 'desc')
        ->get();

        // Calculate statistics
        $totalMarks = $marks->sum('total_marks');
        $obtainedMarks = $marks->sum('obtained_marks');
        $averagePercentage = ($totalMarks > 0) ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;
        $subjectsCount = $marks->unique('subject_id')->count();
        $examsCount = $marks->unique('exam_timetable_id')->count();

        // Calculate grade
        $grade = $this->calculateGrade($averagePercentage);

        // Get recent marks (last 5)
        $recentMarks = $marks->take(5);

        return view('student.dashboard', compact(
            'student',
            'enrollments',
            'marks',
            'totalMarks',
            'obtainedMarks',
            'averagePercentage',
            'subjectsCount',
            'examsCount',
            'grade',
            'recentMarks'
        ));
    }

    private function calculateGrade($percentage)
    {
        if ($percentage >= 90) {
            return ['letter' => 'A+', 'color' => 'success', 'description' => 'Outstanding'];
        } elseif ($percentage >= 80) {
            return ['letter' => 'A', 'color' => 'success', 'description' => 'Excellent'];
        } elseif ($percentage >= 70) {
            return ['letter' => 'B', 'color' => 'success', 'description' => 'Very Good'];
        } elseif ($percentage >= 60) {
            return ['letter' => 'C', 'color' => 'warning', 'description' => 'Good'];
        } elseif ($percentage >= 50) {
            return ['letter' => 'D', 'color' => 'warning', 'description' => 'Fair'];
        } elseif ($percentage >= 40) {
            return ['letter' => 'E', 'color' => 'info', 'description' => 'Pass'];
        } else {
            return ['letter' => 'F', 'color' => 'danger', 'description' => 'Fail'];
        }
    }
}