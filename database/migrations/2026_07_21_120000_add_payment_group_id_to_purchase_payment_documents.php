<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * A payment spread over several purchases is one payment, not one per purchase.
 * `payment_group_id` ties the rows of a single distribution together so the UI
 * can show "6 000 DH payés sur 10 achats" and edit/delete it as one payment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_payment_documents', function (Blueprint $table) {
            $table->string('payment_group_id', 64)->nullable()->after('document_number');
            $table->index('payment_group_id');
        });

        $this->backfillExistingDistributions();
    }

    public function down(): void
    {
        Schema::table('purchase_payment_documents', function (Blueprint $table) {
            $table->dropIndex(['payment_group_id']);
            $table->dropColumn('payment_group_id');
        });
    }

    /**
     * Old distributions created one document per purchase in the same request,
     * so they share supplier, method, payment date and creation second. Stitch
     * those back into a single payment.
     */
    private function backfillExistingDistributions(): void
    {
        $docs = DB::table('purchase_payment_documents as d')
            ->join('raw_material_purchases as p', 'p.purchase_id', '=', 'd.purchase_id')
            ->select('d.document_id', 'd.purchase_id', 'd.payment_method', 'd.payment_date',
                'd.created_at', 'd.uploaded_by', 'p.supplier_id')
            ->orderBy('d.document_id')
            ->get();

        $groups = [];
        foreach ($docs as $doc) {
            $key = implode('|', [
                $doc->supplier_id,
                $doc->payment_method,
                $doc->payment_date,
                // second-level precision: rows of one distribution are written together
                $doc->created_at ? substr((string) $doc->created_at, 0, 19) : 'null',
                $doc->uploaded_by,
            ]);
            $groups[$key][] = $doc;
        }

        foreach ($groups as $group) {
            if (count($group) < 2) {
                continue;
            }

            // Only a real distribution spans several purchases; several documents
            // on the same purchase are separate payments.
            $purchaseIds = array_unique(array_column($group, 'purchase_id'));
            if (count($purchaseIds) < 2) {
                continue;
            }

            DB::table('purchase_payment_documents')
                ->whereIn('document_id', array_column($group, 'document_id'))
                ->update(['payment_group_id' => (string) Str::uuid()]);
        }
    }
};
