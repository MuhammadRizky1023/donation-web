<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class DonationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function getToken(Request $request)
    {
        $donationId = $request->donationId;
        $amount = $request->amount;

        $params = [
            'transaction_details' => [
                'order_id' => uniqid(),
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json(['token' => $snapToken]);
    }

    public function notificationHandler(Request $request)
    {
        $notification = new \Midtrans\Notification();

        if ($notification->transaction_status == 'capture' || $notification->transaction_status == 'settlement') {
            $donationId = $request->input('donationId');
            $amount = $request->input('amount');

            $donationsRef = app('firebase.firestore')->database()->collection('donations')->document($donationId);

            $donationsRef->get()->then(function($snapshot) use ($amount, $donationsRef) {
                if ($snapshot->exists()) {
                    $donation = $snapshot->data();
                    $progress = $donation['progress'] ?? 0;
                    $progress = min($progress + 10, 100);

                    $donationsRef->update([
                        ['path' => 'progress', 'value' => $progress]
                    ]);
                }
            });
        }

        return response()->json(['message' => 'Notification successfully handled']);
    }

    public function index()
    {
        return view('donations.index');
    }

    public function create()
    {
        return view('donations.create');
    }

    public function edit($id)
    {
        return view('donations.edit', compact('id'));
    }
}
