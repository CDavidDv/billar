<?php

namespace App\Http\Controllers\Admin;

use App\Exports\InventoryExport;
use App\Exports\SalesExport;
use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Import');
    }

    public function importProducts(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:5120',
        ]);

        $import = new ProductsImport;
        Excel::import($import, $request->file('file'));

        $failures = $import->failures();

        if ($failures->isNotEmpty()) {
            $errors = $failures->map(fn ($f) => [
                'row' => $f->row(),
                'attribute' => $f->attribute(),
                'errors' => $f->errors(),
                'values' => $f->values(),
            ])->toArray();

            return back()->with('import_errors', $errors)->with('import_success', 0);
        }

        return back()->with('success', 'Importación completada correctamente.');
    }

    public function exportSales(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $filename = 'ventas_'.$request->from.'_'.$request->to.'.xlsx';

        return Excel::download(new SalesExport($request->from, $request->to), $filename);
    }

    public function exportInventory()
    {
        return Excel::download(new InventoryExport, 'inventario_'.now()->format('Y-m-d').'.xlsx');
    }
}
