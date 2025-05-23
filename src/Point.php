<?php

namespace App;

class Point
{
    public int $position;

    private ?Point $pointTop = null;
    private ?Point $pointRight = null;
    private ?Point $pointBottom = null;
    private ?Point $pointLeft = null;

    public function __construct(public string $name,
                                public ?string $top = null,
                                public ?string $right = null,
                                public ?string $bottom = null,
                                public ?string $left = null,
    )
    {

    }

    public function setTop(Point $pointTop): void
    {
        $this->pointTop = $pointTop;
        if ($pointTop->bottom !== $this->name) {
            $pointTop->setBottom($this);
        }
    }

    public function setRight(Point $pointRight): void
    {
        $this->pointRight = $pointRight;
        if ($pointRight->left !== $this->name) {
            $pointRight->setLeft($this);
        }
    }

    public function setBottom(Point $pointBottom): void
    {
        $this->pointBottom = $pointBottom;
        if ($pointBottom->top !== $this->name) {
            $pointBottom->setTop($this);
        }
    }

    public function setLeft(Point $pointLeft): void
    {
        $this->pointLeft = $pointLeft;
        if ($pointLeft->right !== $this->name) {
            $pointLeft->setRight($this);
        }
    }

    public function getPointTop(): ?Point
    {
        return $this->pointTop;
    }

    public function getPointRight(): ?Point
    {
        return $this->pointRight;
    }

    public function getPointBottom(): ?Point
    {
        return $this->pointBottom;
    }

    public function getPointLeft(): ?Point
    {
        return $this->pointLeft;
    }

}
