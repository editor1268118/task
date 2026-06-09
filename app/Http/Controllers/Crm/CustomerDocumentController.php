<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Services\CustomerCrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerDocumentController extends Controller
{
    public function __construct(protected CustomerCrmService $customerCrmService)
    {
    }

    public function store(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $data = $request->validate([
            'task_id' => 'nullable|exists:tasks,id',
            'document_type' => 'required|in:' . implode(',', CustomerDocument::TYPES),
            'document' => 'required|file|max:10240',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $this->customerCrmService->storeDocument($customer, $request->file('document'), $data, Auth::user());

        return back()->with('success', 'Customer document uploaded.');
    }

    public function download(CustomerDocument $document)
    {
        $this->authorize('view', $document->customer);

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}
