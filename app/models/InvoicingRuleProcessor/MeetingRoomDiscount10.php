<?php

class InvoicingRuleProcessor_MeetingRoomDiscount10 extends InvoicingRuleProcessor
{
    public function execute($invoice_lines)
    {
        $result = array();
        $discount = $this->getDiscountRate();
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

    protected function getDiscountRate(){
        return 10;
    }

    public static function getCaption()
    {
        return sprintf('%d%% de réduction sur les salles de réunion', $this->getDiscountRate());
    }

}