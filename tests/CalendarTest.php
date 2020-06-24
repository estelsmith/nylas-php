<?php

namespace Tests;

/**
 * ----------------------------------------------------------------------------------
 * Calendar Test
 * ----------------------------------------------------------------------------------
 *
 * @update lanlin
 * @change 2020/04/26
 *
 * @internal
 */
class CalendarTest extends AbsCase
{
    // ------------------------------------------------------------------------------

    public function testGetOAuthAuthorize(): void
    {
        $params =
        [
            'view' => 'count',
        ];

        $data = $this->client->Calendars()->Calendar()->getCalendarsList($params);

        $this->assertArrayHasKey('count', $data);
    }

    // ------------------------------------------------------------------------------

    public function testGetCalendar(): void
    {
        $id = 'f0yci053ovp2tit18hwemup33';

        $data = $this->client->Calendars()->Calendar()->getCalendar($id);

        $this->assertArrayHasKey($id, $data);
    }

    // ------------------------------------------------------------------------------
}
