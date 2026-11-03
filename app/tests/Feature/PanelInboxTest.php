<?php

namespace Tests\Feature;

use App\Enums\EmailStatus;
use App\Enums\MessageStatus;
use App\Mail\ComposedEmail;
use App\Models\Email;
use App\Models\Message;
use App\Models\User;
use App\Support\EmailDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class PanelInboxTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }

    public function test_admins_can_open_the_inbox_screens(): void
    {
        $admin = $this->admin();
        $message = Message::factory()->create();

        $this->actingAs($admin)->get('/mdosi/messages')->assertOk();
        $this->actingAs($admin)->get('/mdosi/messages/'.$message->getKey())->assertOk()
            ->assertSee($message->name);
        $this->actingAs($admin)->get('/mdosi/emails')->assertOk();
        $this->actingAs($admin)->get('/mdosi/emails/create')->assertOk();
    }

    public function test_non_admins_cannot_read_messages_or_emails(): void
    {
        $user = User::factory()->create();
        $message = Message::factory()->create();

        $this->actingAs($user)->get('/mdosi/messages')->assertForbidden();
        $this->actingAs($user)->get('/mdosi/messages/'.$message->getKey())->assertForbidden();
        $this->actingAs($user)->get('/mdosi/emails')->assertForbidden();
    }

    public function test_the_reply_link_prefills_the_composer(): void
    {
        $admin = $this->admin();
        $message = Message::factory()->create(['email' => 'employer@example.com', 'role_title' => 'Designer']);

        $this->actingAs($admin)
            ->get('/mdosi/emails/create?'.http_build_query([
                'to' => $message->email,
                'subject' => $message->replySubject(),
                'message' => $message->getKey(),
            ]))
            ->assertOk()
            ->assertSee('employer@example.com')
            ->assertSee('Re: Designer');
    }

    public function test_a_draft_is_sent_and_marked_as_sent(): void
    {
        Mail::fake();

        $email = Email::factory()->create([
            'recipients' => ['One@Example.com', 'two@example.com'],
            'status' => EmailStatus::Draft,
        ]);

        $this->assertTrue(EmailDelivery::send($email));

        Mail::assertSentCount(2);

        $email->refresh();

        $this->assertSame(EmailStatus::Sent, $email->status);
        $this->assertNotNull($email->sent_at);
        $this->assertNull($email->error);
        $this->assertSame(['one@example.com', 'two@example.com'], $email->recipientList());
    }

    public function test_a_failed_send_keeps_the_reason_and_does_not_stop_other_recipients(): void
    {
        Mail::shouldReceive('to')
            ->once()
            ->with('bad@example.com')
            ->andThrow(new RuntimeException('The provider rejected the message.'));

        Mail::shouldReceive('to')
            ->once()
            ->with('good@example.com')
            ->andReturn(new class
            {
                public function send($mailable): void {}
            });

        $email = Email::factory()->create([
            'recipients' => ['bad@example.com', 'good@example.com'],
            'status' => EmailStatus::Draft,
        ]);

        $this->assertFalse(EmailDelivery::send($email));

        $email->refresh();

        $this->assertSame(EmailStatus::Failed, $email->status);
        $this->assertStringContainsString('bad@example.com', (string) $email->error);
        $this->assertStringContainsString('Delivered to 1 of 2', (string) $email->error);
    }

    public function test_an_email_with_no_recipients_fails_without_calling_the_provider(): void
    {
        Mail::fake();

        $email = Email::factory()->create(['recipients' => [], 'status' => EmailStatus::Draft]);

        $this->assertFalse(EmailDelivery::send($email));
        Mail::assertNothingSent();

        $this->assertSame(EmailStatus::Failed, $email->fresh()->status);
    }

    public function test_the_email_body_is_sanitised_before_rendering(): void
    {
        $email = Email::factory()->create([
            'body' => '<p>Hello</p><script>alert(1)</script><p onclick="x()">There</p>',
        ]);

        $html = (string) $email->safeBody();

        $this->assertStringContainsString('<p>Hello</p>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onclick', $html);
    }

    public function test_messages_can_be_marked_handled_and_unread(): void
    {
        $message = Message::factory()->create();

        $message->markHandled();

        $this->assertSame(MessageStatus::Handled, $message->fresh()->status);
        $this->assertNotNull($message->fresh()->handled_at);

        $message->markNew();

        $this->assertSame(MessageStatus::New, $message->fresh()->status);
        $this->assertNull($message->fresh()->handled_at);
    }

    public function test_the_default_sender_is_used_when_none_is_chosen(): void
    {
        config([
            'mail.from.address' => 'info@plusthree.io',
            'mail.senders' => ['info@plusthree.io'],
        ]);

        $email = Email::factory()->create(['from_address' => null]);

        $this->assertSame('info@plusthree.io', $email->senderAddress());
    }

    public function test_a_chosen_sender_is_used_for_delivery(): void
    {
        config([
            'mail.from.address' => 'info@plusthree.io',
            'mail.senders' => ['info@plusthree.io', 'hello@plusthree.io'],
        ]);

        Mail::fake();

        $email = Email::factory()->create([
            'from_address' => 'hello@plusthree.io',
            'recipients' => ['one@example.com'],
        ]);

        $this->assertTrue(EmailDelivery::send($email));

        Mail::assertSent(ComposedEmail::class, fn (ComposedEmail $mail): bool => $mail->envelope()->from->address === 'hello@plusthree.io');
    }

    public function test_an_unconfigured_sender_falls_back_to_the_default(): void
    {
        config([
            'mail.from.address' => 'info@plusthree.io',
            'mail.senders' => ['info@plusthree.io'],
        ]);

        Mail::fake();

        $email = Email::factory()->create([
            'from_address' => 'stranger@example.com',
            'recipients' => ['one@example.com'],
        ]);

        $this->assertSame('info@plusthree.io', $email->senderAddress());
        $this->assertTrue(EmailDelivery::send($email));

        Mail::assertSent(ComposedEmail::class, fn (ComposedEmail $mail): bool => $mail->envelope()->from->address === 'info@plusthree.io');
    }

    public function test_the_composer_only_offers_a_sender_picker_when_there_is_a_choice(): void
    {
        $admin = $this->admin();

        config(['mail.senders' => ['info@plusthree.io']]);

        $this->actingAs($admin)->get('/mdosi/emails/create')->assertOk()
            ->assertDontSee('Send from');

        config(['mail.senders' => ['info@plusthree.io', 'hello@plusthree.io']]);

        $this->actingAs($admin)->get('/mdosi/emails/create')->assertOk()
            ->assertSee('Send from')
            ->assertSee('hello@plusthree.io');
    }
}
