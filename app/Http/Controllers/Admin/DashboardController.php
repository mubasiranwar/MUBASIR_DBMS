<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\StudentEnrollment;
use App\Models\ClassSubject;
use App\Models\TeacherSubject;
use App\Models\ExamTimetable;
use App\Models\ExamType;
use App\Models\Mark;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        // Get all basic counts
        $teachers = Teacher::count();
        $students = Student::count();
        $subjects = Subject::count();
        $academicYears = AcademicYear::count();
        $classes = SchoolClass::count();
        $sections = Section::count();
        $teacherSubjects = TeacherSubject::count();
        $classSections = ClassSection::count();
        $classSubjects = ClassSubject::count();
        $enrollments = StudentEnrollment::count();
        $examTimetables = ExamTimetable::count();
        $examTypes = ExamType::count();

        // Get data for filters
        $academicYearsList = AcademicYear::all();
        $classesList = SchoolClass::all();
        $examTypesList = ExamType::all();
        $totalStudents = Student::count();

        // Calculate performance metrics
        $performanceData = $this->calculatePerformanceMetrics();

        // Merge all data
        $data = array_merge(
            compact(
                'teachers',
                'students',
                'subjects',
                'academicYears',
                'classes',
                'sections',
                'teacherSubjects',
                'classSections',
                'classSubjects',
                'enrollments',
                'examTimetables',
                'examTypes',
                'academicYearsList',
                'classesList',
                'examTypesList',
                'totalStudents'
            ),
            $performanceData
        );

        return view('admin.dashboard', $data);
    }

    /**
     * Calculate performance metrics
     */
    private function calculatePerformanceMetrics()
    {
        // Get all marks with relationships
        $marks = Mark::with(['student', 'examTimetable.examType'])
            ->whereHas('examTimetable')
            ->get();

        if ($marks->isEmpty()) {
            return [
                'overallPerformance' => 'N/A',
                'passRate' => 'N/A',
                'topPerformersCount' => 'N/A',
                'averageScore' => 'N/A',
                'highestScore' => 'N/A',
                'lowestScore' => 'N/A',
                'distributionData' => '[0, 0, 0, 0, 0]'
            ];
        }

        // Group marks by student and calculate averages
        $studentAverages = [];
        $studentPassStatus = [];
        $allScores = [];

        foreach ($marks->groupBy('student_id') as $studentId => $studentMarks) {
            $totalMarks = 0;
            $totalSubjects = $studentMarks->count();
            $passedSubjects = 0;

            foreach ($studentMarks as $mark) {
                $score = ($mark->total_marks > 0) ? ($mark->marks_obtained / $mark->total_marks * 100) : 0;
                $totalMarks += $score;
                $allScores[] = $score;

                if ($score >= 40) { // Assuming 40% is passing
                    $passedSubjects++;
                }
            }

            $average = ($totalSubjects > 0) ? $totalMarks / $totalSubjects : 0;
            $studentAverages[] = $average;
            $studentPassStatus[$studentId] = ($totalSubjects > 0 && $passedSubjects == $totalSubjects);
        }

        // Calculate metrics
        $averageScore = !empty($studentAverages) ? round(array_sum($studentAverages) / count($studentAverages), 2) : 0;
        $highestScore = !empty($studentAverages) ? round(max($studentAverages), 2) : 0;
        $lowestScore = !empty($studentAverages) ? round(min($studentAverages), 2) : 0;
        $overallPerformance = $averageScore . '%';
        $passRate = !empty($studentPassStatus) ? round((array_sum($studentPassStatus) / count($studentPassStatus)) * 100, 2) : 0;
        $topPerformersCount = !empty($studentAverages) ? count(array_filter($studentAverages, function($score) {
            return $score >= 90;
        })) : 0;

        // Distribution data for chart
        $distribution = [0, 0, 0, 0, 0]; // [0-20%, 21-40%, 41-60%, 61-80%, 81-100%]
        foreach ($allScores as $score) {
            if ($score <= 20) $distribution[0]++;
            elseif ($score <= 40) $distribution[1]++;
            elseif ($score <= 60) $distribution[2]++;
            elseif ($score <= 80) $distribution[3]++;
            else $distribution[4]++;
        }

        return [
            'overallPerformance' => $overallPerformance,
            'passRate' => $passRate,
            'topPerformersCount' => $topPerformersCount,
            'averageScore' => $averageScore,
            'highestScore' => $highestScore,
            'lowestScore' => $lowestScore,
            'distributionData' => json_encode($distribution)
        ];
    }

    /**
     * Filter performance data
     */
    public function filter(Request $request)
    {
        $query = Mark::with(['student', 'examTimetable.examType', 'examTimetable.academicYear'])
            ->whereHas('examTimetable');

        // Apply filters
        if ($request->academic_year) {
            $query->whereHas('examTimetable', function($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year);
            });
        }

        if ($request->class_id) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('school_class_id', $request->class_id);
            });
        }

        if ($request->exam_type) {
            $query->whereHas('examTimetable', function($q) use ($request) {
                $q->where('exam_type_id', $request->exam_type);
            });
        }

        $marks = $query->get();

        // Calculate filtered metrics
        $filteredData = $this->calculateFilteredMetrics($marks);

        return redirect()->route('admin.dashboard')
            ->with('filtered_data', $filteredData)
            ->with('success', 'Performance data filtered successfully.');
    }

    /**
     * Calculate filtered metrics
     */
    private function calculateFilteredMetrics($marks)
    {
        if ($marks->isEmpty()) {
            return [
                'overallPerformance' => 'N/A',
                'passRate' => 'N/A',
                'topPerformersCount' => 'N/A',
                'averageScore' => 'N/A',
                'highestScore' => 'N/A',
                'lowestScore' => 'N/A'
            ];
        }

        $studentAverages = [];
        $studentPassStatus = [];

        foreach ($marks->groupBy('student_id') as $studentId => $studentMarks) {
            $totalMarks = 0;
            $totalSubjects = $studentMarks->count();
            $passedSubjects = 0;

            foreach ($studentMarks as $mark) {
                $score = ($mark->total_marks > 0) ? ($mark->marks_obtained / $mark->total_marks * 100) : 0;
                $totalMarks += $score;

                if ($score >= 40) {
                    $passedSubjects++;
                }
            }

            $average = ($totalSubjects > 0) ? $totalMarks / $totalSubjects : 0;
            $studentAverages[] = $average;
            $studentPassStatus[$studentId] = ($totalSubjects > 0 && $passedSubjects == $totalSubjects);
        }

        $averageScore = !empty($studentAverages) ? round(array_sum($studentAverages) / count($studentAverages), 2) : 0;
        $highestScore = !empty($studentAverages) ? round(max($studentAverages), 2) : 0;
        $lowestScore = !empty($studentAverages) ? round(min($studentAverages), 2) : 0;
        $overallPerformance = $averageScore . '%';
        $passRate = !empty($studentPassStatus) ? round((array_sum($studentPassStatus) / count($studentPassStatus)) * 100, 2) : 0;
        $topPerformersCount = !empty($studentAverages) ? count(array_filter($studentAverages, function($score) {
            return $score >= 90;
        })) : 0;

        return [
            'overallPerformance' => $overallPerformance,
            'passRate' => $passRate,
            'topPerformersCount' => $topPerformersCount,
            'averageScore' => $averageScore,
            'highestScore' => $highestScore,
            'lowestScore' => $lowestScore
        ];
    }

    /**
     * Overall performance page
     */
    public function overallPerformance()
    {
        $performanceData = $this->calculatePerformanceMetrics();
        $academicYears = AcademicYear::all();
        $classes = SchoolClass::all();
        $examTypes = ExamType::all();
        
        return view('admin.performance.overall', array_merge(
            $performanceData,
            compact('academicYears', 'classes', 'examTypes')
        ));
    }

    /**
     * Pass rate details
     */
    public function passRate()
    {
        $performanceData = $this->calculatePerformanceMetrics();
        return view('admin.performance.pass-rate', $performanceData);
    }

    /**
     * Top performers list
     */
    public function topPerformers()
    {
        $topStudents = $this->getTopPerformers();
        return view('admin.performance.top-performers', compact('topStudents'));
    }

    /**
     * Get top performers
     */
    private function getTopPerformers($limit = 10)
    {
        $students = Student::with(['marks', 'schoolClass'])->get();
        $result = [];

        foreach ($students as $student) {
            $avg = $student->marks->avg(function($mark) {
                return ($mark->total_marks > 0) ? ($mark->marks_obtained / $mark->total_marks * 100) : 0;
            });
            
            if ($avg > 0) {
                $result[] = [
                    'student' => $student,
                    'average' => round($avg, 2)
                ];
            }
        }

        // Sort by average descending and take top performers
        usort($result, function($a, $b) {
            return $b['average'] <=> $a['average'];
        });

        return array_slice($result, 0, $limit);
    }

    /**
     * Performance report page
     */
    public function performanceReport()
    {
        $performanceData = $this->calculatePerformanceMetrics();
        $students = Student::with(['marks', 'schoolClass'])->get();
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        
        return view('admin.performance.report', array_merge(
            $performanceData,
            compact('students', 'classes', 'subjects')
        ));
    }

    /**
     * Generate performance report (PDF)
     */
    public function generateReport()
    {
        $performanceData = $this->calculatePerformanceMetrics();
        $students = Student::with(['marks', 'schoolClass'])->get();
        
        // You can use DomPDF or any PDF package
        // For now, return a view
        return view('admin.performance.pdf-report', array_merge(
            $performanceData,
            compact('students')
        ));
    }

    /**
     * Export performance data
     */
    public function export(Request $request)
    {
        // Export to Excel or CSV
        // You can use Maatwebsite\Excel package
        // For now, return a CSV download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="performance-data.csv"',
        ];

        $columns = ['Student Name', 'Class', 'Average Score', 'Status'];
        $students = Student::with(['marks', 'schoolClass'])->get();
        
        $callback = function() use ($students, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($students as $student) {
                $avg = $student->marks->avg(function($mark) {
                    return ($mark->total_marks > 0) ? ($mark->marks_obtained / $mark->total_marks * 100) : 0;
                });
                
                fputcsv($file, [
                    $student->name,
                    $student->schoolClass->class_name ?? 'N/A',
                    round($avg, 2) . '%',
                    $avg >= 40 ? 'Pass' : 'Fail'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Class-wise performance
     */
    public function classPerformance($classId)
    {
        $class = SchoolClass::findOrFail($classId);
        $studentIds = \App\Models\StudentEnrollment::whereHas('classSection', function($q) use ($classId) {
            $q->where('school_class_id', $classId);
        })->pluck('student_id');
        $students = Student::whereIn('id', $studentIds)->with('marks')->get();
        
        $performanceData = [];
        foreach ($students as $student) {
            $avg = $student->marks->avg(function($mark) {
                return ($mark->total_marks > 0) ? ($mark->marks_obtained / $mark->total_marks * 100) : 0;
            });
            
            $performanceData[] = [
                'student' => $student,
                'average' => round($avg, 2),
                'status' => $avg >= 40 ? 'Pass' : 'Fail'
            ];
        }

        return view('admin.performance.class', compact('class', 'performanceData'));
    }

    /**
     * Subject-wise performance
     */
    public function subjectPerformance($subjectId)
    {
        $subject = Subject::findOrFail($subjectId);
        $marks = Mark::whereHas('examTimetable', function($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        })->with(['student', 'examTimetable'])->get();

        $performanceData = [];
        foreach ($marks as $mark) {
            $score = ($mark->total_marks > 0) ? ($mark->marks_obtained / $mark->total_marks * 100) : 0;
            $performanceData[] = [
                'student' => $mark->student,
                'marks_obtained' => $mark->marks_obtained,
                'total_marks' => $mark->total_marks,
                'percentage' => round($score, 2),
                'status' => $score >= 40 ? 'Pass' : 'Fail'
            ];
        }

        return view('admin.performance.subject', compact('subject', 'performanceData'));
    }

    /**
     * Student-wise performance
     */
    public function studentPerformance($studentId)
    {
        $student = Student::with(['marks', 'schoolClass'])->findOrFail($studentId);
        $marks = $student->marks()->with('examTimetable')->get();

        $performanceData = [];
        foreach ($marks as $mark) {
            $score = ($mark->total_marks > 0) ? ($mark->marks_obtained / $mark->total_marks * 100) : 0;
            $performanceData[] = [
                'exam' => $mark->examTimetable,
                'marks_obtained' => $mark->marks_obtained,
                'total_marks' => $mark->total_marks,
                'percentage' => round($score, 2),
                'status' => $score >= 40 ? 'Pass' : 'Fail'
            ];
        }

        $averageScore = $marks->avg(function($mark) {
            return ($mark->total_marks > 0) ? ($mark->marks_obtained / $mark->total_marks * 100) : 0;
        });

        return view('admin.performance.student', compact('student', 'performanceData', 'averageScore'));
    }

    /**
     * Exam-wise performance
     */
    public function examPerformance($examId)
    {
        $exam = ExamTimetable::with(['subject', 'schoolClass', 'section'])->findOrFail($examId);
        $marks = Mark::where('exam_timetable_id', $examId)->with('student')->get();

        $performanceData = [];
        $totalStudents = $marks->count();
        $passedStudents = 0;

        foreach ($marks as $mark) {
            $score = ($mark->total_marks > 0) ? ($mark->marks_obtained / $mark->total_marks * 100) : 0;
            $status = $score >= 40 ? 'Pass' : 'Fail';
            
            if ($status == 'Pass') {
                $passedStudents++;
            }

            $performanceData[] = [
                'student' => $mark->student,
                'marks_obtained' => $mark->marks_obtained,
                'total_marks' => $mark->total_marks,
                'percentage' => round($score, 2),
                'status' => $status
            ];
        }

        $passRate = $totalStudents > 0 ? round(($passedStudents / $totalStudents) * 100, 2) : 0;

        return view('admin.performance.exam', compact('exam', 'performanceData', 'passRate', 'totalStudents', 'passedStudents'));
    }

    /**
     * Performance comparison
     */
    public function performanceComparison(Request $request)
    {
        $classes = SchoolClass::all();
        $selectedClasses = $request->classes ?? [];
        $comparisonData = [];

        if (!empty($selectedClasses)) {
            foreach ($selectedClasses as $classId) {
                $class = SchoolClass::find($classId);
                if ($class) {
                    $studentIds = \App\Models\StudentEnrollment::whereHas('classSection', function($q) use ($classId) {
                        $q->where('school_class_id', $classId);
                    })->pluck('student_id');
                    $students = Student::whereIn('id', $studentIds)->with('marks')->get();
                    $averages = [];
                    
                    foreach ($students as $student) {
                        $avg = $student->marks->avg(function($mark) {
                            return ($mark->total_marks > 0) ? ($mark->marks_obtained / $mark->total_marks * 100) : 0;
                        });
                        if ($avg > 0) {
                            $averages[] = $avg;
                        }
                    }

                    $comparisonData[] = [
                        'class' => $class,
                        'average' => !empty($averages) ? round(array_sum($averages) / count($averages), 2) : 0,
                        'student_count' => count($students)
                    ];
                }
            }
        }

        return view('admin.performance.comparison', compact('classes', 'comparisonData', 'selectedClasses'));
    }

    /**
     * Performance trends
     */
    public function performanceTrends()
    {
        $academicYears = AcademicYear::all();
        $trendsData = [];

        foreach ($academicYears as $year) {
            $marks = Mark::whereHas('examTimetable', function($q) use ($year) {
                $q->where('academic_year_id', $year->id);
            })->get();

            $averages = [];
            foreach ($marks->groupBy('student_id') as $studentMarks) {
                $avg = $studentMarks->avg(function($mark) {
                    return ($mark->total_marks > 0) ? ($mark->marks_obtained / $mark->total_marks * 100) : 0;
                });
                if ($avg > 0) {
                    $averages[] = $avg;
                }
            }

            $trendsData[] = [
                'year' => $year,
                'average' => !empty($averages) ? round(array_sum($averages) / count($averages), 2) : 0,
                'student_count' => count($averages)
            ];
        }

        return view('admin.performance.trends', compact('trendsData', 'academicYears'));
    }

    /**
     * Grade distribution
     */
    public function gradeDistribution()
    {
        $marks = Mark::with('student')->get();
        $distribution = [
            'A+' => 0, // 90-100%
            'A' => 0,  // 80-89%
            'B' => 0,  // 70-79%
            'C' => 0,  // 60-69%
            'D' => 0,  // 50-59%
            'F' => 0   // Below 50%
        ];

        foreach ($marks as $mark) {
            $percentage = ($mark->total_marks > 0) ? ($mark->marks_obtained / $mark->total_marks * 100) : 0;
            
            if ($percentage >= 90) $distribution['A+']++;
            elseif ($percentage >= 80) $distribution['A']++;
            elseif ($percentage >= 70) $distribution['B']++;
            elseif ($percentage >= 60) $distribution['C']++;
            elseif ($percentage >= 50) $distribution['D']++;
            else $distribution['F']++;
        }

        return view('admin.performance.grade-distribution', compact('distribution'));
    }
}