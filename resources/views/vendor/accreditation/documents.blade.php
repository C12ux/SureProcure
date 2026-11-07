@php
    $docTone = ['pending' => 'neutral', 'verified' => 'success', 'rejected' => 'danger'];
    $docText = ['pending' => 'Awaiting review', 'verified' => 'Verified', 'rejected' => 'Rejected'];
    $size = fn ($bytes) => $bytes >= 1048576 ? number_format($bytes / 1048576, 1).' MB' : number_format(max(1, $bytes) / 1024).' KB';
@endphp

<x-app-layout>
    <x-slot name="title">Documents</x-slot>
    <x-slot name="actions"><a href="{{ route('vendor.accreditation.show') }}" class="btn btn-sm btn-outline-dark">Back</a></x-slot>

    <div class="max-w-3xl">
        <p class="lead text-ink/70">Upload a PDF, image (JPG or PNG) or Word file, up to 10 MB each. Files are private: only you and SureProcure can open them.</p>

        <div class="app-card mt-6 divide-y divide-ink/10 py-0">
            @foreach ($checklist as $row)
                @php
                    $type = $row['type'];
                    $files = $vendor->documents->filter(fn ($d) => $d->type === $type)->values();
                    $met = $files->count() >= $row['min'];
                @endphp

                <section class="py-6" aria-labelledby="doc-{{ $type->value }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 id="doc-{{ $type->value }}" class="font-semibold">
                                {{ $type->label() }}
                                @unless ($row['required'])<span class="font-normal text-ink/60">(optional)</span>@endunless
                            </h2>
                            <p class="mt-1 text-sm text-ink/70">{{ $row['hint'] }}</p>
                            @if ($row['min'] > 1)
                                <p class="mt-1 text-sm text-ink/70">{{ $files->count() }} of {{ $row['min'] }} uploaded.</p>
                            @endif
                        </div>
                        @if ($met)
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-jackson text-mist-50">
                                <x-brand.check class="h-4 w-4" /><span class="sr-only">Done</span>
                            </span>
                        @endif
                    </div>

                    @if ($files->isNotEmpty())
                        <ul class="mt-4 space-y-2">
                            @foreach ($files as $doc)
                                @php $state = $doc->status->value; @endphp
                                <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-ink/10 bg-mist-50 px-4 py-3 text-sm">
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">{{ $doc->original_name }}</p>
                                        <p class="text-ink/60">{{ $size($doc->size_bytes ?? 0) }}, {{ $doc->created_at->format('M j, Y') }}</p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="badge badge--{{ $docTone[$state] }}"><span class="badge-dot"></span>{{ $docText[$state] }}</span>
                                        <a href="{{ route('documents.show', $doc) }}" target="_blank" rel="noopener" class="auth-link">View</a>
                                        @if ($canEdit && $state === 'pending')
                                            <form method="POST" action="{{ route('documents.destroy', $doc) }}" onsubmit="return confirm('Remove this file?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-semibold text-danger-deep underline underline-offset-4">Remove</button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($canEdit)
                        <form method="POST" action="{{ route('vendor.accreditation.documents.upload') }}" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type->value }}">
                            <label class="sr-only" for="file-{{ $type->value }}">Choose a file for {{ $type->label() }}</label>
                            <input id="file-{{ $type->value }}" type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                   class="block w-full rounded-xl border border-ink/25 bg-white text-sm file:mr-4 file:cursor-pointer file:border-0 file:bg-mist-200 file:px-4 file:py-3 file:font-semibold file:text-ink">
                            <button type="submit" class="btn btn-md btn-outline-dark shrink-0">Upload</button>
                        </form>
                        <x-input-error :messages="$errors->getBag('upload_'.$type->value)->get('file')" />
                    @endif
                </section>
            @endforeach
        </div>
    </div>
</x-app-layout>
