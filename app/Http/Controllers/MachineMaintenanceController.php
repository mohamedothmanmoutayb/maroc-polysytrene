<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineMaintenanceSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class MachineMaintenanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_machine_maintenance')->only(['history', 'print', 'printAll']);
        $this->middleware('can:create_machine_maintenance')->only(['store']);
        $this->middleware('can:edit_machine_maintenance')->only(['update']);
        $this->middleware('can:delete_machine_maintenance')->only(['destroy']);
        $this->middleware('can:complete_machine_maintenance')->only(['complete']);
    }

    /**
     * Store a new maintenance schedule for a machine.
     */
    public function store(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,machine_id',
            'label' => 'required|string|max:150',
            'description' => 'nullable|string',
            'interval_days' => 'required|integer|min:1',
            'reminder_days_before' => 'nullable|integer|min:0',
            'next_due_at' => 'required|date',
        ]);

        try {
            $schedule = MachineMaintenanceSchedule::create([
                'machine_id' => $request->machine_id,
                'label' => $request->label,
                'description' => $request->description,
                'interval_days' => $request->interval_days,
                'reminder_days_before' => $request->reminder_days_before ?? 7,
                'next_due_at' => $request->next_due_at,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Programme de maintenance créé avec succès!',
                'data' => $schedule,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a maintenance schedule.
     */
    public function update(Request $request, $id)
    {
        $schedule = MachineMaintenanceSchedule::findOrFail($id);

        $request->validate([
            'label' => 'required|string|max:150',
            'description' => 'nullable|string',
            'interval_days' => 'required|integer|min:1',
            'reminder_days_before' => 'nullable|integer|min:0',
            'next_due_at' => 'required|date',
            'is_active' => 'boolean',
        ]);

        try {
            $schedule->update([
                'label' => $request->label,
                'description' => $request->description,
                'interval_days' => $request->interval_days,
                'reminder_days_before' => $request->reminder_days_before ?? 7,
                'next_due_at' => $request->next_due_at,
                'is_active' => $request->has('is_active'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Programme de maintenance mis à jour avec succès!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a maintenance schedule as completed (the "approve" step) — logs the
     * completion and restarts the cycle from today + interval_days.
     */
    public function complete(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $schedule = MachineMaintenanceSchedule::findOrFail($id);
            $schedule->complete($request->notes, auth()->id());
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Maintenance confirmée — prochaine échéance le ' . $schedule->fresh()->next_due_at->format('d/m/Y'),
                'data' => [
                    'next_due_at' => $schedule->fresh()->next_due_at->format('Y-m-d'),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Completion history for a schedule.
     */
    public function history($id)
    {
        try {
            $schedule = MachineMaintenanceSchedule::findOrFail($id);

            $completions = $schedule->completions()
                ->with('completer:id,username')
                ->get()
                ->map(function ($completion) {
                    return [
                        'completed_at' => $completion->completed_at->format('d/m/Y'),
                        'previous_due_at' => $completion->previous_due_at ? $completion->previous_due_at->format('d/m/Y') : '-',
                        'next_due_at' => $completion->next_due_at->format('d/m/Y'),
                        'notes' => $completion->notes,
                        'completed_by' => $completion->completer->username ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'label' => $schedule->label,
                'data' => $completions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Programme de maintenance non trouvé'
            ], 404);
        }
    }

    /**
     * Remove a maintenance schedule.
     */
    public function destroy($id)
    {
        try {
            $schedule = MachineMaintenanceSchedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Programme de maintenance supprimé avec succès!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print the preventive maintenance schedule for a single machine.
     */
    public function print($machineId)
    {
        $machine = Machine::with(['maintenanceSchedules' => function ($query) {
            $query->active();
        }])->findOrFail($machineId);

        $pdf = Pdf::loadView('pdf.machine-maintenance', [
            'machines' => collect([$machine]),
            'title' => 'Maintenance Préventive — ' . $machine->name,
            'date' => now()->format('d/m/Y'),
        ]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('maintenance-' . \Illuminate\Support\Str::slug($machine->name) . '.pdf');
    }

    /**
     * Print the preventive maintenance schedule for every machine.
     */
    public function printAll()
    {
        $machines = Machine::with(['maintenanceSchedules' => function ($query) {
            $query->active();
        }])
            ->whereHas('maintenanceSchedules', function ($query) {
                $query->active();
            })
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('pdf.machine-maintenance', [
            'machines' => $machines,
            'title' => 'Maintenance Préventive — Toutes les Machines',
            'date' => now()->format('d/m/Y'),
        ]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('maintenance-machines.pdf');
    }
}
