<?php

class InvoicingRuleProcessor_MeetingRoomDiscount extends InvoicingRuleProcessor
{
    protected static $discount;

    public function execute($invoice_lines, $invoice_lines_details)
    {
        $result = array();
        $discount = self::$discount;
        foreach ($invoice_lines as $line) {
            $result[] = $line;
            if ($line->ressource_id && ($line->ressource->ressource_kind_id == RessourceKind::TYPE_MEETING_ROOM)
                && ($line->amount > 0)) {

                $result[] = $this->createDiscountLine($line,
                    sprintf('Réduction commerciale - %s (-%s%%)',
                        $line->ressource->name, $discount), -$discount / 100 * $line->amount);
            }
        }

        return $result;
    }

    public static function getCaption()
    {
        return sprintf('%d%% de réduction sur les salles de réunion', self::$discount);
    }

}