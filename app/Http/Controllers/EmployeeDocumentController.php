<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class EmployeeDocumentController extends Controller
{
    public function index(Request $request, $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        if ($request->ajax()) {
            $documents = EmployeeDocument::where('employee_id', $employeeId);

            return DataTables::of($documents)
                ->addIndexColumn()
                ->addColumn('document_name', function($row) {
                    return '<i class="fas ' . $row->file_icon . ' me-2 text-primary"></i>' . $row->document_name;
                })
                ->addColumn('category', function($row) {
                    $badges = [
                        'cin' => 'bg-info',
                        'cnss' => 'bg-success',
                        'contract' => 'bg-primary',
                        'diploma' => 'bg-warning',
                        'other' => 'bg-secondary'
                    ];
                    $badgeClass = $badges[$row->category] ?? 'bg-secondary';
                    return '<span class="badge ' . $badgeClass . '">' . strtoupper($row->category) . '</span>';
                })
                ->addColumn('file_size', function($row) {
                    return $row->formatted_file_size;
                })
                ->addColumn('uploaded_at', function($row) {
                    return $row->uploaded_at->format('d/m/Y H:i');
                })
                ->addColumn('confidentiel', function($row) {
                    return $row->is_confidentiel ?
                        '<span class="badge bg-danger">Oui</span>' :
                        '<span class="badge bg-success">Non</span>';
                })
                ->addColumn('action', function($row) {
                    $btn = '<div class="dropdown">';
                    $btn .= '<button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">';
                    $btn .= '<i class="fas fa-ellipsis-v"></i></button>';
                    $btn .= '<ul class="dropdown-menu">';
                    $btn .= '<li><a class="dropdown-item" href="' . route('employees.documents.download', $row->document_id) . '">';
                    $btn .= '<i class="fas fa-download me-2"></i>Télécharger</a></li>';
                    $btn .= '<li><a class="dropdown-item preview-doc" href="#" data-id="' . $row->document_id . '">';
                    $btn .= '<i class="fas fa-eye me-2"></i>Aperçu</a></li>';
                    $btn .= '<li><hr class="dropdown-divider"></li>';
                    $btn .= '<li><a class="dropdown-item text-danger delete-doc" href="#" data-id="' . $row->document_id . '">';
                    $btn .= '<i class="fas fa-trash me-2"></i>Supprimer</a></li>';
                    $btn .= '</ul></div>';
                    return $btn;
                })
                ->rawColumns(['document_name', 'category', 'confidentiel', 'action'])
                ->make(true);
        }

        return view('pages.employees.documents', compact('employee'));
    }

    public function upload(Request $request, $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        $request->validate([
            'documents.*' => 'required|file|max:10240', // Max 10MB per file
            'categories.*' => 'required|in:cin,cnss,contract,diploma,other',
            'descriptions.*' => 'nullable|string|max:255',
            'is_confidentiel.*' => 'nullable|boolean'
        ]);

        $uploadedFiles = [];

        try {
            foreach ($request->file('documents') as $key => $file) {
                $originalName = $file->getClientOriginalName();
                $fileSize = $file->getSize() / 1024; // Convert to KB
                $mimeType = $file->getMimeType();
                $extension = $file->getClientOriginalExtension();
                $category = $request->categories[$key] ?? 'other';
                $description = $request->descriptions[$key] ?? null;
                $isConfidentiel = isset($request->is_confidentiel[$key]) && $request->is_confidentiel[$key] == '1';

                // Generate unique filename
                $filename = 'emp_' . $employeeId . '_' . time() . '_' . Str::random(10) . '.' . $extension;

                // Store file
                $path = $file->storeAs('employees/documents/' . $employeeId, $filename, 'public');

                // Create document record
                $document = EmployeeDocument::create([
                    'employee_id' => $employeeId,
                    'document_name' => $originalName,
                    'document_type' => strtoupper($extension),
                    'file_path' => $path,
                    'category' => $category,
                    'description' => $description,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                    'is_confidentiel' => $isConfidentiel,
                    'uploaded_at' => now()
                ]);

                $uploadedFiles[] = $document->document_name;
            }

            return response()->json([
                'success' => true,
                'message' => count($uploadedFiles) . ' document(s) téléchargé(s) avec succès!',
                'files' => $uploadedFiles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download($documentId)
    {
        $document = EmployeeDocument::findOrFail($documentId);
        $employee = $document->employee;

        // Check permission if needed
        if ($document->is_confidentiel) {
            // Add permission check here
        }

        $filePath = storage_path('app/public/' . $document->file_path);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, $document->document_name);
    }

    public function preview($documentId)
    {
        $document = EmployeeDocument::findOrFail($documentId);
        $filePath = storage_path('app/public/' . $document->file_path);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $content = file_get_contents($filePath);
        $base64 = base64_encode($content);

        return response()->json([
            'success' => true,
            'name' => $document->document_name,
            'type' => $document->mime_type,
            'content' => $base64
        ]);
    }

    public function destroy($documentId)
    {
        try {
            $document = EmployeeDocument::findOrFail($documentId);

            // Delete file
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Delete record
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }
}
