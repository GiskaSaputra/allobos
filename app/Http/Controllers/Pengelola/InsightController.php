<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Services\Insights\InsightService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InsightController extends Controller
{
    public function __invoke(Request $request, InsightService $insights): View|StreamedResponse
    {
        $data = $insights->all();

        if ($request->query('unduh') === 'xlsx') {
            $book = new Spreadsheet;
            $book->removeSheetByIndex(0);
            foreach ($data as $key => $section) {
                $sheet = $book->createSheet()->setTitle(mb_substr($section['title'], 0, 31));
                $sheet->fromArray([$section['label'], 'Jumlah'], null, 'A1');
                foreach ($section['rows'] as $i => [$name, $value]) {
                    $sheet->setCellValueExplicit('A'.($i + 2), (string) $name, DataType::TYPE_STRING);
                    $sheet->setCellValue('B'.($i + 2), $value);
                }
            }

            return response()->streamDownload(fn () => (new Xlsx($book))->save('php://output'), 'wawasan-allobos-'.now()->format('Y-m-d').'.xlsx');
        }

        return view('pengelola.insights', ['sections' => $data]);
    }
}
