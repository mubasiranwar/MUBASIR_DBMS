<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Mark;
use App\Models\ExamTimetable;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\ExamType;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\ClassSection;

class MarkController extends Controller
{
    /**
     * Display all marks entered by the teacher.
     */
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher not found');
        }

        $subjectIds = TeacherSubject::where('teacher_id', $teacher->id)
            ->pluck('subject_id');

        $query = ExamTimetable::with([
            'examType',
            'academicYear',
            'schoolClass',
            'section',
            'subject',
            'marks'
        ])
        ->whereIn('subject_id', $subjectIds);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('examType', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhereHas('subject', function($q2) use ($search) {
                    $q2->where('subject_name', 'like', "%{$search}%");
                });
            });
        }

        // Exam filter
        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status == 'completed') {
                $query->whereHas('marks');
            } elseif ($request->status == 'pending') {
                $query->whereDoesntHave('marks');
            }
        }

        $examTimetables = $query->orderBy('exam_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get exam types for filter dropdown
        $examTypes = ExamType::all();

        return view('teacher.marks.index', compact('examTimetables', 'examTypes'));
    }

    /**
     * Show the marks entry form for a specific timetable (Multiple Subjects).
     */
    /**
 * Show the marks entry form for a specific timetable.
 * Now shows only the subject associated with this exam timetable.
 */
public function entry(ExamTimetable $examTimetable)
{
    // Get the class section
    $classSection = ClassSection::where('school_class_id', $examTimetable->school_class_id)
        ->where('section_id', $examTimetable->section_id)
        ->first();

    if (!$classSection) {
        abort(404, 'Class Section not found');
    }

    // Get students with their details
    $students = DB::table('student_enrollments as se')
        ->join('students as s', 'se.student_id', '=', 's.id')
        ->join('users as u', 's.user_id', '=', 'u.id')
        ->where('se.class_section_id', $classSection->id)
        ->select(
            's.id as student_id',
            'u.name as student_name',
            'se.roll_no'
        )
        ->get();

    // Get ONLY the subject for this exam timetable
    $subjects = collect([$examTimetable->subject]);

    // Check if marks already exist
    $existingMarksData = Mark::where('exam_timetable_id', $examTimetable->id)
        ->get()
        ->groupBy('student_id')
        ->map(function($marks) {
            return $marks->keyBy('subject_id');
        })
        ->toArray();

    return view('teacher.marks.entry', compact(
        'examTimetable',
        'students',
        'subjects',
        'existingMarksData'
    ));
}
    /**
     * Save marks for students (Multiple Subjects).
     */
    /**
 * Save marks for students.
 */
