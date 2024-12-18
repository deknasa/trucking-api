<?php

namespace App\Helpers;

use App\Models\AbsensiSupirHeader;
use App\Models\Parameter;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Intervention\Image\ImageManagerStatic as Image;

class App
{
    function getFormat(string $format, int $lastRow, int $bulan, string  $tglbukti, int  $tujuan = 0, int  $cabang = 0, int  $jenisbiaya = 0, int $marketing = 0, int $statusformat = 0)
    {


        $totalSeparator = 0;
        $staticSeparator = '#';
        $staticSeparatorformat = '|';
        $staticIterator = 0;
        $dynamicSeparator = '*';
        $dynamicIterator = 0;
        $tempStaticText = '';
        $tempDynamicText = '';
        $staticTexts = [];
        $dynamicTexts = [];
        $tempResult = '';
        $separatedResults = [];

        $format = str_replace($staticSeparatorformat, "", $format);

        /**
         * Separate static and dynamic text
         * then change them to symbol
         */
        for ($i = 0; $i < strlen($format); $i++) {
            if ($format[$i] == $staticSeparator) {
                $totalSeparator++;
            }

            if ($totalSeparator == 1) {
                if ($format[$i] != $staticSeparator) {
                    $tempStaticText .= $format[$i];
                } else {
                    $separatedResults[] = $dynamicSeparator;
                    $tempResult .= $dynamicSeparator;
                }
            } elseif ($totalSeparator == 0) {
                if ($format[$i] != $staticSeparator) {
                    $tempDynamicText .= $format[$i];
                }

                if ($i == strlen($format) - 1) {
                    $dynamicTexts[] = $tempDynamicText;
                    $tempDynamicText = '';

                    $separatedResults[] = $dynamicSeparator;
                }
            } else {
                $dynamicTexts[] = $tempDynamicText;
                $tempDynamicText = '';
                $separatedResults[] = $format[$i];
                $tempResult .= $format[$i];
            }

            if ($totalSeparator == 2) {
                $staticTexts[] = $tempStaticText;
                $tempStaticText = '';
                $totalSeparator = 0;
            }
        }

        /**
         * Change dynamic text format
         */
        // dd($tglbukti);
        $formatangka='';
        foreach ($dynamicTexts as $index => $dynamicText) {
            switch (str_replace(' ', '', $dynamicText)) {
                case 'R':
                    $dynamicTexts[$index] = $this->numberToRoman($bulan);
                    break;
                case 'T':
                    $dynamicTexts[$index] = $this->tujuan($tujuan);
                    break;
                case 'M':
                    $dynamicTexts[$index] = $this->marketing($marketing);
                    break;
                case 'C':
                    $dynamicTexts[$index] = $this->cabang($cabang);
                    break;
                case 'J':
                    $dynamicTexts[$index] = $this->jenisbiaya($jenisbiaya);
                    break;
                case $this->isDateFormat($dynamicText):
                    $dynamicTexts[$index] = date($dynamicText, strtotime($tglbukti));
                    break;
                case is_numeric($dynamicText):
                    $dynamicText = str_replace(' ', '', $dynamicText);
                    if ($statusformat!=0) {
                        $dynamicTexts[$index] =$dynamicText;
                        $formatangka=$formatangka.$dynamicText;
                    } else {
                        $dynamicTexts[$index] = sprintf('%0' . strlen($dynamicText) . 'd', $lastRow + 1);
               
                    }
                    
                    break;
                default:
                    # code...
                    break;
            }
        }



        /**
         * Change back the symbol
         * into formated text
         */
        // dd($dynamicTexts);
        foreach ($separatedResults as $index => $separatedResult) {
            if ($separatedResult == $staticSeparator) {
                $separatedResults[$index] = $staticTexts[$staticIterator];

                $staticIterator++;
            } elseif ($separatedResult == $dynamicSeparator) {
                $separatedResults[$index] = $dynamicTexts[$dynamicIterator];

                $dynamicIterator++;
            }
        }
        if ($statusformat!=0) {
            $nobukti = join($separatedResults);
            $format=$formatangka;
            $result = [
                [
                    'nobukti' => $nobukti,
                    'formatangka' => $format,
                ], 
            ];
           
        } else {
            $result = join($separatedResults);
        }
        return $result;
    }
    

