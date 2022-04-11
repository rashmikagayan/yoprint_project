<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\UploadBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use App\Models\FileJobBatches;
use App\Http\Controllers\CSVParser;
use App\Events\ActionEvent;


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
        $originalFileName = basename($requst->file->getClientOriginalName(), '.'.$requst->file->getClientOriginalExtension());
        $CSVParser = new CSVParser();
        $CSVParser->parseImport($requst->file);
        $batchData = $this->processFile($originalFileName);
        // return redirect()->back();
    }


    // Process uploaded files
    public function processFile($originalFileName){
        $this->callRefreshEvent();
        $path = resource_path('temp-files/*.csv');
        $files = glob($path);
        $batch = Bus::batch([])
        ->finally(function (Batch $batch) {
            // To notify user via email or etc. project did not require.
            $this->callRefreshEvent();
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
                "CreatedAt" => date ("m/d/Y H:i", strtotime($job['created_at'])),
                "TotalJobs" => $job['total_jobs'],
                "PendingJobs" => $job['pending_jobs'],
                "Status" => $this->fileStatus($job['pending_jobs'], $job['total_jobs'], $job['failed_jobs'])
            ];
        });
        return $fileJobBatches;
    }

    // Call refresh event upon new file added, batch completed, updated
    public function callRefreshEvent(){
        event(new ActionEvent($this->getFileJobBatches())); 
    }

    public function fileStatus($pending, $total, $failed){
        if($failed>0){
            return "Failed";
        }
        if($pending==0){
            return "Completed";
        }
        if($pending==$total){
            return "Pending";
        }else{
            return "Processing";
        }
    }
}
