<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\CustomerInteraction;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CustomerCrmService
{
    public function syncCustomerForTask(Task $task, User $user): ?Customer
    {
        if (!$task->client_name && !$task->client_contact && !$task->customer_id) {
            return null;
        }

        return DB::transaction(function () use ($task, $user) {
            $customer = $task->customer;

            if (!$customer) {
                $customer = Customer::query()
                    ->when($task->client_contact, fn ($q) => $q->where('mobile', $task->client_contact))
                    ->when(!$task->client_contact && $task->client_name, fn ($q) => $q->where('company_name', $task->client_name))
                    ->first();
            }

            $data = [
                'customer_type' => $customer?->customer_type ?? 'B2C',
                'company_name' => $task->client_name ?: $customer?->company_name,
                'contact_person' => $task->client_name ?: $customer?->contact_person,
                'mobile' => $task->client_contact ?: $customer?->mobile,
                'status' => $customer?->status ?? 'Active',
                'created_by' => $customer?->created_by ?? $user->id,
            ];

            if ($customer) {
                $customer->update($data);
            } else {
                $customer = Customer::create($data);
            }

            $task->updateQuietly(['customer_id' => $customer->id]);
            $task->booking?->update(['customer_id' => $customer->id]);

            return $customer;
        });
    }

    public function recordInteraction(Customer $customer, array $data, User $user): CustomerInteraction
    {
        return DB::transaction(function () use ($customer, $data, $user) {
            $interaction = $customer->interactions()->create([
                'task_id' => $data['task_id'] ?? null,
                'interaction_type' => $data['interaction_type'],
                'interaction_date' => $data['interaction_date'] ?? now(),
                'notes' => $data['notes'] ?? null,
                'next_followup_date' => $data['next_followup_date'] ?? null,
                'created_by' => $user->id,
            ]);

            activity()
                ->performedOn($customer)
                ->causedBy($user)
                ->withProperties(array_filter([
                    'interaction_id' => $interaction->id,
                    'task_id' => $interaction->task_id,
                ], fn ($value) => $value !== null))
                ->log('Customer interaction recorded');

            return $interaction;
        });
    }

    public function storeDocument(Customer $customer, UploadedFile $file, array $data, User $user): CustomerDocument
    {
        $path = $file->store('customer-documents/' . $customer->customer_code, 'public');

        return $customer->documents()->create([
            'task_id' => $data['task_id'] ?? null,
            'document_type' => $data['document_type'],
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'remarks' => $data['remarks'] ?? null,
            'uploaded_by' => $user->id,
        ]);
    }
}
