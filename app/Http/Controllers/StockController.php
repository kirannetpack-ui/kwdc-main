<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StockController extends Controller
{
  
    public function index()
    {
        $stocks = Stock::where('client_code', auth()->user()->user_code)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('stock.index', compact('stocks'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('status', 'approved')->get();
        return view('stock.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'number_of_boxes' => 'required|integer|min:1',
            'quantity_per_box' => 'required|integer|min:1',
            'invoice_number' => 'nullable|string|max:100',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:manufacturing_date',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'grn_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'quality_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'other_documents' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $client = auth()->user();
        $totalQuantity = $request->number_of_boxes * $request->quantity_per_box;
        
        $batchId = Stock::generateBatchId($client->user_code);
        $sku = Stock::generateSKU();
        
        $invoicePath = $request->hasFile('invoice_file')
            ? $request->file('invoice_file')->store('stock-documents/invoices', 'private_uploads')
            : null;
        $grnPath = $request->hasFile('grn_file')
            ? $request->file('grn_file')->store('stock-documents/grns', 'private_uploads')
            : null;
        $qualityCertificatePath = $request->hasFile('quality_certificate')
            ? $request->file('quality_certificate')->store('stock-documents/quality-certificates', 'private_uploads')
            : null;
        $otherDocumentPath = $request->hasFile('other_documents')
            ? $request->file('other_documents')->store('stock-documents/others', 'private_uploads')
            : null;
        
        $warehouseName = null;
        if ($request->warehouse_id) {
            $warehouse = Warehouse::find($request->warehouse_id);
            $warehouseName = $warehouse->name ?? null;
        }
        
        // Prepare QR Code Data
        $qrData = [
            'batch_id' => $batchId,
            'sku' => $sku,
            'product_name' => $request->product_name,
            'number_of_boxes' => $request->number_of_boxes,
            'quantity_per_box' => $request->quantity_per_box,
            'total_quantity' => $totalQuantity,
            'unit' => $request->unit,
            'invoice_number' => $request->invoice_number,
            'warehouse_id' => $request->warehouse_id,
            'warehouse_name' => $warehouseName,
            'client_id' => $client->user_code,
            'client_name' => $client->name,
            'client_email' => $client->email,
            'manufacturing_date' => $request->manufacturing_date,
            'expiry_date' => $request->expiry_date,
            'received_date' => now()->format('Y-m-d'),
            'received_by' => $client->name,
            'status' => 'in_stock'
        ];
        
        // Generate QR Code using SVG format (no Imagick needed)
        $qrCodeFileName = $batchId . '.svg';
        $qrCodePath = 'qr_codes/' . $qrCodeFileName;
        
        $qrCode = QrCode::format('svg')
            ->size(400)
            ->errorCorrection('H')
            ->margin(2)
            ->generate(json_encode($qrData));
        
        Storage::disk('public')->put($qrCodePath, $qrCode);
        
        // Create stock record
        $stock = Stock::create([
            'product_name' => $request->product_name,
            'user_id' => $client->id,
            'description' => $request->description,
            'unit' => $request->unit,
            'number_of_boxes' => $request->number_of_boxes,
            'quantity_per_box' => $request->quantity_per_box,
            'total_quantity' => $totalQuantity,
            'batch_id' => $batchId,
            'sku' => $sku,
            'invoice_number' => $request->invoice_number,
            'invoice_file_path' => $invoicePath,
            'grn_file_path' => $grnPath,
            'quality_certificate_path' => $qualityCertificatePath,
            'other_documents_path' => $otherDocumentPath,
            'warehouse_id' => $request->warehouse_id,
            'warehouse_name' => $warehouseName,
            'client_code' => $client->user_code,
            'client_name' => $client->name,
            'manufacturing_date' => $request->manufacturing_date,
            'expiry_date' => $request->expiry_date,
            'received_date' => now(),
            'qr_code_path' => $qrCodePath,
            'qr_code_data' => json_encode($qrData),
            'status' => 'in_stock',
            'remaining_quantity' => $totalQuantity,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
        ]);
        
        return redirect()
            ->route('stock.show', $stock->id)
            ->with('success', 'Stock added successfully! Batch ID: ' . $batchId);
    }

    public function show($id)
    {
        $stock = Stock::where('id', $id)
            ->where('client_code', auth()->user()->user_code)
            ->firstOrFail();
        
        return view('stock.show', compact('stock'));
    }
    
    public function viewQrCode($id)
    {
        $stock = Stock::where('id', $id)
            ->where('client_code', auth()->user()->user_code)
            ->firstOrFail();
        
        if (Storage::disk('public')->exists($stock->qr_code_path)) {
            $file = Storage::disk('public')->path($stock->qr_code_path);
            $mimeType = Storage::disk('public')->mimeType($stock->qr_code_path);
            return response()->file($file, ['Content-Type' => $mimeType]);
        }
        
        return redirect()->back()->with('error', 'QR Code not found.');
    }
    
    public function downloadQrCode($id)
    {
        $stock = Stock::where('id', $id)
            ->where('client_code', auth()->user()->user_code)
            ->firstOrFail();
        
        if (Storage::disk('public')->exists($stock->qr_code_path)) {
            $extension = pathinfo($stock->qr_code_path, PATHINFO_EXTENSION);
            $filename = $stock->batch_id . '.' . $extension;
            return Storage::disk('public')->download($stock->qr_code_path, $filename);
        }
        
        return redirect()->back()->with('error', 'QR Code not found.');
    }
    
    public function downloadDocument($id, $type)
    {
        $stock = Stock::where('id', $id)
            ->where('client_code', auth()->user()->user_code)
            ->firstOrFail();
        
        $path = null;
        if ($type === 'invoice') {
            $path = $stock->invoice_file_path;
        } elseif ($type === 'grn') {
            $path = $stock->grn_file_path;
        } elseif ($type === 'certificate') {
            $path = $stock->quality_certificate_path;
        } elseif ($type === 'other') {
            $path = $stock->other_documents_path;
        }
        
        if ($path && Storage::disk('private_uploads')->exists($path)) {
            return Storage::disk('private_uploads')->download($path);
        }
        
        return redirect()->back()->with('error', 'Document not found.');
    }
}
