<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedules;
use App\Models\ScheduleSlots;
use App\Models\ScheduleBooking;
use Illuminate\Support\Facades\DB;

class SchedulesProvider extends Controller
{
    /**
     * Get all the Schedules with active Slots
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSchedulesWithSlots() {
        $schedules = Schedules::all()->toArray();
        $slots = ScheduleSlots::all()->toArray();

        $currentTime = time();

        foreach ($schedules as $eKey => $sch) {
            $schedules[$eKey]['slots'] = array();

            foreach($slots as $slot){
                if ($sch['id'] == $slot['schedule_id']){

                    //Ignoring the slots whose time has been past including the "x minutes need to be left before the event starts"
                    $timeburst = strtotime($slot['slot_time']) - ($schedules[$eKey]['book_before'] * 60);
                    if ($timeburst >= $currentTime){
                        $schedules[$eKey]['slots'][] = $slot;
                    }                    
                }                
            }
        }

        return response()->json($schedules);
    }

    /**
     * Book a seat
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function book(Request $request, $schedule_id, $slot_id) {

        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $email = $request->input('email');
        $qty = $request->input('qty');

        $validator = \Validator::make(['first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'qty' => $qty], [
            'first_name' => 'required|string|min:2|max:200',
            'last_name' => 'required|string|min:2|max:200',
            'email' => 'required|email|max:300',
            'qty' => 'required|numeric|min:1',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->messages()], 422);
        }

        //Checking Schedule validity
        $schedules = Schedules::where('id', $schedule_id)->first();
        if (empty($schedules)){
            return response()->json(['success' => false, 'errors' => 'Schedule doesnot exists'], 422);
        }

        //Checking slots validity
        $slots = ScheduleSlots::where('id', $slot_id)->where('schedule_id', $schedule_id)->first();
        if (empty($slots)){
            return response()->json(['success' => false, 'errors' => 'Slot doesnot exists'], 422);
        }

        $currentTime = time();
        $timeburst = strtotime($slots->slot_time) - ($schedules->book_before * 60);
        //Checking for "x minutes need to be left before the event starts"
        if ($timeburst < $currentTime){
            return response()->json(['success' => false, 'errors' => 'The last time for booking in this slot has been passed'], 422);
        }

        //Checking whether slots are available or not
        if ($slots->available <= $slots->booked){
            return response()->json(['success' => false, 'errors' => 'All seats are booked'], 422);
        }

        //Checking Requested seats are available or not
        if ($slots->available < ($slots->booked + $qty)){
            return response()->json(['success' => false, 'errors' => 'Request seats are not available, try to reduced the seat or book other slot'], 422);
        }

        //Checking if the user is already booked seat in this slot using email
        $booking = ScheduleBooking::where('slot_id', $slot_id)->where('email', $email)->first();
        if (!empty($booking) && $booking->email == $email){
            return response()->json(['success' => false, 'errors' => 'You already booked a seat in this slot', 'booking' => $booking], 422);
        }

        //Booking ticket under the transaction
        try{
            DB::beginTransaction();

            //Adding entry in booking table
            DB::table('schedule_booking')->insert(
                array(
                        'schedule_id'   => $schedule_id,
                        'slot_id'   =>  $slot_id,
                        'first_name'     =>   $first_name, 
                        'last_name'   =>   $last_name,
                        'email'   =>   $email,
                        'qty'   =>   $qty
                )
            );

            //Increment the booked values by the requested quantity
            DB::update("update schedule_slots set booked = booked + $qty where id = $slot_id");
            DB::commit();
            
        }catch(\Exception $e){       
            DB::rollback();
            return response()->json(['success' => false, 'errors' => 'Unable to book the seat at the moment. Please try again later'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Ticket booked successfully'], 200);
    }
}
