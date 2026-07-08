<?php

namespace App\Providers;

use App\Models\Message;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\Models\ClassHour;
use App\Models\Teacher;
use App\Models\ClassRoom;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS on production so PWA / service worker works correctly
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Global rate limiter (100 requests per minute)
        RateLimiter::for('global', function (Request $request) {
            return app()->runningUnitTests() ? null : Limit::perMinute(100)->by($request->ip());
        });

        // Login rate limiter: limit to 5 login attempts per minute per IP + username/phone combo
        RateLimiter::for('login', function (Request $request) {
            if (app()->runningUnitTests()) {
                return null;
            }
            $phone = $request->input('phone') ?? '';
            $country = $request->input('country_id') ?? '';
            $key = 'login|' . $country . '|' . $phone . '|' . $request->ip();
            return Limit::perMinute(5)->by($key)->response(function (Request $request, array $headers) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Too many login attempts. Please try again in ' . $headers['Retry-After'] . ' seconds.'], 429);
                }
                return back()->with('error', 'Too many login attempts. Please try again in ' . $headers['Retry-After'] . ' seconds.')->withInput($request->except('password'));
            });
        });

        // Admission rate limiter: limit to 10 form submissions per hour per IP
        RateLimiter::for('admission', function (Request $request) {
            return app()->runningUnitTests() ? null : Limit::perHour(10)->by($request->ip());
        });

        // Student right-sidebar: pass upcoming class hours
        View::composer(['student.layouts.right-sidebar-messages', 'student.layouts.right-sidebar-sessions', 'student.layouts.topbar', 'student.layouts.horizontal'], function ($view) {
            $classHours = collect();
            $studentMessagesUnreadCount = 0;
            if (Auth::guard('student')->check()) {
                $student = Auth::guard('student')->user();
                $classRoomIds = $student->class_rooms()->pluck('class_rooms.id');
                $classHours = ClassHour::whereIn('class_room_id', $classRoomIds)
                    ->where('status', 'pending')
                    ->with('classRoom')
                    ->latest()
                    ->take(15)
                    ->get();

                $query = Message::where(function ($q) use ($student, $classRoomIds) {
                    // Received directly by the student
                    $q->where(function ($q2) use ($student) {
                        $q2->where('receiver_type', 'App\Models\Student')
                            ->where('receiver_id', $student->id);
                    })
                        // Or received by their classrooms
                        ->orWhere(function ($q2) use ($classRoomIds) {
                            $q2->where('receiver_type', 'App\Models\ClassRoom')
                                ->whereIn('receiver_id', $classRoomIds);
                        });
                });

                $studentMessages = (clone $query)->with('sender')->latest()->take(10)->get();
                $studentMessagesUnreadCount = (clone $query)->where('is_read', false)
                    ->whereNot(function ($q) use ($student) {
                        $q->where('sender_type', 'App\Models\Student')
                            ->where('sender_id', $student->id);
                    })
                    ->count();
            }
            $view->with('classHours', $classHours);
            $view->with('pendingClassHoursCount', $classHours->count());
            $view->with('studentMessages', $studentMessages);
            $view->with('studentMessagesUnreadCount', $studentMessagesUnreadCount);
        });

        View::composer(['teacher.layouts.right-sidebar-messages', 'teacher.layouts.right-sidebar-sessions', 'teacher.layouts.topbar', 'teacher.layouts.horizontal'], function ($view) {
            $teacherMessages = collect();
            $teacherMessagesCount = 0;
            $teacherMessagesUnreadCount = 0;
            $teacherClassHours = collect();
            
            if (Auth::guard('teacher')->check()) {
                $teacher = Auth::guard('teacher')->user();
                $classRoomIds = $teacher->classRooms()->pluck('class_rooms.id');
                
                // Fetch pending class sessions for teacher
                $teacherClassHours = ClassHour::whereIn('class_room_id', $classRoomIds)
                    ->where('status', 'pending')
                    ->with('classRoom')
                    ->latest()
                    ->take(15)
                    ->get();

                $query = Message::where(function ($q) use ($teacher) {
                    $q->where('receiver_type', Teacher::class)
                        ->where('receiver_id', $teacher->id);
                })
                    ->orWhere(function ($q) use ($teacher, $classRoomIds) {
                        $q->where('receiver_type', ClassRoom::class)
                            ->whereIn('receiver_id', $classRoomIds);
                    });

                $teacherMessages = (clone $query)->with('sender')->latest()->take(10)->get();
                $teacherMessagesCount = (clone $query)->count();
                $teacherMessagesUnreadCount = Message::where('is_read', false)
                    ->where(function ($q) use ($teacher, $classRoomIds) {
                        // Received directly by the teacher
                        $q->where(function ($q2) use ($teacher) {
                            $q2->where('receiver_type', 'App\Models\Teacher')
                                ->where('receiver_id', $teacher->id);
                        })
                            // Or received by their classrooms
                            ->orWhere(function ($q2) use ($classRoomIds) {
                            $q2->where('receiver_type', 'App\Models\ClassRoom')
                                ->whereIn('receiver_id', $classRoomIds);
                        });
                    })
                    ->where(function ($q) use ($teacher) {
                        // Ensure the message was NOT sent by the current teacher
                        $q->where('sender_type', '!=', 'App\Models\Teacher')
                            ->orWhere('sender_id', '!=', $teacher->id);
                    })
                    ->count();
            }
            $view->with('teacherMessages', $teacherMessages);
            $view->with('teacherMessagesCount', $teacherMessagesCount);
            $view->with('teacherMessagesUnreadCount', $teacherMessagesUnreadCount);
            $view->with('teacherClassHours', $teacherClassHours);
            $view->with('pendingTeacherClassHoursCount', $teacherClassHours->count());
        });

        Blade::if('role', function ($roles) {

            if (!Auth::guard('staff')->check()) {
                return false;
            }

            $staff = Auth::guard('staff')->user();

            // Operation department sees everything
            if ($staff->hasRole('operation')) {
                return true;
            }

            return $staff->hasRole($roles);
        });
    }
}
