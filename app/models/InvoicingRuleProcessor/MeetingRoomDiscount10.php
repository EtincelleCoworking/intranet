<?php

class InvoicingRuleProcessor_MeetingRoomDiscount10 extends InvoicingRuleProcessor
{
    public function execute($invoice_lines)
    {
        $result = array();
        foreach ($invoice_lines as $line) {
            $result[] = $line;
            if ($line->ressource_id && ($line->ressource->ressource_kind_id == RessourceKind::TYPE_MEETING_ROOM)
                && ($line->amount > 0)) {
                $new_line = new InvoiceItem();
                $new_line->invoice_id = $line->invoice_id;
                $new_line->ressource_id = $line->ressource_id;
                $new_line->vat_types_id = $line->vat_types_id;
                $new_line->amount = -static::DISCOUNT_RATE / 100 * $line->amount;
                $new_line->text = sprintf('Réduction commerciale - %s (-%s%%)',
                    $line->ressource->name, static::DISCOUNT_RATE);
                $result[] = $new_line;
            }
        }

        return $result;
    }

    const DISCOUNT_RATE = 10;

    public static function getCaption()
    {
        return sprintf('%d%% de réduction sur les salles de réunion', static::DISCOUNT_RATE);
    }

}