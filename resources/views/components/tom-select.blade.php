@props(['options' => [], 'placeholder' => 'Select option...', 'url' => null])

<div wire:ignore class="w-full">
    <select
        x-data="{
            tom: null,
            value: @entangle($attributes->wire('model')),

            init() {
                if (this.tom) return;

                let config = {
                    items: this.value ? [this.value] : [],
                    placeholder: '{{ $placeholder }}',
                    plugins: ['clear_button'],
                    create: false,
                    sortField: {
                        field: 'text',
                        direction: 'asc'
                    },
                    onItemAdd: (value) => {
                        this.value = value;
                    },
                    onItemRemove: (value) => {
                        this.value = null;
                    },
                    onClear: () => {
                        this.value = null;
                    }
                };

                if ('{{ $url }}') {
                    config.load = (query, callback) => {
                        const url = '{{ $url }}' + ( '{{ $url }}'.includes('?') ? '&' : '?' ) + 'q=' + encodeURIComponent(query);
                        fetch(url)
                            .then(response => response.json())
                            .then(json => {
                                callback(json);
                            })
                            .catch(() => {
                                callback();
                            });
                    };
                }

                this.tom = new TomSelect(this.$el, config);

                this.$watch('value', (newValue) => {
                    const current = this.tom.getValue();
                    if (newValue !== current) {
                        if (!newValue) {
                            this.tom.clear(true);
                        } else {
                            this.tom.setValue(newValue, true);
                        }
                    }
                });
            }
        }"
        x-init="init"
        {{ $attributes->whereDoesntStartWith('wire:model') }}
        autocomplete="off"
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $option)
            <option value="{{ $option['value'] }}">{{ $option['text'] ?? $option['label'] }}</option>
        @endforeach
    </select>
</div>
