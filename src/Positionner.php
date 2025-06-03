<?php

namespace App;

class Positionner
{
    /**
     * @var array<string,Point>
     */
    private array $points;

    public function __construct()
    {
        $this->points = [];
    }

    public function add(Point $point): void
    {
        $this->points[$point->name] = $point;
    }

    public function link(): void
    {
        foreach ($this->points as $point) {
            if (!empty($point->top) && isset($this->points[$point->top])) {
                $point->setTop($this->points[$point->top]);
            }
            if (!empty($point->right) && isset($this->points[$point->right])) {
                $point->setRight($this->points[$point->right]);
            }
            if (!empty($point->bottom) && isset($this->points[$point->bottom])) {
                $point->setBottom($this->points[$point->bottom]);
            }
            if (!empty($point->left) && isset($this->points[$point->left])) {
                $point->setLeft($this->points[$point->left]);
            }
        }
    }

    /**
     * @return list<list<Point>>
     */
    public function getGridPoints(): array
    {
        $start = $this->getStart();

        $return = [];
        do {
            $next = $start;

            $point = [];
            do {
                $point[] = $next;
            } while (($next = $next?->getPointRight()) !== null);
            $return[] = $point;
        } while (($start = $start->getPointBottom()) !== null);

        return $return;
    }

    /**
     * @return list<list<Point>>
     */
    public function getFullGridPoints(): array
    {
        $grid = $this->getGridPoints();

        $row = count($grid);
        $col = 0;
        foreach ($grid as $cols) {
            $col = max($col, count($cols));
        }

        $return = array_fill(0, $row, null);
        $cols = array_fill(0, $col, null);
        foreach($return as $k => $r) {
            $return[$k] = $cols;
        }


        $start = $this->getStart();

        $i = 0;
        do {
            $next = $start;
            $j = 0;
            do {
                $return[$i][$j] = $next;
                $j++;
            } while (($next = $next?->getPointRight()) !== null);
            $i++;
        } while (($start = $start->getPointBottom()) !== null);

        return $return;
    }

    /**
     * @return array<string,Point>
     */
    public function getPoints(): array
    {
        return $this->points;
    }

    public function showGridPoints(): string
    {
        $return = '';
        $grid = $this->getFullGridPoints();
        foreach ($grid as $cols) {
            $row = '';
            foreach($cols as $col) {
                $row .= sprintf("%-3s", $col?->name);
            }
            $return .= trim($row).PHP_EOL;
        }
        return $return;
    }

    private function getStart(): Point
    {
        $start = current($this->points);
        while ($start->getPointTop() !== null) {
            $start = $start->getPointTop();
        }
        while ($start->getPointLeft() !== null) {
            $start = $start->getPointLeft();
        }
        return $start;

    }
}

