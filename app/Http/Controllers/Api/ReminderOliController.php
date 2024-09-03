<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReminderOli;
use App\Mail\EmailReminderOli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ReminderOliController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {

        $reminderOli = new ReminderOli();
        // dd(system('getmac'));
        return response([
            'data' => $reminderOli->get(request()->status),
            'attributes' => [
                'totalRows' => $reminderOli->totalRows,
                'totalPages' => $reminderOli->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $forExport = $request->forExport_id;
        $status = $request->status_id;

        $reminderOli = new ReminderOli();
        $reminder_Oli = $reminderOli->get($forExport, $status);

        $data = $reminder_Oli;

        //PRINT TO EXCEL
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', $data[0]->judul);
        $sheet->getStyle("A1")->getFont()->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:F1');

        $sheet->setCellValue('A2', $data[0]->judulLaporan);
        $sheet->getStyle("A2")->getFont()->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:F2');

        $detail_table_header_row = 5;
        $detail_start_row = 6;

        $styleArray = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        );

        $style_number = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],

            'borders' => [
                'top' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'right' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'left' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]
        ];
        $alphabets = range('A', 'Z');
        $detail_columns = [
            [
                'label' => 'No',
                'index' => '',
            ],
            [
                'label' => 'No Pol',
                'index' => 'nopol',
            ],
            [
                'label' => 'Tanggal',
                'index' => 'tanggal',
            ],
            [
                'label' => 'Status Reminder',
                'index' => 'status',
            ],
            [
                'label' => 'KM',
                'index' => 'km'
            ],
            [
                'label' => 'KM Perjalanan',
                'index' => 'kmperjalanan'
            ]
        ];


        foreach ($detail_columns as $detail_columns_index => $detail_column) {
            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
        }
        $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->applyFromArray($styleArray)->getFont()->setBold(true);

        // LOOPING DETAIL
        $dataRow = $detail_table_header_row + 2;
        $previousRow = $dataRow - 1; // Initialize the previous row number
        $a = 1;
        foreach ($data as $response_index => $response_detail) {

            // foreach ($detail_columns as $detail_columns_index => $detail_column) {
            //     $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail[$detail_column['index']] : $response_index + 1);
            // }

            $sheet->setCellValue("A$detail_start_row", $a);
            $sheet->setCellValue("B$detail_start_row", $response_detail->nopol);
            $sheet->setCellValue("C$detail_start_row", date('d-m-Y', strtotime($response_detail->tanggal)));
            $sheet->setCellValue("D$detail_start_row", $response_detail->status);
            $sheet->setCellValue("E$detail_start_row", $response_detail->km);
            $sheet->setCellValue("F$detail_start_row", $response_detail->kmperjalanan);

            $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
            $sheet->getStyle("E$detail_start_row:F$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00");

            $a++;
            $dataRow++;
            $detail_start_row++;
        }
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'Laporan Reminder Oli' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }


    public function sendEmailReminder_olimesin()
    {

        // $data = [
        //     (object)[
        //         "tgl"=> "2023-11-15",
        //         "kodetrado"=> "BK SKSK HY",
        //         "tanggal"=> "11-Oktober-2023",
        //         "batasganti"=> "10000",
        //         "kberjalan"=> "3000",
        //         "Keterangan"=> "04817106",
        //         "warna"=> "RED",
        //         "toemail"=> "iqbal13rafli@gmail.com",
        //         "ccemail"=> "iqbal_rafli13@yahoo.com",
        //         "bccemail"=> "raflimaestro@gmail.com",
        //         "judul"=> "Reminder Penggantian Oli Mesin (Mdn)",
        //     ],
        // ];

        $ReminderOliMesin = (new ReminderOli())->reminderemailolimesin()->get();
        $data = $ReminderOliMesin->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderOliMesin = json_encode($ReminderOliMesin);

        Mail::to($toemail)
            ->cc($ccemail)
            ->bcc($bccemail)
            ->send(new EmailReminderOli($ReminderOliMesin, 'mesin'));

        // return (new EmailReminderOli($ReminderOliMesin,'mesin'))->render();
    }
    public function sendEmailReminder_saringanhawa()
    {

        // $data = [
        //     (object)[
        //         "tgl" => "2023-11-15",
        //         "kodetrado" => "BK SKSK HY",
        //         "tanggal" => "11-Oktober-2023",
        //         "batasganti" => "10000",
        //         "kberjalan" => "3000",
        //         "Keterangan" => "04817106",
        //         "warna" => "RED",
        //         "toemail"=> "iqbal13rafli@gmail.com",
        //         "ccemail"=> "iqbal_rafli13@yahoo.com",
        //         "bccemail"=> "raflimaestro@gmail.com",
        //         "judul" => "RReminder Penggantian Saringan Hawa (Mks)",
        //     ],
        // ];

        $ReminderSaringanHawa = (new ReminderOli())->reminderemailsaringanhawa()->get();
        $data = $ReminderSaringanHawa->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderSaringanHawa = json_encode($ReminderSaringanHawa);

        Mail::to($toemail)
            ->cc($ccemail)
            ->bcc($bccemail)
            ->send(new EmailReminderOli($ReminderSaringanHawa, 'saringanhawa'));

        // return (new EmailReminderOli($ReminderSaringanHawa,'saringanhawa'))->render();
    }
    public function sendEmailReminder_perseneling()
    {

        // $data = [
        //     (object)[
        //         "tgl" => "2023-11-15",
        //         "kodetrado" => "BK SKSK HY",
        //         "tanggal" => "11-Oktober-2023",
        //         "batasganti" => "10000",
        //         "kberjalan" => "3000",
        //         "Keterangan" => "04817106",
        //         "warna" => "RED",
        //         "toemail"=> "iqbal13rafli@gmail.com",
        //         "ccemail"=> "iqbal_rafli13@yahoo.com",
        //         "bccemail"=> "raflimaestro@gmail.com",
        //         "judul" => "Reminder Penggantian Oli Perseneling (Sby)",
        //     ],
        // ];



        $ReminderOliPersneling = (new ReminderOli())->reminderemailolipersneling()->get();
        $data = $ReminderOliPersneling->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderOliPersneling = json_encode($data);

        Mail::to($toemail)
            ->cc($ccemail)
            ->bcc($bccemail)
            ->send(new EmailReminderOli($ReminderOliPersneling, 'perseneling'));

        // return (new EmailReminderOli($ReminderOliPersneling,'perseneling'))->render();

    }
    public function sendEmailReminder_oligardan()
    {

        // $data = [
        //     (object)[
        //         "tgl" => "2023-11-15",
        //         "kodetrado" => "BK SKSK HY",
        //         "tanggal" => "11-Oktober-2023",
        //         "batasganti" => "10000",
        //         "kberjalan" => "3000",
        //         "Keterangan" => "04817106",
        //         "warna" => "RED",
        //         "toemail"=> "iqbal13rafli@gmail.com",
        //         "ccemail"=> "iqbal_rafli13@yahoo.com",
        //         "bccemail"=> "raflimaestro@gmail.com",
        //         "judul" => "Reminder Penggantian Oli Gardan (Bitung)",
        //     ],
        // ];

        $ReminderOliGardan = (new ReminderOli())->reminderemailoligardan()->get();
        $data = $ReminderOliGardan->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderOliGardan = json_encode($data);

        Mail::to($toemail)
            ->cc($ccemail)
            ->bcc($bccemail)
            ->send(new EmailReminderOli($ReminderOliGardan, 'oligardan'));

        // return (new EmailReminderOli($ReminderOliGardan,'oligardan'))->render();
    }
    public function sendEmailReminder_ServiceRutin()
    {

        $ReminderServiceRutin = (new ReminderOli())->reminderemailservicerutin()->get();
        $data = $ReminderServiceRutin->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderServiceRutin = json_encode($ReminderServiceRutin);

        Mail::to($toemail)
            ->cc($ccemail)
            ->bcc($bccemail)
            ->send(new EmailReminderOli($ReminderServiceRutin, 'ServiceRutin'));

        // return (new EmailReminderOli($ReminderServiceRutin,'ServiceRutin'))->render();
    }
}
