<?php

namespace app\components;

class SeatLayout
{
    /**
     * Returns seat layout with seat numbers mapped to row+column.
     */
    public static function getSeatLayout(): array
    {
        return [
            4 => [
                'A' => ['number' => 27],
                'B' => ['number' => 28],
                'C' => ['number' => 29],
                'D' => ['number' => 30],
                'E' => ['number' => 31],
                'F' => ['number' => 32],
                'G' => ['number' => 33],
                'H' => ['number' => 34],
                'I' => ['number' => 35],
                'J' => ['number' => 36],
                'K' => ['number' => 37],
                'L' => ['number' => 38],
                'M' => ['number' => 39],
                'N' => ['number' => 40],
            ],
            3 => [
                'A' => ['number' => 13],
                'B' => ['number' => 14],
                'C' => ['number' => 15],
                'D' => ['number' => 16],
                'E' => ['number' => 17],
                'F' => ['number' => 18],
                'G' => ['number' => 19],
                'H' => ['number' => 20],
                'I' => ['number' => 21],
                'J' => ['number' => 22],
                'K' => ['number' => 23],
                'L' => ['number' => 24],
                'M' => ['number' => 25],
                'N' => ['number' => 26],
            ],
            2 => [
                'E' => ['number' => 7],
                'F' => ['number' => 8],
                'G' => ['number' => 9],
                'H' => ['number' => 10],
                'I' => ['number' => 11],
                'J' => ['number' => 12],
            ],
            1 => [
                'E' => ['number' => 1],
                'F' => ['number' => 2],
                'G' => ['number' => 3],
                'H' => ['number' => 4],
                'I' => ['number' => 5],
                'J' => ['number' => 6],
            ],
        ];
    }
}