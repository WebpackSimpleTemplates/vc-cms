<?php

namespace App\Payload;

use DateInterval;
use DateTime;
use Symfony\Component\HttpFoundation\Request;

class ReportFilterPayload
{
    public function getNow()
    {
        return date("d.m.Y H:i:s");
    }

    public static function createFromRequest(Request $request): ReportFilterPayload
    {
        $query = [];

        foreach ($request->query->keys() as $key) {
            if ($key !== "page") {
                $query[$key] = $request->query->get($key);
            }
        }

        return new ReportFilterPayload($query);
    }

    public function __construct(private array $query) {}

    public function __toString()
    {
        if (count($this->query) == 0) {
            return "";
        }

        return "?".http_build_query($this->query);
    }

    public function param(string $key, mixed $val = null)
    {
        if ($val !== null) {
            return new ReportFilterPayload(array_merge($this->query, [ $key => $val ]));
        }

        return isset($this->query[$key]) ? $this->query[$key] : "";
    }

    public function from(?string $val = null)
    {
        return $this->param("from", $val);
    }

    public function to(?string $val = null)
    {
        return $this->param("to", $val);
    }

    public function today()
    {
        return $this->from(date("Y-m-d"))->to(date("Y-m-d"));
    }

    public function yesterday()
    {
        $datetime = new DateTime();

        $datetime->add(DateInterval::createFromDateString("-1 day"));

        return $this->from($datetime->format("Y-m-d"))->to($datetime->format("Y-m-d"));
    }

    public function currentMonth()
    {
        $monthEnd = 31;

        while ($monthEnd > 0) {
            $dt = DateTime::createFromFormat("Y-m-d", date("Y-m")."-".$monthEnd);

            if (!$dt || $dt->format("Y-m") !== date("Y-m")) {
                $monthEnd--;
            } else {
                break;
            }
        }

        return $this->from(date("Y-m")."-01")->to(date("Y-m")."-".$monthEnd);
    }
}