public function save(Request $request)
{
    $request->validate([
        'exam_timetable_id' => 'required|exists:exam_timetables,id',
        'subject_id' => 'required|exists:subjects,id',
        'marks' => 'required|array',
        'marks.*.obtained' => 'required|numeric|min:0',
        'marks.*.total' => 'required|numeric|min:1',
        'marks.*.remarks' => 'nullable|string|max:255',
    ]);

    $teacher = Teacher::where('user_id', auth()->id())->first();

    if (!$teacher) {
        return redirect()->back()->with('error', 'Teacher not found.');
    }

    foreach ($request->marks as $studentId => $mark) {
        if (empty($studentId)) {
            continue;
        }

        // Validate that obtained marks don't exceed total marks
        if ($mark['obtained'] > $mark['total']) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', "Obtained marks ({$mark['obtained']}) cannot exceed total marks ({$mark['total']}).");
        }

        Mark::updateOrCreate(
            [
                'student_id' => $studentId,
                'subject_id' => $request->subject_id,
                'exam_timetable_id' => $request->exam_timetable_id,
            ],
            [
                'teacher_id' => $teacher->id,
                'total_marks' => $mark['total'],
                'obtained_marks' => $mark['obtained'],
                'remarks' => $mark['remarks'] ?? null,
            ]
        );
    }

    return redirect()
        ->route('teacher.marks.index')
        ->with('success', 'Marks saved successfully.');
}
    /**
     * Show student marks for a specific timetable.
     */
    public function show(ExamTimetable $examTimetable)
    {
        // First get the class section
        $classSection = ClassSection::where('school_class_id', $examTimetable->school_class_id)
            ->where('section_id', $examTimetable->section_id)
            ->first();

        if (!$classSection) {
            abort(404, 'Class Section not found');
        }

        // Get all subjects for this class
        $subjects = Subject::whereHas('schoolClasses', function($q) use ($examTimetable) {
            $q->where('school_class_id', $examTimetable->school_class_id);
        })->get();

        if ($subjects->isEmpty()) {
            $subjects = Subject::all();
        }

        // Get students with marks for all subjects
        $students = DB::table('student_enrollments as se')
            ->join('students as s', 'se.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('se.class_section_id', $classSection->id)
            ->select(
                's.id as student_id',
                'se.roll_no',
                'u.name as student_name'
            )
            ->get();

        // Get marks for all students
        $marksData = Mark::where('exam_timetable_id', $examTimetable->id)
            ->get()
            ->groupBy('student_id')
            ->map(function($marks) {
                return $marks->keyBy('subject_id');
            });

        // Attach marks to students
        foreach ($students as $student) {
            $student->marks = $marksData[$student->student_id] ?? collect();
        }

        return view('teacher.marks.show', compact('examTimetable', 'students', 'subjects'));
    }

    /**
     * Delete all marks for a specific timetable.
     */
    public function destroyAll(ExamTimetable $examTimetable)
    {
        Mark::where('exam_timetable_id', $examTimetable->id)->delete();

        return redirect()
            ->route('teacher.marks.index')
            ->with('success', 'All marks for this exam have been deleted.');
    }

    /**
     * Delete a single mark.
     */
    public function destroy($id)
    {
        $mark = Mark::findOrFail($id);
        $mark->delete();

        return redirect()
            ->route('teacher.marks.index')
            ->with('success', 'Mark deleted successfully.');
    }

    /**
     * Edit a single mark.
     */
    public function edit($id)
    {
        $mark = Mark::with(['student.user', 'subject', 'examTimetable'])->findOrFail($id);
        $examTimetable = ExamTimetable::find($mark->exam_timetable_id);
        
        // Get all exam types for dropdown
        $examTypes = ExamType::all();
        $subjects = Subject::all();
        
        return view('teacher.marks.edit', compact('mark', 'examTimetable', 'examTypes', 'subjects'));
    }

    /**
     * Update a single mark.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'obtained_marks' => 'required|numeric|min:0',
            'total_marks' => 'required|numeric|min:1',
            'remarks' => 'nullable|string|max:255',
        ]);

        $mark = Mark::findOrFail($id);
        
        // Validate that obtained marks don't exceed total marks
        if ($request->obtained_marks > $request->total_marks) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Obtained marks cannot exceed total marks.');
        }

        $mark->update([
            'obtained_marks' => $request->obtained_marks,
            'total_marks' => $request->total_marks,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('teacher.marks.show', $mark->exam_timetable_id)
            ->with('success', 'Mark updated successfully.');
    }

    /**
     * Show create form (timetable selection).
     */
    public function create()
    {
        $teacher = Auth::user()->teacher;

        $examTimetables = ExamTimetable::with([
            'examType',
            'schoolClass',
            'section',
            'subject'
        ])->get();

        return view('teacher.marks.create', compact('examTimetables'));
    }

    /**
     * Store marks (alternative).
     */
    public function store(Request $request)
    {
        return $this->save($request);
    }

    /**
     * Display student marks list with filters.
     */
    public function studentList(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher not found');
        }

        $subjectIds = TeacherSubject::where('teacher_id', $teacher->id)
            ->pluck('subject_id');

        $query = Mark::with([
            'student.user',
            'student',
            'subject',
            'examTimetable.examType',
            'examTimetable.schoolClass',
            'examTimetable.section'
        ])
        ->whereIn('subject_id', $subjectIds);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('student.user', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhereHas('subject', function($q2) use ($search) {
                    $q2->where('subject_name', 'like', "%{$search}%");
                })->orWhereHas('examTimetable.examType', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Apply exam filter
        if ($request->filled('exam_id')) {
            $query->whereHas('examTimetable', function($q) use ($request) {
                $q->where('exam_id', $request->exam_id);
            });
        }

        // Apply class filter
        if ($request->filled('class_id')) {
            $query->whereHas('examTimetable', function($q) use ($request) {
                $q->where('school_class_id', $request->class_id);
            });
        }

        // Apply subject filter
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Order by latest
        $marks = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get filter data
        $examTypes = ExamType::all();
        $classes = SchoolClass::all();
        $subjects = Subject::whereIn('id', $subjectIds)->get();

        return view('teacher.marks.student_marks', compact(
            'marks',
            'examTypes',
            'classes',
            'subjects'
        ));
    }

    /**
     * Download marks report for a specific timetable (Simple CSV).
     */
    public function download(ExamTimetable $examTimetable)
    {
        // First get the class section
        $classSection = ClassSection::where('school_class_id', $examTimetable->school_class_id)
            ->where('section_id', $examTimetable->section_id)
            ->first();

        if (!$classSection) {
            abort(404, 'Class Section not found');
        }

        // Get all subjects for this class
        $subjects = Subject::whereHas('schoolClasses', function($q) use ($examTimetable) {
            $q->where('school_class_id', $examTimetable->school_class_id);
        })->get();

        if ($subjects->isEmpty()) {
            $subjects = Subject::all();
        }

        // Get students with marks
        $students = DB::table('student_enrollments as se')
            ->join('students as s', 'se.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->leftJoin('marks as m', function($join) use ($examTimetable) {
                $join->on('se.student_id', '=', 'm.student_id')
                     ->where('m.exam_timetable_id', '=', $examTimetable->id);
            })
            ->where('se.class_section_id', $classSection->id)
            ->select(
                'se.student_id',
                'se.roll_no',
                'u.name as student_name',
                'm.subject_id',
                'm.total_marks',
                'm.obtained_marks',
                'm.remarks'
            )
            ->get()
            ->groupBy('student_id');

        // Generate CSV
        $csvContent = "Roll No,Student Name";
        
        // Add subject columns
        foreach ($subjects as $subject) {
            $csvContent .= ",{$subject->subject_name} (Total)";
            $csvContent .= ",{$subject->subject_name} (Obtained)";
        }
        $csvContent .= ",Total Marks,Obtained Marks,Percentage,Grade,Status,Remarks\n";

        foreach ($students as $studentId => $studentMarks) {
            $student = $studentMarks->first();
            $csvContent .= "{$student->roll_no},{$student->student_name}";
            
            $totalMarks = 0;
            $obtainedMarks = 0;
            
            foreach ($subjects as $subject) {
                $mark = $studentMarks->where('subject_id', $subject->id)->first();
                $total = $mark->total_marks ?? 0;
                $obtained = $mark->obtained_marks ?? 0;
                
                $csvContent .= ",{$total},{$obtained}";
                $totalMarks += $total;
                $obtainedMarks += $obtained;
            }
            
            $percentage = ($totalMarks > 0) ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;
            $grade = $this->getGrade($percentage);
            $status = ($percentage >= 40) ? 'Pass' : 'Fail';
            $remarks = $studentMarks->first()->remarks ?? '';
            
            $csvContent .= ",{$totalMarks},{$obtainedMarks},{$percentage}%,{$grade},{$status},{$remarks}\n";
        }

        // Return as download
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="marks_report_' . $examTimetable->id . '.csv"');
    }

    /**
     * Helper function to get grade based on percentage.
     */
    private function getGrade($percentage)
    {
        if ($percentage >= 90) {
            return 'A+';
        } elseif ($percentage >= 80) {
            return 'A';
        } elseif ($percentage >= 70) {
            return 'B';
        } elseif ($percentage >= 60) {
            return 'C';
        } elseif ($percentage >= 50) {
            return 'D';
        } elseif ($percentage >= 40) {
            return 'E';
        } else {
            return 'F';
        }
    }
}