<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Badge;

class BadgeController extends Controller
{
    public function eligibility($badgeId)
    {
        $userId = Auth::id();
        $badge = Badge::find($badgeId);

        if (!$badge) {
            return response()->json(['eligible' => false, 'reason' => 'Badge tidak ditemukan.']);
        }

        // PERUBAHAN: Badge 4, 5, 6 adalah badge Kelompok SCI (Otomatis)
        if (in_array($badgeId, [4, 5, 6])) {
            return response()->json(['eligible' => false, 'reason' => 'Badge ini diberikan otomatis oleh sistem (Group SCI).']);
        }

        // Ambil semua hasil aktivitas MANDIRI
        $userResults = DB::table('activity_result')
            ->join('activities', 'activity_result.id_activity', '=', 'activities.id')
            ->join('topics', 'activities.id_topic', '=', 'topics.id')
            ->join('subject', 'topics.id_subject', '=', 'subject.id')
            ->join('classes', 'subject.id_class', '=', 'classes.id')
            ->where('activity_result.id_user', $userId)
            ->where('activities.is_group_activity', 'no')
            ->select(
                'activity_result.id_activity',
                'activity_result.nilai_akhir',
                'activity_result.result',
                'activity_result.created_at',
                'activities.title as activity_title',
                'activities.kkm',
                'classes.id as class_id',
                'classes.name as class_name'
            )
            ->get();

        $matches = [];

        foreach ($userResults as $res) {
            $actId = $res->id_activity;
            $classId = $res->class_id;
            $score = $res->nilai_akhir ?? $res->result ?? 0;
            $isEligible = false;

            // ===============================================
            // 🏅 Logika Badge 1: Fastest (Dulu ID 4)
            // ===============================================
            if ($badgeId == 1) {
                $fastest = DB::table('activity_result')
                    ->join('users', 'activity_result.id_user', '=', 'users.id')
                    ->join('student_classes', 'users.id', '=', 'student_classes.id_student')
                    ->where('activity_result.id_activity', $actId)
                    ->where('student_classes.id_class', $classId)
                    ->orderBy('activity_result.created_at', 'asc')
                    ->first();
                    
                if ($fastest && $fastest->id_user == $userId) {
                    $isEligible = true;
                }
            }
            
            // ===============================================
            // 🏅 Logika Badge 2: Top 3 (Dulu ID 5)
            // ===============================================
            if ($badgeId == 2) {
                $top3 = DB::table('activity_result')
                    ->join('student_classes', 'activity_result.id_user', '=', 'student_classes.id_student')
                    ->where('activity_result.id_activity', $actId)
                    ->where('student_classes.id_class', $classId)
                    ->select('activity_result.id_user', DB::raw('COALESCE(activity_result.nilai_akhir, activity_result.result) as final_score'))
                    ->orderByDesc('final_score')
                    ->orderBy('activity_result.created_at', 'asc')
                    ->limit(3)
                    ->pluck('id_user')
                    ->toArray();
                    
                if (in_array($userId, $top3)) {
                    $isEligible = true;
                }
            }

            // ===============================================
            // 🏅 Logika Badge 3: Smartest (Dulu ID 6)
            // ===============================================
            if ($badgeId == 3) {
                $kkm = $res->kkm ?? 70; 
                if ($score >= 100 || $score >= $kkm) {
                    $isEligible = true;
                }
            }

            if ($isEligible) {
                $alreadyClaimed = DB::table('user_badge')
                    ->where('id_student', $userId)
                    ->where('id_badge', $badgeId)
                    ->where('id_activity', $actId)
                    ->exists();

                $matches[] = [
                    'class_id' => $classId,
                    'class_name' => $res->class_name,
                    'activity_id' => $actId,
                    'activity_title' => $res->activity_title,
                    'already_claimed' => $alreadyClaimed
                ];
            }
        }

        if (count($matches) > 0) {
            return response()->json(['eligible' => true, 'matches' => $matches]);
        }

        return response()->json(['eligible' => false, 'reason' => 'Belum ada aktivitas mandiri yang memenuhi syarat badge ini.']);
    }

    public function claim(Request $request)
    {
        $request->validate([
            'badge_id' => 'required|integer',
            'class_id' => 'required|integer',
            'activity_id' => 'required|integer' 
        ]);

        $userId = Auth::id();
        $badgeId = $request->badge_id;
        $classId = $request->class_id;
        $actId = $request->activity_id;

        // PERUBAHAN: Badge Kelompok tidak bisa diklaim manual
        if (in_array($badgeId, [4, 5, 6])) {
            return response()->json(['success' => false, 'message' => 'Gagal: Badge kelompok tidak bisa diklaim secara manual!']);
        }

        $exists = DB::table('user_badge')
            ->where('id_student', $userId)
            ->where('id_badge', $badgeId)
            ->where('id_activity', $actId)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Badge ini sudah kamu klaim untuk aktivitas tersebut.']);
        }

        DB::table('user_badge')->insert([
            'id_student' => $userId,
            'id_badge' => $badgeId,
            'id_activity' => $actId,
            'id_class' => $classId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $badge = Badge::find($badgeId);

        return response()->json([
            'success' => true,
            'message' => 'Selamat! Badge ' . $badge->name . ' berhasil diklaim.',
            'badge' => $badge
        ]);
    }
}