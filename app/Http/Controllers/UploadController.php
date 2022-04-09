<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\UploadBatch;
use Illuminate\Support\Facades\Bus;

class UploadController extends Controller
{
    // Sperate large file into chunks and upload
    public function store(Request $requst){
        $requst->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = file($requst->file->getRealPath());
        $data = array_slice($file, 1);

        $parts = (array_chunk($data, 100));

        foreach($parts as $index=>$part){
            $fileName = resource_path('temp-files/'.date('y-m-d-H-i-s').$index.'.csv');
            file_put_contents($fileName, $part);
        }
        $batchData = $this->processFile();
        // return redirect('/batch?id='.$batchData->id);
        return redirect()->back();
    }

    // Process uploaded files
    public function processFile(){
        $path = resource_path('temp-files/*.csv');
        $files = glob($path);
        $batch = Bus::batch([])->dispatch();
        // Iterate each file int the $path
        foreach($files as $file){
            $data = array_map('str_getcsv', file($file));
            // Dispatch upload job
            $batch->add(new UploadBatch($data, $batch->id));
            // Remove temp sliced file 
            unlink($file);            
        }
        return $batch;
    }



}
