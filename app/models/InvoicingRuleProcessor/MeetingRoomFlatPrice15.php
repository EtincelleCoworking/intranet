<?php

class InvoicingRuleProcessor_MeetingRoomFlatPrice10 extends InvoicingRuleProcessor
{
    public function execute($invoice_lines, $invoice_lines_details)
    {
        $result = array();
        foreach ($invoice_lines as $line) {
            $result[] = $line;
            if ($line->ressource_id
                && ($line->ressource->ressource_kind_id == RessourceKind::TYPE_MEETING_ROOM)
                && ($line->amount > 0)) {

                $duration = $line->amount / $line->ressource->amount;
                $hourly_discount = ($line->ressource->amount - static::PRICING);
                if ($hourly_discount > 0) {
                    $result[] = $this->createDiscountLine($line,
                        sprintf('Réduction commerciale - %s', $line->ressource->name), -$duration * $hourly_discount);
                }

            }
        }

        return $result;
    }

    const PRICING = 15;

    public static function getCaption()
    {
        return sprintf('Salles de réunion à prix plafonné %d€HT/h', static::PRICING);
    }

}