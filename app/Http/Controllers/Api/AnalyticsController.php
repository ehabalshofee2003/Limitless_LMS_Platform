<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Cohort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * اللوحة الرئيسية للمشرف
     */
    public function dashboard()
    {
        // 1. الإحصائيات المالية
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $monthlyRevenue = Payment::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        // 2. إحصائيات المستخدمين
        $totalStudents = User::role('student')->count();
        $totalInstructors = User::role('instructor')->count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)->count();

        // 3. إحصائيات المحتوى
        $totalCourses = Course::count();
        $activeCohorts = Cohort::where('end_date', '>', now())->count();
        $pendingCourses = Course::where('status', 'pending')->count();

        return response()->json([
            'kpis' => [
                'total_revenue' => $totalRevenue,
                'monthly_revenue' => $monthlyRevenue,
                'total_students' => $totalStudents,
                'total_instructors' => $totalInstructors,
            ],
            'growth' => [
                'new_users_monthly' => $newUsersThisMonth,
            ],
            'content' => [
                'total_courses' => $totalCourses,
                'active_cohorts' => $activeCohorts,
                'pending_approvals' => $pendingCourses,
            ]
        ]);
    }

    /**
     * تحليل الإيرادات (للرسوم البيانية)
     */
    public function revenue()
    {
        // جلب الإيرادات آخر 6 أشهر
        $revenue = Payment::where('status', 'completed')
            ->whereYear('created_at', now()->year) // السنة الحالية
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // تنسيق البيانات لتكون جاهزة للـ Frontend Charts
        $chartData = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        foreach ($revenue as $row) {
            $chartData[] = [
                'month' => $months[$row->month - 1],
                'revenue' => $row->total
            ];
        }

        return response()->json([
            'chart_data' => $chartData
        ]);
    }

    /**
     * تحليل الطلاب
     */
    public function users()
    {
        // أكثر الطلاب نشاطاً (بناءً على عدد الدورات المسجل بها)
        $topStudents = User::role('student')
            ->withCount('cohorts')
            ->orderBy('cohorts_count', 'desc')
            ->take(5)
            ->get(['id', 'name', 'email']);

        return response()->json([
            'top_students' => $topStudents,
        ]);
    }
    
    /**
     * تحليل الدورات (أفضل الدورات مبيعاً)
     */
    public function courses()
    {
        $topCourses = Course::withCount('cohorts')
            ->withSum('cohorts as total_sales', function($query) {
                // هذا افتراضي، الأصح ربطه بالمدفوعات ولكن سنحتسبها هنا كعدد مسجلين
                $query->whereIn('status', ['published']); 
            })
            ->orderBy('cohorts_count', 'desc')
            ->take(5)
            ->get(['id', 'title']);

        return response()->json([
            'top_courses' => $topCourses
        ]);
    }
}