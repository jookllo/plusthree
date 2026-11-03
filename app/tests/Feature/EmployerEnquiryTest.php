<?php

namespace Tests\Feature;

use App\Enums\MessageStatus;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployerEnquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_employer_page_offers_a_form_and_an_email_fallback(): void
    {
        $this->get('/for-employers')->assertOk()
            ->assertSee('Share an opportunity')
            ->assertSee('action="'.route('employers.store').'"', false)
            ->assertSee('name="body"', false)
            ->assertSee('Select a type')
            ->assertDontSee('Not sure')
            ->assertSee('info@plusthree.io');
    }

    public function test_a_valid_enquiry_is_stored_for_the_panel_inbox(): void
    {
        $response = $this->post('/for-employers', [
            'name' => 'Amina Yusuf',
            'email' => 'Amina@Example.com',
            'company' => 'Savannah Labs',
            'role_title' => 'Product Designer',
            'employment_type' => 'full_time',
            'link' => 'https://example.com/jobs/designer',
            'body' => 'We are hiring a product designer for our Nairobi team.',
        ]);

        $response->assertRedirect('/for-employers')
            ->assertSessionHas('enquiry_sent', true);

        $message = Message::query()->sole();

        $this->assertSame('Amina Yusuf', $message->name);
        $this->assertSame('amina@example.com', $message->email);
        $this->assertSame('Savannah Labs', $message->company);
        $this->assertSame(MessageStatus::New, $message->status);
        $this->assertSame('employer_form', $message->source);
    }

    public function test_the_confirmation_panel_replaces_the_form_after_sending(): void
    {
        $this->followingRedirects()
            ->post('/for-employers', [
                'name' => 'Amina Yusuf',
                'email' => 'amina@example.com',
                'body' => 'We are hiring a product designer for our Nairobi team.',
            ])
            ->assertOk()
            ->assertSee('Thank you — it reached us.')
            ->assertDontSee('class="enquiry-form"', false);
    }

    public function test_an_enquiry_missing_details_is_rejected(): void
    {
        $this->post('/for-employers', [
            'name' => '',
            'email' => 'not-an-email',
            'body' => 'short',
        ])->assertSessionHasErrors(['name', 'email', 'body']);

        $this->assertSame(0, Message::query()->count());
    }

    public function test_non_http_links_are_rejected(): void
    {
        $this->post('/for-employers', [
            'name' => 'Amina Yusuf',
            'email' => 'amina@example.com',
            'link' => 'javascript:alert(1)',
            'body' => 'We are hiring a product designer for our Nairobi team.',
        ])->assertSessionHasErrors(['link']);

        $this->assertSame(0, Message::query()->count());
    }

    public function test_the_honeypot_is_accepted_and_discarded(): void
    {
        $this->post('/for-employers', [
            'name' => 'Spam Bot',
            'email' => 'bot@example.com',
            'body' => 'Buy cheap links from this definitely real employer.',
            'website' => 'https://spam.example',
        ])->assertRedirect('/for-employers')
            ->assertSessionHas('enquiry_sent', true);

        $this->assertSame(0, Message::query()->count());
    }
}
