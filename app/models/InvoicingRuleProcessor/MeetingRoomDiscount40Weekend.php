<?php

class InvoicingRuleProcessor_MeetingRoomDiscount40Weekend extends InvoicingRuleProcessor
{
    public function execute($invoice_lines, $invoice_lines_details)
    {
        $result = array();
        $discount = self::getDiscountRate();
        foreach ($invoice_lines as $index => $line) {
            $result[] = $line;
            if ($line->ressource_id && ($line->ressource->ressource_kind_id == RessourceKind::TYPE_MEETING_ROOM)
                && ($line->amount > 0)) {

                $discount_amount = 0;
                foreach ($invoice_lines_details[$index] as $booking) {
                    if (in_array(date('N'), array(6, 7))) { // week-end
                        $discount_amount += -$discount / 100 * $invoice_lines_details[$index]['amount'];
                    }
                }
                if ($discount_amount != 0) {
                    $result[] = $this->createDiscountLine($line,
                        sprintf('Réduction commerciale - %s (-%s%%)',
                            $line->ressource->name, $discount), $discount_amount);
                }
            }
        }

        return $result;
    }

    protected static function getDiscountRate()
    {
        return 40;
    }

    public static function getCaption()
    {
        return sprintf('%d%% de réduction sur les salles de réunion le week-end', self::getDiscountRate());
    }

}