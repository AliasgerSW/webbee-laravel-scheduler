<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Schedules;
use App\Models\ScheduleSlots;
use App\Models\ScheduleBooking;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function($table) {

            //These schedules can be added via separate API. I'm skipping that API for now

            //Dummy schedule created
            $schedule = Schedules::create([
                'date_from' => '2021-09-06 00:00:00',
                'date_to' => '2021-09-07 23:59:59',
                'slot_duration' => '60',
                'book_before' => '120'
            ]);

            $slot_date = ['2021-09-06', '2021-09-07'];

            // '14:00' left for assuming lunch time
            $slot_time = ['10:00:00', '11:00:00', '12:00:00', '13:00:00', '15:00:00', '16:00:00', '17:00:00', '18:00:00'];

            //Dummy slots generated
            //Assuming that these slots will be generated via another API according to the slot duration and avoiding breaks
            foreach ($slot_date as $date) {
                foreach ($slot_time as $time) {
                    ScheduleSlots::create([
                        'schedule_id' => $schedule->id,
                        'slot_time' => $date . ' ' . $time,
                        'available' => '50',
                        'booked' => '0'
                    ]);
                }
            }           

        });
    }
}
