<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Cohort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache; // <--- 1. استدعاء Cache


class AnalyticsController extends Controller
{
    /**
     * اللوحة الرئيسية للمشرف
     */
 public function dashboard()
    {
        // تخزين الإحصائيات لمدة 10 دقائق (600 ثانية)
        $stats = Cache::remember('admin.dashboard.stats', 600, function () {
            return [
                'total_students' => User::role('student')->count(),
                'total_instructors' => User::role('institution')->count(),
                'total_courses' => Course::count(),
                'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            ];
        });

        return response()->json($stats);
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