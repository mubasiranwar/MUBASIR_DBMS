<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\ExamTimetable;
use App\Models\Mark;
use App\Models\ExamType;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'No teacher profile found for your account. Please contact the admin.'
            ]);
        }

        // Get teacher's subject IDs
        $subjectIds = TeacherSubject::where('teacher_id', $teacher->id)
            ->pluck('subject_id');

        // Get teacher's class IDs (through subjects)
        $classIds = DB::table('class_subjects')
            ->whereIn('subject_id', $subjectIds)
            ->pluck('school_class_id')
            ->unique();

        // Stats
        $subjects = $subjectIds->count();
        $classes = $classIds->count();

        // Today's exams
        $today = Carbon::today()->format('Y-m-d');
        $todayTimetable = ExamTimetable::with([
            'examType',
            'schoolClass',
            'section',
            'subject',
            'marks'
        ])
        ->whereIn('subject_id', $subjectIds)
        ->whereDate('exam_date', $today)
        ->orderBy('start_time')
        ->get();

        $todayExams = $todayTimetable->count();

        // Upcoming exams (excluding today)
        $upcomingTimetable = ExamTimetable::with([
            'examType',
            'schoolClass',
            'section',
            'subject'
        ])
        ->whereIn('subject_id', $subjectIds)
        ->whereDate('exam_date', '>', $today)
        ->orderBy('exam_date', 'asc')
        ->limit(10)
        ->get();

        // Quick Entry Exams - All exams assigned to teacher (today + upcoming)
        $quickEntryExams = ExamTimetable::with([
            'examType',
            'schoolClass',
            'section',
            'subject',
            'marks'
        ])
        ->whereIn('subject_id', $subjectIds)
        ->whereDate('exam_date', '>=', $today)
        ->orderBy('exam_date', 'asc')
        ->limit(20)
        ->get();

        // Pending marks (exams without marks)
        $pendingMarks = ExamTimetable::whereIn('subject_id', $subjectIds)
            ->whereDoesntHave('marks')
            ->count();

        return view('teacher.dashboard', compact(
            'subjects',
            'classes',
            'todayExams',
            'pendingMarks',
            'todayTimetable',
            'upcomingTimetable',
            'quickEntryExams'
        ));
    }
}