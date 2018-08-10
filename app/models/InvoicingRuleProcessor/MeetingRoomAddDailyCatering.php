<?php

class InvoicingRuleProcessor_MeetingRoomAddDailyCatering extends InvoicingRuleProcessor
{
    public function execute($invoice_lines)
    {
        $result = array();
        while (count($invoice_lines) > 0) {
            $line = array_shift($invoice_lines);
            $result[] = $line;
            if ($line->ressource_id
                && ($line->ressource->ressource_kind_id == RessourceKind::TYPE_MEETING_ROOM)
                && ($line->amount > 0)) {
                $participant_count = 1;
                if (preg_match('/([0-9]+) participant/', $line->text, $tokens)) {
                    $participant_count = (int)$tokens[1];
                }

                $new_line = new InvoiceItem();
                $new_line->invoice_id = $line->invoice_id;
                $new_line->amount = static::UNIT_PRICE * $participant_count;
                $new_line->ressource_id = Ressource::TYPE_CATERING_INTERNAL;
                $new_line->text = sprintf('Formule séminaire - %0.2f€/pers.<br />%d participants', static::UNIT_PRICE, $participant_count);
                $new_line->vat_types_id = $line->vat_types_id;

                // -- Stack discounts
                while ((count($invoice_lines) > 0)
                    && ($invoice_lines[0]->ressource_id == $line->ressource_id)
                    && ($invoice_lines[0]->amount < 0)) {
                    $line = array_shift($invoice_lines);
                    $result[] = $line;
                }

                $result[] = $new_line;
            }
        }

        return $result;
    }

    const UNIT_PRICE = 10;

    public static function getCaption()
    {
        return sprintf('Ajoute une formule séminaire sur les locations de salle (%0.2f€HT/pers)', static::UNIT_PRICE);
    }

    public function isValidForInvoices()
    {
        return false;
    }
}