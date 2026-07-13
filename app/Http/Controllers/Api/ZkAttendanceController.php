<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ZkAttendanceSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZkAttendanceController extends Controller
{
    /**
     * Ingest a batch of raw punches pushed by the office-LAN agent.
     */
    public function storePunches(Request $request, ZkAttendanceSync $sync)
    {
        Log::info('Received punches from ZK agent', ['punches' => $request->input('punches')]);
        $validated = $request->validate([
            'punches' => 'required|array',
            'punches.*.zk_uid' => 'required|string',
            'punches.*.timestamp' => 'required|date',
        ]);

        $newCount = $sync->ingestPunches($validated['punches']);

        return response()->json([
            'success' => true,
            'received' => count($validated['punches']),
            'new' => $newCount,
        ]);
    }
}
