@extends('layouts.site')

@section('title', 'Share an opportunity — Plusthree')
@section('meta_description', 'Send a role, fellowship or other opportunity to the Plusthree team for review and publishing.')

@section('content')
    <section class="search-band" aria-labelledby="employers-title">
        <div class="wrap">
            <div class="search-intro">
                <h1 id="employers-title">Share an opportunity</h1>
                <p>Reviewed and published by the plusthree team.</p>
            </div>
        </div>
    </section>

    <div class="wrap employers-page">
        <div class="employers-intro">
            <h2>Tell us about the role.</h2>
            <p>Plusthree publishes jobs, fellowships and other opportunities for African talent. Send the details here and the team will review them before anything appears on the board.</p>
            <p>Include a link to the full description or application page if you have one. We publish roles that are open to candidates in Africa or the diaspora.</p>
            <p>Prefer email? Write to <a class="inline-mail" href="mailto:info@plusthree.io">info@plusthree.io</a>.</p>
        </div>

        @if ($sent)
            <div class="enquiry-sent" role="status">
                <h2>Thank you — it reached us.</h2>
                <p>The team reviews submissions before publishing. If we need anything else we will reply to the address you gave.</p>
                <p><a href="{{ route('employers.show') }}">Send another opportunity</a> or <a href="{{ route('jobs.index') }}">browse open roles</a>.</p>
            </div>
        @else
            <form class="enquiry-form" action="{{ route('employers.store') }}" method="post">
                @csrf

                <div class="honeypot" aria-hidden="true">
                    <label for="website">Website</label>
                    <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-grid">
                    <div class="field">
                        <label for="name">Your name <span aria-hidden="true">*</span></label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" maxlength="120" required autocomplete="name"
                               @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                        @error('name')<span class="field-error" id="name-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="email">Email <span aria-hidden="true">*</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="190" required autocomplete="email"
                               @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        @error('email')<span class="field-error" id="email-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="company">Organisation</label>
                        <input id="company" name="company" type="text" value="{{ old('company') }}" maxlength="160" autocomplete="organization"
                               @error('company') aria-invalid="true" aria-describedby="company-error" @enderror>
                        @error('company')<span class="field-error" id="company-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="role_title">Role title</label>
                        <input id="role_title" name="role_title" type="text" value="{{ old('role_title') }}" maxlength="160"
                               @error('role_title') aria-invalid="true" aria-describedby="role_title-error" @enderror>
                        @error('role_title')<span class="field-error" id="role_title-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="employment_type">Opportunity type</label>
                        <select id="employment_type" name="employment_type"
                                @error('employment_type') aria-invalid="true" aria-describedby="employment_type-error" @enderror>
                            <option value="">Not sure</option>
                            @foreach ($employmentTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('employment_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('employment_type')<span class="field-error" id="employment_type-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="link">Link to the role or application</label>
                        <input id="link" name="link" type="url" value="{{ old('link') }}" maxlength="500" placeholder="https://"
                               inputmode="url"
                               @error('link') aria-invalid="true" aria-describedby="link-error" @enderror>
                        @error('link')<span class="field-error" id="link-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field full">
                        <label for="body">Details <span aria-hidden="true">*</span></label>
                        <textarea id="body" name="body" rows="6" maxlength="4000" required
                                  @error('body') aria-invalid="true" aria-describedby="body-error" @enderror>{{ old('body') }}</textarea>
                        @error('body')<span class="field-error" id="body-error">{{ $message }}</span>@enderror
                        <span class="hint">Location, closing date, who can apply, and how to apply. Up to 4000 characters.</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="button" type="submit">
                        Send to the team
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h15"/><path d="m13 6 6 6-6 6"/></svg>
                    </button>
                    <span class="form-note">We use these details only to review and publish the opportunity.</span>
                </div>
            </form>
        @endif
    </div>
@endsection
