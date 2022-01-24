<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSupirHeader;
use App\Models\Parameter;
use Illuminate\Http\Request;

class RunningNumberController extends Controller
{
    /**
     * @param \Illuminate\Http\Request @request
     * 
     * @return string $runningNumber
     */
    public function index(Request $request)
    {
        $parameter = Parameter::where('grp', $request->grp)
            ->where('subgrp', $request->subgrp)
            ->first();

        $text = $parameter->text;

        $text = str_replace('#', '', $text);
        $text = str_replace('RMW', $this->numberToRoman(1), $text);
        $text = str_replace('Y', date('Y'), $text);

        $numberFormat = substr($text, 4, 3);
        $totalRows = AbsensiSupirHeader::count();
        $lastNumber = '';

        for ($i=0; $i < strlen($numberFormat); $i++) { 
            $lastNumber .= '0';
        }
        
        $lastNumber .= $totalRows + 1;
        $text = str_replace('9999', $lastNumber, $text);

        $runningNumber = $text;
        
        return response([
            'data' => $runningNumber
        ]);
    }

    /**
     * @param int $number
     * 
     * @return string
     */
    function numberToRoman($number)
    {
        $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $returnValue = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }

        return $returnValue;
    }
}