    public function runningNumber(string $format, int $lastRow, int $bulan, string  $tglbukti, string $table, int  $tujuan = 0, int  $cabang = 0, int  $jenisbiaya = 0, int $marketing = 0, string $fieldnobukti = 'nobukti'): string
    {

        $totalSeparator = 0;
        $staticSeparator = '#';
        $staticSeparatorformat = '|';
        $staticIterator = 0;
        $dynamicSeparator = '*';
        $dynamicIterator = 0;
        $tempStaticText = '';
        $tempDynamicText = '';
        $staticTexts = [];
        $dynamicTexts = [];
        $tempResult = '';
        $separatedResults = [];

        $format = str_replace($staticSeparatorformat, "", $format);

        /**
         * Separate static and dynamic text
         * then change them to symbol
         */
        for ($i = 0; $i < strlen($format); $i++) {
            if ($format[$i] == $staticSeparator) {
                $totalSeparator++;
            }

            if ($totalSeparator == 1) {
                if ($format[$i] != $staticSeparator) {
                    $tempStaticText .= $format[$i];
                } else {
                    $separatedResults[] = $dynamicSeparator;
                    $tempResult .= $dynamicSeparator;
                }
            } elseif ($totalSeparator == 0) {
                if ($format[$i] != $staticSeparator) {
                    $tempDynamicText .= $format[$i];
                }

                if ($i == strlen($format) - 1) {
                    $dynamicTexts[] = $tempDynamicText;
                    $tempDynamicText = '';

                    $separatedResults[] = $dynamicSeparator;
                }
            } else {
                $dynamicTexts[] = $tempDynamicText;
                $tempDynamicText = '';
                $separatedResults[] = $format[$i];
                $tempResult .= $format[$i];
            }

            if ($totalSeparator == 2) {
                $staticTexts[] = $tempStaticText;
                $tempStaticText = '';
                $totalSeparator = 0;
            }
        }

        /**
         * Change dynamic text format
         */
        // dd($tglbukti);
        foreach ($dynamicTexts as $index => $dynamicText) {
            switch (str_replace(' ', '', $dynamicText)) {
                case 'R':
                    $dynamicTexts[$index] = $this->numberToRoman($bulan);
                    break;
                case 'T':
                    $dynamicTexts[$index] = $this->tujuan($tujuan);
                    break;
                case 'C':
                    $dynamicTexts[$index] = $this->cabang($cabang);
                    break;
                case 'M':
                    $dynamicTexts[$index] = $this->marketing($marketing);
                    break;

                case 'J':
                    $dynamicTexts[$index] = $this->jenisbiaya($jenisbiaya);
                    break;
                case $this->isDateFormat($dynamicText):
                    $dynamicTexts[$index] = date($dynamicText, strtotime($tglbukti));
                    break;
                case is_numeric($dynamicText):
                    $dynamicText = str_replace(' ', '', $dynamicText);
                    $dynamicTexts[$index] = sprintf('%0' . strlen($dynamicText) . 'd', $lastRow + 1);
                    break;
                default:
                    # code...
                    break;
            }
        }

        /**
         * Change back the symbol
         * into formated text
         */
        // dd($separatedResults);
        foreach ($separatedResults as $index => $separatedResult) {
            if ($separatedResult == $staticSeparator) {
                $separatedResults[$index] = $staticTexts[$staticIterator];

                $staticIterator++;
            } elseif ($separatedResult == $dynamicSeparator) {
                $separatedResults[$index] = $dynamicTexts[$dynamicIterator];

                $dynamicIterator++;
            }
        }

        $result = join($separatedResults);

        $sqlcek = db::table($table)->from(db::raw("$table  a with (readuncommitted)"))
            ->select('a.' . $fieldnobukti)
            ->where('a.' . $fieldnobukti, $result)
            ->first();

        if (isset($sqlcek)) {
            $lasRownext = $lastRow + 1;
            $result = $this->runningNumber($format, $lasRownext, $bulan, $tglbukti, $table,$tujuan, $cabang, $jenisbiaya, $marketing, $fieldnobukti);
            // dd($result);
        }


        // dd($result);
        return $result;
    }

