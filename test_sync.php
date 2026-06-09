<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$task = \App\Models\Task::create([
    'title' => 'Test Google Sync',
    'status' => 'pending',
    'priority' => 'high',
    'assigned_by' => 1,
    'assigned_to' => 1,
    'department_id' => 1
]);
echo "Created task {$task->id}\n";

$service = new \App\Services\GoogleSheetsService();
$ref = new ReflectionClass($service);
$prop = $ref->getProperty('service');
$prop->setAccessible(true);
$api = $prop->getValue($service);
$prop2 = $ref->getProperty('spreadsheetId');
$prop2->setAccessible(true);
$id = $prop2->getValue($service);

$spreadsheet = $api->spreadsheets->get($id);
$sheets = $spreadsheet->getSheets();
$sheetTitle = $sheets[0]->getProperties()->getTitle();
echo "First sheet title: " . $sheetTitle . "\n";


\App\Jobs\SyncTaskToGoogleSheet::dispatchSync($task);
$task->refresh();
echo "Row ID after sync: " . $task->google_sheet_row_id . "\n";

$task->update(['status' => 'in_progress']);
\App\Jobs\SyncTaskToGoogleSheet::dispatchSync($task);
$task->refresh();
echo "Row ID after update sync: " . $task->google_sheet_row_id . "\n";
