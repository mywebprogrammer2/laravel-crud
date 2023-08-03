<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\Payment;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        $this->updateInvoiceStatus($payment);
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        $this->updateInvoiceStatus($payment);
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        //
    }

    public function updateInvoiceStatus(Payment $payment){
        $total_paid = Payment::where('invoice_id', $payment->invoice_id)->sum('amount_paid');
        $inv=  Invoice::find( $payment->invoice_id );
        $to_paid=   $inv->total_amount;
        if($total_paid < $to_paid){
            $inv->update(['status' => 'partially paid']);
        }
        else if($total_paid == $to_paid){
            $inv->update(['status' => 'paid']);
        }
    }

}
