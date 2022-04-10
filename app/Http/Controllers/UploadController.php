<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\UploadBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use App\Models\FileJobBatches;

class UploadController extends Controller
{

    public function index(){
        return view('home')->with('jobs', $this->getFileJobBatches());
    }

    // Sperate large file into chunks and upload
    public function store(Request $requst){
        $requst->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = file($requst->file->getRealPath());
        $data = array_slice($file, 1);
        $originalFileName = basename($requst->file->getClientOriginalName(), '.'.$requst->file->getClientOriginalExtension());
        
        $parts = (array_chunk($data, 100));

        foreach($parts as $index=>$part){
            $fileName = resource_path('temp-files/'.date('y-m-d-H-i-s').$index.'.csv');
            file_put_contents($fileName, $part);
        }
        return;
        $batchData = $this->processFile($originalFileName);
        return redirect()->back();
    }

    public function parseImport(CsvImportRequest $request)
    {

        $path = $request->file('csv_file')->getRealPath();

        if ($request->has('header')) {
            $data = Excel::load($path, function($reader) {})->get()->toArray();
        } else {
            $data = array_map('str_getcsv', file($path));
        }

        if (count($data) > 0) {
            if ($request->has('header')) {
                $csv_header_fields = [];
                foreach ($data[0] as $key => $value) {
                    $csv_header_fields[] = $key;
                }
            }
            $csv_data = array_slice($data, 0, 2);

            $csv_data_file = CsvData::create([
                'csv_filename' => $request->file('csv_file')->getClientOriginalName(),
                'csv_header' => $request->has('header'),
                'csv_data' => json_encode($data)
            ]);
        } else {
            return redirect()->back();
        }

        return view('import_fields', compact( 'csv_header_fields', 'csv_data', 'csv_data_file'));

    }

    // Process uploaded files
    public function processFile($originalFileName){
        $path = resource_path('temp-files/*.csv');
        $files = glob($path);
        $batch = Bus::batch([])
        ->finally(function (Batch $batch) {
            // To notify user via email or etc. project did not require.
        })
        ->dispatch();
        // Iterate each file int the $path
        foreach($files as $file){
            $data = array_map('str_getcsv', file($file));
            // Dispatch upload job
            $batch->add(new UploadBatch($data, $batch->id));
            // Remove temp sliced file 
            unlink($file);            
        }
        // Store file and batch data
        FileJobBatches::create([
            'file_name' => $originalFileName,
            'batch_id' => $batch->id
        ]);
        return $batch;
    }



    // Get File Batches jobs
    //TODO:: Add transformer here
    public function getFileJobBatches(){
        $fileJobBatches = FileJobBatches::join('job_batches','job_batches.id','=','file_job_batches.batch_id')->get();
        // return $fileJobBatches;

        // $transformedResponse = collection($fileJobBatches)
        $fileJobBatches->transform(function ($job, $key) {
            return [
                "FileName" => $job['file_name'],
                "BatchId" => $job['batch_id'],
                "CreatedAt" => $job['created_at'],
                "TotalJobs" => $job['total_jobs'],
                "PendingJobs" => $job['pending_jobs'],
            ];
        });
        return $fileJobBatches;
    }

}
