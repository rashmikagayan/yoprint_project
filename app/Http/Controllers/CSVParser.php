<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ProductController;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class CSVParser extends Controller
{
    public function parseImport($file)
    {
        // Increasing memory limit for the file upload
        ini_set('memory_limit', '-1');
        $spreadsheet = Excel::toArray(new ProductController, $file)[0];
        $sheetData = [];
        $spreadsheet = array_slice($spreadsheet, 1);
        // Remove non uft at each column
        foreach ($spreadsheet as $key => $row) {
            $col = array_values($row);
            $data = [
                    $this->removeNonUFT($col[0]), //UNIQUE_KEY
                    $this->removeNonUFT($col[1]), //PRODUCT_TITLE
                    $this->removeNonUFT($col[2]), //PRODUCT_DESCRIPTION
                    $this->removeNonUFT($col[3]), //STYLE
                    $this->removeNonUFT($col[28]), //SANMAR_MAINFRAME_COLOR
                    $this->removeNonUFT($col[18]), //SIZE
                    $this->removeNonUFT($col[14]), //COLOR_NAME
                    $this->removeNonUFT($col[21]), //PIECE_PRICE,
                ]; 
                array_push($sheetData, $data);
        }
        // Chunk into 100 lines per sheet
        $parts = (array_chunk($sheetData, 500));
        foreach($parts as $index=>$part){
            $fileName = resource_path('temp-files/'.date('y-m-d-H-i-s').$index.'.csv');
            $file = fopen($fileName, 'w+');
            foreach($part as $row){
                fputcsv($file, $row, ',');
            }
        }
    }

    public function removeNonUFT($str){
        $str = preg_replace('/\s+/', ' ', $str);
        return preg_replace("/&#?[a-z0-9]+;\s+/i"," ",$str);
    }
}