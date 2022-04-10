<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Product;
use App\Events\ActionEvent;
use Illuminate\Support\Facades\Bus;


class UploadBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    public $batchId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $batchId)
    {
        $this->data = $data;
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Iterate each row in the $file
        foreach($this->data as $row){
            // Update specified columns when product id exist
            Product::updateOrCreate([
                'id'=>$row[0]
            ],[
                'product_title' => $row[1],
                'product_description' => $row[2],
                'style' => $row[3],
                'sanmar_mainframe_color' => $row[4],
                'size' => $row[5],
                'color_name' => $row[6],
                'piece_price' => $row[7],
            ]);
        }
    }
}
