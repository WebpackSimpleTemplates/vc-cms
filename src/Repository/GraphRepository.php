<?php

namespace App\Repository;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\BarPlot;

class GraphRepository
{

    function mapValue($value)
    {
        if (gettype($value) === 'string') {
            $comps = explode(":", $value);

            return ((int) $comps[0]) * 60 + ((int) $comps[1]);
        }

        return $value;
    }

    public function createGraph($width, $height, $vertical = false, $left = 45): Graph
    {
        $graph = new Graph($width,$height,'auto');
        $graph->SetScale("textlin");

        if ($vertical) {
            $graph->Set90AndMargin($left,20,30,10);
        }
        $graph->SetBox(false);

        $graph->ygrid->SetFill(false);

        $graph->yaxis->HideLine(false);
        $graph->yaxis->HideTicks(false,false);

        return $graph;
    }

    public function createBarPlot(string $color, array $values)
    {
        $datay=[];

        foreach ($values as $_ => $value) {
            $datay[] = $this->mapValue($value);
        }

        if (count($datay)) {
            $b1plot = new BarPlot($datay);


            $b1plot->SetColor("white");
            $b1plot->SetFillColor($color);
        } else {
            $b1plot = new BarPlot([0]);

            $b1plot->SetColor("white");
            $b1plot->SetFillColor("white");
            $b1plot->SetWidth(1);
        }

        return $b1plot;
    }

    public function getLables(array $values)
    {
        $lables = [];

        foreach ($values as $i => $_) {
            $lables[] = $i;
        }

        if (count($lables)) {
            return $lables;
        } else {
            return [''];
        }
    }
}
