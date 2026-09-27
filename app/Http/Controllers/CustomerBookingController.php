<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Availability;
use App\Models\BlockedDate;
use App\Models\AvailabilityOverride;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class CustomerBookingController extends Controller
{
    public function create(Request $request, Service $service)
    {
        $service->load('makeupArtist.user');
    
        $makeupArtist = $service->makeupArtist;
    
        // Selected month
        $month = $request->input('month', now()->format('Y-m'));
    
        try {
            $currentMonth = Carbon::createFromFormat('!Y-m', $month)
                ->startOfMonth();
        } catch (\Exception $e) {
            $currentMonth = now()->startOfMonth();
        }
    
        // Weekly availability
        $availabilities = Availability::where(
            'makeup_artist_id',
            $makeupArtist->id
        )
        ->get()
        ->keyBy('day_of_week');
    
        // Full-day blocked dates
        $blockedDates = BlockedDate::where(
            'makeup_artist_id',
            $makeupArtist->id
        )
        ->whereYear('blocked_date', $currentMonth->year)
        ->whereMonth('blocked_date', $currentMonth->month)
        ->get();
    
        // Custom working hours
        $availabilityOverrides = AvailabilityOverride::where(
            'makeup_artist_id',
            $makeupArtist->id
        )
        ->whereYear('override_date', $currentMonth->year)
        ->whereMonth('override_date', $currentMonth->month)
        ->get();
    
        $previousMonth = $currentMonth->copy()
            ->subMonth()
            ->format('Y-m');
    
        $nextMonth = $currentMonth->copy()
            ->addMonth()
            ->format('Y-m');
        
            // Selected appointment date
        $selectedDate = $request->input('date');

        $timeSlots = [];

        if ($selectedDate) {

            $selectedCarbon = Carbon::parse($selectedDate);
        
            $dateString = $selectedCarbon->format('Y-m-d');
        
            $dayName = $selectedCarbon->format('l');
        
        
            // Get existing bookings for this MUA on selected date
            $existingBookings = Booking::where(
                    'makeup_artist_id',
                    $makeupArtist->id
                )
                ->whereDate('booking_date', $dateString)
                ->whereIn('status', ['pending', 'accepted'])
                ->with('service')
                ->get();
        
        
            // Check full-day block
            $isBlocked = BlockedDate::where(
                'makeup_artist_id',
                $makeupArtist->id
            )
            ->whereDate('blocked_date', $dateString)
            ->exists();
        
            if (!$isBlocked) {

                // Check custom hours first
                $override = AvailabilityOverride::where(
                    'makeup_artist_id',
                    $makeupArtist->id
                )
                ->whereDate('override_date', $dateString)
                ->first();


                if ($override) {

                    $startTime = $override->start_time;
                    $endTime = $override->end_time;

                } else {

                    // Use normal weekly availability
                    $weeklyAvailability = Availability::where(
                        'makeup_artist_id',
                        $makeupArtist->id
                    )
                    ->where('day_of_week', $dayName)
                    ->where('is_working', true)
                    ->first();


                    if ($weeklyAvailability) {

                        $startTime = $weeklyAvailability->start_time;
                        $endTime = $weeklyAvailability->end_time;

                    } else {

                        $startTime = null;
                        $endTime = null;
                    }
                }


                // Generate time slots
                if ($startTime && $endTime) {

                    $slot = Carbon::parse(
                        $dateString . ' ' . $startTime
                    );

                    $end = Carbon::parse(
                        $dateString . ' ' . $endTime
                    );

                    $duration = (int) $service->duration;


                    while (
                        $slot->copy()->addMinutes($duration)->lte($end)
                    ) {
                    
                        $newBookingStart = $slot->copy();
                    
                        $newBookingEnd = $slot->copy()
                            ->addMinutes($duration);
                    
                        $hasConflict = false;
                    
                    
                        foreach ($existingBookings as $existingBooking) {
                    
                            $existingStart = Carbon::parse(
                                $dateString . ' ' . $existingBooking->booking_time
                            );
                    
                            $existingEnd = $existingStart->copy()
                                ->addMinutes($existingBooking->service->duration);
                    
                    
                            if (
                                $newBookingStart->lt($existingEnd)
                                &&
                                $newBookingEnd->gt($existingStart)
                            ) {
                    
                                $hasConflict = true;
                    
                                break;
                            }
                        }
                    
                    
                        if (!$hasConflict) {
                    
                            $timeSlots[] = $slot->format('H:i');
                        }
                    
                    
                        $slot->addMinutes(30);
                    }
                }
            }
        }
        return view('customer.booking.create', compact(
            'service',
            'makeupArtist',
            'availabilities',
            'blockedDates',
            'availabilityOverrides',
            'currentMonth',
            'previousMonth',
            'nextMonth',
            'selectedDate',
            'timeSlots'
        ));
    }

        public function payment(Request $request)
    {
     
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date',
            'booking_time' => 'required',
            'event_type' => 'required|string',
            'appointment_location_type' => 'required|string',
            'appointment_address' => 'nullable|string',
            'customer_phone' => 'required|string|max:30',
            'additional_notes' => 'nullable|string',
        ]);

        $service = Service::with('makeupArtist.user')
            ->findOrFail($validated['service_id']);

        $servicePrice = $service->price;

        $depositAmount = $service->deposit_amount ?? 0;

        $balanceAmount = $servicePrice - $depositAmount;

        return view(
            'customer.booking.payment',
            compact(
                'validated',
                'service',
                'servicePrice',
                'depositAmount',
                'balanceAmount'
            )
        );
    }

    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date',
            'booking_time' => 'required',
            'event_type' => 'required|string',
            'appointment_location_type' => 'required|in:studio,customer_location',
            'appointment_address' => 'nullable|string',
            'customer_phone' => 'required|string|max:30',
            'additional_notes' => 'nullable|string',
        ]);
    
        $service = Service::with('makeupArtist')
            ->findOrFail($validated['service_id']);
    
        $makeupArtist = $service->makeupArtist;
        

        // ========================================
        // Validate selected appointment slot
        // ========================================

        $bookingDate = Carbon::parse($validated['booking_date']);
        $dateString = $bookingDate->format('Y-m-d');
        $dayName = $bookingDate->format('l');


        // Prevent past date
        if ($bookingDate->lt(now()->startOfDay())) {
            return back()->withErrors([
                'booking_time' => 'The selected appointment date has already passed.'
            ])->withInput();
        }


        // Check blocked date
        $isBlocked = BlockedDate::where(
                'makeup_artist_id',
                $makeupArtist->id
            )
            ->whereDate('blocked_date', $dateString)
            ->exists();

        if ($isBlocked) {
            return back()->withErrors([
                'booking_time' => 'The selected date is no longer available.'
            ])->withInput();
        }


        // Check custom hours first
        $override = AvailabilityOverride::where(
                'makeup_artist_id',
                $makeupArtist->id
            )
            ->whereDate('override_date', $dateString)
            ->first();


        if ($override) {

            $startTime = $override->start_time;
            $endTime = $override->end_time;

        } else {

            $weeklyAvailability = Availability::where(
                    'makeup_artist_id',
                    $makeupArtist->id
                )
                ->where('day_of_week', $dayName)
                ->where('is_working', true)
                ->first();


            if (!$weeklyAvailability) {

                return back()->withErrors([
                    'booking_time' => 'The MUA is not available on the selected date.'
                ])->withInput();
            }


            $startTime = $weeklyAvailability->start_time;
            $endTime = $weeklyAvailability->end_time;
        }

        $newBookingStart = Carbon::parse(
            $dateString . ' ' . $validated['booking_time']
        );
        
        $newBookingEnd = $newBookingStart->copy()
            ->addMinutes((int) $service->duration);
        
        
        $workingStart = Carbon::parse(
            $dateString . ' ' . $startTime
        );
        
        $workingEnd = Carbon::parse(
            $dateString . ' ' . $endTime
        );
        
        
        // Appointment must be completely inside working hours
        if (
            $newBookingStart->lt($workingStart)
            ||
            $newBookingEnd->gt($workingEnd)
        ) {
        
            return back()->withErrors([
                'booking_time' => 'The selected appointment time is outside the MUA availability.'
            ])->withInput();
        }

        if (!in_array((int) $newBookingStart->format('i'), [0, 30])) {

            return back()->withErrors([
                'booking_time' => 'Invalid appointment time selected.'
            ])->withInput();
        }

        $existingBookings = Booking::where(
            'makeup_artist_id',
            $makeupArtist->id
        )
        ->whereDate('booking_date', $dateString)
        ->whereIn('status', ['pending', 'accepted'])
        ->with('service')
        ->get();


        foreach ($existingBookings as $existingBooking) {

        $existingStart = Carbon::parse(
            $dateString . ' ' . $existingBooking->booking_time
        );

        $existingEnd = $existingStart->copy()
            ->addMinutes((int) $existingBooking->service->duration);


        $hasConflict =
            $newBookingStart->lt($existingEnd)
            &&
            $newBookingEnd->gt($existingStart);


            if ($hasConflict) {

                return redirect()->route(
                    'customer.booking.create',
                    [
                        'service' => $service->id,
                        'month' => $bookingDate->format('Y-m'),
                        'date' => $dateString,
                    ]
                )->withErrors([
                    'booking_time' =>
                        'The selected appointment time is no longer available. Please choose another time.'
                ])->withInput();
            }
        }

                $servicePrice = $service->price;
                $depositAmount = $service->deposit_amount ?? 0;
                $balanceAmount = $servicePrice - $depositAmount;

                // 1. Create booking first
                $booking = Booking::create([
                    'customer_id' => Auth::id(),
                    'makeup_artist_id' => $makeupArtist->id,
                    'service_id' => $service->id,

                    'booking_date' => $validated['booking_date'],
                    'booking_time' => $validated['booking_time'],
                    'event_type' => $validated['event_type'],

                    'appointment_location_type' =>
                        $validated['appointment_location_type'],

                    'appointment_address' =>
                        $validated['appointment_location_type'] === 'customer_location'
                            ? ($validated['appointment_address'] ?? null)
                            : null,

                    'customer_phone' => $validated['customer_phone'],

                    'additional_notes' =>
                        $validated['additional_notes'] ?? null,

                    'status' => 'pending',

                    'service_price' => $servicePrice,
                    'deposit_amount' => $depositAmount,
                    'balance_amount' => $balanceAmount,

                    // Not paid yet until ToyyibPay confirms payment
                    'payment_status' => 'unpaid',
                ]);

                // 2. Create ToyyibPay bill
                $response = Http::asForm()->post(
                    config('services.toyyibpay.base_url') . '/index.php/api/createBill',
                    [
                        'userSecretKey' =>
                            config('services.toyyibpay.secret_key'),

                        'categoryCode' =>
                            config('services.toyyibpay.category_code'),

                        'billName' =>
                            'GlowAI Booking ' . $booking->id,

                        'billDescription' =>
                            'Deposit payment for GlowAI booking',

                        'billPriceSetting' => 1,
                        'billPayorInfo' => 1,

                        // TEMPORARY: RM1.00 for sandbox testing
                        'billAmount' => 100,

                        'billReturnUrl' =>
                            route('customer.booking.toyyibpay.return'),

                        'billCallbackUrl' =>
                            route('customer.booking.toyyibpay.callback'),

                        'billExternalReferenceNo' =>
                            (string) $booking->id,

                        'billTo' =>
                            Auth::user()->name,

                        'billEmail' =>
                            Auth::user()->email,

                        'billPhone' =>
                            $validated['customer_phone'],

                        'billSplitPayment' => 0,
                        'billPaymentChannel' => 0,
                        'billChargeToCustomer' => 1,
                    ]
                );

                // 3. Read ToyyibPay response
                $data = $response->json();

                // 4. If ToyyibPay fails to create bill
                if (
                    !$response->successful()
                    || !is_array($data)
                    || empty($data[0]['BillCode'])
                ) {
                    $booking->delete();

                    return redirect()
                        ->route(
                            'customer.booking.create',
                            $service->id
                        )
                        ->withErrors([
                            'payment' =>
                                'Unable to create ToyyibPay payment. Please try again.'
                        ]);
                }

                // 5. Save ToyyibPay BillCode
                $billCode = $data[0]['BillCode'];

                $booking->update([
                    'toyyibpay_bill_code' => $billCode,
                ]);

                // 6. Redirect customer to ToyyibPay
                return redirect(
                    config('services.toyyibpay.base_url')
                    . '/'
                    . $billCode
                );
    }
    public function toyyibpayReturn(Request $request)
    {
        $statusId = $request->input('status_id');
        $billCode = $request->input('billcode');
        $orderId = $request->input('order_id');
        $transactionId = $request->input('transaction_id');
    
        $booking = Booking::where('id', $orderId)
            ->where('toyyibpay_bill_code', $billCode)
            ->first();
    
        if (!$booking) {
            abort(404, 'Booking payment record not found.');
        }
    
        // Successful payment
        if ($statusId === '1') {
    
            $booking->update([
                'payment_status' => 'paid',
                'toyyibpay_transaction_id' => $transactionId,
            ]);
    
            return redirect()
                ->route(
                    'customer.booking.confirmation',
                    ['booking' => $booking->id]
                )
                ->with(
                    'success',
                    'Payment successful. Your booking has been submitted to the MUA.'
                );
        }
    
        // Failed / cancelled payment
        $booking->update([
            'payment_status' => 'unpaid',
        ]);
    
        return redirect()
            ->route(
                'customer.booking.create',
                [
                    'service' => $booking->service_id,
                    'month' => $booking->booking_date->format('Y-m'),
                    'date' => $booking->booking_date->format('Y-m-d'),
                ]
            )
            ->withErrors([
                'payment' => 'Payment was not successful. Please try again.'
            ]);
    }

    public function toyyibpayCallback(Request $request)
    {
        return response('OK', 200);
    }

    public function confirmation(Booking $booking)
    {
        if ($booking->customer_id !== Auth::id()) {
            abort(403);
        }
    
        $booking->load([
            'customer',
            'makeupArtist.user',
            'service'
        ]);
    
        return view(
            'customer.booking.confirmation',
            compact('booking')
        );
    }

    public function index()
    {
        $bookings = Booking::where('customer_id', Auth::id())
            ->with(['makeupArtist.user', 'service'])
            ->orderBy('booking_date', 'desc')
            ->orderBy('booking_time', 'desc')
            ->get();
    
        return view('customer.booking.index', compact('bookings'));
    }

        public function show(Booking $booking)
    {
        if ($booking->customer_id !== Auth::id()) {
            abort(403);
        }

        $booking->load([
            'makeupArtist.user',
            'service'
        ]);

        return view(
            'customer.booking.show',
            compact('booking')
        );
    }
}