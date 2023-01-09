<?php


namespace PTS\Bolt\Type\Temporal;

interface DateTimeConvertible
{
    /**
     * Create object from DateTime
     * @param \DateTimeInterface $dateTime
     * @return static
     */
    public static function fromDateTime(\DateTimeInterface $dateTime);

    /**
     * Create DateTime object from current temporal
     * @return \DateTime
     */
    public function toDateTime(): \DateTime;
}
