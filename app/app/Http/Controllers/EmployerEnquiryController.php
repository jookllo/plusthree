<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentType;
use App\Enums\MessageStatus;
use App\Http\Requests\StoreEmployerEnquiry;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * The public employer form.
 *
 * Submissions are stored for the panel inbox; nothing is emailed from here, so
 * a mail outage cannot lose an enquiry.
 */
class EmployerEnquiryController extends Controller
{
    public function show(): View
    {
        return view('employers.show', [
            'employmentTypes' => EmploymentType::options(),
            'sent' => session('enquiry_sent', false),
        ]);
    }

    public function store(StoreEmployerEnquiry $request): RedirectResponse
    {
        // Honeypot: bots fill every field. Pretend it worked.
        if (filled($request->input('website'))) {
            Log::info('Discarded employer enquiry that filled the honeypot.');

            return $this->confirmation();
        }

        $data = $request->validated();

        $message = Message::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'company' => $data['company'] ?? null,
            'role_title' => $data['role_title'] ?? null,
            'employment_type' => $data['employment_type'] ?? null,
            'link' => $data['link'] ?? null,
            'body' => $data['body'],
            'source' => 'employer_form',
            'status' => MessageStatus::New,
        ]);

        Log::info('Employer enquiry stored.', ['message_id' => $message->id]);

        return $this->confirmation();
    }

    private function confirmation(): RedirectResponse
    {
        return redirect()
            ->route('employers.show')
            ->with('enquiry_sent', true);
    }
}
