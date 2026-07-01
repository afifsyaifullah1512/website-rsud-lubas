<div>
    <form method="post" action="{{ route('pengaduan.store') }}" class="card p-5 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="name">Nama Lengkap</label>
            <input id="name" name="name" type="text" required minlength="3" maxlength="120"
                value="{{ $name }}"
                class="w-full border-slate-300 rounded-md text-sm @error('name') border-rose-400 @enderror">
            @error('name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="email">Email</label>
                <input id="email" name="email" type="email" required maxlength="160"
                    value="{{ $email }}"
                    class="w-full border-slate-300 rounded-md text-sm @error('email') border-rose-400 @enderror">
                @error('email')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="phone">Telepon <span class="text-slate-400">(opsional)</span></label>
                <input id="phone" name="phone" type="tel" maxlength="20" pattern="[0-9+\-() ]{8,20}"
                    value="{{ $phone }}"
                    class="w-full border-slate-300 rounded-md text-sm @error('phone') border-rose-400 @enderror">
                @error('phone')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="subject">Subjek</label>
            <input id="subject" name="subject" type="text" required maxlength="200"
                value="{{ $subject }}"
                class="w-full border-slate-300 rounded-md text-sm @error('subject') border-rose-400 @enderror">
            @error('subject')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div x-data="{ count: {{ strlen($message) }} }">
            <label class="block text-sm font-medium text-slate-700 mb-1" for="message">Pesan</label>
            <textarea id="message" name="message" required minlength="20" maxlength="5000" rows="6"
                x-on:input="count = $event.target.value.length"
                class="w-full border-slate-300 rounded-md text-sm @error('message') border-rose-400 @enderror">{{ $message }}</textarea>
            <p class="text-xs text-slate-500 mt-1">
                Minimal 20 karakter, maksimal 5000 karakter
                (<span x-text="count"></span>/5000).
            </p>
            @error('message')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Token reCAPTCHA v3 (Requirement 11.1, 11.6). Hanya dirender bila
             sitekey dikonfigurasi; verifikasi dilakukan middleware `recaptcha`. --}}
        @if ($recaptchaSitekey !== '')
            {!! \Lunaweb\RecaptchaV3\Facades\RecaptchaV3::initJs() !!}
            {!! \Lunaweb\RecaptchaV3\Facades\RecaptchaV3::field('complaint') !!}
        @endif

        <button type="submit" class="bg-brand-700 hover:bg-brand-800 text-white px-5 py-2.5 rounded-md font-semibold">
            Kirim Pengaduan
        </button>
    </form>
</div>
