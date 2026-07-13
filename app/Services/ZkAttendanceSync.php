<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ZkPunch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ZkAttendanceSync
{
    /**
     * Ingest raw punches read from the ZKTeco device and rebuild the
     * affected employees' Attendance rows for the affected days.
     *
     * @param array<int, array{zk_uid: string, timestamp: string}> $punches
     * @return int Number of new punches recorded (device logs are re-read in full each run, so this excludes already-known punches)
     */
    public function ingestPunches(array $punches): int
    {
        $affected = [];
        $newCount = 0;

        foreach ($punches as $punch) {
            $employee = Employee::where('zk_uid', $punch['zk_uid'])->first();

            if (!$employee) {
                Log::warning('ZKTeco punch for unmapped device user', ['zk_uid' => $punch['zk_uid']]);
                continue;
            }

            $timestamp = Carbon::parse($punch['timestamp']);

            $zkPunch = ZkPunch::firstOrCreate([
                'employee_id' => $employee->employee_id,
                'timestamp' => $timestamp,
            ]);

            if ($zkPunch->wasRecentlyCreated) {
                $newCount++;
            }

            $affected[$employee->employee_id][$timestamp->toDateString()] = true;
        }

        foreach ($affected as $employeeId => $dates) {
            foreach (array_keys($dates) as $date) {
                $this->rebuildAttendanceForDay($employeeId, $date);
            }
        }

        return $newCount;
    }

    private function rebuildAttendanceForDay(int $employeeId, string $date): void
    {
        $timestamps = ZkPunch::where('employee_id', $employeeId)
            ->whereDate('timestamp', $date)
            ->orderBy('timestamp')
            ->pluck('timestamp');

        $timeEntries = [];
        for ($i = 0; $i < $timestamps->count(); $i += 2) {
            $timeEntries[] = [
                'check_in' => $timestamps[$i]->format('H:i'),
                'check_out' => isset($timestamps[$i + 1]) ? $timestamps[$i + 1]->format('H:i') : null,
            ];
        }

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employeeId,
            'date' => $date,
        ]);

        $isNew = !$attendance->exists;

        $attendance->time_entries = $timeEntries;
        if ($isNew) {
            $attendance->status = 'present';
        }
        $attendance->save();

        $attendance->hours_worked = max(0, $attendance->calculateTotalHours());
        $attendance->save();
    }
}
