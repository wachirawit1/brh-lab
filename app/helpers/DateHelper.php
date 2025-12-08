<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * แปลงวันที่จากรูปแบบ 25681017 เป็น 17 ต.ค. 2568
     */
    public static function formatThaiDate($dateString, $format = 'short')
    {
        if (empty($dateString) || strlen($dateString) != 8) {
            return $dateString;
        }

        $thaiMonthsShort = [
            '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.',
            '04' => 'เม.ย.', '05' => 'พ.ค.', '06' => 'มิ.ย.',
            '07' => 'ก.ค.', '08' => 'ส.ค.', '09' => 'ก.ย.',
            '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
        ];

        $thaiMonthsFull = [
            '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม',
            '04' => 'เมษายน', '05' => 'พฤษภาคม', '06' => 'มิถุนายน',
            '07' => 'กรกฎาคม', '08' => 'สิงหาคม', '09' => 'กันยายน',
            '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
        ];

        // แยกวันที่: 25681017 -> 2568 10 17
        $year = substr($dateString, 0, 4);
        $month = substr($dateString, 4, 2);
        $day = substr($dateString, 6, 2);

        // ลบ 0 นำหน้า
        $day = ltrim($day, '0');

        if ($format === 'full') {
            return $day . ' ' . ($thaiMonthsFull[$month] ?? $month) . ' ' . $year;
        }

        return $day . ' ' . ($thaiMonthsShort[$month] ?? $month) . ' ' . $year;
    }

    /**
     * แปลง DateTime เป็นรูปแบบวันที่ไทย พร้อมเวลา
     *
     * @param string|Carbon $datetime รูปแบบ '2025-10-09 11:29:16' หรือ Carbon object
     * @param string $format 'short' = 9 ต.ค. 2568 11:29, 'full' = 9 ตุลาคม 2568 11:29:16
     * @return string
     */
    public static function formatThaiDateTime($datetime, $format = 'short')
    {
        if (empty($datetime)) {
            return '';
        }

        try {
            $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);

            $thaiMonthsShort = [
                1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.',
                4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
                7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.',
                10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
            ];

            $thaiMonthsFull = [
                1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
                4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
                7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
                10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
            ];

            $day = $carbon->day;
            $month = $carbon->month;
            $year = $carbon->year + 543; // แปลงเป็น พ.ศ.
            $time = $carbon->format('H:i');

            if ($format === 'full') {
                $seconds = $carbon->format(':s');
                return $day . ' ' . $thaiMonthsFull[$month] . ' ' . $year . ' ' . $time . $seconds;
            }

            // format = 'short' (default)
            return $day . ' ' . $thaiMonthsShort[$month] . ' ' . $year . ' ' . $time;

        } catch (\Exception $e) {
            return $datetime;
        }
    }

    /**
     * แปลงวันที่จาก 25681017 เป็น 2568-10-17
     */
    public static function toISO($dateString)
    {
        if (empty($dateString) || strlen($dateString) != 8) {
            return $dateString;
        }

        $year = substr($dateString, 0, 4);
        $month = substr($dateString, 4, 2);
        $day = substr($dateString, 6, 2);

        return $year . '-' . $month . '-' . $day;
    }

    /**
     * แปลงเป็น Carbon object
     */
    public static function toCarbon($dateString)
    {
        if (empty($dateString) || strlen($dateString) != 8) {
            return null;
        }

        $year = (int)substr($dateString, 0, 4) - 543; // แปลงเป็น ค.ศ.
        $month = substr($dateString, 4, 2);
        $day = substr($dateString, 6, 2);

        return Carbon::createFromFormat('Y-m-d', $year . '-' . $month . '-' . $day);
    }
}
