<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CardNumController extends Controller
{
    private const OBSTACLE_STRINGS = [
        '[銀枠]',
        '[金枠]',
        '[紫]',
        '[緑]',
        '[赤]',
        '[青]',
        '[金]',
        '[銀]',
        '[黄]',
        '[黒]',
        '[MC]',
        '[Coro35th]',
        '[二刀龍]',
        '[銀字:NEXT]', //ちょっと考慮いるかも
        '[金ﾌﾟﾚｰﾄ]',
        '[ﾎｲﾙ]',
        '[箔押]',
        '[8th]',
        '[ｼｸ]',
        '[5枚ｾｯﾄ]',
        '[ｻｲﾝ]',
        '[1st]',
        '[2nd]',
        '[3rd]',
        '[金文字]',
        '[winner]',
        '[DC]',
        '[VC]',
        '[CC]',
        '[高価N]',
        '[HC]',
        '[虹]',
    ];

    private const KANA_ENCODE = 'KVAs';
    private const DEBUG_NUM = null;

    public function import(Request $request) {
        set_time_limit(300);
        $posData = $this->getCsvFromReqeuest($request, 'pos');
        $formatedPosData = collect($this->formatPosData($posData));
        $list = $this->getCsvFromReqeuest($request, 'list');

        $result = [];
        foreach($list as $index => $card) {
            $result[$index] = $card;
            $result[$index]['num'] = null;
            try {
                list($cardName, $cardNum) = explode('/【', $card[1], 2);
            } catch(Exception $e) {
                continue;
            }
            if(self::DEBUG_NUM) {
                if($card[0] == self::DEBUG_NUM) {
                    $sameCards = $formatedPosData->where('name', $this->convertName($cardName));
                    dd($this->convertName($cardName),  $formatedPosData->where('posNum', 130010214), $sameCards);
                }
            } else {
                $sameCards = $formatedPosData->where('name', $this->convertName($cardName));
                foreach($sameCards as $sameCard) {
                    if(str_ends_with($cardNum, $sameCard['num'] . '》')) {
                        $result[$index]['num'] = $sameCard['posNum'];
                        $formatedPosData->forget($sameCard['posNum']);
                        break;
                    }
                }
            }
        }
        return $this->outputCsv($result, 'pos-通販.csv');
    }

    private function getCsvFromReqeuest($request, $inputName) {
        $file = $request->file($inputName);
        $path = $file->getRealPath();
        $fp = fopen($path, 'r');
        fgetcsv($fp); //1行スキップ
        $result = [];
        while(($csvData = fgetcsv($fp)) !== FALSE && $csvData[0]) {
            $result[] = mb_convert_encoding($csvData, 'UTF-8', "Shift-JIS");
        }
        return $result;
    }

    private function formatPosData($posData) {
        $result = [];
        $unnormalizated = [];
        foreach($posData as $pos) {
            $pos[1] = str_replace(self::OBSTACLE_STRINGS, '', $pos[1]);
            if(preg_match('/^(.*?)\[(.*?)\]\[(.*?)\]\/?.*$/', $pos[1], $matches)){
                $result[$pos[0]] = [
                    'name' => $this->convertName($matches[1]),
                    'rarity' => $matches[2],
                    'num' => $matches[3],
                    'posNum' => $pos[0],
                ];
            } else {
                if(preg_match('/^(.*?)\[(.*?)\]\/?.*$/', $pos[1], $matches)) {
                    $result[$pos[0]] = [
                        'name' => $this->convertName($matches[1]),
                        'rarity' => null,
                        'num' => $matches[2],
                        'posNum' => $pos[0],
                    ];
                } else {
                    $result[$pos[0]] = [
                        'name' => $this->convertName($pos[1]),
                        'rarity' => null,
                        'num' => null,
                        'posNum' => $pos[0],
                    ];
                    $unnormalizated[] =  $result[$pos[0]]['name'];
                }
            }
        }
        return $result;
    }

    private function convertName($name) {
        $name = str_replace('－', 'ー',mb_convert_kana($name, self::KANA_ENCODE));
        return str_replace([' ','.','・','．','「', '」', "\t"], '', $name);
    }

    private function outputCsv($array, $fileName) {
        $stream = fopen('php://temp', 'r+b');
        foreach ($array as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename='.$fileName,
        );
        return Response::make($csv, 200, $headers);
    }
}
