<?php

namespace App\Tests;

use App\Point;
use App\Positionner;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PositionnerTest extends TestCase
{
    public static function grids(): array
    {
        return [
            'full grid' => [<<<EOF
                A1 A2 A3 A4
                B1 B2 B3 B4
                C1 C2 C3 C4
                D1 D2 D3 D4
            EOF],

            'partial grid' => [<<<EOF
                A1    A3 A4
                B1 B2 B3 B4
                C1 C2 C3 C4
                D1 D2 D3 D4
            EOF],

            'partial grid two' => [<<<EOF
                A1 A2 A3 A4
                B1 B2 B3 B4
                C1    C3 C4
                D1 D2 D3 D4
            EOF]
        ];
    }

    #[DataProvider('grids')]
    public function testLink(string $grid): void
    {
        $expected = self::buildGrid($grid);
        $points = self::flatAndShuffle($expected);

        $positionner = new Positionner();

        $count = 0;
        foreach ($points as $point) {
            $count++;
            $positionner->add($point);
            $positionner->link();
            echo $positionner->showGridPoints();
            echo PHP_EOL;
        }

        $positionner->link();
        $points = $positionner->getPoints();

        self::assertCount($count, $points);

        foreach ($points as $point) {
            self::assertEquals($point->getPointTop()?->name, $point->top, sprintf('"%s" top must be "%s"', $point->name, $point->top));
            self::assertEquals($point->getPointRight()?->name, $point->right, sprintf('"%s" right must be "%s"', $point->name, $point->right));
            self::assertEquals($point->getPointBottom()?->name, $point->bottom, sprintf('"%s" bottom must be "%s"', $point->name, $point->bottom));
            self::assertEquals($point->getPointLeft()?->name, $point->left, sprintf('"%s" left must be "%s"', $point->name, $point->left));
        }
    }

    #[DataProvider('grids')]
    public function testGrid(string $grid): void
    {
        $expected = self::buildGrid($grid);
        $points = self::flatAndShuffle($expected);

        $positionner = new Positionner();

        foreach ($points as $point) {
            $positionner->add($point);
        }

        $positionner->link();
        $points = $positionner->getFullGridPoints();
        self::assertCount(count($expected), $points);

        foreach($points as $i => $cols) {
            self::assertCount(count($expected[$i]), $cols);

            foreach($cols as $j => $point) {
                self::assertSame($expected[$i][$j], $point, sprintf('"%s" top must be "%s"', $i, $j));
            }
        }
    }

    /**
     * @return list<list<Point>>
     */
    private static function buildGrid(string $grid): array
    {
        $points = [];
        $rows = explode("\n", $grid);
        foreach($rows as $row) {
            $row = trim($row);
            $cols = str_split($row, 3);
            $line = [];
            foreach($cols as $col) {
                $line[] = trim($col);
            }
            $points[] = $line;
        }

        $grid = [];
        foreach($points as $i => $row) {
            foreach($row as $j => $p) {
                $point = new Point($p);
                if (isset($points[$i-1][$j]) && !empty($points[$i-1][$j])) {
                    $point->top = $points[$i-1][$j];
                }
                if (isset($points[$i][$j+1]) && !empty($points[$i][$j+1])) {
                    $point->right = $points[$i][$j+1];
                }
                if (isset($points[$i+1][$j]) && !empty($points[$i+1][$j])) {
                    $point->bottom = $points[$i+1][$j];
                }
                if (isset($points[$i][$j-1]) && !empty($points[$i][$j-1])) {
                    $point->left = $points[$i][$j-1];
                }
                $grid[$i][$j] = $point;
            }
        }

        return $grid;
    }

    /**
     * @return list<Point>
     */
    private static function flatAndShuffle(array $grid): array
    {
        $flattened = [];
        foreach ($grid as $row) {
            foreach ($row as $col) {
                $flattened[] = $col;
            }
        }

        shuffle($flattened);

        return $flattened;
    }
}


