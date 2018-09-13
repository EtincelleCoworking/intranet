<?php

class InvoicingRuleProcessor_MeetingRoomFlatPrice10 extends InvoicingRuleProcessor
{
    public function execute($invoice_lines)
    {
        $result = array();
        foreach ($invoice_lines as $line) {
            $result[] = $line;
            if ($line->ressource_id
                && ($line->ressource->ressource_kind_id == RessourceKind::TYPE_MEETING_ROOM)
                && ($line->amount > 0)) {

                $duration = $line->amount / $line->ressource->amount;
                $hourly_discount = ($line->ressource->amount - static::PRICING);

                $result[] = $this->createDiscountLine($line,
                    sprintf('Réduction commerciale - %s', $line->ressource->name), -$duration * $hourly_discount);
            }
        }

        return $result;
    }

    const PRICING = 10;

    public static function getCaption()
    {
        return sprintf('Salles de réunion à prix fixe %d€HT/h', static::PRICING);
    }

}