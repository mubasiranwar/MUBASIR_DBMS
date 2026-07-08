<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Teacher\MarkController;
use App\Http\Controllers\Student\MarkController as StudentMarkController;

use App\Http\Controllers\Admin\ExamTypeController;
use App\Http\Controllers\Admin\ExamTimetableController;
use App\Http\Controllers\Admin\TeacherSubjectController;
use App\Http\Controllers\Admin\ClassSubjectController;

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ClassSectionController;
use App\Http\Controllers\StudentEnrollmentController;

/*
|--------------------------------------------------------------------------
| Welcome Page
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Default Dashboard (Laravel Breeze)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if (!$user) {
        return redirect()->route('login');
    }
    
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'teacher') {
        return redirect()->route('teacher.dashboard');
    } elseif ($user->role === 'student') {
        return redirect()->route('student.dashboard');
    }
    
    return redirect()->route('login');
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])
            ->name('dashboard');

        // Mark Routes
        Route::get('/marks', [MarkController::class, 'index'])->name('marks.index');
        Route::get('/marks/create', [MarkController::class, 'create'])->name('marks.create');
        Route::post('/marks', [MarkController::class, 'store'])->name('marks.store');
        Route::get('/marks/entry/{examTimetable}', [MarkController::class, 'entry'])->name('marks.entry');
        Route::post('/marks/save', [MarkController::class, 'save'])->name('marks.save');
        Route::get('/marks/show/{examTimetable}', [MarkController::class, 'show'])->name('marks.show');
        Route::get('/marks/student-list', [MarkController::class, 'studentList'])->name('marks.student-list');
        Route::get('/marks/download/{examTimetable}', [MarkController::class, 'download'])->name('marks.download');
        Route::get('/marks/{id}/edit', [MarkController::class, 'edit'])->name('marks.edit');
        Route::put('/marks/{id}', [MarkController::class, 'update'])->name('marks.update');
        Route::delete('/marks/{id}', [MarkController::class, 'destroy'])->name('marks.destroy');
        Route::delete('/marks/destroyAll/{examTimetable}', [MarkController::class, 'destroyAll'])->name('marks.destroyAll');

    });

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])
            ->name('dashboard');

        // Profile
        Route::get('/profile', function () {
            return view('student.profile');
        })->name('profile');

        // Marks Routes
        Route::get('/marks', [StudentMarkController::class, 'index'])
            ->name('marks.index');
        
        Route::get('/marks/{examTimetable}', [StudentMarkController::class, 'show'])
            ->name('marks.show');
        
        Route::get('/marks/print/{examTimetable}', [StudentMarkController::class, 'print'])
            ->name('marks.print');
        
        Route::get('/marks/download-pdf/{examTimetable}', [StudentMarkController::class, 'downloadPdf'])
            ->name('marks.download-pdf');

        // Transcript / DMC Routes
        Route::get('/transcript/{examTimetable}', [StudentMarkController::class, 'transcript'])
            ->name('transcript');
        
        Route::get('/transcript/print/{examTimetable}', [StudentMarkController::class, 'printTranscript'])
            ->name('transcript.print');
        
        Route::get('/transcript/download-pdf/{examTimetable}', [StudentMarkController::class, 'downloadTranscriptPdf'])
            ->name('transcript.download-pdf');

        // Performance Card
        Route::get('/performance-card', [StudentMarkController::class, 'performanceCard'])
            ->name('performance-card');
        
        Route::get('/performance-card/print', [StudentMarkController::class, 'printPerformanceCard'])
            ->name('performance-card.print');
        
        Route::get('/performance-card/download-pdf', [StudentMarkController::class, 'downloadPerformanceCardPdf'])
            ->name('performance-card.download-pdf');

    });

/*
|--------------------------------------------------------------------------
| Admin Routes (Protected with auth and admin middleware)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {

    // Resource routes for all admin modules
    Route::resource('academic-years', AcademicYearController::class);
    Route::resource('teachers', TeacherController::class);
    Route::resource('students', StudentController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('classes', SchoolClassController::class);
    Route::resource('sections', SectionController::class);
    Route::resource('class-sections', ClassSectionController::class);
    Route::resource('student-enrollments', StudentEnrollmentController::class);
    Route::resource('teacher-subjects', TeacherSubjectController::class);
    Route::resource('class-subjects', ClassSubjectController::class);
    Route::resource('exam-types', ExamTypeController::class);
    Route::resource('exam-timetables', ExamTimetableController::class);

    // Custom routes for exam timetables
    Route::get(
        'exam-timetables/print/{exam?}',
        [ExamTimetableController::class, 'print']
    )->name('exam-timetables.print');

});

/*
|--------------------------------------------------------------------------
| Admin Dashboard & Performance Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // ============================================
        // PERFORMANCE ROUTES
        // ============================================
        
        // Overall Performance
        Route::get('/performance/overall', [DashboardController::class, 'overallPerformance'])
            ->name('performance.overall');
        
        // Pass Rate
        Route::get('/performance/pass-rate', [DashboardController::class, 'passRate'])
            ->name('performance.pass-rate');
        
        // Top Performers
        Route::get('/performance/top-performers', [DashboardController::class, 'topPerformers'])
            ->name('performance.top-performers');
        
        // Filter Performance Data
        Route::get('/performance/filter', [DashboardController::class, 'filter'])
            ->name('performance.filter');
        
        // Performance Report
        Route::get('/performance/report', [DashboardController::class, 'performanceReport'])
            ->name('performance.report');
        
        // Generate Performance Report (PDF/Excel)
        Route::get('/performance/generate-report', [DashboardController::class, 'generateReport'])
            ->name('performance.generate-report');
        
        // Export Performance Data
        Route::get('/performance/export', [DashboardController::class, 'export'])
            ->name('performance.export');

        // ============================================
        // ADDITIONAL PERFORMANCE SUB-ROUTES
        // ============================================
        
        // Class-wise Performance
        Route::get('/performance/class/{classId}', [DashboardController::class, 'classPerformance'])
            ->name('performance.class');
        
        // Subject-wise Performance
        Route::get('/performance/subject/{subjectId}', [DashboardController::class, 'subjectPerformance'])
            ->name('performance.subject');
        
        // Student-wise Performance
        Route::get('/performance/student/{studentId}', [DashboardController::class, 'studentPerformance'])
            ->name('performance.student');
        
        // Exam-wise Performance
        Route::get('/performance/exam/{examId}', [DashboardController::class, 'examPerformance'])
            ->name('performance.exam');
        
        // Performance Comparison
        Route::get('/performance/comparison', [DashboardController::class, 'performanceComparison'])
            ->name('performance.comparison');
        
        // Performance Trends
        Route::get('/performance/trends', [DashboardController::class, 'performanceTrends'])
            ->name('performance.trends');
        
        // Grade Distribution
        Route::get('/performance/grade-distribution', [DashboardController::class, 'gradeDistribution'])
            ->name('performance.grade-distribution');

    });

/*
|--------------------------------------------------------------------------
| Profile Routes (Laravel Breeze)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});

/*
|--------------------------------------------------------------------------
| Authentication Routes (Laravel Breeze)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';