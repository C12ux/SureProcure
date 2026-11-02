@use('App\Enums\PrStatus')
@use('App\Enums\PrPriority')

@php
    $initialItems = old('items', $requisition->exists
        ? $requisition->items->map(fn ($item) => [
            'description' => $item->description,
            'specifications' => $item->specifications,
            'quantity' => (float) $item->quantity,
            'unit' => $item->unit,
            'estimated_unit_price' => $item->estimated_unit_price !== null ? (float) $item->estimated_unit_price : '',
        ])->values()->all()
        : [['description' => '', 'specifications' => '', 'quantity' => 1, 'unit' => '', 'estimated_unit_price' => '']]);

    $isDraft = ! $requisition->exists || $requisition->status === PrStatus::Draft;
@endphp

<form method="POST" action="{{ $action }}" class="max-w-4xl space-y-6"
      x-data="{
          items: @js(array_values($initialItems)),
          total() {
              return this.items.reduce((sum, i) => sum + (parseFloat(i.quantity) || 0) * (parseFloat(i.estimated_unit_price) || 0), 0)
                  .toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          }
      }">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    {{-- Basics --}}
    <section class="app-card space-y-5">
        <h2 class="app-card-title">What do you need?</h2>

        <div>
            <x-input-label for="title" value="Request title" />
            <x-text-input id="title" class="mt-1.5" name="title" type="text" :value="old('title', $requisition->title)" required placeholder="For example: Office chairs for the new branch" />
            <x-input-error :messages="$errors->get('title')" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="category_id" value="Category" />
                <select id="category_id" name="category_id" class="field-input mt-1.5">
                    <option value="">Not sure yet</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $requisition->category_id) === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category_id')" />
            </div>

            <div>
                <x-input-label for="priority" value="Priority" />
                <select id="priority" name="priority" class="field-input mt-1.5" required>
                    @foreach (PrPriority::options() as $value => $label)
                        <option value="{{ $value }}" @selected(old('priority', $requisition->priority?->value ?? 'normal') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('priority')" />
            </div>

            <div>
                <x-input-label for="needed_by_date" value="Needed by" />
                <x-text-input id="needed_by_date" class="mt-1.5" name="needed_by_date" type="date" :value="old('needed_by_date', $requisition->needed_by_date?->toDateString())" />
                <x-input-error :messages="$errors->get('needed_by_date')" />
            </div>

            <div>
                <x-input-label for="budget" value="Total budget (PHP)" />
                <x-text-input id="budget" class="mt-1.5" name="budget" type="number" step="0.01" min="0" :value="old('budget', $requisition->budget)" placeholder="Optional" />
                <x-input-error :messages="$errors->get('budget')" />
            </div>
        </div>

        <div>
            <x-input-label for="description" value="Details" />
            <textarea id="description" name="description" rows="4" class="field-input mt-1.5" placeholder="Anything your officer should know: delivery location, quality expectations, preferred brands.">{{ old('description', $requisition->description) }}</textarea>
            <x-input-error :messages="$errors->get('description')" />
        </div>
    </section>

    {{-- Items --}}
    <section class="app-card">
        <h2 class="app-card-title">Items</h2>
        <p class="field-hint">List each thing you need. Estimated prices are optional and help your officer judge the bids.</p>

        <x-input-error :messages="$errors->get('items')" />
        @foreach ($errors->get('items.*') as $messages)
            <x-input-error :messages="$messages" />
        @endforeach

        <div class="mt-5 space-y-4">
            <template x-for="(item, index) in items" x-bind:key="index">
                <div class="rounded-xl border border-ink/10 bg-mist-50 p-4">
                    <div class="grid gap-4 sm:grid-cols-12">
                        <div class="sm:col-span-7">
                            <label class="field-label" x-bind:for="`item-description-${index}`">Item</label>
                            <input class="field-input mt-1.5" type="text" required
                                   x-bind:id="`item-description-${index}`" x-bind:name="`items[${index}][description]`" x-model="item.description"
                                   placeholder="For example: Ergonomic office chair">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="field-label" x-bind:for="`item-quantity-${index}`">Quantity</label>
                            <input class="field-input mt-1.5" type="number" step="any" min="0" required
                                   x-bind:id="`item-quantity-${index}`" x-bind:name="`items[${index}][quantity]`" x-model="item.quantity">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="field-label" x-bind:for="`item-unit-${index}`">Unit</label>
                            <input class="field-input mt-1.5" type="text"
                                   x-bind:id="`item-unit-${index}`" x-bind:name="`items[${index}][unit]`" x-model="item.unit" placeholder="pcs, box, set">
                        </div>
                        <div class="sm:col-span-8">
                            <label class="field-label" x-bind:for="`item-specs-${index}`">Specifications</label>
                            <input class="field-input mt-1.5" type="text"
                                   x-bind:id="`item-specs-${index}`" x-bind:name="`items[${index}][specifications]`" x-model="item.specifications" placeholder="Size, material, standard (optional)">
                        </div>
                        <div class="sm:col-span-4">
                            <label class="field-label" x-bind:for="`item-price-${index}`">Estimated unit price</label>
                            <input class="field-input mt-1.5" type="number" step="0.01" min="0"
                                   x-bind:id="`item-price-${index}`" x-bind:name="`items[${index}][estimated_unit_price]`" x-model="item.estimated_unit_price" placeholder="Optional">
                        </div>
                    </div>
                    <div class="mt-3 text-right" x-show="items.length > 1">
                        <button type="button" class="auth-link text-sm" x-on:click="items.splice(index, 1)">Remove item</button>
                    </div>
                </div>
            </template>
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-4">
            <button type="button" class="btn btn-md btn-outline-dark"
                    x-on:click="items.push({ description: '', specifications: '', quantity: 1, unit: '', estimated_unit_price: '' })">
                Add another item
            </button>
            <p class="font-display text-xl font-bold tracking-tight">Estimated total PHP <span x-text="total()"></span></p>
        </div>
    </section>

    {{-- Actions --}}
    <div class="flex flex-wrap items-center gap-3">
        @if ($isDraft)
            <button type="submit" name="action" value="draft" class="btn btn-lg btn-outline-dark">Save draft</button>
            <button type="submit" name="action" value="submit" class="btn btn-lg btn-solid">Save and submit</button>
        @else
            <button type="submit" name="action" value="save" class="btn btn-lg btn-solid">Save changes</button>
        @endif

        <a href="{{ $requisition->exists ? route('requisitions.show', $requisition) : route('requisitions.index') }}" class="auth-link ml-2 text-sm">Cancel</a>
    </div>
</form>
