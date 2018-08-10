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

                $new_line = new InvoiceItem();
                $new_line->invoice_id = $line->invoice_id;
                $new_line->ressource_id = $line->ressource_id;
                $new_line->vat_types_id = $line->vat_types_id;
                $new_line->amount = -$duration * $hourly_discount;
                $new_line->text = sprintf('Réduction commerciale - %s', $line->ressource->name);
                $result[] = $new_line;
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