<?php

class InvoicingRuleProcessor_Cacao15 extends InvoicingRuleProcessor
{
    const ROOM_CACAO = 10;

    public function execute($invoice_lines, $invoice_lines_details)
    {
        $result = array();
        foreach ($invoice_lines as $line) {
            $result[] = $line;
            if ($line->ressource_id && ($line->ressource->id == self::ROOM_CACAO)
                && ($line->amount > 0)) {

                $result[] = $this->createDiscountLine($line,
                    sprintf('Réduction commerciale - %s (-%s%%)',
                        $line->ressource->name, static::DISCOUNT_RATE), -static::DISCOUNT_RATE / 100 * $line->amount);
            }
        }

        return $result;
    }

    const DISCOUNT_RATE = 25;

    public static function getCaption()
    {
        return sprintf('%d%% de réduction sur la salle Cacao', static::DISCOUNT_RATE);
    }

}