    function tujuan(int $tujuan): string
    {
        $query = db::table("tujuan")->from(db::raw("tujuan a with (readuncommitted)"))
            ->select(
                'a.kodetujuan'
            )
            ->where('a.id', $tujuan)
            ->first();


        $kode = $query->kodetujuan ?? '';

        return $kode;
    }

    function marketing(int $marketing): string
    {
        $query = db::table("marketing")->from(db::raw("marketing a with (readuncommitted)"))
            ->select(
                'a.kodemarketing'
            )
            ->where('a.id', $marketing)
            ->first();


        $kode = $query->kodemarketing ?? '';

        return $kode;
    }

    function cabang(int $cabang): string
    {
        $query = db::table("cabang")->from(db::raw("cabang a with (readuncommitted)"))
            ->select(
                'a.kodecabang'
            )
            ->where('a.id', $cabang)
            ->first() ?? '';

        $kode = $query->kodecabang ?? '';

        return $kode;
    }

    function jenisbiaya(int $jenisbiaya): string
    {
        $query = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text as jenisbiaya'
            )
            ->where('a.id', $jenisbiaya)
            ->first() ?? '';

        $kode = $query->jenisbiaya ?? '';

        return $kode;
    }


    function numberToRoman(int $number): string
    {
        $map = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        ];
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

    function isDateFormat(string $format): bool
    {
        $dateFormats = [
            'y',
            'm',
            'd',
            'n'
        ];

        foreach ($dateFormats as $index => $dateFormat) {
            if (strpos(strtolower($format), $dateFormat) > -1) {
                return true;
            }

            continue;
        }

        return false;
    }

    static function imageResize(string $path, string $from, string $uniqueName): array
    {
        $destinationMedium = $path . "medium_" . $uniqueName;
        $destinationSmall = $path . "small_" . $uniqueName;

        $image_resize = Image::make($from);
        $image_resize->backup();
        $image_resize->resize(500, 350, function ($constraint) {
            $constraint->aspectRatio();
        });
        $image_resize->save($destinationMedium);

        $image_resize->reset();
        $image_resize->resize(40, 30, function ($constraint) {
            $constraint->aspectRatio();
        });
        $image_resize->save($destinationSmall);

        $result = [];
        $result[] = "medium_" . $uniqueName;
        $result[] = "small_" . $uniqueName;

        return $result;
    }


    function terbilang($satuan)
    {
        $huruf = array(
            "",
            "satu",
            "dua",
            "tiga",
            "empat",
            "lima",
            "enam",
            "tujuh",
            "delapan",
            "sembilan",
            "sepuluh",
            "sebelas"
        );

        if ($satuan < 12)
            return " " . $huruf[$satuan];
        elseif ($satuan < 20)
            return $this->terbilang($satuan - 10) . " belas";
        elseif ($satuan < 100)
            return $this->terbilang($satuan / 10) . " puluh" .
                $this->terbilang($satuan % 10);
        elseif ($satuan < 200)
            return "seratus" . $this->terbilang($satuan - 100);
        elseif ($satuan < 1000)
            return $this->terbilang($satuan / 100) . " ratus" .
                $this->terbilang($satuan % 100);
        elseif ($satuan < 2000)
            return "seribu" . $this->terbilang($satuan - 1000);
        elseif ($satuan < 1000000)
            return $this->terbilang($satuan / 1000) . " ribu" .
                $this->terbilang($satuan % 1000);
        elseif ($satuan < 1000000000)
            return $this->terbilang($satuan / 1000000) . " juta" .
                $this->terbilang($satuan % 1000000);
        elseif ($satuan >= 1000000000)
            echo "hasil terbilang tidak dapat di proses, nilai terlalu besar";
    }
}
