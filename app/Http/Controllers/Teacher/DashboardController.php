<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassHour;
use App\Models\ClassNote;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Models\TeacherSalary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{


    public function dashboard()
    {
        $teacher = Auth::guard('teacher')->user();

        if (!$teacher) {
            return redirect()->route('teacher.login');
        }

        // Assigned Classes
        $classes = $teacher->classRooms()
            ->active()
            ->with(['course', 'classType'])
            ->get();

        // Monthly Completed Sessions
        $completedSessions = ClassHour::where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();

        // Monthly Pending Sessions
        $pendingSessions = ClassHour::where('teacher_id', $teacher->id)
            ->where('status', 'pending')
            ->whereMonth('link_updated_at', now()->month)
            ->whereYear('link_updated_at', now()->year)
            ->count();

        // Total Hours
        $totalMinutes = ClassHour::where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('duration');

        $totalHours = round($totalMinutes / 60, 2);

        // =========================
        // YEARLY EARNINGS
        // =========================

        // Yearly Earnings
        $yearlyEarnings = TeacherSalary::where('teacher_id', $teacher->id)
            ->whereYear('cycle_start', now()->year)
            ->sum('total_amount') ?? 0;

        // Salary history
        $salaries = TeacherSalary::where('teacher_id', $teacher->id)
            ->latest()
            ->take(12)
            ->get();

        // Latest class notes
        $notes = ClassNote::where('teacher_id', $teacher->id)
            ->latest()
            ->take(12)
            ->get();

        // This Month Notes Count
        $thisMonthNotesCount = ClassNote::where('teacher_id', $teacher->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        //Upcoming Salary (unprocessed completed class hours)
        $upcomingSalary = ClassHour::where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->where('has_salary_calculated', false)
            ->whereNotNull('duration')
            ->selectRaw('SUM((duration / 60) * hourly_wage) as total')
            ->value('total') ?? 0;

        // $pendingSalary = round($upcomingSalary, 2);
        //Pending Salary
        $pendingSalary = TeacherSalary::where('teacher_id', $teacher->id)
            ->where('status', 'unpaid')
            ->sum('total_amount');

        // Fetch active/upcoming holidays for the logged-in teacher
        $teacherClassIds = $teacher->classRooms->pluck('id');
        $holidays = \App\Models\Holiday::where(function ($q) use ($teacher, $teacherClassIds) {
            $q->where('target_type', 'all_teachers')
              ->orWhere(function ($q2) use ($teacher) {
                  $q2->where('target_type', 'selected_teachers')
                     ->whereHas('teachers', function ($q3) use ($teacher) {
                         $q3->where('teachers.id', $teacher->id);
                     });
              })
              ->orWhere(function ($q2) use ($teacherClassIds) {
                  $q2->where('target_type', 'classes')
                     ->whereIn('class_target_type', ['teachers', 'both'])
                     ->whereHas('classRooms', function ($q3) use ($teacherClassIds) {
                         $q3->whereIn('class_rooms.id', $teacherClassIds);
                     });
              });
        })->where('date', '>=', now()->toDateString())
          ->orderBy('date', 'asc')
          ->get();

        // Earnings Graph
        $monthlyData = ClassHour::where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->whereYear('completed_at', now()->year)
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->completed_at)->format('M');
            });

        $chartLabels = [];
        $classCounts = [];
        $earnings = [];

        foreach ($monthlyData as $month => $hours) {

            $chartLabels[] = $month;

            $classCounts[] = $hours->count();

            $total = 0;

            foreach ($hours as $hour) {

                if (!$hour->duration)
                    continue;

                $wage = $hour->hourly_wage ?? 0;

                $total += ($hour->duration / 60) * $wage;
            }

            $earnings[] = round($total, 2);
        }

        $rankData = teacherRankData($teacher->id);

        return view('teacher.dashboard', compact(
            'classes',
            'completedSessions',
            'pendingSessions',
            'totalHours',
            'thisMonthNotesCount',
            'yearlyEarnings',
            'upcomingSalary',
            'salaries',
            'notes',
            'pendingSalary',
            'chartLabels',
            'classCounts',
            'earnings',
            'rankData',
            'holidays'
        ));
    }

    public function profile()
    {
        $teacher = Auth::guard('teacher')->user();

        if (!$teacher) {
            return redirect()->route('teacher.login');
        }

        $classes = $teacher->classRooms()
            ->active()
            ->with(['course', 'classType'])
            ->get();

        return view('teacher.profile', compact('teacher', 'classes'));
    }

    public function updatePhoto(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();

        if (!$teacher) {
            return redirect()->route('teacher.login');
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
        ]);

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');

            // Delete old photo if exists
            if ($teacher->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($teacher->photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($teacher->photo);
            }

            if (!extension_loaded('gd')) {
                // Fallback: save directly if GD is not available
                $path = $photo->store('teachers/photos', 'public');
                $teacher->update(['photo' => $path]);
                return back()->with('success', 'Profile picture updated successfully (uncompressed).');
            }

            $mime = $photo->getMimeType();
            
            // Create image resource based on type
            if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
                $srcImage = @imagecreatefromjpeg($photo->getRealPath());
            } elseif ($mime === 'image/png') {
                $srcImage = @imagecreatefrompng($photo->getRealPath());
            } elseif ($mime === 'image/gif') {
                $srcImage = @imagecreatefromgif($photo->getRealPath());
            } elseif ($mime === 'image/webp') {
                $srcImage = @imagecreatefromwebp($photo->getRealPath());
            } else {
                $srcImage = false;
            }

            if (!$srcImage) {
                // Fallback if image loading fails
                $path = $photo->store('teachers/photos', 'public');
                $teacher->update(['photo' => $path]);
                return back()->with('success', 'Profile picture updated successfully (uncompressed).');
            }

            // Get original dimensions
            $width = imagesx($srcImage);
            $height = imagesy($srcImage);

            // Determine target dimensions (max 800px width/height)
            $maxDim = 800;
            if ($width > $maxDim || $height > $maxDim) {
                if ($width > $height) {
                    $newWidth = $maxDim;
                    $newHeight = (int)($height * ($maxDim / $width));
                } else {
                    $newHeight = $maxDim;
                    $newWidth = (int)($width * ($maxDim / $height));
                }
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }

            // Create target truecolor image and fill with white background (handles transparent png/gif)
            $dstImage = imagecreatetruecolor($newWidth, $newHeight);
            $white = imagecolorallocate($dstImage, 255, 255, 255);
            imagefill($dstImage, 0, 0, $white);

            // Copy and scale the original image
            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($srcImage);

            // Determine destination file name & path
            $filename = 'teachers/photos/' . uniqid() . '.jpg';
            $targetPath = storage_path('app/public/' . $filename);

            // Ensure destination directory exists
            $dir = dirname($targetPath);
            if (!file_exists($dir)) {
                @mkdir($dir, 0755, true);
            }

            // Save with progressive quality compression until file size is below 200KB
            $quality = 85;
            $maxSizeBytes = 200 * 1024;
            
            do {
                ob_start();
                imagejpeg($dstImage, null, $quality);
                $imageData = ob_get_clean();
                $fileSize = strlen($imageData);
                $quality -= 5;
            } while ($fileSize > $maxSizeBytes && $quality >= 30);

            file_put_contents($targetPath, $imageData);
            imagedestroy($dstImage);

            $teacher->update(['photo' => $filename]);

            return back()->with('success', 'Profile picture updated and compressed below 200KB successfully.');
        }

        return back()->with('error', 'Failed to update profile picture.');
    }
}
